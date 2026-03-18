# PANDUAN REVISI 1, 2, dan 3 (Dikerjakan di Microsoft Word)

## REVISI 1: Pastikan Semua Gambar dan Tabel Dikutip dalam Narasi

### Apa yang Dimaksud?

Setiap gambar dan tabel yang ada di skripsi **HARUS** disebutkan/direferensikan dalam teks narasi. Jangan ada gambar/tabel yang "tiba-tiba muncul" tanpa penjelasan.

### Cara Melakukan:

#### **Step 1: Buat Daftar Semua Gambar dan Tabel**f
Buat checklist di Word:

```
DAFTAR GAMBAR:
□ Gambar 1.1: Logo Toko Trijaya
□ Gambar 2.1: Use Case Diagram
□ Gambar 2.2: Activity Diagram - Transaksi Offline
□ Gambar 2.3: Activity Diagram - Transaksi Online
□ Gambar 2.4: Sequence Diagram - WhatsApp Flow
□ Gambar 3.1: ERD (Entity Relationship Diagram)
□ Gambar 3.2: Arsitektur Sistem
□ Gambar 4.1: Screenshot Dashboard Utama
□ Gambar 4.2: Screenshot Form Transaksi
□ Gambar 4.3: Screenshot WhatsApp Order Dashboard
□ Gambar 4.4: Contoh Percakapan WhatsApp
□ Gambar 4.5: Flowchart Sistem Respons Otomatis
... (tambahkan semua)

DAFTAR TABEL:
□ Tabel 2.1: Tabel Kebutuhan Fungsional
□ Tabel 2.2: Tabel Kebutuhan Non-Fungsional
□ Tabel 3.1: Tabel Struktur Database - Products
□ Tabel 3.2: Tabel Struktur Database - Transactions
□ Tabel 3.3: Tabel Relasi Antar Tabel
□ Tabel 4.1: Mapping Rules WhatsApp
□ Tabel 4.2: Test Case Summary
□ Tabel 5.1: UAT Test Cases
... (tambahkan semua)
```

#### **Step 2: Cek Setiap Gambar/Tabel Sudah Dikutip atau Belum**

Untuk setiap gambar/tabel, cari di narasi apakah ada kalimat yang mereferensikannya.

**Contoh BENAR (Gambar Dikutip):**

```
Sistem menggunakan arsitektur client-server dengan tiga layer 
utama seperti yang ditunjukkan pada Gambar 3.2. Layer pertama 
adalah presentation layer yang bertugas...

[Insert Gambar 3.2 di sini]
Gambar 3.2: Arsitektur Sistem Toko Trijaya
```

**Contoh SALAH (Gambar Tidak Dikutip):**

```
Sistem menggunakan arsitektur client-server dengan tiga layer.

[Insert Gambar 3.2 di sini]
Gambar 3.2: Arsitektur Sistem

← TIDAK ADA referensi "Gambar 3.2" di paragraf sebelumnya!
```

#### **Step 3: Tambahkan Kutipan untuk Gambar/Tabel yang Belum Dikutip**

**Template Kalimat untuk Mengutip Gambar:**

```
1. "... seperti yang ditunjukkan pada Gambar X.X"
2. "... dapat dilihat pada Gambar X.X"
3. "Gambar X.X menunjukkan ..."
4. "Berdasarkan Gambar X.X, ..."
5. "Pada Gambar X.X terlihat bahwa ..."
6. "Proses ini digambarkan dalam Gambar X.X"
```

**Template Kalimat untuk Mengutip Tabel:**

```
1. "... seperti yang tercantum dalam Tabel X.X"
2. "Tabel X.X menunjukkan ..."
3. "Data pada Tabel X.X menjelaskan ..."
4. "Berdasarkan Tabel X.X, ..."
5. "Seperti terlihat pada Tabel X.X, ..."
```

**Contoh Perbaikan:**

**SEBELUM:**
```
Sistem memiliki beberapa kebutuhan fungsional dan non-fungsional.

Tabel 2.1: Kebutuhan Fungsional
(isi tabel...)
```

