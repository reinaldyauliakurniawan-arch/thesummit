# The Summit v2 — Implementation Roadmap

## Purpose

Break the implementation of The Summit v2 into small, ordered milestones. Each milestone is independently testable. The order minimizes rework by building foundational systems first and features on top.

---

## Architecture Overview

```
┌─────────────────────────────────────────┐
│            Card Layer (JSON)             │
│  Cards are data. No card-specific code. │
├─────────────────────────────────────────┤
│          Event Engine                     │
│  Generic execution of card effects.      │
├─────────────────────────────────────────┤
│        Consequence System                │
│  Delayed/conditional/persistent effects.  │
├─────────────────────────────────────────┤
│        Social Mechanics                   │
│  Promises, debts, votes, relationships.  │
├─────────────────────────────────────────┤
│      Behavior Analytics                   │
│  Evidence collection, scoring, profiles.  │
├─────────────────────────────────────────┤
│      Reflection Engine                   │
│  Narrative generation from profile data.  │
├─────────────────────────────────────────┤
│       Game Services                      │
│  Turn processing, game lifecycle.        │
├─────────────────────────────────────────┤
│       UI Layer (Livewire/Blade)          │
│  Player interface, game board, summary.  │
└─────────────────────────────────────────┘
```

Build bottom-up: foundations first, UI last.

---

## Milestone 0 — Reset & Foundation

### Objective
Clean the previous implementation. Rebuild on the foundation defined in the 7 design documents.

### Files to Modify
- Remove all v2 code added in the previous commit (services, models, migrations, UI changes)
- Keep the existing v1 game loop (GameService, GameBoard, etc.) intact
- Keep the 60-card seeder intact

### Dependencies
- docs/ are complete (all 7 documents approved)

### Acceptance Criteria
- [ ] V2 migration removed
- [ ] V2 models removed (Consequence, CrossPlayerEffect, PlayerBehavior, Promise, Vote, LeadershipProfile, RealWorldChallenge)
- [ ] V2 services removed (ConsequenceEngine, CrossPlayerEngine, BehaviorTracker, SocialEngine, ReflectionEngine, ChallengeGenerator)
- [ ] GameService.php restored to v1 state
- [ ] UI components restored to v1 state
- [ ] All 60 original cards intact
- [ ] Existing game loop works (create room → join → start → turns → finish)

### Test Cases
- [ ] Run `php artisan migrate` successfully
- [ ] Run `php artisan db:seed` successfully
- [ ] Create a room, join with 3 players, start game, play 5 turns, finish — all works as v1

---

## Milestone 1 — Event Engine Core

### Objective
Build the generic event execution system. This is the foundation everything else depends on.

### Files to Create
- `app/Services/EventEngine/EventEngine.php` — Core queue processor
- `app/Services/EventEngine/Event.php` — Event model
- `app/Services/EventEngine/EventLog.php` — Immutable log
- `app/Services/EventEngine/TargetResolver.php` — Target reference resolution
- `app/Services/EventEngine/ConditionEvaluator.php` — Condition evaluation
- `app/Services/EventEngine/Handlers/EventHandlerInterface.php` — Handler contract
- `app/Services/EventEngine/Handlers/ModifyStatHandler.php` — First handler

### Files to Modify
- `database/migrations/xxxx_create_events_table.php`
- `database/migrations/xxxx_create_event_logs_table.php`
- `app/Models/Event.php`
- `app/Models/EventLog.php`

### Dependencies
- Milestone 0 complete

### Acceptance Criteria
- [ ] EventEngine can queue and execute a `modify_stat` event
- [ ] TargetResolver resolves `self`, `other_players`, `all_players` correctly
- [ ] ConditionEvaluator evaluates `stat_threshold` conditions
- [ ] EventLog records every execution with before/after state
- [ ] Atomicity: failed events roll back all effects in the batch
- [ ] Priority ordering works (system > immediate > social > info > scheduled > deferred)

### Test Cases
- [ ] Queue `modify_stat(mp, +2)` on a player → mp increases by 2
- [ ] Queue `modify_stat(mp, -15)` on a player with mp=8 → mp becomes 0 (floored)
- [ ] Queue two conflicting events → both execute, second one floored
- [ ] Condition `tt >= 5` on player with tt=3 → event is skipped
- [ ] Failed event → full batch rollback

