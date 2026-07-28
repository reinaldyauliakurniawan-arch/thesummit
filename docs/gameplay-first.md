# The Summit v2 — Gameplay-First Architecture

## Priority Shift

> "The current architecture is becoming analytics-heavy while the gameplay itself is still too shallow to generate meaningful behavioral evidence. A sophisticated ReflectionEngine cannot produce accurate leadership insights if the game does not force meaningful leadership decisions."

This document describes the architectural pivot from analytics-first to gameplay-first.

---

## What Changed

### TASK 1: Comprehensive Card Audit
- Audited all 52 original cards against 5 leadership behavior criteria
- Found 26 cards mathematically optimizable (one option dominates all stats)
- Found 10 cards with no genuine trade-off
- Found 24 cards that were genuine dilemmas
- Full report: `docs/card-audit-report.md`

### TASK 2: Remove Fake Decisions
- Redesigned all 60 cards as individual JSON files per `docs/card-schema.md`
- Every card now forces a genuine trade-off where BOTH options gain something AND lose something
- No card can be solved by "pick higher TT" heuristic
- Stat system expanded from MP/SP/TT to include: reputation, resources, flexibility
- Commit: `feat: redesign all 60 cards as JSON`

### TASK 3: Build Evidence, Not Scores
- BehaviorTracker rewritten to use evidence EVENTS instead of stat-based structural inference
- Evidence sources: (1) Explicit card behavior_tags, (2) Observable game events, (3) Minimal pattern detection
- Removed `inferStructuralSignals()` — no more inferring leadership from MP/SP/TT deltas
- Evidence now described in human-readable form: "Option A: Prioritized team over self"
- Commit: `refactor: BehaviorTracker — evidence events, not stat inference`

### TASK 4: Redesign Consequence System
Consequences now include (coverage across 60 cards):
| Consequence Type | Cards | Description |
|---------------|-------|-------------|
| Cross-player effects | 69 | `affect_player`, `affect_team` |
| Delayed effects | 48 | `schedule_event` with `trigger_after_rounds` |
| Conditional triggers | 22 | `conditional_trigger` with stat-based conditions |
| Promises/debts | 9 | `create_promise`, `break_promise`, `create_debt` |
| Hidden information | 6 | `hide_information`, `reveal_information` |
| Reputation changes | 110 | `reputation_change` |

### TASK 5: Emotional Moments
6+ emotional peak cards designed:
1. **BM007**: Friend reported your error (integrity vs friendship)
2. **CM002**: Promoted over your friend (authority vs friendship)
3. **CM007**: Asked to fake evaluation before layoff (integrity vs loyalty)
4. **SM005**: Broke a promise for fair selection (promise vs fairness)
5. **SM009**: Toxic top performer — results vs values (KPI vs culture)
6. **SM010**: Sacrificed dream job for team (ambition vs loyalty)
7. **SM011**: Trusted teammate sabotaged others (trust vs confrontation)
8. **SS010**: Merger — forced culture clash (inclusion vs efficiency)
9. **SS011**: Broke no-layoff promise to save company (promise vs survival)
10. **SS012**: Product safety whistleblowing (integrity vs career)

### TASK 7: Simplify Analytics
- Removed: `inferStructuralSignals()` (stat-delta-based leadership inference)
- Removed: Safe-during-crisis pattern detection (unreliable with redesigned cards)
- Kept: Explicit tags, observable game events, same-option repetition pattern
- Low-confidence dimensions return classification "speculative" (effectively "Insufficient evidence")
- Score methodology and confidence calculation preserved per `docs/leadership-framework.md`

---

## Remaining Tasks
- TASK 6: Redesign win condition (prevent selfish optimization from winning)
- Integration: Wire new JSON card loader into GameService to replace PHP seeder
- Integration: Wire observable game events into BehaviorTracker
- Integration: Update EventLog to record evidence events