**SESUDAH:**
```
Sistem memiliki beberapa kebutuhan fungsional dan non-fungsional
yang dirincikan dalam Tabel 2.1. Kebutuhan fungsional mencakup 
fitur-fitur utama seperti manajemen transaksi, stok, dan pesanan 
WhatsApp. Sementara kebutuhan non-fungsional mencakup aspek 
performa, keamanan, dan ketersediaan sistem.

Tabel 2.1: Kebutuhan Fungsional
(isi tabel...)

Berdasarkan Tabel 2.1, terdapat 15 kebutuhan fungsional yang 
harus dipenuhi oleh sistem...
```

#### **Step 4: Verifikasi dengan Find & Replace**

1. Tekan `Ctrl + F` di Word
2. Cari: "Gambar 1.1" → Apakah muncul di narasi? Jika tidak, tambahkan!
3. Ulangi untuk "Gambar 1.2", "Gambar 2.1", dst
4. Ulangi untuk "Tabel 1.1", "Tabel 2.1", dst

---

## REVISI 2: Cek Tata Tulis Skripsi UKP

### Aturan Penulisan Skripsi UKP

#### **2.1 Penggunaan Huruf Tebal (Bold)**

**Aturan:**
- Judul BAB: **HURUF KAPITAL TEBAL**
- Sub-bab tingkat 1 (2.1): **Huruf Kapital Setiap Kata, Tebal**
- Sub-bab tingkat 2 (2.1.1): **Huruf Kapital Awal Kalimat Saja, Tebal**

**Contoh:**

```
BAB II
LANDASAN TEORI                          ← KAPITAL SEMUA, TEBAL, CENTER

2.1 Sistem Informasi                    ← Kapital Setiap Kata, TEBAL
Sistem informasi adalah...              ← Teks biasa

2.1.1 Definisi sistem informasi         ← Kapital awal saja, TEBAL
Definisi sistem informasi menurut...    ← Teks biasa
```

#### **2.2 Penggunaan Huruf Miring (Italic)**

**Aturan: Semua istilah asing (bukan Bahasa Indonesia) ditulis MIRING**

**Kata-Kata yang Harus Miring:**

```
✅ HARUS MIRING:
- software, hardware, database
- point of sale, cashier, dashboard
- online, offline, website, web application
- smartphone, tablet, desktop
- cloud, server, hosting
- upload, download, update, delete
- login, logout, username, password
- chatbot, auto-reply, webhook
- API (Application Programming Interface)
- Framework (Laravel, Bootstrap, dll)
- real-time, timestamp
- email, attachment
- feedback, user-friendly
- interface, front-end, back-end
- testing, debugging
- dll (semua bahasa Inggris)

❌ TIDAK PERLU MIRING:
- Nama sistem: "Sistem Informasi Toko Trijaya" (bukan asing)
- Kata serapan yang sudah umum: sistem, komputer, data, program (sudah KBBI)
- Nama tempat: Indonesia, Denpasar, Bali
- Nama orang: Michael, Darren
```

#### **Cara Cepat:**

1. **Cara Manual:**
   - Select kata yang harus miring
   - Tekan `Ctrl + I` atau klik tombol *Italic*

2. **Cara Find & Replace:**
   - Tekan `Ctrl + H`
   - Find: `software` (tanpa format)
   - Replace: `software` (dengan format Italic)
   - Click "Replace All"
   - Ulangi untuk kata-kata lain

**⚠️ HATI-HATI:** Jangan italic kata yang sudah dalam kutipan code atau tabel!

#### **2.3 Rata Kanan Kiri (Justify)**

**Aturan:**
- Teks isi/body: **Justify** (rata kanan kiri)
- Judul BAB: **Center** (rata tengah)
- Judul sub-bab: **Left** (rata kiri)

**Cara Setting:**

1. Select semua teks isi (Ctrl + A)
2. Klik `Ctrl + J` atau:
   - Home tab → Paragraph group → Justify button

**Bagian yang TIDAK justify:**
- Judul BAB (center)
- Judul sub-bab (left)
- Daftar bullet/numbering (left)
- Code snippet (left)
- Kutipan/quote (bisa indent)

#### **2.4 Spasi dan Margin**

