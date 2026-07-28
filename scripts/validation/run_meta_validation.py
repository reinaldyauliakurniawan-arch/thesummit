"""
The Summit — meta-validation runner.

Executes all 5 meta-validation tasks:
  TASK 1: Root cause categorization of existing findings
  TASK 2: Simulate with psychological archetypes (replace optimizer profiles)
  TASK 3: Adversarial assessment testing
  TASK 4: Discriminative power measurement
  TASK 5: Hypothesis framework generation

Produces a comprehensive Markdown report in docs/meta-validation-report.md

Usage:
    python3 scripts/validation/run_meta_validation.py [--games N] [--seed N]
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

from validation.simulator import Simulator, PlayerResult, GameResult, serialize_game_result
from validation import config
from validation.psychological_archetypes import (
    PSYCHOLOGICAL_ARCHETYPES,
    ARCHETYPE_LRA_EXPECTATIONS,
    get_archetype,
)
from validation.meta_validation import (
    categorize_findings,
    finding_summary_text,
    test_adversarial_strategies,
    exploit_summary_text,
    measure_discriminative_power,
    discrimination_summary_text,
    generate_hypotheses,
    hypothesis_summary_text,
)

REPORTS_DIR = "/home/z/my-project/thesummit/scripts/validation/reports"
OUTPUT_PATH = "/home/z/my-project/thesummit/docs/meta-validation-report.md"


def load_existing_summary(path: str) -> dict | None:
    if os.path.exists(path):
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f)
    return None


def run_archetype_games(sim: Simulator, n_games: int, n_players: int,
                        base_seed: int) -> dict[str, list[PlayerResult]]:
    """Run games with psychological archetypes and collect per-archetype results."""
    archetype_names = list(PSYCHOLOGICAL_ARCHETYPES.keys())
    results: dict[str, list[PlayerResult]] = defaultdict(list)
    
    # Phase 1: Homogeneous games (each archetype plays itself)
    for strat in archetype_names:
        games_for_this = max(n_games // len(archetype_names), 5)
        for i in range(games_for_this):
            game_id = len(results[f"_game_{strat}_homogeneous"])
            strategies = [strat] * n_players
            result = sim.simulate_game(game_id, strategies, seed=base_seed + game_id)
            for p in result.players:
                results[strat].append(p)
    
    # Phase 2: Mixed archetype games
    n_mixed = n_games // 2
    for i in range(n_mixed):
        rng_seed = base_seed + 100000 + i
        rng = __import__("random").Random(rng_seed)
        strategies = [rng.choice(archetype_names) for _ in range(n_players)]
        game_id = len(results["_mixed_games"])
        result = sim.simulate_game(game_id, strategies, seed=rng_seed)
        for p in result.players:
            results[p.strategy].append(p)
    
    return results


def build_archetype_fingerprint(archetype_results: dict[str, list[PlayerResult]]) -> dict:
    """Build LRA fingerprint for each archetype from simulation results."""
    fingerprints: dict[str, dict] = {}
    
    for arch_name, players in archetype_results.items():
        if not players:
            continue
        
        fp: dict[str, dict] = {}
        for item_code in config.LRA_ITEMS:
            scores = []
            confidences = []
            qualities = []
            proving_counts = []
            disproving_counts = []
            fair_count = 0
            total = 0
            
            for p in players:
                data = p.lra_assessment.get(item_code)
                if not isinstance(data, dict):
                    continue
                total += 1
                if data.get("fairness_status") == "fair":
                    fair_count += 1
                score = data.get("suggested_score")
                if score is not None and isinstance(score, (int, float)):
                    scores.append(score)
                conf = data.get("confidence", 0)
                if conf > 0:
                    confidences.append(conf)
                quality = data.get("quality_level", "")
                if quality:
                    qualities.append(quality)
                proving_counts.append(data.get("proving_count", 0))
                disproving_counts.append(data.get("disproving_count", 0))
            
            fp[item_code] = {
                "label": config.LRA_ITEMS[item_code]["label"],
                "tier": config.LRA_ITEMS[item_code]["tier"],
                "n_players": total,
                "fair_pct": round(fair_count / max(total, 1) * 100, 1),
                "avg_score": round(mean(scores), 2) if scores else None,
                "median_score": median(scores) if scores else None,
                "score_stdev": round(stdev(scores), 2) if len(scores) > 1 else 0,
                "avg_confidence": round(mean(confidences), 3) if confidences else 0,
                "quality_distribution": dict(Counter(qualities)),
                "avg_proving": round(mean(proving_counts), 2) if proving_counts else 0,
                "avg_disproving": round(mean(disproving_counts), 2) if disproving_counts else 0,
            }
        
        fingerprints[arch_name] = fp
    
    return fingerprints


def archetype_fingerprint_text(fingerprints: dict) -> str:
    """Produce human-readable archetype fingerprint comparison."""
    lines = ["## TASK 2 — Psychological Archetype Fingerprints\n"]
    
    lines.append("### Methodology")
    lines.append("")
    lines.append("Replaced 14 optimizer-based strategy profiles with 10 psychologically "
                "realistic player archetypes. Each archetype models how real humans make "
                "decisions — with biases, inconsistencies, stress responses, and context-dependent "
                "behavior. The key question: **Can the LRA assessment distinguish these "
                "archetypes from each other?**")
    lines.append("")
    
    lines.append("### Archetype LRA Score Matrix")
    lines.append("")
    lines.append("Average suggested score per LRA item per archetype (only items with "
                ">50% fair assessment rate shown):")
    lines.append("")
    
    # Build matrix
    arch_names = sorted(fingerprints.keys())
    assessable_items = []
    for item_code in config.LRA_ITEMS:
        # Only show items that are assessable for at least half of archetypes
        fair_count = sum(1 for arch in arch_names
                       if fingerprints.get(arch, {}).get(item_code, {}).get("fair_pct", 0) > 50)
        if fair_count >= len(arch_names) // 2:
            assessable_items.append(item_code)
    
    # Header
    header = "| Item | " + " | ".join(arch[:12] for arch in arch_names) + " |"
    lines.append(header)
    separator = "|------|" + "|".join(["------" for _ in arch_names]) + "|"
    lines.append(separator)
    
    for item_code in assessable_items:
        row = f"| {item_code} |"
        for arch in arch_names:
            fp = fingerprints.get(arch, {}).get(item_code, {})
            score = fp.get("avg_score")
            if score is not None:
                row += f" {score:.1f} |"
            else:
                row += " - |"
        lines.append(row)
    
    lines.append("")
    
    # Per-archetype profile
    lines.append("### Per-Archetype Profile\n")
    
    expectations = ARCHETYPE_LRA_EXPECTATIONS
    
    for arch_name in arch_names:
        fp = fingerprints.get(arch_name, {})
        lines.append(f"#### {arch_name.replace('_', ' ').title()}")
        lines.append("")
        
        # Expected high/low items
        exp = expectations.get(arch_name, {})
        expected_high = exp.get("expected_high", [])
        expected_low = exp.get("expected_low", [])
        expected_contra = exp.get("expected_contradictory", [])
        
        # Verify expectations against actual data
        lines.append("**Expected high scores**: " + ", ".join(
            f"{code} (actual: {fp.get(code, {}).get('avg_score', 'N/A')})"
            for code in expected_high
        ))
        lines.append("**Expected low scores**: " + ", ".join(
            f"{code} (actual: {fp.get(code, {}).get('avg_score', 'N/A')})"
            for code in expected_low
        ))
        if expected_contra:
            lines.append("**Expected contradictory**: " + ", ".join(
                f"{code} (actual quality: {fp.get(code, {}).get('quality_distribution', {})})"
                for code in expected_contra
            ))
        
        # Actual strongest/weakest items
        scored_items = [(code, data["avg_score"]) for code, data in fp.items()
                       if data.get("avg_score") is not None]
        scored_items.sort(key=lambda x: -x[1])
        
        if scored_items:
            top3 = scored_items[:3]
            bottom3 = scored_items[-3:]
            lines.append(f"\n**Actual top 3**: " + ", ".join(
                f"{code} ({score:.1f})" for code, score in top3))
            lines.append(f"**Actual bottom 3**: " + ", ".join(
                f"{code} ({score:.1f})" for code, score in bottom3))
        
        lines.append("")
    
    return "\n".join(lines)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--games", type=int, default=500,
                        help="Games per archetype (default: 500)")
    parser.add_argument("--seed", type=int, default=42,
                        help="Base RNG seed")
    parser.add_argument("--players", type=int, default=4,
                        help="Players per game")
    args = parser.parse_args()
    
    os.makedirs(REPORTS_DIR, exist_ok=True)
    
    print("=" * 60)
    print("THE SUMMIT — META-VALIDATION SUITE")
    print("=" * 60)
    print()
    
    sim = Simulator(seed=args.seed)
    n_games = args.games
    n_players = args.players
    base_seed = args.seed
    
    # ── Load existing simulation summaries ─────────────────────
    print("[1/5] Loading existing validation data...")
    s3 = load_existing_summary(os.path.join(REPORTS_DIR, "sim_summary.json"))
    s6 = load_existing_summary(os.path.join(REPORTS_DIR, "sim_6p_summary.json"))
    
    if s3 and s6:
        print(f"  Found: {s3['meta']['games_played']} (3p) + {s6['meta']['games_played']} (6p) games")
    else:
        print("  WARNING: No existing simulation data found.")
        print("  Run run_validation.py first to generate baseline data.")
        print("  Proceeding with partial analysis...")
        # Create minimal stubs
        s3 = s3 or {"meta": {"games_played": 0}, "item_opportunity_stats": {},
                     "item_assessment_stats": {}, "score_distribution": {},
                     "badge_distribution": {}}
        s6 = s6 or {"meta": {"games_played": 0}, "item_opportunity_stats": {},
                     "item_assessment_stats": {}, "score_distribution": {},
                     "badge_distribution": {}}
    
    # ── TASK 1: Root Cause Categorization ──────────────────────
    print("\n[1/5] TASK 1: Root cause categorization...")
    findings = categorize_findings(s3, s6)
    task1_text = finding_summary_text(findings)
    cat_counts = defaultdict(int)
    for f in findings:
        cat_counts[f.category] += 1
    print(f"  Findings: {len(findings)}")
    for cat, count in sorted(cat_counts.items()):
        print(f"    {cat}: {count}")
    
    # ── TASK 2: Psychological Archetype Simulation ────────────
    print(f"\n[2/5] TASK 2: Running {n_games} games with {len(PSYCHOLOGICAL_ARCHETYPES)} "
          f"psychological archetypes ({n_players} players each)...")
    
    start = time.time()
    archetype_results = run_archetype_games(sim, n_games, n_players, base_seed)
    elapsed = time.time() - start
    total_players = sum(len(ps) for ps in archetype_results.values())
    print(f"  Completed in {elapsed:.1f}s — {total_players} player-games across "
          f"{len(archetype_results)} archetypes")
    
    for arch_name, players in sorted(archetype_results.items()):
        if arch_name.startswith("_"):
            continue
        avg_score = mean(p.final_score for p in players)
        avg_tt = mean(p.tt for p in players)
        badges = Counter(p.badge for p in players)
        print(f"    {arch_name:<22} n={len(players):>4}  "
              f"score={avg_score:.1f}  tt={avg_tt:.1f}  "
              f"badges={dict(badges.most_common(3))}")
    
    fingerprints = build_archetype_fingerprint(archetype_results)
    task2_text = archetype_fingerprint_text(fingerprints)
    
    # Save fingerprints JSON
    fp_path = os.path.join(REPORTS_DIR, "archetype_fingerprints.json")
    with open(fp_path, "w", encoding="utf-8") as f:
        json.dump(fingerprints, f, indent=2, default=str)
    print(f"  Fingerprints saved to {fp_path}")
    
    # ── TASK 3: Adversarial Assessment Testing ─────────────────
    print(f"\n[3/5] TASK 3: Running adversarial assessment tests...")
    exploits = test_adversarial_strategies(sim, n_games=200)
    task3_text = exploit_summary_text(exploits)
    
    successful = [e for e in exploits if e.success]
    print(f"  Exploits tested: {len(exploits)}")
    print(f"  Successful exploits: {len(successful)}")
    for e in successful:
        print(f"    {e.exploit_id}: {e.description}")
    
    # ── TASK 4: Discriminative Power ───────────────────────────
    print(f"\n[4/5] TASK 4: Measuring assessment discriminative power...")
    
    # Use archetype results (only named archetypes, not _internal keys)
    clean_results = {k: v for k, v in archetype_results.items() if not k.startswith("_")}
    disc_results = measure_discriminative_power(clean_results)
    task4_text = discrimination_summary_text(disc_results)
    
    problems = [r for r in disc_results if r.problem]
    print(f"  Total comparisons: {len(disc_results)}")
    print(f"  Discrimination problems: {len(problems)}")
    if problems:
        print(f"  Problem rate: {len(problems)/max(len(disc_results),1)*100:.1f}%")
    
    # ── TASK 5: Hypothesis Framework ───────────────────────────
    print(f"\n[5/5] TASK 5: Generating hypothesis framework...")
    hypotheses = generate_hypotheses(findings, exploits, disc_results)
    task5_text = hypothesis_summary_text(hypotheses)
    print(f"  Hypotheses generated: {len(hypotheses)}")
    for h in hypotheses:
        print(f"    {h.hypothesis_id} [{h.confidence_level}]: {h.title[:60]}...")
    
    # ── Assemble Full Report ───────────────────────────────────
    print(f"\nAssembling meta-validation report...")
    
    report_lines = []
    def w(s=""):
        report_lines.append(s)
    
    w("# The Summit — Meta-Validation Report")
    w()
    w("> **Purpose**: Validate the validity of the validation itself.")
    w("> **Methodology**: 5 meta-validation tasks applied to existing simulation data + new psychological archetype simulations.")
    w(f"> **Games simulated (this run)**: {n_games} with {len(PSYCHOLOGICAL_ARCHETYPES)} archetypes × {n_players} players")
    w(f"> **Baseline data**: {s3['meta']['games_played']} (3p) + {s6['meta']['games_played']} (6p) optimizer games")
    w("> **Date**: 2026-07-29")
    w()
    w("---")
    w()
    
    # Executive summary
    w("## Executive Summary")
    w()
    w(f"The meta-validation framework analyzed {len(findings)} findings, "
       f"tested {len(exploits)} adversarial exploits, measured discriminative power "
       f"across {len(PSYCHOLOGICAL_ARCHETYPES)} psychological archetypes ({len(disc_results)} comparisons), "
       f"and generated {len(hypotheses)} structured hypotheses.")
    w()
    w("### Key Results")
    w()
    w(f"| Task | Result |")
    w(f"|------|--------|")
    
    cat_summary = defaultdict(int)
    for f in findings:
        cat_labels = {
            "model_defect": "Model Defect",
            "content_deficit": "Content Deficit",
            "progression_defect": "Progression Defect",
            "simulation_artifact": "Simulation Artifact",
        }
        cat_summary[cat_labels.get(f.category, f.category)] += 1
    w(f"| TASK 1: Root causes | {dict(cat_summary)} |")
    w(f"| TASK 2: Archetypes | {len(PSYCHOLOGICAL_ARCHETYPES)} archetypes simulated, fingerprints generated |")
    w(f"| TASK 3: Adversarial | {len(successful)}/{len(exploits)} exploits successful |")
    problem_rate = len(problems)/max(len(disc_results),1)*100
    w(f"| TASK 4: Discrimination | {problem_rate:.1f}% problem rate |")
    w(f"| TASK 5: Hypotheses | {len(hypotheses)} hypotheses generated |")
    w()
    
    # Critical findings
    critical = [f for f in findings if f.severity == "critical"]
    high_sev = [f for f in findings if f.severity == "high"]
    
    if critical or high_sev:
        w("### Priority Actions")
        w()
        for f in critical + high_sev:
            w(f"- **{f.finding_id}** [{f.severity.upper()}]: {f.description}")
            w(f"  Root cause: {f.category}")
            w(f"  Recommendation: {f.recommendation}")
            w()
    
    w("---")
    w()
    
    # Append each task section
    w(task1_text)
    w("---")
    w()
    w(task2_text)
    w("---")
    w()
    w(task3_text)
    w("---")
    w()
    w(task4_text)
    w("---")
    w()
    w(task5_text)
    
    # ── Appendix: Archetype Descriptions ──────────────────────
    w("## Appendix A — Psychological Archetype Descriptions")
    w()
    w("| Archetype | Decision Pattern | Key Bias | Expected Blind Spot |")
    w("|-----------|-----------------|----------|---------------------|")
    
    arch_descriptions = {
        "conflict_avoider": "Avoids confrontation options, prefers empathy",
        "people_pleaser": "Maximizes benefit to others at personal cost",
        "micromanager": "Prefers control/oversight, resists delegation",
        "controller": "Maximizes own stats, sees TT as secondary",
        "hero_syndrome": "Takes risky/dramatic options, especially under stress",
        "political_player": "Maximizes reputation, avoids visible risk",
        "servant_leader": "Balances team benefit with personal capability",
        "perfectionist": "Minimizes variance, avoids stat loss",
        "opportunist": "Switches strategy based on recent outcomes",
        "consensus_seeker": "Alternates options to appear balanced",
    }
    
    arch_blind_spots = {
        "conflict_avoider": "Decisiveness, tough conversations",
        "people_pleaser": "Own progression, decisiveness",
        "micromanager": "Delegation, adaptability, cross-org",
        "controller": "Team engagement, empathy",
        "hero_syndrome": "Systems/process, consistency",
        "political_player": "Risk-taking, integrity under pressure",
        "servant_leader": "None (ideal profile)",
        "perfectionist": "Risk-taking, learning from failure",
        "opportunist": "Consistency (many contradictions)",
        "consensus_seeker": "Assertiveness, decisive action",
    }
    
    for arch in sorted(PSYCHOLOGICAL_ARCHETYPES.keys()):
        desc = arch_descriptions.get(arch, "")
        blind = arch_blind_spots.get(arch, "")
        w(f"| {arch} | {desc} | See description file | {blind} |")
    
    w()
    
    # ── Appendix: Methodology ─────────────────────────────────
    w("## Appendix B — Methodology")
    w()
    w("### Root Cause Categorization Rules (TASK 1)")
    w()
    w("Every finding is tested against 4 hypotheses before classification:")
    w("1. **Content deficit**: cards_tagging <= 3 AND >70% below minimum")
    w("2. **Progression defect**: cards exist but game moves too fast through the level")
    w("3. **Model defect**: many cards exist but expected_per_game is still way off")
    w("4. **Simulation artifact**: under-observed but no game mechanic explains it")
    w()
    w("The hypothesis with the highest confidence is selected as the root cause.")
    w("If multiple hypotheses tie, the finding flags ambiguity.")
    w()
    w("### Psychological Archetype Design (TASK 2)")
    w()
    w("Each archetype uses a scoring function with these components:")
    w("- **Stat weighting**: How much each stat (MP/SP/TT) matters to them")
    w("- **Tag preference**: Which behavior_tags they seek or avoid")
    w("- **Stress response**: How stress level modifies their decisions")
    w("- **Inconsistency rate**: % chance of random choice (models distraction)")
    w("- **Noise level**: Gaussian noise on scores (models imperfect evaluation)")
    w()
    w("### Adversarial Testing Protocol (TASK 3)")
    w()
    w("5 exploit strategies are tested:")
    w("1. **Proving farmer**: Maximizes proving LRA tags")
    w("2. **Diversity hacker**: Maximizes behavior dimension count")
    w("3. **Altruist score hacker**: Maximizes TT for Catalyst badge")
    w("4. **Contradiction bomb**: Mixes proving/disproving to force 'mixed' scores")
    w("5. **Random walk baseline**: Checks if random play gets high scores")
    w()
    w("An exploit is 'successful' if it produces assessment outcomes that don't "
       "match the player's actual behavioral pattern.")
    w()
    
    # Write report
    os.makedirs(os.path.dirname(OUTPUT_PATH), exist_ok=True)
    with open(OUTPUT_PATH, "w", encoding="utf-8") as f:
        f.write("\n".join(report_lines))
    
    print(f"\n{'=' * 60}")
    print(f"Meta-validation report written to: {OUTPUT_PATH}")
    print(f"Report length: {len(report_lines)} lines")
    print(f"{'=' * 60}")


if __name__ == "__main__":
    main()
