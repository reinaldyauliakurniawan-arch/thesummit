# The Summit — Meta-Validation Report

> **Purpose**: Validate the validity of the validation itself.
> **Methodology**: 5 meta-validation tasks applied to existing simulation data + new psychological archetype simulations.
> **Games simulated (this run)**: 500 with 10 archetypes × 4 players
> **Baseline data**: 1000 (3p) + 1000 (6p) optimizer games
> **Date**: 2026-07-29

---

## Executive Summary

The meta-validation framework analyzed 15 findings, tested 5 adversarial exploits, measured discriminative power across 10 psychological archetypes (1254 comparisons), and generated 3 structured hypotheses.

### Key Results

| Task | Result |
|------|--------|
| TASK 1: Root causes | {'Simulation Artifact': 8, 'Content Deficit': 2, 'Model Defect': 2, 'Progression Defect': 3} |
| TASK 2: Archetypes | 10 archetypes simulated, fingerprints generated |
| TASK 3: Adversarial | 0/5 exploits successful |
| TASK 4: Discrimination | 14.4% problem rate |
| TASK 5: Hypotheses | 3 hypotheses generated |

### Priority Actions

- **OPP-R1_S4** [CRITICAL]: LRA item R1_S4 (Personal Work System) is under-observed: 100.0% of games below minimum opportunity threshold (expected=0.7, actual=0.6)
  Root cause: content_deficit
  Recommendation: Add 2 cards tagging R1_S4 at the appropriate level.

- **OPP-R3_M1** [CRITICAL]: LRA item R3_M1 (Assess Leadership Quality) is under-observed: 95.1% of games below minimum opportunity threshold (expected=2.5, actual=0.3)
  Root cause: progression_defect
  Recommendation: Either: (a) slow progression to allow more turns at summit, or (b) add cards at OTHER levels that also tag R3_M1, or (c) accept limited assessment for this item.

- **OPP-R3_S1** [CRITICAL]: LRA item R3_S1 (Assessing Leadership) is under-observed: 100.0% of games below minimum opportunity threshold (expected=0.5, actual=0.1)
  Root cause: content_deficit
  Recommendation: Add 2 cards tagging R3_S1 at the appropriate level.

- **OPP-R3_S4** [CRITICAL]: LRA item R3_S4 (Strategy Translation) is under-observed: 95.13% of games below minimum opportunity threshold (expected=1.75, actual=0.3)
  Root cause: progression_defect
  Recommendation: Either: (a) slow progression to allow more turns at summit, or (b) add cards at OTHER levels that also tag R3_S4, or (c) accept limited assessment for this item.

- **OPP-R2_S1** [HIGH]: LRA item R2_S1 (Job Design & Delegation) is under-observed: 80.6% of games below minimum opportunity threshold (expected=2.0, actual=0.8)
  Root cause: simulation_artifact
  Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.

- **OPP-R2_S2** [HIGH]: LRA item R2_S2 (Selecting/Deselecting) is under-observed: 89.87% of games below minimum opportunity threshold (expected=2.3, actual=0.5)
  Root cause: simulation_artifact
  Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.

- **OPP-R2_S6** [HIGH]: LRA item R2_S6 (Coaching) is under-observed: 74.7% of games below minimum opportunity threshold (expected=5.5, actual=1.6)
  Root cause: model_defect
  Recommendation: Recalculate expected_per_game from simulation data. Replace theoretical 5.5 with empirical 1.6.

- **OPP-R2_S9** [HIGH]: LRA item R2_S9 (Upward/Cross Communication) is under-observed: 71.97% of games below minimum opportunity threshold (expected=5.2, actual=1.9)
  Root cause: simulation_artifact
  Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.

- **OPP-R3_S3** [HIGH]: LRA item R3_S3 (Developing Leaders) is under-observed: 82.07% of games below minimum opportunity threshold (expected=3.75, actual=0.7)
  Root cause: progression_defect
  Recommendation: Either: (a) slow progression to allow more turns at summit, or (b) add cards at OTHER levels that also tag R3_S3, or (c) accept limited assessment for this item.

