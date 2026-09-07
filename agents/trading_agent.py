import asyncio
from datetime import datetime
import json
import numpy as np
import pandas as pd

from core.agent_factory import BaseAgent
from core.memory import Memory
from modules.comms.event_bus import EventBus
from modules.trading.engine import AsyncDataFetcher, TradingCore


class TradingAgent(BaseAgent):
    def __init__(self, context=None, llm=None):
        super().__init__(role="trader", context=context, llm=llm)
        self.tools = {"run_analysis": self._run_full_backtest}

    async def _run_full_backtest(self, symbol: str, days: int, capital: float):
        """Executes the complete backtest pipeline and publishes structured events."""
        # 1. Notify UI via EventBus
        EventBus().publish("trading_status", {"msg": f"Début backtest {symbol} sur {days}j"})

        async with AsyncDataFetcher() as fetcher:
            # 2. Async data fetching
            ohlcv, macro, gdelt = await asyncio.gather(
                fetcher.fetch_ohlcv(symbol, days),
                fetcher.fetch_macro_async(days),
                self._fetch_gdelt_async(days)
            )

        # 3. Pure signal computation and simulation
        df = TradingCore.compute_features(ohlcv, macro, gdelt)
        df = TradingCore.generate_signals(df, symbol)
        result = TradingCore.simulate_trades(df, capital)

        # 4. Enrich result with context
        project_id = getattr(self.context, 'project_id', 'N/A') if self.context else 'N/A'
        result.project_id = project_id
        result.symbol = symbol

        # 5. Save into vector memory
        Memory().store(
            f"Backtest {symbol}: Return {result.total_return_pct:.2f}%",
            {"type": "trading", "project": project_id}
        )

        # 6. Publish on event bus for real-time dashboard updates
        EventBus().publish("trading_completed", result.model_dump() if hasattr(result, 'model_dump') else result.dict())

        # 7. Return to orchestrator
        return result

    async def _fetch_gdelt_async(self, days: int) -> pd.DataFrame:
        """Asynchronous GDELT sentiment fallback."""
        dates = pd.date_range(end=datetime.now(), periods=days, freq='D')
        rng = np.random.default_rng(42)
        return pd.DataFrame({
            'sentiment': np.clip(rng.normal(0, 0.5, days).cumsum() * 0.1, -1, 1),
            'volume': rng.poisson(150, days)
        }, index=dates)

    async def execute(self, task: str, additional_context: str = ""):
        """Main execution entry point called by orchestrator."""
        if self.llm:
            parse_prompt = f"Extrait de cette phrase le symbole (ex: BTC/USDT), le nombre de jours et le capital : {task}. Réponds en JSON."
            llm_response = await self.llm.chat(model="llama3", messages=[{"role": "user", "content": parse_prompt}])
            try:
                params = json.loads(llm_response)
                return await self._run_full_backtest(
                    symbol=params.get("symbol", "BTC/USDT"),
                    days=int(params.get("days", 60)),
                    capital=float(params.get("capital", 100000))
                )
            except Exception as e:
                EventBus().publish("trading_error", {"error": str(e)})
                return {"status": "failed", "reason": str(e)}
        else:
            # Fallback if no LLM instance is attached
            return await self._run_full_backtest(symbol="BTC/USDT", days=60, capital=100000.0)
