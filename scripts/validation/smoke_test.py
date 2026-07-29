"""
Quick smoke test — run 5 games and dump summary stats.
"""
import sys
import os
sys.path.insert(0, "/home/z/my-project/thesummit/scripts")

from validation.simulator import Simulator, serialize_game_result
from validation import config
import json

sim = Simulator(seed=42)

strategies = ["random", "greedy_score", "balanced"]

for game_id in range(5):
    result = sim.simulate_game(game_id, strategies * 2, seed=42 + game_id)  # 6 players
    print(f"\n=== Game {game_id + 1} (seed={result.seed}) ===")
    print(f"Rounds played: {result.rounds_played}")
    print(f"Final round triggered: {result.final_round_triggered} "
          f"(at round {result.final_round_trigger_round})")
    print(f"\nPlayer results (sorted by rank):")
    print(f"{'Rank':<5}{'Name':<5}{'Strategy':<22}{'Level':<10}{'MP':<4}{'SP':<4}{'TT':<4}{'Score':<7}{'Badge':<15}")
    for p in result.players:
        print(f"{p.rank:<5}{p.name:<5}{p.strategy:<22}{p.final_level:<10}"
              f"{p.mp:<4}{p.sp:<4}{p.tt:<4}{p.final_score:<7}{p.badge:<15}")

# Show LRA assessment for one player
sample_player = result.players[0]
print(f"\n=== LRA Assessment for {sample_player.name} ({sample_player.strategy}) ===")
print(f"Opportunity summary:")
print(f"  Total items: {sample_player.opportunity_summary['total_items']}")
print(f"  Assessable: {sample_player.opportunity_summary['items_assessable']}")
print(f"  No opportunity: {sample_player.opportunity_summary['items_no_opportunity']}")
print(f"  Insufficient opp: {sample_player.opportunity_summary['items_insufficient_opportunity']}")
print(f"  Limited coverage: {sample_player.opportunity_summary['items_limited_coverage']}")

print(f"\nFirst 10 LRA items:")
for code, item in list(sample_player.lra_assessment.items())[:10]:
    if isinstance(item, dict) and 'opportunities_presented' in item:
        print(f"  {code}: opp={item['opportunities_presented']}/{item['min_opportunities']} "
              f"status={item['fairness_status']} "
              f"score={item.get('suggested_score')} "
              f"conf={item.get('confidence', 0):.2f}")
