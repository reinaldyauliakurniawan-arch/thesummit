"""
The Summit — main validation runner.

Runs N simulated games with rotating strategy mixes, collects aggregate
statistics, and writes a raw JSON results file for downstream analysis.

Usage:
    python3 scripts/validation/run_validation.py [--games N] [--seed N]

Output:
    scripts/validation/reports/sim_results.json
    scripts/validation/reports/sim_summary.json
"""
from __future__ import annotations
import argparse
import json
import os
import sys
import time
from collections import defaultdict, Counter
from statistics import mean, median, stdev

sys.path.insert(0, "/home/z/my-project/thesummit/scripts")

from validation.simulator import Simulator, serialize_game_result
from validation import config
from validation.strategy_agents import STRATEGIES

REPORTS_DIR = "/home/z/my-project/thesummit/scripts/validation/reports"


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--games", type=int, default=1000,
                        help="Number of games to simulate (default: 1000)")
    parser.add_argument("--seed", type=int, default=42,
                        help="Base RNG seed (each game uses seed+i)")
    parser.add_argument("--players", type=int, default=3,
                        help="Players per game (3-6)")
    parser.add_argument("--out", type=str, default=None,
                        help="Output prefix (default: sim)")
    args = parser.parse_args()

    os.makedirs(REPORTS_DIR, exist_ok=True)

    n_games = args.games
    n_players = args.players
    base_seed = args.seed

    # Strategy mixes — we want each strategy to be tested both
    # in homogeneous games (all same strategy) AND in mixed games
    strategy_names = list(STRATEGIES.keys())

    # Build a list of (game_id, player_strategies) for each game
    game_plans: list[tuple[int, list[str]]] = []

    # Phase 1: homogeneous games — every strategy plays itself (5 games each)
    for strat in strategy_names:
        for i in range(5):
            game_plans.append((len(game_plans), [strat] * n_players))

    # Phase 2: mixed strategy games — random strategies per player
    rng = __import__("random").Random(base_seed)
    while len(game_plans) < n_games:
        strategies = [rng.choice(strategy_names) for _ in range(n_players)]
        game_plans.append((len(game_plans), strategies))

    # Limit to requested number of games
    game_plans = game_plans[:n_games]

    print(f"Running {len(game_plans)} simulated games "
          f"with {n_players} players each...")
    print(f"Strategies in pool: {len(strategy_names)}")
    print()

    # Initialize simulator
    sim = Simulator(seed=base_seed)

    # Aggregate counters
    all_game_results = []
    strategy_stats = defaultdict(lambda: {
        "games_played": 0,
        "wins": 0,                     # rank 1
        "badges": Counter(),
        "final_levels": Counter(),
        "scores": [],
        "tt_values": [],
        "mp_values": [],
        "sp_values": [],
        "turns_played": [],
        "cross_effects_given": [],
    })
    item_opportunity_counts = defaultdict(list)  # item_code → list of opp counts per player-game
    item_assessment_counts = defaultdict(lambda: Counter())  # item → Counter of fairness_status
    item_score_distribution = defaultdict(lambda: Counter())  # item → Counter of suggested_score
    item_confidence_values = defaultdict(list)  # item → list of confidence values (only when defensible)
    item_evidence_counts = defaultdict(list)
    item_missed_proving_counts = defaultdict(list)
    item_missed_disproving_counts = defaultdict(list)

    # Per-strategy per-item opportunity counts (for coverage equity check)
    strategy_item_opps = defaultdict(lambda: defaultdict(list))

    badge_distribution = Counter()
    final_level_distribution = Counter()
    score_distribution = []
    rounds_per_game = []
    final_round_trigger_count = 0

    start_time = time.time()
    for idx, (game_id, strategies) in enumerate(game_plans):
        if (idx + 1) % 100 == 0:
            elapsed = time.time() - start_time
            rate = (idx + 1) / elapsed
            eta = (len(game_plans) - idx - 1) / rate if rate > 0 else 0
            print(f"  Game {idx + 1}/{len(game_plans)} "
                  f"({rate:.1f} games/s, ETA {eta:.0f}s)")

        result = sim.simulate_game(game_id, strategies, seed=base_seed + game_id)

        # Track final-round trigger
        if result.final_round_triggered:
            final_round_trigger_count += 1
        rounds_per_game.append(result.rounds_played)

        for p in result.players:
            # Strategy stats
            s = strategy_stats[p.strategy]
            s["games_played"] += 1
            s["wins"] += 1 if p.rank == 1 else 0
            s["badges"][p.badge] += 1
            s["final_levels"][p.final_level] += 1
            s["scores"].append(p.final_score)
            s["tt_values"].append(p.tt)
            s["mp_values"].append(p.mp)
            s["sp_values"].append(p.sp)
            s["turns_played"].append(p.turns_played)
            s["cross_effects_given"].append(p.cross_effects_given)

            # Global distributions
            badge_distribution[p.badge] += 1
            final_level_distribution[p.final_level] += 1
            score_distribution.append(p.final_score)

            # LRA item-level stats
            for item_code, item_data in p.lra_assessment.items():
                if not isinstance(item_data, dict):
                    continue
                opp = item_data.get("opportunities_presented", 0)
                item_opportunity_counts[item_code].append(opp)
                item_assessment_counts[item_code][item_data.get("fairness_status", "unknown")] += 1
                if item_data.get("suggested_score") is not None:
                    item_score_distribution[item_code][str(item_data["suggested_score"])] += 1
                if item_data.get("defensible", False):
                    item_confidence_values[item_code].append(item_data.get("confidence", 0))
                item_evidence_counts[item_code].append(item_data.get("evidence_count", 0))
                item_missed_proving_counts[item_code].append(
                    item_data.get("missed_proving_count", 0))
                item_missed_disproving_counts[item_code].append(
                    item_data.get("missed_disproving_count", 0))

                strategy_item_opps[p.strategy][item_code].append(opp)

        # Save first 5 game results as full serialized samples
        if idx < 5:
            all_game_results.append(serialize_game_result(result))

    elapsed = time.time() - start_time
    print(f"\nCompleted {len(game_plans)} games in {elapsed:.1f}s "
          f"({len(game_plans) / elapsed:.1f} games/s)")

    # ── Build summary ────────────────────────────────────────
    summary = {
        "meta": {
            "games_played": len(game_plans),
            "players_per_game": n_players,
            "total_player_games": len(game_plans) * n_players,
            "elapsed_seconds": round(elapsed, 2),
            "base_seed": base_seed,
            "strategies_tested": strategy_names,
        },
        "game_outcomes": {
            "final_round_triggered_pct": round(
                final_round_trigger_count / len(game_plans) * 100, 2),
            "rounds_per_game": {
                "mean": round(mean(rounds_per_game), 2),
                "median": median(rounds_per_game),
                "min": min(rounds_per_game),
                "max": max(rounds_per_game),
                "stdev": round(stdev(rounds_per_game), 2) if len(rounds_per_game) > 1 else 0,
            },
        },
        "badge_distribution": dict(badge_distribution),
        "final_level_distribution": dict(final_level_distribution),
        "score_distribution": {
            "mean": round(mean(score_distribution), 2),
            "median": median(score_distribution),
            "min": min(score_distribution),
            "max": max(score_distribution),
            "stdev": round(stdev(score_distribution), 2),
            "pct_at_max": round(
                sum(1 for s in score_distribution if s >= max(score_distribution))
                / len(score_distribution) * 100, 2),
        },
        "strategy_stats": {},
        "item_opportunity_stats": {},
        "item_assessment_stats": {},
        "item_score_distribution": {
            code: dict(counter) for code, counter in item_score_distribution.items()
        },
        "item_confidence_stats": {},
        "strategy_item_opportunity_stats": {},
    }

    # Strategy stats
    for strat, s in strategy_stats.items():
        gp = s["games_played"]
        summary["strategy_stats"][strat] = {
            "games_played": gp,
            "win_rate": round(s["wins"] / gp * 100, 2) if gp > 0 else 0,
            "badges": dict(s["badges"]),
            "final_levels": dict(s["final_levels"]),
            "score": {
                "mean": round(mean(s["scores"]), 2) if s["scores"] else 0,
                "median": median(s["scores"]) if s["scores"] else 0,
                "min": min(s["scores"]) if s["scores"] else 0,
                "max": max(s["scores"]) if s["scores"] else 0,
                "stdev": round(stdev(s["scores"]), 2) if len(s["scores"]) > 1 else 0,
            },
            "tt": {
                "mean": round(mean(s["tt_values"]), 2) if s["tt_values"] else 0,
                "median": median(s["tt_values"]) if s["tt_values"] else 0,
                "min": min(s["tt_values"]) if s["tt_values"] else 0,
                "max": max(s["tt_values"]) if s["tt_values"] else 0,
            },
            "mp": {"mean": round(mean(s["mp_values"]), 2) if s["mp_values"] else 0},
            "sp": {"mean": round(mean(s["sp_values"]), 2) if s["sp_values"] else 0},
            "turns_played": {
                "mean": round(mean(s["turns_played"]), 2) if s["turns_played"] else 0,
            },
            "cross_effects_given": {
                "mean": round(mean(s["cross_effects_given"]), 2) if s["cross_effects_given"] else 0,
                "max": max(s["cross_effects_given"]) if s["cross_effects_given"] else 0,
            },
            "summit_rate": round(
                s["final_levels"].get("summit", 0) / gp * 100, 2) if gp > 0 else 0,
        }

    # Item opportunity stats (TASK A)
    for item_code, opps in item_opportunity_counts.items():
        expected = config.OPPORTUNITY_MODEL.get(item_code, {}).get("expected_per_game", 0)
        min_opp = config.OPPORTUNITY_MODEL.get(item_code, {}).get("min_opportunities", 2)
        summary["item_opportunity_stats"][item_code] = {
            "label": config.LRA_ITEMS.get(item_code, {}).get("label", item_code),
            "tier": config.LRA_ITEMS.get(item_code, {}).get("tier", ""),
            "expected_per_game": expected,
            "min_opportunities": min_opp,
            "limited_coverage": config.OPPORTUNITY_MODEL.get(item_code, {}).get(
                "limited_coverage", False),
            "actual_mean": round(mean(opps), 3),
            "actual_median": median(opps),
            "actual_stdev": round(stdev(opps), 3) if len(opps) > 1 else 0,
            "actual_min": min(opps),
            "actual_max": max(opps),
            "pct_zero_opp": round(sum(1 for o in opps if o == 0) / len(opps) * 100, 2),
            "pct_below_min": round(sum(1 for o in opps if o < min_opp) / len(opps) * 100, 2),
            "n": len(opps),
        }

    # Item assessment stats (TASK C + D)
    for item_code, counter in item_assessment_counts.items():
        n = sum(counter.values())
        opps = item_opportunity_counts[item_code]
        confs = item_confidence_values[item_code]
        summary["item_assessment_stats"][item_code] = {
            "label": config.LRA_ITEMS.get(item_code, {}).get("label", item_code),
            "n": n,
            "fairness_status_counts": dict(counter),
            "fairness_status_pct": {
                k: round(v / n * 100, 2) for k, v in counter.items()
            },
            "confidence_when_defensible": {
                "mean": round(mean(confs), 3) if confs else 0,
                "median": median(confs) if confs else 0,
                "n": len(confs),
            },
            "evidence_count": {
                "mean": round(mean(item_evidence_counts[item_code]), 2)
                if item_evidence_counts[item_code] else 0,
                "median": median(item_evidence_counts[item_code])
                if item_evidence_counts[item_code] else 0,
            },
            "missed_proving": {
                "mean": round(mean(item_missed_proving_counts[item_code]), 3)
                if item_missed_proving_counts[item_code] else 0,
                "max": max(item_missed_proving_counts[item_code])
                if item_missed_proving_counts[item_code] else 0,
            },
        }

    # Strategy × item opportunity (equity check)
    for strat, item_map in strategy_item_opps.items():
        summary["strategy_item_opportunity_stats"][strat] = {}
        for item_code, opps in item_map.items():
            summary["strategy_item_opportunity_stats"][strat][item_code] = {
                "mean": round(mean(opps), 3) if opps else 0,
                "pct_below_min": round(
                    sum(1 for o in opps
                        if o < config.OPPORTUNITY_MODEL.get(item_code, {}).get(
                            "min_opportunities", 2))
                    / len(opps) * 100, 2) if opps else 0,
                "n": len(opps),
            }

    # ── Write outputs ────────────────────────────────────────
    out_prefix = args.out or "sim"
    raw_path = os.path.join(REPORTS_DIR, f"{out_prefix}_samples.json")
    summary_path = os.path.join(REPORTS_DIR, f"{out_prefix}_summary.json")

    with open(raw_path, "w", encoding="utf-8") as f:
        json.dump({"sample_games": all_game_results}, f, indent=2, default=str)

    with open(summary_path, "w", encoding="utf-8") as f:
        json.dump(summary, f, indent=2, default=str)

    print(f"\nSample games (first 5) → {raw_path}")
    print(f"Summary statistics    → {summary_path}")
    print(f"\n=== KEY FINDINGS ===")
    print(f"Final-round trigger rate: "
          f"{summary['game_outcomes']['final_round_triggered_pct']}%")
    print(f"Score distribution: mean={summary['score_distribution']['mean']}, "
          f"max={summary['score_distribution']['max']}, "
          f"% at max={summary['score_distribution']['pct_at_max']}%")
    print(f"\nBadge distribution:")
    for badge, count in sorted(badge_distribution.items(),
                                key=lambda x: -x[1]):
        pct = count / (len(game_plans) * n_players) * 100
        print(f"  {badge:<20} {count:>6} ({pct:.1f}%)")

    print(f"\nStrategy win rates (rank 1):")
    sorted_strats = sorted(summary["strategy_stats"].items(),
                           key=lambda x: -x[1]["win_rate"])
    for strat, s in sorted_strats:
        print(f"  {strat:<22} {s['win_rate']:>5.1f}% win  "
              f"(summit {s['summit_rate']:>5.1f}%, "
              f"score mean {s['score']['mean']:>5})")

    # Top under-observed items
    print(f"\nTop 10 under-observed LRA items "
          f"(by % of games with insufficient_opportunity):")
    sorted_items = sorted(
        summary["item_opportunity_stats"].items(),
        key=lambda x: -x[1]["pct_below_min"])
    for code, st in sorted_items[:10]:
        print(f"  {code:<8} {st['label'][:40]:<40} "
              f"expected={st['expected_per_game']:.1f}  "
              f"actual={st['actual_mean']:.1f}  "
              f"%below_min={st['pct_below_min']:.1f}%")


if __name__ == "__main__":
    main()
