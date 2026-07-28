"""
The Summit — Facilitator Playtesting Tool (CLI).

Allows a facilitator to run a guided playtest session where they make
choices for a simulated player. All decisions are logged with full
evidence tracking for post-session analysis.

Usage:
    python3 scripts/validation/facilitator_cli.py [--players N] [--output path]

Features:
  - Displays card narrative + two options with full effect breakdown
  - Facilitator picks A or B
  - Shows running stats (MP, SP, TT, reputation, resources, flexibility)
  - Tracks LRA evidence + opportunity + missed opportunities
  - Shows real-time LRA assessment after each turn
  - At game end: prints full assessment report + saves to JSON
  - Supports multiple players (facilitator plays all of them sequentially)

Output:
  - Console: live play-by-play
  - JSON file: full evidence log + LRA assessment for post-analysis
"""
from __future__ import annotations
import argparse
import json
import os
import sys
import time
from datetime import datetime

sys.path.insert(0, "/home/z/my-project/thesummit/scripts")

from validation.simulator import Simulator
from validation.game_state import GameRoom, Player, GameTurn
from validation import config

REPORTS_DIR = "/home/z/my-project/thesummit/scripts/validation/reports"

BOLD = "\033[1m"
DIM = "\033[2m"
GREEN = "\033[92m"
YELLOW = "\033[93m"
RED = "\033[91m"
CYAN = "\033[96m"
RESET = "\033[0m"


def print_banner():
    print(f"""
{BOLD}╔══════════════════════════════════════════════════╗
║         THE SUMMIT — Facilitator Playtest           ║
║   Interactive CLI for guided gameplay validation    ║
╚══════════════════════════════════════════════════╝{RESET}
""")


def print_player_stats(player: Player):
    level_label = config.LEVELS.get(player.current_level, {}).get("label", player.current_level)
    level_color = {"basecamp": CYAN, "camp": YELLOW, "summit": GREEN}.get(player.current_level, "")
    print(f"\n{BOLD}┌─ {player.name} ({player.strategy}) ─────────────────────────────┐{RESET}")
    print(f"  Level:  {level_color}{level_label}{RESET}  "
          f"Turns: {len(player.turns)}")
    print(f"  MP: {BOLD}{player.mp:>3}{RESET}  SP: {BOLD}{player.sp:>3}{RESET}  "
          f"TT: {BOLD}{player.tt:>3}{RESET}  Rep: {player.reputation:>4}")
    print(f"  Resources: {player.resources:>3}  Flexibility: {player.flexibility:>3}")
    print(f"  Promises: {player.promises_kept} kept, {player.promises_broken} broken")

    # Show Rope Bridge thresholds
    if player.current_level == "basecamp":
        th = config.THRESHOLDS["to_camp"]
        mp_ok = "✓" if player.mp >= th["mp"] else "✗"
        sp_ok = "✓" if player.sp >= th["sp"] else "✗"
        print(f"  Rope Bridge (→ Camp): MP {mp_ok}({player.mp}/{th['mp']}) "
              f"SP {sp_ok}({player.sp}/{th['sp']})")
    elif player.current_level == "camp":
        th = config.THRESHOLDS["to_summit"]
        mp_ok = "✓" if player.mp >= th["mp"] else "✗"
        sp_ok = "✓" if player.sp >= th["sp"] else "✗"
        tt_ok = "✓" if player.tt >= th["tt"] else "✗"
        print(f"  Rope Bridge (→ Summit): MP {mp_ok}({player.mp}/{th['mp']}) "
              f"SP {sp_ok}({player.sp}/{th['sp']}) TT {tt_ok}({player.tt}/{th['tt']})")
    elif player.current_level == "summit":
        th = config.THRESHOLDS["final_win"]
        mp_ok = "✓" if player.mp >= th["mp"] else "✗"
        sp_ok = "✓" if player.sp >= th["sp"] else "✗"
        tt_ok = "✓" if player.tt >= th["tt"] else "✗"
        print(f"  Final Win: MP {mp_ok}({player.mp}/{th['mp']}) "
              f"SP {sp_ok}({player.sp}/{th['sp']}) TT {tt_ok}({player.tt}/{th['tt']})")

    print(f"{BOLD}└──────────────────────────────────────────────────────┘{RESET}")


