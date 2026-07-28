# The Summit v2 — Event Engine Specification

## Purpose

Design the generic event execution system that powers all gameplay mechanics in The Summit. The engine must execute events defined as data (JSON), never card-specific code. Every card, consequence, scheduled effect, and game mechanic is expressed as events processed through this engine.

---

## Core Principles

1. **Generic**: The engine does not know what card it is executing. It executes effect objects.
2. **Composable**: Events can contain other events (nesting). The engine resolves them recursively.
3. **Deterministic where required**: Stat modifications are deterministic. Random outcomes use seeded RNG.
4. **Atomic**: A turn's effects are applied as a transaction. If any effect fails, the entire turn rolls back.
5. **Observable**: Every executed event produces an event log entry for the UI and analytics.

---

## Event Lifecycle

### Phases

Every event passes through 5 phases:

```
Queue → Validate → Execute → Record → Notify
```

#### Phase 1: Queue

Events enter a processing queue. The queue has a defined priority order.

#### Phase 2: Validate

The engine checks:
- The event's target is valid and resolvable
- All parameters are within acceptable bounds
- No circular dependencies exist
- The event's condition (if any) can be evaluated

If validation fails, the event is rejected and logged.

#### Phase 3: Execute

The engine dispatches the event to the appropriate handler. The handler:
1. Resolves the target reference to concrete player(s)
2. Evaluates any condition
3. Applies the effect
4. Returns a result object

#### Phase 4: Record

The engine writes an `EventLog` entry:
```jsonc
{
  "event_id": "uuid",
  "event_type": "modify_stat",
  "source": { "type": "card", "id": "basecamp_mindset_001", "choice": "A" },
  "target": { "type": "player", "id": 5 },
  "params": { "stat": "mp", "delta": 2 },
  "result": { "before": 8, "after": 10 },
  "timestamp": "2026-07-28T10:30:00Z",
  "round": 3,
  "turn_id": 42
}
```

#### Phase 5: Notify

The engine dispatches notifications:
- **UI**: Real-time update to the game board (Livewire polling)
- **Analytics**: Forward to the behavior tracker
- **Consequences**: Check if this event triggered any pending conditional effects

---

## Event Queue

### Priority Levels

| Priority | Level | Event Types |
|----------|-------|-------------|
| 0 (highest) | System | `advance_level`, `trigger_final_round`, `finish_game` |
| 1 | Immediate effects | `modify_stat`, `roll_dice` |
| 2 | Social | `create_promise`, `create_vote`, `create_debt` |
| 3 | Information | `reveal_information`, `hide_information` |
| 4 | Scheduled | `schedule_event` (the scheduling itself, not the deferred event) |
| 5 (lowest) | Deferred | Events triggered by `schedule_event` at a later round |

### Processing Order

Within the same priority, events are processed in the order they were added to the queue (FIFO). This ensures deterministic execution.

### Batch Processing

A single turn may generate multiple events:
1. The player's chosen card effects (priority 1-3)
2. Risk die outcomes (priority 1)
3. Any triggered conditional effects (priority 5)
4. Any scheduled events whose round has arrived (priority 5)

The engine processes all events in priority order before returning the turn result to the player.

---

## Event Structure

### Base Event

