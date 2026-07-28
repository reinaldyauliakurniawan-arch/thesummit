# LRA Evidence Map — Complete Gameplay-to-Assessment Mapping

> **Purpose**: Maps every game mechanic to every observable assessment item
> **Status**: TASK 2 (Mapping) + TASK 3 (Gaps) + TASK 4 (Evidence Quality) Complete
> **Scope**: PtP, R1, R2, R3 (31 directly observable items)

---

## Evidence Quality Framework

Before the mapping, here are the universal evidence quality definitions that apply to ALL assessment items:

### Quality Levels

| Level | Label | Definition | Confidence Range |
|-------|-------|-----------|-----------------|
| ⬜ | **Insufficient** | Fewer than 2 independent observations, or observations span only one context type | 0.0–0.24 |
| 🟨 | **Weak** | 2 independent observations, same context type (e.g., only crisis), or observations contain confounds | 0.25–0.49 |
| 🟧 | **Medium** | 3+ independent observations across 2+ context types, mostly consistent direction | 0.50–0.74 |
| 🟩 | **Strong** | 5+ independent observations across 3+ context types, consistent direction, includes at least 1 crisis-level observation | 0.75–0.89 |
| 🟦 | **Repeated** | 7+ observations across all context types (neutral + crisis), consistent direction, pattern stable across game phases | 0.90–1.00 |

### Special Evidence Types

| Type | Label | When Applied |
|------|-------|--------------|
| ↕️ | **Contradictory** | ≥3 observations in opposing directions with similar context weight. Conclusion: "Mixed signals — behavior is context-dependent." Cannot assign a single score. |
| ❓ | **Insufficient** | <2 observations OR all observations are from a single turn type (e.g., only mindset cards). Cannot support ANY conclusion. |

### Observation Context Types

| Context | Weight | Description |
|---------|--------|-------------|
| **Neutral-Basecamp** | 0.8× | Regular decision at individual level |
| **Crisis-Basecamp** | 1.2× | High-pressure decision at individual level |
| **Neutral-Camp** | 1.0× | Regular decision at team level |
| **Crisis-Camp** | 1.4× | High-pressure decision at team level |
| **Neutral-Summit** | 1.2× | Regular decision at leadership level |
| **Crisis-Summit** | 1.6× | High-pressure decision at leadership level |
| **Social (Promise/Debt)** | 1.3× | Inter-player commitment behavior |
| **Cross-Player** | 1.3× | Effect chosen that impacts other players |
| **Consequence (Delayed)** | 1.1× | Behavior revealed through delayed outcome (e.g., chose risky option → suffered later) |
| **Vote** | 1.1× | Group decision behavior |

### Confidence Formula

```
evidence_weight = Σ(observation × context_weight × direction_consistency)
total_possible  = Σ(observation × context_weight)
raw_confidence  = evidence_weight / total_possible (capped at 1.0)
stability        = 1.0 - (direction_changes / max(1, total_observations - 1))
final_confidence = raw_confidence × (0.6 + 0.4 × stability)
```

**Rule**: ANY assessment conclusion requires `final_confidence ≥ 0.50` (Medium). Below 0.50 → return "Insufficient evidence" for that item.

---

## MAPPING: PtP Items

---

### PtP.M1 — Adopting Company Core Values / Integrity Under Pressure

**What the game observes**: Whether a player chooses ethical options when they carry personal cost.

| Card | Option A (Ethical Choice) | Option B (Self-Benefiting Choice) | Evidence Type |
|------|--------------------------|-----------------------------------|--------------|
| BM008 | Tolak arahan tidak etis → MP+2, TT+1, rep+1, flex-1 | Ikuti arahan → SP+2, MP-1, rep-1 | **Direct** — ethical under authority pressure |
| BM007 | Bicara privat, minta dia melapor → TT+1, rep+1, sched rep-2 | Langsung lapor → MP+1, TT-3 | **Direct** — integrity with interpersonal cost |
| BM005 | Sebutkan kontribusi rekanmu → TT+2, rep+1, rep-1 | Terima pujian → MP+1, rep+2 | **Direct** — honesty at personal reputation cost |
| SM012 | Audit semua tim → MP+2, TT+1, rep+1 | Perbaiki diam-diam → SP+1, TT-1 | **Direct** — systemic integrity vs convenience |
| SS012 | Laporkan, hentikan produk → MP+3, TT+2, rep+1, resources-2 | Diam, fix diam-diam → SP+2, MP-2, TT-2 | **Direct** — ethics at major resource cost |
| CM007 | Tolak, laporkan HR → MP+3, TT+1, rep-2 | Ikuti arahan → SP+1, MP-2, TT-3 | **Direct** — integrity under management pressure |
| SM009 | Tegaskan cara tidak sesuai → MP+2, TT+1 | Biarkan karena hasil → SP+2, TT-3 | **Direct** — values over results |

**Cards generating NO evidence for PtP.M1**: 53 of 60
**Evidence density**: 7/60 cards (11.7%)

**Minimum for Medium confidence**: 3 observations across ≥2 context types (e.g., 1 Basecamp + 1 Camp + 1 Summit)

**GAP ASSESSMENT**: ✅ **ADEQUATE** — 7 cards across all 3 levels, including crisis cards at each level. Integrity is well-tested. No changes needed.

---

### PtP.M2 — Ego Rendah & Open to Input (Receptivity to Feedback)

**What the game observes**: Whether a player chooses options that seek input, accept feedback, or incorporate others' perspectives.

| Card | Low-Ego / Open-to-Input Option | High-Ego / Defensive Option | Evidence Type |
|------|-------------------------------|----------------------------|--------------|
| BM002 | Terima feedback, tanya detail → MP+1, TT+1, rep+1 | Diskusikan balik → MP+2, SP+1, TT-1, rep-1 | **Direct** — accepting harsh feedback |
| CM003 | Terima, komit perbaikan → MP+2, TT+2, rep-1 | Jelaskan konteks → MP+1, SP+1, TT-1, rep+1 | **Direct** — accepting negative retrospective feedback |
| SM006 | Paksa diskusi, minta pendapat → MP+1, TT+2 | Ambil keputusan sendiri → SP+2, TT-2 | **Direct** — soliciting input from silent leads |
| SM002 | Workshop bersama lead → MP+1, TT+2 | Susun sendiri → SP+2, TT-2 | **Direct** — collaborative vs solo vision |
| SM004 | Breakdown bersama → MP+1, TT+2 | Dokumen sendiri → SP+2, TT-1 | **Direct** — inclusive strategy breakdown |
| SS003 | Pilot 1-2 tim, buktikan → MP+1, SP+2, TT+1 | Rollout sekaligus → SP+2, TT-1 | **Direct** — evidence-based adoption vs top-down |