**Aturan Umum UKP:**
- Font: Times New Roman, 12pt
- Line spacing: 1.5 lines (bukan double)
- Alignment: Justify (rata kanan kiri)
- Paragraph spacing: 0 pt before, 0 pt after (kecuali antar sub-bab)
- Margin:
  - Top: 3 cm
  - Bottom: 3 cm
  - Left: 4 cm
  - Right: 3 cm
- Page number: Bottom center, mulai dari BAB I

**Cara Set:**
1. Select All (Ctrl + A)
2. Home tab → Paragraph → Line Spacing → 1.5
3. Layout tab → Margins → Custom Margins → Input nilai di atas

#### **2.5 Penomoran Gambar dan Tabel**

**Format:**
- Gambar: `Gambar [Bab].[Nomor]: [Judul]`
  - Contoh: `Gambar 3.1: Use Case Diagram Sistem`
- Tabel: `Tabel [Bab].[Nomor]: [Judul]`
  - Contoh: `Tabel 4.2: Hasil Pengujian UAT`

**Posisi Caption:**
- **Gambar:** Caption di BAWAH gambar (center)
- **Tabel:** Caption di ATAS tabel (center)

**Cara Numbering Otomatis di Word:**
1. Klik gambar → References tab → Insert Caption
2. Label: "Gambar"
3. Position: "Below selected item"
4. Numbering: Chapter starts at 1 (atau sesuai bab)

---

## REVISI 3: Perbaiki Gambar ERD agar Lebih Jelas

### Masalah: ERD Tidak Terlihat Jelas

**Kemungkinan Penyebab:**
1. Resolusi gambar terlalu rendah
2. Font dalam gambar terlalu kecil
3. Warna tidak kontras
4. Tabel terlalu banyak, gambar terlalu kecil saat dicetak

### Solusi:

#### **Opsi 1: Re-export ERD dengan Resolusi Tinggi**

Jika ERD dibuat dengan tools (MySQL Workbench, draw.io, dll):

**MySQL Workbench:**
1. Buka file ERD (.mwb)
2. File → Export → Export as PNG
3. **Settings penting:**
   - Resolution: **300 DPI** (minimum)
   - Scale: 100% atau 150%
   - Include: All tables
4. Save as PNG

**Draw.io / Diagrams.net:**
1. File → Export as → PNG
2. **Settings:**
   - Zoom: 200%
   - Border width: 10
   - Transparent background: Unchecked
3. Export

**Lucidchart:**
1. File → Download → PNG
2. Select: High Quality (300 DPI)

#### **Opsi 2: Pisah ERD Menjadi Beberapa Gambar**

Jika ERD terlalu besar dan kompleks:

**Bagi menjadi:**
- Gambar 3.1: ERD - Modul Transaksi (tables: transactions, transaction_details, products, customers)
- Gambar 3.2: ERD - Modul WhatsApp (tables: whatsapp_orders, whatsapp_order_items)
- Gambar 3.3: ERD - Modul Inventory (tables: inventories, product_units, purchases)
- Gambar 3.4: ERD - Modul Cashflow & Report (tables: cash_flows, reports)

**Keuntungan:**
- ✅ Setiap gambar lebih fokus
- ✅ Font lebih besar dan jelas
- ✅ Lebih mudah dijelaskan dalam narasi

#### **Opsi 3: Buat ERD Ulang dengan Tools yang Lebih Baik**

**Recommended Tools:**

**1. dbdiagram.io** (Online, Free)
- URL: https://dbdiagram.io
- Kelebihan:
  - ✅ Export high-res PNG/SVG
  - ✅ Clean & modern design
  - ✅ Easy to read

**Contoh Syntax dbdiagram.io:**
```sql
Table products {
  id int [pk, increment]
  name varchar(255)
  type_id int [ref: > product_types.id]
  color_id int [ref: > product_colors.id]
  created_at timestamp
}

Table product_units {
  id int [pk, increment]
  product_id int [ref: > products.id]
  name varchar(50)
  stock int
  price decimal
  conversion_value int
}

Table transactions {
  id int [pk, increment]
  user_id int [ref: > users.id]
  customer_id int [ref: > customers.id]
  whatsapp_order_id int [ref: > whatsapp_orders.id]
  transaction_code varchar(50)
  total_amount decimal
  payment_method varchar(20)
  status varchar(20)
  created_at timestamp
}

Table whatsapp_orders {
  id int [pk, increment]
  name varchar(255)
  phone varchar(20)
  order_text text
  status varchar(20)
  created_at timestamp
}

// (tambahkan tabel lainnya...)
```

