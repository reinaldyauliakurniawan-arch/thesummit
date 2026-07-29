"""
The Summit — core game simulator.

Runs a full game from setup → finish. Mirrors GameService::processTurn +
applyCardEffects + checkRopeBridge + checkFinalWin + finishGame.

Each game:
  1. Create N players (3-6) with assigned strategy profiles
  2. Cycle turns: each player draws a card, decides option, applies effects
  3. After each turn: process delayed events, check Rope Bridge progression,
     check Final Round trigger
  4. Final Round: each player takes 1 more turn
  5. finishGame(): calculate scores, assign badges, rank players, run LRA assessment

Output: GameResult dataclass with full per-player evidence + assessment.
"""
from __future__ import annotations
from dataclasses import dataclass, field
from typing import Any
import json
import random

from . import config
from .cards_loader import Card, load_all_cards, group_cards_by_level_category
from .game_state import GameRoom, Player, GameTurn
from .behavior_tracker import BehaviorTracker
from .strategy_agents import get_strategy as get_optimizer_strategy
from .psychological_archetypes import get_archetype as get_psych_strategy


def get_strategy(name: str):
    """Resolve strategy from either optimizer or psychological archetype registry."""
    try:
        return get_optimizer_strategy(name)
    except ValueError:
        return get_psych_strategy(name)


@dataclass
class PlayerResult:
    name: str
    strategy: str
    final_level: str
    mp: int
    sp: int
    tt: int
    reputation: int
    resources: int
    flexibility: int
    promises_kept: int
    promises_broken: int
    final_score: float
    badge: str
    rank: int
    turns_played: int
    behavior_dims: list[str]
    cross_effects_given: int
    # LRA assessment (per-item dict)
    lra_assessment: dict
    # Coverage + summary
    coverage: dict
    opportunity_summary: dict
    # Detailed turn log (compact)
    turn_log: list[dict] = field(default_factory=list)


@dataclass
class GameResult:
    game_id: int
    seed: int
    player_strategies: list[str]
    rounds_played: int
    final_round_triggered: bool
    final_round_trigger_round: int | None
    players: list[PlayerResult]
    # Per-item opportunity counts across all players
    # (for opportunity model validation)
    opportunity_counts: dict[str, list[int]] = field(default_factory=dict)


