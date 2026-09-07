from typing import Callable, Dict, List, Any


class EventBus:
    """Singleton EventBus for asynchronous pub/sub messaging across Nexus-OS modules."""

    _instance = None

    def __new__(cls, *args, **kwargs):
        if cls._instance is None:
            cls._instance = super(EventBus, cls).__new__(cls)
            cls._instance._subscribers = {}
        return cls._instance

    def subscribe(self, event_type: str, callback: Callable[[Dict[str, Any]], None]) -> None:
        """Subscribes a callback to an event type."""
        if event_type not in self._subscribers:
            self._subscribers[event_type] = []
        self._subscribers[event_type].append(callback)

    def publish(self, event_type: str, data: Any) -> None:
        """Publishes an event payload to all registered subscribers."""
        payload = {"event": event_type, "data": data}
        if event_type in self._subscribers:
            for callback in self._subscribers[event_type]:
                try:
                    callback(payload)
                except Exception as e:
                    print(f"Error executing callback for event {event_type}: {e}")
