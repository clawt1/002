import plotly.graph_objects as go
import streamlit as st

from modules.comms.event_bus import EventBus

st.set_page_config(page_title="Nexus-Trading", layout="wide")
st.title("📊 Nexus-Trading - Tableau de bord Macro-Géo")

# Zone d'affichage des résultats
if "trading_result" not in st.session_state:
    st.session_state.trading_result = None


def on_trading_completed(msg):
    st.session_state.trading_result = msg['data']


# Abonnement au bus d'événements
EventBus().subscribe("trading_completed", on_trading_completed)

if st.session_state.trading_result:
    res = st.session_state.trading_result
    col1, col2, col3, col4 = st.columns(4)
    col1.metric("Return", f"{res['total_return_pct']:.2f}%")
    col2.metric("Win Rate", f"{res['win_rate']:.1f}%")
    col3.metric("Sharpe", f"{res['sharpe_ratio']:.2f}")
    col4.metric("Drawdown", f"{res['max_drawdown']:.2f}%")

    # Courbe d'équité interactive Plotly
    fig = go.Figure()
    fig.add_trace(go.Scatter(y=res['equity_curve'], mode='lines', name='Equity'))
    fig.update_layout(title="Courbe d'équité", xaxis_title="Période", yaxis_title="Capital ($)")
    st.plotly_chart(fig, use_container_width=True)
else:
    st.info("Aucun backtest exécuté. En attente d'événements de trading...")