**2. Draw.io** (Free, Offline/Online)
- URL: https://app.diagrams.net
- Kelebihan:
  - ✅ Offline mode available
  - ✅ Banyak template
  - ✅ Export SVG (vector, tidak pecah)

**3. Lucidchart** (Freemium)
- URL: https://lucidchart.com
- Kelebihan:
  - ✅ Professional look
  - ✅ Collaboration features
  - ✅ ERD templates

#### **Opsi 4: Improve ERD yang Ada**

Jika tidak mau buat ulang, improve ERD existing:

**Tips:**

1. **Perbesar Font:**
   - Table name: Min 14pt
   - Column name: Min 10pt
   - Data type: Min 9pt

2. **Gunakan Warna yang Kontras:**
   - Header table: Warna gelap (navy blue, dark gray)
   - Text: Putih (jika header gelap) atau hitam (jika header terang)
   - Background: Putih atau very light gray

3. **Tambahkan Legend:**
   ```
   LEGEND:
   🔑 PK = Primary Key
   🔗 FK = Foreign Key
   ─── = One-to-Many Relationship
   ═══ = One-to-One Relationship
   ```

4. **Group Tables by Module:**
   ```
   ┌─────────────────────────────┐
   │  MODUL TRANSAKSI            │
   │  - transactions             │
   │  - transaction_details      │
   │  - customers                │
   └─────────────────────────────┘

   ┌─────────────────────────────┐
   │  MODUL WHATSAPP             │
   │  - whatsapp_orders          │
   │  - whatsapp_order_items     │
   └─────────────────────────────┘
   ```

5. **Buat ERD dalam Format Landscape:**
   - Lebih banyak ruang horizontal
   - Tabel bisa disusun lebih rapi

#### **Step-by-Step Fix ERD di Word:**

**1. Delete ERD yang lama (low quality)**

**2. Insert ERD baru dengan resolution tinggi:**
```
Insert tab → Pictures → Select ERD file (.png dengan 300 DPI)
```

**3. Resize gambar:**
```
Klik gambar → Format tab → Size
Width: 15 cm (untuk portrait) atau 20 cm (untuk landscape)
✅ Lock aspect ratio
```

**4. Compress gambar (optional, agar file Word tidak terlalu besar):**
```
Klik gambar → Format tab → Compress Pictures
Resolution: "Print (220 ppi)" atau "High quality (330 ppi)"
❌ JANGAN pilih "Email (96 ppi)" - terlalu rendah!
```

**5. Tambahkan caption:**
```
Klik gambar → References → Insert Caption
Label: Gambar
Position: Below selected item
Caption: "ERD (Entity Relationship Diagram) Sistem Toko Trijaya"
```

**6. Update referensi di narasi:**
```
Tambahkan kalimat seperti:
"Gambar 3.1 menunjukkan Entity Relationship Diagram (ERD) dari
sistem Toko Trijaya yang terdiri dari 12 tabel utama..."
```

### Checklist Kualitas ERD:

- [ ] Semua tabel terlihat jelas
- [ ] Nama tabel bisa dibaca (min font 10pt saat dicetak)
- [ ] Nama kolom bisa dibaca
- [ ] Primary Key (PK) ditandai jelas
- [ ] Foreign Key (FK) ditandai jelas
- [ ] Relasi antar tabel terlihat (garis penghubung)
- [ ] Kardinalitas jelas (1:1, 1:N, N:M)
- [ ] Ada legend/keterangan
- [ ] Warna kontras (tidak pucat)
- [ ] Resolusi minimum 300 DPI
- [ ] Caption ada dan benar
- [ ] Disebutkan dalam narasi

---

## Checklist Lengkap Revisi 1-3

### Revisi 1: Kutipan Gambar dan Tabel

```
□ Buat daftar semua gambar (Gambar 1.1, 1.2, 2.1, ...)
□ Buat daftar semua tabel (Tabel 1.1, 1.2, 2.1, ...)
□ Cek setiap gambar sudah dikutip di narasi
□ Cek setiap tabel sudah dikutip di narasi
□ Tambahkan kutipan untuk yang belum ada
□ Pastikan kutipan SEBELUM gambar/tabel muncul
□ Verifikasi dengan Ctrl+F
```

