# REVISI 5: User Acceptance Testing (UAT) yang Benar

## 5.1 Pengertian UAT

**User Acceptance Testing (UAT)** adalah tahap pengujian di mana user/stakeholder yang sebenarnya menguji sistem untuk memverifikasi bahwa sistem sudah memenuhi kebutuhan bisnis dan siap untuk digunakan dalam lingkungan produksi.

**UAT BUKAN:**
- ❌ Pengujian teknis oleh developer (itu namanya Unit Testing atau Integration Testing)
- ❌ Checklist fitur yang ada
- ❌ Daftar fungsionalitas sistem

**UAT ADALAH:**
- ✅ Pengujian oleh user nyata (owner toko, kasir, customer)
- ✅ Skenario penggunaan real-world
- ✅ Validasi apakah sistem membantu bisnis
- ✅ Acceptance criteria: "Apakah sistem ini bisa dipakai dan membantu?"

## 5.2 Stakeholder yang Terlibat

### 5.2.1 Peserta UAT

| No | Stakeholder | Peran | Tanggung Jawab dalam UAT |
|----|-------------|-------|--------------------------|
| 1 | **Pemilik Toko** | End User, Decision Maker | Menguji fitur manajemen pesanan, laporan, dashboard |
| 2 | **Kasir** | End User, Daily Operator | Menguji fitur transaksi, stok, kasir |
| 3 | **Customer** | End User | Menguji fitur pemesanan WhatsApp |
| 4 | **Developer** | Observer, Support | Mencatat feedback, tidak menguji |

### 5.2.2 Profil Tester

**Tester 1: Pemilik Toko**
- Nama: Bapak Sutrisno (nama samaran)
- Jabatan: Pemilik Toko Trijaya
- Pengalaman: 15 tahun mengelola toko
- Keahlian IT: Dasar (bisa gunakan smartphone & WhatsApp)

**Tester 2: Kasir**
- Nama: Ibu Sari (nama samaran)
- Jabatan: Kasir Toko Trijaya
- Pengalaman: 3 tahun sebagai kasir
- Keahlian IT: Menengah (familiar dengan sistem POS sederhana)

**Tester 3: Customer**
- Nama: Bapak Agus (nama samaran)
- Jabatan: Pengusaha konveksi (pembeli reguler)
- Pengalaman: 5 tahun sebagai customer Toko Trijaya
- Keahlian IT: Dasar (aktif gunakan WhatsApp untuk bisnis)

## 5.3 Metodologi UAT

### 5.3.1 Pendekatan

Pendekatan yang digunakan: **Scenario-Based Testing**

**Langkah-langkah:**
1. Define skenario bisnis yang realistis
2. User execute skenario tanpa bantuan developer
3. User catat hasil, masalah, dan feedback
4. Developer perbaiki issue yang ditemukan
5. Re-test hingga user accept

### 5.3.2 Acceptance Criteria

Sistem dianggap **DITERIMA** jika:
- ✅ User bisa complete semua skenario bisnis tanpa error blocker
- ✅ User merasa sistem membantu pekerjaan mereka
- ✅ User bersedia menggunakan sistem di produksi
- ✅ Minimal 80% test case PASS
- ✅ Tidak ada bug critical/high yang belum fixed

## 5.4 Test Cases & Scenarios

### 5.4.1 Modul: Transaksi Offline (Tester: Kasir)

#### **TC-UAT-001: Transaksi Penjualan Cash - Happy Path**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-001 |
| **Modul** | Transaksi Offline |
| **Tester** | Ibu Sari (Kasir) |
| **Objective** | Memastikan kasir bisa membuat transaksi penjualan tunai dengan lancar |
| **Precondition** | • User sudah login sebagai kasir<br>• Ada produk dengan stok tersedia<br>• Nominal kembalian siap |
| **Test Data** | • Produk: K10 (NKL), 2 LUSIN<br>• Harga: Rp 25.000/LUSIN<br>• Customer: TOKO AGUS<br>• Payment: Cash, Rp 100.000 |