- **SCORE-SAT** [HIGH]: Score cap saturation: 51.9% of players hit maximum score
  Root cause: model_defect
  Recommendation: Reduce tt_bonus_cap from 15 to 10 in config/summit.php and config.py

---

## TASK 1 — Root Cause Categorization

| Category | Count | Actionable |
|----------|-------|-----------|
| Model Defect | 2 | 2 |
| Content Deficit | 2 | 2 |
| Progression Defect | 3 | 3 |
| Simulation Artifact | 8 | 0 |

### Model Defect (2 findings)

**SCORE-SAT** — Score cap saturation: 51.9% of players hit maximum score
- Severity: high
- Confidence in root cause: 85%
- Evidence: 3-player: 51.9% at max score; Max score: 55; Mean score: 45.0
- Possible explanations: tt_bonus_cap (15) is too easy to reach; Level value (30 at summit) dominates scoring; Diversity bonus (0-5) inflates summit scores
- Actionable: Yes
- Recommendation: Reduce tt_bonus_cap from 15 to 10 in config/summit.php and config.py
- Experiment: Reduce tt_bonus_cap to 10 and re-run 500 games

**OPP-R2_S6** — LRA item R2_S6 (Coaching) is under-observed: 74.7% of games below minimum opportunity threshold (expected=5.5, actual=1.6)
- Severity: high
- Confidence in root cause: 65%
- Evidence: Expected per game: 5.5; Actual mean: 1.6; Ratio: 0.30x
- Possible explanations: Model defect: expected_per_game formula is wrong; Simulation artifact: agent behavior causes bias
- Actionable: Yes
- Recommendation: Recalculate expected_per_game from simulation data. Replace theoretical 5.5 with empirical 1.6.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

### Content Deficit (2 findings)

**OPP-R1_S4** — LRA item R1_S4 (Personal Work System) is under-observed: 100.0% of games below minimum opportunity threshold (expected=0.7, actual=0.6)
- Severity: critical
- Confidence in root cause: 90%
- Evidence: Expected per game: 0.7; Actual mean: 0.6; Ratio: 0.90x
- Possible explanations: Content deficit: not enough cards
- Actionable: Yes
- Recommendation: Add 2 cards tagging R1_S4 at the appropriate level.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R3_S1** — LRA item R3_S1 (Assessing Leadership) is under-observed: 100.0% of games below minimum opportunity threshold (expected=0.5, actual=0.1)
- Severity: critical
- Confidence in root cause: 90%
- Evidence: Expected per game: 0.5; Actual mean: 0.1; Ratio: 0.15x
- Possible explanations: Content deficit: not enough cards
- Actionable: Yes
- Recommendation: Add 2 cards tagging R3_S1 at the appropriate level.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

### Progression Defect (3 findings)

**OPP-R3_M1** — LRA item R3_M1 (Assess Leadership Quality) is under-observed: 95.1% of games below minimum opportunity threshold (expected=2.5, actual=0.3)
- Severity: critical
- Confidence in root cause: 75%
- Evidence: Expected per game: 2.5; Actual mean: 0.3; Ratio: 0.12x
- Possible explanations: Progression defect: game moves too fast through level; Model defect: expected_per_game formula is wrong
- Actionable: Yes
- Recommendation: Either: (a) slow progression to allow more turns at summit, or (b) add cards at OTHER levels that also tag R3_M1, or (c) accept limited assessment for this item.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R3_S3** — LRA item R3_S3 (Developing Leaders) is under-observed: 82.07% of games below minimum opportunity threshold (expected=3.75, actual=0.7)
- Severity: high
- Confidence in root cause: 75%
- Evidence: Expected per game: 3.75; Actual mean: 0.7; Ratio: 0.19x
- Possible explanations: Progression defect: game moves too fast through level; Model defect: expected_per_game formula is wrong
- Actionable: Yes
- Recommendation: Either: (a) slow progression to allow more turns at summit, or (b) add cards at OTHER levels that also tag R3_S3, or (c) accept limited assessment for this item.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R3_S4** — LRA item R3_S4 (Strategy Translation) is under-observed: 95.13% of games below minimum opportunity threshold (expected=1.75, actual=0.3)
- Severity: critical
- Confidence in root cause: 75%
- Evidence: Expected per game: 1.75; Actual mean: 0.3; Ratio: 0.16x
- Possible explanations: Progression defect: game moves too fast through level
- Actionable: Yes
- Recommendation: Either: (a) slow progression to allow more turns at summit, or (b) add cards at OTHER levels that also tag R3_S4, or (c) accept limited assessment for this item.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

