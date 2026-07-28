# The Summit v2 — Player Journey

> **Purpose:** Define the complete emotional and cognitive journey of one player from game start until one week after the game ends. Every phase maps to specific leadership development objectives.
> **Audience:** Game designers, facilitators, HR/L&D professionals, coaches

---

## Design Philosophy

The Summit is not a board game with leadership themes. It is a **leadership development system** that uses a game format as its delivery mechanism. Every phase of the player journey exists to serve a specific learning objective. If a phase does not develop leadership, it must be redesigned.

The journey has three layers operating simultaneously:
- **Cognitive layer:** What the player thinks about — strategy, trade-offs, consequences
- **Emotional layer:** What the player feels — confidence, doubt, guilt, pride
- **Behavioral layer:** What the player actually does — choices, patterns, sacrifices

---

## Phase 1: Introduction (Pre-Game → Turn 1)

### Context
Player joins a room with 3-5 colleagues. The onboarding frames the game as "a leadership simulation, not a competition." Player sees their stats at zero: MP=0, SP=0, TT=0.

### Player Thoughts
"This looks like a team building exercise. I wonder if there's a right answer. Let me figure out the pattern quickly so I can optimize my score. The stats remind me of performance reviews — I need to get MP and SP high."

### Player Emotions
Curiosity, mild skepticism, competitive anticipation, slight anxiety about being evaluated by colleagues.

### Leadership Lesson Introduced
**Self-awareness as the foundation.** The game does not tell the player what kind of leader they are. It creates situations that reveal it. The introduction phase plants the seed: "This game knows something about your leadership that you don't."

### Gameplay Purpose
- Establish the stat system (MP, SP, TT, reputation, resources, flexibility) as metaphors for real leadership capital
- Create the illusion of a "score to optimize" — which the game will systematically dismantle
- Establish the async format (players have time to think, unlike real-time decisions)

### Expected Behavior Change
Player begins treating decisions as optimization problems. This is intentional — the game needs to establish this baseline so it can challenge it later. By Turn 3, the optimization approach should start failing.

### Corresponding Cards
- Basecamp mindset/skillset cards (Turns 1-2): Present straightforward-looking dilemmas where the "smart" choice seems obvious, but hidden consequences make it less clear than it appears.

---

## Phase 2: Early Confidence (Turns 2-4)

### Context
Player has made 2-3 decisions. Stats are climbing. The player believes they understand the system. They may be accumulating MP/SP rapidly while ignoring TT.

### Player Thoughts
"OK, I think I get it. MP is about mindset growth, SP is about execution skill. If I just focus on building these two, I'll reach the next level fastest. TT seems nice but not essential right now. I'll optimize for MP+SP."

### Player Emotions
Growing confidence, satisfaction at "figuring out the system," mild disconnection from other players, emerging sense of competence.

### Leadership Lesson Introduced
**The competence trap.** Early career leaders often believe that individual competence (MP/SP) is the path to leadership success. The game rewards this belief — temporarily. The player does not yet realize that the rules are about to change.

### Gameplay Purpose
- Let the player establish a pattern (likely self-optimization)
- Build enough confidence that the first real challenge feels consequential
- Create contrast: the player's confidence before the crisis makes the crisis more impactful
- Record baseline behavior data for the reflection report

### Expected Behavior Change
Player consolidates their strategy. They may start choosing options consistently ("always A" or "always B"). This is being recorded by the BehaviorTracker as pattern evidence. The player does not know this.

### Key Mechanic
The **same-option pattern detection** starts monitoring. If the player chooses the same option letter 5+ consecutive turns, the system records an `adaptability: negative` evidence point — but does not reveal it yet.

---

## Phase 3: First Dilemma (Turns 4-6)

### Context
First crisis card appears (basecamp krisis type, ~30% of basecamp cards). The Risk Die is introduced. Hidden information is revealed for the first time. The player encounters a situation where there is no "correct" answer.

### Corresponding Cards
- **BM007 (Kesalahan rekan dekat):** "Kamu menemukan kesalahan besar dalam laporan yang sudah dikirim ke klien oleh rekanmu. Rekanmu itu teman dekatmu."
  - Opsi A: Bicara privat, minta dia melapor sendiri (empathy +2, collaboration +1, control -1)
  - Opsi B: Lapor langsung ke atasan (decisiveness +2, empathy -2, control +1)
  - Hidden reveal: "Ternyata klien sudah menemukan kesalahan itu sendiri dan sedang mengevaluasi respons tim."
  - **This is where optimization breaks down.** Neither option is clearly "better." The hidden reveal reframes both choices.