```jsonc
{
  "event_id": "uuid",
  "parent_event_id": null,
  "type": "modify_stat",
  "target_ref": "self",
  "params": { ... },
  "condition": null,
  "priority": 1,
  "timing": "immediate",
  "source": { ... },
  "game_state_snapshot": { ... },
  "status": "pending"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `event_id` | string | Unique ID. |
| `parent_event_id` | string? | For nested events (e.g., scheduled event inside schedule_event). |
| `type` | string | Maps to a game grammar primitive. |
| `target_ref` | TargetRef | Generic target reference. |
| `params` | object | Primitive-specific parameters. |
| `condition` | Condition? | Optional condition to evaluate before execution. |
| `priority` | integer | Queue priority. |
| `timing` | enum | `immediate` \| `deferred` |
| `source` | Source | What triggered this event. |
| `game_state_snapshot` | object? | Frozen game state at queue time (for validation). |
| `status` | enum | `pending` \| `validated` \| `executed` \| `rejected` \| `rolled_back` |

### Source Types

```jsonc
{ "source": { "type": "card", "card_id": "basecamp_mindset_001", "choice": "A" } }
{ "source": { "type": "consequence", "consequence_id": 42 } }
{ "source": { "type": "system", "reason": "round_start" } }
{ "source": { "type": "social", "actor_player_id": 5, "action": "fulfill_promise" } }
{ "source": { "type": "condition", "condition_id": 17 } }
{ "source": { "type": "event_chain", "parent_event_id": "uuid" } }
```

---

## Target Resolution

The engine resolves generic target references to concrete player IDs.

### Resolution Algorithm

```
resolve(target_ref, context) → player_id[]
```

| Target Reference | Resolution |
|-----------------|------------|
| `self` | `context.choosing_player_id` |
| `other_players` | All active players in room except `self` |
| `all_players` | All active players in room |
| `min_stat_player:{stat}` | Player with lowest value of `{stat}` (excluding self if applicable) |
| `max_stat_player:{stat}` | Player with highest value of `{stat}` (excluding self if applicable) |
| `random_other` | One random player from `other_players` |
| `adjacent_players` | Players immediately before and after in turn order |
| `player:{id}` | Specific player by ID (used by resolved events) |

### Resolution Failure

If a target reference resolves to zero players (e.g., `adjacent_players` in a 1-player game), the event is marked `rejected` and logged. It does not block other events.

---

## Condition Evaluation

### Evaluation Algorithm

```
evaluate(condition, game_state) → boolean
```

The engine evaluates conditions against the current game state. Conditions are defined in `docs/card-schema.md` (Condition System).

### Condition Caching

Conditions for deferred events (scheduled and conditional) are re-evaluated every round. The engine does not cache condition results — game state changes between evaluations.

### Side-Effect-Free

Condition evaluation MUST NOT modify game state. It is a read-only operation.

---

## Conflict Resolution

### Stat Floor/Ceiling Conflicts

When multiple events modify the same stat in the same batch:

1. Events are applied in priority order
2. After each application, stat floors and ceilings are enforced
3. The final stat value is the result after ALL events in the batch

Example:
- Event A: `mp += 3` (priority 1) → mp goes from 8 to 11
- Event B: `mp -= 15` (priority 1, FIFO after A) → mp goes from 11 to 0 (floored)

The player sees: `mp +3 -15 (floored at 0)`

### Cross-Player Effect Conflicts

When multiple events target the same player from different sources:

1. Each event's effect is applied independently
2. Effects are applied in source event order
3. Cross-player effects do NOT cancel each other

### Promise/Vote Conflicts

When a player attempts to break a promise and simultaneously create a new promise:

1. The break_promise event is processed first (higher priority)
2. Reputation penalty is applied
3. Then the create_promise event is processed
4. The new promise is created with the already-reduced reputation

### Conditional Effect Cascades

When executing a conditional effect triggers another conditional effect:

1. The inner conditional is added to the end of the current priority queue
2. It is NOT executed immediately (prevents unbounded recursion)
3. If the queue depth exceeds 10 levels, the cascade is terminated

---

## Dependency Resolution

### Explicit Dependencies

Events can declare dependencies:

```jsonc
{
  "type": "modify_stat",
  "depends_on": ["event_id_1", "event_id_2"],
  ...
}
```

The engine will not execute this event until all dependencies are in `executed` status.

### Circular Dependency Detection

Before processing, the engine builds a dependency graph. If a cycle is detected:
1. All events in the cycle are marked `rejected`
2. An error is logged
3. The turn is NOT rolled back (other events without dependencies still execute)

### Implicit Dependencies

Scheduled events implicitly depend on the turn processing completing. They are never executed in the same batch as the events that scheduled them.

---

## Rollback Rules

### When Rollback Occurs

A rollback happens when:
1. A system-level event fails (e.g., `advance_level` when already at summit)
2. The engine encounters an unrecoverable error
3. A validation error is detected during execution (not during the validate phase)

### Rollback Scope

Rollback is scoped to the current **turn batch only**. Events from previous turns are never rolled back.

### Rollback Mechanism

```
1. Snapshot all affected player stats before the batch
2. Execute events in priority order
3. If failure detected:
   a. Restore all stats from snapshot
   b. Mark all events in the batch as `rolled_back`
   c. Log the failure
4. If success:
   a. Discard snapshot
   b. Commit event log entries
```

### What IS Rolled Back

- Stat modifications (mp, sp, tt, reputation, resources, flexibility)
- Level changes
- Promise/debt creation
- Vote creation

### What is NOT Rolled Back

- Event log entries (they record the failure)
- Analytics evidence (evidence of a failed decision is still evidence)
- Notifications already sent

---

## Event Chaining

### Definition

Event chaining occurs when the execution of one event generates new events. The chain is the sequence of cause-and-effect events.

### Chain Types

#### Immediate Chain

An event generates another event that enters the same batch queue.

Example: `roll_dice` → result is 2 → generates `trigger_dysfunction` and `modify_stat(tt, -2)`

#### Deferred Chain

An event schedules a future event that enters a later batch queue.

Example: `schedule_event` → 3 rounds later → generates `modify_stat(mp, +1)`

#### Conditional Chain

An event's execution changes game state, which triggers a pending conditional event.

Example: Player's TT drops to 2 → conditional effect "TT <= 3" fires → generates `modify_stat(reputation, -2)`

### Chain Depth Limit

Maximum chain depth: **10 levels**

If a chain reaches depth 10, the deepest event is marked `rejected` with reason `chain_depth_exceeded`.

### Chain Visualization

```
Turn 3, Player A chooses Card X:
  ├─ modify_stat(self, mp, +2)         [priority 1, depth 0]
  ├─ modify_stat(self, tt, -1)         [priority 1, depth 0]
  ├─ roll_dice(6)                       [priority 1, depth 0]
  │   └─ result: 2
  │       ├─ trigger_dysfunction(random) [priority 1, depth 1]
  │       │   └─ affect_team(tt, -1)    [priority 1, depth 2]
  │       └─ modify_stat(self, tt, -2)  [priority 1, depth 1]
  └─ schedule_event(...)                [priority 4, depth 0]
      └─ [deferred to Turn 6]
          └─ modify_stat(self, mp, +1)  [priority 5, depth 0]
