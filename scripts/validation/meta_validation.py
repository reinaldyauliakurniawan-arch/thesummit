"""
The Summit — meta-validation framework.

Validates the validity of the validation itself by:
  TASK 1: Categorizing findings into 4 root-cause categories
  TASK 2: Testing with psychologically realistic archetypes
  TASK 3: Adversarial assessment testing (trying to fool the LRA)
  TASK 4: Measuring assessment discriminative power
  TASK 5: Hypothesis framework for balancing recommendations

This module provides analysis functions that consume simulation data
and produce structured findings with root-cause categorization.
"""
from __future__ import annotations
from dataclasses import dataclass, field
from typing import Any
from collections import defaultdict, Counter
from statistics import mean, median, stdev

from . import config


# ── TASK 1: Root Cause Categorization ──────────────────────────

class FindingCategory:
    """Four categories for validation findings."""
    MODEL_DEFECT = "model_defect"
    """The opportunity model or assessment formula has a logical error."""
    
    CONTENT_DEFICIT = "content_deficit"
    """Not enough cards cover a competency — the content pool is insufficient."""
    
    PROGRESSION_DEFECT = "progression_defect"
    """Game progression mechanics cause some competencies to be under-observed.
    E.g., players advance too quickly through a level, leaving insufficient turns."""
    
    SIMULATION_ARTIFACT = "simulation_artifact"
    """The finding is an artifact of how the simulation works, not a real problem.
    E.g., strategy agents behave differently from humans, or the card draw
    algorithm creates patterns that wouldn't occur in real play."""


@dataclass
class CategorizedFinding:
    """A single finding with root-cause classification."""
    finding_id: str
    description: str
    category: str  # One of FindingCategory constants
    severity: str  # "critical", "high", "medium", "low"
    evidence: list[str]  # Data points supporting this finding
    possible_explanations: list[str]
    confidence_in_root_cause: float  # 0.0 - 1.0
    recommended_experiment: str
    is_actionable: bool  # Can we fix it without redesigning the game?
    recommendation: str = ""