def print_card(card, turn_number: int, player: Player):
    category = config.category_for_turn(turn_number)
    print(f"\n{BOLD}═══ Turn {turn_number} | {card.level.upper()} / {category.upper()} | "
          f"{card.type.upper()} ═══{RESET}")
    print(f"\n  {DIM}📋 {card.id}{RESET}")
    print(f"\n  {card.situation}")

    if card.has_hidden_info and card.hidden_reveal:
        print(f"\n  {YELLOW}🔍 Hidden Info: {card.hidden_reveal}{RESET}")

    print(f"\n{BOLD}  Option A:{RESET} {card.option_text('A')}")
    eff_a = card.stat_deltas("A")
    lra_a = card.option_lra_tags("A")
    print(f"    {DIM}MP {eff_a['mp']:+d}  SP {eff_a['sp']:+d}  "
          f"TT {eff_a['tt']:+d}  Rep {eff_a['reputation']:+d}{RESET}")
    if lra_a:
        tags_str = ", ".join(f"{k}({v})" for k, v in lra_a.items())
        print(f"    {CYAN}LRA: {tags_str}{RESET}")

    print(f"\n{BOLD}  Option B:{RESET} {card.option_text('B')}")
    eff_b = card.stat_deltas("B")
    lra_b = card.option_lra_tags("B")
    print(f"    {DIM}MP {eff_b['mp']:+d}  SP {eff_b['sp']:+d}  "
          f"TT {eff_b['tt']:+d}  Rep {eff_b['reputation']:+d}{RESET}")
    if lra_b:
        tags_str = ", ".join(f"{k}({v})" for k, v in lra_b.items())
        print(f"    {CYAN}LRA: {tags_str}{RESET}")

    # Show cross-player effects
    for opt in ("A", "B"):
        cross_tt = card.cross_player_tt_delta(opt)
        if cross_tt != 0:
            print(f"    {YELLOW}Option {opt}: Teammates get TT {cross_tt:+d}{RESET}")

    print()


def print_lra_update(player: Player, card):
    """Show new LRA evidence from the latest turn."""
    latest = player.evidence[-1] if player.evidence else None
    if not latest:
        return

    # Find all evidence from the latest turn
    turn = len(player.turns)
    turn_evidence = [e for e in player.evidence if e.turn == turn]
    lra_evidence = [e for e in turn_evidence if e.source in ("lra_tag", "missed_opportunity")]
    opportunity_evidence = [e for e in turn_evidence if e.source == "opportunity"]

    if opportunity_evidence:
        items = [e.lra_item for e in opportunity_evidence]
        print(f"  {DIM}Opportunities presented: {', '.join(items)}{RESET}")

    for e in lra_evidence:
        if e.source == "lra_tag":
            marker = f"{GREEN}✓{RESET}" if e.lra_signal == "proving" else f"{RED}✗{RESET}"
            label = config.LRA_ITEMS.get(e.lra_item, {}).get("label", e.lra_item)
            print(f"  {marker} {e.lra_item} ({label}): {e.lra_signal}")
        elif e.source == "missed_opportunity":
            marker = f"{YELLOW}⊘{RESET}"
            label = config.LRA_ITEMS.get(e.lra_item, {}).get("label", e.lra_item)
            print(f"  {marker} {e.lra_item} ({label}): {e.lra_signal}")


def print_post_game(room: GameRoom):
    """Print full post-game LRA assessment."""
    print(f"\n{BOLD}{'═' * 60}{RESET}")
    print(f"{BOLD}  GAME OVER — Assessment Report{RESET}")
    print(f"{BOLD}{'═' * 60}{RESET}")

    for player in sorted(room.players, key=lambda p: p.rank):
        assessment = room.behavior_tracker.assess(player)
        coverage = assessment.pop("_coverage", {})
        summary = assessment.pop("_summary", {})

        print(f"\n{BOLD}{'─' * 60}{RESET}")
        print(f"{BOLD}{player.name} ({player.strategy}){RESET}")
        print(f"  Rank: {player.rank}  |  Level: {player.final_level}  |  "
              f"Score: {player.final_score}  |  Badge: {player.badge}")
        print(f"  MP: {player.mp}  SP: {player.sp}  TT: {player.tt}  "
              f"Rep: {player.reputation}  Turns: {len(player.turns)}")

        print(f"\n  LRA Assessment Summary:")
        print(f"    Items assessable: {summary['items_assessable']}/{summary['total_items']}")
        print(f"    No opportunity: {summary['items_no_opportunity']}")
        print(f"    Insufficient opp: {summary['items_insufficient_opportunity']}")
        print(f"    Limited coverage: {summary['items_limited_coverage']}")

        print(f"\n  Per-Item Results:")
        for code in sorted(config.LRA_ITEMS.keys()):
            item = assessment.get(code, {})
            if not isinstance(item, dict):
                continue
            label = item.get("label", code)
            opp = item.get("opportunities_presented", 0)
            min_opp = item.get("min_opportunities", 2)
            status = item.get("fairness_status", "?")
            score = item.get("suggested_score", "?")
            conf = item.get("confidence", 0)
            proving = item.get("proving_count", 0)
            disproving = item.get("disproving_count", 0)

            if status == "fair":
                marker = f"{GREEN}●{RESET}"
            elif status in ("no_opportunity", "insufficient_opportunity", "insufficient_evidence"):
                marker = f"{YELLOW}○{RESET}"
            else:
                marker = f"{DIM}·{RESET}"

            score_str = f"{score}" if score else "—"
            conf_str = f"{conf:.2f}" if conf > 0 else "—"
            print(f"    {marker} {code}: {label[:35]:<35} "
                  f"opp={opp}/{min_opp} {status:<25} "
                  f"score={score_str} conf={conf_str} "
                  f"(✓{proving} ✗{disproving})")


