# TEMPLATE FIND & REPLACE - Perubahan Istilah Chatbot

## 🎯 Tujuan

Mengubah semua istilah "Chatbot" menjadi istilah yang lebih tepat sesuai permintaan revisi.

**Instruksi Dosen:**
> "Istilah chatbot diubah dengan yang lebih sesuai"
> "Rule based balasan dst dimasukkan di judul"

---

## 📝 Daftar Find & Replace

### Di Microsoft Word:

**Cara:**
1. Tekan `Ctrl + H` (Find & Replace)
2. Ikuti tabel di bawah
3. **PENTING:** Replace satu per satu, jangan "Replace All" langsung! (untuk kontrol)

### Tabel Find & Replace

| No | Find (Cari) | Replace (Ganti) | Catatan |
|----|-------------|-----------------|---------|
| 1 | `chatbot WhatsApp` | `sistem respons otomatis WhatsApp berbasis aturan` | Lowercase |
| 2 | `Chatbot WhatsApp` | `Sistem Respons Otomatis WhatsApp Berbasis Aturan` | Title case |
| 3 | `CHATBOT WHATSAPP` | `SISTEM RESPONS OTOMATIS WHATSAPP BERBASIS ATURAN` | Uppercase |
| 4 | `chatbot` | `sistem respons otomatis` | Lowercase, generic |
| 5 | `Chatbot` | `Sistem Respons Otomatis` | Title case, generic |
| 6 | `CHATBOT` | `SISTEM RESPONS OTOMATIS` | Uppercase |
| 7 | `AI Chatbot` | `Sistem Berbasis Aturan` | Jika ada istilah AI Chatbot |
| 8 | `Smart Chatbot` | `Sistem Respons Otomatis Cerdas Berbasis Aturan` | Jika ada istilah Smart |

### Contoh Perubahan di Judul

**SEBELUM:**
```
BAB IV
IMPLEMENTASI DAN PENGUJIAN

4.1 Implementasi Chatbot WhatsApp
4.2 Pengujian Chatbot
```

**SESUDAH:**
```
BAB IV
IMPLEMENTASI DAN PENGUJIAN

4.1 Implementasi Sistem Respons Otomatis WhatsApp Berbasis Aturan (Rule-Based)
4.2 Pengujian Sistem Respons Otomatis Berbasis Aturan
```

### Contoh Perubahan di Isi

**SEBELUM:**
```
Chatbot WhatsApp merupakan fitur yang memungkinkan customer 
untuk melakukan pemesanan secara otomatis. Chatbot ini 
menggunakan teknologi AI untuk memahami pesan customer.
```

**SESUDAH:**
```
Sistem respons otomatis WhatsApp berbasis aturan merupakan 
fitur yang memungkinkan customer untuk melakukan pemesanan 
secara otomatis. Sistem ini menggunakan pendekatan berbasis 
aturan (rule-based approach) untuk mencocokkan pesan customer 
dengan pola tertentu, BUKAN menggunakan teknologi kecerdasan 
buatan (artificial intelligence).
```

---

## 🔍 Verifikasi Setelah Replace

### Checklist Verifikasi:

Setelah Find & Replace, lakukan pengecekan manual:

- [ ] **Cari kata "chatbot"** (Ctrl + F) → Harus tidak ada lagi
- [ ] **Cek judul BAB/sub-bab** → Sudah berubah?
- [ ] **Cek isi paragraf** → Sudah berubah?
- [ ] **Cek caption gambar** → Sudah berubah?
- [ ] **Cek daftar isi** → Update (klik kanan → Update Field)
- [ ] **Cek abstract/ringkasan** → Sudah berubah?

### Bagian yang Harus Ada Kata "Rule-Based"

**WAJIB Tambahkan "Rule-Based" di:**

1. **Judul Skripsi** (jika mengandung chatbot)
   ```
   SEBELUM:
   Sistem Informasi Penjualan dengan Fitur Chatbot WhatsApp
   
   SESUDAH:
   Sistem Informasi Penjualan dengan Fitur Respons Otomatis 
   WhatsApp Berbasis Aturan (Rule-Based System)
   ```

2. **Abstrak**
   ```
   Tambahkan kalimat:
   "Sistem respons otomatis yang dikembangkan menggunakan 
   pendekatan berbasis aturan (rule-based approach) di mana..."
   ```

3. **Judul Bab/Sub-Bab yang Relevan**
   ```
   4.1 Implementasi Sistem Respons Otomatis WhatsApp Berbasis Aturan (Rule-Based)
   4.2 Algoritma Pattern Matching Berbasis Aturan
   4.3 Testing Sistem Rule-Based
   ```

4. **Penjelasan Cara Kerja**
   ```
   "Sistem ini bekerja dengan pendekatan rule-based (berbasis aturan), 
   yaitu..."
   ```