### Simulation Artifact (8 findings)

**OPP-PtP_M1** — LRA item PtP_M1 (Integritas di Bawah Tekanan) is under-observed: 50.83% of games below minimum opportunity threshold (expected=5.6, actual=2.4)
- Severity: medium
- Confidence in root cause: 50%
- Evidence: Expected per game: 5.6; Actual mean: 2.4; Ratio: 0.43x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R2_M2** — LRA item R2_M2 (Value Managerial Work) is under-observed: 54.67% of games below minimum opportunity threshold (expected=3.2, actual=1.5)
- Severity: medium
- Confidence in root cause: 50%
- Evidence: Expected per game: 3.2; Actual mean: 1.5; Ratio: 0.48x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R2_S1** — LRA item R2_S1 (Job Design & Delegation) is under-observed: 80.6% of games below minimum opportunity threshold (expected=2.0, actual=0.8)
- Severity: high
- Confidence in root cause: 50%
- Evidence: Expected per game: 2.0; Actual mean: 0.8; Ratio: 0.39x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R2_S2** — LRA item R2_S2 (Selecting/Deselecting) is under-observed: 89.87% of games below minimum opportunity threshold (expected=2.3, actual=0.5)
- Severity: high
- Confidence in root cause: 50%
- Evidence: Expected per game: 2.3; Actual mean: 0.5; Ratio: 0.23x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R2_S3** — LRA item R2_S3 (Performance Monitoring) is under-observed: 61.23% of games below minimum opportunity threshold (expected=3.2, actual=1.3)
- Severity: medium
- Confidence in root cause: 50%
- Evidence: Expected per game: 3.2; Actual mean: 1.3; Ratio: 0.40x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R2_S8** — LRA item R2_S8 (Team Workflow/SOP) is under-observed: 53.67% of games below minimum opportunity threshold (expected=4.8, actual=1.5)
- Severity: medium
- Confidence in root cause: 50%
- Evidence: Expected per game: 4.8; Actual mean: 1.5; Ratio: 0.31x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R2_S9** — LRA item R2_S9 (Upward/Cross Communication) is under-observed: 71.97% of games below minimum opportunity threshold (expected=5.2, actual=1.9)
- Severity: high
- Confidence in root cause: 50%
- Evidence: Expected per game: 5.2; Actual mean: 1.9; Ratio: 0.37x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

**OPP-R3_M2** — LRA item R3_M2 (Decisive Under Uncertainty) is under-observed: 51.23% of games below minimum opportunity threshold (expected=3.0, actual=1.6)
- Severity: medium
- Confidence in root cause: 50%
- Evidence: Expected per game: 3.0; Actual mean: 1.6; Ratio: 0.55x
- Possible explanations: Simulation artifact: agent behavior causes bias
- Actionable: No
- Recommendation: Run validation with psychological archetypes (TASK 2) to determine if real-human decision patterns produce different opportunity frequencies. If under-observation persists, reclassify as content_deficit.
- Experiment: Run 500 games with 10 psychological archetypes. If opportunity frequency changes significantly (>20%), reclassify as simulation_artifact.

---

## TASK 2 — Psychological Archetype Fingerprints

### Methodology

Replaced 14 optimizer-based strategy profiles with 10 psychologically realistic player archetypes. Each archetype models how real humans make decisions — with biases, inconsistencies, stress responses, and context-dependent behavior. The key question: **Can the LRA assessment distinguish these archetypes from each other?**

