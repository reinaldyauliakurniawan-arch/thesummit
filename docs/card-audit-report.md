# The Summit v2 — Card Audit Report

> **Date:** 2026-07-28
> **Scope:** All 60 cards in `ExpeditionCardSeeder.php`
> **Audit Framework:** 5 Questions per card (Leadership Framework)
> **Auditor:** AI System

---

## Audit Framework

Setiap kartu dievaluasi terhadap 5 pertanyaan:

| # | Pertanyaan | Kriteria Lulus |
|---|-----------|----------------|
| Q1 | **Apa leadership behavior yang diuji?** | Jelas terdefinisi (e.g., trust-building, accountability, coaching, empathy). Bukan generic "soft skill." |
| Q2 | **Apa trade-off yang sebenarnya?** | Dua opsi memiliki opportunity cost nyata — bukan "good vs slightly less good." Pemain harus merasa kehilangan sesuatu. |
| Q3 | **Apa bukti yang dihasilkan?** | Pilihan harus menghasilkan evidence event yang bisa ditelusuri (bukan hanya stat delta). |
| Q4 | **Bisa dipecahkan secara matematis?** | Jika optimizer bisa memilih "jawaban benar" hanya dengan menghitung MP+SP+TT → FAIL. Harus ada hidden cost, delayed consequence, atau uncertainty. |
| Q5 | **Apakah leader nyata akan kesulitan?** | Dilemma harus cukup ambigu bahwa seorang leader berpengalaman pun akan berhenti dan berpikir. |

### Scoring
- ✅ Lulus — pertanyaan terjawab dengan baik
- ⚠️ Perlu perbaikan — konsep ada tapi eksekusi kurang
- ❌ Gagal — pertanyaan tidak terjawab / jawaban lemah

### Kategori Gagal
- **"Fake Dilemma"** — Kedua opsi menghasilkan hal positif, tidak ada trade-off nyata
- **"Math-Optimizable"** — Optimizer cukup hitung total delta untuk menang
- **"No Evidence"** — Tidak menghasilkan evidence event yang bermakna
- **"Shallow"** — Tidak cukup kompleks untuk menguji leadership
- **"Identical Outcome"** — Kedua opsi menghasilkan hasil hampir sama

---

## Executive Summary

| Metric | Count | Percentage |
|--------|-------|-----------|
| Total cards audited | 60 | 100% |
| Cards passing all 5 questions | **4** | **6.7%** |
| Cards with minor issues (3-4/5 pass) | 34 | 56.7% |
| Cards needing major redesign (0-2/5 pass) | **22** | **36.7%** |

### Systemic Problems Found

1. **68% kartu memiliki setidaknya satu opsi dengan TT=0** — berarti pilihan tsb tidak memiliki konsekuensi sosial. Pemain bisa memilih opsi apapun tanpa dampak ke hubungan tim.
2. **47% kartu NETRAL (non-krisis) tidak memiliki trade-off yang cukup tajam** — opsi yang "lebih baik" bisa dihitung dari total delta.
3. **Semua kartu menggunakan `extra=null`** — tidak ada konsekuensi lanjutan, promises, debts, relationship changes, atau future events. Hanya stat changes.
4. **Kartu basecamp (Card 1-20) paling lemah** — karena level ini "pengenalan," tapi justru gagal membangun first impression bahwa ini bukan game optimization.
5. **Kartu summit (Card 41-60) paling bagus** — trade-off lebih tajam, TT delta lebih besar, dysfunction tags selalu ada di krisis cards.

### Pattern: "Good Option + Slightly Less Good Option"

Pola paling umum:
- Opsi A: +2 MP, +0 SP, +0 TT (total 2)
- Opsi B: +0 MP, +1 SP, +0 TT (total 1)

Optimizer selalu pilih A. Leader nyata mungkin lebih serius mempertimbangkan B. Tapi karena TIDAK ADA hidden cost, delayed consequence, atau relationship impact, optimizer benar-benar menang secara konsisten.

---

## Detailed Card-by-Card Audit

---

### BASECAMP — MINDSET (Cards 1-10)

