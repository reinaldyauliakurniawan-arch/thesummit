"""
The Summit — card loader.

Reads all card JSON files from database/cards/ and parses them into
Card objects. Cards are kept as raw dicts (matching the JSON structure)
plus convenience accessors — the simulator and BehaviorTracker use the
same data shape the production Laravel app stores in `expedition_cards`.

Card JSON structure (key fields used by simulator):
    id, level, category, type, narrative.situation,
    choices.A.text, choices.A.effects[], choices.A.behavior_tags{},
    choices.A.lra_tags{},
    choices.B.text, choices.B.effects[], choices.B.behavior_tags{},
    choices.B.lra_tags{},
    hidden_info.enabled, hidden_info.reveal_timing,
    narrative.hidden_reveal
"""
from __future__ import annotations
import json
import os
from dataclasses import dataclass, field
from typing import Any

CARDS_DIR = "/home/z/my-project/thesummit/database/cards"


@dataclass
class Card:
    """Parsed card with convenience accessors."""
    id: str
    level: str
    category: str
    type: str
    situation: str
    hidden_reveal: str | None
    has_hidden_info: bool
    hidden_info_reveal_timing: str | None
    choices: dict[str, dict]  # {"A": {...}, "B": {...}}
    raw: dict = field(default_factory=dict)

    def is_krisis(self) -> bool:
        return self.type == "krisis"

    def option_text(self, option: str) -> str:
        return self.choices.get(option, {}).get("text", "")

    def option_effects(self, option: str) -> list[dict]:
        return self.choices.get(option, {}).get("effects", [])

    def option_behavior_tags(self, option: str) -> dict[str, int]:
        return self.choices.get(option, {}).get("behavior_tags", {}) or {}

    def option_lra_tags(self, option: str) -> dict[str, str]:
        return self.choices.get(option, {}).get("lra_tags", {}) or {}

    def all_lra_items(self) -> set[str]:
        """Return set of all LRA items tagged on EITHER option."""
        items: set[str] = set()
        for opt in ("A", "B"):
            items.update(self.option_lra_tags(opt).keys())
        return items

    def all_behavior_dims(self) -> set[str]:
        """Return set of all behavior dimensions tagged on EITHER option."""
        dims: set[str] = set()
        for opt in ("A", "B"):
            dims.update(self.option_behavior_tags(opt).keys())
        return dims

    def stat_deltas(self, option: str) -> dict[str, int]:
        """Flatten modify_stat effects into a {stat: delta} dict for the option.

        Sums multiple modify_stat effects on the same stat.
        Also includes reputation, resources, flexibility.
        """
        deltas: dict[str, int] = {
            "mp": 0, "sp": 0, "tt": 0,
            "reputation": 0, "resources": 0, "flexibility": 0,
        }
        for eff in self.option_effects(option):
            if eff.get("type") != "modify_stat":
                continue
            stat = eff.get("params", {}).get("stat")
            delta = eff.get("params", {}).get("delta", 0)
            if stat in deltas:
                deltas[stat] += delta
        return deltas

    def has_cross_player_effect(self, option: str) -> bool:
        """Check if option has affect_team or relationship_change effect."""
        for eff in self.option_effects(option):
            if eff.get("type") in ("affect_team", "relationship_change"):
                return True
        return False

    def cross_player_tt_delta(self, option: str) -> int:
        """For affect_team effects modifying tt, return the delta per teammate."""
        for eff in self.option_effects(option):
            if eff.get("type") == "affect_team":
                inner = eff.get("params", {}).get("effect", {})
                if inner.get("type") == "modify_stat" and inner.get("params", {}).get("stat") == "tt":
                    return inner["params"].get("delta", 0)
        return 0

    def has_delayed_effect(self, option: str) -> bool:
        for eff in self.option_effects(option):
            if eff.get("type") == "schedule_event":
                return True
        return False

    def delayed_effect_rounds(self, option: str) -> int | None:
        for eff in self.option_effects(option):
            if eff.get("type") == "schedule_event":
                return eff.get("params", {}).get("trigger_after_rounds")
        return None


def load_all_cards() -> list[Card]:
    """Load every card JSON from database/cards/<level>_<category>/*.json."""
    cards: list[Card] = []
    if not os.path.isdir(CARDS_DIR):
        raise FileNotFoundError(f"Cards directory not found: {CARDS_DIR}")

    for level_dir in os.listdir(CARDS_DIR):
        full_path = os.path.join(CARDS_DIR, level_dir)
        if not os.path.isdir(full_path):
            continue

        # level_dir like "basecamp_mindset" or "basecamp" (parent)
        # We expect subdirs: basecamp/{basecamp_mindset, basecamp_skillset}
        # but the actual JSONs live in basecamp_mindset/, camp_mindset/, etc.
        # Handle both layouts.
        for fname in sorted(os.listdir(full_path)):
            if not fname.endswith(".json"):
                continue
            fpath = os.path.join(full_path, fname)
            with open(fpath, "r", encoding="utf-8") as f:
                raw = json.load(f)

            level = raw.get("level", "")
            category = raw.get("category", "")
            if not level or not category:
                # Infer from path
                if "_" in level_dir:
                    level, category = level_dir.split("_", 1)
                else:
                    continue

            card = Card(
                id=raw["id"],
                level=level,
                category=category,
                type=raw.get("type", "dilemma"),
                situation=raw.get("narrative", {}).get("situation", ""),
                hidden_reveal=raw.get("narrative", {}).get("hidden_reveal"),
                has_hidden_info=raw.get("hidden_info", {}).get("enabled", False),
                hidden_info_reveal_timing=raw.get("hidden_info", {}).get("reveal_timing"),
                choices=raw.get("choices", {}),
                raw=raw,
            )
            cards.append(card)

    return cards


def group_cards_by_level_category(cards: list[Card]) -> dict[str, dict[str, list[Card]]]:
    """Group cards as: {level: {category: [card, ...]}}."""
    grouped: dict[str, dict[str, list[Card]]] = {}
    for c in cards:
        grouped.setdefault(c.level, {}).setdefault(c.category, []).append(c)
    return grouped


def card_index_by_id(cards: list[Card]) -> dict[str, Card]:
    return {c.id: c for c in cards}


if __name__ == "__main__":
    cards = load_all_cards()
    grouped = group_cards_by_level_category(cards)
    print(f"Total cards loaded: {len(cards)}")
    for level in ("basecamp", "camp", "summit"):
        for cat in ("mindset", "skillset"):
            n = len(grouped.get(level, {}).get(cat, []))
            print(f"  {level}/{cat}: {n}")
    print(f"\nLRA-tagged cards (any option): "
          f"{sum(1 for c in cards if c.all_lra_items())}")
    print(f"Hidden-info cards: "
          f"{sum(1 for c in cards if c.has_hidden_info)}")
    print(f"Krisis cards: {sum(1 for c in cards if c.is_krisis())}")
