from typing import Any, Optional


class BaseAgent:
    """Base agent class for Nexus-OS agent framework."""

    def __init__(self, role: str, context: Any = None, llm: Any = None):
        self.role = role
        self.context = context
        self.llm = llm

    async def execute(self, task: str, additional_context: str = "") -> Any:
        raise NotImplementedError("Subclasses must implement execute()")
