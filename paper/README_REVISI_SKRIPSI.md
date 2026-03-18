# 📚 PANDUAN REVISI SKRIPSI - Michael Darren Sutawijaya (C14210239)

## 🎯 Overview

Dokumen ini berisi panduan lengkap untuk mengerjakan 8 poin revisi skripsi yang diminta oleh dosen penguji.

**Status:** ✅ Semua dokumentasi pendukung sudah dibuat!

---

## 📋 Daftar File Revisi yang Sudah Dibuat

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 1 | `PANDUAN_REVISI_1_2_3.md` | Panduan untuk Revisi 1, 2, 3 (dikerjakan di Word) | ✅ |
| 2 | `REVISI_4_Penjelasan_Sistem_WhatsApp_Otomatis.md` | Konten teknis untuk Revisi 4 | ✅ |
| 3 | `REVISI_5_UAT_User_Acceptance_Testing.md` | Template UAT untuk Revisi 5 | ✅ |
| 4 | `REVISI_6_Mekanisme_WhatsApp_Lengkap.md` | Konten teknis untuk Revisi 6 | ✅ |
| 5 | `REVISI_7_Dokumentasi_API_Fonnte.md` | Konten teknis untuk Revisi 7 | ✅ |
| 6 | `REVISI_8_Mekanisme_Transaksi_Online_Offline.md` | Konten teknis untuk Revisi 8 | ✅ |

---

## 🗓️ Action Plan - Urutan Pengerjaan yang Disarankan

### MINGGU 1: Revisi Teknis (4, 5, 6, 7, 8)

**Prioritas: TINGGI - Konten Substantif**

#### **Hari 1-2: Revisi 6 (Mekanisme WhatsApp)**

- [ ] **Baca file:** `REVISI_6_Mekanisme_WhatsApp_Lengkap.md`
- [ ] **Action:**
  - Copy konten Section 6.1 (Pendaftaran) → Paste ke BAB III atau BAB IV skripsi
  - Copy konten Section 6.2 (Urutan Kerja) → Paste ke BAB IV
  - Copy flow diagram → Buat gambar di draw.io → Insert ke skripsi
  - **PENTING:** Ubah semua istilah "Chatbot" menjadi "Sistem Respons Otomatis WhatsApp Berbasis Aturan"
  - Ubah judul sub-bab menjadi: "Sistem Respons Otomatis WhatsApp Berbasis Aturan"
  - Tambahkan kata "Rule-Based" di judul

#### **Hari 3-4: Revisi 4 (Penjelasan Detail Sistem)**

- [ ] **Baca file:** `REVISI_4_Penjelasan_Sistem_WhatsApp_Otomatis.md`
- [ ] **Action:**
  - Copy Section 4.2 (Arsitektur) → Paste ke BAB III
  - Copy Section 4.3 (Cara Kerja) → Paste ke BAB IV
  - Copy Section 4.4 (Data yang Digunakan) → Paste ke BAB III
  - Copy Section 4.5 (Algoritma) → Paste ke BAB IV
  - Copy Section 4.9 (Pengujian) → Paste ke BAB IV atau BAB V
  - Buat flow diagram dari Section 4.8 → Insert sebagai gambar

#### **Hari 5: Revisi 7 (API Fonnte)**