---

## 📌 Penjelasan yang Harus Ditambahkan

### Tambahkan Paragraf Ini di Bagian Penjelasan Sistem:

**Section: Pengenalan Sistem Respons Otomatis**

```
Sistem respons otomatis WhatsApp yang dikembangkan dalam penelitian 
ini merupakan sistem berbasis aturan (rule-based system) yang dirancang 
untuk membantu customer melakukan pemesanan produk secara otomatis 
melalui WhatsApp. Sistem ini BUKAN menggunakan teknologi kecerdasan 
buatan (artificial intelligence) atau pembelajaran mesin (machine learning), 
melainkan menggunakan pendekatan deterministik dengan aturan IF-THEN 
yang telah didefinisikan sebelumnya.

Pemilihan pendekatan berbasis aturan (rule-based) ini didasarkan pada 
beberapa pertimbangan:

1. Kebutuhan sistem yang terstruktur dan spesifik (cek stok, pemesanan, 
   lihat katalog) tidak memerlukan pemahaman bahasa natural yang kompleks.

2. Biaya development dan maintenance lebih rendah dibandingkan sistem 
   berbasis AI yang memerlukan data training dan infrastruktur komputasi 
   yang lebih kompleks.

3. Akurasi response 100% untuk pattern yang sesuai, sementara AI chatbot 
   memiliki akurasi probabilistik (70-95%) yang bisa menghasilkan response 
   yang tidak diinginkan.

4. Response time lebih cepat karena tidak memerlukan proses inference 
   machine learning.

5. Lebih mudah di-maintain dan di-debug oleh developer yang tidak memiliki 
   expertise dalam AI/ML.

Sistem berbasis aturan ini bekerja dengan cara mencocokkan input dari 
customer dengan pola (pattern) tertentu menggunakan regex (regular expression) 
dan string matching. Setiap pola yang cocok akan memicu action tertentu dan 
menghasilkan response yang sudah ditentukan. Misalnya, jika customer mengetik 
"STOK K10", sistem akan mengenali pola "STOK [nama produk]" dan menjalankan 
function handleStockQuery() untuk mengambil data stok dari database dan 
mengirimkan response dengan format yang sudah ditentukan.
```

**Section: Perbedaan dengan AI Chatbot**

```
Penting untuk dipahami bahwa sistem respons otomatis berbasis aturan 
memiliki karakteristik yang berbeda dengan chatbot berbasis kecerdasan 
buatan (AI chatbot). Tabel berikut menunjukkan perbandingan keduanya:

[INSERT TABEL PERBANDINGAN RULE-BASED VS AI - dari file REVISI_4]

Berdasarkan perbandingan pada tabel di atas, dapat dilihat bahwa sistem 
berbasis aturan lebih sesuai untuk use case Toko Trijaya yang memiliki 
kebutuhan terstruktur dan tidak memerlukan fleksibilitas pemahaman 
bahasa natural yang tinggi.
```

---

## ✏️ Kata-Kata Pengganti "Chatbot"

### Istilah yang Bisa Digunakan:

**1. Formal (untuk judul & penjelasan akademis):**
- Sistem Respons Otomatis WhatsApp Berbasis Aturan
- Sistem Berbasis Aturan untuk WhatsApp
- Sistem Auto-Reply WhatsApp dengan Pendekatan Rule-Based
- Sistem Pemrosesan Pesan WhatsApp Berbasis Aturan

**2. Semi-formal (untuk isi/narasi):**
- Sistem respons otomatis
- Sistem berbasis aturan
- Sistem auto-reply
- Fitur respons otomatis WhatsApp

**3. Saat Kontras dengan AI:**
- Sistem rule-based (vs AI-based chatbot)
- Sistem deterministik (vs probabilistic AI)
- Sistem berbasis pattern matching

### Contoh Penggunaan di Kalimat:

```
"Sistem respons otomatis WhatsApp berbasis aturan ini memiliki 
beberapa komponen utama..."

"Pengembangan sistem berbasis aturan dilakukan dengan 
mendefinisikan rules atau aturan-aturan IF-THEN..."

"Kelebihan sistem rule-based dibandingkan AI chatbot adalah..."

"Sistem auto-reply WhatsApp menggunakan pendekatan berbasis aturan 
di mana setiap perintah customer..."
```

---

## 🔄 Revisi Abstrak & Ringkasan

### Abstrak (Revisi)

**Kata kunci yang perlu diupdate:**

**SEBELUM:**
```
Kata Kunci: Sistem Informasi, Point of Sale, Chatbot WhatsApp, Laravel
```

**SESUDAH:**
```
Kata Kunci: Sistem Informasi, Point of Sale, Sistem Berbasis Aturan, 
WhatsApp Integration, Laravel
```

**Isi abstrak yang perlu diupdate:**