**Test Steps:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Kasir klik menu "Transaksi" | Halaman create transaksi terbuka |
| 2 | Kasir pilih filter "Jenis: KELING" | List produk jenis KELING ditampilkan |
| 3 | Kasir cari "K10" di search box | Produk K10 muncul di hasil pencarian |
| 4 | Kasir klik produk "K10 - LUSIN (NKL)" | Produk details ditampilkan (nama, stok, harga) |
| 5 | Kasir input quantity: 2 | Quantity field terisi "2" |
| 6 | Kasir klik "Tambah ke Cart" | Item masuk cart, subtotal: Rp 50.000 |
| 7 | Kasir klik "Lanjut ke Pembayaran" | Form pembayaran terbuka |
| 8 | Kasir input Customer Name: "TOKO AGUS" | Field customer terisi |
| 9 | Kasir pilih Payment Method: "CASH" | Field cash amount muncul |
| 10 | Kasir input Cash Amount: 100000 | Kembalian otomatis muncul: Rp 50.000 |
| 11 | Kasir pilih Status: "PAID" | Status terselect |
| 12 | Kasir klik "Simpan Transaksi" | Loading indicator muncul |
| 13 | System process transaksi | Redirect ke halaman nota |
| 14 | Kasir review nota | Nota tampil dengan data yang benar |
| 15 | Kasir klik "Print Nota" | Nota tercetak / PDF terdownload |

**Expected Final State:**
```
✅ Transaction created: status = PAID
✅ Stock K10 LUSIN: 10 → 8 (decreased by 2)
✅ CashFlow created: account = cash, amount = 50.000
✅ Inventory record created: quantity = -2, type = keluar
✅ Customer "TOKO AGUS" created (if new)
✅ Receipt generated successfully
```

**Actual Result:** 
- [ ] PASS
- [ ] FAIL (Reason: ________________)

**Tester Notes:**
```
_____________________________________________________
_____________________________________________________
```

**Tester Signature:** ______________ Date: __________

---

#### **TC-UAT-002: Transaksi Penjualan QRIS - Pre-Order**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-002 |
| **Modul** | Transaksi Offline |
| **Tester** | Ibu Sari (Kasir) |
| **Objective** | Kasir bisa buat nota pre-order (UNPAID) untuk customer yang pesan tapi belum bayar |
| **Precondition** | • User login<br>• Produk dengan stok cukup |
| **Test Data** | • Produk: KC206 (ATG), 1 GROSS<br>• Harga: Rp 300.000<br>• Payment: QRIS<br>• Status: UNPAID |

**Test Steps:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1-7 | (Sama seperti TC-UAT-001) | Items masuk cart |
| 8 | Kasir input Customer Name & Phone | Data terisi |
| 9 | Kasir pilih Payment: QRIS | QRIS terselect |
| 10 | Kasir pilih Status: **UNPAID** | Status terselect |
| 11 | Kasir klik "Simpan" | Success message muncul |

**Expected Final State:**
```
✅ Transaction created: status = UNPAID
❌ Stock TIDAK updated (karena belum bayar)
❌ CashFlow TIDAK created (karena belum bayar)
✅ Nota tersimpan sebagai "Hutang" / "Pre-Order"
```

**Later: Saat Customer Bayar**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Kasir buka transaksi UNPAID | Detail transaksi tampil |
| 2 | Customer bayar via QRIS | Payment confirmed |
| 3 | Kasir klik tombol "Bayar" | Popup konfirmasi muncul |
| 4 | Kasir konfirmasi | Status updated: UNPAID → PAID |
| 5 | System auto-process | Stock updated, CashFlow created |

**Actual Result:** 
- [ ] PASS
- [ ] FAIL (Reason: ________________)

**Tester Notes:**
```
_____________________________________________________
```

---