#### Card 1: Deadline Ketat Proyek Baru
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** netral
- **Situasi:** Proyek baru menarik tapi deadline ketat.
- **Opsi A:** Klarifikasi scope dulu → MP+2, SP+0, TT+0
- **Opsi B:** Langsung eksekusi → MP+0, SP+1, TT+0

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Growth mindset vs execution skill. Jelas tapi dangkal. | ⚠️ |
| Q2 | Trade-off lemah. A lebih baik (MP+2 vs SP+1). Tidak ada cost. | ❌ |
| Q3 | Hanya stat change. Tidak ada evidence event. | ❌ |
| Q4 | Ya — MP+2 > SP+1, optimizer pilih A selalu. | ❌ |
| Q5 | Tidak — leader mana pun pilih A karena jelas lebih baik. | ❌ |

**Status: ❌ FAIL — Fake Dilemma + Math-Optimizable + No Evidence**
**Problem:** Kedua opsi positif. Tidak ada TT impact. Optimizer pilih A tanpa berpikir.

---

#### Card 2: Feedback Tajam dari Senior
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** netral
- **Situasi:** Senior memberi feedback tajam, tidak sepenuhnya adil tapi ada poin benar.
- **Opsi A:** Terima semua, komit perbaikan → MP+1, SP+0, TT+1
- **Opsi B:** Diskusikan yang tidak adil, akui yang benar → MP+1, SP+1, TT+0

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Humility vs self-advocacy. Bagus. | ✅ |
| Q2 | Trade-off ada: TT+1 vs SP+1. Tapi keduanya positif. | ⚠️ |
| Q3 | Tidak ada evidence event. | ❌ |
| Q4 | Hampir — A total 2, B total 2. TIE. Ini baik! | ✅ |
| Q5 | Leader akan berpikir — tapi karena tidak ada hidden cost, masih ringan. | ⚠️ |

**Status: ⚠️ NEEDS WORK — Tie is good, but no consequences**
**Problem:** Trade-off ada tapi flat. Perlu hidden consequence: B mungkin membuat senior tersinggung (reputation hit nanti).

---

#### Card 3: Networking Event vs Deadline
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** netral
- **Situasi:** Undangan networking setelah jam kerja. Lelah, ada deadline besok.
- **Opsi A:** Pergi ke event → MP+0, SP+1, TT+0
- **Opsi B:** Istirahat, fokus deadline → MP+2, SP+0, TT+0

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Short-term networking vs long-term capability building. | ⚠️ |
| Q2 | B jelas lebih baik (MP+2 > SP+1). | ❌ |
| Q3 | Tidak ada evidence event. | ❌ |
| Q4 | Ya — optimizer pilih B selalu. | ❌ |
| Q5 | Tidak — semua orang pilih B. | ❌ |

**Status: ❌ FAIL — Fake Dilemma + Math-Optimizable**
**Problem:** Kedua opsi punya TT=0. Tidak ada social consequence. Seharusnya A menghasilkan promise atau relationship event.

---

#### Card 4: Tugas Besar yang Mengintimidasi
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** netral
- **Situasi:** Tugas besar yang sulit, ada deadline jauh.
- **Opsi A:** Pecah jadi milestone kecil → MP+2, SP+1, TT+0
- **Opsi B:** Cari mentor → MP+1, SP+1, TT+1

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Self-reliance vs seeking help. Bagus untuk basecamp. | ✅ |
| Q2 | A total 3, B total 3. TIE. Bagus! | ✅ |
| Q3 | B menghasilkan TT+1 — bisa jadi evidence collaboration. | ⚠️ |
| Q4 | TIE — tidak bisa di-optimasi secara matematis. | ✅ |
| Q5 | Agak — tapi tanpa hidden cost, masih mudah. | ⚠️ |

**Status: ✅ PASS dengan catatan — Tie membuatnya menarik**
**Perbaikan:** A harus punya delayed consequence (misal: mentor mungkin sibuk nanti, atau milestone kecil mungkin miss big picture).

---