- **BM008 (Perintah tidak etis):** "Atasan memintamu melakukan sesuatu yang bertentangan dengan nilai-nilaimu."
  - Opsi A: Ikuti arahan (adaptability +2, control +1) + conditional: if TT <= 3, reputation -2
  - Opsi B: Sampaikan keberatan (decisiveness +1, risk_taking +1, adaptability +1) + conditional: if TT >= 6, reputation +2
  - **The conditional triggers make the "right" answer context-dependent.** If the player has neglected TT (trust), standing up for values costs reputation instead of gaining it.

### Player Thoughts
"Wait — both options cost something real. If I protect my friend (Option A), I'm compromising integrity. If I report it (Option B), I'm betraying a friendship. The hidden info changes everything — the client already knows, so my choice isn't about hiding the error, it's about how I handle it under pressure."

### Player Emotions
Disorientation, moral discomfort, first genuine uncertainty, emerging awareness that this is not a simple optimization game, possibly guilt or defensiveness depending on choice.

### Leadership Lesson
**Leadership requires choosing between competing values, not between right and wrong.** Integrity vs loyalty. Empathy vs accountability. Speed vs thoroughness. Real leaders make these choices under pressure with incomplete information. The game strips away the comfort of "there's always a correct answer."

### Gameplay Purpose
- First genuine dilemma where mathematical optimization fails
- Introduction of hidden information mechanic — the player realizes they don't have full data
- First Risk Die roll on crisis cards — introduces randomness that cannot be controlled
- Behavioral evidence shifts from "this player optimizes stats" to "this player protects relationships" or "this player prioritizes integrity"

### Expected Behavior Change
The player's confidence is disrupted. They start reading card situations more carefully rather than scanning stat deltas. They may begin considering: "What would a real leader do in this situation?" This is the first moment of genuine leadership thinking.

---

## Phase 4: Increasing Uncertainty (Turns 7-12 — Camp Level)

### Context
Player transitions to Camp level ("Leading Others"). Cards now regularly involve other team members. Cross-player effects mean the player's choices affect other players' stats. Consequences from Basecamp decisions start triggering via `schedule_event`. The player receives their first evidence that past decisions have future costs.

### Corresponding Cards
- **CM001 (Anggota tim menurun):** "Anggota tim yang biasanya bagus tiba-tiba menurun. Kamu tahu dia ada masalah pribadi, tapi deadline tidak bisa ditunda."
  - Opsi A: Ajak bicara personal, tawarkan fleksibilitas (empathy +2, collaboration +1, coaching +1, TT +2, cross-player positive effect)
  - Opsi B: Tetap tuntut standar yang sama (decisiveness +1, control +2, empathy -1, TT -1)
  - Hidden reveal: "Masalah pribadi anggota tim sebenarnya terkait burnout — dia sudah minta bantuan 2 minggu lalu tapi tidak ada yang merespons."
  - **This reveal punishes the "just push through" approach and rewards empathetic leadership.**

- **CM007 (PHK tidak transparan):** "Kamu tahu anggota tim akan di-PHK. Dia tidak tahu dan kamu diminta turunkan evaluasinya sebagai justifikasi."
  - Opsi A: Tolak permintaan (decisiveness +2, empathy +2, TT +1, reputation +1)
  - Opsi B: Ikuti arahan manajemen (adaptability +1, empathy -2, TT -2, reputation -2)

- **CS001 (Distribusi tugas membosankan):** "Tugas penting tapi membosankan vs tugas menarik tapi impact rendah."
  - Opsi A: Rotasi adil (collaboration +2, empathy +1, SP -1, TT +2, cross-player positive)
  - Opsi B: Assign by expertise (control +1, collaboration -1, SP +2, TT -1, cross-player negative)

- **CS007 (Bug kritis saat rayakan milestone):** "Bug berasal dari kode senior tim. Apakah fokus fix atau cari siapa salah?"
  - Opsi A: Fix + blameless post-mortem (empathy +2, collaboration +1, TT +2, flexibility +1)
  - Opsi B: Cari siapa salah, konsekuensi (control +2, empathy -2, TT -3, cross-player negative)