class Simulator:
    """Runs games. Stateful only via RNG seed."""

    def __init__(self, cards: list[Card] | None = None, seed: int | None = None):
        self.cards = cards or load_all_cards()
        self.cards_by_level_cat = group_cards_by_level_category(self.cards)
        self.rng = random.Random(seed)

    # ── Card drawing ─────────────────────────────────────────

    def draw_card(self, player: Player, turn_number: int) -> Card:
        """Mirror GameService::drawCard — alternate mindset/skillset by turn."""
        level = player.current_level
        category = config.category_for_turn(turn_number)
        pool = self.cards_by_level_cat.get(level, {}).get(category, [])
        if not pool:
            # Fallback: try the other category at the same level
            other_cat = "skillset" if category == "mindset" else "mindset"
            pool = self.cards_by_level_cat.get(level, {}).get(other_cat, [])
            if not pool:
                raise RuntimeError(f"No cards for level={level}")

        played_ids = set(player.played_card_ids())
        last_two = set(player.last_two_card_ids())

        # Strategy 1: exclude all played cards
        available = [c for c in pool if c.id not in played_ids]
        if available:
            return self.rng.choice(available)

        # Strategy 2: exclude only last 2
        available = [c for c in pool if c.id not in last_two]
        if available:
            return self.rng.choice(available)

        # Strategy 3: full pool reset
        return self.rng.choice(pool)

    # ── Apply effects ────────────────────────────────────────

    def apply_effects(self, player: Player, card: Card, option: str) -> dict[str, int]:
        """Mirror GameService::applyCardEffects."""
        deltas = card.stat_deltas(option)
        player.mp = max(0, player.mp + deltas["mp"])
        player.sp = max(0, player.sp + deltas["sp"])
        player.tt = max(0, player.tt + deltas["tt"])
        player.reputation += deltas["reputation"]
        player.resources = max(0, player.resources + deltas["resources"])
        player.flexibility += deltas["flexibility"]

        # Track cross-player effects for badge logic
        if card.has_cross_player_effect(option):
            player.cross_effects_given += 1
            # Other players in room receive the TT delta (simplified)
            # — handled by the room loop, not here

        # Schedule delayed events
        if card.has_delayed_effect(option):
            rounds = card.delayed_effect_rounds(option) or 2
            # Look up what the delayed effect does (mp +1 by default)
            for eff in card.option_effects(option):
                if eff.get("type") == "schedule_event":
                    inner = eff.get("params", {}).get("event", {})
                    if inner.get("type") == "modify_stat":
                        stat = inner.get("params", {}).get("stat")
                        delta = inner.get("params", {}).get("delta", 0)
                        # Schedule relative to current turn
                        # (will fire after `rounds` rounds — for solo, that's N more turns)
                        # We schedule per-player via room.delayed_events list
                        # Store as (target_player_name, stat, delta, fire_after_round)
                        # But for simplicity, fire after the same number of additional turns
                        pass  # Delayed events handled in process_pending_events

        return deltas

    def process_pending_events(self, room: GameRoom, player: Player) -> list[dict]:
        """Process any delayed events scheduled to fire this turn for the player.

        Simplified: each delayed event has a 'fire_after_turns' counter
        that decrements each turn; when 0, the effect applies.
        """
        fired = []
        for ev in player.__dict__.get("_pending_delayed", []):
            ev["fire_after_turns"] -= 1
            if ev["fire_after_turns"] <= 0:
                stat = ev["stat"]
                delta = ev["delta"]
                if stat == "mp":
                    player.mp = max(0, player.mp + delta)
                elif stat == "sp":
                    player.sp = max(0, player.sp + delta)
                elif stat == "tt":
                    player.tt = max(0, player.tt + delta)
                fired.append({"stat": stat, "delta": delta})
        player.__dict__.setdefault("_pending_delayed",
                                    [ev for ev in player.__dict__.get("_pending_delayed", [])
                                     if ev["fire_after_turns"] > 0])
        return fired

    def apply_cross_player_effects(self, room: GameRoom, player: Player,
                                   card: Card, option: str) -> list[dict]:
        """Apply affect_team / relationship_change effects to other players.

        Simplified: only TT cross-player effect is applied (most common).
        Reputation is given to source player only (game design).
        """
        applied = []
        cross_tt = card.cross_player_tt_delta(option)
        if cross_tt != 0:
            for other in room.players:
                if other is player:
                    continue
                other.tt = max(0, other.tt + cross_tt)
                other.cross_effects_received += 1
                applied.append({"target": other.name, "tt_delta": cross_tt})
        return applied

    def roll_risk_die(self) -> tuple[int, int, str | None]:
        """Returns (roll, tt_delta, dysfunction)."""
        roll = self.rng.randint(1, 6)
        if roll in config.RISK_DIE["dysfunction_range"]:
            return roll, config.RISK_DIE["dysfunction_tt_penalty"], \
                   self.rng.choice(config.DYSFUNCTIONS)
        if roll in config.RISK_DIE["bonus_range"]:
            return roll, config.RISK_DIE["bonus_tt_reward"], None
        return roll, 0, None

    # ── Single turn ──────────────────────────────────────────

    def process_turn(self, room: GameRoom, player: Player) -> GameTurn:
        """Process one player's turn — mirror GameService::processTurn."""
        turn_number = len(player.turns) + 1
        card = self.draw_card(player, turn_number)

        # Decide using strategy
        strategy_fn = get_strategy(player.strategy)
        option = strategy_fn(player, card, self.rng)

        level_before = player.current_level
        effects = self.apply_effects(player, card, option)

        # Process delayed events (decrement counters, fire if due)
        # — handled per turn (each turn = 1 round for this player)
        # Apply cross-player effects
        cross_effects = self.apply_cross_player_effects(room, player, card, option)

        # Roll risk die for krisis cards (we have no krisis cards in the pool — skip)
        risk_die = None
        dysfunction = None
        if card.is_krisis():
            risk_die, tt_delta, dysfunction = self.roll_risk_die()
            player.tt = max(0, player.tt + tt_delta)
            effects["tt"] += tt_delta

        # Schedule delayed events for this player
        for eff in card.option_effects(option):
            if eff.get("type") == "schedule_event":
                inner = eff.get("params", {}).get("event", {})
                rounds = eff.get("params", {}).get("trigger_after_rounds", 2)
                if inner.get("type") == "modify_stat":
                    stat = inner.get("params", {}).get("stat")
                    delta = inner.get("params", {}).get("delta", 0)
                    player.__dict__.setdefault("_pending_delayed", []).append({
                        "stat": stat,
                        "delta": delta,
                        "fire_after_turns": rounds,
                    })

        # Process pending delayed events (counters tick down each turn)
        self.process_pending_events(room, player)

        # Track evidence (LRA + opportunity + missed opportunity)
        room.behavior_tracker.track_turn(player, turn_number, card, option)

        # Build the turn record
        turn = GameTurn(
            turn_number=turn_number,
            card=card,
            chosen_option=option,
            effects=effects,
            risk_die=risk_die,
            dysfunction=dysfunction,
            level_before=level_before,
            level_after=player.current_level,
        )
        player.turns.append(turn)

        # Check Rope Bridge progression
        # (only attempt if current level is not summit)
        if player.current_level == "basecamp" and player.meets_threshold("to_camp"):
            player.current_level = "camp"
            turn.level_after = "camp"
            turn.rope_bridge_attempted = True
            turn.rope_bridge_success = True
        elif player.current_level == "camp" and player.meets_threshold("to_summit"):
            player.current_level = "summit"
            turn.level_after = "summit"
            turn.rope_bridge_attempted = True
            turn.rope_bridge_success = True

        # Check final win trigger
        if (room.status == "in_progress"
                and player.current_level == "summit"
                and player.meets_threshold("final_win")):
            room.status = "final_round"
            room.final_round_started_at_round = room.round_number
            turn.triggered_final = True

        # Track behavior dimensions demonstrated
        tags = card.option_behavior_tags(option)
        for dim in tags:
            if abs(tags[dim]) >= 1:
                player.behavior_dims_demonstrated.add(dim)

        return turn

    # ── Full game ────────────────────────────────────────────

    def simulate_game(self, game_id: int, player_strategies: list[str],
                      seed: int | None = None) -> GameResult:
        """Simulate one full game.

        Args:
            game_id: Game ID for tracking
            player_strategies: List of strategy profile names, one per player
            seed: RNG seed (if None, derives from game_id)
        """
        if seed is not None:
            self.rng = random.Random(seed)

        if not (config.MIN_PLAYERS <= len(player_strategies) <= config.MAX_PLAYERS):
            raise ValueError(f"Need {config.MIN_PLAYERS}-{config.MAX_PLAYERS} players")

        room = GameRoom(game_id=game_id, rng=self.rng)
        for i, strat in enumerate(player_strategies):
            room.players.append(Player(
                name=f"P{i+1}",
                strategy=strat,
            ))

        # Main game loop
        max_total_turns = config.MAX_TURNS_PER_PLAYER * len(room.players) + 10
        total_turns_taken = 0

        while room.status == "in_progress" and total_turns_taken < max_total_turns:
            current = room.current_player()
            # Skip players who have hit turn cap
            if len(current.turns) >= config.MAX_TURNS_PER_PLAYER:
                room.advance_player()
                total_turns_taken += 1
                # If all players capped, end the game
                if all(len(p.turns) >= config.MAX_TURNS_PER_PLAYER for p in room.players):
                    room.status = "finished"
                    break
                continue

            self.process_turn(room, current)
            total_turns_taken += 1
            room.advance_player()

        # Final round: each player takes 1 more turn
        if room.status == "final_round":
            for i, player in enumerate(room.players):
                if len(player.turns) < config.MAX_TURNS_PER_PLAYER:
                    self.process_turn(room, player)
            room.status = "finished"

        # If we hit the turn cap without anyone summiting, end anyway
        room.status = "finished"

        # Calculate scores + assign badges
        for player in room.players:
            player.final_level = player.current_level
            player.final_score = player.calculate_score()

        # Sort by badge priority → score → tt
        def sort_key(p: Player) -> tuple:
            badge = p.assign_badge(room.players)
            p.badge = badge
            return (-config.BADGE_PRIORITY[badge],
                    -p.final_score,
                    -p.tt)

        sorted_players = sorted(room.players, key=sort_key)
        for rank, p in enumerate(sorted_players, 1):
            p.rank = rank

        # Build per-player results with LRA assessment
        player_results: list[PlayerResult] = []
        for player in sorted_players:
            assessment = room.behavior_tracker.assess(player)
            coverage = assessment.pop("_coverage", {})
            summary = assessment.pop("_summary", {})

            turn_log = []
            for t in player.turns:
                turn_log.append({
                    "turn": t.turn_number,
                    "card": t.card.id,
                    "option": t.chosen_option,
                    "effects": t.effects,
                    "level_before": t.level_before,
                    "level_after": t.level_after,
                    "rope_bridge_success": t.rope_bridge_success,
                    "triggered_final": t.triggered_final,
                })

            player_results.append(PlayerResult(
                name=player.name,
                strategy=player.strategy,
                final_level=player.final_level,
                mp=player.mp,
                sp=player.sp,
                tt=player.tt,
                reputation=player.reputation,
                resources=player.resources,
                flexibility=player.flexibility,
                promises_kept=player.promises_kept,
                promises_broken=player.promises_broken,
                final_score=player.final_score,
                badge=player.badge,
                rank=player.rank,
                turns_played=len(player.turns),
                behavior_dims=sorted(player.behavior_dims_demonstrated),
                cross_effects_given=player.cross_effects_given,
                lra_assessment=assessment,
                coverage=coverage,
                opportunity_summary=summary,
                turn_log=turn_log,
            ))

        # Aggregate opportunity counts per item across all players
        opp_counts: dict[str, list[int]] = {}
        for item_code in config.LRA_ITEMS:
            opp_counts[item_code] = []
            for p in room.players:
                opp = sum(1 for e in p.evidence
                          if e.lra_item == item_code and e.source == "opportunity")
                opp_counts[item_code].append(opp)

        return GameResult(
            game_id=game_id,
            seed=seed or 0,
            player_strategies=player_strategies,
            rounds_played=room.round_number,
            final_round_triggered=room.final_round_started_at_round is not None,
            final_round_trigger_round=room.final_round_started_at_round,
            players=player_results,
            opportunity_counts=opp_counts,
        )


def serialize_game_result(result: GameResult) -> dict:
    """Convert GameResult to JSON-serializable dict for reporting."""
    return {
        "game_id": result.game_id,
        "seed": result.seed,
        "player_strategies": result.player_strategies,
        "rounds_played": result.rounds_played,
        "final_round_triggered": result.final_round_triggered,
        "final_round_trigger_round": result.final_round_trigger_round,
        "players": [
            {
                "name": p.name,
                "strategy": p.strategy,
                "final_level": p.final_level,
                "mp": p.mp, "sp": p.sp, "tt": p.tt,
                "reputation": p.reputation, "resources": p.resources,
                "flexibility": p.flexibility,
                "promises_kept": p.promises_kept,
                "promises_broken": p.promises_broken,
                "final_score": p.final_score,
                "badge": p.badge,
                "rank": p.rank,
                "turns_played": p.turns_played,
                "behavior_dims": p.behavior_dims,
                "cross_effects_given": p.cross_effects_given,
                "lra_assessment": p.lra_assessment,
                "coverage": p.coverage,
                "opportunity_summary": p.opportunity_summary,
                "turn_log": p.turn_log,
            }
            for p in result.players
        ],
    }