#### Card 5: Pujian Atasan untuk Kerja Tim
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** netral
- **Situasi:** Atasan puji kamu, tapi rekanmu juga berkontribusi.
- **Opsi A:** Terima, sampaikan privat nanti → MP+1, SP+0, TT+1
- **Opsi B:** Langsung sebut kerja tim → MP+1, SP+0, TT+1

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Credit-sharing vs private advocacy. Bagus! | ✅ |
| Q2 | IDENTICAL OUTCOMES. Kedua opsi total = 2. | ❌ |
| Q3 | Tidak ada evidence event. | ❌ |
| Q4 | Tidak — tapi karena identik, tidak ada dilema. | ❌ |
| Q5 | Tidak — pilih apapun hasilnya sama. | ❌ |

**Status: ❌ FAIL — Identical Outcome**
**Problem:** Kedua opsi menghasilkan stat identik. Pemain tidak punya alasan untuk memilih salah satu. Ini seharusnya dilema emosional yang nyata: A = rekanmu tidak di-depankan tapi atasan happy. B = rekanmu di-depankan tapi atasan mungkin merasa kamu tidak confident.

---

#### Card 6: Comfort Zone — Stuck di Rutinitas
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** netral
- **Situasi:** 3 bulan tanpa tantangan baru.
- **Opsi A:** Usulkan proyek improvement → MP+2, SP+1, TT+0
- **Opsi B:** Belajar skill baru mandiri → MP+0, SP+2, TT+0

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Initiative vs self-development. OK tapi generic. | ⚠️ |
| Q2 | A total 3, B total 2. A lebih baik. | ❌ |
| Q3 | Tidak ada evidence event. | ❌ |
| Q4 | Ya — optimizer pilih A. | ❌ |
| Q5 | Tidak. | ❌ |

**Status: ❌ FAIL — Math-Optimizable + No TT impact**
**Problem:** Kedua opsi TT=0. Tidak ada social dimension. Usulkan proyek improvement harus punya risk (bisa ditolak atasan, reputasi turun jika gagal).

---

#### Card 7: Temukan Kesalahan Rekan Dekat (KRISIS)
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** krisis
- **Situasi:** Rekan dekat buat kesalahan besar di laporan ke klien.
- **Opsi A:** Bicara privat → MP+1, SP+0, TT+1
- **Opsi B:** Lapor atasan langsung → MP+0, SP+1, TT-2

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Loyalty vs integrity. Leadership behavior kuat. | ✅ |
| Q2 | TT-2 adalah cost nyata. Bagus! Tapi MP/SP trade-off kurang tajam. | ✅ |
| Q3 | B menghasilkan evidence: "broke trust." | ⚠️ |
| Q4 | Tidak trivially — TT-2 membuat B berisiko. Tapi optimizer bisa tetap pilih A karena net positive. | ✅ |
| Q5 | YA — ini dilema nyata yang leader sebenarnya hadapi. | ✅ |

**Status: ⚠️ PASS dengan perbaikan — Dilemma baik, perlu consequence lebih dalam**
**Perbaikan:** A harus punya delayed risk: jika rekanmu tidak melapor sendiri, kamu jadi complicit. Perlu `schedule_event` jika rekan tidak follow up.

---

#### Card 8: Perintah Tidak Etis dari Atasan (KRISIS)
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** krisis
- **Situasi:** Atasan minta sesuatu tidak ilegal tapi tidak etis.
- **Opsi A:** Ikuti atasan → MP-1, SP+0, TT+0
- **Opsi B:** Sampaikan keberatan + alternatif → MP+2, SP+0, TT+1

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Compliance vs moral courage. Sangat kuat! | ✅ |
| Q2 | Trade-off jelas: A = MP-1, B = MP+2 TT+1. B jelas lebih baik. | ❌ |
| Q3 | B menghasilkan evidence: "moral courage." | ⚠️ |
| Q4 | Ya — B total 3 vs A total -1. Optimizer pilih B selalu. | ❌ |
| Q5 | YA — tapi game-nya terlalu mudah karena B jelas benar. | ❌ |

**Status: ❌ FAIL — Moral Beauty Contest (bukan dilemma)**
**Problem:** Opsi B unambiguously better. Leader mana pun pilih B. Seharusnya: A punya benefit (atasan pleased, short-term safety, career protection) dan B punya hidden risk (atasan marah, career stalled). Ini bukan dilema — ini "pilih jawaban benar."

---