### Player Thoughts
"My choices are affecting other players. When I chose to push the struggling team member, my TT dropped and someone else lost resources. When I delegated the investigation, the whole team got better. These aren't abstract stats anymore — they represent real relationships."

"This delayed consequence just hit me. Three turns ago I chose to skip clarifying scope, and now it's costing me because the scope was wrong. I didn't think decisions had consequences beyond the current turn."

"Camp is harder than Basecamp. The dilemmas aren't about me anymore — they're about how I handle other people's problems. And the hidden reveals keep showing me that my assumptions were wrong."

### Player Emotions
Growing discomfort, empathy activation, social awareness, guilt about past self-optimizing choices, emerging sense of responsibility for team outcomes, frustration at complexity, intellectual engagement.

### Leadership Lesson
**Leadership at the "Leading Others" level is fundamentally about other people, not about yourself.** The MP/SP accumulation strategy that worked in Basecamp now produces social costs. Trust Tokens (TT) become the bottleneck — you cannot progress without them, and you only earn them by investing in others. This mirrors the real leadership transition from individual contributor to people manager.

### Gameplay Purpose
- Cross-player effects make the game genuinely multiplayer for the first time
- Delayed consequences from Basecamp decisions create continuity between levels
- Hidden reveals consistently reward empathetic choices and punish purely analytical ones
- The player must now balance self-advancement (MP/SP) against team investment (TT) — this is the core leadership tension

### Expected Behavior Change
The player starts considering other players as real stakeholders, not competitors. They begin weighing "If I choose this, how does it affect the team?" This is the behavioral shift from individual contributor mindset to leadership mindset. The player may also start communicating with other players outside the game (chat, conversation) about shared challenges.

---

## Phase 5: Relationship Tension (Turns 13-18 — Camp Late)

### Context
Multiple consequences are now active simultaneously. Promises made earlier need to be fulfilled. Cross-player effects from multiple players create complex social dynamics. The player may have broken an implicit promise (chose an option that harmed another player who had helped them). The **promise system** becomes active.

### Corresponding Cards
- **CM009 (Target naik 50% tanpa resource):** "Dukung tim vs motivasi tim"
  - Opsi A: Advokat untuk tim ke manajemen (collaboration +2, empathy +2, TT +2, reputation +1)
  - Conditional: if MP <= 3, TT -1 ("Without capability, motivation crumbles")
  - Opsi B: Frame sebagai kesempatan (adaptability +1, control +1, empathy -1, TT -1)
  - Conditional: if MP >= 10, reputation +1 ("Positive framing earns visibility when backed by results")

- **CS007 (Bug kritis — blameless vs accountability):** Already triggers relationship tension when the player chooses accountability and the senior reviewer loses reputation.

- Cards with `create_promise` effects: The player must promise future actions to other players. These promises create future obligations tracked by the SocialEngine.

### Player Thoughts
"I made a promise to help Player C three turns ago, but now I have a crisis card that requires me to choose between fulfilling my promise and protecting myself. If I break the promise, my TT drops and reputation suffers. But if I fulfill it, I might fall behind on my own progression. This is exactly like real leadership — you can't always keep all your commitments."

"I just noticed that Player B has been consistently helping other players. Their TT is the highest in the room, but their MP/SP is lower than mine. Am I winning, or are they? The scoring system says I'm ahead, but something feels wrong about that."

### Player Emotions
Tension between self-interest and team-interest, guilt about accumulated social debts, anxiety about pending consequences, emerging respect for team-oriented players, possible resentment toward "free riders," social bonding with other players.

### Leadership Lesson
**Leadership creates relational debt.** Every time a leader makes a decision that benefits the team, they accumulate social capital. Every time they make a self-interested decision, they spend it. When the debt exceeds the capital, trust collapses. Real leaders manage this balance consciously. The game makes this balance visible through TT and reputation.

### Gameplay Purpose
- Promise system creates multi-turn commitments that constrain future choices
- Cross-player effects accumulate, creating visible social dynamics
- The player must now plan not just for the current turn, but for 2-3 turns ahead
- Reputational costs become real — choosing "selfish" options when others can see creates social consequences

