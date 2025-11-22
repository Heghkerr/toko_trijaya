# 📱 Cara Akses Aplikasi dari HP/Tablet

## 🎯 Tujuan
Mengakses aplikasi Toko Trijaya yang berjalan di Laragon (localhost) dari HP atau Tablet di jaringan WiFi yang sama.

---

## 📋 Langkah-langkah

### **1. Cari IP Address Komputer Anda**

#### **Windows:**
1. Buka **Command Prompt** atau **PowerShell**
2. Ketik: `ipconfig`
3. Cari **IPv4 Address** di bagian **Wireless LAN adapter Wi-Fi** atau **Ethernet adapter**
4. Contoh: `192.168.1.100` atau `192.168.0.105`

#### **Atau cara cepat:**
- Klik kanan pada icon WiFi di taskbar
- Pilih **Properties**
- Scroll ke bawah, lihat **IPv4 address**

---

### **2. Pastikan Laragon Bisa Diakses dari Jaringan Lokal**

#### **Opsi A: Menggunakan IP Address (Paling Mudah)**

1. Buka **Laragon**
2. Klik kanan pada icon Laragon di system tray
3. Pilih **Menu** → **Preferences** atau **Settings**
4. Cari pengaturan **Allow external access** atau **Listen on all interfaces**
5. Atau edit file konfigurasi Apache/Nginx

#### **Opsi B: Edit Host File (Jika perlu)**

Jika Laragon tidak bisa diakses dari luar, edit file hosts:
- Lokasi: `C:\Windows\System32\drivers\etc\hosts`
- Tambahkan: `192.168.1.100 toko-trijaya.local` (ganti dengan IP Anda)

---

### **3. Akses dari HP/Tablet**

1. **Pastikan HP/Tablet terhubung ke WiFi yang sama** dengan komputer
2. Buka browser di HP/Tablet (Chrome, Safari, dll)
3. Ketik di address bar:
   ```
   http://192.168.1.100:8000
   ```
   atau
   ```
   http://192.168.1.100/toko_trijaya/public
   ```
   (Ganti `192.168.1.100` dengan IP address komputer Anda)

4. Jika menggunakan port khusus, sesuaikan:
   ```
   http://192.168.1.100:8080
   ```

---

### **4. Konfigurasi Laragon untuk External Access**

#### **Jika Laragon tidak bisa diakses dari luar:**

1. Buka **Laragon**
2. Klik **Menu** → **Apache** → **httpd.conf**
3. Cari baris:
   ```apache
   Listen 127.0.0.1:80
   ```
4. Ubah menjadi:
   ```apache
   Listen 0.0.0.0:80
   ```
5. Cari juga:
   ```apache
   <VirtualHost 127.0.0.1:80>
   ```
6. Ubah menjadi:
   ```apache
   <VirtualHost *:80>
   ```
7. Simpan dan restart Apache di Laragon

---

### **5. Konfigurasi Firewall Windows**

Jika masih tidak bisa diakses, mungkin firewall memblokir:

1. Buka **Windows Defender Firewall**
2. Klik **Advanced settings**
3. Klik **Inbound Rules** → **New Rule**
4. Pilih **Port** → **Next**
5. Pilih **TCP** → **Specific local ports**: `80` atau `8000` (sesuai port Anda)
6. Pilih **Allow the connection** → **Next**
7. Centang semua (Domain, Private, Public) → **Next**
8. Beri nama: "Laragon HTTP" → **Finish**

---

### **6. Test dari HP/Tablet**

1. Buka browser di HP
2. Ketik IP address komputer + path aplikasi
3. Contoh:
   - `http://192.168.1.100/toko_trijaya/public`
   - `http://192.168.1.100:8000` (jika pakai `php artisan serve`)

---

## 🔧 Alternatif: Menggunakan `php artisan serve`

Jika Laragon tidak bisa diakses dari luar, gunakan Laravel built-in server:

### **Di Command Prompt/PowerShell:**
```bash
cd C:\laragon\www\toko_trijaya
php artisan serve --host=0.0.0.0 --port=8000
```

### **Akses dari HP:**
```
http://192.168.1.100:8000
```
(Ganti dengan IP komputer Anda)

---

## 📱 Install PWA di HP

Setelah bisa akses dari HP:

1. Buka aplikasi di browser HP
2. Banner **Install** akan muncul
3. Klik **Install**
4. Aplikasi akan terinstall di home screen HP
5. Bisa digunakan seperti aplikasi native!

---

## 🐛 Troubleshooting

### **Tidak bisa akses dari HP:**
1. ✅ Pastikan HP dan komputer di WiFi yang sama
2. ✅ Cek IP address sudah benar
3. ✅ Pastikan firewall tidak memblokir
4. ✅ Cek Laragon sudah running
5. ✅ Coba akses dari komputer sendiri dulu: `http://localhost`

### **Error "Connection refused":**
- Laragon mungkin hanya listen di localhost
- Gunakan `php artisan serve --host=0.0.0.0`

### **Error "This site can't be reached":**
- Cek IP address sudah benar
- Pastikan port sudah benar (80, 8000, dll)
- Cek firewall Windows

### **PWA tidak bisa install:**
- Pastikan akses via HTTPS atau localhost
- Untuk production, perlu SSL certificate
- Untuk development, bisa pakai ngrok atau tunnel

---

## 🚀 Tips

1. **Gunakan IP Static** (opsional):
   - Set IP address komputer menjadi static agar tidak berubah
   - Buka Network Settings → Change adapter options → Properties → IPv4 → Set manual

2. **Gunakan Domain Lokal** (opsional):
   - Edit hosts file di HP (jika root/rooted)
   - Atau gunakan mDNS: `http://nama-komputer.local`

3. **Untuk Production:**
   - Deploy ke server/VPS
   - Atau gunakan ngrok untuk tunnel: `ngrok http 8000`

---

## ✅ Checklist

- [ ] IP address komputer sudah ditemukan
- [ ] HP/Tablet terhubung ke WiFi yang sama
- [ ] Laragon sudah running
- [ ] Firewall sudah dikonfigurasi
- [ ] Bisa akses dari browser HP
- [ ] PWA bisa di-install (opsional)

---

**Selamat! Sekarang aplikasi bisa diakses dari HP/Tablet! 📱**