### Revisi 2: Tata Tulis

```
HURUF TEBAL:
□ Judul BAB: KAPITAL SEMUA, TEBAL, CENTER
□ Sub-bab 1 (X.X): Kapital Setiap Kata, Tebal, Left
□ Sub-bab 2 (X.X.X): Kapital awal, Tebal, Left

HURUF MIRING:
□ Cek semua kata bahasa Inggris (software, online, dll)
□ Set semua kata asing ke Italic (Ctrl + I)
□ Verifikasi tidak ada kata asing yang tidak miring

ALIGNMENT:
□ Teks isi: Justify (Ctrl + J)
□ Judul BAB: Center
□ Judul sub-bab: Left
□ Daftar bullet: Left

FONT & SPACING:
□ Font: Times New Roman 12pt
□ Line spacing: 1.5
□ Paragraph spacing: 0 before, 0 after

MARGIN:
□ Top: 3 cm
□ Bottom: 3 cm
□ Left: 4 cm
□ Right: 3 cm

PAGE NUMBER:
□ Mulai dari BAB I (bukan dari cover)
□ Posisi: Bottom center
□ Format: Angka arab (1, 2, 3, ...)
□ BAB Pendahuluan sebelumnya: Romawi kecil (i, ii, iii, ...)
```

### Revisi 3: ERD

```
□ ERD resolution minimum 300 DPI
□ Font dalam ERD min 10pt (readable saat print)
□ Semua tabel terlihat jelas
□ Relasi antar tabel jelas (garis + kardinalitas)
□ PK dan FK ditandai
□ Ada legend/keterangan
□ Warna kontras (tidak pucat)
□ Caption ada di BAWAH gambar
□ Caption: "Gambar X.X: ERD Sistem Toko Trijaya"
□ ERD disebutkan dalam narasi
□ Jika terlalu kompleks, pisah menjadi beberapa gambar
```

---

## Tips Revisi Efisien

### 1. Gunakan Style di Word

**Buat Styles untuk konsistensi:**

```
Home tab → Styles → Create New Style

Style 1: "Judul BAB"
- Font: Times New Roman, 14pt, BOLD
- Alignment: Center
- Spacing: 12pt before, 6pt after

Style 2: "Sub-Bab 1"
- Font: Times New Roman, 12pt, BOLD
- Alignment: Left
- Spacing: 12pt before, 6pt after

Style 3: "Sub-Bab 2"
- Font: Times New Roman, 12pt, BOLD
- Alignment: Left
- Spacing: 6pt before, 3pt after

Style 4: "Body Text"
- Font: Times New Roman, 12pt
- Alignment: Justify
- Line spacing: 1.5
- First line indent: 1.27 cm (0.5 inch)
```

### 2. Gunakan Find & Replace untuk Batch Edit

**Contoh: Italic semua kata "software"**

```
Ctrl + H
Find: software
Replace: software (select replacement text → Ctrl + I untuk italic)
Replace All
```

### 3. Gunakan Navigation Pane

```
View tab → Navigation Pane
```

Ini akan tampilkan outline dokumen:
- BAB I
  - 1.1 Latar Belakang
  - 1.2 Rumusan Masalah
  - ...
- BAB II
  - 2.1 ...

Lebih mudah untuk navigate dan cek struktur.

### 4. Print Preview Sebelum Final

```
File → Print → Print Preview
```

Cek:
- Margin sudah benar?
- Font terlihat jelas?
- Gambar tidak pecah?
- Page number sudah benar?

---

## Template Checklist Progress

Gunakan checklist ini untuk track progress revisi:

