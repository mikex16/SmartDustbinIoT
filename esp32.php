<?php
/**
 * ============================================================================
 * SmartDustbin - Sistem Pemantauan Tong Sampah Pintar ESP32
 * Fail API Endpoint: esp32.php
 * Sasaran Pelayan: https://your-domain.com/tong/esp32.php & http://localhost/tong/esp32.php
 * ============================================================================
 */

// Tetapkan Zon Masa Rasmi Malaysia / Sabah (GMT+8)
date_default_timezone_set("Asia/Kuala_Lumpur");

// Header Respons API (CORS & JSON)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, HEAD");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-KEY");
header("Content-Type: application/json; charset=utf-8");

// Kendalikan pre-flight OPTIONS request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';
$app_config = get_tong_config();

$isLocalhost = (php_sapi_name() === 'cli') || 
               in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || 
               strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
               strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;

// Tetapan API Key rahsia dari konfigurasi
$RAHSIA_API_KEY = $app_config['api_key'] ?? "YOUR_SECRET_API_KEY";

// Ambang Pengesanan Online / Offline dinamik dari Tetapan (Minit x 60)
$threshold_minutes = max(1, (int)($app_config['offline_threshold_minutes'] ?? 2));
define('HEARTBEAT_THRESHOLD_SECONDS', $threshold_minutes * 60);

// Laluan fail log simpanan teks tempatan
$LOG_FILE = __DIR__ . "/log_tong.txt";