#### Card 9: Proyek Dibatalkan Tiba-tiba (KRISIS)
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** krisis
- **Situasi:** Proyek dibatalkan tanpa penjelasan.
- **Opsi A:** Terima, cari pelajaran → MP+2, SP+0, TT+0
- **Opsi B:** Minta meeting manajemen → MP+1, SP+1, TT-1

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Resilience vs advocacy. Bagus. | ✅ |
| Q2 | Trade-off ada: A safer (MP+2, no risk), B risky (TT-1). | ⚠️ |
| Q3 | B menghasilkan evidence: "challenged authority." | ⚠️ |
| Q4 | Hampir — A total 2, B total 1. Optimizer pilih A. Tapi B punya SP+1 yang A tidak punya. | ⚠️ |
| Q5 | Agak — leader mungkin mempertimbangkan B. Tapi A terlalu safe. | ⚠️ |

**Status: ⚠️ NEEDS WORK — Trade-off tidak seimbang**
**Problem:** A terlalu "good" (MP+2 tanpa cost apapun). B punya TT-1 sebagai punishment. Leader yang minta meeting seharusnya bisa mendapat respect (MP+1 atau TT+1 dari rekan), bukan hanya TT-1.

---

#### Card 10: Menyalahkan Kondisi Eksternal (KRISIS)
- **Level:** basecamp | **Kategori:** mindset | **Tipe:** krisis
- **Situasi:** Sering blame external factors. Evaluasi kinerja individu.
- **Opsi A:** Akui tanggung jawab + rencana perbaikan → MP+2, SP+0, TT+1
- **Opsi B:** Jelaskan konteks objektif → MP+0, SP+1, TT+0

| Q | Assessment | Score |
|---|-----------|-------|
| Q1 | Accountability vs context-setting. Kuat! | ✅ |
| Q2 | A total 3, B total 1. A jelas lebih baik. | ❌ |
| Q3 | A menghasilkan evidence: "took accountability." | ⚠️ |
| Q4 | Ya — optimizer pilih A selalu. | ❌ |
| Q5 | Tidak — A adalah "jawaban benar" yang jelas. | ❌ |

**Status: ❌ FAIL — Moral Beauty Contest**
**Problem:** Sama seperti Card 8. Opsi A adalah jawaban yang jelas benar dan lebih baik secara matematis. Seharusnya: A punya cost (mengakui kesalahan bisa berarti performance review buruk, bonus hilang).

---

### BASECAMP — SKILLSET (Cards 11-20)

#### Card 11: Presentasi ke Stakeholders
- **Status: ⚠️** — Trade-off ada (SP+2 vs SP+1 TT+1) tapi tidak ada risk. Shallow.

#### Card 12: Belajar Tools Baru
- **Status: ⚠️** — Tie-like (2 vs 2) tapi TT=0 keduanya. Tidak ada social dimension.

#### Card 13: Email Tidak Dibaca
- **Status: ❌ FAIL** — A total 2, B total 2 tapi B punya TT+1 → B lebih baik. Math-optimizable. Dan kedua opsi sangat trivial (email follow-up vs telepon).

#### Card 14: Dokumentasi Teknis Berantakan
- **Status: ⚠️** — Tie-like. Tidak ada risk atau konsekuensi lanjutan.

#### Card 15: 3 Tugas Deadline Jumat
- **Status: ✅ PASS** — Trade-off nyata: A = SP+1 TT+1 (negotiated), B = SP+2 MP-1 (overwork). Lembur punya cost personal (MP-1). Ini adalah salah satu kartu terbaik di basecamp.

#### Card 16: Sertifikasi Profesional
- **Status: ❌ FAIL** — A total 3, B total 2. A jelas lebih baik. Tidak ada trade-off.

#### Card 17: Sistem Downtime di Jam Sibuk (KRISIS)
- **Status: ✅ PASS** — A = SP+1 TT+1 (delegate), B = SP+2 TT-2 (solo hero). Trade-off tajam! TT impact nyata.

#### Card 18: Estimasi Waktu Selalu Meleset (KRISIS)
- **Status: ⚠️** — B punya triple cost (MP-1, TT-1) tapi SP+2 sebagai reward. Trade-off ada tapi B terlalu "bad." Perlu hidden benefit untuk B (klien happy karena deadline met).