#### **TC-UAT-003: Stok Tidak Cukup**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-003 |
| **Modul** | Validasi Stok |
| **Tester** | Ibu Sari (Kasir) |
| **Objective** | System harus prevent kasir add item dengan quantity > stock |
| **Precondition** | • Produk K10 LUSIN: stock = 3 |
| **Test Data** | • Quantity request: 5 LUSIN (lebih dari stok) |

**Test Steps:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Kasir cari produk K10 LUSIN | Produk muncul, stock: 3 |
| 2 | Kasir input quantity: 5 | Quantity terisi "5" |
| 3 | Kasir klik "Tambah ke Cart" | ❌ Error message: "Stok tidak cukup! Tersedia: 3 LUSIN" |
| 4 | Item TIDAK masuk cart | Cart tetap kosong atau isi sebelumnya |

**Expected Final State:**
```
✅ Item NOT added to cart
✅ Error message displayed clearly
✅ Stock unchanged
```

**Actual Result:** 
- [ ] PASS
- [ ] FAIL

**Tester Notes:**
```
_____________________________________________________
```

---

### 5.4.2 Modul: Transaksi Online WhatsApp (Tester: Customer)

#### **TC-UAT-004: Pemesanan via WhatsApp - Happy Path**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-004 |
| **Modul** | Sistem Respons Otomatis WhatsApp |
| **Tester** | Bapak Agus (Customer) |
| **Objective** | Customer bisa pesan produk via WhatsApp tanpa bantuan owner |
| **Precondition** | • Customer punya nomor WhatsApp Toko<br>• Produk stok tersedia |
| **Test Data** | • Pesanan: 2 LUSIN K10 warna NKL |

**Test Steps:**

| Step | Customer Action | Expected System Response | Pass/Fail |
|------|----------------|--------------------------|-----------|
| 1 | Kirim pesan "Halo" ke WA Toko | System kirim Menu Utama dengan pilihan 1-5 | [ ] |
| 2 | Ketik "3" atau "PESAN" | System kirim format pesanan:<br>- NAMA:<br>- NAMA PRODUK:<br>- WARNA PRODUK:<br>- JUMLAH PRODUK: | [ ] |
| 3 | Kirim format:<br>NAMA: AGUS<br><br>NAMA PRODUK: K10<br>WARNA PRODUK: NKL<br>JUMLAH PRODUK: 2 LUSIN | System kirim konfirmasi:<br>- Nama: AGUS<br>- Detail: K10 (NKL) 2 LUSIN<br>- Stok tersedia: ✅<br>- Pertanyaan: "Data sudah benar?"<br>- Instruksi: Ketik YA/TIDAK | [ ] |
| 4 | Ketik "YA" | System kirim:<br>✅ "Pesanan diterima!"<br>📋 Order ID: #XXX<br>📦 Status: PENDING<br>Terimakasih pesan | [ ] |
| 5 | (Customer menunggu) | Owner terima notifikasi WA:<br>📦 PESANAN BARU<br>Nama: AGUS<br>Detail pesanan lengkap | [ ] |

**Acceptance Criteria:**
```
✅ Customer bisa pesan tanpa telepon/chat owner
✅ Format pesanan jelas dan mudah diikuti
✅ Validasi stok otomatis
✅ Konfirmasi jelas sebelum submit
✅ Owner langsung ternotifikasi
✅ Customer dapat Order ID untuk tracking
```

**Actual Result:** 
- [ ] ACCEPTED
- [ ] REJECTED (Reason: ________________)

**Customer Feedback:**
```
Apakah proses pemesanan mudah? (1-5): ___
Apakah instruksi sistem jelas? (1-5): ___
Apakah Anda akan gunakan fitur ini lagi? Ya / Tidak

Saran perbaikan:
_____________________________________________________
_____________________________________________________
```

**Tester Signature:** ______________ Date: __________

---

#### **TC-UAT-005: Cek Stok Produk via WhatsApp**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-005 |
| **Modul** | WhatsApp - Cek Stok |
| **Tester** | Bapak Agus (Customer) |
| **Objective** | Customer bisa cek stok produk tanpa hubungi owner |

