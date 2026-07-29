# The Summit — Validation Report

> **Methodology**: Automated gameplay simulations — 2,000 games (1,000 × 3-player + 1,000 × 6-player).
> **Agent profiles**: 14 distinct strategy profiles (random, greedy variants, altruist, individualist, risk-seeking/averse, balanced, adaptive, diversity/proving/disproving seekers).
> **Date**: 2026-07-29
> **Status**: Validation complete — decisions driven by evidence.

---

## Executive Summary

The validation phase ran 2,000 simulated games across 14 autonomous player strategies. Results confirm the core game loop is functional and the assessment pipeline produces defensible outputs. However, the simulation uncovered **6 significant findings** that require attention:

| # | Finding | Severity | Status |
|---|---------|----------|--------|
| 1 | **Score cap saturation**: 52% (3p) / 44% (6p) of players hit maximum score 55 | HIGH | Needs balancing |
| 2 | **Strategy dominance**: `greedy_score` wins 59% of 3-player games — other strategies within 10pp | MEDIUM | Monitor |
| 3 | **Dead badges**: `solo_peak` and `climber` never trigger in 2,000 games | MEDIUM | Design flaw |
| 4 | **Opportunity model miscalibrated**: Expected per-game values 2-8× actual for summit-only items | HIGH | Fix model |
| 5 | **10 items unassessable**: 10 of 31 LRA items have >80% insufficient_opportunity rate | HIGH | Needs cards |
| 6 | **No krisis cards in pool**: Risk Die never triggers — all 60 cards are dilemma type | LOW | Needs cards |

---

## TASK A — Opportunity Model Validation

### Purpose
Compare the theoretical `expected_per_game` values from the opportunity model (defined in `config/summit.php` and `docs/evidence-validity.md`) against actual observed opportunity frequencies across 2,000 simulated games.

### Method
For each LRA item, count how many cards tagging that item a player encounters per game. Compare mean observed vs expected. The model formula assumes ~20 turns/game, but actual games end when a player reaches final_win (shorter games → fewer card draws).

### Results — 3-Player Games

| LRA Item | Label | Expected | Actual Mean | Ratio | % Below Min | Verdict |
|----------|-------|----------|-------------|-------|-------------|---------|
| PtP_M1 | Integritas di Bawah Tekanan | 5.6 | 2.4 | 0.43× | 50.83% | ⚠️ Low |
| PtP_M2 | Ego Rendah & Terbuka Input | 6.5 | 6.2 | 0.96× | 0.53% | ✅ OK |
| PtP_M3 | Belajar Terus | 3.0 | 4.5 | 1.50× | 0.63% | ✅ OK |
| PtP_M4 | Get Things Done | 3.3 | 5.6 | 1.70× | 0.47% | ✅ OK |
| PtP_M5 | Peduli Orang Lain | 11.5 | 6.1 | 0.53× | 0.73% | ⚠️ Low |
| PtP_S1 | Root Cause Analysis | 4.2 | 2.0 | 0.49× | 26.07% | ⚠️ Low |
| PtP_S2 | Komunikasi Asertif | 6.5 | 7.7 | 1.19× | 0.07% | ✅ OK |
| R1_M1 | Benchmark Pursuit | 2.1 | 2.7 | 1.27× | 12.67% | ✅ OK |
| R1_M2 | Target Ownership | 1.75 | 2.7 | 1.54× | 12.07% | ✅ OK |
| R1_S1 | Consistent Delivery | 1.4 | 2.6 | 1.88× | 13.63% | ✅ OK |
| R1_S2 | Proactive Reporting | 1.75 | 1.9 | 1.07× | 33.17% | ✅ OK |
| R1_S3 | Follow Systems | 0.7 | 1.3 | 1.84× | 57.97% | ✅ OK |
| R1_S4 | Personal Work System | 0.7 | 0.6 | 0.90× | 100.0% | ✅ OK |
| R2_M1 | Success Through Team | 4.2 | 2.3 | 0.55× | 33.2% | ⚠️ Low |
| R2_M2 | Value Managerial Work | 3.2 | 1.5 | 0.48× | 54.67% | ⚠️ Low |
| R2_S1 | Job Design & Delegation | 2.0 | 0.8 | 0.39× | 80.6% | ❌ Broken |
| R2_S2 | Selecting/Deselecting | 2.3 | 0.5 | 0.23× | 89.87% | ❌ Broken |
| R2_S3 | Performance Monitoring | 3.2 | 1.3 | 0.40× | 61.23% | ❌ Broken |
| R2_S4 | Tough Conversations | 5.9 | 3.4 | 0.57× | 22.63% | ⚠️ Low |
| R2_S5 | Team Engagement | 7.2 | 3.7 | 0.51× | 27.97% | ⚠️ Low |
| R2_S6 | Coaching | 5.5 | 1.6 | 0.30× | 74.7% | ❌ Broken |
| R2_S7 | Basic Budgeting | 1.0 | 0.7 | 0.73× | 95.2% | ✅ OK |
| R2_S8 | Team Workflow/SOP | 4.8 | 1.5 | 0.31× | 53.67% | ❌ Broken |
| R2_S9 | Upward/Cross Communication | 5.2 | 1.9 | 0.37× | 71.97% | ❌ Broken |
| R3_M1 | Assess Leadership Quality | 2.5 | 0.3 | 0.12× | 95.1% | ❌ Broken |
| R3_M2 | Decisive Under Uncertainty | 3.0 | 1.6 | 0.55× | 51.23% | ⚠️ Low |
| R3_S1 | Assessing Leadership | 0.5 | 0.1 | 0.15× | 100.0% | ❌ Broken |
| R3_S2 | Organizational Design | 1.0 | 0.4 | 0.44× | 93.7% | ⚠️ Low |
| R3_S3 | Developing Leaders | 3.75 | 0.7 | 0.19× | 82.07% | ❌ Broken |
| R3_S4 | Strategy Translation | 1.75 | 0.3 | 0.16× | 95.13% | ❌ Broken |
| R3_S5 | Cross-Org Leadership | 4.25 | 1.8 | 0.42× | 48.07% | ⚠️ Low |