### Archetype LRA Score Matrix

Average suggested score per LRA item per archetype (only items with >50% fair assessment rate shown):

| Item | conflict_avo | consensus_se | controller | hero_syndrom | micromanager | opportunist | people_pleas | perfectionis | political_pl | servant_lead |
|------|------|------|------|------|------|------|------|------|------|------|
| PtP_M2 | 1.6 | 1.7 | 1.3 | 1.4 | 1.1 | 1.2 | 3.0 | 2.7 | 2.7 | 2.3 |
| PtP_M3 | 2.6 | 2.2 | 2.3 | 2.9 | 2.3 | 2.2 | 2.9 | 2.7 | 2.9 | 2.9 |
| PtP_M4 | 1.2 | 1.8 | 1.8 | 2.9 | 2.3 | 2.1 | 1.5 | 1.5 | 1.2 | 1.6 |
| PtP_M5 | 2.6 | 2.5 | 1.2 | 1.1 | 1.3 | 3.0 | 3.1 | 4.2 | 3.2 | 3.3 |
| PtP_S1 | 2.5 | 2.4 | 1.6 | 1.6 | 1.1 | 2.8 | 3.0 | 2.4 | 2.5 | 3.0 |
| PtP_S2 | 2.5 | 2.3 | 2.8 | 1.6 | 2.4 | 2.6 | 1.9 | 2.8 | 2.8 | 2.0 |
| R1_M1 | 2.6 | 2.3 | 3.0 | 2.5 | 2.6 | 2.5 | 2.2 | 2.5 | 3.0 | 1.1 |
| R1_M2 | 1.8 | 1.7 | 2.0 | 2.5 | 2.7 | 1.7 | 2.5 | 1.8 | 1.6 | 1.5 |
| R1_S1 | 1.1 | 1.5 | 2.0 | 2.5 | 1.9 | 2.2 | 1.0 | 1.1 | 1.5 | 1.0 |
| R1_S2 | 2.9 | 2.9 | 3.0 | 1.0 | 1.0 | 2.9 | 3.0 | 3.0 | 3.0 | 3.0 |
| R2_M1 | 2.8 | 2.2 | 1.2 | 1.4 | 2.6 | 2.7 | 1.9 | 2.7 | 2.9 | 2.2 |
| R2_S3 | 2.1 | 2.8 | 3.0 | 2.1 | 3.0 | 1.4 | 2.8 | 2.0 | 2.1 | 2.8 |
| R2_S4 | 2.4 | 2.5 | 1.7 | 2.1 | 2.7 | 2.8 | 1.7 | 3.1 | 2.6 | 2.3 |
| R2_S5 | 1.2 | 2.8 | 1.4 | 1.4 | 1.1 | 2.2 | 2.9 | 3.3 | 2.1 | 2.6 |

### Per-Archetype Profile

#### Conflict Avoider

**Expected high scores**: PtP_M5 (actual: 2.6), PtP_M2 (actual: 1.57), R2_S5 (actual: 1.18)
**Expected low scores**: PtP_S2 (actual: 2.47), R2_S4 (actual: 2.41)
**Expected contradictory**: R3_M2 (actual quality: {'insufficient': 244, 'weak': 61, 'medium': 2})

**Actual top 3**: R2_S1 (3.0), R2_S7 (3.0), R2_S8 (3.0)
**Actual bottom 3**: R2_S9 (1.1), R1_S1 (1.1), R2_S2 (1.0)

#### Consensus Seeker

**Expected high scores**: PtP_M2 (actual: 1.73), R2_S5 (actual: 2.82)
**Expected low scores**: PtP_S2 (actual: 2.32), R3_M2 (actual: 1.1)
**Expected contradictory**: R1_M2 (actual quality: {'insufficient': 181, 'weak': 135})

**Actual top 3**: R2_S1 (3.0), R2_S2 (3.0), R1_S2 (2.9)
**Actual bottom 3**: R2_S6 (1.4), R3_M2 (1.1), R3_S2 (1.0)

