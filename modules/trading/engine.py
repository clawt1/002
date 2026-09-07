import asyncio
import logging
from dataclasses import dataclass, field
from datetime import datetime, timedelta
from typing import Any, Dict, List, Optional

import numpy as np
import pandas as pd
from pydantic import BaseModel, Field

import ccxt.async_support as ccxt_async
import yfinance as yf

from modules.comms.event_bus import EventBus

logger = logging.getLogger(__name__)


# --- Data Models ---
class Order(BaseModel):
    timestamp: datetime
    symbol: str
    side: str  # BUY or SELL
    price: float
    size: float  # Quantity
    fee: float = 0.0
    slippage_applied: float = 0.0
    exit_reason: Optional[str] = None


class BacktestResult(BaseModel):
    project_id: str
    symbol: str
    initial_capital: float
    final_equity: float
    total_return_pct: float
    total_fees: float
    win_rate: float
    sharpe_ratio: float
    max_drawdown: float
    trades: List[Order] = Field(default_factory=list)
    equity_curve: List[float] = Field(default_factory=list)


# --- Asynchronous Data Fetcher ---
class AsyncDataFetcher:
    def __init__(self):
        self.exchange = None

    async def __aenter__(self):
        self.exchange = ccxt_async.binance({'enableRateLimit': True})
        return self

    async def __aexit__(self, exc_type, exc, tb):
        if self.exchange:
            await self.exchange.close()

    async def fetch_ohlcv(self, symbol: str, days: int, timeframe: str = '1h') -> pd.DataFrame:
        """Asynchronous pagination without blocking the orchestrator."""
        since = self.exchange.parse8601((datetime.now() - timedelta(days=days)).isoformat())
        all_candles = []
        limit = 1000

        while True:
            batch = await self.exchange.fetch_ohlcv(symbol, timeframe, since=since, limit=limit)
            if not batch:
                break
            all_candles.extend(batch)
            last_ts = batch[-1][0]
            if len(batch) < limit:
                break
            since = last_ts + 1

        df = pd.DataFrame(all_candles, columns=['timestamp', 'open', 'high', 'low', 'close', 'volume'])
        df['timestamp'] = pd.to_datetime(df['timestamp'], unit='ms')
        return df.set_index('timestamp').sort_index()

    async def fetch_macro_async(self, days: int) -> pd.DataFrame:
        """yfinance is synchronous, offloaded to thread executor."""
        loop = asyncio.get_running_loop()
        start = (datetime.now() - timedelta(days=days + 5)).strftime("%Y-%m-%d")
        tickers = {"VIX": "^VIX", "DXY": "DX-Y.NYB", "US10Y": "^TNX"}

        def _sync_download():
            data = {}
            for name, tk in tickers.items():
                try:
                    hist = yf.download(tk, start=start, progress=False, auto_adjust=True)
                    if not hist.empty:
                        s = hist['Close']
                        data[name] = s / 10.0 if name == "US10Y" else s
                except Exception as e:
                    logger.warning(f"Error downloading {tk}: {e}")
            if not data:
                dates = pd.date_range(end=datetime.now(), periods=days, freq='D')
                return pd.DataFrame({'VIX': 18.0, 'DXY': 102.0, 'US10Y': 4.2}, index=dates)
            return pd.DataFrame(data).ffill().bfill().tail(days)

        df = await loop.run_in_executor(None, _sync_download)
        df.index = pd.to_datetime(df.index).normalize()
        return df