```
REVISI 1: KUTIPAN GAMBAR & TABEL
Tanggal Mulai: ___/___/_____
Tanggal Selesai: ___/___/_____

BAB I:
  □ Gambar 1.1 dikutip
  □ Gambar 1.2 dikutip
  □ Tabel 1.1 dikutip

BAB II:
  □ Gambar 2.1 dikutip
  □ Gambar 2.2 dikutip
  □ Tabel 2.1 dikutip
  □ Tabel 2.2 dikutip

BAB III:
  □ Gambar 3.1 (ERD) dikutip ✓ PENTING!
  □ Gambar 3.2 dikutip
  □ Tabel 3.1 dikutip
  ... dst

─────────────────────────────────────

REVISI 2: TATA TULIS
Tanggal Mulai: ___/___/_____
Tanggal Selesai: ___/___/_____

FORMATTING:
  □ Judul BAB: Kapital, Tebal, Center
  □ Sub-bab: Format sesuai aturan
  □ Body text: Justify, Times New Roman 12pt, 1.5 spacing
  □ Margin: 4-3-3-3 cm

ITALIC:
  □ BAB I: Kata asing di-italic
  □ BAB II: Kata asing di-italic
  □ BAB III: Kata asing di-italic
  □ BAB IV: Kata asing di-italic
  □ BAB V: Kata asing di-italic

─────────────────────────────────────

REVISI 3: ERD
Tanggal: ___/___/_____

  □ ERD di-export ulang (300 DPI)
  □ Font dalam ERD min 10pt
  □ Semua tabel visible
  □ Relasi terlihat jelas
  □ Caption ditambahkan
  □ Dikutip dalam narasi
  □ Insert ke Word
  □ Verifikasi print preview
  
  Status: □ Selesai  □ Belum

─────────────────────────────────────

APPROVAL:
□ Semua revisi 1-3 selesai
□ Self-review OK
□ Siap untuk review dosen

Nama: _________________
Tanda Tangan: __________
Tanggal: ___/___/_____
```

---

## Contoh Perbaikan (Before & After)

### Contoh 1: Kutipan Gambar

**❌ SEBELUM (Salah):**
```
BAB III
ANALISIS DAN PERANCANGAN SISTEM

3.1 Perancangan Database

Sistem menggunakan 12 tabel untuk menyimpan data.

[Gambar 3.1: ERD]

Tabel products menyimpan data produk...
```

**✅ SESUDAH (Benar):**
```
BAB III
ANALISIS DAN PERANCANGAN SISTEM

3.1 Perancangan Database

Sistem menggunakan 12 tabel untuk menyimpan data seperti yang 
ditunjukkan pada Gambar 3.1. ERD ini menggambarkan relasi antar 
tabel yang terdiri dari modul transaksi, modul WhatsApp, modul 
inventory, dan modul cashflow.

[Gambar 3.1: ERD]
Gambar 3.1: Entity Relationship Diagram Sistem Toko Trijaya

Berdasarkan Gambar 3.1, tabel products menyimpan data produk 
yang memiliki relasi one-to-many dengan tabel product_units...
```

### Contoh 2: Italic

**❌ SEBELUM (Salah):**
```
Sistem ini menggunakan framework Laravel untuk backend dan 
Bootstrap untuk frontend. Database menggunakan MySQL dan 
deployment dilakukan di cloud server. Fitur utama meliputi 
point of sale, inventory management, dan chatbot WhatsApp.
```
(Tidak ada kata yang italic)

**✅ SESUDAH (Benar):**
```
Sistem ini menggunakan framework Laravel untuk backend dan 
Bootstrap untuk frontend. Database menggunakan MySQL dan 
deployment dilakukan di cloud server. Fitur utama meliputi 
point of sale, inventory management, dan chatbot WhatsApp.
```
(Semua kata asing di-italic)

### Contoh 3: Tata Tulis

**❌ SEBELUM (Salah):**
```
BAB II                                    ← Tidak tebal
landasan teori                            ← Tidak kapital

2.1 sistem informasi                      ← Tidak tebal, tidak kapital
sistem informasi adalah...                ← Left align (harusnya justify)
```

**✅ SESUDAH (Benar):**
```
BAB II                                    ← Tebal, center
LANDASAN TEORI                            ← Kapital semua, tebal, center

2.1 Sistem Informasi                      ← Kapital setiap kata, tebal, left
Sistem informasi adalah...                ← Justify (rata kanan kiri)
```

---

**SELAMAT MENGERJAKAN REVISI!**

Jika ada pertanyaan atau butuh bantuan lanjutan untuk revisi 1-3, 
silakan tanya! 😊