#### Controller

**Expected high scores**: PtP_M4 (actual: 1.81), R1_M1 (actual: 2.98), R1_M2 (actual: 2)
**Expected low scores**: PtP_M5 (actual: 1.19), R2_S5 (actual: 1.44)
**Expected contradictory**: R2_M1 (actual quality: {'weak': 136, 'insufficient': 124, 'medium': 25, 'strong': 1})

**Actual top 3**: R1_S2 (3.0), R1_S3 (3.0), R2_S1 (3.0)
**Actual bottom 3**: R2_M1 (1.2), PtP_M5 (1.2), R3_S2 (1.0)

#### Hero Syndrome

**Expected high scores**: R3_M2 (actual: 1.52), PtP_M1 (actual: 1.09)
**Expected low scores**: R2_S8 (actual: 2.73), R1_S3 (actual: 2)
**Expected contradictory**: R2_S3 (actual quality: {'weak': 121, 'insufficient': 165, 'medium': 5})

**Actual top 3**: R2_S2 (3.0), PtP_M4 (2.9), PtP_M3 (2.9)
**Actual bottom 3**: R1_S2 (1.0), R3_S2 (1.0), R3_S4 (1.0)

#### Micromanager

**Expected high scores**: R2_S3 (actual: 2.98), R2_S6 (actual: 2.45), PtP_S1 (actual: 1.1)
**Expected low scores**: R2_M1 (actual: 2.59), R2_S1 (actual: 2.97)
**Expected contradictory**: R3_S5 (actual quality: {'insufficient': 202, 'strong': 52, 'weak': 32, 'medium': 12})

**Actual top 3**: R1_S3 (3.0), R2_S3 (3.0), R2_S1 (3.0)
**Actual bottom 3**: R3_S2 (1.0), R3_S3 (1.0), R3_S4 (1.0)

#### Opportunist

**Expected high scores**: 
**Expected low scores**: 
**Expected contradictory**: PtP_M1 (actual quality: {'insufficient': 263, 'weak': 34, 'medium': 15}), PtP_M2 (actual quality: {'medium': 102, 'weak': 194, 'contradictory': 13, 'strong': 3}), R1_M1 (actual quality: {'insufficient': 249, 'weak': 63}), R2_M1 (actual quality: {'weak': 98, 'insufficient': 190, 'medium': 23, 'strong': 1}), R2_S4 (actual quality: {'insufficient': 180, 'medium': 103, 'weak': 21, 'strong': 8})

**Actual top 3**: R2_S2 (3.0), R3_M1 (3.0), R3_S4 (3.0)
**Actual bottom 3**: R3_M2 (1.2), PtP_M2 (1.2), R3_S2 (1.2)

#### People Pleaser

**Expected high scores**: PtP_M5 (actual: 3.12), R2_S5 (actual: 2.9), R2_S6 (actual: 3)
**Expected low scores**: PtP_M4 (actual: 1.49), R1_M1 (actual: 2.25)
**Expected contradictory**: R3_M2 (actual quality: {'weak': 134, 'medium': 58, 'insufficient': 102})

**Actual top 3**: PtP_M5 (3.1), PtP_M2 (3.0), R1_S2 (3.0)
**Actual bottom 3**: R1_S1 (1.0), R1_S3 (1.0), R3_S2 (1.0)

#### Perfectionist

**Expected high scores**: R1_S1 (actual: 1.05), R2_S3 (actual: 2.01), R2_S8 (actual: 2.53)
**Expected low scores**: PtP_M3 (actual: 2.72), R3_M2 (actual: 1.1)
**Expected contradictory**: PtP_M1 (actual quality: {'insufficient': 203, 'weak': 77, 'medium': 18})

**Actual top 3**: PtP_M5 (4.2), R2_S5 (3.3), R2_S4 (3.1)
**Actual bottom 3**: R3_S2 (1.3), R3_M2 (1.1), R1_S1 (1.1)

#### Political Player

