#!/usr/bin/env python3
"""
Generate all 60 card JSON files for The Summit v2 per card-schema.md.
Also generates CardJsonSeeder.php and validate_cards.py.
"""
import json, os, sys

BASE = "/home/z/my-project/thesummit"
CARDS_DIR = os.path.join(BASE, "database", "cards")
SEEDER_PATH = os.path.join(BASE, "database", "seeders", "CardJsonSeeder.php")
VAL_PATH = os.path.join(BASE, "scripts", "validate_cards.py")

# ── helpers ──────────────────────────────────────────────────
def eff(**stats):
    return [{"type":"modify_stat","target":"self",
             "params":{"stat":s,"delta":v}} for s,v in stats.items()
            if not s.startswith("_")]

def delayed(stat, delta, after, label="", hidden=False):
    return {"type":"schedule_event","target":"self","params":{
        "event":{"type":"modify_stat","target":"self","params":{"stat":stat,"delta":delta}},
        "trigger_after_rounds":after,"is_hidden":hidden,"label":label}}

def conditional(stat, delta, wstat, wop, wval, label="", hidden=True):
    return {"type":"conditional_trigger","target":"self","params":{
        "condition":{"type":"stat_threshold","stat":wstat,"operator":wop,"value":wval},
        "event":{"type":"modify_stat","target":"self","params":{"stat":stat,"delta":delta}},
        "is_hidden":hidden,"label":label}}

def team_eff(stat, delta, exclude=True):
    return {"type":"affect_team","target":"other_players","params":{
        "effect":{"type":"modify_stat","target":"self","params":{"stat":stat,"delta":delta}},
        "exclude_source":exclude}}

def reveal(content, scope="chooser"):
    return {"type":"reveal_information","target":"self","params":{
        "reveal_type":"full","content":content,"scope":scope}}

def relationship(change, delta, desc=""):
    return {"type":"relationship_change","target":"other_players","params":{
        "change":change,"delta":delta,"description":desc}}

def card(id, level, cat, typ, situation,
         a_text, b_text, a_effects, b_effects, a_tags, b_tags,
         dysfunction=None, hidden_info=None,
         hint_a=None, hint_b=None, hidden_reveal=None):
    c = {
        "id": id, "version": "1.0",
        "level": level, "category": cat, "type": typ,
        "metadata": {"author":"summit-team","created":"2026-07-28"},
        "narrative": {"situation": situation}
    }
    if dysfunction: c["metadata"]["dysfunction_tag"] = dysfunction
    if hint_a: c["narrative"]["outcome_hint_a"] = hint_a
    if hint_b: c["narrative"]["outcome_hint_b"] = hint_b
    if hidden_reveal: c["narrative"]["hidden_reveal"] = hidden_reveal
    if hidden_info: c["hidden_info"] = hidden_info
    c["choices"] = {
        "A": {"text": a_text, "effects": a_effects, "behavior_tags": a_tags},
        "B": {"text": b_text, "effects": b_effects, "behavior_tags": b_tags}
    }
    return c

HI_AFTER = {"enabled":True,"reveal_timing":"after_choice","reveal_scope":"chooser"}
HI_BEFORE = {"enabled":True,"reveal_timing":"before_choice","reveal_scope":"chooser"}

# ── ALL 60 CARDS ──────────────────────────────────────────────
cards = []

# ══════════════════════════════════════════════════════════════
# BASECAMP MINDSET — 10 cards (6 dilemma + 4 crisis)
# ══════════════════════════════════════════════════════════════
cards.append(card("basecamp_mindset_001","basecamp","mindset","dilemma",
    "Kamu mendapat tugas proyek baru yang menarik, tapi deadline-nya sangat ketat. Kamu antusias tapi khawatir bisa deliver tepat waktu.",
    "Minta klarifikasi scope dulu sebelum mulai, meski terasa membuang waktu di awal.",
    "Langsung eksekusi dan beradaptasi seiring jalan, percaya pada kemampuanmu.",
    eff(mp=2, flexibility=-1) + [delayed("mp",1,3,"Scope benefit materializes")],
    eff(sp=2, flexibility=1),
    {"decisiveness":1,"control":1}, {"risk_taking":1,"adaptability":1},
    hint_a="Waktu terasa terbuang di awal, tapi scope jadi jelas.",
    hint_b="Eksekusi cepat tapi ada risiko salah arah.",
    hidden_reveal="Ternyata atasan sedang memantau siapa yang proaktif vs siapa yang langsung kerja."
))
cards.append(card("basecamp_mindset_002","basecamp","mindset","dilemma",
    "Seorang rekan senior memberikan feedback yang cukup tajam tentang presentasimu. Evaluasinya tidak sepenuhnya adil, tapi ada poin yang benar.",
    "Terima semua feedback dan berkomitmen memperbaiki tanpa mempertanyakan validitasnya.",
    "Diskusikan balik poin-poin yang kamu anggap tidak adil sambil mengakui yang benar.",
    eff(mp=1, tt=1) + [conditional("reputation",2,"tt",">=",8,"Team trust unlocks reputation boost",hidden=False)],
    eff(mp=1, sp=1, tt=-1),
    {"empathy":2,"adaptability":-1}, {"decisiveness":1,"empathy":-1}
))
cards.append(card("basecamp_mindset_003","basecamp","mindset","dilemma",
    "Kamu diundang ke networking event setelah jam kerja. Kamu lelah dan punya deadline besok, tapi event ini dihadiri orang yang bisa bantu karirmu.",
    "Pergi ke event, prioritaskan relasi jangka panjang meski besok lebih berat.",
    "Tidak pergi, istirahat dan fokus deliver deadline dengan kualitas terbaik.",
    eff(sp=1, reputation=1, flexibility=-1) + [delayed("reputation",1,4,"Network benefit matures")],
    eff(mp=2, resources=1),
    {"collaboration":1,"adaptability":1}, {"decisiveness":1,"control":1}
))
cards.append(card("basecamp_mindset_004","basecamp","mindset","dilemma",
    "Kamu menyadari bahwa selama ini sering menghindari tugas yang kelihatannya terlalu sulit. Ada satu tugas besar dengan deadline 2 minggu.",
    "Pecah tugas menjadi milestone kecil dan mulai dari bagian yang paling kamu kuasai.",
    "Cari mentor yang lebih berpengalaman untuk membimbingmu mengerjakan.",
    eff(mp=2, sp=1),
    eff(mp=1, sp=1, tt=1, flexibility=1) + [relationship("trust_gained",1,"Mentor appreciates your outreach")],
    {"decisiveness":1,"control":1}, {"collaboration":1,"coaching":1,"adaptability":1}
))
cards.append(card("basecamp_mindset_005","basecamp","mindset","dilemma",
    "Atasanmu memuji hasil kerjamu di depan tim, tapi ada bagian yang dibantu rekanmu yang tidak disebutkan.",
    "Terima pujian dan nanti privat sampaikan kontribusi rekanmu kepada atasan.",
    "Langsung saat itu sebutkan bahwa ini kerja tim, bukan hanya kamu.",
    eff(mp=1, reputation=1) + [delayed("reputation",1,2,"Private credit-sharing builds trust")],
    eff(tt=2, reputation=1),
    {"control":1,"adaptability":1}, {"empathy":2,"collaboration":1},
    hidden_info=HI_AFTER, hidden_reveal="Rekanmu sebenarnya sudah noticed dan sedang menunggu apakah kamu akan menyebutnya atau tidak."
))
cards.append(card("basecamp_mindset_006","basecamp","mindset","dilemma",
    "Kamu merasa stuck di comfort zone. Setiap hari terasa rutin tanpa tantangan baru dalam 3 bulan terakhir.",
    "Ambil inisiatif mengusulkan proyek improvement ke atasan meski belum diminta.",
    "Gunakan waktu luang untuk belajar skill baru yang bisa langsung diaplikasikan.",
    eff(mp=2, flexibility=-1, reputation=1),
    eff(sp=3, flexibility=1),
    {"risk_taking":1,"decisiveness":1}, {"adaptability":2}
))
cards.append(card("basecamp_mindset_007","basecamp","mindset","crisis",
    "Kamu menemukan kesalahan besar dalam laporan yang sudah dikirim ke klien oleh rekanmu. Rekanmu itu teman dekatmu.",
    "Bicara privat dengan rekanmu dan minta dia yang melapor ke atasan.",
    "Langsung lapor ke atasan dengan bukti karena menyangkut integritas perusahaan.",
    eff(mp=1, reputation=1, tt=1) + [team_eff("tt",1), reveal("Ternyata klien sudah menemukan kesalahan itu sendiri dan sedang mengevaluasi respons tim.")],
    eff(sp=1, tt=-2, reputation=-2) + [reveal("Ternyata klien sudah menemukan kesalahan itu sendiri dan sedang mengevaluasi respons tim.")],
    {"empathy":2,"collaboration":1,"control":-1}, {"decisiveness":2,"empathy":-2,"control":1},
    dysfunction="absence_of_trust", hidden_info=HI_AFTER
))
cards.append(card("basecamp_mindset_008","basecamp","mindset","crisis",
    "Atasan memintamu melakukan sesuatu yang bertentangan dengan nilai-nilaimu. Bukan ilegal, tapi terasa tidak etis.",
    "Ikuti arahan atasan karena dia tahu lebih banyak tentang konteks bisnis.",
    "Sampaikan keberatan dengan sopan dan tawarkan alternatif yang tetap memenuhi tujuan.",
    eff(mp=-1, tt=-1, reputation=-1) + [conditional("reputation",-2,"tt","<=",3,"Low trust amplifies reputational damage")],
    eff(mp=1, reputation=1, tt=1) + [conditional("reputation",2,"tt",">=",6,"Standing firm on values earns respect")],
    {"adaptability":1,"control":-1}, {"decisiveness":1,"risk_taking":1,"adaptability":1},
    dysfunction="lack_of_commitment", hidden_info=HI_BEFORE,
    hidden_reveal="Atasan sedang diuji oleh manajemen atas komitmen etika timnya."
))
cards.append(card("basecamp_mindset_009","basecamp","mindset","crisis",
    "Proyekmu dibatalkan tiba-tiba oleh manajemen tanpa penjelasan memadai. Kamu frustrasi dan kehilangan motivasi.",
    "Terima keputusan, cari pelajaran dari proses yang sudah dilalui, fokus ke tugas berikutnya.",
    "Minta meeting dengan manajemen untuk memahami alasan pembatalan.",
    eff(mp=2, flexibility=1),
    eff(mp=1, sp=1, tt=-1, reputation=-1) + [reveal("Ternyata pembatalan karena anggaran direalokasi untuk proyek yang kamu tolak bulan lalu.")],
    {"adaptability":1}, {"decisiveness":1,"control":1,"adaptability":-1},
    dysfunction="inattention_to_results", hidden_info=HI_AFTER
))
cards.append(card("basecamp_mindset_010","basecamp","mindset","crisis",
    "Kamu sering menyalahkan kondisi eksternal atas hasil kerjamu yang tidak optimal. Tim sedang evaluasi kinerja individu.",
    "Akui tanggung jawab penuh dan tunjukkan rencana perbaikan konkret.",
    "Jelaskan konteks kesulitan yang kamu hadapi secara objektif tanpa menyalahkan siapapun.",
    eff(mp=2, tt=1, reputation=1),
    eff(sp=1, flexibility=1),
    {"decisiveness":2,"empathy":1}, {"adaptability":1},
    dysfunction="avoidance_of_accountability"
))