### Expected Behavior Change
The player begins thinking systemically: "If I choose this now, what does it cost me 3 turns from now?" This is strategic thinking applied to relationships, not just stats. The player may also start negotiating with other players ("If you help me on this turn, I'll support you on the next one").

---

## Phase 6: Major Sacrifice (Turns 19-22 — Summit Early)

### Context
Player reaches Summit level ("Leading Leaders"). The dilemmas are now about organizational leadership — managing other leaders, making decisions that affect entire teams, facing ethical choices with no good answer. This is where the game delivers its emotional peak.

### Corresponding Cards
- **SM005 (Dua lead bersaing untuk posisi):** "Buat proses transparan vs pilih diam-diam."
  - Opsi A: Proses transparan (collaboration +2, empathy +1, TT +2, reputation +1, cross-player positive)
  - Opsi B: Pilih diam-diam (control +2, collaboration -2, empathy -1, TT -3, reputation -2)
  - Conditional on B: if TT <= 4, reputation -2 ("Secrecy erodes trust fast")

- **SM009 (Tim terbaik merusak hubungan tim lain):** "Apresiasi hasil tapi tegaskan budaya vs biarkan."
  - Opsi A: Tegaskan budaya (decisiveness +1, empathy +1, collaboration +1, TT +1, reputation +1, cross-player positive)
  - Opsi B: Biarkan karena hasil penting (control +1, collaboration -2, empathy -1, TT -3, reputation -2)
  - Conditional on B: if TT <= 4, TT -1 ("Tolerating toxicity spreads dysfunction")

- **SM007 (Budget dipotong 30%):** "Libatkan lead vs tentukan sendiri."
  - Opsi A: Libatkan (collaboration +2, coaching +1, TT +3, cross-player positive)
  - Opsi B: Tentukan sendiri (control +2, collaboration -2, TT -3, reputation -1, cross-player negative)

- **SS010 (Merger — budaya berbeda):** "Workshop co-creation vs tetapkan budaya existing."
  - Opsi A: Co-creation (MP +1, SP +1, TT +3, cross-player positive)
  - Opsi B: Impose (SP +2, TT -3, reputation -1, cross-player negative)

- **SS011 (Pecah janji tidak PHK untuk selamatkan perusahaan):** The ultimate emotional dilemma — break a promise for the greater good.

### Player Thoughts
"These aren't hypothetical leadership scenarios anymore. These feel like real decisions I've faced or will face. Choosing to lay off people to save the company, choosing between transparent selection and political control, choosing between team culture and KPI results — these are the decisions that define careers."

"I'm now at the point where TT matters more than MP/SP combined. If I don't have the team's trust, I can't lead effectively. But earning that trust means consistently sacrificing my own advancement for others. The math doesn't work out for self-interested play anymore — and that's exactly the point."

### Player Emotions
Heavy emotional weight, genuine moral discomfort, empathy for the characters in the scenarios, sense of consequence and responsibility, possible personal revelation ("I would actually do that in real life"), respect for the complexity of senior leadership, emotional fatigue mixed with engagement.

### Leadership Lesson
**Senior leadership is defined by trade-offs between competing goods, not choices between good and bad.** The Summit level dilemmas never have a "correct" answer. Both options advance legitimate values. The leader's job is to choose, accept the cost, and manage the consequences. This is the most important lesson of the entire game.

### Gameplay Purpose
- Emotional peak cards create memorable moments that players recall long after the game
- The trade-off calculus becomes genuinely difficult — even experienced leaders would struggle
- Evidence generation peaks: these high-stakes choices produce the strongest behavioral signals
- The win condition becomes meaningful: The Carrier badge requires Summit + TT>=8 + reputation>=0 + net positive promises — you cannot win by selfish optimization

### Expected Behavior Change
The player stops trying to "win" the game and starts trying to "lead well." This is the fundamental behavioral shift. The player may experience a moment of clarity: "I've been optimizing stats when I should have been building trust." This realization, if it happens, is the game's primary learning outcome.

---

## Phase 7: Emotional Peak (Turn 23-26 — Summit Late)

### Context
The game enters its final rounds. All accumulated consequences resolve. Promises expire. Final scores are calculated. The player faces the last crisis card — often the most emotionally difficult one.

### The Emotional Peak Moment
This is designed to be the single most memorable moment of the game. It occurs when:
1. A promise the player made earlier must be honored or broken
2. The hidden information from an earlier decision is fully revealed
3. The player must choose between personal victory and team benefit