Tambahkan kalimat tentang rule-based:
```
"... dilengkapi dengan fitur respons otomatis WhatsApp menggunakan 
pendekatan berbasis aturan (rule-based approach) yang memungkinkan 
customer melakukan pemesanan secara otomatis tanpa intervensi manual..."
```

### Kata Pengantar / Ringkasan Eksekutif

Jika ada kata "chatbot", replace juga dengan istilah yang lebih tepat.

---

## 📊 Progress Checklist - Find & Replace

Print checklist ini dan centang setiap selesai:

```
FIND & REPLACE ISTILAH:

□ Find: "chatbot WhatsApp" → Replace (sesuai tabel)
□ Find: "Chatbot WhatsApp" → Replace
□ Find: "CHATBOT WHATSAPP" → Replace
□ Find: "chatbot" → Replace
□ Find: "Chatbot" → Replace
□ Find: "CHATBOT" → Replace

VERIFIKASI:

□ Ctrl + F: "chatbot" → Result: 0 found
□ Cek judul BAB → Sudah berubah
□ Cek sub-bab → Sudah berubah  
□ Cek caption gambar → Sudah berubah
□ Cek isi paragraf → Sudah berubah
□ Update daftar isi (Ctrl + A → F9)
□ Update kata kunci di abstrak

TAMBAHAN "RULE-BASED":

□ Judul skripsi ada "Rule-Based"
□ Abstrak ada penjelasan "rule-based approach"
□ Judul bab implementasi ada "Rule-Based"
□ Ada paragraf penjelasan perbedaan rule-based vs AI
□ Ada tabel perbandingan rule-based vs AI

FINAL CHECK:

□ Semua perubahan sudah di-save
□ File di-backup (copy ke folder lain)
□ Ready untuk review dosen

Nama: _________________
Tanggal: ___/___/_____
Centang: □ SELESAI
```

---

## 🎁 Bonus: Template Penjelasan Rule-Based (Siap Copy-Paste)

### Untuk BAB II (Landasan Teori):

```
2.X Rule-Based System (Sistem Berbasis Aturan)

Rule-based system adalah sistem yang bekerja berdasarkan aturan-aturan 
(rules) yang telah didefinisikan sebelumnya dalam bentuk IF-THEN. 
Sistem ini bersifat deterministik, artinya untuk input yang sama akan 
selalu menghasilkan output yang sama.

Karakteristik rule-based system:
1. Deterministik: Output dapat diprediksi dengan pasti
2. Transparan: Aturan dapat dilihat dan dipahami dengan jelas
3. Mudah di-maintain: Penambahan atau perubahan aturan dapat dilakukan 
   dengan mudah tanpa perlu re-training
4. Tidak memerlukan data training: Berbeda dengan machine learning yang 
   memerlukan dataset besar untuk training
5. Cepat: Proses matching pattern sangat cepat (< 1 detik)

Perbedaan utama rule-based system dengan AI-based system terletak pada 
cara sistem membuat keputusan. Rule-based menggunakan aturan eksplisit 
yang didefinisikan oleh developer, sementara AI-based system "belajar" 
dari data dan membuat keputusan berdasarkan pola yang ditemukan dalam 
data training (Russell & Norvig, 2020).

Dalam konteks aplikasi WhatsApp, rule-based system cocok digunakan untuk 
use case yang terstruktur seperti:
- Customer service untuk pertanyaan yang sering ditanyakan (FAQ)
- Pemesanan produk dengan format tertentu
- Pengecekan status (order status, stock status)
- Navigasi menu

(Tambahkan referensi)
Referensi:
Russell, S., & Norvig, P. (2020). Artificial Intelligence: A Modern Approach 
(4th ed.). Pearson.
```

### Untuk BAB III (Perancangan):

```
3.X Perancangan Sistem Respons Otomatis Berbasis Aturan

Sistem respons otomatis yang dirancang menggunakan pendekatan berbasis 
aturan (rule-based approach) dengan arsitektur sebagai berikut:

[INSERT GAMBAR ARSITEKTUR]

Sistem terdiri dari beberapa komponen utama:

1. WhatsappService
   Komponen yang menangani komunikasi dengan API Fonnte.com untuk mengirim 
   dan menerima pesan WhatsApp.

2. ChatbotService (Rule Engine)
   Komponen inti yang berisi logika rule-based system. Service ini memiliki 
   fungsi-fungsi untuk:
   - Pattern matching: Mencocokkan input customer dengan pattern tertentu
   - State management: Menyimpan konteks percakapan
   - Response generation: Menghasilkan respons sesuai rule yang match

3. Rule Repository
   Kumpulan aturan (rules) yang didefinisikan dalam bentuk kondisi IF-THEN:
   
   IF customer ketik "STOK [nama produk]" THEN
     - Extract nama produk
     - Query database untuk cek stok
     - Generate response dengan format stok
     - Kirim via WhatsappService
   
   IF customer ketik "PESAN" THEN
     - Set state = 'waiting_order'
     - Generate response dengan format pesanan
     - Kirim via WhatsappService
   
   (Dan seterusnya untuk command lain)

Rule-based approach dipilih karena use case pemesanan produk memiliki 
struktur yang jelas dan tidak memerlukan pemahaman bahasa natural yang 
kompleks. Customer hanya perlu mengikuti format yang ditentukan, dan 
sistem akan memproses dengan akurasi 100% (untuk input yang sesuai format).
```