**Expected high scores**: R2_S5 (actual: 2.06), R2_S9 (actual: 4.17), PtP_M2 (actual: 2.74)
**Expected low scores**: PtP_M1 (actual: 1.24), R3_M2 (actual: 1.04)
**Expected contradictory**: R2_S4 (actual quality: {'insufficient': 131, 'weak': 69, 'medium': 92, 'strong': 5})

**Actual top 3**: R2_S9 (4.2), PtP_M5 (3.2), R1_S3 (3.0)
**Actual bottom 3**: PtP_M4 (1.2), R3_M2 (1.0), R3_S2 (1.0)

#### Servant Leader

**Expected high scores**: PtP_M5 (actual: 3.32), R2_M1 (actual: 2.22), R2_S5 (actual: 2.63), R2_S6 (actual: 2.97)
**Expected low scores**: 

**Actual top 3**: PtP_M5 (3.3), PtP_S1 (3.0), R2_S2 (3.0)
**Actual bottom 3**: R1_M1 (1.1), R1_S1 (1.0), R1_S3 (1.0)

---

## TASK 3 — Adversarial Assessment Testing

### Exploit Attempts


| ID | Strategy | Target | Success? | Severity | Evidence Summary |
|----|----------|--------|----------|----------|-----------------|
| ADV-001 | proving_seeker | ALL | NO | medium | total_players_tested=200; high_score_instances=107; avg_high_score_per_player=0.535 |
| ADV-002 | diversity_seeker | Badge: the_strategist | NO | low | total_players_tested=200; strategist_badges=13; strategist_pct=6.5 |
| ADV-003 | altruist | Badge: the_catalyst | NO | medium | total_players_tested=200; catalyst_badges=39; catalyst_pct=19.5 |
| ADV-004 | proving_seeker + disproving_seeker mix | ALL (contradiction detection) | NO | low | total_players_tested=200; contradictory_items=2; total_assessed_items=2690 |
| ADV-005 | random | ALL | NO | low | total_players_tested=200; accidental_high_scores=16; avg_accidental_per_player=0.08 |

**Exploits found**: 0 of 5 tested

---

## TASK 4 — Assessment Discriminative Power

**Total archetype-pair × item comparisons**: 1254
**Discrimination problems**: 180 (14.4%)

| Similarity | Count | % | Problem? |
|-----------|-------|---|---------|
| Identical | 69 | 5.5% | YES |
| Similar   | 580 | 46.3% | YES (if confident) |
| Different | 566 | 45.1% | No |
| Opposite  | 39 | 3.1% | No |

### Top Discrimination Problems

(Archetypes that should be distinguishable but get similar scores)

| Archetype A | Archetype B | Item | Score Diff | Confidence A | Confidence B |
|-------------|-------------|------|-----------|-------------|-------------|
| political_player | perfectionist | PtP_S2 | 0.0 | similar |
| conflict_avoider | political_player | PtP_M4 | 0.01 | similar |
| controller | consensus_seeker | PtP_M4 | 0.01 | similar |
| people_pleaser | servant_leader | PtP_S1 | 0.02 | similar |
| controller | servant_leader | R1_S2 | 0.02 | similar |
| hero_syndrome | political_player | PtP_M3 | 0.02 | similar |
| people_pleaser | political_player | PtP_M3 | 0.03 | similar |
| people_pleaser | political_player | R3_S5 | 0.03 | similar |
| people_pleaser | servant_leader | PtP_M3 | 0.03 | similar |
| people_pleaser | perfectionist | PtP_M4 | 0.03 | similar |
| micromanager | servant_leader | R2_S9 | 0.03 | similar |
| controller | hero_syndrome | R2_S5 | 0.03 | similar |
| controller | political_player | R1_M1 | 0.03 | similar |
| conflict_avoider | consensus_seeker | R2_S4 | 0.04 | similar |
| micromanager | controller | PtP_M3 | 0.04 | similar |
| micromanager | hero_syndrome | R2_S9 | 0.04 | similar |
| micromanager | consensus_seeker | PtP_S2 | 0.04 | similar |
| opportunist | consensus_seeker | PtP_M3 | 0.04 | similar |
| controller | political_player | PtP_S2 | 0.05 | similar |
| controller | perfectionist | PtP_S2 | 0.05 | similar |