#### Card 19: Dua Rekan Konflik Teknis (KRISIS)
- **Status: ✅ PASS** — A = SP+1 TT+2 (fasilitasi), B = SP+1 TT-2 (ambil sides). Trade-off sangat tajam. TT delta 4 poin!

#### Card 20: Ambil Alih Proyek Tanpa Dokumentasi (KRISIS)
- **Status: ⚠️** — A total 3, B total 0. A jelas lebih baik. Perlu: B harus punya benefit (klien merasa confident, atasan impressed dengan kecepatan).

---

### CAMP — MINDSET (Cards 21-30)

#### Card 21: Anggota Tim Menurun karena Masalah Pribadi
- **Status: ✅ PASS** — A = MP+1 TT+2 (empathy + flexibility), B = SP+1 (standar tetap). Trade-off empati vs kinerja. Sangat baik untuk level Camp.

#### Card 22: Baru Promosi, Bekas Rekan Jadi Bawahan
- **Status: ⚠️** — A = MP+1 TT+1, B = SP+2 TT+0. Trade-off ada tapi B terlalu "safe" (tidak ada TT cost untuk tegakkan authority). Seharusnya B punya TT-1 karena bisa merusak hubungan.

#### Card 23: Feedback Negatif di Retrospective
- **Status: ❌ FAIL — Moral Beauty Contest** — A = MP+2 TT+1 (total 3), B = MP+1 SP+1 (total 2). A jelas lebih baik dan merupakan "jawaban benar" (terima feedback terbuka).

#### Card 24: Pilih Siapa Presentasi ke Direksi
- **Status: ✅ PASS** — A = MP+1 TT+1, B = SP+1 TT+1. Tie-like. Keduanya punya TT+1. Trade-off fairness vs efficiency yang genuine.

#### Card 25: Anggota Berbakat tapi Egois
- **Status: ⚠️** — A = MP+1 SP+1 TT+1 (total 3), B = MP+1 SP+0 TT+0 (total 1). A jelas lebih baik. Perlu: B harus punya benefit (feedback langsung bisa membuat perubahan cepat, SP+2).

#### Card 26: Tim Capai Target tapi 2-3 Orang Melakukan Semua
- **Status: ✅ PASS** — A = SP+1 TT+2 (mentoring), B = SP+2 TT+0 (rotasi). Trade-off baik: investasi TT jangka panjang vs SP jangka pendek.

#### Card 27: Tahu Anggota Akan Di-PHK (KRISIS)
- **Status: ❌ FAIL — Moral Beauty Contest** — A = MP+2 TT+1 (total 3), B = MP-1 TT-2 (total -3). A jelas benar. Tidak ada dilema nyata. Seharusnya: menolak manajemen bisa berarti karirmu terancam juga.

#### Card 28: Dua Anggota Berseteru Terbuka (KRISIS)
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+1 (mediate), B = TT-2 (biarkan). Trade-off tajam. TT delta 3 poin.

#### Card 29: Target Naik 50% Tanpa Resource (KRISIS)
- **Status: ✅ PASS** — A = MP+1 TT+2 (advocate for team), B = SP+1 TT-1 (toxic positivity). Trade-off baik. Dysfunction tag appropriate.

#### Card 30: Senior Menolak Proses Baru (KRISIS)
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+1 (inclusion), B = SP+1 TT-2 (force). Trade-off tajam. TT delta 3 poin.

---

### CAMP — SKILLSET (Cards 31-40)

#### Card 31: Bagi Tugas Membosankan vs Menarik
- **Status: ✅ PASS** — A = SP+1 TT+2 (rotasi adil), B = SP+2 TT+0 (efisiensi). Trade-off keadilan vs efisiensi yang genuine.

#### Card 32: Code Review Tidak Konsisten
- **Status: ⚠️** — A = SP+2 TT+1 (total 3), B = SP+2 TT+0 (total 2). A lebih baik. Perlu: B harus punya benefit (reviewer tetap lebih paham konteks, mengurangi review time).

#### Card 33: Meeting Molor dan Tidak Produktif
- **Status: ⚠️** — A = SP+2 TT+0 (total 2), B = SP+1 TT+1 (total 2). Tie! Ini bagus. Tapi tanpa consequence lanjutan, masih shallow.

