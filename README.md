# SmartDustbinIoT 🗑️⚡

> **Sistem Pemantauan Tong Sampah Pintar IoT & Inovasi Budaya Kitar Semula**  
> Dihasilkan dan dibangunkan oleh murid-murid **SK Kelatuan Papar, Sabah** bersama sokongan komuniti **PPRZ.net**.  
> 🌐 **Demo Live:** [https://api.pprz.net/tong/](https://api.pprz.net/tong/)

---

## 📖 Mengenai Projek (Project Overview)

**SmartDustbinIoT** merupakan sebuah projek inovasi cilik berimpak tinggi yang menggabungkan tiga (3) elemen utama:
1. **Budaya Kitar Semula & Upcycling:** Rangka tong sampah pintar dibina menggunakan bahan-bahan terbuang dan kitar semula oleh murid-murid SK Kelatuan.
2. **Internet of Things (IoT):** Dilengkapi mikropengawal **ESP32** dan sensor ultrasonik **HC-SR04** untuk mengesan paras kepenuhan sampah secara langsung (*real-time*).
3. **Pembangunan Perisian AI (Vibe Coding):** Pengaturcaraan bahagian hadapan (*Frontend PWA UI/UX*) dan bahagian belakang (*Backend REST API & DB*) dibangunkan secara inovatif berasaskan teknologi kecerdasan buatan (*AI Vibe Coding*).

---

## ✨ Ciri-Ciri Utama (Key Features)

- 📱 **Progressive Web App (PWA):** Antara muka *mobile-first* dengan reka bentuk *1-Screen Snap Viewport* yang kemas dan boleh dipasang (*installable*) terus ke skrin telefon pintar iOS & Android.
- 🎨 **Adaptive & Maskable Icons:** Mengikut piawaian PWA Android & iOS terkini dengan lambang rasmi SK Kelatuan Papar tanpa terpotong.
- 📶 **Pengesanan Sambungan & Status Offline:** Mengesan sekiranya peranti terputus talian (*offline*) dan memaparkan waktu serta tempoh data terakhir diterima melalui ikon ringkas.
- 🔔 **Sistem Notifikasi & Penggera Pintar:** Dilengkapi *Web Audio API alarm* berirama dan *Web Push Notifications* apabila kapasiti sampah mencapai ambang amaran (≥ 80%).
- 🎛️ **Simulator Interaktif (AJAX):** Halaman `simulate.php` dilindungi kata laluan untuk tujuan latihan amali sains murid di makmal sekolah tanpa perlu mengubah kod.
- ⚙️ **Panel Tetapan Sistem Dinamik:** Halaman `settings.php` untuk mengubah parameter ambang masa offline, peratus amaran, nama lokasi dan ID peranti secara dinamik.
- 📄 **Halaman Penceritaan Inovasi:** Halaman `about.php` yang memperincikan falsafah dan perjalanan murid membina projek ini dari bahan terbuang hingga ke era AI.

---

## 🛠️ Senibina & Susunan Fail (Architecture & File Structure)

```text
├── index.php              # Paparan utama PWA (Mobile-first monitor, 1-screen snap viewport)
├── about.php              # Halaman dokumentasi & penceritaan inovasi murid SK Kelatuan
├── simulate.php           # Simulator interaktif AJAX (Password protected)
├── settings.php           # Panel konfigurasi sistem & tetapan ambang (Password protected)
├── config.php             # Enjin konfigurasi JSON & fungsi sambungan DB
├── config.json            # Fail simpanan data konfigurasi (dinamik & terselamat)
├── config.local.php       # (Pilihan / Diabaikan Git) Konfigurasi DB peribadi pelayan anda
├── esp32.php              # REST API endpoint untuk mikropengawal ESP32 & log data
├── esp32_firmware/        # Folder kod sumber firmware mikropengawal ESP32
│   ├── esp32_firmware.ino # Lakaran Arduino C++ untuk membaca sensor ultrasonik HC-SR04
│   └── README.md          # Panduan flashing & konfigurasi litar perkakasan
├── log_tong.txt           # Sandaran fail log teks telemetri tong sampah
├── manifest.json          # Manifest PWA (name: SmartDustbin, tema, ikon)
├── sw.js                  # Service Worker untuk sokongan luar talian & caching
├── icon-192.png           # Ikon PWA standard 192x192
├── icon-512.png           # Ikon PWA standard 512x512
├── icon-maskable-192.png  # Ikon adaptif Android maskable 192x192
├── icon-maskable-512.png  # Ikon adaptif Android maskable 512x512
├── apple-touch-icon.png   # Ikon Apple iOS Home Screen 180x180
├── favicon.png            # Favicon pelayar desktop 64x64
├── deploy.ps1             # Skrip automasi muat naik FTP (Placeholder)
├── deploy.local.ps1       # (Pilihan / Diabaikan Git) Konfigurasi FTP peribadi
└── README.md              # Dokumentasi lengkap projek
```

---

## 🔌 Litar Perkakasan (Hardware Wiring)

Sambungan antara mikropengawal **ESP32** dan sensor ultrasonik **HC-SR04**:

| HC-SR04 Pin | ESP32 Pin | Catatan |
| :--- | :--- | :--- |
| **VCC** | **VIN / 5V** | Bekalan kuasa sensor |
| **GND** | **GND** | Bumi sepunya |
| **TRIG** | **GPIO 5** | Isyarat pemicu ultrasonik |
| **ECHO** | **GPIO 18** | Isyarat pantulan gelombang |

---

## 🚀 Panduan Pemasangan Tempatan (Local Setup)

1. Pastikan persekitaran web server seperti **XAMPP / Laragon** telah dipasang dengan PHP 8.0+ dan MySQL.
2. Letakkan fail ke dalam folder `C:\xampp\htdocs\tong\`.
3. Buka pelayar dan akses:
   - **PWA Utama:** `http://localhost/tong/index.php`
   - **Simulator:** `http://localhost/tong/simulate.php` *(Kata laluan lalai: `admin123`)*
   - **Info Inovasi:** `http://localhost/tong/about.php`
   - **Tetapan:** `http://localhost/tong/settings.php` *(Kata laluan lalai: `admin123`)*

---

## 📤 Pelayan Langsung & Deployment

- **Aplikasi Web:** `https://your-domain.com/tong/`
- **Endpoint API ESP32:** `https://your-domain.com/tong/esp32.php`
- **Konfigurasi Keselamatan:**
  - Anda boleh mencipta fail `config.local.php` di dalam direktori root projek untuk menetapkan konfigurasi sambungan MySQL pelayan anda sendiri (fail ini secara automatik diabaikan oleh `.gitignore` bagi memastikan maklumat sulit anda kekal selamat).
  - Untuk penggunaan skrip `deploy.ps1`, anda boleh menyediakan `deploy.local.ps1` yang mengandungi perincian akaun FTP hosting anda sendiri.
- Skrip muat naik automatik:
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\deploy.ps1
  ```

---

## 💡 Penghargaan & Hak Cipta

- **Inovator & Pembuat:** Murid-murid SK Kelatuan Papar, Sabah
- **Moto Sekolah:** *Berusaha Berilmu*
- **Sokongan Teknologi:** PPRZ.net Smart Business Network
- **Kaedah Pembangunan:** Human-in-the-loop AI Vibe Coding (2026)