### Per-Archetype Discrimination Profile

**conflict_avoider**: 9 items with discrimination problems
  Problem items: PtP_M2, PtP_M3, PtP_M4, PtP_M5, PtP_S2, R2_M1, R2_S4, R2_S5, R2_S6

**controller**: 12 items with discrimination problems
  Problem items: PtP_M2, PtP_M3, PtP_M4, PtP_M5, PtP_S2, R1_M1, R1_M2, R1_S2, R2_M2, R2_S5

**hero_syndrome**: 6 items with discrimination problems
  Problem items: PtP_M2, PtP_M3, PtP_S2, R2_S4, R2_S6, R2_S9

**micromanager**: 10 items with discrimination problems
  Problem items: PtP_M2, PtP_M3, PtP_M4, PtP_M5, PtP_S2, R1_M1, R2_S4, R2_S5, R2_S8, R2_S9

**opportunist**: 5 items with discrimination problems
  Problem items: PtP_M1, PtP_M3, PtP_M4, PtP_S2, R2_S4

**people_pleaser**: 11 items with discrimination problems
  Problem items: PtP_M1, PtP_M2, PtP_M3, PtP_M4, PtP_M5, PtP_S1, PtP_S2, R2_S5, R2_S6, R2_S9

**perfectionist**: 8 items with discrimination problems
  Problem items: PtP_M3, PtP_M4, PtP_S1, PtP_S2, R2_S4, R2_S5, R2_S6, R2_S9

**political_player**: 9 items with discrimination problems
  Problem items: PtP_M2, PtP_M3, PtP_M4, PtP_M5, PtP_S2, R2_S4, R2_S5, R2_S6, R3_S5

**servant_leader**: 8 items with discrimination problems
  Problem items: PtP_M1, PtP_M2, PtP_M3, PtP_M4, PtP_M5, PtP_S1, PtP_S2, R2_S5

---

## TASK 5 — Hypothesis Framework

### All Hypotheses


### H-001: Reducing tt_bonus_cap from 15 to 10 will reduce score saturation without breaking badge qualifications

**Confidence**: high
**Depends on**: None

**Observed Evidence**:
- Score cap saturation: 3-player: 51.9% at max score
- TT accumulates beyond cap threshold in most games
- Max score (55) is reachable by any player who summits with TT>=10

**Possible Explanations**:
- TT bonus cap of 15 is too generous — players easily exceed TT=10
- Level value (30) + reputation cap (5) + diversity (5) already sums to 40, leaving only 15 headroom for TT bonus which is too tight
- Cards give too much TT per turn on average

**Recommended Experiment**: Change tt_bonus_cap to 10 in config.py, run 500 games with psychological archetypes, measure: (a) % at max score, (b) Carrier qualification rate, (c) score distribution spread

**Expected Outcome**: Max score drops from 55 to 50. % at max drops from ~50% to ~15%. Carrier qualification unchanged (TT>=8 requirement unaffected).

**Decision Criteria**: Accept if: (a) % at max < 20%, (b) Carrier rate doesn't drop >10%, (c) score stdev increases by >2 points. Reject if: badge distribution changes significantly.

---

### H-002: Recalibrating expected_per_game from empirical data will improve opportunity fairness classification accuracy

**Confidence**: high
**Depends on**: None

**Observed Evidence**:
- 1 items have expected_per_game >2x actual
- Example: Expected per game: 5.5
- Model assumes ~20 turns/game, actual mean is ~8-10

**Possible Explanations**:
- The original model calculated expected_per_game assuming each player experiences all 3 levels for ~7 turns each
- Final round trigger cuts games short — players don't play all 20 turns
- Card draw alternation (mindset/skillset) halves the effective pool per draw

**Recommended Experiment**: Replace expected_per_game with actual mean from 1000-game simulation. Re-run fairness classification. Measure: (a) how many items change fairness status, (b) whether recalibrated values are stable across 3p and 6p games