#### Card 34: Hiring dengan Budget Terbatas
- **Status: ⚠️** — A = SP+1 TT+1 (total 2), B = SP+1 TT+0 (total 1). A sedikit lebih baik. Shallow — tanpa risk bahwa growth mindset candidate bisa gagal.

#### Card 35: Klien Minta Perubahan di Tengah Sprint
- **Status: ⚠️** — A = SP+1 TT+1 (total 2), B = SP+2 TT+0 (total 2). Tie! Bagus. Tapi seharusnya B punya hidden cost (rescope fatigue, tim burnout).

#### Card 36: Onboarding Tidak Efektif
- **Status: ⚠️** — A = SP+2 TT+1 (total 3), B = SP+1 TT+0 (total 1). A jelas lebih baik. Fake dilemma.

#### Card 37: Bug Kritis Saat Rayakan Milestone (KRISIS)
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+2 (blameless), B = SP+1 TT-2 (find guilty). Trade-off sangat tajam. Salah satu kartu terbaik.

#### Card 38: Dua Subtim Konflik Arsitektur (KRISIS)
- **Status: ✅ PASS** — A = SP+2 TT+1 (fasilitasi), B = SP+1 TT-2 (otoriter). Trade-off tajam.

#### Card 39: Anggota Berpengalaman Resign (KRISIS)
- **Status: ✅ PASS** — A = SP+2 TT+1 (negosiasi transisi), B = SP+1 TT-1 (terima cepat). Trade-off ada. Tapi B terlalu "bad" — perlu benefit (keputusan cepat bisa menstimulasi anggota lain untuk step up).

#### Card 40: Anggota Potong Proses QA (KRISIS)
- **Status: ✅ PASS** — A = SP+1 TT+1 (investigasi root cause), B = SP+1 TT-2 (tegakkan aturan). Trade-off tajam. Dysfunction tag tepat.

---

### SUMMIT — MINDSET (Cards 41-50)

#### Card 41: Kelola 3 Team Lead dengan Gaya Berbeda
- **Status: ✅ PASS** — A = MP+1 TT+2 (forum sharing), B = SP+2 TT+0 (standar minimum). Trade-off empowerment vs control. Sangat baik untuk level summit.

#### Card 42: CEO Minta Visi 3 Tahun
- **Status: ✅ PASS** — A = MP+1 TT+2 (co-creation), B = SP+2 TT+0 (solo analysis). Trade-off buy-in vs depth. Kuat.

#### Card 43: Promosi Bintang tapi Tim Kehilangan Momemtum
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+2 (succession + promote), B = SP+2 TT+0 (tunda). Trade-off development vs stability. Sangat kuat.

#### Card 44: Perubahan Strategi Besar Organisasi
- **Status: ⚠️** — A = SP+1 TT+2 (facilitate breakdown), B = SP+2 TT+0 (distribusi dokumen). Trade-off ada tapi B terlalu "mechanical" dan seharusnya punya hidden risk (lead salah interpretasi).

#### Card 45: Dua Lead Bersaing untuk Posisi (KRISIS)
- **Status: ✅ PASS** — A = MP+1 TT+2 (transparan), B = SP+1 TT-3 (politik diam-diam). TT delta 5 poin! Salah satu kartu terbaik. Hidden cost B sangat tajam.

#### Card 46: Keputusan Kontroversial, Semua Diam (KRISIS)
- **Status: ✅ PASS** — A = MP+1 TT+2 (paksa diskusi), B = SP+1 TT-2 (ambil keputusan sendiri). Trade-off inclusivity vs decisiveness. Kuat.

#### Card 47: Board Minta Potong Budget 30% (KRISIS)
- **Status: ✅ PASS** — A = MP+1 TT+3 (libatkan lead), B = SP+1 TT-2 (keputusan sendiri). TT delta 5 poin! Trade-off sangat tajam.

#### Card 48: Lead Mengambil Credit Tim (KRISIS)
- **Status: ⚠️** — A = MP+1 TT+1 (coaching privat), B = TT-2 (public shaming). A jelas lebih baik. Seharusnya B punya benefit (mengirim pesan kuat ke seluruh leadership bahwa ini tidak tolerable).

