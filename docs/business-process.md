# Tsuyanki - Spesifikasi Bisnis Proses

## I. TUJUAN BISNIS SISTEM (FOUNDATION)

Tsuyanki adalah aplikasi belajar berbasis spaced repetition dengan prinsip berikut:

- Pengguna belajar unit terkecil: **CARD**
- Penjadwalan review ditentukan algoritma (SM-2 / turunannya)

### Sistem harus:

- Konsisten
- Deterministik
- Bisa diskalakan
- Kompatibel konten Anki (tanpa tergantung format internal Anki)

**Tidak ada proses "santai" atau "heuristik UI saja". Semua aksi user berdampak ke state belajar.**

---

## II. ENTITAS UTAMA & PERANNYA

Ini penting sebelum masuk proses.

### User
Pemilik seluruh data belajar.

### Deck
Wadah logis kumpulan materi (misal: JLPT N5 – Grammar).

### Note
Representasi konten konseptual (misal: satu kosa kata, satu konsep).

### Card
Representasi unit review.
- Satu Note → bisa menghasilkan banyak Card (misal: forward, reverse).

### ReviewState
State dinamis satu Card untuk satu User:
- interval
- ease factor
- due date
- repetition count

### ReviewLog
Catatan historis setiap review (immutable).

### Media
Asset (gambar/audio) yang direferensikan Note/Card.

---

## III. BISNIS PROSES UTAMA (HIGH LEVEL)

Ada 7 proses utama, semua saling terkait:

1. User lifecycle
2. Deck lifecycle
3. Content creation (Note → Card)
4. Study / Review
5. Scheduling (SM-2)
6. Import (Anki compatibility)
7. Analytics & feedback

Saya jelaskan satu per satu secara linear dan eksplisit.

---

## IV. USER LIFECYCLE

### 1. User register

**Input:** name, email, password

**Sistem:**
- Hash password
- Buat record user
- Set default preferences
- Tidak ada deck otomatis kecuali explicit

### 2. User login

- Generate auth token
- Tidak memuat data belajar langsung (lazy loading)

### 3. User session aktif

- Semua request API harus terasosiasi user_id
- Tidak ada data global / shared antar user

---

## V. DECK LIFECYCLE

### 1. Create deck

**Input:** name, description (optional)

**Output:** deck_id

- Deck kosong tidak masalah (allowed)

### 2. Update deck

- Hanya metadata
- Tidak mempengaruhi jadwal card

### 3. Delete deck

**Cascade delete:**
- Notes
- Cards
- ReviewStates
- Media reference
- ReviewLogs boleh ikut terhapus (karena konteks hilang)

---

## VI. CONTENT CREATION (NOTE → CARD)

Ini bagian yang sering disalahpahami.

### 1. User membuat NOTE

**Input:**
- deck_id
- fields (misal: front_text, back_text, example)

**NOTE tidak bisa direview**

### 2. Sistem menghasilkan CARD dari NOTE

Berdasarkan note_type / template

**Contoh:**
- Basic: 1 note → 1 card
- Reverse: 1 note → 2 card

Setiap card:
- punya card_id unik
- refer ke note_id

### 3. Saat card dibuat:

Sistem otomatis membuat ReviewState awal:
- interval = 0
- repetition = 0
- ease_factor = default (misal 2.5)
- due_date = now (atau besok, tergantung kebijakan)

**Tidak ada card tanpa review_state.**

---

## VII. STUDY / REVIEW PROCESS (CORE FLOW)

Ini alur paling kritikal.

### 1. User klik "Start Review"

**Sistem:**
- Query semua card dengan due_date ≤ now
- Urutkan (by due_date, lalu random kecil)

### 2. Sistem menampilkan CARD (front side)

- Tidak mengubah state apa pun

### 3. User reveal answer

- Masih tidak mengubah state

### 4. User memberi rating:

Again / Hard / Good / Easy

**Ini satu-satunya trigger perubahan state**

### 5. Sistem menjalankan algoritma SM-2:

**Input:**
- rating
- previous interval
- repetition
- ease_factor

**Output:**
- interval baru
- ease_factor baru
- repetition baru
- due_date baru

### 6. Sistem menyimpan:

- Update ReviewState (overwrite)
- Insert ReviewLog (append-only)

### 7. Card berikutnya ditampilkan

**Tidak ada auto-save tanpa rating.**
**Tidak ada shortcut.**

---

## VIII. SCHEDULING (SM-2, TANPA AMBIGUITAS)

### Aturan keras:

- ReviewState adalah state tunggal yang valid
- ReviewLog adalah history, tidak mempengaruhi state langsung
- due_date selalu hasil kalkulasi, bukan input user

### Jika user:

- Skip review → tidak ada perubahan
- Close app → state terakhir aman
- Review ulang card yang belum due → tetap dihitung (optional policy)

---

## IX. IMPORT (ANKI COMPATIBILITY)

**Tujuan:** konten kompatibel, algoritma milik Tsuyanki

### Alur:

1. User upload file .apkg

2. **Sistem:**
   - Ekstrak notes
   - Ekstrak cards

3. **Mapping:**
   - Note → Note Tsuyanki
   - Card → Card Tsuyanki
   - Media → Media Tsuyanki

4. **ReviewState:**
   - Bisa:
     - Reset (default)
     - Atau mapping kasar (opsional, advanced)

**Tidak ada ketergantungan ke database internal Anki setelah import selesai.**

---

## X. ANALYTICS & STATISTICS

Semua statistik diturunkan dari **ReviewLog**, bukan ReviewState.

### Contoh:

- Cards reviewed today
- Accuracy
- Retention
- Streak

**ReviewState hanya untuk future scheduling, bukan analytics.**

---

## XI. ATURAN NON-NEGOTIABLE (DESIGN CONSTRAINTS)

1. Card adalah satu-satunya unit review
2. ReviewState selalu satu per card per user
3. ReviewLog immutable
4. UI tidak boleh mengubah state tanpa aksi eksplisit user
5. Import ≠ clone Anki internal logic

---

## XII. RINGKASAN 1 KALIMAT

**Tsuyanki adalah sistem deterministik di mana konten → card → review → algoritma → state, tanpa shortcut dan tanpa asumsi tersembunyi.**