def save_session(room: GameRoom, output_path: str):
    """Save the full session data to JSON."""
    session_data = {
        "timestamp": datetime.now().isoformat(),
        "players": len(room.players),
        "rounds": room.round_number,
        "final_round_triggered": room.final_round_started_at_round is not None,
        "results": [],
    }
    for player in room.players:
        assessment = room.behavior_tracker.assess(player)
        assessment.pop("_coverage", None)
        assessment.pop("_summary", None)
        session_data["results"].append({
            "name": player.name,
            "strategy": player.strategy,
            "final_level": player.final_level,
            "mp": player.mp, "sp": player.sp, "tt": player.tt,
            "reputation": player.reputation,
            "final_score": player.final_score,
            "badge": player.badge,
            "rank": player.rank,
            "turns_played": len(player.turns),
            "behavior_dims": sorted(player.behavior_dims_demonstrated),
            "lra_assessment": assessment,
            "evidence_log": [
                {
                    "turn": e.turn,
                    "card_id": e.card_id,
                    "option": e.option,
                    "lra_item": e.lra_item,
                    "source": e.source,
                    "lra_signal": e.lra_signal,
                    "context_type": e.context_type,
                    "context_weight": e.context_weight,
                    "description": e.description,
                }
                for e in player.evidence
            ],
            "turn_log": [
                {
                    "turn": t.turn_number,
                    "card": t.card.id,
                    "option": t.chosen_option,
                    "effects": t.effects,
                    "level_before": t.level_before,
                    "level_after": t.level_after,
                    "rope_bridge_success": t.rope_bridge_success,
                }
                for t in player.turns
            ],
        })

    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(session_data, f, indent=2, ensure_ascii=False)
    print(f"\n{GREEN}Session saved to: {output_path}{RESET}")