- [ ] **Baca file:** `REVISI_7_Dokumentasi_API_Fonnte.md`
- [ ] **Action:**
  - Copy Section 7.2 (Konfigurasi) → Paste ke BAB III (Implementasi)
  - Copy Section 7.3 (Code Implementation) → Paste ke BAB IV
  - Copy Section 7.4 (Format Request/Response) → Paste ke BAB IV
  - Tambahkan screenshot dashboard Fonnte (ambil dari https://fonnte.com setelah login)
  - Tambahkan screenshot Postman/cURL request-response

#### **Hari 6: Revisi 8 (Transaksi Online/Offline)**

- [ ] **Baca file:** `REVISI_8_Mekanisme_Transaksi_Online_Offline.md`
- [ ] **Action:**
  - Copy Section 8.2 (Transaksi Offline) → Paste ke BAB IV
  - Copy Section 8.3 (Transaksi Online) → Paste ke BAB IV
  - Copy Section 8.4 (Perbandingan) → Paste ke BAB IV atau BAB V
  - Copy sequence diagram → Buat gambar di draw.io → Insert
  - Copy tabel perbandingan → Insert sebagai tabel Word

#### **Hari 7: Revisi 5 (UAT)**

- [ ] **Baca file:** `REVISI_5_UAT_User_Acceptance_Testing.md`
- [ ] **Action:**
  - Copy seluruh struktur UAT → Paste ke BAB V (Pengujian)
  - **PENTING:** Isi tabel UAT dengan hasil testing NYATA
  - Minta owner/kasir/customer untuk test dan isi formulir
  - Scan formulir yang sudah ditandatangani → Insert sebagai lampiran
  - Update tabel hasil testing dengan data real

---

### MINGGU 2: Revisi Formatting (1, 2, 3)

**Prioritas: SEDANG - Formatting & Quality**

#### **Hari 8-9: Revisi 2 (Tata Tulis)**

- [ ] **Baca file:** `PANDUAN_REVISI_1_2_3.md` → Section "Revisi 2"
- [ ] **Action:**
  - Cek format judul BAB (KAPITAL, TEBAL, CENTER)
  - Cek format sub-bab (sesuai aturan)
  - Find & replace semua kata asing → Set ITALIC
    - software, hardware, online, offline, website, database
    - framework, Laravel, API, chatbot, dashboard
    - real-time, user-friendly, interface
    - (lihat daftar lengkap di panduan)
  - Set body text: Justify (Ctrl + J)
  - Set line spacing: 1.5
  - Set margin: 4-3-3-3 cm
  - Set font: Times New Roman 12pt

#### **Hari 10: Revisi 1 (Kutipan Gambar & Tabel)**

- [ ] **Baca file:** `PANDUAN_REVISI_1_2_3.md` → Section "Revisi 1"
- [ ] **Action:**
  - Buat daftar semua gambar (Gambar 1.1, 1.2, 2.1, ...)
  - Buat daftar semua tabel (Tabel 1.1, 1.2, ...)
  - Cek satu per satu apakah sudah dikutip di narasi
  - Tambahkan kutipan dengan template:
    - "... seperti yang ditunjukkan pada Gambar X.X"
    - "Tabel X.X menunjukkan ..."
  - Pastikan kutipan SEBELUM gambar/tabel muncul

#### **Hari 11: Revisi 3 (ERD)**

- [ ] **Baca file:** `PANDUAN_REVISI_1_2_3.md` → Section "Revisi 3"
- [ ] **Action:**
  - Export ERD dengan resolusi tinggi (300 DPI minimum)
  - Tools: MySQL Workbench / dbdiagram.io / draw.io
  - Pastikan font min 10pt (readable saat print)
  - Perbesar ukuran tabel jika perlu
  - Tambahkan legend (PK, FK, relasi)
  - Jika terlalu kompleks, pisah jadi beberapa gambar
  - Insert ke Word dengan ukuran yang cukup besar
  - Tambahkan caption di bawah gambar
  - Tambahkan kutipan di narasi

---

## ✅ Checklist Final Review

Sebelum submit ke dosen, pastikan:

### Konten (Substantive)

- [ ] ✅ Revisi 4: Penjelasan sistem WhatsApp sudah lengkap (cara kerja, data, algoritma)
- [ ] ✅ Revisi 5: UAT sudah benar (test cases, user feedback, acceptance criteria)
- [ ] ✅ Revisi 6: Mekanisme WA jelas (pendaftaran, urutan kerja, rule-based)
- [ ] ✅ Revisi 7: Dokumentasi API Fonnte lengkap (code, request/response, error handling)
- [ ] ✅ Revisi 8: Transaksi online/offline dijelaskan (flow, perbedaan, integration)

### Formatting (Presentation)

- [ ] ✅ Revisi 1: Semua gambar dikutip di narasi
- [ ] ✅ Revisi 1: Semua tabel dikutip di narasi
- [ ] ✅ Revisi 2: Judul BAB format benar (KAPITAL, TEBAL, CENTER)
- [ ] ✅ Revisi 2: Sub-bab format benar (tebal, kapitalisasi sesuai aturan)
- [ ] ✅ Revisi 2: Kata asing semua ITALIC
- [ ] ✅ Revisi 2: Body text JUSTIFY
- [ ] ✅ Revisi 2: Font Times New Roman 12pt
- [ ] ✅ Revisi 2: Line spacing 1.5
- [ ] ✅ Revisi 2: Margin 4-3-3-3 cm
- [ ] ✅ Revisi 3: ERD jelas dan bisa dibaca
- [ ] ✅ Revisi 3: ERD dikutip di narasi

### Perubahan Istilah (Terminologi)

- [ ] ✅ "Chatbot" → "Sistem Respons Otomatis WhatsApp Berbasis Aturan"
- [ ] ✅ Tambahkan "Rule-Based" di judul dan sub-judul yang relevan
- [ ] ✅ Istilah asing di-italic: *software*, *online*, *database*, *framework*, dll

### Diagram & Gambar

- [ ] ✅ Flow diagram sistem WhatsApp dibuat dan diinsert
- [ ] ✅ Sequence diagram transaksi dibuat dan diinsert
- [ ] ✅ State diagram order flow dibuat dan diinsert
- [ ] ✅ Screenshot dashboard (WhatsApp orders, transactions)
- [ ] ✅ Screenshot contoh percakapan WhatsApp
- [ ] ✅ ERD diperbaiki dan jelas

### Tabel & Data

- [ ] ✅ Tabel mapping rules (command → action → response)
- [ ] ✅ Tabel perbandingan rule-based vs AI
- [ ] ✅ Tabel perbandingan online vs offline transaction
- [ ] ✅ Tabel test cases UAT
- [ ] ✅ Tabel hasil UAT (diisi dengan data real)

---

## 📝 Catatan Penting

### Hal-Hal yang HARUS Diubah di Seluruh Skripsi:

1. **Istilah "Chatbot"**
   ```
   Find: chatbot
   Replace: sistem respons otomatis WhatsApp berbasis aturan
   
   ATAU
   
   Replace: sistem respons otomatis berbasis aturan
   ```

2. **Judul Bab/Sub-Bab yang Mengandung "Chatbot"**
   ```
   SEBELUM:
   4.3 Implementasi Chatbot WhatsApp
   
   SESUDAH:
   4.3 Implementasi Sistem Respons Otomatis WhatsApp Berbasis Aturan (Rule-Based)
   ```

3. **Tambahkan Penjelasan "Rule-Based"**
   
   Di setiap pembahasan tentang sistem WhatsApp, tambahkan keterangan bahwa ini adalah rule-based system, BUKAN AI:
   
   ```
   "Sistem respons otomatis WhatsApp yang dikembangkan menggunakan 
   pendekatan berbasis aturan (rule-based approach), di mana setiap 
   perintah customer dicocokkan dengan pola (pattern) tertentu dan 
   memberikan respons yang sudah ditentukan sebelumnya. Sistem ini 
   TIDAK menggunakan teknologi kecerdasan buatan (artificial intelligence) 
   atau pembelajaran mesin (machine learning)."
   ```

### Screenshot yang Perlu Ditambahkan:

**1. Dashboard Fonnte (Revisi 7)**
- Login ke https://fonnte.com
- Screenshot halaman:
  - Device management (QR code)
  - API token page
  - Webhook settings
  - Message log/history

**2. Contoh Percakapan WhatsApp (Revisi 4, 6)**
- Screenshot percakapan nyata:
  - Customer ketik "MENU"
  - Customer ketik "STOK K10"
  - Customer pesan dengan format lengkap
  - Konfirmasi pesanan
  - Success message
- Bisa gunakan WhatsApp Web untuk screenshot yang lebih rapih

**3. Dashboard Aplikasi (Revisi 8)**
- Screenshot:
  - Dashboard utama (penjualan hari ini, chart)
  - Halaman create transaction
  - Halaman WhatsApp orders
  - Halaman nota/receipt
  - Halaman inventory

**4. Postman/cURL Testing (Revisi 7)**
- Screenshot test API Fonnte:
  - Request ke `/send` dengan token
  - Response success
  - Response error (invalid token)

**5. Flow Diagram (Revisi 4, 6, 8)**
- Buat di draw.io atau Lucidchart:
  - Flow menu utama
  - Flow pemesanan
  - Flow transaksi offline
  - Flow transaksi online (end-to-end)
  - State diagram

---

## 🚀 Quick Start Guide

### Langkah 1: Baca Semua File Panduan

```bash
1. Buka folder: C:\laragon\www\toko_trijaya\paper\
2. Baca urutan:
   - README_REVISI_SKRIPSI.md (file ini)
   - PANDUAN_REVISI_1_2_3.md
   - REVISI_4_Penjelasan_Sistem_WhatsApp_Otomatis.md
   - REVISI_5_UAT_User_Acceptance_Testing.md
   - REVISI_6_Mekanisme_WhatsApp_Lengkap.md
   - REVISI_7_Dokumentasi_API_Fonnte.md
   - REVISI_8_Mekanisme_Transaksi_Online_Offline.md
```

### Langkah 2: Kerjakan Revisi Teknis (4-8) Dulu

**Kenapa duluan?**
- Konten teknis lebih substansial
- Perlu banyak waktu untuk copy-paste dan adjust
- Perlu buat diagram/screenshot

**Cara:**
1. Buka file `.md` di VS Code atau Notepad++
2. Copy section yang relevan
3. Paste ke Word di posisi yang sesuai
4. Adjust formatting (heading, bullet, numbering)
5. Insert gambar/diagram yang diminta

### Langkah 3: Kerjakan Revisi Formatting (1-3)

**Kenapa belakangan?**
- Lebih cepat (mostly find & replace)
- Tidak substantif (hanya perbaikan format)
- Bisa dikerjakan dalam 1-2 hari

**Cara:**
1. Follow panduan di `PANDUAN_REVISI_1_2_3.md`
2. Gunakan checklist yang disediakan
3. Verifikasi dengan print preview

### Langkah 4: Final Review

- [ ] Print 1 halaman untuk cek font size readable
- [ ] Print ERD untuk cek kejelasan
- [ ] Baca ulang semua konten yang ditambahkan
- [ ] Pastikan flow/urutan logis
- [ ] Cek typo dan grammar
- [ ] Verifikasi daftar pustaka (jika ada referensi baru)

---

## 📊 Tracking Progress

### Progress Tracker (Update setiap selesai 1 revisi)

| Revisi | Deskripsi | Estimasi | Start Date | End Date | Status |
|--------|-----------|----------|------------|----------|--------|
| Rev 6 | Mekanisme WA | 2 hari | ___/___/___ | ___/___/___ | [ ] Done |
| Rev 4 | Penjelasan sistem | 2 hari | ___/___/___ | ___/___/___ | [ ] Done |
| Rev 7 | API Fonnte | 1 hari | ___/___/___ | ___/___/___ | [ ] Done |
| Rev 8 | Transaksi online/offline | 1 hari | ___/___/___ | ___/___/___ | [ ] Done |
| Rev 5 | UAT | 1 hari | ___/___/___ | ___/___/___ | [ ] Done |
| Rev 2 | Tata tulis | 1 hari | ___/___/___ | ___/___/___ | [ ] Done |
| Rev 1 | Kutipan gambar/tabel | 1 hari | ___/___/___ | ___/___/___ | [ ] Done |
| Rev 3 | ERD | 0.5 hari | ___/___/___ | ___/___/___ | [ ] Done |

**Total Estimasi:** 9.5 hari kerja (sekitar 2 minggu dengan waktu luang)

---

## 💡 Tips & Trik

### 1. Copy-Paste dari Markdown ke Word

**Masalah:** Format markdown tidak langsung compatible dengan Word

**Solusi:**

**Cara A: Manual Copy**
```
1. Buka file .md di VS Code/Notepad++
2. Copy teks
3. Paste ke Word
4. Format manual:
   - Heading → Apply style "Heading 1/2/3"
   - Code block → Font Courier New, border
   - Bullet list → Convert to Word bullet
```

**Cara B: Convert via Pandoc (Advanced)**
```bash
# Install Pandoc dulu: https://pandoc.org/installing.html

# Convert MD to DOCX
pandoc REVISI_4_Penjelasan_Sistem_WhatsApp_Otomatis.md -o output.docx

# Buka output.docx → Copy paste ke skripsi
```

### 2. Buat Diagram dengan Draw.io

**Langkah:**
1. Buka https://app.diagrams.net
2. Pilih template: Flowchart / Sequence Diagram
3. Drag & drop shapes
4. Atur text dan connector
5. Export: File → Export as → PNG (300 DPI, Scale: 200%)
6. Insert PNG ke Word

**Template yang Perlu Dibuat:**
- Flow diagram pemesanan WhatsApp (dari file Revisi 6)
- Sequence diagram transaksi (dari file Revisi 8)
- State diagram order (dari file Revisi 6)
- Flow diagram sistem respons otomatis (dari file Revisi 4)

### 3. Ambil Screenshot yang Berkualitas

**Tools:**
- **Windows Snipping Tool** (Win + Shift + S)
- **Lightshot** (https://app.prntscr.com)
- **ShareX** (https://getsharex.com)

**Tips Screenshot:**
1. Gunakan resolusi layar tinggi (1920x1080 minimum)
2. Zoom out browser jika perlu (Ctrl + scroll)
3. Crop hanya bagian yang relevan
4. Jangan ada data sensitif (password, token, dll)
5. Beri highlight (arrow, box) pada bagian penting

### 4. Italic Batch dengan Find & Replace

**Langkah:**
1. Ctrl + H (Find & Replace)
2. Find what: `software`
3. Replace with: `software`
4. Click di box "Replace with" → Select text "software"
5. Ctrl + I (set italic)
6. Replace All
7. Ulangi untuk kata lain

**Daftar Kata yang Harus Di-Italic:**

```
software, hardware, firmware
online, offline, website, web application
database, server, cloud, hosting
framework, library, package
Laravel, Bootstrap, MySQL, PHP
API, REST API, webhook, endpoint
chatbot, auto-reply, rule-based
dashboard, interface, user interface
real-time, timestamp, logging
testing, debugging, deployment
login, logout, authentication, authorization
email, password, username
point of sale, cashier, transaction
upload, download, update, delete, create
mobile, smartphone, tablet, desktop
frontend, backend, full-stack
open source, git, github
development, production, staging
cache, session, cookie
```

---

## 🎓 Standar Penulisan UKP (Referensi)

### Format Halaman

```
HALAMAN JUDUL
  - Center aligned
  - Font: Times New Roman
  - Ukuran: 14pt (judul), 12pt (info)

ABSTRAK
  - Max 1 halaman
  - Spasi: 1
  - Font: Times New Roman 12pt
  - Kata kunci: 3-5 kata

DAFTAR ISI
  - Auto-generate (Insert → Table of Contents)
  - Update sebelum print (Klik kanan → Update Field)

BAB I - BAB V
  - Font: Times New Roman 12pt
  - Spasi: 1.5
  - Alignment: Justify
  - Margin: 4-3-3-3 cm

DAFTAR PUSTAKA
  - Sorted alphabetically
  - Format: APA / IEEE (sesuai jurusan)
  - Font: Times New Roman 12pt
  - Spasi: 1.5

LAMPIRAN
  - Code (jika ada)
  - Formulir UAT
  - Dokumentasi tambahan
```

### Penomoran Halaman

```
BAGIAN AWAL (Abstrak, Daftar Isi, dll)
  - Penomoran: Romawi kecil (i, ii, iii, iv, ...)
  - Posisi: Bottom center

BAB I - BAB V
  - Penomoran: Angka arab (1, 2, 3, ...)
  - Mulai dari angka 1 di BAB I
  - Posisi: Bottom center

LAMPIRAN
  - Lanjut dari BAB V
```

**Cara Set Penomoran:**
1. Insert → Page Number → Bottom of Page → Plain Number 2
2. Untuk romawi: Format Page Numbers → Number format: i, ii, iii

---

## 📞 Bantuan Lanjutan

### Jika Butuh Bantuan:

1. **Revisi Teknis (4-8):** Sudah ada di file `.md`, tinggal copy-paste
2. **Revisi Formatting (1-3):** Follow panduan step-by-step
3. **Buat Diagram:** Gunakan draw.io, ada template di file
4. **UAT:** Gunakan template yang sudah disediakan, isi dengan data real

### Jika Stuck:

**Tanya:**
- "Bagian mana dari Revisi X yang perlu saya masukkan ke BAB Y?"
- "Bagaimana cara membuat diagram Z?"
- "Format tabel/gambar ini sudah benar?"

---

## 🎉 Motivasi

Anda sudah 80% selesai! 🎊

Yang tersisa hanya:
1. Copy-paste konten teknis yang sudah disiapkan (30% effort)
2. Buat diagram dari template (30% effort)
3. Fix formatting (30% effort)
4. Review final (10% effort)

**Target:** 2 minggu kerja fokus = Skripsi siap submit! 💪

**Semangat, Michael! You got this! 🔥**

---

**File ini dibuat oleh:** AI Assistant (Claude Sonnet 4.5)
**Tanggal:** 16 Desember 2025
**Untuk:** Michael Darren Sutawijaya (C14210239)
**Skripsi:** Sistem Informasi Toko Trijaya dengan Integrasi WhatsApp

**Good luck with your thesis! 📖✨**