# ══════════════════════════════════════════════════════════════
# BASECAMP SKILLSET — 10 cards (6 dilemma + 4 crisis)
# ══════════════════════════════════════════════════════════════
cards.append(card("basecamp_skillset_001","basecamp","skillset","dilemma",
    "Kamu diminta presentasi progress proyek ke stakeholders. Waktu 10 menit, materi banyak.",
    "Fokus pada 3 poin kunci dengan data yang kuat, sisanya jadi lampiran.",
    "Buat visualisasi infografis yang ringkas dan biarkan audiens bertanya untuk detail.",
    eff(sp=2, reputation=1),
    eff(sp=1, tt=1, flexibility=1),
    {"decisiveness":1,"control":1}, {"adaptability":1,"collaboration":1}
))
cards.append(card("basecamp_skillset_002","basecamp","skillset","dilemma",
    "Kamu perlu menguasai tools baru. Ada 2 opsi: training resmi 3 hari atau belajar mandiri seminggu dari resource online.",
    "Ikut training resmi — lebih terstruktur dan ada sertifikat.",
    "Belajar mandiri — lebih fleksibel dan langsung praktik di proyek nyata.",
    eff(sp=2, reputation=1, flexibility=-1),
    eff(sp=1, flexibility=2, mp=1),
    {"control":1}, {"adaptability":1,"risk_taking":1}
))
cards.append(card("basecamp_skillset_003","basecamp","skillset","dilemma",
    "Email penting yang kamu kirim tidak dibaca penerima. Kamu perlu follow up tapi tidak ingin terkesan mendesak.",
    "Kirim ulang email dengan subject baru yang lebih menarik dan tambahkan ringkasan poin.",
    "Telepon atau ajak meeting singkat untuk bahas isi email secara langsung.",
    eff(sp=2, flexibility=-1),
    eff(sp=1, tt=1, flexibility=1) + [relationship("trust_gained",1,"Direct communication builds rapport")],
    {"control":1,"adaptability":1}, {"collaboration":1,"empathy":1}
))
cards.append(card("basecamp_skillset_004","basecamp","skillset","dilemma",
    "Kamu ditugaskan menulis dokumentasi teknis untuk sistem yang ada. Dokumentasi sebelumnya berantakan dan tidak konsisten.",
    "Tulis ulang dari awal dengan template baru yang rapi dan konsisten.",
    "Perbaiki bagian paling kritis dulu dan buat guideline untuk ke depannya.",
    eff(sp=2, reputation=1) + [delayed("reputation",1,4,"Clean docs earn long-term respect")],
    eff(sp=1, tt=1, flexibility=1),
    {"control":2,"decisiveness":1}, {"adaptability":1,"collaboration":1,"coaching":1},
    hidden_info=HI_AFTER, hidden_reveal="Tim lain ternyata sudah mengeluh tentang dokumentasi ini ke atasan — ada ekspektasi tinggi untuk perbaikan."
))
cards.append(card("basecamp_skillset_005","basecamp","skillset","dilemma",
    "Kamu harus selesaikan 3 tugas dengan deadline Jumat. Hari Rabu dan estimasi cuma bisa selesaikan 2 dengan kualitas baik.",
    "Negosiasi deadline salah satu tugas, selesaikan 2 lainnya dengan kualitas tinggi.",
    "Kerja lembur untuk menyelesaikan ketiganya, kualitas bisa tidak merata.",
    eff(sp=1, tt=1, reputation=1, flexibility=-1),
    eff(sp=2, mp=-1, flexibility=-1) + [conditional("flexibility",-1,"mp","<=",4,"Burnout reduces flexibility")],
    {"collaboration":1,"empathy":1,"decisiveness":1}, {"decisiveness":1,"control":1,"adaptability":-1}
))
cards.append(card("basecamp_skillset_006","basecamp","skillset","dilemma",
    "Ada peluang sertifikasi profesional relevan, tapi butuh 2 bulan persiapan dan biaya tidak kecil.",
    "Ambil sertifikasi dan alokasi waktu khusus untuk persiapan.",
    "Tunda dan fokus pengembangan skill melalui proyek kerja saat ini.",
    eff(sp=3, flexibility=-1, resources=-1),
    eff(mp=1, sp=1, flexibility=1, tt=1),
    {"decisiveness":2,"control":1}, {"adaptability":1,"collaboration":1}
))
cards.append(card("basecamp_skillset_007","basecamp","skillset","crisis",
    "Sistem yang kamu kelola downtime di jam sibuk. Kamu tidak tahu penyebabnya dan klien sudah komplain.",
    "Kumpulkan tim, delegasikan investigasi, koordinasikan komunikasi ke klien.",
    "Ambil alih investigasi sendiri sambil minta tim lain tangani komunikasi.",
    eff(sp=1, tt=1, reputation=1) + [team_eff("tt",1,True)],
    eff(sp=2, tt=-2) + [team_eff("tt",-1,False)],
    {"collaboration":2,"coaching":1,"control":-1}, {"decisiveness":1,"control":2,"collaboration":-2},
    dysfunction="absence_of_trust", hidden_info=HI_AFTER,
    hidden_reveal="Root cause sebenarnya adalah konfigurasi yang kamu skip di review terakhir."
))
cards.append(card("basecamp_skillset_008","basecamp","skillset","crisis",
    "Cara kamu estimasi waktu proyek selalu meleset. Ada 2 proyek dengan deadline dekat dan estimasi tidak realistis.",
    "Akui kesalahan estimasi, usulkan revisi timeline realistis ke stakeholder.",
    "Kerja lembur ekstra dan coba penuhi deadline asli tanpa memberitahu siapapun.",
    eff(mp=1, tt=1, reputation=1),
    eff(sp=2, mp=-1, tt=-1, flexibility=-1) + [conditional("flexibility",-1,"mp","<=",3,"Repeated overwork damages flexibility")],
    {"decisiveness":1,"empathy":1,"adaptability":1}, {"control":1,"adaptability":-1},
    dysfunction="avoidance_of_accountability"
))
cards.append(card("basecamp_skillset_009","basecamp","skillset","crisis",
    "Dua rekan kerja konflik tentang pendekatan teknis berbeda. Masing-masing minta kamu mendukung mereka di meeting besok.",
    "Jadikan fasilitator dan bantu mereka temukan titik temu dari kedua pendekatan.",
    "Pilih yang kamu anggap paling tepat dan dukung di meeting.",
    eff(sp=1, tt=2, reputation=1) + [relationship("trust_gained",1,"Mediation earns respect from both sides")],
    eff(sp=1, mp=1, tt=-2, reputation=-1) + [team_eff("tt",-1,True)],
    {"collaboration":2,"empathy":1,"coaching":1}, {"decisiveness":1,"control":2,"empathy":-1},
    dysfunction="fear_of_conflict", hidden_info=HI_BEFORE,
    hidden_reveal="Ternyata atasan sudah tahu tentang konflik ini dan menunggu langkahmu."
))
cards.append(card("basecamp_skillset_010","basecamp","skillset","crisis",
    "Kamu diminta ambil alih proyek yang sudah jalan 2 minggu dari orang yang baru resign. Dokumentasi hampir tidak ada.",
    "Minta waktu 2 hari untuk audit kondisi sebelum komit timeline baru.",
    "Langsung mulai kerja dan berikan timeline optimis untuk tenangkan klien.",
    eff(mp=1, sp=1, tt=1, reputation=1),
    eff(sp=2, mp=-1, tt=-1, reputation=-1) + [delayed("tt",-1,2,"Technical debt compounds")],
    {"decisiveness":0,"adaptability":1,"collaboration":1}, {"decisiveness":2,"control":1,"risk_taking":1},
    dysfunction="lack_of_commitment"
))