# --- Pure Trading Engine ---
class TradingCore:
    @staticmethod
    def compute_features(df_ohlcv: pd.DataFrame, macro_df: pd.DataFrame, gdelt_df: pd.DataFrame) -> pd.DataFrame:
        """Robust temporal alignment."""
        df = df_ohlcv.copy()
        df['date'] = df.index.normalize()
        macro_df.index = macro_df.index.normalize()
        gdelt_df.index = gdelt_df.index.normalize()

        for col in macro_df.columns:
            df[col] = macro_df[col].reindex(df['date'], method='ffill').values
        df['gdelt_sentiment'] = gdelt_df['sentiment'].reindex(df['date'], method='ffill').values
        df['gdelt_volume'] = gdelt_df['volume'].reindex(df['date'], method='ffill').values

        df['macro_stress'] = ((df['VIX'] / 30) * 0.6 + ((df['DXY'] - 95) / 15).clip(0, 1) * 0.4) * 100
        df['geo_fear'] = (1 - df['gdelt_sentiment'].clip(-1, 1)) * 30 + df['macro_stress'] * 0.5
        df['geo_fear'] = df['geo_fear'].clip(0, 100)
        return df

    @staticmethod
    def generate_signals(df: pd.DataFrame, symbol: str) -> pd.DataFrame:
        df['ema_7'] = df['close'].ewm(span=7).mean()
        df['ema_30'] = df['close'].ewm(span=30).mean()
        tech = (df['ema_7'] > df['ema_30']).astype(int) * 2 - 1  # -1 or 1
        geo_weight = 0.25 if 'BTC' in symbol else 0.40
        geo_signal = np.where(df['geo_fear'] > 70, 1, np.where(df['geo_fear'] < 30, -1, 0))
        df['composite'] = tech * (1 - geo_weight) + geo_signal * geo_weight
        df['buy_prob'] = ((df['composite'] + 1) / 2 * 100).clip(0, 100)
        return df

    @staticmethod
    def simulate_trades(df: pd.DataFrame, initial_capital: float, fee_pct=0.001, slippage=0.0005) -> BacktestResult:
        capital = initial_capital
        position = 0.0
        entry_price = 0.0
        trades = []
        equity = [initial_capital]
        max_equity = initial_capital

        for i in range(1, len(df)):
            row = df.iloc[i]
            price = float(row['close'])
            prob = float(row['buy_prob'])
            ts = df.index[i]

            # Stop-loss / Take-profit
            if position > 0:
                pnl_pct = (price - entry_price) / entry_price
                if pnl_pct <= -0.03:  # SL
                    fill_price = price * (1 - slippage)
                    proceeds = position * fill_price
                    fee = proceeds * fee_pct
                    capital += proceeds - fee
                    trades.append(Order(timestamp=ts, symbol="SYM", side="SELL", price=fill_price, size=position, fee=fee, exit_reason="stop_loss"))
                    position = 0.0
                elif pnl_pct >= 0.06:  # TP
                    fill_price = price * (1 - slippage)
                    proceeds = position * fill_price
                    fee = proceeds * fee_pct
                    capital += proceeds - fee
                    trades.append(Order(timestamp=ts, symbol="SYM", side="SELL", price=fill_price, size=position, fee=fee, exit_reason="take_profit"))
                    position = 0.0

            # Entry / Exit logic
            if prob > 65 and position == 0:
                fill_price = price * (1 + slippage)
                size_to_buy = (capital * 0.02) / fill_price  # Risk 2%
                fee = (size_to_buy * fill_price) * fee_pct
                if capital > fee:
                    capital -= (size_to_buy * fill_price) + fee
                    position = size_to_buy
                    entry_price = fill_price
                    trades.append(Order(timestamp=ts, symbol="SYM", side="BUY", price=fill_price, size=size_to_buy, fee=fee))
            elif prob < 35 and position > 0:
                fill_price = price * (1 - slippage)
                proceeds = position * fill_price
                fee = proceeds * fee_pct
                capital += proceeds - fee
                trades.append(Order(timestamp=ts, symbol="SYM", side="SELL", price=fill_price, size=position, fee=fee, exit_reason="signal"))
                position = 0.0

            current_equity = capital + (position * price)
            equity.append(current_equity)
            max_equity = max(max_equity, current_equity)

        # Force close position at end
        if position > 0:
            price = float(df['close'].iloc[-1])
            fill_price = price * (1 - slippage)
            proceeds = position * fill_price
            fee = proceeds * fee_pct
            capital += proceeds - fee
            trades.append(Order(timestamp=df.index[-1], symbol="SYM", side="SELL", price=fill_price, size=position, fee=fee, exit_reason="end_of_backtest"))
            position = 0.0
            equity[-1] = capital

        # Compute metrics
        sells = [t for t in trades if t.side == "SELL"]
        wins = sum(1 for t in sells if (t.price * t.size) > 0)
        final_eq = equity[-1] if equity else capital
        dd = max(0, max(equity) - final_eq)

        returns = np.diff(equity)
        sharpe = (np.mean(returns) / (np.std(returns) + 1e-6)) * np.sqrt(252) if len(returns) > 0 else 0.0

        return BacktestResult(
            project_id="N/A",
            symbol="SYM",
            initial_capital=initial_capital,
            final_equity=final_eq,
            total_return_pct=((final_eq - initial_capital) / initial_capital) * 100,
            total_fees=sum(t.fee for t in trades),
            win_rate=(wins / len(sells)) * 100 if sells else 0,
            sharpe_ratio=float(sharpe),
            max_drawdown=float(dd / max_equity * 100) if max_equity > 0 else 0,
            trades=trades,
            equity_curve=equity
        )
