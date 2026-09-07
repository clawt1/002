from typing import Any, Dict, List, Optional


class Memory:
    """Vector / Context Memory interface for Nexus-OS agents."""

    _instance = None

    def __new__(cls, *args, **kwargs):
        if cls._instance is None:
            cls._instance = super(Memory, cls).__new__(cls)
            cls._instance._store = []
        return cls._instance

    def store(self, content: str, metadata: Optional[Dict[str, Any]] = None) -> None:
        """Stores a memory record with metadata."""
        record = {"content": content, "metadata": metadata or {}}
        self._store.append(record)

    def query(self, query_text: str, limit: int = 5) -> List[Dict[str, Any]]:
        """Queries stored memories."""
        return [item for item in self._store if query_text.lower() in item["content"].lower()][:limit]
