"""Nexus-OS System Bootstrapper."""

DEPENDENCIES = [
    "ccxt>=4.0.0",
    "yfinance>=0.2.0",
    "pandas>=2.0",
    "numpy>=1.24",
    "matplotlib",
    "reportlab",  # For PDF reporting
    "aiohttp",    # For async API calls
    "pydantic>=2.0",
    "plotly",
    "streamlit"
]


def check_and_install_dependencies():
    """Checks and installs Nexus-OS required dependencies."""
    import importlib
    import subprocess
    import sys

    missing = []
    for dep in DEPENDENCIES:
        pkg_name = dep.split(">=")[0].strip()
        try:
            importlib.import_module(pkg_name)
        except ImportError:
            missing.append(dep)

    if missing:
        print(f"Installing missing dependencies: {missing}")
        subprocess.check_call([sys.executable, "-m", "pip", "install", *missing])


if __name__ == "__main__":
    print("Bootstrapping Nexus-OS Trading Module...")
    print(f"Registered Dependencies: {DEPENDENCIES}")