**Evidence density**: 6/60 cards (10%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Only 6 cards. Basecamp has only 1 card (BM002). No Camp skillset cards test this. **Smallest fix**: Modify BS009 (conflict resolution) to add an option where the player explicitly seeks input from both parties before deciding. Modify CS005 (scope change) to add a "seek team input before negotiating" option.

---

### PtP.M3 — Mau Belajar Terus (Continuous Learning Drive)

**What the game observes**: Whether a player invests in learning opportunities, especially at personal cost.

| Card | Learning-Oriented Option | Non-Learning Option | Evidence Type |
|------|--------------------------|---------------------|--------------|
| BM006 | Ajukan proyek improvement → MP+2, flex+1, rep-1 | Belajar skill baru → SP+2, flex-1 | **Partial** — Both options involve growth; A = initiative, B = skill |
| BS002 | Training resmi 3 hari → SP+2, MP+1, resources-1 | Belajar mandiri → SP+1, flex+1, TT+1 | **Direct** — formal learning investment |
| BS006 | Ambil sertifikasi → SP+3, MP-1, resources-2 | Tunda, fokus proyek → MP+1, SP+1, TT+1 | **Direct** — certification at significant cost |
| BM004 | Pecah jadi milestone (self-reliance) | Cari mentor (learning) → MP+1, TT+1, rep-1 | **Partial** — B = seeking mentorship = learning |

**Evidence density**: 4/60 cards (6.7%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Only 4 cards touch learning. None at Camp or Summit level. Learning is a PtP gate item but barely tested after Basecamp. **Smallest fix**: Add a Camp card (CM or CS) where the leader chooses between handling a problem themselves vs. learning a new framework to solve it systemically. Add a Summit card where the leader chooses between a proven approach and investing in learning a new methodology for the team.

---

### PtP.M4 — Semangat Get Things Done (Execution Drive)

**What the game observes**: Whether a player persists through obstacles and delivers despite adversity.

| Card | Persistence/Execution Option | Avoidance/Quit Option | Evidence Type |
|------|-----------------------------|----------------------|--------------|
| BS005 | Negosiasi deadline → SP+1, MP+1, rep-1 | Lembur selesaikan → SP+2, MP-2 | **Partial** — Both show drive; A = smart, B = brute |
| BS008 | Akui kesalahan, revisi → MP+2, TT+1, rep-1 | Kerja lembur → SP+2, MP-2 | **Direct** — owning failure + fixing vs brute force |
| BM009 | Terima keputusan, fokus ke depan → MP+2, TT+1 | Minta penjelasan → MP+1, TT-1 | **Direct** — resilience after cancellation |
| BM004 | Pecah jadi milestone kecil → MP+2, SP+1 | Cari mentor → MP+1, TT+1, rep-1 | **Direct** — self-reliance in tackling hard tasks |
| CM009 | Perjuangkan tim → MP+1, TT+2, rep-1 | Terima dan motivasi → SP+1, TT-2 | **Direct** — fighting for team under pressure |

**Evidence density**: 5/60 cards (8.3%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Execution drive is tested indirectly. The game more often tests HOW you execute (collaborative vs solo) rather than WHETHER you execute. No card directly tests persisting when resources are extremely low and giving up is the easy option. **Smallest fix**: Modify BM003 (networking event) — option A could include persisting despite exhaustion as a sub-signal. No new card needed; the existing cards provide adequate coverage when combined with consequence tracking (did the player complete the game? did they reach Summit?).

**NOTE**: Game completion itself IS evidence for PtP.M4 — a player who completes all rounds demonstrates execution drive. Track: `% of game completed`, `number of turns played to completion`.

---

### PtP.M5 — Peduli dengan Orang Lain (Others-Development Orientation)

**What the game observes**: Whether a player invests personal resources in others' growth and well-being.

| Card | Others-Oriented Option | Self-Oriented Option | Evidence Type |
|------|----------------------|---------------------|--------------|
| BM005 | Sebutkan kontribusi rekanmu → TT+2, rep+1, rep-1 | Terima pujian → MP+1, rep+2 | **Direct** — sharing credit at personal cost |
| CM001 | Ajak bicara, redistribusi beban → MP+1, TT+2 | Tuntut standar sama → SP+1, TT-1 | **Direct** — caring for struggling team member |
| CM004 | Pilih yang belum pernah present → MP+1, TT+1, rep-1 | Pilih yang paling siap → SP+1, rep+2 | **Direct** — developing others' opportunity |
| CM006 | Program mentoring → MP+1, TT+2 | Rotasi tugas → SP+2, TT-1 | **Direct** — investing in team development |
| CS004 | Hire growth mindset → MP+1, TT+1, resources-1 | Hire pengalaman → SP+2, rep+1 | **Direct** — choosing growth over convenience |
| CS006 | Buddy + learning plan → SP+2, MP+1, TT+1 | Self-service docs → SP+1, TT-1 | **Direct** — investing in onboarding quality |
| SM001 | Forum berbagi challenge → MP+1, TT+2 | Standar minimum → SP+2, TT-1 | **Direct** — investing in leader development |
| SM003 | Siapkan successor → MP+1, SP+1, TT+1 | Promosikan sekarang → SP+2, TT-2 | **Direct** — succession investment |
| SM008 | Bicara privat, bantu perbaiki → MP+1, TT+1 | Angkat di forum → SP+1, TT-2 | **Direct** — private coaching |
| SM010 | Tolak tawaran, tetap bersama → MP+2, TT+3, rep+2 | Terima tawaran → SP+3, TT-2 | **Direct** — loyalty to team at major cost |
| SS008 | Coaching intensif tentang PM → MP+1, SP+1, TT+2 | Ambil alih PM → SP+2, TT-2 | **Direct** — coaching vs taking over |

**Evidence density**: 11/60 cards (18.3%)

**GAP ASSESSMENT**: ✅ **WELL-COVERED** — One of the best-covered competencies. 11 cards across all levels, consistently testing investment in others.

---

### PtP.S1 — Root Cause Analysis

**What the game observes**: Whether a player investigates underlying causes vs. applies surface fixes.

| Card | Root-Cause Option | Surface-Fix Option | Evidence Type |
|------|------------------|-------------------|--------------|
| BS004 | Perbaiki kritis + guideline → SP+1, TT+1, flex+1 | Tulis ulang dari awal → SP+2, MP+1 | **Direct** — systemic fix vs rewrite |
| BS008 | Akui kesalahan, revisi timeline → MP+2, TT+1 | Kerja lembur → SP+2, MP-2 | **Direct** — addressing root cause vs symptom |
| CS002 | Reviewer tetap per modul → SP+1, TT+1 | Checklist wajib → SP+2, TT-1 | **Direct** — relationship-based solution vs process |
| SS005 | Audit semua tim, perbaiki proses → MP+2, TT+1 | Sanksi tegas → SP+1, TT-3 | **Direct** — systemic fix vs punitive |
| SS009 | Bureaucracy audit → SP+2, MP+1, TT+1 | KPI baru → SP+1, rep+1 | **Direct** — root cause analysis vs new metric |
| SM012 | Audit semua tim → MP+2, TT+1 | Perbaiki diam-diam → SP+1 | **Direct** — systemic vs individual fix |

**Evidence density**: 6/60 cards (10%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — 6 cards is decent but all test root cause at team/system level. No card tests root cause analysis at the **individual task level** (e.g., "Bug keeps recurring — do you investigate why or just fix it again?"). **Smallest fix**: Add a Basecamp skillset card where the player faces a recurring personal error and chooses between investigating the pattern vs just working around it.

---

### PtP.S2 — Komunikasi Asertif (Assertive Communication)

**What the game observes**: Whether a player directly addresses difficult topics, expresses disagreement, or confronts issues.

| Card | Assertive Option | Avoidant/Aggressive Option | Evidence Type |
|------|-----------------|---------------------------|--------------|
| BS003 | Telepon/ajak meeting → SP+1, MP+1, rep-1 | Kirim ulang email → SP+2 | **Direct** — direct escalation vs passive |
| BS009 | Fasilitasi titik temu → SP+1, TT+2 | Pilih yang benar → SP+1, TT-2 | **Direct** — facilitation vs taking sides |
| BM002 | Terima feedback, tanya detail → MP+1, TT+1, rep+1 | Diskusikan balik → MP+2, TT-1, rep-1 | **Partial** — B is assertive but defensive |
| BM008 | Tolak dan tawarkan alternatif → MP+2, TT+1, rep+1 | Ikuti arahan → SP+2, rep-1 | **Direct** — assertive refusal |
| CM007 | Tolak, laporkan HR → MP+3, TT+1, rep-2 | Ikuti arahan → SP+1, TT-3 | **Direct** — assertive under pressure |
| CM008 | Hentikan debat, fasilitasi → MP+1, SP+1, TT+1 | Biarkan selesai sendiri → MP+1, TT-2 | **Direct** — assertive intervention |
| SS004 | Transparan, fokus pembelajaran → MP+2, TT+1 | Highlight berhasil → SP+2, TT-1 | **Direct** — honest reporting vs spin |

**Evidence density**: 7/60 cards (11.7%)

**GAP ASSESSMENT**: ✅ **ADEQUATE** — 7 cards across all levels. Good coverage of assertive communication in various contexts. No changes needed.

---

## MAPPING: R1 Items

---

### R1.M1 — Benchmark Pursuit (Excellence Orientation)

| Card | Benchmark/Growth Option | Comfort/Status-Quo Option | Evidence Type |
|------|------------------------|--------------------------|--------------|
| BM006 | Ajukan proyek improvement → MP+2, flex+1, rep-1 | Belajar skill baru → SP+2, flex-1 | **Partial** — A = initiative, B = skill growth |
| BS006 | Ambil sertifikasi → SP+3, MP-1, resources-2 | Tunda, fokus proyek → MP+1, SP+1, TT+1 | **Direct** — investing in measurable standard |

**Evidence density**: 2/60 cards (3.3%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Only 2 cards. Benchmark pursuit is a key R1 mindset item but barely tested. No card directly presents an external standard to pursue. **Smallest fix**: Modify BS006 to explicitly mention an industry benchmark/certification as the target. Add a Basecamp mindset card where the player discovers a competitor's superior method and chooses whether to investigate and adopt it.

---

### R1.M2 — Target Ownership (Proactive Ownership)

| Card | Proactive/Ownership Option | Passive/Wait Option | Evidence Type |
|------|---------------------------|---------------------|--------------|
| BM004 | Pecah jadi milestone → MP+2, SP+1 | Cari mentor → MP+1, TT+1 | **Partial** — A = self-reliance |
| BM009 | Terima, fokus ke depan → MP+2, TT+1 | Minta penjelasan → MP+1, TT-1 | **Direct** — ownership vs questioning |
| BS005 | Negosiasi deadline → SP+1, MP+1, rep-1 | Lembur → SP+2, MP-2 | **Partial** — Both show ownership |
| SM006 | Paksa diskusi → MP+1, TT+2 | Ambil sendiri → SP+2, TT-2 | **Partial** — ownership of team decision |
| SM007 | Libatkan lead → MP+2, TT+3 | Tentukan sendiri → SP+2, TT-3 | **Direct** — shared ownership vs top-down |

**Evidence density**: 5/60 cards (8.3%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Target ownership is tested indirectly through "takes initiative" cards. No card directly tests the specific behavior of "pursuing targets without being asked" because the game always presents choices. The game structure itself (you must choose) limits evidence for proactive initiation. **Smallest fix**: Track whether the player makes decisions quickly (turn response time) as a proxy for proactive ownership. No card changes needed.

---

### R1.S1 — Consistent Output Delivery (Reliability)

**Game-level evidence** (not card-specific):

| Evidence Source | What It Measures | Evidence Type |
|----------------|-----------------|--------------|
| **Game completion** | Did the player finish the game? | **Proxy** — completing = delivering |
| **Level reached** | How far did they climb? (basecamp/camp/summit) | **Proxy** — progress = delivery |
| **Stat floors** | How often did MP/SP/TT hit 0? | **Proxy** — hitting 0 = failure to sustain |
| **Missed opportunities** (Reflection Engine) | Did the player consistently choose lower-impact options? | **Direct** — consistent underdelivery |
| **Consequence management** | Did delayed effects from early choices cascade negatively? | **Proxy** — poor planning = inconsistent delivery |

**Evidence density**: Systemic (not card-specific)

**GAP ASSESSMENT**: ✅ **ADEQUATE** — Reliability is measured through overall game performance rather than individual cards. The scoring system (level reached, stat floors, consistency of positive outcomes) captures this. No card changes needed.

---

### R1.S2 — Proactive Reporting

| Card | Reporting/Transparent Option | Silent/Withholding Option | Evidence Type |
|------|---------------------------|--------------------------|--------------|
| BS003 | Telepon/ajak meeting → rep-1 | Kirim ulang email → SP+2 | **Partial** — A = proactive escalation |
| BM007 | Bicara privat, minta dia melapor → TT+1, rep+1 | Langsung lapor → MP+1, TT-3 | **Partial** — A = private reporting approach |
| SM008 | Bicara privat, bantu → MP+1, TT+1 | Angkat di forum → SP+1, TT-2 | **Partial** — both involve reporting |

**Evidence density**: 3/60 cards (5%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Only 3 cards, and none directly test "proactive reporting to superiors." The game has no explicit "report progress" mechanic. **Smallest fix**: Add a Camp skillset card (CS010) where the player discovers a project risk and chooses between: (A) immediately escalating to management with data, or (B) trying to fix it first and reporting only if it fails. This directly tests proactive reporting.

---

### R1.S3 — Follow Existing System and Rules

| Card | System-Following Option | System-Bypassing Option | Evidence Type |
|------|------------------------|------------------------|--------------|
| CS002 | Checklist wajib code review → SP+2, TT-1 | Reviewer tetap → SP+1, TT+1 | **Direct** — formal system vs informal |
| CS003 | Timeboxing + parking lot → SP+2, rep+1 | Kurangi meeting, async → SP+1, TT+1 | **Partial** — A = structured process |
| BM001 | Klarifikasi scope → MP+1, flex+1 | Langsung eksekusi → SP+2, flex-1 | **Partial** — A = following process (scope first) |

**Evidence density**: 3/60 cards (5%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Only 3 cards, and none directly test compliance with existing systems. Most cards test BUILDING systems, not FOLLOWING them. **Smallest fix**: Modify BS004 (documentation) to frame it as: "Company has a documentation standard you find tedious. Do you (A) follow the standard even though it slows you, or (B) use your own faster approach?" This directly tests system compliance vs personal preference.

---

### R1.S4 — Personal Work System (Systematization)

| Card | System-Building Option | Ad-Hoc Option | Evidence Type |
|------|----------------------|--------------|--------------|
| BS004 | Perbaiki kritis + guideline → SP+1, TT+1, flex+1 | Tulis ulang dari awal → SP+2 | **Direct** — creating reusable guideline |
| CS002 | Checklist wajib → SP+2, TT-1 | Reviewer tetap → SP+1, TT+1 | **Direct** — systematizing code review |
| CS003 | Timeboxing + parking lot → SP+2, rep+1 | Kurangi meeting → SP+1, TT+1 | **Direct** — systematizing meetings |
| CS006 | Buddy + learning plan → SP+2, TT+1 | Self-service docs → SP+1, TT-1 | **Direct** — systematizing onboarding |

**Evidence density**: 4/60 cards (6.7%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — 4 cards but all at Camp level. No Basecamp card tests building personal systems (which is the R1 scope). **Smallest fix**: Modify BS005 (3 tasks Friday deadline) — add a third option or modify option A to include "creating a personal priority system for similar situations in the future."

---

## MAPPING: R2 Items

---

### R2.M1 — Success Through Team (Team-Empowerment Mindset)

| Card | Empowerment Option | Control/Do-It-Myself Option | Evidence Type |
|------|------------------|--------------------------|--------------|
| BS007 | Kumpulkan tim, delegasikan → SP+1, TT+2, rep+1 | Ambil alih sendiri → SP+2, TT-2 | **Direct** — delegate vs do |
| CS008 | Fasilitasi diskusi, cari hybrid → MP+1, TT+1 | Putuskan sendiri → SP+2, TT-2 | **Direct** — facilitate vs dictate |
| CS009 | Negosiasi transisi + KT → MP+1, TT+1 | Distribusikan segera → SP+2, TT-1 | **Direct** — invest in team vs quick fix |
| SM006 | Paksa diskusi → MP+1, TT+2 | Ambil keputusan sendiri → SP+2, TT-2 | **Direct** — empower vs control |
| SM007 | Libatkan lead → MP+2, TT+3 | Tentukan sendiri → SP+2, TT-3 | **Direct** — inclusive vs top-down |
| SM008 | Coaching privat → MP+1, TT+1 | Public example → SP+1, TT-2 | **Direct** — develop vs punish |
| SS008 | Coaching intensif → MP+1, TT+2 | Ambil alih PM → SP+2, TT-2 | **Direct** — coach vs take over |

**Evidence density**: 7/60 cards (11.7%)

**GAP ASSESSMENT**: ✅ **WELL-COVERED** — Empowerment is a central game theme. 7 cards directly test this.

---

### R2.M2 — Values Managerial/Systemic Work

| Card | System-Building Option | Technical-Work Option | Evidence Type |
|------|----------------------|---------------------|--------------|
| CS002 | Checklist wajib → SP+2, TT-1 | Reviewer tetap → SP+1, TT+1 | **Direct** — building process vs relationship |
| CS003 | Timeboxing + parking lot → SP+2, rep+1 | Kurangi meeting → SP+1, TT+1 | **Direct** — systemic meeting fix |
| CS006 | Buddy + learning plan → SP+2, TT+1 | Self-service docs → SP+1 | **Direct** — systemic onboarding |
| SM009 | Tegaskan budaya → MP+2, TT+1 | Biarkan karena hasil → SP+2, TT-3 | **Direct** — investing in culture over results |
| SS009 | Bureaucracy audit → SP+2, TT+1 | KPI baru → SP+1, rep+1 | **Direct** — systemic fix |
| SS005 | Audit semua tim, perbaiki proses → MP+2, TT+1 | Sanksi tegas → SP+1, TT-3 | **Direct** — systemic vs punitive |

**Evidence density**: 6/60 cards (10%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — 6 cards is decent, but most test this at Camp/Summit. Only 1 Basecamp card (BS004 indirectly). No card explicitly presents the dilemma of "boring system work vs exciting technical work" which is the core R2.M2 tension. **Smallest fix**: Modify CS002 to explicitly frame the dilemma as "senior wants you to build a code review system (managerial work) vs fixing bugs yourself (technical work)."

---

### R2.S1 — Job Design & Delegation

| Card | Right-Person Delegation | Wrong-Person/Random Delegation | Evidence Type |
|------|------------------------|-------------------------------|--------------|
| CS001 | Rotasi tugas adil → MP+1, TT+2 | Assign by expertise → SP+2, TT-1 | **Direct** — fair rotation vs expertise-only |
| CS004 | Hire growth mindset → MP+1, TT+1 | Hire pengalaman → SP+2, TT-1 | **Direct** — values-based selection |
| CS005 | Negosiasi ke sprint berikutnya → MP+1, TT+1 | Terima perubahan → SP+1, TT-1 | **Partial** — scope protection as delegation |
| SS002 | Pooled budget, prioritas bersama → MP+1, TT+2 | Alokasi proporsional → SP+2, TT-1 | **Direct** — collaborative resource allocation |

**Evidence density**: 4/60 cards (6.7%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Delegation is tested but no card directly presents a mismatch between person and task where the player must deselect someone. The hardest part of R2.S1 is REMOVING wrong people. **Smallest fix**: Add a Camp skillset card where the player must choose between: (A) keeping a team member in a role they're wrong for (avoiding conflict) or (B) moving them to a better-fit role (right person, right seat, but requires a difficult conversation).

---

### R2.S2 — Selecting and Deselecting Team Members

| Card | Selection/Deselection Option | Avoidance Option | Evidence Type |
|------|---------------------------|----------------|--------------|
| CS004 | Hire growth mindset → MP+1, TT+1 | Hire pengalaman → SP+2, TT-1 | **Direct** — values-based selection |
| CM007 | Tolak, laporkan HR → MP+3, TT+1, rep-2 | Ikuti arahan → SP+1, TT-3 | **Direct** — deselecting unethical management |
| SM005 | Seleksi transparan → MP+2, TT+1, rep+1, rep-2 | Pilih diam-diam → SP+1, TT-3, rep-2 | **Direct** — transparent selection |

**Evidence density**: 3/60 cards (5%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Only 3 cards. The critical "deselection" behavior (removing wrong people) is only tested in CM007 (extreme ethical case). No card tests the common dilemma of "a team member who is nice but incompetent — do you address it or avoid it?" **Smallest fix**: Modify CM001 (declining team member) — currently the options are about support vs standards. Add a third dimension: the player discovers the decline is due to a fundamental skill mismatch (not personal problems). Now the choice includes potentially moving them out of the role.

---

### R2.S3 — Performance Monitoring

| Card | Monitoring Option | Non-Monitoring Option | Evidence Type |
|------|------------------|---------------------|--------------|
| SS001 | Framework leading+lagging → SP+2, TT+1 | OKR standar → SP+2, TT-1 | **Direct** — sophisticated monitoring vs standard |
| CS007 | Blameless post-mortem → MP+1, TT+2 | Cari siapa salah → SP+1, TT-3 | **Partial** — investigation = monitoring |
| SS004 | Transparan, fokus pembelajaran → MP+2, TT+1 | Highlight berhasil → SP+2, TT-1 | **Partial** — honest assessment = monitoring |

**Evidence density**: 3/60 cards (5%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Performance monitoring as a RHYTHM (check-ins, regular monitoring) is not directly tested. Cards test one-off monitoring decisions but not the HABIT of monitoring. **Smallest fix**: Track monitoring behavior through CONSEQUENCE patterns. If a player consistently addresses problems before they escalate (choosing root-cause options), this IS evidence of good monitoring rhythm. No card changes needed if we interpret monitoring broadly. But for direct evidence, add a Camp card about choosing between: (A) establishing weekly 1:1 check-in rhythm, or (B) only meeting when problems arise.

---

### R2.S4 — Having Tough Conversations

| Card | Tough Conversation Option | Avoidance Option | Evidence Type |
|------|--------------------------|----------------|--------------|
| CM002 | Tetapkan ekspektasi jelas → SP+2, TT-2 | Pertahankan hubungan → TT+2, rep-1 | **Direct** — direct expectations vs avoidance |
| CM007 | Tolak, laporkan HR → MP+3, TT+1, rep-2 | Ikuti arahan → SP+1, TT-3 | **Direct** — tough ethical stand |
| CM008 | Hentikan debat, fasilitasi → MP+1, TT+1 | Biarkan selesai sendiri → TT-2 | **Direct** — intervening in conflict |
| SS005 | Audit semua tim, perbaiki proses → MP+2, TT+1 | Sanksi tegas → SP+1, TT-3 | **Partial** — systemic approach vs punitive |
| BM007 | Bicara privat, minta dia melapor → TT+1, rep+1 | Langsung lapor → MP+1, TT-3 | **Partial** — private confrontation |
| SM008 | Bicara privat, bantu perbaiki → MP+1, TT+1 | Angkat di forum → SP+1, TT-2 | **Direct** — private tough conversation |
| SS008 | Coaching intensif tentang PM → MP+1, TT+2 | Ambil alih PM → SP+2, TT-2 | **Direct** — addressing underperformance |

**Evidence density**: 7/60 cards (11.7%)

**GAP ASSESSMENT**: ✅ **ADEQUATE** — 7 cards across all levels test willingness to have difficult conversations.

---

### R2.S5 — Building Team Engagement (Psychological Safety)

| Card | Engagement-Building Option | Engagement-Damaging Option | Evidence Type |
|------|--------------------------|--------------------------|--------------|
| BS009 | Fasilitasi titik temu → SP+1, TT+2 | Pilih yang benar → SP+1, TT-2 | **Direct** — mediation creates safety |
| CS001 | Rotasi tugas adil → MP+1, TT+2 | Assign by expertise → SP+2, TT-1 | **Direct** — fairness builds engagement |
| CS007 | Blameless post-mortem → MP+1, TT+2 | Cari siapa salah → SP+1, TT-3 | **Direct** — psychological safety vs blame |
| CM008 | Hentikan debat, fasilitasi → MP+1, TT+1 | Biarkan → TT-2 | **Direct** — creating safe space |
| SM006 | Paksa diskusi → MP+1, TT+2 | Ambil sendiri → SP+2, TT-2 | **Direct** — creating voice safety |
| SS006 | Mediasi, fasilitasi data → MP+1, TT+2 | Putuskan, larang → SP+2, TT-3 | **Direct** — safe dialogue vs suppression |
| SS010 | Workshop merged culture → MP+2, TT+3 | Tetapkan existing → SP+2, TT-3 | **Direct** — inclusive culture building |

**Evidence density**: 7/60 cards (11.7%)

**GAP ASSESSMENT**: ✅ **WELL-COVERED** — Psychological safety is well-tested through multiple lenses.

---

### R2.S6 — Empowering Through Questions and Feedback (Coaching)

| Card | Coaching Option | Answer-Giving Option | Evidence Type |
|------|----------------|---------------------|--------------|
| BM004 | Cari mentor → MP+1, TT+1 (seeking coaching) | Pecah milestone → MP+2, SP+1 | **Reverse** — seeking coaching is evidence of valuing it |
| CM004 | Pilih yang belum pernah → MP+1, TT+1 (creating opportunity) | Pilih yang siap → SP+1, rep+2 | **Direct** — development opportunity |
| CM005 | Cari proyek wajib kolaborasi → MP+1, TT+1 | Feedback langsung → MP+1, rep+1 | **Direct** — experiential coaching |
| CM006 | Program mentoring → MP+1, TT+2 | Rotasi tugas → SP+2, TT-1 | **Direct** — structured coaching |
| SM001 | Forum berbagi challenge → MP+1, TT+2 | Standar minimum → SP+2, TT-1 | **Direct** — peer coaching |
| SM008 | Bicara privat, bantu perbaiki → MP+1, TT+1 | Angkat di forum → SP+1, TT-2 | **Direct** — private coaching |
| SS008 | Coaching intensif → MP+1, TT+2 | Ambil alih PM → SP+2, TT-2 | **Direct** — coaching vs takeover |

**Evidence density**: 7/60 cards (11.7%)

**GAP ASSESSMENT**: ✅ **WELL-COVERED** — Coaching is well-represented. However, no card tests the SPECIFIC behavior of "asking questions instead of giving answers" — most coaching options involve taking action FOR or WITH the person. **Smallest fix**: Modify SM008 to make option A explicitly about "asking coaching questions to help the team lead discover the issue themselves" vs option B "telling them what to do."

---

### R2.S7 — Basic Budgeting

| Card | Budget-Conscious Option | Budget-Wasteful Option | Evidence Type |
|------|------------------------|----------------------|--------------|
| SS002 | Pooled budget, prioritas bersama → MP+1, TT+2, resources-1 | Alokasi proporsional → SP+2, TT-1 | **Direct** — efficient allocation |
| BS005 | Negosiasi deadline → SP+1, MP+1 | Lembur → SP+2, MP-2, resources-1 | **Partial** — resource management |
| BM006 | Ajukan proyek → rep-1, flex+1 | Belajar skill → resources-1 | **Partial** — resource investment decision |
| SM007 | Libatkan lead dalam reprioritization → MP+2, TT+3 | Tentukan sendiri → SP+2, TT-3 | **Direct** — budget prioritization |

**Evidence density**: 4/60 cards (6.7%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Budgeting is tested indirectly through resource management. No card directly presents a budget tradeoff (e.g., "You have X budget — do you invest in A or B?"). **Smallest fix**: Modify SS002 to explicitly frame the dilemma around budget constraints: "Total training budget = 1x. Requests = 3x. Choose allocation strategy."

---

### R2.S8 — Building Team Workflow/SOP

| Card | Workflow-Building Option | Non-Systematic Option | Evidence Type |
|------|------------------------|----------------------|--------------|
| CS002 | Checklist wajib code review → SP+2, TT-1 | Reviewer tetap → SP+1, TT+1 | **Direct** — SOP creation |
| CS003 | Timeboxing + parking lot → SP+2, rep+1 | Kurangi meeting → SP+1, TT+1 | **Direct** — meeting SOP |
| CS006 | Buddy + learning plan → SP+2, TT+1 | Self-service docs → SP+1, TT-1 | **Direct** — onboarding SOP |
| BS004 | Perbaiki kritis + guideline → SP+1, TT+1 | Tulis ulang → SP+2 | **Direct** — documentation SOP |
| SS009 | Bureaucracy audit → SP+2, TT+1 | KPI baru → SP+1, rep+1 | **Direct** — workflow optimization |

**Evidence density**: 5/60 cards (8.3%)

**GAP ASSESSMENT**: ✅ **ADEQUATE** — SOP/workflow building is well-tested, especially at Camp level.

---

### R2.S9 — Proactive Upward Communication & Cross-Team Coordination

| Card | Proactive Communication Option | Reactive/Silo Option | Evidence Type |
|------|------------------------------|---------------------|--------------|
| BS003 | Telepon/ajak meeting → SP+1, MP+1, rep-1 | Kirim ulang email → SP+2 | **Direct** — proactive escalation |
| CS008 | Fasilitasi diskusi → MP+1, TT+1 | Putuskan sendiri → SP+2, TT-2 | **Partial** — cross-subteam coordination |
| CS005 | Negosiasi ke sprint berikutnya → MP+1, TT+1 | Terima perubahan → SP+1, TT-1 | **Partial** — client coordination |
| SS006 | Mediasi → MP+1, TT+2 | Putuskan arsitektur → SP+2, TT-3 | **Direct** — cross-team facilitation |
| SS010 | Workshop merged culture → MP+2, TT+3 | Tetapkan existing → SP+2, TT-3 | **Direct** — cross-team integration |

**Evidence density**: 5/60 cards (8.3%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Cross-team is decent, but UPWARD communication (reporting to superiors) is barely tested. **Smallest fix**: Modify CM009 (targets raised 50%) — option A already involves upward communication (perjuangkan tim ke manajement), which is good. Add explicit "reporting progress to management" as a behavior tag for this option.

---

## MAPPING: R3 Items

---

### R3.M1 — Assesses Subordinates on Leadership Quality

| Card | Leadership-Focused Option | Output-Focused Option | Evidence Type |
|------|--------------------------|----------------------|--------------|
| SM001 | Forum berbagi challenge → MP+1, TT+2 | Standar minimum → SP+2, TT-1 | **Direct** — discussing leadership challenges |
| SM006 | Paksa diskusi → MP+1, TT+2 | Ambil keputusan sendiri → SP+2, TT-2 | **Direct** — demanding leadership voice |
| SM008 | Bicara privat, bantu → MP+1, TT+1 | Angkat di forum → SP+1, TT-2 | **Direct** — developing leadership quality |
| SS008 | Coaching intensif tentang PM → MP+1, TT+2 | Ambil alih PM → SP+2, TT-2 | **Direct** — coaching leadership skill |

**Evidence density**: 4/60 cards (6.7%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — 4 cards test this, but no card directly tests ASSESSING leadership quality (evaluating how well someone leads). Cards test coaching/developing but not the evaluative component. **Smallest fix**: Modify SM001 to include an explicit assessment dimension: "Before the forum, review each lead's leadership quality — what criteria do you use?"

---

### R3.M2 — Acts on Best Available Information (Decisiveness Under Uncertainty)

| Card | Decisive Option | Indecisive/Wait Option | Evidence Type |
|------|----------------|----------------------|--------------|
| BM008 | Tolak dan tawarkan alternatif → MP+2, TT+1 | Ikuti arahan → SP+2, rep-1 | **Partial** — decisive ethical stand |
| SM006 | Paksa diskusi → MP+1, TT+2 | Ambil keputusan sendiri → SP+2, TT-2 | **Partial** — decisive facilitation |
| SS003 | Pilot 1-2 tim, buktikan → MP+1, TT+1 | Rollout sekaligus → SP+2, TT-1 | **Direct** — evidence-based decisiveness |
| SS007 | Kumpulkan lead, breakdown → MP+2, TT+2 | Akui baru tahu → MP+1, TT-2 | **Direct** — decisive action under surprise |
| BM001 | Klarifikasi scope → MP+1, flex+1 | Langsung eksekusi → SP+2, flex-1 | **Partial** — seeking info vs action |

**Evidence density**: 5/60 cards (8.3%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Decisiveness is tested but often conflated with "collaborative vs solo" rather than "decisive vs indecisive." No card directly tests making a decision with incomplete information where waiting would be the default. **Smallest fix**: The existing cards provide adequate evidence when interpreted through the "made a choice under uncertainty" lens (every card choice IS a decision under some uncertainty).

---

### R3.S1 — Assessing Leadership Performance

| Card | Structured Assessment Option | Unstructured Option | Evidence Type |
|------|---------------------------|-------------------|--------------|
| SS001 | Framework leading+lagging → SP+2, TT+1 | OKR standar → SP+2, TT-1 | **Direct** — sophisticated assessment framework |
| CS007 | Blameless post-mortem → MP+1, TT+2 | Cari siapa salah → SP+1, TT-3 | **Partial** — systemic assessment |
| SS004 | Transparan, fokus pembelajaran → MP+2, TT+1 | Highlight berhasil → SP+2, TT-1 | **Direct** — honest assessment |

**Evidence density**: 3/60 cards (5%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Only SS001 directly tests structured assessment. This is a key R3 skillset item. **Smallest fix**: Modify SS001 to more explicitly frame the dilemma: "Your leads have very different performance but you need one framework. Do you (A) invest time building a custom framework, or (B) apply a generic standard?" Add behavior tags for "structured_assessment" vs "generic_assessment."

---

### R3.S2 — Organizational Design

| Card | Clear Structure Option | Unclear Structure Option | Evidence Type |
|------|----------------------|------------------------|--------------|
| SM001 | Forum berbagi challenge → MP+1, TT+2 | Standar minimum → SP+2, TT-1 | **Partial** — structural discussion |
| SS009 | Bureaucracy audit → SP+2, TT+1 | KPI baru → SP+1, rep+1 | **Partial** — structural optimization |

**Evidence density**: 2/60 cards (3.3%)

**GAP ASSESSMENT**: 🔴 **SIGNIFICANT GAP** — Only 2 cards partially test org design. No card directly presents a structural dilemma (e.g., "Your 3 team leads have overlapping responsibilities — how do you redesign?"). **Smallest fix**: Add a Summit skillset card where the player must restructure reporting lines after discovering accountability gaps between teams.

---

### R3.S3 — Selecting and Developing Leaders of Others

| Card | Development-Oriented Option | Shortcut Option | Evidence Type |
|------|---------------------------|----------------|--------------|
| SM003 | Siapkan successor → MP+1, SP+1, TT+1 | Promosikan sekarang → SP+2, TT-2 | **Direct** — succession planning |
| SM011 | Konfrontasi privat, beri kesempatan → TT+1, MP+1 | Eskalasi ke HR → MP+1, TT-3 | **Direct** — developing vs discarding |
| SS008 | Coaching intensif → MP+1, TT+2 | Ambil alih PM → SP+2, TT-2 | **Direct** — developing leadership |
| CS004 | Hire growth mindset → MP+1, TT+1 | Hire pengalaman → SP+2, TT-1 | **Direct** — development-oriented selection |

**Evidence density**: 4/60 cards (6.7%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — 4 cards test leader development but no card tests building a SYSTEMATIC development program (vs ad-hoc). **Smallest fix**: Modify SM003 to explicitly include the system aspect: "Do you (A) create a structured 3-month successor preparation program, or (B) promote now and coach on the job?"

---

### R3.S4 — Translating Strategy into Operating Plans

| Card | Translation/Inclusive Option | Top-Down Option | Evidence Type |
|------|---------------------------|-----------------|--------------|
| SM002 | Workshop bersama lead → MP+1, TT+2 | Susun sendiri → SP+2, TT-2 | **Direct** — collaborative translation |
| SM004 | Breakdown bersama lead → MP+1, TT+2 | Dokumen strategi detail → SP+2, TT-1 | **Direct** — inclusive breakdown |
| SS007 | Kumpulkan lead, breakdown implikasi → MP+2, TT+2 | Minta tim bersabar → MP+1, TT-2 | **Direct** — strategy translation under pressure |

**Evidence density**: 3/60 cards (5%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — 3 cards test strategy translation but none test the SPECIFIC skill of connecting daily work to strategic vision. **Smallest fix**: Add a Summit mindset card where the player must explain a strategic change to leads who "don't get it" — option A = breakdown into concrete daily implications per team, option B = share the vision document and expect them to figure it out.

---

### R3.S5 — Leading Across the Organization

| Card | Cross-Org Option | Silo Option | Evidence Type |
|------|-----------------|------------|--------------|
| SS006 | Mediasi, fasilitasi data → MP+1, TT+2 | Putuskan, larang → SP+2, TT-3 | **Direct** — cross-team mediation |
| SS010 | Workshop merged culture → MP+2, TT+3 | Tetapkan existing → SP+2, TT-3 | **Direct** — cross-org integration |
| CS008 | Fasilitasi diskusi → MP+1, TT+1 | Putuskan sendiri → SP+2, TT-2 | **Partial** — cross-subteam |

**Evidence density**: 3/60 cards (5%)

**GAP ASSESSMENT**: ⚠️ **MODERATE GAP** — Cross-organization leadership is primarily tested through crisis cards. No neutral-level card tests routine cross-team collaboration. **Smallest fix**: Add a Summit neutral card about routine cross-team coordination (e.g., "Two teams need to align on a shared API — do you facilitate a joint session or have each team design their own interface?").

---

## COVERAGE SUMMARY

### Evidence Density by Assessment Item

| Item | Cards | Density | Status |
|------|-------|---------|--------|
| **PtP.M1** Integrity | 7 | 11.7% | ✅ Adequate |
| **PtP.M2** Open to Input | 6 | 10.0% | ⚠️ Moderate Gap |
| **PtP.M3** Continuous Learning | 4 | 6.7% | 🔴 Significant Gap |
| **PtP.M4** Execution Drive | 5 | 8.3% | ⚠️ Moderate Gap (systemic proxy) |
| **PtP.M5** Others Development | 11 | 18.3% | ✅ Well-Covered |
| **PtP.S1** Root Cause Analysis | 6 | 10.0% | ⚠️ Moderate Gap |
| **PtP.S2** Assertive Communication | 7 | 11.7% | ✅ Adequate |
| **R1.M1** Benchmark Pursuit | 2 | 3.3% | 🔴 Significant Gap |
| **R1.M2** Target Ownership | 5 | 8.3% | ⚠️ Moderate Gap |
| **R1.S1** Consistent Delivery | Systemic | N/A | ✅ Adequate (systemic) |
| **R1.S2** Proactive Reporting | 3 | 5.0% | 🔴 Significant Gap |
| **R1.S3** Follow Systems | 3 | 5.0% | 🔴 Significant Gap |
| **R1.S4** Personal Work System | 4 | 6.7% | ⚠️ Moderate Gap |
| **R2.M1** Team Empowerment | 7 | 11.7% | ✅ Well-Covered |
| **R2.M2** Managerial Work Value | 6 | 10.0% | ⚠️ Moderate Gap |
| **R2.S1** Job Design & Delegation | 4 | 6.7% | ⚠️ Moderate Gap |
| **R2.S2** Selecting/Deselecting | 3 | 5.0% | 🔴 Significant Gap |
| **R2.S3** Performance Monitoring | 3 | 5.0% | 🔴 Significant Gap |
| **R2.S4** Tough Conversations | 7 | 11.7% | ✅ Adequate |
| **R2.S5** Team Engagement | 7 | 11.7% | ✅ Well-Covered |
| **R2.S6** Coaching | 7 | 11.7% | ✅ Well-Covered |
| **R2.S7** Basic Budgeting | 4 | 6.7% | ⚠️ Moderate Gap |
| **R2.S8** Team Workflow/SOP | 5 | 8.3% | ✅ Adequate |
| **R2.S9** Upward/Cross Communication | 5 | 8.3% | ⚠️ Moderate Gap |
| **R3.M1** Leadership Quality Focus | 4 | 6.7% | ⚠️ Moderate Gap |
| **R3.M2** Decisiveness Under Uncertainty | 5 | 8.3% | ⚠️ Moderate Gap |
| **R3.S1** Assessing Leadership | 3 | 5.0% | 🔴 Significant Gap |
| **R3.S2** Organizational Design | 2 | 3.3% | 🔴 Significant Gap |
| **R3.S3** Developing Leaders | 4 | 6.7% | ⚠️ Moderate Gap |
| **R3.S4** Strategy Translation | 3 | 5.0% | ⚠️ Moderate Gap |
| **R3.S5** Cross-Org Leadership | 3 | 5.0% | ⚠️ Moderate Gap |

### Gap Priority Ranking

| Priority | Items | Proposed Changes |
|----------|-------|-----------------|
| **P1 — Critical** | PtP.M3 (Learning), R1.S2 (Reporting), R1.S3 (Follow Systems) | Add/modify ~3 cards |
| **P2 — High** | R2.S2 (Deselecting), R2.S3 (Monitoring), R3.S1 (Assessment), R3.S2 (Org Design) | Add/modify ~4 cards |
| **P3 — Medium** | PtP.M2, PtP.M4, PtP.S1, R1.M1, R1.M2, R1.S4, R2.S1, R2.M2, R2.S7, R2.S9, R3.M1, R3.M2, R3.S3, R3.S4, R3.S5 | Modify ~10 existing cards (add behavior tags) |
| **P4 — Adequate** | PtP.M1, PtP.M5, PtP.S2, R1.S1, R2.M1, R2.S4, R2.S5, R2.S6, R2.S8 | No changes needed |

### Total Proposed Changes

| Change Type | Count | Impact |
|------------|-------|--------|
| New cards | 2–3 | Fill critical gaps (Learning, Reporting) |
| Modified cards | 6–8 | Expand evidence for existing items |
| Behavior tag additions | ~10 | Improve evidence mapping precision |
| New LRA assessment tags | 31 items | Map every card option to specific LRA items |

---

## EVIDENCE QUALITY SPECIFICATIONS PER ITEM

### For each assessment item, the system must track:

```
{
  "assessment_item": "PtP.M1",
  "observations": [
    {
      "turn": 7,
      "card": "BM008",
      "option_chosen": "A",
      "behavior_signal": "positive",  // positive = proving, negative = disproving
      "context_type": "crisis_basecamp",
      "context_weight": 1.2,
      "cost_to_player": {"mp": 0, "sp": 0, "tt": 0, "rep": 0, "resources": 0, "flexibility": -1},
      "description": "Refused unethical instruction and offered alternative"
    }
  ],
  "evidence_count": 7,
  "context_types_seen": ["neutral_basecamp", "crisis_basecamp", "crisis_camp", "crisis_summit"],
  "positive_observations": 5,
  "negative_observations": 2,
  "direction_changes": 1,
  "raw_confidence": 0.71,
  "stability": 0.83,
  "final_confidence": 0.67,
  "quality_level": "medium",
  "assessment_score": 4,  // mapped from evidence pattern
  "defensible": true,
  "facilitator_explanation": "Player chose ethical options in 5 of 7 opportunities, including under crisis pressure at all 3 levels. Two exceptions: BM007 chose to report directly to boss (potential damage to relationship) and CM009 chose to fight management (which could indicate values or could indicate defiance). Overall pattern: integrity holds under pressure."
}
```

### Score Mapping Rules

| Evidence Pattern | Suggested Score |
|----------------|----------------|
| ≥80% positive, Strong/Repeated quality, includes crisis-level | 5 (Role Model) |
| ≥70% positive, Strong quality, includes crisis-level | 4 (Exceeds) |
| ≥60% positive, Medium quality, multi-context | 3 (Meets) |
| ≥50% positive but Weak/Insufficient quality | 2 (Below) |
| <50% positive, any quality | 1 (Not Meeting) |
| Contradictory (↕️) | Return range: "Score 2–4: behavior is context-dependent" |
| Insufficient (❓) | Return: "Insufficient evidence for assessment" |
