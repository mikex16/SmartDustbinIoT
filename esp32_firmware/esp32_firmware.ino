/*
 * ==============================================================================
 * SmartDustbin - Firmware ESP32 Tong Sampah Pintar
 * Target API: https://your-domain.com/tong/esp32.php (atau http://<IP_PC>/tong/esp32.php)
 * ==============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>

// 1. Kredensial Wi-Fi
const char* ssid     = "NAMA_WIFI_ANDA";
const char* password = "KATA_LALUAN_WIFI";

// 2. URL Sasaran API (Gantikan dengan domain pelayan anda sendiri)
const char* serverUrl = "https://your-domain.com/tong/esp32.php";
// Jika guna lokal XAMPP (gantikan dengan IP PC anda):
// const char* serverUrl = "http://192.168.1.100/tong/esp32.php";

// 3. Kunci API Rahsia (Mesti sepadan dengan tetapan di web / config.php)
const char* apiKey    = "YOUR_SECRET_API_KEY";
const char* deviceId  = "Tong-01";
const char* lokasi    = "Kantin Sekolah";

// 4. Pin Sensor Ultrasonik HC-SR04
const int TRIG_PIN = 5;
const int ECHO_PIN = 18;

// Tetapan Dimensi Tong Sampah (dalam cm)
const float TINGGI_TONG_CM = 50.0; // Jarak sensor ke dasar tong kosong
const float JARAK_MIN_CM   = 5.0;  // Jarak minimum apabila tong penuh (dekat sensor)

unsigned long previousMillis = 0;
const long intervalHantar    = 30000; // Hantar bacaan setiap 30 saat (30000ms)

void setup() {
  Serial.begin(115200);
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  Serial.println("\n--- Memulakan Tong Sampah Pintar ESP32 (PPRZ.net) ---");
  WiFi.begin(ssid, password);

  Serial.print("Menyambung ke Wi-Fi");
  int cuba = 0;
  while (WiFi.status() != WL_CONNECTED && cuba < 20) {
    delay(500);
    Serial.print(".");
    cuba++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWi-Fi Berjaya Disambung!");
    Serial.print("IP ESP32: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nSambungan Wi-Fi Gagal. Sila semak SSID & Password.");
  }
}

float bacaJarakCM() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH, 30000); // Timeout 30ms
  if (duration == 0) {
    return -1.0; // Ralat bacaan sensor
  }
  return (duration * 0.0343) / 2.0;
}

void loop() {
  unsigned long currentMillis = millis();

  if (currentMillis - previousMillis >= intervalHantar) {
    previousMillis = currentMillis;

    float jarak = bacaJarakCM();
    if (jarak < 0) {
      Serial.println("[AMARAN] Sensor ultrasonik tiada tindak balas!");
      return;
    }

    // Kira peratusan kepenuhan tong sampah
    float parasSampah = TINGGI_TONG_CM - jarak;
    float peratusPenuh = (parasSampah / (TINGGI_TONG_CM - JARAK_MIN_CM)) * 100.0;

    if (peratusPenuh < 0)   peratusPenuh = 0;
    if (peratusPenuh > 100) peratusPenuh = 100;

    String statusTong = "Normal";
    if (peratusPenuh >= 90) {
      statusTong = "PENUH - Perlu Dikosongkan";
    } else if (peratusPenuh >= 70) {
      statusTong = "Hampir Penuh";
    } else if (peratusPenuh >= 40) {
      statusTong = "Separuh Penuh";
    }

    Serial.printf("Jarak: %.1f cm | Kapasiti: %.1f%% | Status: %s\n", jarak, peratusPenuh, statusTong.c_str());

    // Hantar data ke pelayan jika Wi-Fi bersambung
    if (WiFi.status() == WL_CONNECTED) {
      WiFiClientSecure client;
      client.setInsecure(); // Membolehkan HTTPS tanpa pengesahan sijil SSL yang rumit di ESP32

      HTTPClient http;
      http.begin(client, serverUrl);
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");

      String postData = "api_key=" + String(apiKey) +
                        "&device_id=" + String(deviceId) +
                        "&lokasi=" + String(lokasi) +
                        "&capacity=" + String(peratusPenuh, 1) +
                        "&jarak_cm=" + String(jarak, 1) +
                        "&status=" + statusTong;

      int httpResponseCode = http.POST(postData);

      if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Kod Respons HTTP: " + String(httpResponseCode));
        Serial.println("Respons Pelayan: " + response);
      } else {
        Serial.println("Ralat HTTP POST: " + String(httpResponseCode));
      }
      http.end();
    } else {
      Serial.println("Wi-Fi terputus. Mencuba sambung semula...");
      WiFi.reconnect();
    }
  }
}