// ----------------------------------------------------------------------------
// 2. FUNGSI SAMBUNGAN PANGKALAN DATA (MySQLi)
// ----------------------------------------------------------------------------
function get_database() {
    $conn = get_tong_db();
    if ($conn && !$conn->connect_error) {
        // Bina jadual automatik jika belum wujud
        $conn->query("
            CREATE TABLE IF NOT EXISTS `iot_tong_sampah` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `device_id` VARCHAR(50) NOT NULL DEFAULT 'Tong-01',
                `lokasi` VARCHAR(100) NOT NULL DEFAULT 'Kantin Sekolah',
                `kapasiti` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
                `jarak_cm` DECIMAL(6, 2) NOT NULL DEFAULT 0.00,
                `status` VARCHAR(50) NOT NULL DEFAULT 'Normal',
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`device_id`),
                INDEX (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        return $conn;
    }
    return null;
}

// ----------------------------------------------------------------------------
// 3. FUNGSI PENGIRAAN STATUS SAMBUNGAN TONG (ONLINE / OFFLINE)
// ----------------------------------------------------------------------------
function calculate_device_connection($created_at_str) {
    if (empty($created_at_str) || $created_at_str === '-') {
        return [
            "is_online" => false,
            "status" => "OFFLINE",
            "offline_since" => null,
            "offline_since_full" => null,
            "offline_duration" => null,
            "seconds_since_last_ping" => 999999,
            "message" => "Tiada rekod sambungan daripada peranti tong."
        ];
    }

    $last_time = strtotime($created_at_str);
    $now = time();
    $diff_seconds = max(0, $now - $last_time);
    $is_online = ($diff_seconds <= HEARTBEAT_THRESHOLD_SECONDS);

    // Format masa Malaysia / Sabah (cth: "07:30:15 PM")
    $time_str = date("h:i:s A", $last_time);
    $date_str = date("d/m/Y", $last_time);

    // Format tempoh masa mesra pengguna
    if ($diff_seconds < 60) {
        $duration_text = "$diff_seconds saat lalu";
    } elseif ($diff_seconds < 3600) {
        $mins = floor($diff_seconds / 60);
        $duration_text = "$mins minit lalu";
    } elseif ($diff_seconds < 86400) {
        $hours = floor($diff_seconds / 3600);
        $mins = floor(($diff_seconds % 3600) / 60);
        $duration_text = "$hours jam $mins minit lalu";
    } else {
        $days = floor($diff_seconds / 86400);
        $duration_text = "$days hari lalu";
    }

    return [
        "is_online" => $is_online,
        "status" => $is_online ? "ONLINE" : "OFFLINE",
        "threshold_seconds" => HEARTBEAT_THRESHOLD_SECONDS,
        "seconds_since_last_ping" => $diff_seconds,
        "last_seen_at" => $created_at_str,
        "last_seen_time" => $time_str,
        "last_seen_date" => $date_str,
        "offline_since" => !$is_online ? $time_str : null,
        "offline_since_full" => !$is_online ? "$time_str ($date_str)" : null,
        "offline_duration" => !$is_online ? $duration_text : null,
        "message" => $is_online 
            ? "Tong aktif bersambung ke pelayan (Kemaskini: $time_str)" 
            : "Tong terputus sambungan (Offline sejak jam $time_str, $duration_text)"
    ];
}

// ----------------------------------------------------------------------------
// 4. KENDALIKAN GET REQUEST (Untuk Semakan Dashboard, PWA & Pengesanan Sambungan)
// ----------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "GET" || $_SERVER["REQUEST_METHOD"] === "HEAD") {
    $conn = get_database();
    $latest = null;

    if ($conn) {
        $res = $conn->query("SELECT * FROM `iot_tong_sampah` ORDER BY `id` DESC LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $latest = $row;
        }
        $conn->close();
    }

    // Jika tiada dari database, cuba baca dari fail log
    if (!$latest && file_exists($LOG_FILE)) {
        $lines = array_filter(explode("\n", trim(file_get_contents($LOG_FILE))));
        if (!empty($lines)) {
            $last_line = end($lines);
            // Ekstrak masa daripada format log: "Masa: YYYY-MM-DD HH:MM:SS | ..."
            if (preg_match('/Masa:\s*([0-9\-:\s]+)\|/', $last_line, $m)) {
                $log_time = trim($m[1]);
            } else {
                $log_time = date("Y-m-d H:i:s", filemtime($LOG_FILE));
            }
            $latest = [
                "raw_log" => $last_line,
                "created_at" => $log_time,
                "status" => "Data Log",
                "kapasiti" => "0.00",
                "jarak_cm" => "0.00"
            ];
        }
    }

    // Kira status sambungan peranti ESP32 ke pelayan
    $connection = calculate_device_connection($latest['created_at'] ?? null);

    echo json_encode([
        "status" => "online",
        "service" => "SmartDustbin IoT API",
        "server" => $isLocalhost ? "localhost" : ($_SERVER['HTTP_HOST'] ?? "production-server"),
        "server_time" => date("Y-m-d H:i:s"),
        "device_connection" => $connection,
        "latest_data" => $latest ?? "Belum ada rekod data diterima."
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// ----------------------------------------------------------------------------
// 5. KENDALIKAN POST REQUEST (Penghantaran Data dari ESP32)
// ----------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Ambil API Key sama ada dari POST form-data atau Header X-API-KEY
    $api_key = $_POST["api_key"] ?? $_SERVER["HTTP_X_API_KEY"] ?? "";

    // Semak jika API Key wujud dan betul
    if ($api_key !== $RAHSIA_API_KEY) {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "Ralat: API Key tidak sah."
        ]);
        exit;
    }

    // Ambil data yang dihantar oleh ESP32
    $kapasiti  = isset($_POST["capacity"]) ? floatval($_POST["capacity"]) : (isset($_POST["peratus_penuh"]) ? floatval($_POST["peratus_penuh"]) : 0.0);
    $status    = isset($_POST["status"]) ? trim(strip_tags($_POST["status"])) : "Normal";
    $device_id = isset($_POST["device_id"]) ? trim(strip_tags($_POST["device_id"])) : ($app_config['default_device_id'] ?? "Tong-01");
    $lokasi    = isset($_POST["lokasi"]) ? trim(strip_tags($_POST["lokasi"])) : ($app_config['default_location'] ?? "Kantin Sekolah");
    $jarak_cm  = isset($_POST["jarak_cm"]) ? floatval($_POST["jarak_cm"]) : 0.0;

    $masa_sekarang = date("Y-m-d H:i:s");
    $ip_address = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";

    // 1. Simpan data ke dalam fail teks sebagai sandaran (log_tong.txt)
    $data_log = "Masa: $masa_sekarang | Device: $device_id | Kapasiti: {$kapasiti}% | Jarak: {$jarak_cm}cm | Status: $status | IP: $ip_address" . PHP_EOL;
    @file_put_contents($LOG_FILE, $data_log, FILE_APPEND | LOCK_EX);

    // 2. Simpan ke dalam MySQL Database (Konfigurasi sibpapar.pprz.net)
    $db_saved = false;
    $db_error = null;
    $record_id = null;

    $conn = get_database();
    if ($conn) {
        $stmt = $conn->prepare("INSERT INTO `iot_tong_sampah` (`device_id`, `lokasi`, `kapasiti`, `jarak_cm`, `status`, `ip_address`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssddsss", $device_id, $lokasi, $kapasiti, $jarak_cm, $status, $ip_address, $masa_sekarang);
            if ($stmt->execute()) {
                $db_saved = true;
                $record_id = $conn->insert_id;
            } else {
                $db_error = $stmt->error;
            }
            $stmt->close();
        } else {
            $db_error = $conn->error;
        }
        $conn->close();
    }

    // 3. Beri maklum balas berjaya kepada ESP32
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Berjaya: Data diterima.",
        "server" => $isLocalhost ? "localhost" : ($_SERVER['HTTP_HOST'] ?? "production-server"),
        "record_id" => $record_id,
        "db_saved" => $db_saved,
        "log_saved" => true,
        "connection" => [
            "status" => "ONLINE",
            "is_online" => true,
            "connected_at" => date("h:i:s A", strtotime($masa_sekarang))
        ],
        "data" => [
            "device_id" => $device_id,
            "lokasi" => $lokasi,
            "kapasiti" => $kapasiti,
            "jarak_cm" => $jarak_cm,
            "status" => $status,
            "timestamp" => $masa_sekarang
        ]
    ]);
    exit;
}

// Jika kaedah selain GET / POST / OPTIONS
http_response_code(405);
echo json_encode([
    "status" => "error",
    "message" => "Ralat: Hanya HTTP POST atau GET dibenarkan."
]);