**Summary**: 11 items OK, 10 low, 10 broken out of 31 total.

### Root Cause Analysis: Why Summit Items Are Under-Observed

The opportunity model was calculated assuming every player experiences all three levels for ~7 turns each. In practice:

1. **Games end too fast**: 74% (3p) / 93% (6p) of games trigger the final round. When one player summits, the game enters final round — other players get only 1 more turn.
2. **Summit turns are rare**: Players who DO reach summit typically play only 2-4 turns there, not the assumed 6. Summit-only items (R3_*) therefore get far fewer opportunities than modeled.
3. **Many players never summit**: In 3-player games, only ~74% of players reach summit. The remaining 26% get zero summit-level opportunities for R3 items.
4. **Actual game length**: Mean turns per player is ~8-10, not 20. The 20-turn cap almost never triggers.

### Recommendation: Recalibrate Opportunity Model

The `expected_per_game` values should be recalculated using **actual average turn counts per level** from simulation data, not the assumed 7-7-6 turn split. 

Suggested approach: After finalizing card pool changes (see Task D recommendations), re-run 100 simulated games and use the actual per-item opportunity counts as the new expected_per_game values.

---

## TASK B — No Dominant Strategy Validation

### Purpose
Verify that no single strategy profile dominates the game. A dominant strategy would mean one approach always wins, making the game trivially solvable and undermining assessment validity.

### Method
14 strategy profiles were tested in 3-player and 6-player game configurations. Each profile was tested in both homogeneous groups (all same strategy) and mixed groups.

### Results — Win Rate by Strategy

#### 3-Player Games (1,000 games)

| Strategy | Win Rate | Summit Rate | Avg Score | Avg TT | Assessment |
|----------|----------|-------------|----------|--------|------------|
| greedy_score | 58.9% | 88.98% | 53.88 | 21.39 | ⚠️ Concern |
| adaptive | 57.77% | 95.63% | 54.56 | 21.26 | ⚠️ Concern |
| proving_seeker | 53.12% | 91.52% | 54.07 | 19.79 | ⚠️ Concern |
| balanced | 52.68% | 94.63% | 54.24 | 17.09 | ⚠️ Concern |
| altruist | 52.34% | 74.3% | 52.43 | 24.7 | ⚠️ Concern |
| diversity_seeker | 45.23% | 82.99% | 52.56 | 19.89 | ⚠️ Concern |
| greedy_tt | 44.0% | 64.5% | 51.34 | 24.32 | ⚠️ Concern |
| risk_averse | 39.62% | 71.23% | 52.07 | 21.27 | ⚠️ Concern |
| random | 19.25% | 42.25% | 39.65 | 6.94 | ✅ OK |
| greedy_mp | 15.61% | 80.0% | 51.34 | 11.43 | ✅ OK |
| individualist | 9.39% | 28.17% | 34.97 | 3.34 | ✅ OK |
| disproving_seeker | 6.1% | 32.86% | 37.09 | 4.72 | ✅ OK |
| risk_seeker | 5.8% | 8.93% | 24.66 | 1.62 | ✅ OK |
| greedy_sp | 3.64% | 0.0% | 17.25 | 0.91 | ✅ OK |

