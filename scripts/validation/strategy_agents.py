"""
The Summit — strategy agents.

Each agent has a `decide(player, card) -> 'A' | 'B'` method.
Strategies are designed to span the spectrum of possible playstyles
so that validation can detect whether any single strategy dominates.

Strategy registry:
  random           — pure uniform random
  greedy_score     — maximize (mp + sp + tt) on the option
  greedy_tt        — maximize tt (collaboration-first)
  greedy_mp        — maximize mp (mindset-first)
  greedy_sp        — maximize sp (skillset-first)
  altruist         — prefer options with cross-player TT gain even at self cost
  individualist    — prefer options with high mp/sp even at tt cost
  risk_seeker      — prefer option with higher variance in deltas
  risk_averse      — prefer option with lower variance / safer (avoid tt loss)
  balanced         — weighted: 0.4 mp + 0.4 sp + 0.2 tt
  diversity_seeker — prefer option with more behavior_tags dims
  adaptive         — level-aware: greedy@basecamp, balanced@camp, altruist@summit
  proving_seeker   — prefer option with more LRA 'proving' tags
  disproving_seeker— prefer option with more LRA 'disproving' tags
"""
from __future__ import annotations
import random
from typing import Callable

from .cards_loader import Card
from .game_state import Player


StrategyFn = Callable[[Player, Card, random.Random], str]


# ── Helpers ──────────────────────────────────────────────────

def _score_for(option: str, card: Card) -> tuple[int, int, int]:
    """Return (mp, sp, tt) deltas for an option."""
    d = card.stat_deltas(option)
    return d["mp"], d["sp"], d["tt"]


def _total_stats(option: str, card: Card) -> int:
    mp, sp, tt = _score_for(option, card)
    return mp + sp + tt


def _weighted(option: str, card: Card,
              w_mp: float, w_sp: float, w_tt: float) -> float:
    mp, sp, tt = _score_for(option, card)
    return w_mp * mp + w_sp * sp + w_tt * tt


def _behavior_dim_count(option: str, card: Card) -> int:
    return len(card.option_behavior_tags(option))


def _lra_proving_count(option: str, card: Card) -> int:
    return sum(1 for sig in card.option_lra_tags(option).values()
               if sig == "proving")


def _lra_disproving_count(option: str, card: Card) -> int:
    return sum(1 for sig in card.option_lra_tags(option).values()
               if sig == "disproving")


def _has_cross_player_tt(option: str, card: Card) -> bool:
    return card.cross_player_tt_delta(option) > 0


def _variance(option: str, card: Card) -> float:
    mp, sp, tt = _score_for(option, card)
    mean = (mp + sp + tt) / 3
    return ((mp - mean) ** 2 + (sp - mean) ** 2 + (tt - mean) ** 2) ** 0.5


def _pick_best(card: Card, scorer: Callable[[str], float],
               prefer_a_on_tie: bool = True, rng: random.Random | None = None) -> str:
    a_score = scorer("A")
    b_score = scorer("B")
    if a_score == b_score:
        if rng is not None:
            return rng.choice(["A", "B"])
        return "A" if prefer_a_on_tie else "B"
    return "A" if a_score > b_score else "B"


# ── Strategy implementations ────────────────────────────────

def strategy_random(player: Player, card: Card, rng: random.Random) -> str:
    return rng.choice(["A", "B"])


def strategy_greedy_score(player: Player, card: Card, rng: random.Random) -> str:
    return _pick_best(card, lambda o: _total_stats(o, card), rng=rng)


def strategy_greedy_tt(player: Player, card: Card, rng: random.Random) -> str:
    return _pick_best(card, lambda o: card.stat_deltas(o)["tt"], rng=rng)


def strategy_greedy_mp(player: Player, card: Card, rng: random.Random) -> str:
    return _pick_best(card, lambda o: card.stat_deltas(o)["mp"], rng=rng)


def strategy_greedy_sp(player: Player, card: Card, rng: random.Random) -> str:
    return _pick_best(card, lambda o: card.stat_deltas(o)["sp"], rng=rng)


