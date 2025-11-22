# 📋 Panduan Cek Status Scheduler Laravel

## 🔍 Cara Mengecek Scheduler Berjalan

### 1. **Lihat Daftar Scheduled Tasks**
Jalankan command berikut di terminal Laragon atau command prompt:
```bash
php artisan schedule:list
```
Ini akan menampilkan semua task yang dijadwalkan beserta waktu eksekusinya.

### 2. **Test Command Secara Manual**
Test command laporan harian secara langsung:
```bash
php artisan report:daily-whatsapp
```
Jika berhasil, berarti command-nya sudah benar. Pastikan `OWNER_PHONE` sudah di-set di `.env`.

### 3. **Test Scheduler (Simulasi)**
Jalankan scheduler secara manual untuk melihat apa yang akan dieksekusi:
```bash
php artisan schedule:test
```
Ini akan menampilkan task mana yang akan dijalankan sekarang.

### 4. **Jalankan Scheduler Sekarang**
Untuk test eksekusi scheduler:
```bash
php artisan schedule:run
```
Ini akan menjalankan semua task yang seharusnya berjalan pada waktu saat ini.

### 5. **Cek Log**
Cek file log untuk melihat apakah scheduler sudah berjalan:
```
storage/logs/laravel.log
```
Cari kata kunci: `report:daily-whatsapp` atau `Laporan harian`

---

## ⚙️ Setup Cron Job (Agar Scheduler Berjalan Otomatis)

### **Untuk Linux/Mac:**
1. Buka terminal
2. Jalankan: `crontab -e`
3. Tambahkan baris berikut:
   ```bash
   * * * * * cd /path/to/toko_trijaya && php artisan schedule:run >> /dev/null 2>&1
   ```
   Ganti `/path/to/toko_trijaya` dengan path lengkap project Anda.
4. Simpan dan keluar (Ctrl+X, lalu Y, lalu Enter)

### **Untuk Windows (Laragon):**

#### **Opsi 1: Menggunakan Task Scheduler Windows**
1. Buka **Task Scheduler** (Windows + R, ketik `taskschd.msc`)
2. Klik **Create Basic Task**
3. Isi:
   - Name: `Laravel Scheduler`
   - Trigger: **Daily** atau **When the computer starts**
   - Action: **Start a program**
   - Program: Path ke `php.exe` (biasanya di `C:\laragon\bin\php\php-8.x.x\php.exe`)
   - Arguments: `artisan schedule:run`
   - Start in: `C:\laragon\www\toko_trijaya`
4. Set **Repeat task every: 1 minute** (atau sesuai kebutuhan)

#### **Opsi 2: Menggunakan Laragon Task Scheduler**
1. Buka Laragon
2. Klik menu **Tools** → **Task Scheduler**
3. Tambahkan task baru dengan konfigurasi:
   - Command: `php artisan schedule:run`
   - Working Directory: `C:\laragon\www\toko_trijaya`
   - Interval: Setiap 1 menit

---

## 🧪 Command Helper untuk Cek Status

Saya sudah membuat command helper. Jalankan:
```bash
php artisan scheduler:check
```
Ini akan menampilkan informasi lengkap tentang scheduler.

---

## ✅ Checklist Verifikasi

- [ ] Command `php artisan schedule:list` menampilkan task `report:daily-whatsapp`
- [ ] Command `php artisan report:daily-whatsapp` berhasil dijalankan manual
- [ ] Variabel `OWNER_PHONE` sudah di-set di `.env`
- [ ] Variabel `FONNTE_TOKEN` sudah di-set di `.env`
- [ ] Cron job atau Task Scheduler sudah di-setup
- [ ] Log menunjukkan scheduler berjalan (cek `storage/logs/laravel.log`)

---

## 🐛 Troubleshooting

### **Scheduler tidak berjalan:**
1. Pastikan cron job/Task Scheduler sudah di-setup dengan benar
2. Cek path PHP dan project sudah benar
3. Test dengan `php artisan schedule:run` secara manual
4. Cek log untuk error messages

### **Command tidak terdeteksi:**
1. Pastikan Anda berada di direktori project
2. Pastikan PHP sudah di PATH atau gunakan path lengkap
3. Di Laragon, gunakan terminal Laragon yang sudah include PHP

### **WhatsApp tidak terkirim:**
1. Cek `FONNTE_TOKEN` di `.env` sudah benar
2. Cek `OWNER_PHONE` di `.env` sudah benar (format: 08xx atau 628xx)
3. Test dengan command manual: `php artisan report:daily-whatsapp`
4. Cek log untuk detail error

---

## 📝 Catatan Penting

- Scheduler Laravel perlu dijalankan **setiap menit** oleh cron job
- Command `schedule:run` akan mengecek task mana yang harus dijalankan
- Task `report:daily-whatsapp` akan dijalankan otomatis setiap jam 17:00 WIB
- Pastikan server/komputer selalu menyala pada jam 17:00 agar laporan terkirim