# ══════════════════════════════════════════════════════════════
# CAMP MINDSET — 10 cards (6 dilemma + 4 crisis)
# ══════════════════════════════════════════════════════════════
cards.append(card("camp_mindset_001","camp","mindset","dilemma",
    "Anggota tim yang biasanya bagus tiba-tiba menurun. Kamu tahu dia ada masalah pribadi, tapi deadline tidak bisa ditunda.",
    "Ajak bicara personal, tawarkan fleksibilitas sementara, redistribusi beban kerja.",
    "Tetap tuntut standar kinerja yang sama karena tim lain juga bekerja keras.",
    eff(mp=1, tt=2, reputation=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Team sees you care")],
    eff(sp=1, tt=-1, reputation=1),
    {"empathy":2,"collaboration":1,"coaching":1}, {"decisiveness":1,"control":2,"empathy":-1},
    hidden_info=HI_AFTER, hidden_reveal="Masalah pribadi anggota tim sebenarnya terkait burnout — dia sudah minta bantuan 2 minggu lalu tapi tidak ada yang merespons."
))
cards.append(card("camp_mindset_002","camp","mindset","dilemma",
    "Kamu baru dipromosikan jadi lead tim. Bekas rekan selevelmu sekarang bawahanmu. Ada ketegangan halus.",
    "Pertahankan gaya komunikasi yang sama seperti sebelum promosi.",
    "Tetapkan ekspektasi baru jelas tentang peran dan tanggung jawab masing-masing.",
    eff(mp=1, tt=1, flexibility=1),
    eff(sp=2, tt=-1, reputation=-1) + [conditional("reputation",1,"tt",">=",6,"Clear expectations earn trust over time")],
    {"empathy":2,"adaptability":1}, {"decisiveness":1,"control":2,"empathy":-1}
))
cards.append(card("camp_mindset_003","camp","mindset","dilemma",
    "Dalam retrospective, beberapa anggota memberikan feedback negatif tentang cara kamu memimpin meeting.",
    "Terima semua feedback terbuka, catat poinnya, komitkan perbaikan di sesi berikutnya.",
    "Jelaskan konteks di balik keputusanmu supaya mereka paham alasanmu.",
    eff(mp=2, tt=1, reputation=1) + [relationship("trust_gained",1,"Vulnerability builds trust")],
    eff(sp=1, reputation=-1, tt=-1),
    {"empathy":2,"adaptability":1,"collaboration":1}, {"decisiveness":1,"control":2,"adaptability":-1}
))
cards.append(card("camp_mindset_004","camp","mindset","dilemma",
    "Kamu harus memutuskan siapa presentasi ke direksi — ada 2 kandidat sama-sama kompeten dengan gaya berbeda.",
    "Pilih yang paling siap dan berikan yang lain kesempatan di forum berbeda.",
    "Buat format co-presentation agar keduanya mendapat exposure yang adil.",
    eff(mp=1, tt=1, reputation=1),
    eff(sp=1, tt=1, flexibility=1) + [delayed("tt",1,3,"Co-presentation builds team capability")],
    {"decisiveness":1,"coaching":1}, {"collaboration":2,"coaching":1,"empathy":1}
))
cards.append(card("camp_mindset_005","camp","mindset","dilemma",
    "Anggota tim sangat berbakat tapi sulit menerima masukan dan cenderung bekerja sendiri, mengganggu koordinasi.",
    "Cari proyek yang butuh keahlian spesifiknya tapi mengharuskan kolaborasi.",
    "Berikan feedback langsung tentang pentingnya kolaborasi dan dampingi prosesnya.",
    eff(sp=1, tt=1, flexibility=1, mp=1),
    eff(mp=1, tt=-1, reputation=-1) + [conditional("tt",-1,"mp",">=",10,"High MP with low TT signals solo behavior")],
    {"coaching":2,"adaptability":1,"empathy":1}, {"decisiveness":2,"control":1,"coaching":-1},
    hidden_info=HI_BEFORE, hidden_reveal="Anggota tim ini sebenarnya insecure — performancenya drop saat merasa di-judge."
))
cards.append(card("camp_mindset_006","camp","mindset","dilemma",
    "Tim mencapai target kuartalan tapi sebagian besar kontribusi dari 2-3 orang. Anggota lain tidak berkembang.",
    "Buat program mentoring internal di mana top performer membimbing yang lain.",
    "Rotasi tugas agar setiap orang mendapat tantangan baru yang berbeda.",
    eff(sp=1, tt=2, reputation=1) + [delayed("sp",1,4,"Mentoring develops team capacity"), team_eff("sp",1,True)],
    eff(sp=2, flexibility=1, tt=-1),
    {"coaching":2,"collaboration":1,"empathy":1}, {"adaptability":1,"decisiveness":1}
))
cards.append(card("camp_mindset_007","camp","mindset","crisis",
    "Kamu tahu anggota tim akan di-PHK. Dia tidak tahu dan kamu diminta turunkan evaluasinya sebagai justifikasi.",
    "Tolak permintaan dan sampaikan ke HR bahwa kamu tidak bisa menjadi bagian dari proses tidak transparan.",
    "Ikuti arahan manajemen karena ini keputusan bisnis.",
    eff(mp=2, tt=1, reputation=1) + [relationship("trust_gained",1,"Integrity earns team respect")],
    eff(mp=-1, sp=1, tt=-2, reputation=-2),
    {"decisiveness":2,"empathy":2,"control":1}, {"adaptability":1,"empathy":-2},
    dysfunction="absence_of_trust"
))
cards.append(card("camp_mindset_008","camp","mindset","crisis",
    "Dua anggota tim berseteru terbuka di meeting. Tim lain diam dan tidak nyaman.",
    "Hentikan debat, akui kedua poin pandang, jadwalkan diskusi terpisah untuk selesaikan akar masalah.",
    "Biarkan mereka menyelesaikan sendiri karena konflik kadang diperlukan.",
    eff(mp=1, sp=1, tt=1, reputation=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Safe space restored")],
    eff(mp=1, tt=-2, reputation=-1),
    {"collaboration":2,"empathy":1,"decisiveness":1}, {"adaptability":1,"empathy":-1},
    dysfunction="fear_of_conflict", hidden_info=HI_AFTER,
    hidden_reveal="Konflik ini sebenarnya sudah berlangsung 2 minggu secara pasif — ini baru permukaan."
))
cards.append(card("camp_mindset_009","camp","mindset","crisis",
    "Target tim dinaikkan 50% tanpa penambahan resource. Reaksi tim beragam — frustasi, pasrah, marah.",
    "Dukung tim dengan menyampaikan keberatan ke manajemen bersama data, minta revisi target.",
    "Motivasi tim dengan framing positif bahwa ini kesempatan membuktikan kapabilitas.",
    eff(mp=1, tt=2, reputation=1) + [relationship("trust_gained",1,"Advocating earns loyalty"), conditional("tt",-1,"mp","<=",3,"Without capability, motivation crumbles")],
    eff(sp=1, mp=1, tt=-1, flexibility=1) + [conditional("reputation",1,"mp",">=",10,"Positive framing earns visibility when backed by results")],
    {"collaboration":2,"empathy":2,"decisiveness":1}, {"adaptability":1,"control":1,"empathy":-1},
    dysfunction="lack_of_commitment"
))
cards.append(card("camp_mindset_010","camp","mindset","crisis",
    "Anggota tim senior menolak proses barumu. Dia bilang proses lama sudah cukup baik dan perubahan membuang waktu.",
    "Dengarkan keberatannya, cari titik valid, libatkan dia dalam penyempurnaan proses baru.",
    "Tegaskan keputusan sudah dibuat dan semua wajib mengikutinya.",
    eff(mp=1, sp=1, tt=1, reputation=1, flexibility=1) + [delayed("reputation",1,3,"Inclusive process earns senior buy-in")],
    eff(sp=1, tt=-2, reputation=-1),
    {"empathy":2,"collaboration":1,"coaching":1,"adaptability":1}, {"decisiveness":2,"control":2,"empathy":-1,"adaptability":-1},
    dysfunction="avoidance_of_accountability", hidden_info=HI_BEFORE,
    hidden_reveal="Anggota senior ini sebenarnya takut proses baru akan mengekspos kelemahan skillnya."
))

# ══════════════════════════════════════════════════════════════
# CAMP SKILLSET — 10 cards (6 dilemma + 4 crisis)
# ══════════════════════════════════════════════════════════════
cards.append(card("camp_skillset_001","camp","skillset","dilemma",
    "Kamu harus membagikan tugas sprint. Ada tugas penting tapi membosankan, dan tugas menarik tapi impact rendah.",
    "Rotasi tugas membosankan secara adil di setiap sprint.",
    "Assign berdasarkan keahlian masing-masing untuk efisiensi terbaik.",
    eff(sp=1, tt=2, reputation=1) + [delayed("sp",1,3,"Fair rotation builds versatile team"), team_eff("flexibility",1,True)],
    eff(sp=2, mp=1, tt=-1, flexibility=-1),
    {"collaboration":2,"coaching":1,"empathy":1}, {"decisiveness":1,"control":2,"adaptability":-1}
))
cards.append(card("camp_skillset_002","camp","skillset","dilemma",
    "Standar code review tidak konsisten — kadang ketat, kadang formalitas. Ini menyebabkan bug yang seharusnya bisa dicegah.",
    "Buat checklist wajib untuk code review dan sosialisasikan ke seluruh tim.",
    "Assign reviewer tetap per modul agar paling paham konteksnya.",
    eff(sp=2, tt=1, reputation=1) + [delayed("tt",1,2,"Consistent review builds quality culture")],
    eff(sp=2, tt=-1, flexibility=-1),
    {"collaboration":1,"control":1,"coaching":1}, {"decisiveness":1,"control":2,"collaboration":-1}
))
cards.append(card("camp_skillset_003","camp","skillset","dilemma",
    "Meeting tim sering molor dan tidak produktif. Agenda ada tapi sering deviasi ke topik lain.",
    "Terapkan timeboxing ketat dan parking lot untuk topik yang melenceng.",
    "Kurangi frekuensi meeting dan ganti dengan async update via dokumentasi.",
    eff(sp=2, flexibility=-1, reputation=1),
    eff(sp=1, flexibility=2, tt=1, mp=1),
    {"control":2,"decisiveness":1}, {"adaptability":1,"collaboration":1}
))
cards.append(card("camp_skillset_004","camp","skillset","dilemma",
    "Kamu perlu hiring 1 anggota tim. Budget terbatas dan pasar kerja kompetitif.",
    "Fokus pada kandidat dengan growth mindset tinggi meski experience-nya masih minim.",
    "Tunggu sampai ketemu kandidat yang benar-benar match semua kriteria.",
    eff(sp=1, tt=1, flexibility=1, mp=1) + [delayed("sp",1,4,"High-growth hire develops faster than expected")],
    eff(sp=1, reputation=1, flexibility=-1),
    {"coaching":2,"collaboration":1,"risk_taking":1}, {"control":1,"decisiveness":1,"risk_taking":-1},
    hidden_info=HI_AFTER, hidden_reveal="Kandidat dengan growth mindset ternyata juga diminati competitor — deadline untuk offer ada minggu ini."
))
cards.append(card("camp_skillset_005","camp","skillset","dilemma",
    "Klien meminta perubahan spesifikasi signifikan di tengah sprint. Tim sudah commit pada plan awal.",
    "Negosiasi masukkan perubahan ke sprint berikutnya, tawarkan solusi interim.",
    "Terima perubahan, rescope sprint, komunikasikan dampaknya transparan ke tim.",
    eff(sp=1, tt=1, reputation=1, flexibility=1),
    eff(sp=2, tt=-1, flexibility=1) + [team_eff("tt",1,True)],
    {"decisiveness":1,"collaboration":1,"control":1}, {"adaptability":1,"collaboration":1,"control":-1}
))
cards.append(card("camp_skillset_006","camp","skillset","dilemma",
    "Onboarding anggota baru memakan waktu lebih lama. Proses yang ada tidak efektif, anggota baru bingung.",
    "Assign buddy untuk setiap anggota baru dan buat 30-day learning plan.",
    "Berikan akses ke semua dokumentasi dan biarkan mereka belajar mandiri.",
    eff(sp=2, tt=1, reputation=1) + [delayed("tt",1,2,"Buddy system accelerates integration"), team_eff("sp",1,True)],
    eff(sp=1, flexibility=1, mp=1),
    {"coaching":2,"collaboration":1,"empathy":1}, {"adaptability":1}
))
cards.append(card("camp_skillset_007","camp","skillset","crisis",
    "Bug kritis di produksi tepat saat tim merayakan milestone. Bug berasal dari kode yang di-review senior tim.",
    "Fokus fix bug dulu, lalu lakukan blameless post-mortem.",
    "Cari tahu siapa yang salah dan pastikan ada konsekuensi agar tidak terulang.",
    eff(mp=1, sp=1, tt=2, reputation=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Blameless culture strengthens team")],
    eff(sp=1, tt=-2, reputation=-1) + [team_eff("tt",-1,True)],
    {"collaboration":2,"empathy":1,"decisiveness":1}, {"control":2,"decisiveness":1,"empathy":-2,"collaboration":-1},
    dysfunction="absence_of_trust"
))
cards.append(card("camp_skillset_008","camp","skillset","crisis",
    "Dua subtim membuat keputusan teknis bertentangan untuk modul yang saling terhubung. Integrasi minggu depan bermasalah.",
    "Panggil kedua lead, fasilitasi diskusi teknis untuk temukan arsitektur yang menyatukan keduanya.",
    "Putuskan sendiri arsitektur yang lebih baik dan minta kedua pihak mengikutinya.",
    eff(sp=1, tt=2, reputation=1, flexibility=1) + [relationship("trust_gained",1,"Collaborative resolution builds respect")],
    eff(sp=1, tt=-2, reputation=-1, flexibility=-1),
    {"collaboration":2,"coaching":1,"empathy":1}, {"decisiveness":2,"control":2,"collaboration":-2},
    dysfunction="fear_of_conflict"
))
cards.append(card("camp_skillset_009","camp","skillset","crisis",
    "Anggota tim paling berpengalaman mengajukan resign. Dia knowledge base untuk sistem kritis, belum ada dokumentasi memadai.",
    "Negosiasi masa transisi 1 bulan dan jalankan knowledge transfer intensif.",
    "Terima resign segera dan distribusikan tugasnya ke anggota lain.",
    eff(sp=1, tt=1, reputation=1, flexibility=1) + [delayed("sp",1,3,"Knowledge transfer strengthens remaining team"), team_eff("sp",1,True)],
    eff(sp=1, tt=-1, reputation=-1, flexibility=-1) + [conditional("flexibility",-1,"sp","<=",5,"Knowledge gap reduces flexibility")],
    {"collaboration":1,"coaching":1,"empathy":1,"adaptability":1}, {"decisiveness":2,"control":1,"empathy":-1},
    dysfunction="lack_of_commitment"
))
cards.append(card("camp_skillset_010","camp","skillset","crisis",
    "Beberapa anggota diam-diam memotong proses QA karena merasa lambat. Penyebab peningkatan bug di produksi.",
    "Investigasi alasan pemotongan proses, perbaiki proses QA yang memang terlalu berat.",
    "Tegaskan QA wajib dan berikan warning ke yang melanggar.",
    eff(sp=1, tt=1, reputation=1, flexibility=1) + [team_eff("tt",1,True), delayed("tt",1,3,"Improved QA process earns trust")],
    eff(sp=1, tt=-2, reputation=-1),
    {"collaboration":1,"empathy":2,"coaching":1,"adaptability":1}, {"control":2,"decisiveness":1,"empathy":-2,"collaboration":-1},
    dysfunction="avoidance_of_accountability", hidden_info=HI_AFTER,
    hidden_reveal="Ternyata QA lambat karena 1 tool berbayar belum di-approve procurement — bukan masalah proses tapi budget."
))

# ══════════════════════════════════════════════════════════════
# SUMMIT MINDSET — 10 cards (4 dilemma + 6 crisis)
# ══════════════════════════════════════════════════════════════
cards.append(card("summit_mindset_001","summit","mindset","dilemma",
    "Kamu mengelola 3 team lead dengan gaya kepemimpinan sangat berbeda: satu otoriter, satu demokratis, satu laissez-faire.",
    "Buat forum reguler di mana mereka berbagi challenge dan belajar dari perbedaan gaya.",
    "Tetapkan standar kepemimpinan minimum yang harus dipatuhi semua, sisanya biarkan berekspresi.",
    eff(mp=1, tt=2, reputation=1) + [delayed("tt",1,3,"Cross-learning strengthens leadership bench"), team_eff("sp",1,True)],
    eff(sp=2, flexibility=-1, tt=-1, reputation=1),
    {"collaboration":2,"coaching":2,"empathy":1}, {"decisiveness":2,"control":2,"adaptability":-1}
))
cards.append(card("summit_mindset_002","summit","mindset","dilemma",
    "CEO meminta visi 3 tahun untuk divisi kamu. Kamu punya ide kuat tapi tanpa buy-in para lead, eksekusinya gagal.",
    "Workshop bersama para lead untuk rumuskan visi bersama, lalu kamu kristalisasi untuk CEO.",
    "Susun visi sendiri berdasarkan analisis mendalam dan presentasikan ke para lead untuk feedback.",
    eff(mp=1, tt=2, reputation=1, flexibility=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Inclusive vision builds ownership")],
    eff(sp=2, tt=-1, flexibility=-1) + [delayed("reputation",1,3,"Strong analysis earns CEO confidence")],
    {"collaboration":2,"coaching":1,"empathy":1}, {"decisiveness":2,"control":1,"collaboration":-1}
))
cards.append(card("summit_mindset_003","summit","mindset","dilemma",
    "Salah satu team lead menunjukkan potensi luar biasa dan siap naik level. Tapi jika dipromosikan, timnya kehilangan momentum.",
    "Siapkan successor-nya sekarang, promosikan dalam 2-3 bulan dengan transisi terencana.",
    "Tunda promosi sampai timnya benar-benar stabil dengan performa konsisten.",
    eff(mp=1, sp=1, tt=2, reputation=1) + [delayed("sp",1,4,"Succession planning develops two leaders"), team_eff("sp",1,True)],
    eff(sp=2, tt=-1, flexibility=-1) + [conditional("tt",1,"mp",">=",10,"Patience earns team stability")],
    {"coaching":2,"collaboration":1,"decisiveness":1,"risk_taking":1}, {"adaptability":1,"control":1}
))
cards.append(card("summit_mindset_004","summit","mindset","dilemma",
    "Organisasi mengalami perubahan strategi besar. Para lead bingung tentang prioritas baru, tim kehilangan fokus.",
    "Kumpulkan semua lead, breakdown strategi baru menjadi inisiatif per tim, bantu translate ke eksekusi.",
    "Buat dokumen strategi detail dan distribusikan, biarkan lead mengartikulasikan sendiri ke timnya.",
    eff(sp=1, tt=2, reputation=1, flexibility=1) + [team_eff("tt",1,True), delayed("reputation",1,2,"Hands-on translation earns credibility")],
    eff(sp=2, mp=1, tt=-1, flexibility=-1),
    {"collaboration":2,"coaching":1,"empathy":1}, {"decisiveness":2,"control":1,"collaboration":-1},
    hidden_info=HI_BEFORE, hidden_reveal="Perubahan strategi ini sebenarnya respons terhadap competitor yang sudah 6 bulan lebih maju."
))
cards.append(card("summit_mindset_005","summit","mindset","crisis",
    "Dua team lead bersaing untuk posisi kosong. Persaingan merusak kolaborasi dan menimbulkan politik internal.",
    "Buat proses seleksi transparan dan objektif, pastikan yang kalah tetap mendapat pengembangan.",
    "Pilih yang paling siap secara diam-diam dan kontrol informasi agar tidak menimbulkan gejolak.",
    eff(mp=1, tt=2, reputation=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Fair process maintains morale")],
    eff(sp=1, tt=-3, reputation=-2, flexibility=-1) + [conditional("reputation",-2,"tt","<=",4,"Secrecy erodes trust fast")],
    {"collaboration":2,"empathy":1,"decisiveness":1}, {"control":2,"collaboration":-2,"empathy":-1},
    dysfunction="absence_of_trust"
))
cards.append(card("summit_mindset_006","summit","mindset","crisis",
    "Dalam meeting leadership, ada keputusan kontroversial. Semua lead diam menunggu arahanmu.",
    "Paksa diskusi dengan meminta setiap lead menyatakan pendapatnya sebelum kamu memberikan pandangan.",
    "Ambil keputusan sendiri dan komunikasikan reasoning-nya secara jelas setelahnya.",
    eff(mp=1, tt=2, reputation=1, flexibility=1) + [team_eff("tt",1,True), delayed("sp",1,3,"Facilitation develops lead capabilities")],
    eff(sp=2, tt=-2, reputation=-1, flexibility=-1),
    {"collaboration":2,"coaching":2,"decisiveness":1}, {"decisiveness":2,"control":2,"collaboration":-1,"coaching":-1},
    dysfunction="fear_of_conflict", hidden_info=HI_BEFORE,
    hidden_reveal="Para lead sebenarnya punya pendapat kuat tapi takut konflik — mereka saling menunggu."
))
cards.append(card("summit_mindset_007","summit","mindset","crisis",
    "Board minta pemotongan budget 30%. Kamu harus komunikasikan ke para lead yang sudah commit plan tahunan.",
    "Libatkan para lead dalam reprioritization, berikan mereka otoritas tentukan apa yang dipotong.",
    "Tentukan sendiri pemotongan per tim berdasarkan data objektif sebagai keputusan final.",
    eff(mp=1, tt=3, reputation=1, flexibility=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Shared ownership builds commitment")],
    eff(sp=2, tt=-2, reputation=-1, flexibility=-1) + [conditional("flexibility",-1,"tt","<=",3,"Top-down cuts reduce team agility")],
    {"collaboration":2,"coaching":2,"empathy":1}, {"decisiveness":2,"control":2,"collaboration":-2}
))
cards.append(card("summit_mindset_008","summit","mindset","crisis",
    "Team lead sering mengambil credit untuk kerja timnya saat presentasi ke direksi. Timnya resah.",
    "Bicarakan privat, tunjukkan data dampaknya, bantu perbaiki kebiasaannya.",
    "Angkat isu ini di forum leadership sebagai contoh budaya yang harus dihindari.",
    eff(mp=1, tt=1, reputation=1, flexibility=1) + [delayed("reputation",1,2,"Private coaching preserves dignity"), relationship("trust_gained",1,"Direct approach shows you notice team's feelings")],
    eff(tt=-2, reputation=-1, flexibility=-1) + [team_eff("tt",-1,True), conditional("reputation",-1,"tt","<=",4,"Public shaming backfires")],
    {"empathy":2,"coaching":2,"adaptability":1}, {"decisiveness":2,"control":2,"empathy":-2},
    dysfunction="avoidance_of_accountability"
))
cards.append(card("summit_mindset_009","summit","mindset","crisis",
    "Tim terbaikmu mencapai semua KPI tapi dengan cara merusak hubungan tim lain (hoarding resource, tidak berbagi info).",
    "Apresiasi hasilnya tapi tegaskan caranya tidak sejalan dengan budaya organisasi.",
    "Biarkan karena hasilnya penting untuk organisasi, urusan internal bisa disesuaikan nanti.",
    eff(mp=1, tt=1, reputation=1, flexibility=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Values-first leadership earns respect")],
    eff(sp=1, tt=-3, reputation=-2, flexibility=-1) + [conditional("tt",-1,"tt","<=",4,"Tolerating toxicity spreads dysfunction")],
    {"decisiveness":1,"empathy":1,"collaboration":1}, {"control":1,"collaboration":-2,"empathy":-1},
    dysfunction="inattention_to_results"
))
cards.append(card("summit_mindset_010","summit","mindset","crisis",
    "Kamu ditawari memimpin divisi baru lebih besar, tapi meninggalkan tim yang kamu bangun 2 tahun di saat mereka paling butuh arahan.",
    "Terima tawaran tapi pastikan succession plan kuat dan tetap support tim lama sebagai mentor.",
    "Tolak tawaran karena komitmenmu kepada tim saat ini adalah prioritas.",
    eff(mp=1, sp=1, tt=1, reputation=1, flexibility=1) + [delayed("reputation",1,3,"Mentor role from above strengthens both teams"), relationship("trust_gained",1,"Continued support shows genuine care")],
    eff(mp=2, tt=-1, reputation=1) + [conditional("reputation",1,"tt",">=",5,"Loyalty is recognized")],
    {"decisiveness":1,"coaching":1,"collaboration":1,"risk_taking":1}, {"empathy":2,"collaboration":1,"decisiveness":-1},
    dysfunction="lack_of_commitment"
))

# ══════════════════════════════════════════════════════════════
# SUMMIT SKILLSET — 10 cards (4 dilemma + 6 crisis)
# ══════════════════════════════════════════════════════════════
cards.append(card("summit_skillset_001","summit","skillset","dilemma",
    "Kamu perlu mengukur efektivitas 5 tim secara adil dengan KPI dan konteks berbeda-beda.",
    "Buat framework evaluasi yang mengukur leading indicators dan lagging indicators dengan bobot disepakati.",
    "Gunakan OKR standar untuk semua tim dengan customization minimal per konteks.",
    eff(sp=2, tt=1, reputation=1, flexibility=1) + [team_eff("sp",1,True), delayed("reputation",1,3,"Fair framework earns broad trust")],
    eff(sp=2, flexibility=-1, tt=-1, reputation=-1) + [team_eff("reputation",-1,True)],
    {"collaboration":1,"coaching":1,"empathy":1}, {"decisiveness":1,"control":2,"empathy":-1}
))
cards.append(card("summit_skillset_002","summit","skillset","dilemma",
    "Budget pelatihan terbatas. 5 team lead mengajukan program berbeda. Total permintaan 3x lipat budget.",
    "Buat pooled learning budget dan minta lead memprioritaskan bersama yang paling impact untuk divisi.",
    "Alokasi proporsional berdasarkan ukuran tim dan kebutuhan yang terbukti data.",
    eff(sp=1, tt=2, reputation=1, flexibility=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Collaborative budgeting builds solidarity")],
    eff(sp=2, tt=-1, flexibility=-1, mp=1),
    {"collaboration":2,"coaching":1,"empathy":1}, {"decisiveness":1,"control":2,"collaboration":-1}
))
cards.append(card("summit_skillset_003","summit","skillset","dilemma",
    "Organisasi mau adopsi framework baru. Para lead punya skeptisisme berbeda.",
    "Jalankan pilot di 1-2 tim yang paling terbuka, buktikan hasilnya, lalu scale gradual.",
    "Rollout sekaligus ke semua tim dengan training intensif dan monitoring ketat.",
    eff(sp=2, tt=1, reputation=1, flexibility=1) + [delayed("tt",1,4,"Proof-of-concept builds organic adoption"), team_eff("flexibility",1,True)],
    eff(sp=2, tt=-1, flexibility=-1, reputation=-1) + [team_eff("flexibility",-1,True)],
    {"adaptability":2,"coaching":1,"collaboration":1}, {"decisiveness":2,"control":2,"adaptability":-1}
))
cards.append(card("summit_skillset_004","summit","skillset","dilemma",
    "Kamu perlu presentasi kinerja divisi ke board. Data menunjukkan hasil campuran — peningkatan signifikan di beberapa area, penurunan di area lain.",
    "Presentasi transparan dengan focus pada pembelajaran dan rencana aksi untuk area yang menurun.",
    "Highlight area yang berhasil dan minimalkan area yang menurun dengan framing konteks yang memihak.",
    eff(mp=1, sp=1, tt=1, reputation=2) + [delayed("reputation",1,2,"Transparency builds board trust long-term")],
    eff(sp=2, reputation=-1, tt=-1, flexibility=-1) + [conditional("reputation",-1,"mp","<=",5,"Spinning catches up eventually")],
    {"decisiveness":1,"adaptability":1,"empathy":1,"collaboration":1}, {"control":2,"adaptability":-1,"empathy":-1},
    hidden_info=HI_BEFORE, hidden_reveal="Board sebenarnya sudah mendapat laporan terpisah dari auditor internal — mereka tahu area yang menurun."
))
cards.append(card("summit_skillset_005","summit","skillset","crisis",
    "Investigasi internal mengungkap satu tim melaporkan metrik yang dipoles selama 6 bulan. Lead klaim hanya mengikuti budaya yang sudah ada.",
    "Tangani sistemik: audit metrik semua tim, perbaiki proses pelaporan, latih ulang para lead.",
    "Berikan sanksi tegas ke lead sebagai contoh untuk semua.",
    eff(mp=1, sp=1, tt=2, reputation=1) + [team_eff("tt",1,True), delayed("reputation",1,3,"Systemic fix prevents recurrence")],
    eff(sp=1, tt=-3, reputation=-2, flexibility=-1) + [team_eff("tt",-1,True), conditional("reputation",-1,"tt","<=",4,"Punitive approach creates fear culture")],
    {"collaboration":1,"coaching":1,"empathy":1,"adaptability":1}, {"control":2,"decisiveness":1,"empathy":-2,"collaboration":-1},
    dysfunction="absence_of_trust"
))
cards.append(card("summit_skillset_006","summit","skillset","crisis",
    "Dua tim debat terbuka di slack tentang arsitektur. Debat sudah menyerang personal, produktivitas kedua tim turun 40%.",
    "Mediasi langsung, bawa kedua lead ke ruangan, fasilitasi diskusi berbasis data.",
    "Putuskan sendiri arsitektur yang benar dan larang perdebatan lebih lanjut.",
    eff(sp=1, tt=2, reputation=1, flexibility=1) + [team_eff("tt",1,True), relationship("trust_gained",1,"Data-driven resolution earns respect")],
    eff(sp=2, tt=-3, reputation=-2, flexibility=-1) + [team_eff("flexibility",-1,True)],
    {"collaboration":2,"empathy":1,"coaching":1,"decisiveness":1}, {"control":2,"decisiveness":2,"collaboration":-2,"empathy":-1},
    dysfunction="fear_of_conflict"
))
cards.append(card("summit_skillset_007","summit","skillset","crisis",
    "CEO umumkan pivot besar di all-hands. Para lead tidak di-brief sebelumnya dan kaget.",
    "Segera kumpulkan para lead, breakdown implikasi pivot, buat rencana transisi 30 hari.",
    "Sampaikan ke tim bahwa kamu juga baru tahu dan minta mereka bersabar.",
    eff(mp=1, sp=1, tt=2, reputation=1, flexibility=1) + [team_eff("tt",1,True), delayed("reputation",1,2,"Rapid response earns leadership credibility")],
    eff(tt=-2, reputation=-1, flexibility=-1) + [conditional("reputation",-1,"tt","<=",4,"Passive response erodes team confidence")],
    {"collaboration":2,"coaching":1,"decisiveness":1,"adaptability":1}, {"empathy":1,"control":-1,"decisiveness":-1},
    dysfunction="lack_of_commitment"
))
cards.append(card("summit_skillset_008","summit","skillset","crisis",
    "Team lead membiarkan anggota underperform tanpa intervensi karena tidak mau terlihat kejam.",
    "Coaching intensif tentang performance management yang tulus tapi tegas, dampingi prosesnya.",
    "Ambil alih proses performance management langsung untuk anggota yang underperform.",
    eff(sp=1, tt=2, reputation=1, flexibility=1) + [delayed("sp",1,3,"Coaching the coach multiplies impact"), team_eff("sp",1,True)],
    eff(sp=2, tt=-2, reputation=-1, flexibility=-1) + [team_eff("tt",-1,True)],
    {"coaching":2,"collaboration":1,"empathy":2}, {"control":2,"decisiveness":2,"coaching":-1,"empathy":-1},
    dysfunction="avoidance_of_accountability"
))
cards.append(card("summit_skillset_009","summit","skillset","crisis",
    "3 dari 5 tim menghabiskan 30% waktu untuk internal politics — meeting tidak produktif, reporting berlebihan, silo communication.",
    "Implementasi bureaucracy audit: identifikasi dan eliminasi aktivitas yang tidak memberikan value ke customer.",
    "Tetapkan KPI baru yang mengukur output nyata, kurangi KPI berbasis aktivitas internal.",
    eff(sp=1, tt=1, reputation=1, flexibility=1) + [team_eff("tt",1,True), delayed("tt",1,2,"Bureaucracy audit frees up capacity"), team_eff("flexibility",1,True)],
    eff(sp=2, tt=-1, flexibility=-1),
    {"collaboration":1,"coaching":1,"adaptability":1}, {"decisiveness":2,"control":2,"adaptability":-1}
))
cards.append(card("summit_skillset_010","summit","skillset","crisis",
    "Perusahaan merger. Kamu harus integrasikan 2 tim leadership dengan budaya sangat berbeda. Tim lama merasa terancam, tim baru merasa tidak dihargai.",
    "Workshop merged culture di mana kedua pihak berkontribusi menetapkan nilai-nilai dan cara kerja baru.",
    "Tetapkan culture perusahaan yang sudah ada sebagai standar, minta tim baru beradaptasi.",
    eff(mp=1, sp=1, tt=3, reputation=1, flexibility=1) + [team_eff("tt",1,True), relationship("trust_gained",2,"Inclusive culture building earns loyalty from both sides"), delayed("tt",1,3,"Cultural integration strengthens over time")],
    eff(sp=2, tt=-3, reputation=-2, flexibility=-1) + [team_eff("tt",-2,False), conditional("tt",-1,"tt","<=",4,"Imposed culture causes disengagement")],
    {"collaboration":2,"empathy":2,"coaching":1,"adaptability":1}, {"control":2,"collaboration":-2,"empathy":-2},
    dysfunction="absence_of_trust", hidden_info=HI_BEFORE,
    hidden_reveal="CEO sebenarnya mengharapkan integrasi budaya yang otentik — dia akan mengevaluasi bagaimana kamu menangani ini sebagai faktor dalam penilaianmu."
))

# ══════════════════════════════════════════════════════════════
# WRITE JSON FILES
# ══════════════════════════════════════════════════════════════
print(f"Generating {len(cards)} card JSON files...")
for c in cards:
    parts = c['id'].rsplit('_', 1)
    prefix = parts[0]  # e.g. "basecamp_mindset"
    seq = parts[1]     # e.g. "001"
    dirpath = os.path.join(CARDS_DIR, prefix)
    os.makedirs(dirpath, exist_ok=True)
    filepath = os.path.join(dirpath, f"{seq}.json")
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(c, f, ensure_ascii=False, indent=2)
    print(f"  wrote {filepath}")

# ══════════════════════════════════════════════════════════════
# WRITE PHP SEEDER
# ══════════════════════════════════════════════════════════════
print(f"\nGenerating PHP seeder...")
seeder = '''<?php
namespace Database\\Seeders;
use Illuminate\\Database\\Seeder;
use App\\Models\\ExpeditionCard;

class CardJsonSeeder extends Seeder
{
    public function run(): void
    {
        ExpeditionCard::query()->delete();

        $cardsDir = database_path("cards");
        $files = array_merge(
            glob("$cardsDir/*/*.json"),
            glob("$cardsDir/*/*/*.json")
        );
        sort($files);

        $count = 0;
        foreach ($files as $file) {
            $json = json_decode(file_get_contents($file), true);
            if (!$json) continue;

            $aEff = $json["choices"]["A"]["effects"] ?? [];
            $bEff = $json["choices"]["B"]["effects"] ?? [];

            ExpeditionCard::create([
                "card_id"                    => $json["id"],
                "level"                      => $json["level"],
                "kategori"                   => $json["category"],
                "tipe"                       => $json["type"] === "crisis" ? "krisis" : "netral",
                "teks_situasi"              => $json["narrative"]["situation"] ?? "",
                "opsi_a_teks"               => $json["choices"]["A"]["text"] ?? "",
                "opsi_a_mp"                 => $this->stat($aEff, "mp"),
                "opsi_a_sp"                 => $this->stat($aEff, "sp"),
                "opsi_a_tt"                 => $this->stat($aEff, "tt"),
                "opsi_a_reputation"         => $this->stat($aEff, "reputation"),
                "opsi_a_resources"          => $this->stat($aEff, "resources"),
                "opsi_a_flexibility"        => $this->stat($aEff, "flexibility"),
                "opsi_a_behavior_tags"      => $json["choices"]["A"]["behavior_tags"] ?? [],
                "opsi_a_delayed_effects"    => $this->delayed($aEff),
                "opsi_a_conditional_effects"=> $this->conditional($aEff),
                "opsi_a_cross_player"       => $this->team($aEff),
                "opsi_b_teks"               => $json["choices"]["B"]["text"] ?? "",
                "opsi_b_mp"                 => $this->stat($bEff, "mp"),
                "opsi_b_sp"                 => $this->stat($bEff, "sp"),
                "opsi_b_tt"                 => $this->stat($bEff, "tt"),
                "opsi_b_reputation"         => $this->stat($bEff, "reputation"),
                "opsi_b_resources"          => $this->stat($bEff, "resources"),
                "opsi_b_flexibility"        => $this->stat($bEff, "flexibility"),
                "opsi_b_behavior_tags"      => $json["choices"]["B"]["behavior_tags"] ?? [],
                "opsi_b_delayed_effects"    => $this->delayed($bEff),
                "opsi_b_conditional_effects"=> $this->conditional($bEff),
                "opsi_b_cross_player"       => $this->team($bEff),
                "dysfunction_tag"            => $json["metadata"]["dysfunction_tag"] ?? null,
                "has_hidden_info"           => ($json["hidden_info"]["enabled"] ?? false) ? true : false,
                "hidden_info_reveal"        => $json["narrative"]["hidden_reveal"] ?? null,
                "card_json"                 => json_encode($json),
            ]);
            $count++;
        }
        $this->command->info("$count cards seeded from JSON files.");
    }

    private function stat(array $effects, string $s): int
    {
        foreach ($effects as $e) {
            if (($e["type"] ?? "") === "modify_stat" && ($e["params"]["stat"] ?? "") === $s)
                return $e["params"]["delta"] ?? 0;
        }
        return 0;
    }

    private function delayed(array $effects): array
    {
        $r = [];
        foreach ($effects as $e) {
            if (($e["type"] ?? "") === "schedule_event") {
                $inner = $e["params"]["event"] ?? [];
                $r[] = [
                    "stat" => $inner["params"]["stat"] ?? "",
                    "delta" => $inner["params"]["delta"] ?? 0,
                    "trigger_after_rounds" => $e["params"]["trigger_after_rounds"] ?? 0,
                    "label" => $e["params"]["label"] ?? "",
                    "is_hidden" => $e["params"]["is_hidden"] ?? false,
                ];
            }
        }
        return $r;
    }

    private function conditional(array $effects): array
    {
        $r = [];
        foreach ($effects as $e) {
            if (($e["type"] ?? "") === "conditional_trigger") {
                $inner = $e["params"]["event"] ?? [];
                $r[] = [
                    "stat" => $inner["params"]["stat"] ?? "",
                    "delta" => $inner["params"]["delta"] ?? 0,
                    "condition" => $e["params"]["condition"] ?? [],
                    "label" => $e["params"]["label"] ?? "",
                    "is_hidden" => $e["params"]["is_hidden"] ?? true,
                ];
            }
        }
        return $r;
    }

    private function team(array $effects): array
    {
        $r = [];
        foreach ($effects as $e) {
            if (($e["type"] ?? "") === "affect_team") {
                $inner = $e["params"]["effect"] ?? [];
                $r[] = [
                    "stat" => $inner["params"]["stat"] ?? "",
                    "delta" => $inner["params"]["delta"] ?? 0,
                    "exclude_source" => $e["params"]["exclude_source"] ?? true,
                ];
            }
        }
        return $r;
    }
}
'''
with open(SEEDER_PATH, 'w', encoding='utf-8') as f:
    f.write(seeder)
print(f"  Seeder: {SEEDER_PATH}")

# ══════════════════════════════════════════════════════════════
# WRITE VALIDATION SCRIPT
# ══════════════════════════════════════════════════════════════
print(f"\nGenerating validation script...")
val_script = r'''#!/usr/bin/env python3
"""Validate all card JSONs per card-schema.md rules."""
import json, os, sys

CARDS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "database", "cards")
VALID_STATS = ["mp","sp","tt","reputation","resources","flexibility"]
VALID_DIMS = ["risk_taking","collaboration","empathy","decisiveness","coaching","control","adaptability"]

def load_all_cards():
    cards = []
    for root, dirs, files in os.walk(CARDS_DIR):
        for f in sorted(files):
            if f.endswith(".json"):
                with open(os.path.join(root, f)) as fh:
                    cards.append(json.load(fh))
    return cards

def extract_stats(effects):
    stats = {}
    for e in effects:
        if e.get("type") == "modify_stat":
            s = e["params"]["stat"]
            stats[s] = stats.get(s, 0) + e["params"]["delta"]
    return stats

def option_positive_total(stats):
    return sum(v for v in stats.values() if v > 0)

def validate(cards):
    errors = []
    dominant = []
    identical = []
    hidden_count = 0
    delayed_count = 0
    conditional_count = 0
    behavior_tagged = 0

    for c in cards:
        cid = c.get("id", "?")
        for req in ["id","version","level","category","type","narrative","choices"]:
            if req not in c:
                errors.append(f"{cid}: missing '{req}'")
        for ch in ["A","B"]:
            choice = c.get("choices",{}).get(ch,{})
            if "text" not in choice:
                errors.append(f"{cid}: choice {ch} missing text")
            if not choice.get("effects"):
                errors.append(f"{cid}: choice {ch} has no effects")
            for e in choice.get("effects",[]):
                if "type" not in e:
                    errors.append(f"{cid}: choice {ch} effect without type")
                if e.get("type") == "modify_stat":
                    st = e.get("params",{}).get("stat","")
                    if st not in VALID_STATS:
                        errors.append(f"{cid}: choice {ch} invalid stat '{st}'")
            tags = choice.get("behavior_tags",{})
            for dim, val in tags.items():
                if dim not in VALID_DIMS:
                    errors.append(f"{cid}: choice {ch} unknown dim '{dim}'")
                if abs(val) > 2:
                    errors.append(f"{cid}: choice {ch} tag '{dim}'={val} outside [-2,2]")

        if c.get("hidden_info",{}).get("enabled"):
            hidden_count += 1
        for ch in ["A","B"]:
            for e in c.get("choices",{}).get(ch,{}).get("effects",[]):
                if e.get("type") == "schedule_event": delayed_count += 1
                if e.get("type") == "conditional_trigger": conditional_count += 1
        a_tags = c.get("choices",{}).get("A",{}).get("behavior_tags",{})
        b_tags = c.get("choices",{}).get("B",{}).get("behavior_tags",{})
        if a_tags or b_tags:
            behavior_tagged += 1

        a_s = extract_stats(c.get("choices",{}).get("A",{}).get("effects",[]))
        b_s = extract_stats(c.get("choices",{}).get("B",{}).get("effects",[]))
        all_s = set(list(a_s.keys()) + list(b_s.keys()))
        a_better = sum(1 for s in all_s if a_s.get(s,0) > b_s.get(s,0))
        b_better = sum(1 for s in all_s if b_s.get(s,0) > a_s.get(s,0))
        a_tot = option_positive_total(a_s)
        b_tot = option_positive_total(b_s)
        if a_better > 0 and b_better == 0 and a_tot - b_tot > 2:
            dominant.append(f"{cid}: A dominates B (A+={a_tot}, B+={b_tot})")
        if b_better > 0 and a_better == 0 and b_tot - a_tot > 2:
            dominant.append(f"{cid}: B dominates A (A+={a_tot}, B+={b_tot})")
        if a_s == b_s and a_tot > 0:
            a_types = sorted([e["type"] for e in c.get("choices",{}).get("A",{}).get("effects",[])])
            b_types = sorted([e["type"] for e in c.get("choices",{}).get("B",{}).get("effects",[])])
            if a_types == b_types and len(set(a_types)) <= 2:
                identical.append(f"{cid}: options functionally identical")

    total = len(cards)
    print(f"=== Card Validation Report ===")
    print(f"Total cards: {total}")
    print(f"Hidden info: {hidden_count}/{total} ({hidden_count/total*100:.1f}%)" if total else "")
    print(f"Delayed effects: {delayed_count}")
    print(f"Conditional effects: {conditional_count}")
    print(f"Behavior-tagged: {behavior_tagged}/{total} ({behavior_tagged/total*100:.1f}%)" if total else "")
    print()
    if errors:
        print(f"ERRORS ({len(errors)}):")
        for e in errors: print(f"  [ERR] {e}")
    else:
        print("No schema errors.")
    print()
    if dominant:
        print(f"DOMINANT OPTIONS ({len(dominant)}):")
        for d in dominant: print(f"  [DOM] {d}")
    else:
        print("No dominant options. PASS")
    print()
    if identical:
        print(f"IDENTICAL OPTIONS ({len(identical)}):")
        for i in identical: print(f"  [ID] {i}")
    else:
        print("No identical options. PASS")
    print()
    sys.exit(1 if (errors or dominant or identical) else 0)

if __name__ == "__main__":
    cards = load_all_cards()
    validate(cards)
'''
with open(VAL_PATH, 'w', encoding='utf-8') as f:
    f.write(val_script)
print(f"  Validator: {VAL_PATH}")

print(f"\nDone! {len(cards)} cards generated.")
print(f"Run validation: python3 {VAL_PATH}")