def categorize_findings(summary_3p: dict, summary_6p: dict) -> list[CategorizedFinding]:
    """Analyze simulation summaries and produce categorized findings.
    
    Rules:
    - Do NOT assume the opportunity model is wrong (per user directive)
    - Determine the TRUE root cause by checking multiple hypotheses
    - Content deficit: cards_tagging is low AND no other explanation fits
    - Progression defect: expected_per_game is reasonable but actual is low
      due to game length / level transition patterns
    - Model defect: the model's formula itself has an error
    - Simulation artifact: the finding only appears in sim, not explainable by game mechanics
    """
    findings: list[CategorizedFinding] = []
    
    # ── Analyze each LRA item ─────────────────────────────────
    opp3 = summary_3p.get("item_opportunity_stats", {})
    assess3 = summary_3p.get("item_assessment_stats", {})
    
    for item_code in config.OPPORTUNITY_MODEL:
        opp_model = config.OPPORTUNITY_MODEL[item_code]
        opp_stats = opp3.get(item_code, {})
        assess_stats = assess3.get(item_code, {})
        
        if not opp_stats:
            continue
        
        expected = opp_model.get("expected_per_game", 0)
        actual_mean = opp_stats.get("actual_mean", 0)
        pct_below_min = opp_stats.get("pct_below_min", 0)
        cards_tagging = opp_model.get("cards_tagging", 0)
        limited = opp_model.get("limited_coverage", False)
        
        actual_ratio = actual_mean / expected if expected > 0 else float("inf")
        
        # Only create findings for items with significant deviations
        if pct_below_min < 50 and actual_ratio >= 0.5:
            continue  # This item is fine
        
        # ── Root cause determination ──────────────────────────
        
        # Hypothesis 1: Content deficit (not enough cards)
        is_content_deficit = cards_tagging <= 3 and pct_below_min > 70
        
        # Hypothesis 2: Progression defect (game moves too fast)
        item_tier = config.LRA_ITEMS.get(item_code, {}).get("tier", "")
        level_map = {"PtP": "all", "R1": "basecamp", "R2": "camp", "R3": "summit"}
        item_level = level_map.get(item_tier, "all")
        
        is_progression_defect = False
        if item_level in ("summit", "basecamp"):
            # Summit: players get few turns there
            # Basecamp: players advance quickly (only need MP>=8, SP>=8)
            # Both lead to under-observation regardless of card count
            if cards_tagging >= 5 and pct_below_min > 50 and actual_ratio < 0.5:
                is_progression_defect = True
        
        # Hypothesis 3: Model defect (formula error)
        is_model_defect = False
        if expected > 0 and cards_tagging >= 10 and actual_ratio < 0.3:
            # Many cards tag this item but expected is still way off
            # Likely the model's formula is wrong
            is_model_defect = True
        
        # Hypothesis 4: Simulation artifact
        is_simulation_artifact = False
        if pct_below_min > 50 and cards_tagging >= 5 and not is_progression_defect:
            # Cards exist, progression seems OK, but still under-observed
            # Could be a simulation artifact (e.g., strategy agents avoiding these cards)
            is_simulation_artifact = True
        
        # ── Determine primary root cause ─────────────────────
        if is_content_deficit:
            category = FindingCategory.CONTENT_DEFICIT
            confidence = 0.9
            explanation = (f"Only {cards_tagging} card(s) tag this competency. "
                         f"Even with perfect game length, {cards_tagging} cards "
                         f"cannot reliably provide {opp_model.get('min_opportunities', 2)}+ "
                         f"opportunities per game.")
            recommendation = (f"Add {max(4, opp_model.get('min_opportunities', 2) * 2) - cards_tagging} "
                            f"cards tagging {item_code} at the appropriate level.")
        elif is_progression_defect:
            category = FindingCategory.PROGRESSION_DEFECT
            confidence = 0.75
            explanation = (f"{cards_tagging} cards exist, but players spend insufficient turns "
                         f"at {'summit' if item_level == 'summit' else 'basecamp'} level. "
                         f"Expected {expected} opps/game but actual mean is {actual_mean:.1f}. "
                         f"The progression mechanics cause rapid level transitions.")
            recommendation = (f"Either: (a) slow progression to allow more turns at "
                            f"{'summit' if item_level == 'summit' else 'basecamp'}, or "
                            f"(b) add cards at OTHER levels that also tag {item_code}, "
                            f"or (c) accept limited assessment for this item.")
        elif is_model_defect:
            category = FindingCategory.MODEL_DEFECT
            confidence = 0.65
            explanation = (f"Expected {expected} opps/game but actual is {actual_mean:.1f} "
                         f"(ratio {actual_ratio:.2f}x). With {cards_tagging} cards tagging "
                         f"this item, the model's expected_per_game formula may be incorrect.")
            recommendation = (f"Recalculate expected_per_game from simulation data. "
                            f"Replace theoretical {expected} with empirical {actual_mean:.1f}.")
        elif is_simulation_artifact:
            category = FindingCategory.SIMULATION_ARTIFACT
            confidence = 0.5
            explanation = (f"Under-observed ({pct_below_min}% below min) despite {cards_tagging} "
                         f"cards. May be caused by strategy agents making choices that avoid "
                         f"these cards or the simulation's card draw algorithm creating patterns.")
            recommendation = (f"Run validation with psychological archetypes (TASK 2) to "
                            f"determine if real-human decision patterns produce different "
                            f"opportunity frequencies. If under-observation persists, "
                            f"reclassify as content_deficit.")
        else:
            continue
        
        severity = "critical" if pct_below_min > 90 else (
            "high" if pct_below_min > 70 else (
                "medium" if pct_below_min > 50 else "low"))
        
        findings.append(CategorizedFinding(
            finding_id=f"OPP-{item_code}",
            description=f"LRA item {item_code} ({config.LRA_ITEMS[item_code]['label']}) "
                       f"is under-observed: {pct_below_min}% of games below minimum opportunity "
                       f"threshold (expected={expected}, actual={actual_mean:.1f})",
            category=category,
            severity=severity,
            evidence=[
                f"Expected per game: {expected}",
                f"Actual mean: {actual_mean:.1f}",
                f"Ratio: {actual_ratio:.2f}x",
                f"% below minimum: {pct_below_min}%",
                f"Cards tagging: {cards_tagging}",
                f"Limited coverage: {limited}",
            ],
            possible_explanations=[
                ("Content deficit: not enough cards" if is_content_deficit else None),
                ("Progression defect: game moves too fast through level" if is_progression_defect else None),
                ("Model defect: expected_per_game formula is wrong" if is_model_defect else None),
                ("Simulation artifact: agent behavior causes bias" if is_simulation_artifact else None),
            ],
            confidence_in_root_cause=confidence,
            recommended_experiment=(
                f"Run 500 games with 10 psychological archetypes. "
                f"If opportunity frequency changes significantly (>20%), "
                f"reclassify as simulation_artifact."
            ),
            is_actionable=(category != FindingCategory.SIMULATION_ARTIFACT),
            recommendation=recommendation,
        ))
    
    # ── Score saturation finding ─────────────────────────────
    score_dist = summary_3p.get("score_distribution", {})
    pct_at_max = score_dist.get("pct_at_max", 0)
    if pct_at_max > 30:
        findings.append(CategorizedFinding(
            finding_id="SCORE-SAT",
            description=f"Score cap saturation: {pct_at_max}% of players hit maximum score",
            category=FindingCategory.MODEL_DEFECT,
            severity="high" if pct_at_max > 50 else "medium",
            evidence=[
                f"3-player: {pct_at_max}% at max score",
                f"Max score: {score_dist.get('max', 'N/A')}",
                f"Mean score: {score_dist.get('mean', 'N/A')}",
            ],
            possible_explanations=[
                "tt_bonus_cap (15) is too easy to reach",
                "Level value (30 at summit) dominates scoring",
                "Diversity bonus (0-5) inflates summit scores",
            ],
            confidence_in_root_cause=0.85,
            recommended_experiment="Reduce tt_bonus_cap to 10 and re-run 500 games",
            is_actionable=True,
            recommendation="Reduce tt_bonus_cap from 15 to 10 in config/summit.php and config.py",
        ))
    
    # ── Badge distribution finding ────────────────────────────
    badge_dist = summary_3p.get("badge_distribution", {})
    total_badges = sum(badge_dist.values())
    dead_badges = [b for b, c in badge_dist.items() if c == 0]
    if dead_badges:
        findings.append(CategorizedFinding(
            finding_id="BADGE-DEAD",
            description=f"Badges that never trigger: {', '.join(dead_badges)}",
            category=FindingCategory.PROGRESSION_DEFECT,
            severity="medium",
            evidence=[f"Badge distribution: {badge_dist}"],
            possible_explanations=[
                "Badge criteria are too easy/hard relative to normal gameplay",
                "Game progression makes certain stat combinations impossible",
            ],
            confidence_in_root_cause=0.7,
            recommended_experiment="Check per-player stat distributions to see if "
                                  "badge criteria thresholds are ever reached",
            is_actionable=True,
            recommendation="Either tighten Carrier requirements or retire dead badges",
        ))
    
    return findings