def main():
    parser = argparse.ArgumentParser(description="The Summit — Facilitator Playtest CLI")
    parser.add_argument("--players", type=int, default=3, help="Number of players (3-6)")
    parser.add_argument("--output", type=str, default=None, help="Output JSON path")
    parser.add_argument("--seed", type=int, default=None, help="RNG seed for reproducibility")
    args = parser.parse_args()

    os.makedirs(REPORTS_DIR, exist_ok=True)

    n_players = max(config.MIN_PLAYERS, min(config.MAX_PLAYERS, args.players))
    output_path = args.output or os.path.join(
        REPORTS_DIR, f"playtest_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json")

    print_banner()
    print(f"Players: {n_players}  |  Output: {output_path}")
    print(f"Type {BOLD}'q'{RESET} to quit at any time.\n")

    sim = Simulator(seed=args.seed)

    # Setup players
    room = GameRoom(game_id=0, rng=sim.rng)
    strategies = ["facilitator"] * n_players
    for i, strat in enumerate(strategies):
        room.players.append(Player(
            name=f"Player {i + 1}",
            strategy=strat,
        ))

    print(f"{BOLD}Game started with {n_players} players.{RESET}")
    print(f"Each player's turn will be presented sequentially.\n")

    # Game loop
    while room.status == "in_progress":
        for idx, player in enumerate(room.players):
            if len(player.turns) >= config.MAX_TURNS_PER_PLAYER:
                continue

            print_player_stats(player)

            turn_number = len(player.turns) + 1
            card = sim.draw_card(player, turn_number)

            print_card(card, turn_number, player)

            # Get facilitator choice
            while True:
                try:
                    choice = input(f"{BOLD}  Choose [A/B]: {RESET}").strip().upper()
                    if choice == "Q":
                        print(f"\n{YELLOW}Session ended by facilitator. Saving partial results...{RESET}")
                        room.status = "finished"
                        save_session(room, output_path)
                        return
                    if choice in ("A", "B"):
                        break
                    print(f"  {RED}Invalid. Enter A, B, or Q.{RESET}")
                except (EOFError, KeyboardInterrupt):
                    print(f"\n{YELLOW}Interrupted. Saving...{RESET}")
                    save_session(room, output_path)
                    return

            # Process turn
            effects = sim.apply_effects(player, card, choice)
            sim.apply_cross_player_effects(room, player, card, choice)

            # Process delayed events
            sim.process_pending_events(room, player)

            # Schedule delayed events
            for eff in card.option_effects(choice):
                if eff.get("type") == "schedule_event":
                    inner = eff.get("params", {}).get("event", {})
                    rounds = eff.get("params", {}).get("trigger_after_rounds", 2)
                    if inner.get("type") == "modify_stat":
                        stat = inner.get("params", {}).get("stat")
                        delta = inner.get("params", {}).get("delta", 0)
                        player.__dict__.setdefault("_pending_delayed", []).append({
                            "stat": stat, "delta": delta, "fire_after_turns": rounds,
                        })

            # Track evidence
            room.behavior_tracker.track_turn(player, turn_number, card, choice)

            # Record turn
            level_before = player.turns[-1].level_after if player.turns else "basecamp"
            # Check rope bridge
            if player.current_level == "basecamp" and player.meets_threshold("to_camp"):
                player.current_level = "camp"
            elif player.current_level == "camp" and player.meets_threshold("to_summit"):
                player.current_level = "summit"

            turn = GameTurn(
                turn_number=turn_number,
                card=card,
                chosen_option=choice,
                effects=effects,
                level_before=level_before,
                level_after=player.current_level,
            )
            player.turns.append(turn)

            # Track behavior dims
            tags = card.option_behavior_tags(choice)
            for dim in tags:
                if abs(tags[dim]) >= 1:
                    player.behavior_dims_demonstrated.add(dim)

            # Show result
            eff = effects
            print(f"  {DIM}Applied: MP {eff.get('mp', 0):+d}  SP {eff.get('sp', 0):+d}  "
                  f"TT {eff.get('tt', 0):+d}  Rep {eff.get('reputation', 0):+d}{RESET}")
            if turn.level_before != turn.level_after:
                print(f"  {GREEN}⬆ LEVEL UP: {turn.level_before} → {turn.level_after}{RESET}")

            # Check final win
            if (room.status == "in_progress"
                    and player.current_level == "summit"
                    and player.meets_threshold("final_win")):
                room.status = "final_round"
                room.final_round_started_at_round = room.round_number
                print(f"  {GREEN}🏆 FINAL ROUND TRIGGERED by {player.name}!{RESET}")

            # Show LRA evidence from this turn
            print_lra_update(player, card)

            time.sleep(0.1)  # Brief pause

        room.round_number += 1

        # Check if all players hit turn cap
        if all(len(p.turns) >= config.MAX_TURNS_PER_PLAYER for p in room.players):
            room.status = "finished"

    # Final round
    if room.status == "final_round":
        print(f"\n{BOLD}{'═' * 60}{RESET}")
        print(f"{BOLD}  FINAL ROUND — Each player gets one more turn{RESET}")
        print(f"{BOLD}{'═' * 60}{RESET}")
        for player in room.players:
            if len(player.turns) >= config.MAX_TURNS_PER_PLAYER:
                continue

            print_player_stats(player)
            turn_number = len(player.turns) + 1
            card = sim.draw_card(player, turn_number)
            print_card(card, turn_number, player)

            while True:
                try:
                    choice = input(f"{BOLD}  Choose [A/B]: {RESET}").strip().upper()
                    if choice in ("A", "B"):
                        break
                except (EOFError, KeyboardInterrupt):
                    choice = "A"

            effects = sim.apply_effects(player, card, choice)
            sim.apply_cross_player_effects(room, player, card, choice)
            sim.process_pending_events(room, player)
            room.behavior_tracker.track_turn(player, turn_number, card, choice)

            level_before = player.current_level
            turn = GameTurn(
                turn_number=turn_number, card=card,
                chosen_option=choice, effects=effects,
                level_before=level_before, level_after=player.current_level,
            )
            player.turns.append(turn)
            tags = card.option_behavior_tags(choice)
            for dim in tags:
                if abs(tags[dim]) >= 1:
                    player.behavior_dims_demonstrated.add(dim)

            print(f"  {DIM}Applied: MP {effects.get('mp', 0):+d}  SP {effects.get('sp', 0):+d}  "
                  f"TT {effects.get('tt', 0):+d}{RESET}")
            print_lra_update(player, card)

    room.status = "finished"

    # Calculate scores + assign badges
    for player in room.players:
        player.final_level = player.current_level
        player.final_score = player.calculate_score()

    def sort_key(p):
        badge = p.assign_badge(room.players)
        p.badge = badge
        return (-config.BADGE_PRIORITY[badge], -p.final_score, -p.tt)

    sorted_players = sorted(room.players, key=sort_key)
    for rank, p in enumerate(sorted_players, 1):
        p.rank = rank

    # Print post-game report
    print_post_game(room)

    # Save session
    save_session(room, output_path)
    print(f"\n{GREEN}Playtest complete.{RESET}")


if __name__ == "__main__":
    main()