**Test Steps:**

| Step | Customer Action | Expected System Response | Pass/Fail |
|------|----------------|--------------------------|-----------|
| 1 | Ketik "STOK K10" | System tampilkan:<br>📦 STOK PRODUK<br>🔹 K10<br>- Tipe: KELING<br>- Warna: NKL<br>- Stok: XXX pcs<br>- Harga: Rp XXX per LUSIN | [ ] |
| 2 | Ketik "STOK XYZ123" (produk tidak ada) | System tampilkan:<br>❌ Produk XYZ123 tidak ditemukan<br>+ Menu navigasi | [ ] |
| 3 | Ketik "STOK" (tanpa nama produk) | System tampilkan:<br>❌ Format salah!<br>Contoh: STOK KELING atau STOK K10 | [ ] |

**Acceptance Criteria:**
```
✅ Customer dapat info stok real-time
✅ Respon cepat (< 3 detik)
✅ Info harga juga ditampilkan
✅ Error message jelas dengan contoh
```

**Actual Result:** 
- [ ] ACCEPTED
- [ ] REJECTED

**Customer Feedback:**
```
Apakah info stok membantu? (1-5): ___
Komentar:
_____________________________________________________
```

---

#### **TC-UAT-006: Batalkan Pesanan WhatsApp**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-006 |
| **Modul** | WhatsApp - Cancel Order |
| **Tester** | Bapak Agus (Customer) |
| **Objective** | Customer bisa batalkan pesanan sendiri via WhatsApp |
| **Precondition** | Customer punya pesanan dengan status PENDING/CONFIRMED |

**Test Steps:**

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Ketik "CEK PESANAN" atau "4" | System tampilkan list pesanan customer | [ ] |
| 2 | Lihat pesanan #123 (status: PENDING) | Info pesanan lengkap + tombol cancel:<br>"Ketik BATAL PESANAN 123 untuk membatalkan" | [ ] |
| 3 | Ketik "BATAL PESANAN 123" | System kirim:<br>✅ "Pesanan #123 berhasil dibatalkan" | [ ] |
| 4 | Owner terima notif | Owner dapat WA:<br>❌ PESANAN DIBATALKAN<br>Pesanan #123<br>Nama: AGUS<br>Detail... | [ ] |

**Acceptance Criteria:**
```
✅ Customer bisa lihat pesanan sendiri
✅ Customer bisa batalkan sendiri (tidak perlu chat owner)
✅ Konfirmasi pembatalan jelas
✅ Owner ternotifikasi saat pesanan dibatalkan
```

**Actual Result:** 
- [ ] ACCEPTED
- [ ] REJECTED

---

### 5.4.3 Modul: Dashboard Owner (Tester: Pemilik Toko)

#### **TC-UAT-007: Review & Proses Pesanan WhatsApp**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-007 |
| **Modul** | Dashboard Pesanan WhatsApp |
| **Tester** | Bapak Sutrisno (Owner) |
| **Objective** | Owner bisa review dan proses pesanan dari WhatsApp |
| **Precondition** | Ada pesanan WhatsApp dengan status PENDING |

**Test Steps:**

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Owner login ke dashboard web | Dashboard terbuka | [ ] |
| 2 | Owner klik menu "Pesanan WhatsApp" | List pesanan WhatsApp tampil | [ ] |
| 3 | Owner lihat pesanan #123 (PENDING) | Detail pesanan:<br>- Nama customer<br>- No. HP<br>- Waktu pesan<br>- Detail items<br>- Status: PENDING | [ ] |
| 4 | Owner klik "Proses Pesanan" | Sistem create transaksi:<br>- Status: UNPAID<br>- Link ke WA Order #123<br>Redirect ke halaman transaksi | [ ] |
| 5 | Owner review transaksi | Total amount sesuai<br>Items sesuai<br>Status: UNPAID | [ ] |
| 6 | (Menunggu customer bayar) | Status tetap UNPAID | [ ] |
| 7 | Customer kirim bukti transfer | Owner terima bukti via WA (manual) | [ ] |
| 8 | Owner verify payment | Payment confirmed | [ ] |
| 9 | Owner klik tombol "Bayar" | Popup konfirmasi: "Update status ke PAID?" | [ ] |
| 10 | Owner konfirmasi | System update:<br>- Transaction status → PAID<br>- Stock updated<br>- CashFlow created<br>- WA Order status → PROCESSED | [ ] |
| 11 | Owner klik "Kirim WhatsApp" | Nota digital terkirim ke customer | [ ] |
| 12 | Customer terima nota | Customer terima nota lengkap via WA | [ ] |