**Expected Outcome**: No item fairness status changes (the min_opportunities threshold is the actual gate, not expected_per_game). But the model becomes honest about what to expect.

**Decision Criteria**: Accept if: recalibrated values match actual within 20%. Reject if: actual values vary >50% between 3p and 6p games (indicates player count affects opportunity frequency).

---

### H-003: Adding cards for 2 under-covered items (R1_S4, R3_S1...) will bring all items above minimum opportunity threshold

**Confidence**: medium
**Depends on**: H-002

**Observed Evidence**:
- 2 items have insufficient card coverage
- Worst case: Expected per game: 0.7

**Possible Explanations**:
- Card pool was designed with uneven LRA coverage — some competencies have 2 cards, others have 20+
- Some competencies are inherently harder to create dilemma scenarios for

**Recommended Experiment**: Add 4 new cards targeting the under-covered items. Run 500 games. Measure: (a) % below min for each item, (b) whether new cards affect game balance (score distribution, badge rates)

**Expected Outcome**: All items reach >60% fair assessment rate. Game balance unchanged (new cards have similar stat distributions).

**Decision Criteria**: Accept if: all targeted items reach >60% fair rate AND score/badge distributions don't shift >10%. Reject if: new cards create a dominant strategy.

---

## Appendix A — Psychological Archetype Descriptions

| Archetype | Decision Pattern | Key Bias | Expected Blind Spot |
|-----------|-----------------|----------|---------------------|
| conflict_avoider | Avoids confrontation options, prefers empathy | See description file | Decisiveness, tough conversations |
| consensus_seeker | Alternates options to appear balanced | See description file | Assertiveness, decisive action |
| controller | Maximizes own stats, sees TT as secondary | See description file | Team engagement, empathy |
| hero_syndrome | Takes risky/dramatic options, especially under stress | See description file | Systems/process, consistency |
| micromanager | Prefers control/oversight, resists delegation | See description file | Delegation, adaptability, cross-org |
| opportunist | Switches strategy based on recent outcomes | See description file | Consistency (many contradictions) |
| people_pleaser | Maximizes benefit to others at personal cost | See description file | Own progression, decisiveness |
| perfectionist | Minimizes variance, avoids stat loss | See description file | Risk-taking, learning from failure |
| political_player | Maximizes reputation, avoids visible risk | See description file | Risk-taking, integrity under pressure |
| servant_leader | Balances team benefit with personal capability | See description file | None (ideal profile) |

## Appendix B — Methodology

### Root Cause Categorization Rules (TASK 1)

Every finding is tested against 4 hypotheses before classification:
1. **Content deficit**: cards_tagging <= 3 AND >70% below minimum
2. **Progression defect**: cards exist but game moves too fast through the level
3. **Model defect**: many cards exist but expected_per_game is still way off
4. **Simulation artifact**: under-observed but no game mechanic explains it

The hypothesis with the highest confidence is selected as the root cause.
If multiple hypotheses tie, the finding flags ambiguity.

### Psychological Archetype Design (TASK 2)

Each archetype uses a scoring function with these components:
- **Stat weighting**: How much each stat (MP/SP/TT) matters to them
- **Tag preference**: Which behavior_tags they seek or avoid
- **Stress response**: How stress level modifies their decisions
- **Inconsistency rate**: % chance of random choice (models distraction)
- **Noise level**: Gaussian noise on scores (models imperfect evaluation)

### Adversarial Testing Protocol (TASK 3)

5 exploit strategies are tested:
1. **Proving farmer**: Maximizes proving LRA tags
2. **Diversity hacker**: Maximizes behavior dimension count
3. **Altruist score hacker**: Maximizes TT for Catalyst badge
4. **Contradiction bomb**: Mixes proving/disproving to force 'mixed' scores
5. **Random walk baseline**: Checks if random play gets high scores

An exploit is 'successful' if it produces assessment outcomes that don't match the player's actual behavioral pattern.