---

## Milestone 2 — Event Handlers (All Primitives)

### Objective
Implement all game grammar primitive handlers. After this milestone, the event engine can execute any effect defined in the grammar.

### Files to Create
- `app/Services/EventEngine/Handlers/ScheduleEventHandler.php`
- `app/Services/EventEngine/Handlers/ConditionalTriggerHandler.php`
- `app/Services/EventEngine/Handlers/CancelEventHandler.php`
- `app/Services/EventEngine/Handlers/RevealInformationHandler.php`
- `app/Services/EventEngine/Handlers/HideInformationHandler.php`
- `app/Services/EventEngine/Handlers/LockChoiceHandler.php`
- `app/Services/EventEngine/Handlers/UnlockChoiceHandler.php`
- `app/Services/EventEngine/Handlers/RollDiceHandler.php`
- `app/Services/EventEngine/Handlers/ProbabilityCheckHandler.php`
- `app/Services/EventEngine/Handlers/CreatePromiseHandler.php`
- `app/Services/EventEngine/Handlers/BreakPromiseHandler.php`
- `app/Services/EventEngine/Handlers/CreateDebtHandler.php`
- `app/Services/EventEngine/Handlers/ResolveDebtHandler.php`
- `app/Services/EventEngine/Handlers/AffectTeamHandler.php`
- `app/Services/EventEngine/Handlers/TriggerDysfunctionHandler.php`
- `app/Services/EventEngine/Handlers/AdvanceLevelHandler.php`
- `app/Services/EventEngine/Handlers/CreateVoteHandler.php`
- `app/Services/EventEngine/Handlers/RelationshipChangeHandler.php`
- `app/Services/EventEngine/Handlers/ReputationChangeHandler.php`
- `app/Services/EventEngine/HandlerRegistry.php`

### Files to Modify
- `app/Services/EventEngine/EventEngine.php` — Register all handlers

### Dependencies
- Milestone 1 complete

### Acceptance Criteria
- [ ] `schedule_event` creates a deferred event that fires after N rounds
- [ ] `roll_dice` dispatches different effects based on die result
- [ ] `trigger_dysfunction` applies shared penalty to team
- [ ] `create_promise` creates a tracked promise between two players
- [ ] `affect_team` applies an effect to all players (excluding source)
- [ ] Event chaining works: `roll_dice` → `trigger_dysfunction` → `affect_team`
- [ ] Chain depth limit (10) prevents infinite recursion

### Test Cases
- [ ] Schedule `modify_stat(mp, +1)` for round 5 → at round 5, mp increases
- [ ] Roll dice, get result 2 → dysfunction triggered, TT -2, team shared penalty
- [ ] Create promise from player A to player B → promise appears in player B's active promises
- [ ] Chain: roll_dice(2) → trigger_dysfunction → affect_team(tt, -1) → all other players lose 1 TT

---

## Milestone 3 — Card JSON Schema & Loader

### Objective
Implement the card DSL. Cards become JSON files. The engine loads and executes them without any card-specific code.

### Files to Create
- `app/Services/CardEngine/CardLoader.php` — Loads cards from JSON files
- `app/Services/CardEngine/CardValidator.php` — Validates card JSON against schema
- `app/Services/CardEngine/CardExecutor.php` — Translates card JSON into event queue
- `app/Services/CardEngine/CardResolver.php` — Resolves card targets during execution
- `database/cards/` — Directory structure for JSON card files
- `tests/CardValidationTest.php` — Validation test suite

### Files to Modify
- `config/summit.php` — Add card storage path config
- `app/Models/ExpeditionCard.php` — Add `card_json` column
- `database/migrations/xxxx_add_card_json_column.php`

### Dependencies
- Milestone 2 complete

### Acceptance Criteria
- [ ] A card defined in JSON can be loaded, validated, and executed by the engine
- [ ] CardLoader reads all cards from `database/cards/` directory
- [ ] CardValidator rejects invalid cards (missing effects, bad stat names, etc.)
- [ ] CardExecutor translates a card choice into an event queue
- [ ] Adding a new card requires only creating a JSON file — zero PHP changes
- [ ] The 60 existing seeder cards are converted to JSON format

