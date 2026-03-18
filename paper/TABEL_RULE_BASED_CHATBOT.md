# Tabel Rules Sistem Respons Otomatis WhatsApp Berbasis Aturan (Rule-Based)

## Tabel 1: Mapping Rules Command ke Action

| No | Pattern/Keyword | Priority | Deteksi | Action | Response | Contoh Input Customer | Contoh Output Sistem |
|----|----------------|----------|---------|--------|----------|----------------------|---------------------|
| 1 | `"0"` atau `"MENU"` | **PRIORITAS TERTINGGI** | Exact match atau contains | Reset semua state, kirim menu utama | Menu lengkap dengan 5 opsi | `0` atau `MENU` | Menu dengan 5 pilihan (KATALOG, STOK, PESAN, CEK PESANAN, FAQ) |
| 2 | `"1"` atau `"KATALOG"` | Tinggi | Exact match atau contains | Tampilkan daftar jenis produk | List jenis produk dengan nomor | `1` atau `katalog` | Daftar jenis produk (1. KELING, 2. KANCING, dll) |
| 3 | `"2"` atau `"STOK [nama produk]"` | Tinggi | Contains "stok" atau "stock" | Cek stok produk | Info stok produk (nama, warna, stok, harga) | `2` atau `STOK K10` | "📦 STOK PRODUK\n\n🔹 K10\n   Stok: 50 pcs\n   Harga: Rp 25.000 per LUSIN" |
| 4 | `"3"` atau `"PESAN"` | Tinggi | Exact match "pesan" atau "order" | Mulai proses pemesanan | Format pesanan | `3` atau `pesan` | Format: "Nama: ...\nNama Produk: ...\nWarna Produk: ...\nJumlah Produk: ..." |
| 5 | `"4"` atau `"CEK PESANAN"` | Tinggi | Contains "cek pesanan", "pesanan saya", "riwayat pesanan", "lihat pesanan" | Tampilkan 5 pesanan terakhir | List pesanan dengan status | `4` atau `cek pesanan` | List pesanan (#123, #124, dll) dengan status |
| 6 | `"5"` atau `"FAQ"` | Tinggi | Exact match "faq" | Tampilkan FAQ | FAQ lengkap | `5` atau `faq` | FAQ tentang produk, pemesanan, lokasi, dll |
| 7 | Format pesanan: `"NAMA:"` atau `"PESANAN:"` | Tinggi | Regex: `/nama\s*:/i` atau `/pesanan\s*:/i` | Parse format pesanan | Konfirmasi pesanan atau error format | `NAMA: DARREN\nPESANAN: 2 LUSIN K10 warna NKL` | Konfirmasi pesanan dengan detail stok |
| 8 | `"BATAL"` atau `"CANCEL"` | Sedang | Contains "batal", "cancel", "batalkan", "tidak jadi", "gagal" | Batalkan pesanan yang sedang dibuat | Pesan batal + menu | `batal` | "❌ Pesanan dibatalkan.\n\nKetik *1/KATALOG* untuk lihat katalog" |
| 9 | `"BATAL PESANAN [ID]"` | Sedang | Contains "batal pesanan" + extract ID | Batalkan pesanan dengan ID tertentu | Konfirmasi batal atau error | `batal pesanan 123` | "✅ Pesanan berhasil dibatalkan!\n\nPesanan #123 telah dibatalkan." |
| 10 | `"TERIMAKASIH"` | Rendah | Contains "terimakasih", "terima kasih", "makasih", "thanks", "thank you" | Respon sopan + menu | Ucapan balasan + menu | `terimakasih` | "Sama-sama kak! 😊\n\nKetik *1/KATALOG* untuk lihat katalog" |
| 11 | Angka (1-9) saat lihat katalog | Sedang | Numeric + state `catalog_state` aktif | Pilih jenis produk berdasarkan nomor | List produk dalam jenis tersebut | `1` (setelah lihat katalog) | "📦 KATALOG: KELING\n\n1. K10\n2. K12\n..." |
| 12 | Nama produk (setelah lihat katalog) | Rendah | Context `viewed_products` + nama produk match | Auto cek stok produk | Info stok produk | `K10` (setelah lihat katalog) | "📦 STOK PRODUK\n\n🔹 K10\n   Stok: 50 pcs" |
| 13 | Pesan tidak dikenali | Rendah | Tidak match dengan rule apapun | Tampilkan menu options | Menu + pesan error | `halo` atau `test` | "❌ Maaf, perintah tidak dikenali.\n\n💡 Silakan pilih menu berikut:..." |

---

## Tabel 2: Rules untuk Proses Pemesanan (Order State)

| No | State | Pattern/Keyword | Action | Response | Contoh Input | Contoh Output |
|----|-------|----------------|--------|----------|--------------|---------------|
| 1 | `waiting_order` | Format: `NAMA: ...\nNAMA PRODUK: ...\nWARNA PRODUK: ...\nJUMLAH PRODUK: ...` | Parse format, validasi stok | Konfirmasi pesanan atau error format | `NAMA: DARREN\nNAMA PRODUK: K10\nWARNA PRODUK: NKL\nJUMLAH PRODUK: 2 LUSIN` | Konfirmasi dengan detail stok |
| 2 | `waiting_order` | `"0"` atau `"BATAL"` | Batalkan pesanan | Pesan batal | `0` atau `batal` | "❌ Pesanan dibatalkan." |
| 3 | `waiting_order` | Format tidak lengkap | Error format | Pesan error + contoh format | `NAMA: DARREN` (tidak lengkap) | "❌ Format pesanan tidak sesuai!\n\n📦 Contoh untuk 1 produk:..." |
| 4 | `confirmation` | `"YA"`, `"BENAR"`, `"YES"`, `"OK"`, `"SETUJU"` | Simpan pesanan ke database | Success message dengan Order ID | `ya` | "✅ Pesanan Anda telah diterima!\n\n📋 Order ID: #123" |
| 5 | `confirmation` | `"0"`, `"TIDAK"`, `"SALAH"`, `"BATAL"`, `"NO"` | Batalkan pesanan | Pesan batal | `tidak` | "❌ Pesanan dibatalkan." |
| 6 | `confirmation` | Pesan tidak jelas | Minta konfirmasi ulang | Konfirmasi ulang | `mungkin` | "❓ Mohon konfirmasi:\n\nNAMA: DARREN\nPESANAN: ...\n\nKetik *YA* atau *BENAR* untuk mengirim" |

---

## Tabel 3: Rules untuk Normalisasi Nama Produk

| No | Pattern Input | Normalisasi | Contoh Input | Hasil Normalisasi |
|----|---------------|-------------|--------------|-------------------|
| 1 | `"keling 10"` atau `"k 10"` | `"K10"` | `keling 10` | `K10` |
| 2 | `"k10"` atau `"k-10"` atau `"k_10"` | `"K10"` | `k10` | `K10` |
| 3 | Nama produk lain | Uppercase + trim | `kancing 8` | `KANCING 8` |
| 4 | Nama dengan spasi ganda | Single space | `keling  10` | `KELING 10` |

---

## Tabel 4: Rules untuk Parsing Format Pesanan

| No | Format | Pattern Regex | Field yang Diekstrak | Contoh |
|----|--------|---------------|----------------------|--------|
| 1 | Format Baru | `/nama\s*:\s*(.+?)(?=\n|$)/i` | `nama` | `NAMA: DARREN` → `DARREN` |
| 2 | Format Baru | `/nama\s+produk\s*:\s*(.+?)(?=\n|$)/i` | `nama_produk[]` (array) | `NAMA PRODUK: K10, KC206` → `['K10', 'KC206']` |
| 3 | Format Baru | `/warna\s+produk\s*:\s*(.+?)(?=\n|$)/i` | `warna_produk[]` (array) | `WARNA PRODUK: NKL, ATG` → `['NKL', 'ATG']` |
| 4 | Format Baru | `/jumlah\s+produk\s*:\s*(.+?)(?=\n|$)/i` | `jumlah_produk[]` (array) | `JUMLAH PRODUK: 2 LUSIN, 1 GROSS` → `['2 LUSIN', '1 GROSS']` |
| 5 | Format Lama | `/nama\s*:\s*(.+?)(?:\n|pesanan|$)/i` | `nama` | `NAMA: DARREN\nPESANAN: ...` → `DARREN` |
| 6 | Format Lama | `/pesanan\s*:\s*(.+?)$/is` | `pesanan` | `PESANAN: 2 LUSIN K10 warna NKL` → `2 LUSIN K10 warna NKL` |

---

## Tabel 5: Rules untuk Validasi Stok Pesanan

| No | Kondisi | Validasi | Response | Contoh |
|----|---------|----------|----------|--------|
| 1 | Format lengkap dengan unit | Parse: `[quantity] [unit] [product] warna [color]` | Cek stok di `product_units` | `2 LUSIN K10 warna NKL` → Cek stok LUSIN untuk K10 warna NKL |
| 2 | Format tanpa unit | Parse: `[product] warna [color]` | Tampilkan pilihan unit yang tersedia | `K10 warna NKL` → "📦 Pilihan satuan: 1. LUSIN, 2. GROSS" |
| 3 | Format tanpa warna | Parse: `[product]` saja | Tampilkan pilihan warna yang tersedia | `K10` → "🎨 Warna yang tersedia: 1. NKL, 2. BN" |
| 4 | Stok cukup | `stock >= quantity` | ✅ OK, lanjut ke konfirmasi | Stok: 10 LUSIN, Pesanan: 2 LUSIN → ✅ |
| 5 | Stok tidak cukup | `stock < quantity` | ❌ Error dengan info stok tersedia | Stok: 5 LUSIN, Pesanan: 10 LUSIN → "⚠️ Stok tidak mencukupi!\nTersedia: 5 LUSIN" |
| 6 | Produk tidak ditemukan | Product tidak ada di database | ❌ Error dengan saran | `STOK XYZ` → "❌ Produk XYZ tidak ditemukan.\n\nKetik *KATALOG* untuk melihat produk yang tersedia." |
| 7 | Warna tidak cocok | Product ada tapi warna tidak match | ❌ Error dengan daftar warna tersedia | `K10 warna MERAH` → "❌ Produk K10 dengan warna MERAH tidak ditemukan.\n\n🎨 Warna yang tersedia: NKL, BN" |

---

## Tabel 6: Rules untuk Duplicate Message Detection

| No | Kondisi | Action | Alasan |
|----|---------|--------|--------|
| 1 | Pesan numerik (0-9) | **SKIP** duplicate check | Command pendek, tidak mungkin duplicate |
| 2 | Command keyword pendek (`menu`, `katalog`, `stok`, `pesan`, `batal`, `cek`) | **SKIP** duplicate check | Command pendek, tidak mungkin duplicate |
| 3 | Pesan < 10 karakter | **SKIP** duplicate check | Command pendek, tidak mungkin duplicate |
| 4 | Pesan panjang (pesanan) | **CEK** duplicate dengan cache key | Pesanan bisa dikirim ulang oleh customer |
| 5 | Message ID dari webhook tersedia | Gunakan `message_id` sebagai cache key | Lebih akurat daripada hash message |
| 6 | Message ID tidak tersedia | Gunakan hash `md5(phone + message)` sebagai cache key | Fallback method |
| 7 | Cache key sudah ada (5 menit terakhir) | **IGNORE** pesan, return "duplicate" | Mencegah double processing |

---

## Tabel 7: Rules untuk Customer Verification

| No | Kondisi | Action | Response |
|----|---------|--------|----------|
| 1 | Nomor telepon **ADA** di tabel `customers` | ✅ **PROCESS** pesan | Lanjut ke proses chatbot |
| 2 | Nomor telepon **TIDAK ADA** di tabel `customers` | ❌ **IGNORE** pesan | Return `{"status": "ignored", "message": "Customer not registered"}` |
| 3 | Format nomor telepon | Normalisasi: `08xxx` → `628xxx` | Format konsisten untuk query database |

---

## Tabel 8: Rules untuk Auto-Send Menu

| No | Kondisi | Action | Response |
|----|---------|--------|----------|
| 1 | Menu belum dikirim hari ini | ✅ Kirim menu otomatis | Menu lengkap |
| 2 | Menu sudah dikirim hari ini | ❌ Skip (tidak kirim lagi) | Lanjut ke proses command |
| 3 | Cache key: `chatbot_menu_sent_{phone}_{date}` | Check cache | Expire: tengah malam (end of day) |

---

## Tabel 9: Rules untuk Context-Aware Response

| No | Context | Pattern | Action | Contoh |
|----|---------|---------|--------|--------|
| 1 | `catalog_state.viewing_types = true` | Angka (1-9) | Pilih jenis produk berdasarkan nomor | User lihat katalog → ketik `1` → Tampilkan produk jenis #1 |
| 2 | `last_context.action = 'viewed_products'` | Nama produk | Auto cek stok produk | User lihat produk K10 → ketik `K10` → Auto cek stok K10 |
| 3 | `order_state.step = 'waiting_order'` | Format pesanan | Parse dan validasi pesanan | User dalam proses pesan → kirim format → Parse pesanan |
| 4 | `order_state.step = 'confirmation'` | "YA" atau "TIDAK" | Konfirmasi atau batal pesanan | User konfirmasi → "YA" → Simpan pesanan |

---

## Tabel 10: Priority Order (Urutan Pengecekan Rules)

| Priority | Rule | Alasan |
|----------|------|--------|
| **1 (Tertinggi)** | Command `"0"` atau `"MENU"` | Reset state, harus diproses pertama |
| **2** | Customer verification | Cek nomor terdaftar sebelum proses apapun |
| **3** | Auto-send menu (jika belum hari ini) | Kirim menu sekali per hari |
| **4** | Order state check | Jika sedang proses pesanan, handle order step |
| **5** | Format pesanan (`NAMA:` atau `PESANAN:`) | Deteksi format pesanan sebelum command lain |
| **6** | Command keywords (`STOK`, `KATALOG`, `PESAN`, dll) | Command utama |
| **7** | Numeric commands (1-9) | Shortcut angka |
| **8** | Context-aware (setelah lihat katalog) | Deteksi berdasarkan context |
| **9** | Thank you keywords | Respon sopan |
| **10 (Terendah)** | Default (tidak dikenali) | Tampilkan menu + error message |

---

## Tabel 11: State Management (Cache Keys)

| Cache Key | Purpose | Expire Time | Contoh Value |
|-----------|---------|-------------|--------------|
| `order_state_{phone}` | Menyimpan state proses pesanan | 30 menit | `{'step': 'waiting_order', 'data': []}` |
| `catalog_state_{phone}` | Menyimpan state katalog (jenis yang dilihat) | 10 menit | `{'viewing_types': true, 'types': [1,2,3]}` |
| `last_context_{phone}` | Menyimpan context terakhir user | 10 menit | `{'action': 'viewed_products', 'type_id': 1}` |
| `chatbot_menu_sent_{phone}_{date}` | Flag menu sudah dikirim hari ini | End of day (tengah malam) | `true` |
| `whatsapp_msg_{hash}` | Duplicate message detection | 5 menit | `true` |
| `whatsapp_msg_id_{message_id}` | Duplicate message detection (lebih akurat) | 5 menit | `true` |

---

## Tabel 12: Error Handling Rules

| No | Error Type | Pattern | Response | Contoh |
|----|------------|---------|----------|--------|
| 1 | Format pesanan tidak lengkap | Missing `NAMA:` atau `PESANAN:` | Error + contoh format | "❌ Format pesanan tidak sesuai!\n\n📦 Contoh: ..." |
| 2 | Produk tidak ditemukan | Product tidak ada di database | Error + saran katalog | "❌ Produk K10 tidak ditemukan.\n\nKetik *KATALOG* untuk melihat produk yang tersedia." |
| 3 | Stok tidak cukup | `stock < quantity` | Error + info stok tersedia | "⚠️ Stok tidak mencukupi!\n\nTersedia: 5 LUSIN" |
| 4 | Satuan tidak tersedia | Unit tidak ada untuk produk | Error + daftar satuan tersedia | "❌ Satuan GROSS tidak tersedia.\n\n📦 Satuan yang tersedia: LUSIN, PAK" |
| 5 | Warna tidak cocok | Color tidak match dengan produk | Error + daftar warna tersedia | "❌ Warna MERAH tidak ditemukan.\n\n🎨 Warna yang tersedia: NKL, BN" |
| 6 | Command tidak dikenali | Tidak match dengan rule apapun | Error + menu options | "❌ Maaf, perintah tidak dikenali.\n\n💡 Silakan pilih menu berikut:..." |
| 7 | Duplicate message | Message sudah diproses 5 menit lalu | Ignore (tidak kirim response) | Return `{"status": "duplicate"}` |
| 8 | Customer tidak terdaftar | Nomor tidak ada di database | Ignore (tidak kirim response) | Return `{"status": "ignored"}` |

---

## Ringkasan: Karakteristik Rule-Based System

| Aspek | Penjelasan |
|-------|------------|
| **Pendekatan** | Pattern matching berdasarkan keyword dan regex |
| **Tidak Menggunakan** | AI, Machine Learning, atau Natural Language Processing |
| **Cara Kerja** | 1. Input customer → 2. Match dengan pattern → 3. Execute action → 4. Generate response |
| **State Management** | Menggunakan Laravel Cache untuk menyimpan state sementara |
| **Priority** | Rules dengan priority tinggi dicek terlebih dahulu |
| **Context-Aware** | Sistem mengingat context terakhir user (katalog, pesanan, dll) |
| **Error Handling** | Setiap error memberikan feedback yang jelas dengan saran |
| **Extensibility** | Mudah ditambah rule baru dengan menambah pattern di `processMessage()` |

---

**Catatan untuk Skripsi:**
- Tabel ini bisa dimasukkan ke **BAB IV (Implementasi)** atau **BAB III (Perancangan Sistem)**
- Tambahkan penjelasan bahwa sistem ini **BUKAN AI**, melainkan **rule-based** dengan pattern matching
- Bisa ditambahkan diagram flow yang menunjukkan urutan pengecekan rules
- Tabel 10 (Priority Order) sangat penting untuk menjelaskan alur logika sistem

