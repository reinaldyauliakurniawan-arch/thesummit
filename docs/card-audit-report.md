# The Summit v2 - Card Audit Report

**Date**: 2026-07-28 | **Cards**: 60 | **Auditor**: Automated Analysis

## Summary

| Verdict | Count | % |
|---------|-------|---|
| GOOD | 24 | 40% |
| REDESIGN | 26 | 43% |
| WEAK | 10 | 17% |

- Optimizable (math solver): 26
- Genuine leader struggle: 24
- Weak evidence: 5

## Critical Finding: The Game Is A Quiz, Not A Simulation

An optimizer can solve 95%+ of cards with one rule: **pick the option with higher TT**.
Option A (collaborative) almost always has >= TT. Option B (selfish) almost always has -TT.
This creates a morality play, not a dilemma. Real leaders face choices where BOTH options cost something.

## Structural Problems

### 1. Dominant Option Pattern
Option A = good leader (+TT), Option B = bad leader (-TT). No real tension.

### 2. Missing Consequence Types
All 52 cards use ONLY MP/SP/TT. Missing from design docs:
- Reputation changes, Relationship changes, Locked/unlocked choices
- Promises, Debts, Future events, Hidden information, Cross-player effects

### 3. No Cross-Player Effects
Zero cards affect other players. The game is single-player with shared scoring.

### 4. No Hidden Information
Zero cards have hidden_info. Everything visible upfront. Target: 10/25/40% by level.

### 5. No Delayed/Conditional Effects
Zero cards use ScheduleEvent or ConditionalTrigger. All consequences are immediate.

### 6. No Emotional Peaks
No card forces choosing between friendship and performance, or helping another at personal cost.

### 7. Identical/Trivial Options

- **BM_N05**: IDENTICAL stats - no real choice | A: MP+1 SP+0 TT+1 | B: MP+1 SP+0 TT+1
- **BM_K02**: B dominates in all stats | A: MP-1 SP+0 TT+0 | B: MP+2 SP+0 TT+1
- **BS_K03**: A dominates in all stats | A: MP+0 SP+1 TT+2 | B: MP+0 SP+1 TT-2
- **CM_N05**: A dominates in all stats | A: MP+1 SP+1 TT+1 | B: MP+1 SP+0 TT+0
- **CM_K01**: A dominates in all stats | A: MP+2 SP+0 TT+1 | B: MP-1 SP+0 TT-2
- **CM_K02**: A dominates in all stats | A: MP+1 SP+1 TT+1 | B: MP+0 SP+0 TT-2
- **CM_K04**: A dominates in all stats | A: MP+1 SP+1 TT+1 | B: MP+0 SP+1 TT-2
- **CS_N02**: A dominates in all stats | A: MP+0 SP+2 TT+1 | B: MP+0 SP+2 TT+0
- **CS_N04**: A dominates in all stats | A: MP+0 SP+1 TT+1 | B: MP+0 SP+1 TT+0
- **CS_N06**: A dominates in all stats | A: MP+0 SP+2 TT+1 | B: MP+0 SP+1 TT+0
- **CS_K01**: A dominates in all stats | A: MP+1 SP+1 TT+2 | B: MP+0 SP+1 TT-2
- **CS_K02**: A dominates in all stats | A: MP+0 SP+2 TT+1 | B: MP+0 SP+1 TT-2
- **CS_K03**: A dominates in all stats | A: MP+0 SP+2 TT+1 | B: MP+0 SP+1 TT-1
- **CS_K04**: A dominates in all stats | A: MP+0 SP+1 TT+1 | B: MP+0 SP+1 TT-2
- **SM_K01**: Total gap 5 - one option clearly superior | A: MP+1 SP+0 TT+2 | B: MP+0 SP+1 TT-3
- **SM_K02**: Total gap 4 - one option clearly superior | A: MP+1 SP+0 TT+2 | B: MP+0 SP+1 TT-2
- **SM_K03**: Total gap 5 - one option clearly superior | A: MP+1 SP+0 TT+3 | B: MP+0 SP+1 TT-2
- **SM_K04**: A dominates in all stats | A: MP+1 SP+0 TT+1 | B: MP+0 SP+0 TT-2
- **SM_K05**: Total gap 4 - one option clearly superior | A: MP+1 SP+0 TT+1 | B: MP+0 SP+1 TT-3
- **SS_N01**: A dominates in all stats | A: MP+0 SP+2 TT+1 | B: MP+0 SP+2 TT+0
- **SS_N03**: A dominates in all stats | A: MP+0 SP+2 TT+1 | B: MP+0 SP+2 TT+0
- **SS_K01**: A dominates in all stats | A: MP+1 SP+1 TT+2 | B: MP+0 SP+1 TT-3
- **SS_K02**: Total gap 4 - one option clearly superior | A: MP+0 SP+1 TT+2 | B: MP+0 SP+2 TT-3
- **SS_K03**: A dominates in all stats | A: MP+1 SP+1 TT+2 | B: MP+0 SP+0 TT-2
- **SS_K05**: A dominates in all stats | A: MP+0 SP+2 TT+1 | B: MP+0 SP+1 TT+0
- **SS_K06**: Total gap 6 - one option clearly superior | A: MP+1 SP+1 TT+3 | B: MP+0 SP+2 TT-3