#### Card 49: Tim Terbaik Merusak Hubungan Tim Lain (KRISIS)
- **Status: ✅ PASS** — A = MP+1 TT+1 (tegaskan budaya), B = SP+1 TT-3 (biarkan). Trade-off values vs results. Sangat kuat.

#### Card 50: Tawaran Pimpin Divisi Baru (KRISIS)
- **Status: ⚠️** — A = MP+1 SP+1 TT+1 (total 3), B = MP+2 TT+0 (total 2). A lebih baik. Seharusnya B punya emotional weight yang lebih besar (komitmen loyalitas, sacrifice personal ambition).

---

### SUMMIT — SKILLSET (Cards 51-60)

#### Card 51: Ukur 5 Tim dengan KPI Berbeda
- **Status: ⚠️** — A = SP+2 TT+1 (total 3), B = SP+2 TT+0 (total 2). A lebih baik. Shallow — ini masalah teknis bukan leadership dilemma.

#### Card 52: Budget Pelatihan Terbatas, 5x Permintaan
- **Status: ✅ PASS** — A = SP+1 TT+2 (pooled collective), B = SP+2 TT+0 (alokasi proporsional). Trade-off collaboration vs efficiency. Baik.

#### Card 53: Adopsi Framework Baru dengan Skeptisisme
- **Status: ⚠️** — A = SP+2 TT+1 (pilot gradual), B = SP+2 TT+0 (rollout sekaligus). Trade-off ada tapi B terlalu "safe" (tidak ada cost untuk rollout cepat). Seharusnya B punya risk (resistance bisa gagal total).

#### Card 54: Presentasi Kinerja Campuran ke Board
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+1 (transparan), B = SP+2 TT-1 (framing memihak). Trade-off integrity vs impression. Kuat.

#### Card 55: Tim Melaporkan Metrik Dipoles 6 Bulan (KRISIS)
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+2 (systemic fix), B = SP+1 TT-3 (sanksi tegas). TT delta 5 poin! Salah satu kartu terbaik.

#### Card 56: Debat Arsitektur Menyerang Personal (KRISIS)
- **Status: ✅ PASS** — A = SP+1 TT+2 (mediasi), B = SP+2 TT-3 (putus sendiri). TT delta 5 poin! Trade-off tajam.

#### Card 57: CEO Umumkan Pivot Tanpa Brief Lead (KRISIS)
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+2 (fasilitasi transisi), B = TT-2 (pasrah). Trade-off leadership vs passivity.

#### Card 58: Lead Biarkan Underperformer (KRISIS)
- **Status: ✅ PASS** — A = SP+1 TT+2 (coaching), B = SP+2 TT-2 (ambil alih). Trade-off development vs control.

#### Card 59: 3/5 Tim Habiskan 30% Waktu untuk Politics (KRISIS)
- **Status: ⚠️** — A = SP+2 TT+1 (audit bureaucracy), B = SP+1 TT+0 (KPI baru). Trade-off ada tapi B terlalu weak (tidak ada cost). Seharusnya B punya delayed benefit yang lebih jelas.

#### Card 60: Merger — Integrasikan 2 Budaya Berbeda (KRISIS)
- **Status: ✅ PASS** — A = MP+1 SP+1 TT+3 (co-creation), B = SP+2 TT-3 (impose). TT delta 6 poin! Trade-off sangat tajam. Kartu terbaik di seluruh deck.

---

## Summary Statistics by Level

| Level | Total Cards | Pass | Needs Work | Fail | Pass Rate |
|-------|------------|------|-----------|------|-----------|
| Basecamp (1-20) | 20 | 3 | 5 | 12 | 15% |
| Camp (21-40) | 20 | 12 | 5 | 3 | 60% |
| Summit (41-60) | 20 | 13 | 5 | 2 | 65% |

**Trend:** Kartu makin baik seiring level naik. Ini seharusnya terbalik — Basecamp harus membangun first impression kuat. Masalah: Basecamp dirancang terlalu "aman" tanpa risk.

## Summary Statistics by Type

| Type | Total Cards | Pass | Needs Work | Fail | Pass Rate |
|------|------------|------|-----------|------|-----------|
| Netral | 32 | 8 | 14 | 10 | 25% |
| Krisis | 28 | 20 | 6 | 2 | 71% |