**Acceptance Criteria:**
```
✅ Owner bisa lihat semua pesanan WhatsApp
✅ Owner bisa approve/reject pesanan
✅ System auto-create transaksi dari pesanan
✅ Owner bisa update status payment dengan mudah
✅ Nota otomatis terkirim via WA
```

**Actual Result:** 
- [ ] ACCEPTED
- [ ] REJECTED

**Owner Feedback:**
```
Apakah dashboard mudah digunakan? (1-5): ___
Apakah fitur approve pesanan membantu? (1-5): ___

Saran:
_____________________________________________________
```

---

#### **TC-UAT-008: Lihat Laporan Harian**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-008 |
| **Modul** | Dashboard - Laporan |
| **Tester** | Bapak Sutrisno (Owner) |
| **Objective** | Owner bisa lihat laporan penjualan harian (offline + online) |

**Test Steps:**

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Owner klik menu "Dashboard" | Dashboard utama terbuka | [ ] |
| 2 | Owner lihat widget "Penjualan Hari Ini" | Tampil:<br>- Total penjualan (Rp)<br>- Jumlah transaksi<br>- Chart penjualan | [ ] |
| 3 | Owner klik "Lihat Detail Laporan" | Halaman laporan terbuka | [ ] |
| 4 | Owner lihat breakdown: | • Penjualan Cash: Rp XXX<br>• Penjualan Card: Rp XXX<br>• Penjualan QRIS: Rp XXX<br>• Total: Rp XXX | [ ] |
| 5 | Owner lihat "Saldo Kas Saat Ini" | Saldo kas tampil dengan benar | [ ] |
| 6 | Owner filter tanggal: "01/12/2025 - 16/12/2025" | Laporan berubah sesuai range tanggal | [ ] |

**Acceptance Criteria:**
```
✅ Laporan akurat (sesuai transaksi)
✅ Breakdown by payment method jelas
✅ Saldo kas terhitung otomatis
✅ Filter tanggal berfungsi
✅ Include transaksi offline + online
```

**Actual Result:** 
- [ ] ACCEPTED
- [ ] REJECTED

---

### 5.4.4 Modul: Inventory Management (Tester: Owner)

#### **TC-UAT-009: Cek Riwayat Stok (Inventory)**

| Item | Detail |
|------|--------|
| **Test Case ID** | TC-UAT-009 |
| **Modul** | Inventory |
| **Tester** | Bapak Sutrisno (Owner) |
| **Objective** | Owner bisa track riwayat keluar-masuk stok produk |

**Test Steps:**

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Owner klik menu "Inventory" | Halaman inventory terbuka | [ ] |
| 2 | Owner lihat riwayat K10 LUSIN | List perubahan stok:<br>- Tanggal<br>- Tipe (masuk/keluar)<br>- Quantity<br>- Keterangan<br>- User | [ ] |
| 3 | Owner filter: Tipe = "Keluar" | Hanya transaksi keluar yang tampil | [ ] |
| 4 | Owner klik detail inventory | Detail lengkap:<br>- Produk: K10<br>- Unit: LUSIN<br>- Qty: -2 (keluar)<br>- Keterangan: "Penjualan #TRX-XXX"<br>- Tanggal: ... | [ ] |