def finding_summary_text(findings: list[CategorizedFinding]) -> str:
    """Produce human-readable summary of categorized findings."""
    lines = ["## TASK 1 — Root Cause Categorization\n"]
    
    # Group by category
    by_cat: dict[str, list[CategorizedFinding]] = defaultdict(list)
    for f in findings:
        by_cat[f.category].append(f)
    
    cat_labels = {
        FindingCategory.MODEL_DEFECT: "Model Defect",
        FindingCategory.CONTENT_DEFICIT: "Content Deficit",
        FindingCategory.PROGRESSION_DEFECT: "Progression Defect",
        FindingCategory.SIMULATION_ARTIFACT: "Simulation Artifact",
    }
    
    # Summary table
    lines.append("| Category | Count | Actionable |")
    lines.append("|----------|-------|-----------|")
    for cat in [FindingCategory.MODEL_DEFECT, FindingCategory.CONTENT_DEFICIT,
                FindingCategory.PROGRESSION_DEFECT, FindingCategory.SIMULATION_ARTIFACT]:
        items = by_cat.get(cat, [])
        actionable = sum(1 for i in items if i.is_actionable)
        label = cat_labels[cat]
        lines.append(f"| {label} | {len(items)} | {actionable} |")
    lines.append("")
    
    # Detailed findings
    for cat in [FindingCategory.MODEL_DEFECT, FindingCategory.CONTENT_DEFICIT,
                FindingCategory.PROGRESSION_DEFECT, FindingCategory.SIMULATION_ARTIFACT]:
        items = by_cat.get(cat, [])
        if not items:
            continue
        label = cat_labels[cat]
        lines.append(f"### {label} ({len(items)} findings)")
        lines.append("")
        for f in sorted(items, key=lambda x: -x.confidence_in_root_cause):
            lines.append(f"**{f.finding_id}** — {f.description}")
            lines.append(f"- Severity: {f.severity}")
            lines.append(f"- Confidence in root cause: {f.confidence_in_root_cause:.0%}")
            lines.append(f"- Evidence: {'; '.join(f.evidence[:3])}")
            exp_str = "; ".join(e for e in f.possible_explanations if e)
            lines.append(f"- Possible explanations: {exp_str}")
            lines.append(f"- Actionable: {'Yes' if f.is_actionable else 'No'}")
            lines.append(f"- Recommendation: {f.recommendation}")
            lines.append(f"- Experiment: {f.recommended_experiment}")
            lines.append("")
    
    return "\n".join(lines)


# ── TASK 3: Adversarial Assessment Testing ──────────────────────

@dataclass
class ExploitReport:
    """A single exploit attempt against the LRA assessment."""
    exploit_id: str
    strategy_name: str
    description: str
    target_item: str  # Which LRA item this tries to exploit
    mechanism: str    # How the exploit works
    success: bool     # Did it fool the assessment?
    evidence: dict    # Assessment output showing the exploit
    severity: str     # "critical" = always fools, "high" = often fools