### Test Cases
- [ ] Load card from JSON → validate → all fields present
- [ ] Invalid card (missing effects) → validation fails with descriptive error
- [ ] Execute card choice A → event queue generated with correct effects
- [ ] Card with hidden_info → reveal_information event generated after choice
- [ ] Card with roll_dice → dice roll event generated
- [ ] Card with cross_player effects → affect_team events generated

---

## Milestone 4 — Consequence System

### Objective
Implement persistent consequences (PRD Feature 1). Decisions create delayed and conditional effects that fire in future rounds.

### Files to Create
- `app/Services/ConsequenceSystem/ConsequenceManager.php` — Create, track, trigger consequences
- `app/Services/ConsequenceSystem/ConsequenceProcessor.php` — Process pending consequences each round
- `database/migrations/xxxx_create_consequences_table.php`
- `app/Models/Consequence.php`

### Files to Modify
- `app/Services/GameService.php` — Integrate consequence processing into turn flow

### Dependencies
- Milestone 3 complete

### Acceptance Criteria
- [ ] Choosing an option with `schedule_event` creates a Consequence record
- [ ] Each round, `ConsequenceProcessor` checks all pending consequences
- [ ] Delayed consequence fires after N rounds
- [ ] Conditional consequence fires when condition is met
- [ ] Hidden consequences are not visible to the affected player
- [ ] Triggered consequences produce EventLog entries
- [ ] UI shows active (non-hidden) consequences for the current player

### Test Cases
- [ ] Play card with delayed effect (3 rounds) → at round +3, stat changes
- [ ] Play card with conditional effect (tt <= 3) → when TT drops to 3, effect fires
- [ ] Hidden consequence → player cannot see it in UI
- [ ] Multiple consequences in same round → all trigger in priority order

---

## Milestone 5 — Social Mechanics

### Objective
Implement promises, debts, votes, and relationships (PRD Features 3 & 5).

### Files to Create
- `database/migrations/xxxx_create_social_tables.php` (promises, debts, votes, relationships)
- `app/Models/Promise.php`
- `app/Models/Debt.php`
- `app/Models/Vote.php`
- `app/Models/Relationship.php`
- `app/Services/SocialSystem/PromiseManager.php`
- `app/Services/SocialSystem/DebtManager.php`
- `app/Services/SocialSystem/VoteManager.php`
- `app/Services/SocialSystem/RelationshipManager.php`

### Files to Modify
- `app/Services/GameService.php` — Integrate social processing into turn flow
- `config/summit.php` — Add social config

### Dependencies
- Milestone 4 complete

### Acceptance Criteria
- [ ] Players can create promises (via card effects or manual UI action)
- [ ] Promise fulfillment increases promiser reputation
- [ ] Promise breaking decreases promiser reputation and TT
- [ ] Auto-break after 5 turns if unfulfilled
- [ ] Vote events can be triggered by cards
- [ ] All players can cast votes
- [ ] Vote resolves when all players vote or timeout
- [ ] Vote result applies the resolution event
- [ ] Debts are created by card effects
- [ ] Debt penalties auto-fire if unresolved

### Test Cases
- [ ] Create promise → appears in active promises list
- [ ] Fulfill promise → reputation +2
- [ ] Break promise → reputation -3, TT -1
- [ ] Create vote → all players see voting UI
- [ ] All vote → result applied
- [ ] Create debt → penalty fires if unresolved by deadline

---

## Milestone 6 — Behavior Analytics

### Objective
Implement the leadership analytics framework. Collect evidence from decisions and produce structured profiles.

### Files to Create
- `database/migrations/xxxx_create_behavior_tables.php`
- `app/Models/BehaviorEvidence.php`
- `app/Models/LeadershipProfile.php`
- `app/Services/Analytics/EvidenceCollector.php` — Extract evidence from turns
- `app/Services/Analytics/DimensionScorer.php` — Aggregate evidence into scores
- `app/Services/Analytics/ProfileGenerator.php` — Produce structured profiles
- `app/Services/Analytics/ConfidenceCalculator.php` — Compute confidence levels

### Files to Modify
- `app/Services/GameService.php` — Trigger evidence collection after each turn
- `app/Services/GameService.php` — Trigger profile generation at game end

### Dependencies
- Milestone 5 complete