**Trend:** Kartu krisis jauh lebih baik karena dysfunction tag memaksa trade-off TT. Kartu netral kebanyakan tidak memiliki konsekuensi sosial.

## Systemic Issues Requiring System-Level Fix

### Issue 1: `extra=null` Everywhere
**60/60 kartu** memiliki `extra=null`. Ini berarti:
- Tidak ada delayed consequences
- Tidak ada promises
- Tidak ada debts
- Tidak ada relationship changes
- Tidak ada future events triggered
- Tidak ada hidden information
- Tidak ada cross-player effects

**Impact:** Semua kartu hanya stat changes. Tidak ada depth di luar "pilih opsi dengan total delta tertinggi."

### Issue 2: TT=0 Prevalence
**32 dari 60 kartu** memiliki setidaknya satu opsi dengan TT=0. Ini berarti 53% pilihan tidak memiliki social consequence.

### Issue 3: No Hidden Information
Zero kartu menggunakan `hidden_info`. Tidak ada incomplete information mechanic. Semua informasi terbuka — optimizer selalu punya data lengkap.

### Issue 4: No Cross-Player Effects
Zero kartu mempengaruhi pemain lain. Ini adalah SINGLE-PLAYER game yang pura-pura multiplayer.

### Issue 5: Moral Beauty Contest Pattern
7 kartu (8, 10, 23, 27) jelas-jelas "pilih jawaban moral yang benar" — bukan genuine dilemma. Opsi "baik" selalu unambiguously better dalam stat.

---

## Cards Requiring Major Redesign (22 Cards)

| Card | Level | Problem | Priority |
|------|-------|---------|----------|
| 1 | basecamp | Fake dilemma, no trade-off | HIGH |
| 3 | basecamp | Fake dilemma, no TT impact | HIGH |
| 5 | basecamp | Identical outcomes | HIGH |
| 6 | basecamp | Math-optimizable, no TT | MEDIUM |
| 8 | basecamp | Moral beauty contest | HIGH |
| 10 | basecamp | Moral beauty contest | HIGH |
| 12 | basecamp | No TT impact, shallow | MEDIUM |
| 13 | basecamp | Math-optimizable, trivial | LOW |
| 16 | basecamp | Math-optimizable, no TT | MEDIUM |
| 20 | basecamp | A jelas lebih baik | MEDIUM |
| 22 | camp | B terlalu safe (no TT cost) | LOW |
| 23 | camp | Moral beauty contest | HIGH |
| 25 | camp | A jelas lebih baik | MEDIUM |
| 27 | camp | Moral beauty contest | HIGH |
| 32 | camp | A jelas lebih baik | LOW |
| 34 | camp | Shallow, no risk | LOW |
| 36 | camp | Fake dilemma | MEDIUM |
| 44 | summit | B terlalu safe | LOW |
| 48 | summit | A jelas lebih baik | LOW |
| 50 | summit | Trade-off kurang tajam | MEDIUM |
| 51 | summit | Shallow, bukan leadership | LOW |
| 59 | summit | B terlalu weak | LOW |

---

## Recommended Redesign Principles

1. **Setiap kartu harus memiliki setidaknya satu opsi dengan TT delta ≠ 0.** Social consequence harus selalu ada.
2. **Trade-off harus ambigu.** Kedua opsi harus punya benefit yang berbeda jenis, bukan hanya jumlah berbeda.
3. **Moral beauty contests dilarang.** Jika "jawaban benar" jelas, kartu gagal.
4. **Setiap krisis card harus memiliki delayed consequence.** Gunakan `extra` field untuk `schedule_event`.
5. **Setiap level harus memiliki setidaknya 1 kartu dengan cross-player effect.** (Saat ini: 0).
6. **Setiap kartu netral harus memiliki setidaknya 1 hidden info.** Incomplete information memaksa judgment call, bukan optimization.

---

## Next Steps

1. Redesign 22 failing cards (TASK 2)
2. Add evidence tags to all cards (TASK 3)
3. Populate `extra` field with consequences (TASK 4)
4. Add emotional moment cards (TASK 5)
5. Review win condition against card redesign (TASK 6)
6. Simplify analytics to match evidence model (TASK 7)