**Example:** The player has reached Summit with high MP/SP. They are about to win. Then they receive a card where choosing the "leadership" option costs them TT and reputation, potentially losing their Carrier badge, while choosing the "selfish" option secures victory. This is the final test: do they lead, or do they win?

### Player Thoughts
"This is it. If I choose the right thing for the team, I might lose. If I choose what's best for me, I win but I'll know I didn't deserve it. My colleagues will see my choice. What I do here defines not just my score — it defines who I am as a leader."

"I've spent the entire game building trust. Do I sacrifice it now for the win? Or do I keep my integrity and accept whatever badge I get?"

### Player Emotions
Peak intensity — anxiety, resolve, possible anger at the game designer, pride in the choice made, vulnerability, relief when the decision is over, emotional exhaustion.

### Leadership Lesson
**Your leadership is defined by what you do when it costs you.** Anyone can lead when it's easy. Real leadership is revealed in the moments when the right thing to do is also the expensive thing to do. The game creates an artificial version of this moment, but the emotional response is genuine.

### Gameplay Purpose
- Final evidence generation for the leadership profile
- The choice here is the strongest single behavioral signal in the entire game
- Badge assignment depends on accumulated behavior, not just this choice — but this choice can tip the balance between Carrier and Solo Peak
- Creates the "story" the player tells after the game: "Remember when I had to choose between..."

### Expected Behavior Change
The player makes their most authentic leadership choice of the entire game. This choice is weighted 1.5x in the evidence model (crisis + summit level). The player may experience a post-decision emotional release — the game is ending, and they know their leadership profile will reflect everything they've done.

---

## Phase 8: Reflection (Game End — Immediate)

### Context
Game finishes. All players receive their leadership profile: strengths, blind spots, key turning point, missed opportunities, coaching recommendations. The profile is specific and evidence-based — every claim references a specific decision.

### Corresponding System
The **Reflection Engine** generates a narrative report from the behavior profile:
- **Leadership Style:** Derived from the player's top 2 behavioral dimensions (e.g., "collaborative-empathetic")
- **Strengths:** Top 3 dimensions with confidence >= 0.5 and positive score
- **Blind Spots:** Top 3 dimensions with confidence >= 0.5 and negative score
- **Key Turning Point:** The single turn with the strongest behavioral signal (highest magnitude evidence)
- **Missed Opportunities:** Dimensions where the player had 0 evidence (unexplored)
- **Coaching Recommendations:** Based on the gap between strengths and blind spots

### Player Thoughts
"I didn't realize I always avoid conflict. The profile shows I chose the 'safe' option on every crisis card. And my collaboration score is high — but that's because I always chose to help others at my own expense. Is that leadership, or is it people-pleasing?"

"My key turning point was Turn 14, when I chose to advocate for my team instead of accepting the unrealistic target. The system noted that as 'strong collaboration evidence.' But I remember feeling scared when I made that choice. The reflection report captures something real."

### Player Emotions
Vulnerability, surprise at accurate insights, defensiveness about blind spots, validation about strengths, curiosity about how colleagues' profiles compare, motivation to change.

### Leadership Lesson
**Self-awareness requires external feedback processed through internal reflection.** The profile is not a judgment — it's a mirror. The player must decide what to do with what they see. The game provides the data; the player provides the interpretation.

### Gameplay Purpose
- The leadership profile is the game's primary deliverable — not the score, not the badge
- Evidence-based insights prevent the player from dismissing the feedback as generic
- The profile creates a bridge between game behavior and real-world behavior
- Coaching recommendations are specific and actionable, not platitudes

### Expected Behavior Change
The player begins translating game insights into self-knowledge: "In the game, I always avoided risk. In real life, I do the same thing." This is the critical transfer moment — when the player recognizes that the game was not just a game.

---

## Phase 9: Real-World Action (Game End — Next 3 Days)

### Context
The game generates a **Real-World Challenge** based on the player's blind spots. This is a specific, actionable challenge the player commits to completing within one week.