**Acceptance Criteria:**
```
✅ Riwayat stok tercatat lengkap
✅ Bisa track penjualan per produk
✅ Info user/kasir tercatat
✅ Filter berfungsi
```

**Actual Result:** 
- [ ] ACCEPTED
- [ ] REJECTED

---

## 5.5 UAT Summary Report

### 5.5.1 Test Execution Summary

**Testing Period:** __ / __ / 2025 - __ / __ / 2025

**Test Cases Executed:**

| Test Case ID | Module | Tester | Result | Priority |
|--------------|--------|--------|--------|----------|
| TC-UAT-001 | Transaksi Offline - Cash | Kasir | [ ] Pass<br>[ ] Fail | High |
| TC-UAT-002 | Transaksi Offline - Pre-Order | Kasir | [ ] Pass<br>[ ] Fail | High |
| TC-UAT-003 | Validasi Stok | Kasir | [ ] Pass<br>[ ] Fail | High |
| TC-UAT-004 | Pemesanan WhatsApp | Customer | [ ] Pass<br>[ ] Fail | High |
| TC-UAT-005 | Cek Stok WhatsApp | Customer | [ ] Pass<br>[ ] Fail | Medium |
| TC-UAT-006 | Cancel Order WhatsApp | Customer | [ ] Pass<br>[ ] Fail | Medium |
| TC-UAT-007 | Dashboard - Proses Order | Owner | [ ] Pass<br>[ ] Fail | High |
| TC-UAT-008 | Dashboard - Laporan | Owner | [ ] Pass<br>[ ] Fail | High |
| TC-UAT-009 | Inventory Tracking | Owner | [ ] Pass<br>[ ] Fail | Low |

**Total Test Cases:** 9
**Passed:** ___ / 9
**Failed:** ___ / 9
**Pass Rate:** ____%

### 5.5.2 Issues Found

| Issue ID | Severity | Module | Description | Status | Fixed By |
|----------|----------|--------|-------------|--------|----------|
| ISS-001 | High | ... | ... | [ ] Open<br>[ ] Fixed | ... |
| ISS-002 | Medium | ... | ... | [ ] Open<br>[ ] Fixed | ... |
| ISS-003 | Low | ... | ... | [ ] Open<br>[ ] Fixed | ... |

**Severity Levels:**
- **Critical:** System crash, data loss, security breach
- **High:** Fitur utama tidak berfungsi
- **Medium:** Fitur minor error, workaround available
- **Low:** Cosmetic, typo, minor UX issue

### 5.5.3 User Satisfaction Survey

**Skala Penilaian: 1 (Sangat Tidak Puas) - 5 (Sangat Puas)**

**A. Pemilik Toko (Owner)**

| No | Aspek | Rating (1-5) |
|----|-------|--------------|
| 1 | Kemudahan menggunakan dashboard | ___ |
| 2 | Kecepatan sistem | ___ |
| 3 | Kelengkapan fitur | ___ |
| 4 | Akurasi laporan | ___ |
| 5 | Notifikasi pesanan WhatsApp | ___ |
| 6 | Proses approve pesanan | ___ |
| **Total** | **_____ / 30** |

**Kesimpulan Owner:**
- [ ] TERIMA sistem ini untuk digunakan di produksi
- [ ] TOLAK, perlu perbaikan: ______________________

**B. Kasir**

| No | Aspek | Rating (1-5) |
|----|-------|--------------|
| 1 | Kemudahan buat transaksi | ___ |
| 2 | Cari produk (search/filter) | ___ |
| 3 | Proses pembayaran | ___ |
| 4 | Print nota | ___ |
| 5 | Kecepatan sistem | ___ |
| **Total** | **_____ / 25** |

**Kesimpulan Kasir:**
- [ ] Sistem lebih mudah dari proses manual
- [ ] Sistem sama dengan proses manual
- [ ] Sistem lebih sulit dari proses manual

**C. Customer**