### Acceptance Criteria
- [ ] Every turn generates 0-3 BehaviorEvidence records
- [ ] Explicit tags create high-reliability evidence
- [ ] Structural inference creates medium-reliability evidence
- [ ] Pattern inference creates low-reliability evidence
- [ ] Dimension scores aggregate correctly with weighting
- [ ] Confidence calculation accounts for evidence count and consistency
- [ ] Profile output matches the structure in `docs/leadership-framework.md`
- [ ] Profiles with < 5 turns are marked "Insufficient Data"
- [ ] Strengths and blind spots are correctly classified

### Test Cases
- [ ] Play 10 turns with consistent collaboration choices → collaboration score > 0, confidence > 0.5
- [ ] Play 10 turns with mixed risk signals → risk_taking has low consistency
- [ ] Play 3 turns → profile is "Insufficient Data"
- [ ] Explicit tag `empathy: 2` → creates strong positive evidence

---

## Milestone 7 — Reflection Engine

### Objective
Generate narrative end-game reports from structured profile data.

### Files to Create
- `app/Services/Reflection/ReportGenerator.php` — Generate narrative sections
- `app/Services/Reflection/StyleNarrator.php` — Generate leadership style narrative
- `app/Services/Reflection/TurningPointDetector.php` — Find key moments
- `app/Services/Reflection/RecommendationEngine.php` — Generate coaching advice
- `database/migrations/xxxx_create_reflections_table.php`
- `app/Models/Reflection.php` — Stores generated report

### Files to Modify
- `app/Livewire/GameSummary.php` — Display reflection report
- `resources/views/livewire/game-summary.blade.php` — Reflection UI

### Dependencies
- Milestone 6 complete

### Acceptance Criteria
- [ ] At game end, a Reflection is generated for each player
- [ ] Report includes: leadership style, strengths, blind spots, turning point, missed opportunities, coaching recommendations
- [ ] Narrative is generated from profile data, never hardcoded
- [ ] Behavior scores are visualized with bar charts
- [ ] Each claim in the report references specific evidence
- [ ] Reports with low confidence use hedging language ("suggests" vs "demonstrates")

### Test Cases
- [ ] Complete 12-turn game → reflection generated with all sections
- [ ] Profile with collaboration strength → reflection mentions collaboration
- [ ] Profile with "Insufficient Data" → reflection notes limited data

---

## Milestone 8 — Real-World Challenge

### Objective
Generate personalized challenges based on blind spots.

### Files to Create
- `app/Services/Challenge/ChallengeGenerator.php` — Pick challenge from blind spot
- `database/migrations/xxxx_create_challenges_table.php`
- `app/Models/Challenge.php`

### Files to Modify
- `app/Services/GameService.php` — Trigger challenge generation at game end
- `app/Livewire/GameSummary.php` — Challenge UI (mark as completed)
- `app/Livewire/Dashboard.php` — Show pending challenges on dashboard

### Dependencies
- Milestone 7 complete

### Acceptance Criteria
- [ ] At game end, a Challenge is generated for each player
- [ ] Challenge type corresponds to player's primary blind spot
- [ ] Challenge is specific, observable, and completable in one week
- [ ] Player can mark challenge as completed
- [ ] Dashboard shows pending challenges from previous sessions
- [ ] Next session asks about challenge completion (stretch goal — M9)

### Test Cases
- [ ] Player with risk_taking blind spot → delegation challenge generated
- [ ] Mark challenge completed → is_completed = true
- [ ] Dashboard shows uncompleted challenge from previous game

---

## Milestone 9 — Card Content Overhaul

### Objective
Convert all 60 cards to JSON format and add v2 mechanics (delayed effects, cross-player effects, hidden info, behavior tags).

### Files to Create
- `database/cards/basecamp/mindset/*.json` — ~10 cards
- `database/cards/basecamp/skillset/*.json` — ~10 cards
- `database/cards/camp/mindset/*.json` — ~10 cards
- `database/cards/camp/skillset/*.json` — ~10 cards
- `database/cards/summit/mindset/*.json` — ~10 cards
- `database/cards/summit/skillset/*.json` — ~10 cards

### Files to Modify
- `database/seeders/ExpeditionCardSeeder.php` — Update to read from JSON
- Remove old V2CardEnhancementSeeder

### Dependencies
- Milestone 3 complete

