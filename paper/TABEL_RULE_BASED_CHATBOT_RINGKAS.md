# Tabel Rules Sistem Respons Otomatis WhatsApp (Versi Ringkas untuk Skripsi)

## Tabel 1: Mapping Command ke Action

| No | Command/Pattern | Deteksi | Action | Contoh Input | Contoh Output |
|----|-----------------|---------|--------|--------------|---------------|
| 1 | `0` atau `MENU` | Exact match | Reset state, kirim menu | `0` | Menu lengkap dengan 5 opsi |
| 2 | `1` atau `KATALOG` | Contains | Tampilkan jenis produk | `1` | Daftar jenis produk (KELING, KANCING, dll) |
| 3 | `2` atau `STOK [produk]` | Contains "stok" | Cek stok produk | `STOK K10` | Info stok: nama, warna, jumlah, harga |
| 4 | `3` atau `PESAN` | Exact match | Mulai proses pesanan | `3` | Format pesanan |
| 5 | `4` atau `CEK PESANAN` | Contains | Tampilkan pesanan | `cek pesanan` | List 5 pesanan terakhir |
| 6 | `5` atau `FAQ` | Exact match | Tampilkan FAQ | `faq` | FAQ lengkap |
| 7 | Format: `NAMA: ...` | Regex | Parse pesanan | `NAMA: DARREN\nPESANAN: ...` | Konfirmasi pesanan |
| 8 | `BATAL` | Contains | Batalkan pesanan | `batal` | Pesan batal + menu |
| 9 | `TERIMAKASIH` | Contains | Respon sopan | `terimakasih` | "Sama-sama kak! 😊" + menu |
| 10 | Tidak dikenali | Default | Tampilkan menu | `halo` | Error + menu options |

## Tabel 2: Rules Proses Pemesanan

| State | Input | Action | Output |
|-------|-------|--------|--------|
| `waiting_order` | Format lengkap | Parse & validasi stok | Konfirmasi pesanan |
| `waiting_order` | Format tidak lengkap | Error | Pesan error + contoh format |
| `waiting_order` | `0` atau `BATAL` | Batalkan | Pesan batal |
| `confirmation` | `YA` atau `BENAR` | Simpan pesanan | Success + Order ID |
| `confirmation` | `TIDAK` atau `BATAL` | Batalkan | Pesan batal |
| `confirmation` | Tidak jelas | Minta ulang | Konfirmasi ulang |

## Tabel 3: Validasi Stok

| Kondisi | Validasi | Response |
|---------|----------|----------|
| Stok cukup | `stock >= quantity` | ✅ OK, lanjut konfirmasi |
| Stok tidak cukup | `stock < quantity` | ❌ Error + info stok tersedia |
| Produk tidak ditemukan | Product tidak ada | ❌ Error + saran katalog |
| Warna tidak cocok | Color tidak match | ❌ Error + daftar warna tersedia |
| Satuan tidak tersedia | Unit tidak ada | ❌ Error + daftar satuan tersedia |

## Tabel 4: Priority Order (Urutan Pengecekan)

| Priority | Rule | Alasan |
|----------|------|--------|
| 1 | Command `0` atau `MENU` | Reset state, prioritas tertinggi |
| 2 | Customer verification | Cek nomor terdaftar |
| 3 | Auto-send menu | Kirim menu sekali per hari |
| 4 | Order state check | Handle jika sedang proses pesanan |
| 5 | Format pesanan | Deteksi format sebelum command lain |
| 6 | Command keywords | Command utama (STOK, KATALOG, dll) |
| 7 | Numeric commands | Shortcut angka (1-9) |
| 8 | Context-aware | Deteksi berdasarkan context |
| 9 | Thank you | Respon sopan |
| 10 | Default | Error + menu options |

## Tabel 5: State Management

| Cache Key | Purpose | Expire |
|-----------|---------|--------|
| `order_state_{phone}` | State proses pesanan | 30 menit |
| `catalog_state_{phone}` | State katalog | 10 menit |
| `last_context_{phone}` | Context terakhir user | 10 menit |
| `chatbot_menu_sent_{phone}_{date}` | Flag menu sudah dikirim | End of day |
| `whatsapp_msg_{hash}` | Duplicate detection | 5 menit |

---

**Penjelasan untuk Skripsi:**

Sistem respons otomatis WhatsApp yang dikembangkan menggunakan pendekatan **berbasis aturan (rule-based approach)**, di mana setiap perintah customer dicocokkan dengan pola (pattern) tertentu menggunakan teknik pattern matching dan regular expression. Sistem ini **TIDAK menggunakan teknologi kecerdasan buatan (artificial intelligence)** atau pembelajaran mesin (machine learning), melainkan mengandalkan aturan-aturan yang sudah ditentukan sebelumnya.

**Cara Kerja:**
1. Input customer diterima melalui webhook
2. Sistem melakukan pattern matching dengan rules yang ada (dari priority tertinggi ke terendah)
3. Jika match, sistem menjalankan action yang sesuai
4. Sistem mengirim response yang sudah ditentukan sebelumnya
5. State disimpan di cache untuk context-aware response

**Keuntungan Rule-Based:**
- ✅ Predictable: Response selalu konsisten
- ✅ Mudah di-debug: Bisa trace rule mana yang terpilih
- ✅ Tidak perlu training data
- ✅ Cepat dan efisien
- ✅ Mudah ditambah rule baru

**Keterbatasan:**
- ❌ Tidak bisa memahami bahasa natural yang kompleks
- ❌ Tidak bisa belajar dari interaksi
- ❌ Perlu update manual jika ada rule baru