| No | Aspek | Rating (1-5) |
|----|-------|--------------|
| 1 | Kemudahan pesan via WhatsApp | ___ |
| 2 | Kejelasan instruksi sistem | ___ |
| 3 | Kecepatan respon sistem | ___ |
| 4 | Fitur cek stok berguna | ___ |
| 5 | Akan gunakan lagi untuk order berikutnya | ___ |
| **Total** | **_____ / 25** |

**Kesimpulan Customer:**
- [ ] Lebih mudah dari telepon/chat manual
- [ ] Sama dengan cara lama
- [ ] Lebih sulit, prefer cara lama

## 5.6 Final UAT Decision

### 5.6.1 UAT Conclusion

**Overall Test Result:**

- **Total Test Cases:** 9
- **Passed:** ___
- **Failed:** ___
- **Pass Rate:** ____%

**Overall User Satisfaction:**

- **Owner:** ___ / 30 (___%)
- **Kasir:** ___ / 25 (___%)
- **Customer:** ___ / 25 (___%)
- **Average:** ____%

### 5.6.2 Acceptance Decision

**Decision Matrix:**

| Condition | Requirement | Status |
|-----------|-------------|--------|
| Pass Rate >= 80% | ✅ Required | [ ] MET<br>[ ] NOT MET |
| No Critical/High bugs | ✅ Required | [ ] MET<br>[ ] NOT MET |
| User Satisfaction >= 70% | ✅ Required | [ ] MET<br>[ ] NOT MET |
| All stakeholders agree | ✅ Required | [ ] MET<br>[ ] NOT MET |

**FINAL DECISION:**

- [ ] ✅ **ACCEPTED** - System ready for production
- [ ] ⚠️ **ACCEPTED WITH CONDITIONS** - Deploy with minor fixes
- [ ] ❌ **REJECTED** - Requires major rework

**Conditions (if conditional accept):**
1. _____________________________________________________
2. _____________________________________________________

**Signatures:**

**Owner (Bapak Sutrisno):**
Signature: ______________ Date: __________

**Kasir (Ibu Sari):**
Signature: ______________ Date: __________

**Customer Representative (Bapak Agus):**
Signature: ______________ Date: __________

**Developer (Michael Darren Sutawijaya):**
Signature: ______________ Date: __________

---

## 5.7 Perbedaan UAT vs Testing Lain

| Aspek | Unit Testing | Integration Testing | UAT |
|-------|--------------|---------------------|-----|
| **Dilakukan oleh** | Developer | Developer/QA | End User |
| **Fokus** | Individual function/method | Interaksi antar modul | Business requirement |
| **Environment** | Development | Staging | Production-like |
| **Data** | Mock/dummy data | Test data | Real/realistic data |
| **Tujuan** | Code correctness | Module integration | User acceptance |
| **Tools** | PHPUnit, Jest | Selenium, Postman | Manual testing |
| **Success Criteria** | All tests pass | Integration OK | User satisfied |
| **When** | During development | After integration | Before deployment |

**Contoh Perbedaan:**

**Unit Test:**
```php
// Test function normalizeProductName()
public function test_normalize_product_name()
{
    $service = new ChatbotService();
    
    $result = $service->normalizeProductName('keling 10');
    $this->assertEquals('K10', $result);
    
    $result = $service->normalizeProductName('k 10');
    $this->assertEquals('K10', $result);
}
```
→ Developer test apakah function bekerja benar

**UAT:**
```
Customer ketik: "STOK KELING 10"
Expected: System tampilkan stok K10

Result: ✅ Pass - Customer puas dengan fitur
```
→ User test apakah fitur berguna untuk bisnis

---

**Catatan untuk Skripsi:**
- UAT harus dilakukan oleh user nyata, BUKAN developer
- Sertakan screenshotFormulir UAT yang sudah diisi dan ditandatangani
- Jika UAT belum dilakukan, jadwalkan dan laksanakan dengan user
- Lampirkan formulir UAT di Appendix skripsi
- Feedback user (positif/negatif) harus dicatat dengan jujur