### Acceptance Criteria
- [ ] All 60 cards converted to JSON format
- [ ] Each card passes CardValidator
- [ ] Every card has at least one meaningful trade-off
- [ ] ~30% of cards have hidden info
- [ ] ~40% of cards have delayed effects
- [ ] ~50% of camp+ cards have cross-player effects
- [ ] Every card has behavior_tags on at least one choice
- [ ] No "strictly better" options exist

### Test Cases
- [ ] Validate all 60 cards → 0 errors
- [ ] Play through all 60 cards → no runtime errors
- [ ] Audit: option A/B selection split is 40-60% for each card

---

## Milestone 10 — UI Overhaul

### Objective
Update all UI components to display v2 features: consequences, promises, votes, relationships, reputation, resources, flexibility.

### Files to Modify
- `resources/views/livewire/game-board.blade.php` — Full v2 game board
- `resources/views/components/expedition-card.blade.php` — Hidden info, cross-player, delayed effects
- `resources/views/livewire/game-summary.blade.php` — Reflection report + challenge
- `resources/views/components/progress-bar.blade.php` — Add reputation/resources
- `app/Livewire/GameBoard.php` — Promise/vote/consequence UI state

### Dependencies
- Milestones 4, 5, 6, 7, 8, 9 complete

### Acceptance Criteria
- [ ] Active consequences panel visible on game board
- [ ] Promise creation UI (modal with type, recipient, description)
- [ ] Vote UI (options, cast vote button)
- [ ] Player cards show reputation and resources
- [ ] Effects display shows cross-player effects and hidden info reveals
- [ ] Game summary shows full reflection report with behavior scores
- [ ] Challenge display with mark-as-completed button
- [ ] All new UI elements are responsive (mobile-friendly)

### Test Cases
- [ ] Play a card with hidden info → after choosing, hidden info is revealed in UI
- [ ] Play a card with cross-player effects → other players see the effect in their log
- [ ] Create a promise → appears in active promises panel
- [ ] Trigger a consequence → appears in consequences panel
- [ ] View game summary → reflection report is displayed with all sections

---

## Milestone 11 — Anti-Dominant-Strategy Validation

### Objective
Playtest and validate that no dominant strategy exists. Adjust card balance as needed.

### Files to Modify
- `database/cards/*.json` — Balance adjustments based on playtesting

### Dependencies
- All previous milestones complete

### Acceptance Criteria
- [ ] 10+ simulated games → no single strategy wins > 60% of games
- [ ] Every session has at least 3 memorable dilemmas (as defined in game-economy.md)
- [ ] Selfish play results in lower win rate than balanced play
- [ ] Early-game MP/SP hoarding fails to reach summit
- [ ] Pure TT hoarding fails to meet progression thresholds
- [ ] No card has > 70/30 selection split consistently

---

## Implementation Order Summary

```
M0: Reset & Foundation
  ↓
M1: Event Engine Core (handlers + queue + rollback)
  ↓
M2: All Event Handlers (all grammar primitives)
  ↓
M3: Card JSON Schema & Loader (data-driven cards)
  ↓
M4: Consequence System (delayed/conditional effects)
  ↓
M5: Social Mechanics (promises, debts, votes, relationships)
  ↓
M6: Behavior Analytics (evidence → profile)
  ↓
M7: Reflection Engine (profile → narrative report)
  ↓
M8: Real-World Challenge (blind spot → challenge)
  ↓
M9: Card Content Overhaul (60 cards → JSON with v2 features)  [can start at M3]
  ↓
M10: UI Overhaul (all v2 features visible to players)
  ↓
M11: Balance Validation (playtesting, adjustments)
```

Note: M9 can begin in parallel with M4-M8 since it only depends on M3 (card schema). This reduces total implementation time.

### Estimated Effort

| Milestone | Estimated Effort | Dependencies |
|-----------|----------------|--------------|
| M0 | 0.5 day | None |
| M1 | 2 days | M0 |
| M2 | 3 days | M1 |
| M3 | 2 days | M2 |
| M4 | 1.5 days | M3 |
| M5 | 2 days | M4 |
| M6 | 2 days | M5 |
| M7 | 1.5 days | M6 |
| M8 | 1 day | M7 |
| M9 | 3 days | M3 (parallel) |
| M10 | 2 days | M4-M9 |
| M11 | 2 days | All |
| **Total** | **~22.5 days** | — |

With parallel work on M9, critical path is ~19.5 days.