## Per-Card Details

| ID | A | B | Verdict | Evidence | Struggle? |
|----|---|---|---------|---------|----------|
| BM_N01 | MP+2 SP+0 TT+0 | MP+0 SP+1 TT+0 | GOOD | moderate | Yes |
| BM_N02 | MP+1 SP+0 TT+1 | MP+1 SP+1 TT+0 | WEAK | moderate | No |
| BM_N03 | MP+0 SP+1 TT+0 | MP+2 SP+0 TT+0 | GOOD | moderate | Yes |
| BM_N04 | MP+2 SP+1 TT+0 | MP+1 SP+1 TT+1 | WEAK | moderate | No |
| BM_N05 | MP+1 SP+0 TT+1 | MP+1 SP+0 TT+1 | REDESIGN | weak | No |
| BM_N06 | MP+2 SP+1 TT+0 | MP+0 SP+2 TT+0 | GOOD | moderate | Yes |
| BM_K01 | MP+1 SP+0 TT+1 | MP+0 SP+1 TT-2 | GOOD | rich | Yes |
| BM_K02 | MP-1 SP+0 TT+0 | MP+2 SP+0 TT+1 | REDESIGN | rich | No |
| BM_K03 | MP+2 SP+0 TT+0 | MP+1 SP+1 TT-1 | GOOD | rich | Yes |
| BM_K04 | MP+2 SP+0 TT+1 | MP+0 SP+1 TT+0 | GOOD | rich | Yes |
| BS_N01 | MP+0 SP+2 TT+0 | MP+0 SP+1 TT+1 | WEAK | moderate | No |
| BS_N02 | MP+0 SP+2 TT+0 | MP+1 SP+1 TT+0 | WEAK | moderate | No |
| BS_N03 | MP+0 SP+2 TT+0 | MP+0 SP+1 TT+1 | WEAK | moderate | No |
| BS_N04 | MP+0 SP+2 TT+0 | MP+0 SP+1 TT+1 | WEAK | moderate | No |
| BS_N05 | MP+0 SP+1 TT+1 | MP-1 SP+2 TT+0 | GOOD | rich | Yes |
| BS_N06 | MP+0 SP+3 TT+0 | MP+1 SP+1 TT+0 | GOOD | moderate | Yes |
| BS_K01 | MP+0 SP+1 TT+1 | MP+0 SP+2 TT-2 | GOOD | rich | Yes |
| BS_K02 | MP+1 SP+0 TT+1 | MP-1 SP+2 TT-1 | GOOD | rich | Yes |
| BS_K03 | MP+0 SP+1 TT+2 | MP+0 SP+1 TT-2 | REDESIGN | rich | No |
| BS_K04 | MP+1 SP+1 TT+1 | MP-1 SP+2 TT-1 | GOOD | rich | Yes |
| CM_N01 | MP+1 SP+0 TT+2 | MP+0 SP+1 TT+0 | GOOD | rich | Yes |
| CM_N02 | MP+1 SP+0 TT+1 | MP+0 SP+2 TT+0 | WEAK | rich | No |
| CM_N03 | MP+2 SP+0 TT+1 | MP+1 SP+1 TT+0 | GOOD | rich | Yes |
| CM_N04 | MP+1 SP+0 TT+1 | MP+0 SP+1 TT+1 | WEAK | moderate | No |
| CM_N05 | MP+1 SP+1 TT+1 | MP+1 SP+0 TT+0 | REDESIGN | moderate | No |
| CM_N06 | MP+0 SP+1 TT+2 | MP+0 SP+2 TT+0 | GOOD | moderate | Yes |
| CM_K01 | MP+2 SP+0 TT+1 | MP-1 SP+0 TT-2 | REDESIGN | rich | No |
| CM_K02 | MP+1 SP+1 TT+1 | MP+0 SP+0 TT-2 | REDESIGN | rich | No |
| CM_K03 | MP+1 SP+0 TT+2 | MP+0 SP+1 TT-1 | GOOD | rich | Yes |
| CM_K04 | MP+1 SP+1 TT+1 | MP+0 SP+1 TT-2 | REDESIGN | rich | No |
| CS_N01 | MP+0 SP+1 TT+2 | MP+0 SP+2 TT+0 | GOOD | moderate | Yes |
| CS_N02 | MP+0 SP+2 TT+1 | MP+0 SP+2 TT+0 | REDESIGN | weak | No |
| CS_N03 | MP+0 SP+2 TT+0 | MP+0 SP+1 TT+1 | WEAK | moderate | No |
| CS_N04 | MP+0 SP+1 TT+1 | MP+0 SP+1 TT+0 | REDESIGN | weak | No |
| CS_N05 | MP+0 SP+1 TT+1 | MP+0 SP+2 TT+0 | WEAK | moderate | No |
| CS_N06 | MP+0 SP+2 TT+1 | MP+0 SP+1 TT+0 | REDESIGN | moderate | No |
| CS_K01 | MP+1 SP+1 TT+2 | MP+0 SP+1 TT-2 | REDESIGN | rich | No |
| CS_K02 | MP+0 SP+2 TT+1 | MP+0 SP+1 TT-2 | REDESIGN | rich | No |
| CS_K03 | MP+0 SP+2 TT+1 | MP+0 SP+1 TT-1 | REDESIGN | rich | No |
| CS_K04 | MP+0 SP+1 TT+1 | MP+0 SP+1 TT-2 | REDESIGN | rich | No |
| SM_N01 | MP+1 SP+0 TT+2 | MP+0 SP+2 TT+0 | GOOD | rich | Yes |
| SM_N02 | MP+1 SP+0 TT+2 | MP+0 SP+2 TT+0 | GOOD | rich | Yes |
| SM_N03 | MP+1 SP+1 TT+2 | MP+0 SP+2 TT+0 | GOOD | rich | Yes |
| SM_N04 | MP+0 SP+1 TT+2 | MP+0 SP+2 TT+0 | GOOD | moderate | Yes |
| SM_K01 | MP+1 SP+0 TT+2 | MP+0 SP+1 TT-3 | REDESIGN | rich | No |
| SM_K02 | MP+1 SP+0 TT+2 | MP+0 SP+1 TT-2 | REDESIGN | rich | No |
| SM_K03 | MP+1 SP+0 TT+3 | MP+0 SP+1 TT-2 | REDESIGN | rich | No |
| SM_K04 | MP+1 SP+0 TT+1 | MP+0 SP+0 TT-2 | REDESIGN | rich | No |
| SM_K05 | MP+1 SP+0 TT+1 | MP+0 SP+1 TT-3 | REDESIGN | rich | No |
| SM_K06 | MP+1 SP+1 TT+1 | MP+2 SP+0 TT+0 | GOOD | rich | Yes |
| SS_N01 | MP+0 SP+2 TT+1 | MP+0 SP+2 TT+0 | REDESIGN | weak | No |
| SS_N02 | MP+0 SP+1 TT+2 | MP+0 SP+2 TT+0 | GOOD | moderate | Yes |
| SS_N03 | MP+0 SP+2 TT+1 | MP+0 SP+2 TT+0 | REDESIGN | weak | No |
| SS_N04 | MP+1 SP+1 TT+1 | MP+0 SP+2 TT-1 | GOOD | rich | Yes |
| SS_K01 | MP+1 SP+1 TT+2 | MP+0 SP+1 TT-3 | REDESIGN | rich | No |
| SS_K02 | MP+0 SP+1 TT+2 | MP+0 SP+2 TT-3 | REDESIGN | rich | No |
| SS_K03 | MP+1 SP+1 TT+2 | MP+0 SP+0 TT-2 | REDESIGN | rich | No |
| SS_K04 | MP+0 SP+1 TT+2 | MP+0 SP+2 TT-2 | GOOD | rich | Yes |
| SS_K05 | MP+0 SP+2 TT+1 | MP+0 SP+1 TT+0 | REDESIGN | rich | No |
| SS_K06 | MP+1 SP+1 TT+3 | MP+0 SP+2 TT-3 | REDESIGN | rich | No |

## Recommendations

1. **REBALANCE stats** so neither option dominates in all 3 stats
2. **Make Option B sometimes RIGHT** - real dilemmas have two defensible choices
3. **Add consequence types**: reputation, hidden info, cross-player, locked choices, delayed effects
4. **Create emotional moment cards**: promise dilemmas, friendship vs performance, personal sacrifice
5. **Add behavior_tags** to every card for evidence generation
6. **Remove identical option pairs** (BM_N05)