### Untuk BAB IV (Implementasi):

```
4.X Implementasi Algoritma Pattern Matching Berbasis Aturan

Implementasi rule-based system dalam sistem respons otomatis WhatsApp 
menggunakan beberapa teknik pattern matching:

4.X.1 String Matching
Pencocokan string sederhana menggunakan fungsi strpos() dan strtolower():

```php
// Rule: Jika pesan mengandung "stok" → handle stock query
if (strpos($message, 'stok') !== false) {
    return $this->handleStockQuery($phone, $message);
}
```

Keuntungan: Cepat, sederhana, mudah dipahami
Kekurangan: Tidak fleksibel, rentan terhadap typo

4.X.2 Regular Expression (Regex) Matching
Pencocokan pattern yang lebih kompleks menggunakan regex:

```php
// Rule: Extract format pesanan
// Pattern: "NAMA: [nama customer]"
if (preg_match('/nama\s*:\s*(.+?)(?=\n|$)/i', $message, $matches)) {
    $customerName = trim($matches[1]);
}
```

Keuntungan: Fleksibel, bisa handle variasi input
Kekurangan: Lebih lambat dari string matching, susah di-debug

4.X.3 Exact Match
Pencocokan eksact untuk command tertentu:

```php
// Rule: Command "0" atau "MENU" → kirim menu
if ($message === '0' || $message === 'menu') {
    return $this->sendMenu($phone);
}
```

Keuntungan: Paling cepat, paling akurat
Kekurangan: Tidak toleran terhadap variasi (typo akan fail)

(Lanjutkan dengan penjelasan lebih detail...)
```

---

## 🎨 Template Caption Gambar/Tabel (dengan Rule-Based)

### Caption yang Perlu Diupdate:

**SEBELUM:**
```
Gambar 4.1: Flowchart Chatbot WhatsApp
```

**SESUDAH:**
```
Gambar 4.1: Flowchart Sistem Respons Otomatis WhatsApp Berbasis Aturan
```

---

**SEBELUM:**
```
Tabel 4.2: Rules Chatbot
```

**SESUDAH:**
```
Tabel 4.2: Mapping Rules Sistem Berbasis Aturan
```

---

**SEBELUM:**
```
Gambar 4.5: Arsitektur Chatbot
```

**SESUDAH:**
```
Gambar 4.5: Arsitektur Sistem Respons Otomatis Berbasis Aturan (Rule-Based Architecture)
```

---

## ⚠️ PERHATIAN

### Kata "Chatbot" yang BOLEH Tetap Ada:

**Dalam konteks perbandingan atau referensi:**

```
✅ BOLEH:
"Berbeda dengan AI chatbot yang menggunakan machine learning, 
sistem ini menggunakan pendekatan rule-based..."

"Pada penelitian terdahulu, Peneliti A (2020) mengembangkan 
chatbot menggunakan NLP, namun dalam penelitian ini digunakan 
pendekatan yang berbeda yaitu rule-based system..."

"Kelebihan sistem rule-based dibandingkan AI chatbot adalah..."
```

**Dalam kutipan/referensi:**

```
✅ BOLEH:
Menurut Shawar & Atwell (2007), "Chatbot systems can be classified 
into rule-based and machine learning-based approaches."
```

---

## 📖 Referensi untuk Ditambahkan

### Referensi Rule-Based System:

```
1. Russell, S. J., & Norvig, P. (2020). Artificial Intelligence: 
   A Modern Approach (4th ed.). Pearson Education.

2. Shawar, B. A., & Atwell, E. (2007). Chatbots: Are they Really Useful? 
   Journal for Language Technology and Computational Linguistics, 22(1), 29-49.

3. Adamopoulou, E., & Moussiades, L. (2020). Chatbots: History, technology, 
   and applications. Machine Learning with Applications, 2, 100006.

4. Dahiya, M. (2017). A Tool of Conversation: Chatbot. International Journal 
   of Computer Sciences and Engineering, 5(5), 158-161.
```

(Sesuaikan dengan referensi yang tersedia di perpustakaan/online)

---

**SELAMAT MENGERJAKAN!**

File ini akan sangat membantu untuk consistency dalam perubahan istilah.

**Good luck! 🍀**