def strategy_altruist(player: Player, card: Card, rng: random.Random) -> str:
    """Prefer options that give TT to teammates even at self-cost."""
    def score(o: str) -> float:
        tt_self = card.stat_deltas(o)["tt"]
        cross_tt = card.cross_player_tt_delta(o)
        return cross_tt * 2.0 + tt_self * 0.5  # team-benefit weighted higher
    return _pick_best(card, score, rng=rng)


def strategy_individualist(player: Player, card: Card, rng: random.Random) -> str:
    """Max mp+sp, ignore tt (may even accept tt loss)."""
    def score(o: str) -> float:
        mp, sp, tt = _score_for(o, card)
        return mp * 1.5 + sp * 1.5 + tt * (-0.5)  # actively discount tt
    return _pick_best(card, score, rng=rng)


def strategy_risk_seeker(player: Player, card: Card, rng: random.Random) -> str:
    return _pick_best(card, lambda o: _variance(o, card), rng=rng)


def strategy_risk_averse(player: Player, card: Card, rng: random.Random) -> str:
    # Prefer lower variance AND avoid tt loss
    def score(o: str) -> float:
        v = -_variance(o, card)  # lower variance = higher score
        tt = card.stat_deltas(o)["tt"]
        return v + (0.5 if tt >= 0 else -1.0)
    return _pick_best(card, score, rng=rng)


def strategy_balanced(player: Player, card: Card, rng: random.Random) -> str:
    return _pick_best(card,
                      lambda o: _weighted(o, card, 0.4, 0.4, 0.2),
                      rng=rng)


def strategy_diversity_seeker(player: Player, card: Card, rng: random.Random) -> str:
    """Pick option that adds the most NEW behavior dimensions for the player."""
    def score(o: str) -> float:
        tags = card.option_behavior_tags(o)
        new_dims = sum(1 for d in tags if d not in player.behavior_dims_demonstrated)
        return new_dims + 0.1 * _total_stats(o, card)
    return _pick_best(card, score, rng=rng)


def strategy_adaptive(player: Player, card: Card, rng: random.Random) -> str:
    """Switch strategy based on current level."""
    if player.current_level == "basecamp":
        return strategy_greedy_score(player, card, rng)
    if player.current_level == "camp":
        return strategy_balanced(player, card, rng)
    return strategy_altruist(player, card, rng)


def strategy_proving_seeker(player: Player, card: Card, rng: random.Random) -> str:
    """Always pick option with more 'proving' LRA tags (maximize positive evidence)."""
    def score(o: str) -> float:
        return _lra_proving_count(o, card) + 0.01 * _total_stats(o, card)
    return _pick_best(card, score, rng=rng)


def strategy_disproving_seeker(player: Player, card: Card, rng: random.Random) -> str:
    """Always pick option with more 'disproving' LRA tags (negative evidence)."""
    def score(o: str) -> float:
        return _lra_disproving_count(o, card) + 0.01 * _total_stats(o, card)
    return _pick_best(card, score, rng=rng)


# ── Registry ────────────────────────────────────────────────

STRATEGIES: dict[str, StrategyFn] = {
    "random":              strategy_random,
    "greedy_score":        strategy_greedy_score,
    "greedy_tt":           strategy_greedy_tt,
    "greedy_mp":           strategy_greedy_mp,
    "greedy_sp":           strategy_greedy_sp,
    "altruist":            strategy_altruist,
    "individualist":       strategy_individualist,
    "risk_seeker":         strategy_risk_seeker,
    "risk_averse":         strategy_risk_averse,
    "balanced":            strategy_balanced,
    "diversity_seeker":    strategy_diversity_seeker,
    "adaptive":            strategy_adaptive,
    "proving_seeker":      strategy_proving_seeker,
    "disproving_seeker":   strategy_disproving_seeker,
}


def get_strategy(name: str) -> StrategyFn:
    if name not in STRATEGIES:
        raise ValueError(f"Unknown strategy: {name}. Available: {list(STRATEGIES.keys())}")
    return STRATEGIES[name]
