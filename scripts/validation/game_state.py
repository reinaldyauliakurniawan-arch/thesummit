"""
The Summit — game state (Python port).

Holds per-player state, tracks turns, applies card effects, evaluates
Rope Bridge thresholds, scores final results, and assigns badges.
Mirrors app/Models/GamePlayer.php + app/Services/GameService.php logic
for offline simulation.
"""
from __future__ import annotations
from dataclasses import dataclass, field
from typing import Any
import random

from . import config
from .cards_loader import Card
from .behavior_tracker import BehaviorTracker, EvidenceRecord


@dataclass
class GameTurn:
    turn_number: int
    card: Card
    chosen_option: str
    effects: dict[str, int]
    risk_die: int | None = None
    dysfunction: str | None = None
    level_before: str = ""
    level_after: str = ""
    rope_bridge_attempted: bool = False
    rope_bridge_success: bool = False
    triggered_final: bool = False
    cross_player_effects: list[dict] = field(default_factory=list)


@dataclass
class Player:
    """Per-player state. Mirrors GamePlayer model + simulator extras."""
    name: str
    strategy: str  # strategy profile name
    current_level: str = "basecamp"
    mp: int = 0
    sp: int = 0
    tt: int = 0
    reputation: int = 0
    resources: int = 0
    flexibility: int = 0
    promises_kept: int = 0
    promises_broken: int = 0
    turns: list[GameTurn] = field(default_factory=list)
    evidence: list[EvidenceRecord] = field(default_factory=list)
    cross_effects_given: int = 0  # number of cross-player effects this player gave
    cross_effects_received: int = 0
    # End-of-game results
    final_level: str = ""
    final_score: float = 0.0
    badge: str = ""
    rank: int = 0
    behavior_dims_demonstrated: set[str] = field(default_factory=set)

    # ── Threshold checks ──
    def meets_threshold(self, key: str) -> bool:
        th = config.THRESHOLDS.get(key)
        if not th:
            return False
        mp_ok = self.mp >= th["mp"]
        sp_ok = self.sp >= th["sp"]
        tt_ok = True if th.get("tt_required") is False else self.tt >= th["tt"]
        return mp_ok and sp_ok and tt_ok

    # ── Card draw history helpers ──
    def played_card_ids(self) -> list[str]:
        return [t.card.id for t in self.turns]

    def last_two_card_ids(self) -> list[str]:
        return [t.card.id for t in self.turns[-2:]]

    # ── Scoring ──
    def calculate_score(self) -> float:
        """Mirror GamePlayer::calculateScore."""
        level_value = config.LEVEL_VALUE.get(self.current_level, 1)
        base = level_value * 10
        tt_bonus = min(self.tt * config.SCORING["tt_weight"],
                       config.SCORING["tt_bonus_cap"])
        rep_bonus = max(-5, min(5, self.reputation))
        diversity_bonus = self._leadership_diversity_bonus()
        selfish_tax = 0
        if self.promises_broken > self.promises_kept and self.promises_broken > 0:
            selfish_tax = min((self.promises_broken - self.promises_kept)
                              * config.SCORING["selfish_tax_per"],
                              config.SCORING["selfish_tax_cap"])
        return round(base + tt_bonus + rep_bonus + diversity_bonus - selfish_tax, 1)

    def _leadership_diversity_bonus(self) -> int:
        """Mirror GamePlayer::calculateLeadershipDiversityBonus.

        Counts distinct behavior dimensions demonstrated with magnitude >= 1
        across the player's turns. Returns 0-5.
        """
        dims: set[str] = set()
        for turn in self.turns:
            tags = turn.card.option_behavior_tags(turn.chosen_option)
            for dim, val in tags.items():
                if abs(val) >= 1:
                    dims.add(dim)
        self.behavior_dims_demonstrated = dims
        return min(max(len(dims) - 1, 0), 5)

    # ── Badge qualifications ──
    def qualifies_as_carrier(self) -> bool:
        return (self.current_level == "summit"
                and self.tt >= 8
                and self.reputation >= 0
                and self.promises_kept >= self.promises_broken)

    def qualifies_as_catalyst(self, all_players: list["Player"]) -> bool:
        if self.current_level == "summit":
            return False
        if self.cross_effects_given == 0:
            return False
        # Highest TT among non-summit players (within tolerance of 1)
        non_summit = [p for p in all_players if p.current_level != "summit"]
        if not non_summit:
            return False
        max_tt = max(p.tt for p in non_summit)
        return self.tt >= (max_tt - 1)

    def qualifies_as_strategist(self) -> bool:
        return self._leadership_diversity_bonus() >= 4

    def assign_badge(self, all_players: list["Player"]) -> str:
        """Mirror GameService::assignBadge cascade."""
        if self.qualifies_as_carrier():
            return "the_carrier"
        if self.qualifies_as_catalyst(all_players):
            return "the_catalyst"
        if self.qualifies_as_strategist():
            return "the_strategist"
        if self.current_level == "summit":
            return "solo_peak"
        return "none"


@dataclass
class GameRoom:
    """Per-game state. Holds players + sim metadata."""
    game_id: int
    players: list[Player] = field(default_factory=list)
    current_player_idx: int = 0
    round_number: int = 0  # increments after each player takes a turn
    status: str = "in_progress"  # 'in_progress' | 'final_round' | 'finished'
    final_round_started_at_round: int | None = None
    final_round_turns_taken: dict[int, int] = field(default_factory=dict)  # player_idx → count
    max_rounds: int = 30  # safety cap
    behavior_tracker: BehaviorTracker = field(default_factory=BehaviorTracker)
    rng: random.Random = field(default_factory=random.Random)
    delayed_events: list[dict] = field(default_factory=list)  # pending delayed consequences

    def current_player(self) -> Player:
        return self.players[self.current_player_idx]

    def advance_player(self) -> None:
        self.current_player_idx = (self.current_player_idx + 1) % len(self.players)
        if self.current_player_idx == 0:
            self.round_number += 1
