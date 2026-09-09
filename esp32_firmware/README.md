# Firmware ESP32 - SmartDustbin 🗑️⚡

Folder ini mengandungi kod sumber Arduino/C++ untuk mikropengawal **ESP32** bagi projek **SmartDustbin (SK Kelatuan Papar)**.

---

## 📁 Struktur Fail
- `esp32_firmware.ino`: Lakaran utama (*main Arduino sketch*) untuk membaca sensor ultrasonik HC-SR04 dan menghantar data ke pelayan awan melalui REST API.

> [!NOTE]
> Fail sketch Arduino `.ino` dinamakan sama seperti nama folder (`esp32_firmware/esp32_firmware.ino`) bagi membolehkan fail dibuka secara terus menggunakan **Arduino IDE** tanpa mesej pemindahan folder.

---

## 🔌 Litar & Pendawaian (Wiring Diagram)

| Sensor HC-SR04 | ESP32 Pin | Fungsi |
| :--- | :--- | :--- |
| **VCC** | **VIN / 5V** | Bekalan kuasa sensor (5V) |
| **GND** | **GND** | Bumi sepunya |
| **TRIG** | **GPIO 5** | Isyarat pemicu gelombang ultrasonik |
| **ECHO** | **GPIO 18** | Isyarat penerima pantulan gelombang |

---

## ⚙️ Keperluan Perisian (Software Requirements)
1. **Arduino IDE** (v1.8.x atau v2.x)
2. **Pakej Board ESP32:**
   - URL Pengurus Papan: `https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json`
   - Pasang versi terkini `esp32 by Espressif Systems` melalui **Boards Manager**.
3. **Pustaka Terbina Dalam (*Built-in Libraries*):**
   - `<WiFi.h>`
   - `<HTTPClient.h>`
   - `<WiFiClientSecure.h>`
   *(Semua pustaka ini sudah tersedia secara automatik bersama pakej papan ESP32)*

---

## 🚀 Langkah Muat Naik (Flashing Instructions)
1. Buka fail `esp32_firmware.ino` menggunakan **Arduino IDE**.
2. Pergi ke **Tools > Board > esp32 > ESP32 Dev Module**.
3. Pilih **Port COM** yang sepadan dengan ESP32 anda di **Tools > Port**.
4. Kemaskini konfigurasi Wi-Fi anda pada baris kod:
   ```cpp
   const char* ssid     = "NAMA_WIFI_SEKOLAH_ATAU_RUMAH";
   const char* password = "KATA_LALUAN_WIFI";
   ```
5. Kemaskini sasaran pelayan API & Kunci Rahsia mengikut hos anda:
   ```cpp
   const char* serverUrl = "https://your-domain.com/tong/esp32.php";
   const char* apiKey    = "YOUR_SECRET_API_KEY";
   ```
6. Tekan butang **Upload** (ikon anak panah ke kanan) pada Arduino IDE.
7. Buka **Serial Monitor** pada kelajuan baud `115200` untuk memantau status sambungan Wi-Fi dan bacaan kapasiti sampah.