```

---

## Timing

### Within a Turn

```
Turn Start
  │
  ├─ Process scheduled events for this round [priority 5]
  │   ├─ Check delayed events: trigger_after_rounds reached?
  │   ├─ Check conditional events: condition now true?
  │   └─ Execute triggered events
  │
  ├─ Player chooses option
  │   ├─ Parse card effects into event queue
  │   └─ Validate all events
  │
  ├─ Execute events [priority 0-4]
  │   ├─ Priority 0: System events
  │   ├─ Priority 1: Stat modifications, risk die
  │   ├─ Priority 2: Social mechanics
  │   ├─ Priority 3: Information reveals
  │   └─ Priority 4: Schedule new events
  │
  ├─ Evaluate new conditional triggers
  │   └─ If any fire, add to current batch [priority 5]
  │
  ├─ Commit or Rollback
  │
  └─ Record analytics evidence
```

### Between Turns

```
Between Turn N and Turn N+1:
  ├─ Check promise expiry
  │   └─ Auto-break promises older than N turns
  ├─ Resolve completed votes
  │   └─ Apply vote results
  └─ Update UI state for polling
```

### At Game End

```
finish_game event:
  ├─ Cancel all pending scheduled events
  ├─ Cancel all pending conditional events
  ├─ Cancel all unresolved debts
  ├─ Mark all unfulfilled promises as expired
  ├─ Generate leadership profiles
  └─ Generate real-world challenges
```

---

## Event Types Registry

The engine maintains a registry of event type handlers. Each handler implements:

```
interface EventHandler {
  validate(event, game_state): ValidationResult
  resolve_target(target_ref, context): PlayerId[]
  execute(event, targets): ExecutionResult
  describe(event): string  // Human-readable description for logs
}
```

### Handler Registration

Handlers are registered by type name. Adding a new game grammar primitive requires:
1. Define the primitive in `docs/game-grammar.md`
2. Create a handler class
3. Register it in the handler registry

The engine does not need to be modified for new handlers.

### Built-in Handlers

| Type | Handler |
|------|---------|
| `modify_stat` | StatModificationHandler |
| `schedule_event` | EventSchedulerHandler |
| `cancel_event` | EventCancellationHandler |
| `conditional_trigger` | ConditionalTriggerHandler |
| `reveal_information` | InformationRevealHandler |
| `hide_information` | InformationHideHandler |
| `lock_choice` | ChoiceLockHandler |
| `unlock_choice` | ChoiceUnlockHandler |
| `spawn_card` | CardSpawnHandler |
| `remove_card` | CardRemovalHandler |
| `roll_dice` | DiceRollHandler |
| `probability_check` | ProbabilityCheckHandler |
| `create_promise` | PromiseCreationHandler |
| `fulfill_promise` | PromiseFulfillmentHandler |
| `break_promise` | PromiseBreakHandler |
| `create_debt` | DebtCreationHandler |
| `resolve_debt` | DebtResolutionHandler |
| `relationship_change` | RelationshipChangeHandler |
| `reputation_change` | ReputationChangeHandler |
| `affect_team` | TeamEffectHandler |
| `affect_player` | PlayerEffectHandler |
| `trigger_crisis` | CrisisTriggerHandler |
| `trigger_reward` | RewardTriggerHandler |
| `trigger_dysfunction` | DysfunctionTriggerHandler |
| `advance_level` | LevelAdvancementHandler |
| `trigger_final_round` | FinalRoundTriggerHandler |
| `create_vote` | VoteCreationHandler |

---

## Performance Requirements

From the PRD:
- Turn resolution < 300ms
- Reflection generation < 2s

### Target Per-Event Timing

| Event Type | Target Time |
|-----------|-------------|
| `modify_stat` | < 1ms |
| `schedule_event` | < 2ms |
| `roll_dice` | < 1ms |
| `conditional_trigger` (check) | < 2ms |
| `affect_team` | < 3ms (scales with player count) |
| `reveal_information` | < 1ms |
| `create_promise` | < 2ms |
| `create_vote` | < 2ms |

A typical turn generates 5-15 events. Total target: < 50ms for event processing (well within 300ms budget when including DB operations and UI rendering).