### Corresponding System
The **Challenge Generator** creates challenges from blind spots:
- Blind spot: risk_taking → Challenge: "In the next team meeting, propose an unconventional approach to a current problem. Document the reaction."
- Blind spot: empathy → Challenge: "Have a 15-minute personal conversation with a team member you haven't connected with recently. Ask about their challenges, not their tasks."
- Blind spot: decisiveness → Challenge: "Make a decision you've been postponing within 48 hours. Note what happened because of the delay vs. what happened because of the decision."

### Player Thoughts
"The challenge says I should 'propose an unconventional approach.' That terrifies me. But the game showed me that I always choose the safe option. If I don't do this, I'm confirming my blind spot. If I do it, I'm growing."

### Player Emotions
Motivation mixed with anxiety, accountability (committed publicly to the challenge), hope that change is possible, connection to the game experience ("This is my Summit moment in real life").

### Leadership Lesson
**Insight without action is entertainment.** The game does not consider itself successful if the player merely enjoyed it or learned something. Success = the player takes one concrete action in their real leadership context that they would not have taken without the game.

### Gameplay Purpose
- The challenge connects game learning to real-world application
- The one-week deadline creates urgency without being overwhelming
- The challenge is specific enough to be measurable but open enough to be personal
- The player can mark the challenge as completed, creating a sense of closure

### Expected Behavior Change
The player commits to and (ideally) completes the real-world challenge. This is the game's ultimate behavioral output — not a changed score, but a changed action in the player's actual leadership context.

---

## Phase 10: Long-Term Transfer (One Week After — Ongoing)

### Context
One week after the game, the player has either completed or abandoned their real-world challenge. They return to work with new vocabulary for leadership behavior ("I noticed I was doing the thing the game flagged — I chose control over collaboration again").

### Player Thoughts
"I caught myself about to make the same decision I always make. The game gave me a word for it — 'control tendency.' I stopped and thought: is this the best approach, or is this just my default? I chose differently this time."

"The game showed me that I consistently sacrifice my own advancement for the team. I thought that was a strength. But the profile flagged it as a potential blind spot — 'over-accommodation.' Now I'm thinking about when helping others is genuine leadership and when it's avoidance of standing up for my own ideas."

### Player Emotions
Continued self-awareness, periodic discomfort when recognizing patterns, growing confidence in new behaviors, possible relapse into old patterns, appreciation for the game as a learning experience.

### Leadership Lesson
**Leadership development is a practice, not an event.** The game is a catalyst, not a cure. The player must continue applying what they learned. The game's value increases over time as the player encounters situations that mirror the dilemmas they faced.

### Gameplay Purpose (Post-Game)
- The ChallengeFollowUpService checks for unresolved challenges when the player joins a new game session
- The player's leadership profile from previous games is accessible for comparison
- The game creates a shared language ("Remember when you chose to break that promise?") that persists in the team culture

### Expected Behavior Change
The player demonstrates measurable behavior change in their real leadership context. This is assessed not by the game, but by the player themselves and their colleagues. The game's success metric is: "Did the player do one thing differently because of this experience?"

---

## Journey Metrics

| Phase | Turn Range | Primary Emotion | Primary Lesson | Evidence Generated |
|-------|-----------|-----------------|----------------|-------------------|
| Introduction | Pre-1 | Curiosity | Self-awareness | Baseline |
| Early Confidence | 2-4 | Competence | Competence trap | Pattern baseline |
| First Dilemma | 4-6 | Disorientation | Competing values | First strong signals |
| Increasing Uncertainty | 7-12 | Empathy activation | Leadership = others | Cross-player evidence |
| Relationship Tension | 13-18 | Guilt/responsibility | Relational debt | Promise evidence |
| Major Sacrifice | 19-22 | Moral weight | Trade-offs between goods | Peak evidence |
| Emotional Peak | 23-26 | Peak intensity | Costly leadership | Strongest signal |
| Reflection | End | Vulnerability | External feedback | Profile generated |
| Real-World Action | +3 days | Accountability | Insight → action | Challenge completed |
| Long-Term Transfer | +1 week | Self-awareness | Practice, not event | Behavioral change |

---

## Validation Questions

For every phase, ask:
1. Does this phase create a specific leadership behavior change? If no, redesign.
2. Is the emotional arc genuine or manufactured? If manufactured, redesign.
3. Does the player leave with something they can use on Monday morning? If no, redesign.
4. Would a real leader recognize themselves in this journey? If no, redesign.
5. Does this phase generate evidence that supports the reflection report? If no, redesign.