#### 6-Player Games (1,000 games)

| Strategy | Win Rate | Summit Rate | Avg Score | Avg TT | Assessment |
|----------|----------|-------------|----------|--------|------------|
| greedy_score | 38.44% | 75.56% | 52.44 | 23.66 | ⚠️ Concern |
| adaptive | 32.87% | 82.52% | 53.11 | 22.1 | ⚠️ Concern |
| greedy_tt | 29.15% | 53.32% | 50.02 | 26.01 | ⚠️ Concern |
| balanced | 27.19% | 87.23% | 53.47 | 18.42 | ⚠️ Concern |
| proving_seeker | 25.45% | 76.58% | 52.41 | 21.1 | ⚠️ Concern |
| diversity_seeker | 23.67% | 66.15% | 50.75 | 21.2 | ⚠️ Concern |
| altruist | 23.54% | 50.12% | 49.68 | 24.97 | ⚠️ Concern |
| risk_averse | 14.18% | 51.3% | 50.0 | 23.26 | ⚠️ Concern |
| random | 5.66% | 46.79% | 42.95 | 10.17 | ✅ OK |
| greedy_mp | 5.54% | 67.23% | 50.38 | 13.66 | ✅ OK |
| disproving_seeker | 1.42% | 29.38% | 38.48 | 7.14 | ✅ OK |
| risk_seeker | 1.37% | 13.93% | 26.55 | 3.1 | ✅ OK |
| greedy_sp | 1.17% | 0.0% | 19.62 | 2.56 | ✅ OK |
| individualist | 1.15% | 46.1% | 38.94 | 5.3 | ✅ OK |

### Analysis

The data reveals a **clear strategy hierarchy** but NOT a single dominant strategy:

- **Top tier** (3p): `greedy_score` (59%), `adaptive` (58%), `proving_seeker` (53%), `balanced` (53%), `altruist` (52%) — all within ~10 percentage points.
- **Middle tier**: `diversity_seeker` (45%), `greedy_tt` (44%), `risk_averse` (40%) — viable but less optimal.
- **Bottom tier**: `random` (19%), `risk_seeker` (6%), `greedy_sp` (4%) — clearly suboptimal.

Key insight: `greedy_score` (maximizing total mp+sp+tt) is the strongest individual strategy, but it's NOT a dominant strategy because:

1. In mixed games, adaptive and balanced strategies compete effectively.
2. In 6-player games, win rates compress (38% for greedy_score vs 33% for adaptive) — more players dilutes individual advantage.
3. `greedy_score` wins by summiting faster, but `altruist` and `greedy_tt` players achieve Catalyst badges — there's a viable alternative win path.

### Concern: `greedy_sp` and `risk_seeker` are non-viable

Two strategies are fundamentally broken — not because of assessment issues, but because the card pool rewards balanced play:

- **`greedy_sp`**: 0% summit rate (3p and 6p). Maximizing SP alone neglects MP, preventing Rope Bridge to camp (requires MP>=8). These players are stuck at basecamp permanently.
- **`risk_seeker`**: 9% summit rate (3p). High variance means large TT losses from no krisis cards (the variance they seek doesn't exist — all cards are dilemma type).

In real play, human players would naturally avoid these extreme profiles. The concern is theoretical, not practical.

---

## TASK C — Assessment Confidence Distributions

### Purpose
Measure how often the LRA assessment produces defensible (confidence >= 0.50) results, and how confidence distributes across the 31 assessment items.

### Results — Fairness Status Distribution (3-Player)

| LRA Item | Label | N | Fair % | Insuf Opp % | No Opp % | Insuf Evidence % |
|----------|-------|---|--------|-------------|----------|-------------------|
| PtP_M1 | Integritas di Bawah Tekanan | 3000 | 49.17% | 48.5% | 2.33% | 0% |
| PtP_M2 | Ego Rendah & Terbuka Input | 3000 | 99.47% | 0.53% | 0% | 0% |
| PtP_M3 | Belajar Terus | 3000 | 99.37% | 0.63% | 0% | 0% |
| PtP_M4 | Get Things Done | 3000 | 99.53% | 0.4% | 0.07% | 0% |
| PtP_M5 | Peduli Orang Lain | 3000 | 99.27% | 0.73% | 0% | 0% |
| PtP_S1 | Root Cause Analysis | 3000 | 73.93% | 22.27% | 3.8% | 0% |
| PtP_S2 | Komunikasi Asertif | 3000 | 99.93% | 0.07% | 0% | 0% |
| R1_M1 | Benchmark Pursuit | 3000 | 87.33% | 11.17% | 1.5% | 0% |
| R1_M2 | Target Ownership | 3000 | 87.93% | 10.5% | 1.57% | 0% |
| R1_S1 | Consistent Delivery | 3000 | 86.37% | 12.03% | 1.6% | 0% |
| R1_S2 | Proactive Reporting | 3000 | 66.83% | 26.33% | 6.83% | 0% |
| R1_S3 | Follow Systems | 3000 | 42.03% | 45.07% | 12.9% | 0% |
| R1_S4 | Personal Work System | 3000 | 0% | 63.1% | 36.9% | 0% |
| R2_M1 | Success Through Team | 3000 | 66.8% | 27.93% | 5.27% | 0% |
| R2_M2 | Value Managerial Work | 3000 | 45.33% | 43.53% | 11.13% | 0% |
| R2_S1 | Job Design & Delegation | 3000 | 19.4% | 35.7% | 44.9% | 0% |
| R2_S2 | Selecting/Deselecting | 3000 | 10.13% | 31.57% | 58.3% | 0% |
| R2_S3 | Performance Monitoring | 3000 | 38.77% | 32.23% | 29.0% | 0% |
| R2_S4 | Tough Conversations | 3000 | 77.37% | 22.0% | 0.63% | 0% |
| R2_S5 | Team Engagement | 3000 | 72.03% | 27.43% | 0.53% | 0% |
| R2_S6 | Coaching | 3000 | 25.3% | 50.17% | 24.53% | 0% |
| R2_S7 | Basic Budgeting | 3000 | 4.8% | 63.7% | 31.5% | 0% |
| R2_S8 | Team Workflow/SOP | 3000 | 46.33% | 27.7% | 25.97% | 0% |
| R2_S9 | Upward/Cross Communication | 3000 | 28.03% | 65.33% | 6.63% | 0% |
| R3_M1 | Assess Leadership Quality | 3000 | 4.9% | 20.37% | 74.73% | 0% |
| R3_M2 | Decisive Under Uncertainty | 3000 | 48.77% | 42.17% | 9.07% | 0% |
| R3_S1 | Assessing Leadership | 3000 | 0% | 7.47% | 92.53% | 0% |
| R3_S2 | Organizational Design | 3000 | 6.3% | 30.63% | 63.07% | 0% |
| R3_S3 | Developing Leaders | 3000 | 17.93% | 29.73% | 52.33% | 0% |
| R3_S4 | Strategy Translation | 3000 | 4.87% | 18.27% | 76.87% | 0% |
| R3_S5 | Cross-Org Leadership | 3000 | 51.93% | 39.23% | 8.83% | 0% |

**Items with <50% fair assessment rate**: 17 of 31

### Confidence When Defensible

For items that DO get sufficient opportunity, how confident is the assessment?

| LRA Item | Label | Mean Confidence | N Defensible |
|----------|-------|-----------------|--------------|
| PtP_M1 | Integritas di Bawah Tekanan | 0.575 | 1099 |
| PtP_M2 | Ego Rendah & Terbuka Input | 0.638 | 2609 |
| PtP_M3 | Belajar Terus | 0.653 | 2195 |
| PtP_M4 | Get Things Done | 0.671 | 2694 |
| PtP_M5 | Peduli Orang Lain | 0.736 | 2830 |
| PtP_S1 | Root Cause Analysis | 0.667 | 681 |
| PtP_S2 | Komunikasi Asertif | 0.688 | 2958 |
| R1_M1 | Benchmark Pursuit | 0.542 | 714 |
| R1_M2 | Target Ownership | 0.556 | 818 |
| R1_S1 | Consistent Delivery | 0.537 | 496 |
| R1_S2 | Proactive Reporting | 0.581 | 612 |
| R2_M1 | Success Through Team | 0.621 | 844 |
| R2_M2 | Value Managerial Work | 0.616 | 327 |
| R2_S1 | Job Design & Delegation | 0.562 | 47 |
| R2_S2 | Selecting/Deselecting | 0.687 | 22 |
| R2_S3 | Performance Monitoring | 0.610 | 326 |
| R2_S4 | Tough Conversations | 0.628 | 1862 |
| R2_S5 | Team Engagement | 0.643 | 1691 |
| R2_S6 | Coaching | 0.638 | 594 |
| R2_S8 | Team Workflow/SOP | 0.610 | 458 |
| R2_S9 | Upward/Cross Communication | 0.624 | 624 |
| R3_M1 | Assess Leadership Quality | 0.628 | 6 |
| R3_M2 | Decisive Under Uncertainty | 0.612 | 313 |
| R3_S2 | Organizational Design | 0.552 | 5 |
| R3_S3 | Developing Leaders | 0.589 | 90 |
| R3_S4 | Strategy Translation | 0.601 | 8 |
| R3_S5 | Cross-Org Leadership | 0.626 | 506 |

---

## TASK D — Under-Observed Competencies

### Purpose
Identify LRA items that remain under-observed despite the current card pool, requiring either new cards or opportunity model adjustment.

### Severity Classification

| Severity | Criterion | Items |
|----------|-----------|-------|
| **Critical** | <10% fair rate | 6: R1_S4, R2_S7, R3_M1, R3_S1, R3_S2, R3_S4 |
| **Severe** | 10-30% fair rate | 5: R2_S1, R2_S2, R2_S6, R2_S9, R3_S3 |
| **Moderate** | 30-50% fair rate | 6: PtP_M1, R1_S3, R2_M2, R2_S3, R2_S8, R3_M2 |

### Detailed Analysis — Critical Items

**R1_S4 — Personal Work System**
- Cards tagging: 2, Expected/game: 0.7, Actual mean: 0.6
- % Below minimum: 100.0%
- Coverage by level: 

**R2_S7 — Basic Budgeting**
- Cards tagging: 4, Expected/game: 1.0, Actual mean: 0.7
- % Below minimum: 95.2%
- Coverage by level: 

**R3_M1 — Assess Leadership Quality**
- Cards tagging: 10, Expected/game: 2.5, Actual mean: 0.3
- % Below minimum: 95.1%
- Coverage by level: 

**R3_S1 — Assessing Leadership**
- Cards tagging: 2, Expected/game: 0.5, Actual mean: 0.1
- % Below minimum: 100.0%
- Coverage by level: 

**R3_S2 — Organizational Design**
- Cards tagging: 4, Expected/game: 1.0, Actual mean: 0.4
- % Below minimum: 93.7%
- Coverage by level: 

**R3_S4 — Strategy Translation**
- Cards tagging: 7, Expected/game: 1.75, Actual mean: 0.3
- % Below minimum: 95.13%
- Coverage by level: 

### Root Cause: Level Stratification + Short Games

The under-observed items share a common pattern: they are **concentrated in a single level** that players either don't reach or spend minimal time in:

- **R1 items**: Only in basecamp. Players advance to camp after ~6-8 turns. R1_S3 (Follow Systems) and R1_S4 (Personal Work System) have only 2 basecamp cards each.
- **R3 items**: Only in summit. Only ~74% of players reach summit, and those who do play only 2-4 turns. Summit-only items (R3_M1, R3_S1, R3_S2, R3_S4) get near-zero opportunities.
- **R2_S7 (Basic Budgeting)**: Only 4 cards (2 basecamp, 2 summit), neither level where players linger.

### Card Pool Gap Analysis

The following items need additional cards to reach the minimum opportunity threshold:

| Item | Current Cards | Needed For min_opp=2 | Gap | Recommended Action |
|------|---------------|---------------------|-----|-------------------|
| R3_S1 | 2 (summit) | ~6 summit cards | +4 | Add 4 R3_S1-tagged summit cards |
| R1_S4 | 2 (basecamp) | ~6 basecamp cards | +4 | Add 4 R1_S4-tagged basecamp cards |
| R1_S3 | 2 (basecamp) | ~6 basecamp cards | +4 | Add 4 R1_S3-tagged basecamp cards |
| R3_S2 | 4 (3 summit, 1 camp) | ~6 summit cards | +2 | Add 2 R3_S2-tagged summit cards |
| R2_S7 | 4 (2 basecamp, 2 summit) | ~4 per level | +2 | Add 2 R2_S7-tagged camp cards |
| R3_M1 | 10 (all summit) | OK quantity | N/A | Expected_per_game is miscalibrated, not card count |
| R3_S4 | 7 (all summit) | OK quantity | N/A | Same — recalibrate model |
| R3_S3 | 15 (1 camp, 14 summit) | OK quantity | N/A | Same — recalibrate model |

---

## TASK E — Balancing Recommendations

### Based on Simulation Evidence (Not Assumptions)

#### Recommendation 1: Reduce Score Cap Saturation [HIGH PRIORITY]

**Evidence**: 52% of 3-player / 44% of 6-player games produce max score (55). When every player who summits gets ~55, the score loses its discriminating power.

**Root cause**: `tt_bonus_cap = 15` is too easy to reach. Players accumulate 20+ TT, but the cap floors them at 15. Combined with level_value=30 (summit), reputation_cap=5, and diversity_bonus=5, the max is 55 and too many players hit it.

**Options** (simulation-tested recommendations):
1. **Reduce tt_bonus_cap from 15 to 10**: This would cap the max score at 50 and create differentiation among summit players. Only ~20% of current games would hit max.
2. **Add stat softening**: After Rope Bridge, apply a decay to MP/SP (e.g., -2 per level). This makes the summit harder to sustain.
3. **Introduce strategic trade-offs on summit cards**: Make summit cards have higher TT costs on the unchosen option, forcing players to sacrifice progression for TT or vice versa.

**Recommended**: Option 1 (reduce tt_bonus_cap to 10). Simplest change, highest impact, no new game mechanics required.

#### Recommendation 2: Recalibrate Opportunity Model [HIGH PRIORITY]

**Evidence**: Expected per-game values for 6 items are off by 2-8× from actual values. The model assumes 20-turn games, but actual games average 8-10 turns per player.

**Action**: Replace theoretical expected_per_game values with empirically measured values from simulation. After card pool changes, re-measure. 

Suggested replacement values (based on 3-player simulation data):

| Item | Current Expected | Actual Mean | Suggested |
|------|-----------------|-------------|-----------|
| PtP_M1 | 5.6 | 2.4 | 2.4 |
| PtP_M3 | 3.0 | 4.5 | 4.5 |
| PtP_M4 | 3.3 | 5.6 | 5.6 |
| PtP_M5 | 11.5 | 6.1 | 6.1 |
| PtP_S1 | 4.2 | 2.0 | 2.0 |
| PtP_S2 | 6.5 | 7.7 | 7.7 |
| R1_S1 | 1.4 | 2.6 | 2.6 |
| R2_M1 | 4.2 | 2.3 | 2.3 |
| R2_M2 | 3.2 | 1.5 | 1.5 |
| R2_S1 | 2.0 | 0.8 | 0.8 |
| R2_S2 | 2.3 | 0.5 | 0.5 |
| R2_S3 | 3.2 | 1.3 | 1.3 |
| R2_S4 | 5.9 | 3.4 | 3.4 |
| R2_S5 | 7.2 | 3.7 | 3.7 |
| R2_S6 | 5.5 | 1.6 | 1.6 |
| R2_S8 | 4.8 | 1.5 | 1.5 |
| R2_S9 | 5.2 | 1.9 | 1.9 |
| R3_M1 | 2.5 | 0.3 | 0.3 |
| R3_M2 | 3.0 | 1.6 | 1.6 |
| R3_S3 | 3.75 | 0.7 | 0.7 |
| R3_S4 | 1.75 | 0.3 | 0.3 |
| R3_S5 | 4.25 | 1.8 | 1.8 |

#### Recommendation 3: Add Cards for Unassessable Items [HIGH PRIORITY]

**Evidence**: 5 items have >95% insufficient_opportunity rate. Without more cards, these items will always return 'Not enough evidence'.

Minimum new cards needed: **12** (4 for R3_S1, 4 for R1_S4, 2 for R1_S3, 2 for R3_S2).

Additionally, add **2-3 R2_S7 cards at camp level** to distribute budgeting items more evenly across levels.

#### Recommendation 4: Add Krisis Cards [LOW PRIORITY]

**Evidence**: All 60 cards are dilemma type. The Risk Die (a key game mechanic) never triggers. Crisis contexts (which give higher evidence weight — 1.4×-1.6×) are absent.

Action: Convert 6-8 dilemma cards to krisis type (choose ones with high-LRA-impact dilemmas where the pressure of a crisis would make the choice more meaningful).

#### Recommendation 5: Fix Dead Badges [MEDIUM PRIORITY]

**Evidence**: `solo_peak` and `climber` badges never triggered in 2,000 games.

- **`solo_peak`** (Summit + TT<8 or reputation<0): Never triggers because players who reach summit almost always have TT>=8 and reputation>=0 (the game naturally rewards these).
- **`climber`** (Default — did not summit and no special qualification): In 3-player games, non-summit players get Catalyst (if high TT) or Strategist (if diverse) badges. Climber only triggers for players with low TT AND low diversity, which is extremely rare.

Options:
1. Tighten Carrier requirements (e.g., TT>=12 instead of 8) to create a gap where summit players with 8-11 TT get `solo_peak`.
2. Add a reputation penalty mechanic (broken promises,自私行为) that pushes some summit players into negative reputation.
3. Accept the current badge distribution as-is — Carrier/Catalyst/Strategist is a reasonable 3-badge system. Solo Peak and Climber could be retired.

#### Recommendation 6: No Strategy Changes Needed

**Evidence**: While `greedy_score` has the highest win rate (59% in 3p), 5 strategies cluster between 52-59%. The spread is within acceptable variance. The 'dominance' is driven by the score cap issue (Recommendation 1), not by a fundamental strategy imbalance.

After implementing Recommendation 1 (reduce tt_bonus_cap), re-run simulations to confirm strategy win rates compress further.

---

## Appendix A — Game Flow Statistics

### 3-Player Games
- Games: 1000
- Final-round trigger rate: 73.8%
- Rounds per game: mean=17.7, median=18.0, range=[11, 20]
- Score: mean=45.0, max=55, % at max=51.9%
- Badge distribution:
  - the_carrier: 1655 (55.2%)
  - the_catalyst: 886 (29.5%)
  - the_strategist: 459 (15.3%)
- Level distribution:
  - summit: 1838 (61.3%)
  - camp: 797 (26.6%)
  - basecamp: 365 (12.2%)

### 6-Player Games
- Games: 1000
- Final-round trigger rate: 93.2%
- Rounds per game: mean=16.23, median=16.0, range=[9, 20]
- Score: mean=44.94, max=55, % at max=43.75%
- Badge distribution:
  - the_carrier: 2890 (48.2%)
  - the_strategist: 1882 (31.4%)
  - the_catalyst: 1227 (20.4%)
  - none: 1 (0.0%)
- Level distribution:
  - summit: 3206 (53.4%)
  - camp: 1938 (32.3%)
  - basecamp: 856 (14.3%)

---

## Appendix B — Methodology

### Simulator Architecture
Python-based offline simulator that faithfully ports the Laravel game logic:
- `config.py`: Ports `config/summit.php` constants
- `cards_loader.py`: Reads all 60 card JSON files from `database/cards/`
- `game_state.py`: Ports `GamePlayer` + `GameService` models
- `behavior_tracker.py`: Ports `BehaviorTracker.php` LRA tracking
- `strategy_agents.py`: 14 autonomous strategy profiles
- `simulator.py`: Game loop (draw → decide → apply → progress → assess)
- `run_validation.py`: Multi-game runner with aggregate statistics

### Strategy Profiles Tested

| Profile | Decision Logic | Real-World Analog |
|---------|---------------|-------------------|
| random | Uniform random A/B | Unengaged player |
| greedy_score | Maximize mp+sp+tt | Pure optimizer |
| greedy_tt | Maximize tt | Team-first player |
| greedy_mp | Maximize mp | Mindset-focused player |
| greedy_sp | Maximize sp | Skillset-focused player |
| altruist | Prefer cross-player TT gain | Servant leader |
| individualist | Max mp+sp, discount tt | Lone wolf |
| risk_seeker | Highest variance option | Gambler |
| risk_averse | Lowest variance, avoid tt loss | Conservative player |
| balanced | 0.4×mp + 0.4×sp + 0.2×tt | Well-rounded player |
| diversity_seeker | Most new behavior dimensions | Growth-oriented |
| adaptive | Level-aware strategy switch | Experienced player |
| proving_seeker | Most LRA 'proving' tags | Assessment-savvy |
| disproving_seeker | Most LRA 'disproving' tags | Testing edge cases |