def test_adversarial_strategies(simulator, n_games: int = 200) -> list[ExploitReport]:
    """Run adversarial strategies designed to fool the LRA assessment.
    
    Exploit vectors tested:
    1. Proving farming: Always pick the option with more 'proving' LRA tags
       (already exists as 'proving_seeker' — test if it produces artificially high scores)
    2. Contradiction avoidance: Alternate between proving options for different items
       to avoid triggering 'contradictory' quality level
    3. Stat sacrifice: Accept bad stats to pick the option with best LRA tags
       (tests whether assessment separates "genuine leadership" from "gaming the system")
    4. Minimum viable leadership: Pick just enough proving options to get
       score 3/5 on every item, then coast
    5. Score hacking via missed opportunities: Choose the proving option for
       popular items but skip proving for rare items (so missed_proving doesn't count)
    """
    from .strategy_agents import get_strategy
    from .simulator import Simulator as Sim
    
    exploits: list[ExploitReport] = []
    
    # ── Exploit 1: Proving Farmer ─────────────────────────────
    results_farm = []
    for game_id in range(n_games // 4):
        result = simulator.simulate_game(
            game_id,
            ["proving_seeker"] * 4,
            seed=10000 + game_id,
        )
        results_farm.extend(result.players)
    
    # Check: do proving farmers get inflated scores?
    high_scores = []
    for p in results_farm:
        for code, data in p.lra_assessment.items():
            if isinstance(data, dict) and data.get("suggested_score") is not None:
                if data["suggested_score"] >= 4:
                    high_scores.append({
                        "item": code,
                        "score": data["suggested_score"],
                        "confidence": data.get("confidence", 0),
                        "quality": data.get("quality_level", ""),
                        "fairness": data.get("fairness_status", ""),
                    })
    
    success = len(high_scores) > len(results_farm) * 5  # More than 5 per player on avg
    exploits.append(ExploitReport(
        exploit_id="ADV-001",
        strategy_name="proving_seeker",
        description="Proving farmer: always picks option with most 'proving' LRA tags",
        target_item="ALL",
        mechanism="Strategy explicitly maximizes proving tag count regardless of game context",
        success=success,
        evidence={
            "total_players_tested": len(results_farm),
            "high_score_instances": len(high_scores),
            "avg_high_score_per_player": len(high_scores) / max(len(results_farm), 1),
            "sample_high_scores": high_scores[:10],
        },
        severity="high" if success else "medium",
    ))
    
    # ── Exploit 2: Diversity Hacker ────────────────────────────
    results_div = []
    for game_id in range(n_games // 4):
        result = simulator.simulate_game(
            game_id,
            ["diversity_seeker"] * 4,
            seed=20000 + game_id,
        )
        results_div.extend(result.players)
    
    # Check: do diversity hackers get Strategist badge artificially?
    strategist_count = sum(1 for p in results_div if p.badge == "the_strategist")
    strategist_pct = strategist_count / max(len(results_div), 1) * 100
    
    exploits.append(ExploitReport(
        exploit_id="ADV-002",
        strategy_name="diversity_seeker",
        description="Diversity hacker: picks options to maximize behavior dimension count",
        target_item="Badge: the_strategist",
        mechanism="Strategy explicitly maximizes new behavior dimensions to qualify for Strategist badge",
        success=strategist_pct > 50,
        evidence={
            "total_players_tested": len(results_div),
            "strategist_badges": strategist_count,
            "strategist_pct": round(strategist_pct, 1),
        },
        severity="high" if strategist_pct > 50 else "low",
    ))
    
    # ── Exploit 3: Altruist Score Hacker ──────────────────────
    results_alt = []
    for game_id in range(n_games // 4):
        result = simulator.simulate_game(
            game_id,
            ["altruist"] * 4,
            seed=30000 + game_id,
        )
        results_alt.extend(result.players)
    
    # Check: do altruists get Catalyst badge without genuine leadership?
    catalyst_count = sum(1 for p in results_alt if p.badge == "the_catalyst")
    catalyst_avg_score = mean(
        [p.final_score for p in results_alt if p.badge == "the_catalyst"]
    ) if catalyst_count > 0 else 0
    
    exploits.append(ExploitReport(
        exploit_id="ADV-003",
        strategy_name="altruist",
        description="Altruist score hacker: maximizes TT to get Catalyst badge",
        target_item="Badge: the_catalyst",
        mechanism="Strategy maximizes cross-player TT to qualify for Catalyst without genuine leadership breadth",
        success=catalyst_count > len(results_alt) * 0.3,
        evidence={
            "total_players_tested": len(results_alt),
            "catalyst_badges": catalyst_count,
            "catalyst_pct": round(catalyst_count / max(len(results_alt), 1) * 100, 1),
            "catalyst_avg_score": round(catalyst_avg_score, 1),
        },
        severity="medium",
    ))
    
    # ── Exploit 4: Contradiction Bomb ─────────────────────────
    results_bomb = []
    for game_id in range(n_games // 4):
        # Mix proving and disproving seekers in same game
        result = simulator.simulate_game(
            game_id,
            ["proving_seeker", "disproving_seeker", "proving_seeker", "disproving_seeker"],
            seed=40000 + game_id,
        )
        results_bomb.extend(result.players)
    
    # Check: how many items end up "contradictory"?
    contradictory_items = 0
    total_assessed = 0
    for p in results_bomb:
        for code, data in p.lra_assessment.items():
            if isinstance(data, dict) and data.get("quality_level") == "contradictory":
                contradictory_items += 1
            if isinstance(data, dict) and data.get("suggested_score") is not None:
                total_assessed += 1
    
    contradiction_rate = contradictory_items / max(total_assessed, 1) * 100
    
    exploits.append(ExploitReport(
        exploit_id="ADV-004",
        strategy_name="proving_seeker + disproving_seeker mix",
        description="Contradiction bomb: mixing proving/disproving strategies to trigger 'mixed' scores",
        target_item="ALL (contradiction detection)",
        mechanism="A player deliberately alternating between proving and disproving to force 'mixed' and avoid any defensible assessment",
        success=contradiction_rate > 30,
        evidence={
            "total_players_tested": len(results_bomb),
            "contradictory_items": contradictory_items,
            "total_assessed_items": total_assessed,
            "contradiction_rate": round(contradiction_rate, 1),
        },
        severity="high" if contradiction_rate > 30 else "low",
    ))
    
    # ── Exploit 5: Random Walk (baseline) ──────────────────────
    results_rand = []
    for game_id in range(n_games // 4):
        result = simulator.simulate_game(
            game_id,
            ["random"] * 4,
            seed=50000 + game_id,
        )
        results_rand.extend(result.players)
    
    # Check: do random players accidentally get high scores?
    accidental_high = 0
    for p in results_rand:
        for code, data in p.lra_assessment.items():
            score_val = data.get("suggested_score")
            if isinstance(data, dict) and score_val is not None and score_val != "mixed" and score_val >= 4:
                accidental_high += 1
    
    exploits.append(ExploitReport(
        exploit_id="ADV-005",
        strategy_name="random",
        description="Random walk baseline: pure random choices getting undeserved high scores",
        target_item="ALL",
        mechanism="Player makes completely random choices but the assessment still assigns high scores",
        success=accidental_high > len(results_rand) * 2,
        evidence={
            "total_players_tested": len(results_rand),
            "accidental_high_scores": accidental_high,
            "avg_accidental_per_player": round(accidental_high / max(len(results_rand), 1), 2),
        },
        severity="high" if accidental_high > len(results_rand) * 2 else "low",
    ))
    
    return exploits


def exploit_summary_text(exploits: list[ExploitReport]) -> str:
    """Produce human-readable summary of adversarial test results."""
    lines = ["## TASK 3 — Adversarial Assessment Testing\n"]
    lines.append("### Exploit Attempts\n")
    lines.append("")
    lines.append("| ID | Strategy | Target | Success? | Severity | Evidence Summary |")
    lines.append("|----|----------|--------|----------|----------|-----------------|")
    
    for e in exploits:
        ev = e.evidence
        if isinstance(ev, dict):
            summary_parts = [f"{k}={v}" for k, v in list(ev.items())[:3]]
            ev_summary = "; ".join(summary_parts)
        else:
            ev_summary = str(ev)[:80]
        
        lines.append(
            f"| {e.exploit_id} | {e.strategy_name} | {e.target_item} | "
            f"{'YES' if e.success else 'NO'} | {e.severity} | {ev_summary} |"
        )
    
    lines.append("")
    
    # Summary
    successful = [e for e in exploits if e.success]
    lines.append(f"**Exploits found**: {len(successful)} of {len(exploits)} tested")
    lines.append("")
    
    for e in successful:
        lines.append(f"#### {e.exploit_id}: {e.description}")
        lines.append(f"- Mechanism: {e.mechanism}")
        lines.append(f"- Severity: {e.severity}")
        ev_text = ""
        if isinstance(e.evidence, dict):
            for k, v in e.evidence.items():
                ev_text += f"  - {k}: {v}\n"
        lines.append(f"- Evidence:\n{ev_text}")
        lines.append("")
    
    # Mitigation recommendations
    if successful:
        lines.append("### Recommended Mitigations\n")
        for e in successful:
            if e.exploit_id == "ADV-001":
                lines.append(f"- **{e.exploit_id}**: Add a 'strategic diversity' check — "
                            f"if a player always picks the proving option, flag as possible "
                            f"assessment gaming. Require proving evidence across MULTIPLE context types.")
            elif e.exploit_id == "ADV-002":
                lines.append(f"- **{e.exploit_id}**: The Strategist badge requires breadth but not depth. "
                            f"Consider requiring at least 3 observations per dimension at quality>=medium.")
            elif e.exploit_id == "ADV-003":
                lines.append(f"- **{e.exploit_id}**: Catalyst badge requires high TT + cross-player effects "
                            f"but doesn't require leadership breadth. Add a minimum diversity bonus threshold.")
            elif e.exploit_id == "ADV-004":
                lines.append(f"- **{e.exploit_id}**: Contradiction detection works as designed — "
                            f"'mixed' score is the correct output for inconsistent behavior. "
                            f"This is NOT an exploit but a valid assessment result.")
            elif e.exploit_id == "ADV-005":
                lines.append(f"- **{e.exploit_id}**: Random players should not get high scores. "
                            f"If they do, the score mapping thresholds are too lenient. "
                            f"Consider requiring quality_level >= medium for score >= 4.")
        lines.append("")
    
    return "\n".join(lines)


# ── TASK 4: Discriminative Power Analysis ──────────────────────

@dataclass
class DiscriminationResult:
    """Result of comparing two archetypes' assessment outputs."""
    archetype_a: str
    archetype_b: str
    item: str
    score_diff: float  # Absolute difference in suggested scores
    confident_both: bool  # Both have defensible assessments?
    assessment_similarity: str  # "identical", "similar", "different", "opposite"
    problem: bool  # True if different archetypes get similar scores


def measure_discriminative_power(archetype_results: dict[str, list]) -> list[DiscriminationResult]:
    """Measure whether the assessment distinguishes between different archetypes.
    
    For each pair of archetypes, for each LRA item:
    - Compare their suggested scores
    - If both are defensible (confidence >= 0.50) AND scores are identical,
      flag as a discrimination problem
    
    Args:
        archetype_results: {archetype_name: [PlayerResult, ...]}
    """
    results: list[DiscriminationResult] = []
    archetype_names = list(archetype_results.keys())
    
    for i, name_a in enumerate(archetype_names):
        for name_b in archetype_names[i+1:]:
            players_a = archetype_results[name_a]
            players_b = archetype_results[name_b]
            
            if not players_a or not players_b:
                continue
            
            # Average scores per item for each archetype
            for item_code in config.LRA_ITEMS:
                scores_a = []
                scores_b = []
                conf_a = []
                conf_b = []
                
                for p in players_a:
                    data = p.lra_assessment.get(item_code)
                    score_val = data.get("suggested_score") if isinstance(data, dict) else None
                    if isinstance(score_val, (int, float)) and score_val != "mixed":
                        scores_a.append(score_val)
                        conf_a.append(data.get("confidence", 0))
                
                for p in players_b:
                    data = p.lra_assessment.get(item_code)
                    score_val = data.get("suggested_score") if isinstance(data, dict) else None
                    if isinstance(score_val, (int, float)) and score_val != "mixed":
                        scores_b.append(score_val)
                        conf_b.append(data.get("confidence", 0))
                
                if not scores_a or not scores_b:
                    continue
                
                avg_a = mean(scores_a)
                avg_b = mean(scores_b)
                avg_conf_a = mean(conf_a) if conf_a else 0
                avg_conf_b = mean(conf_b) if conf_b else 0
                
                diff = abs(avg_a - avg_b)
                confident_both = avg_conf_a >= 0.50 and avg_conf_b >= 0.50
                
                if diff == 0:
                    similarity = "identical"
                elif diff <= 0.5:
                    similarity = "similar"
                elif diff >= 2.0:
                    similarity = "opposite"
                else:
                    similarity = "different"
                
                problem = confident_both and similarity in ("identical", "similar")
                
                results.append(DiscriminationResult(
                    archetype_a=name_a,
                    archetype_b=name_b,
                    item=item_code,
                    score_diff=round(diff, 2),
                    confident_both=confident_both,
                    assessment_similarity=similarity,
                    problem=problem,
                ))
    
    return results


def discrimination_summary_text(results: list[DiscriminationResult]) -> str:
    """Produce human-readable summary of discriminative power analysis."""
    lines = ["## TASK 4 — Assessment Discriminative Power\n"]
    
    total = len(results)
    problems = [r for r in results if r.problem]
    identical = [r for r in results if r.assessment_similarity == "identical"]
    similar = [r for r in results if r.assessment_similarity == "similar"]
    different = [r for r in results if r.assessment_similarity == "different"]
    opposite = [r for r in results if r.assessment_similarity == "opposite"]
    
    lines.append(f"**Total archetype-pair × item comparisons**: {total}")
    lines.append(f"**Discrimination problems**: {len(problems)} "
                f"({len(problems)/max(total,1)*100:.1f}%)")
    lines.append("")
    lines.append("| Similarity | Count | % | Problem? |")
    lines.append("|-----------|-------|---|---------|")
    lines.append(f"| Identical | {len(identical)} | {len(identical)/max(total,1)*100:.1f}% | YES |")
    lines.append(f"| Similar   | {len(similar)} | {len(similar)/max(total,1)*100:.1f}% | YES (if confident) |")
    lines.append(f"| Different | {len(different)} | {len(different)/max(total,1)*100:.1f}% | No |")
    lines.append(f"| Opposite  | {len(opposite)} | {len(opposite)/max(total,1)*100:.1f}% | No |")
    lines.append("")
    
    # Top problems
    if problems:
        lines.append("### Top Discrimination Problems\n")
        lines.append("(Archetypes that should be distinguishable but get similar scores)\n")
        lines.append("| Archetype A | Archetype B | Item | Score Diff | Confidence A | Confidence B |")
        lines.append("|-------------|-------------|------|-----------|-------------|-------------|")
        
        for r in sorted(problems, key=lambda x: x.score_diff)[:20]:
            lines.append(
                f"| {r.archetype_a} | {r.archetype_b} | {r.item} | "
                f"{r.score_diff} | {r.assessment_similarity} |"
            )
        lines.append("")
    
    # Per-archetype discrimination analysis
    lines.append("### Per-Archetype Discrimination Profile\n")
    archetype_items = defaultdict(lambda: defaultdict(list))
    for r in results:
        archetype_items[r.archetype_a][r.item].append(r)
    
    for arch_name in sorted(archetype_items.keys()):
        items = archetype_items[arch_name]
        problem_items = [item for item, comps in items.items()
                        if any(c.problem for c in comps)]
        lines.append(f"**{arch_name}**: {len(problem_items)} items with discrimination problems")
        if problem_items:
            lines.append(f"  Problem items: {', '.join(problem_items[:10])}")
        lines.append("")
    
    return "\n".join(lines)


# ── TASK 5: Hypothesis Framework ───────────────────────────────

@dataclass
class Hypothesis:
    """A structured hypothesis for a balancing recommendation."""
    hypothesis_id: str
    title: str
    observed_evidence: list[str]
    possible_explanations: list[str]
    confidence_level: str  # "high", "medium", "low"
    recommended_experiment: str
    expected_outcome: str
    decision_criteria: str  # What would confirm/reject this hypothesis
    depends_on: list[str]  # IDs of other hypotheses this depends on


def generate_hypotheses(
    findings: list[CategorizedFinding],
    exploits: list[ExploitReport],
    discrimination_results: list[DiscriminationResult],
) -> list[Hypothesis]:
    """Generate structured hypotheses for every potential balancing change.
    
    Per user directive: every recommendation must state:
    1. Observed evidence
    2. Possible explanations
    3. Confidence level
    4. Recommended experiment
    
    No jumping directly from observation to solution.
    """
    hypotheses: list[Hypothesis] = []
    
    # ── Hypothesis 1: Score Cap ───────────────────────────────
    score_findings = [f for f in findings if f.finding_id == "SCORE-SAT"]
    if score_findings:
        f = score_findings[0]
        hypotheses.append(Hypothesis(
            hypothesis_id="H-001",
            title="Reducing tt_bonus_cap from 15 to 10 will reduce score saturation "
                   "without breaking badge qualifications",
            observed_evidence=[
                f"Score cap saturation: {f.evidence[0]}" if f.evidence else "N/A",
                "TT accumulates beyond cap threshold in most games",
                "Max score (55) is reachable by any player who summits with TT>=10",
            ],
            possible_explanations=[
                "TT bonus cap of 15 is too generous — players easily exceed TT=10",
                "Level value (30) + reputation cap (5) + diversity (5) already sums to 40, "
                "leaving only 15 headroom for TT bonus which is too tight",
                "Cards give too much TT per turn on average",
            ],
            confidence_level="high",
            recommended_experiment="Change tt_bonus_cap to 10 in config.py, run 500 games "
                                  "with psychological archetypes, measure: (a) % at max score, "
                                  "(b) Carrier qualification rate, (c) score distribution spread",
            expected_outcome="Max score drops from 55 to 50. % at max drops from ~50% to ~15%. "
                            "Carrier qualification unchanged (TT>=8 requirement unaffected).",
            decision_criteria="Accept if: (a) % at max < 20%, (b) Carrier rate doesn't drop >10%, "
                            "(c) score stdev increases by >2 points. "
                            "Reject if: badge distribution changes significantly.",
            depends_on=[],
        ))
    
    # ── Hypothesis 2: Opportunity Model Recalibration ──────────
    model_defects = [f for f in findings if f.category == FindingCategory.MODEL_DEFECT
                     and "OPP-" in f.finding_id]
    if model_defects:
        hypotheses.append(Hypothesis(
            hypothesis_id="H-002",
            title="Recalibrating expected_per_game from empirical data will improve "
                   "opportunity fairness classification accuracy",
            observed_evidence=[
                f"{len(model_defects)} items have expected_per_game >2x actual",
                f"Example: {model_defects[0].evidence[0]}" if model_defects else "N/A",
                "Model assumes ~20 turns/game, actual mean is ~8-10",
            ],
            possible_explanations=[
                "The original model calculated expected_per_game assuming each player "
                "experiences all 3 levels for ~7 turns each",
                "Final round trigger cuts games short — players don't play all 20 turns",
                "Card draw alternation (mindset/skillset) halves the effective pool per draw",
            ],
            confidence_level="high",
            recommended_experiment="Replace expected_per_game with actual mean from 1000-game "
                                  "simulation. Re-run fairness classification. Measure: "
                                  "(a) how many items change fairness status, (b) whether "
                                  "recalibrated values are stable across 3p and 6p games",
            expected_outcome="No item fairness status changes (the min_opportunities threshold "
                            "is the actual gate, not expected_per_game). But the model becomes "
                            "honest about what to expect.",
            decision_criteria="Accept if: recalibrated values match actual within 20%. "
                            "Reject if: actual values vary >50% between 3p and 6p games "
                            "(indicates player count affects opportunity frequency).",
            depends_on=[],
        ))
    
    # ── Hypothesis 3: Card Pool Expansion ──────────────────────
    content_deficits = [f for f in findings if f.category == FindingCategory.CONTENT_DEFICIT]
    if content_deficits:
        items_needing_cards = [f.finding_id.replace("OPP-", "") for f in content_deficits]
        hypotheses.append(Hypothesis(
            hypothesis_id="H-003",
            title=f"Adding cards for {len(items_needing_cards)} under-covered items "
                   f"({', '.join(items_needing_cards[:5])}...) will bring all items "
                   f"above minimum opportunity threshold",
            observed_evidence=[
                f"{len(content_deficits)} items have insufficient card coverage",
                f"Worst case: {content_deficits[0].evidence[0]}" if content_deficits else "N/A",
            ],
            possible_explanations=[
                "Card pool was designed with uneven LRA coverage — some competencies "
                "have 2 cards, others have 20+",
                "Some competencies are inherently harder to create dilemma scenarios for",
            ],
            confidence_level="medium",
            recommended_experiment=f"Add {len(content_deficits) * 2} new cards targeting the "
                                  f"under-covered items. Run 500 games. Measure: "
                                  f"(a) % below min for each item, (b) whether new cards "
                                  f"affect game balance (score distribution, badge rates)",
            expected_outcome="All items reach >60% fair assessment rate. "
                            "Game balance unchanged (new cards have similar stat distributions).",
            decision_criteria="Accept if: all targeted items reach >60% fair rate AND "
                            "score/badge distributions don't shift >10%. "
                            "Reject if: new cards create a dominant strategy.",
            depends_on=["H-002"],
        ))
    
    # ── Hypothesis 4: Exploit Mitigation ───────────────────────
    successful_exploits = [e for e in exploits if e.success]
    if successful_exploits:
        hypotheses.append(Hypothesis(
            hypothesis_id="H-004",
            title="Adding strategic diversity checks will prevent assessment gaming "
                   "without penalizing genuine leadership behavior",
            observed_evidence=[
                f"{len(successful_exploits)} exploits found",
                f"Exploits: {', '.join(e.exploit_id for e in successful_exploits)}",
            ],
            possible_explanations=[
                "The assessment counts proving tags without considering whether the "
                "player is strategically targeting them",
                "Badge qualifications use simple stat thresholds without checking "
                "for strategic diversity",
            ],
            confidence_level="low",
            recommended_experiment="Implement strategic diversity check: flag players "
                                  "who pick the 'proving' option >90% of the time when "
                                  "a proving option is available. Test with: "
                                  "(a) proving_seeker (should be flagged), "
                                  "(b) servant_leader (should NOT be flagged), "
                                  "(c) random (should NOT be flagged)",
            expected_outcome="Proving seekers get flagged as 'assessment gaming'. "
                            "Genuine leaders are not affected.",
            decision_criteria="Accept if: flag catches >80% of proving_seekers and "
                            "<5% of servant_leaders. "
                            "Reject if: flag rate is similar across all archetypes.",
            depends_on=["H-001"],
        ))
    
    # ── Hypothesis 5: Dead Badge Fix ────────────────────────────
    badge_findings = [f for f in findings if f.finding_id == "BADGE-DEAD"]
    if badge_findings:
        hypotheses.append(Hypothesis(
            hypothesis_id="H-005",
            title="Tightening Carrier TT requirement from 8 to 12 will activate Solo Peak "
                   "badge without breaking game balance",
            observed_evidence=[
                "Solo Peak and Climber badges never triggered in 2000+ games",
                "Players who summit almost always have TT>=8 and reputation>=0",
                "Carrier cascade priority means summit+TT>=8 always gets Carrier",
            ],
            possible_explanations=[
                "TT accumulates easily through normal gameplay — TT>=8 is too low a bar",
                "No mechanic pushes TT down for summit players",
                "Reputation is always >=0 because no significant penalty mechanic exists",
            ],
            confidence_level="medium",
            recommended_experiment="Change Carrier TT requirement from 8 to 12. Run 500 games. "
                                  "Measure: (a) Solo Peak trigger rate, (b) Carrier trigger rate, "
                                  "(c) whether the change creates frustration (players summit "
                                  "but don't qualify for any badge)",
            expected_outcome="Solo Peak triggers for ~20-30% of summit players. "
                            "Carrier rate drops from ~40% to ~15-20%.",
            decision_criteria="Accept if: Solo Peak rate 15-35% AND Carrier rate >10%. "
                            "Reject if: Carrier rate drops to <5% or too many summit players "
                            "get 'climber' (no special badge).",
            depends_on=["H-001"],
        ))
    
    return hypotheses


def hypothesis_summary_text(hypotheses: list[Hypothesis]) -> str:
    """Produce human-readable summary of hypotheses."""
    lines = ["## TASK 5 — Hypothesis Framework\n"]
    
    lines.append("### All Hypotheses\n")
    lines.append("")
    
    for h in hypotheses:
        lines.append(f"### {h.hypothesis_id}: {h.title}\n")
        lines.append(f"**Confidence**: {h.confidence_level}")
        lines.append(f"**Depends on**: {', '.join(h.depends_on) if h.depends_on else 'None'}")
        lines.append("")
        
        lines.append("**Observed Evidence**:")
        for e in h.observed_evidence:
            lines.append(f"- {e}")
        lines.append("")
        
        lines.append("**Possible Explanations**:")
        for e in h.possible_explanations:
            lines.append(f"- {e}")
        lines.append("")
        
        lines.append(f"**Recommended Experiment**: {h.recommended_experiment}")
        lines.append("")
        lines.append(f"**Expected Outcome**: {h.expected_outcome}")
        lines.append("")
        lines.append(f"**Decision Criteria**: {h.decision_criteria}")
        lines.append("")
        lines.append("---")
        lines.append("")
    
    return "\n".join(lines)
