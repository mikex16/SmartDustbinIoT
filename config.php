<?php
/**
 * ============================================================================
 * SmartDustbin - Konfigurasi Berpusat (Central Configuration)
 * Sasaran: http://localhost/tong/ & https://your-domain.com/tong/
 * ============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set("Asia/Kuala_Lumpur");

define('CONFIG_FILE_PATH', __DIR__ . '/config.json');

/**
 * Konfigurasi asal (Default placeholder values untuk GitHub / Open Source)
 */
function get_default_tong_config() {
    return [
        'offline_threshold_minutes' => 2,                // Minit sebelum peranti dikira offline
        'capacity_alert_percent'    => 80,               // Peratus amaran merah / bahaya (alarm)
        'capacity_warning_percent'  => 50,               // Peratus amaran kuning / separuh penuh
        'bin_height_cm'             => 40.0,             // Ketinggian fizikal tong (cm)
        'default_device_id'         => 'Tong-01',
        'default_location'          => 'Kantin Sekolah',
        'admin_password'            => 'admin123',
        'auto_alarm_sound'          => true,
        'api_key'                   => 'YOUR_SECRET_API_KEY'
    ];
}

/**
 * Ambil konfigurasi semasa dari fail config.json (dengan sandaran default)
 */
function get_tong_config() {
    $defaults = get_default_tong_config();
    if (!file_exists(CONFIG_FILE_PATH)) {
        save_tong_config($defaults);
        return $defaults;
    }
    $raw = @file_get_contents(CONFIG_FILE_PATH);
    if (!$raw) {
        return $defaults;
    }
    $data = @json_decode($raw, true);
    if (!is_array($data)) {
        return $defaults;
    }
    return array_merge($defaults, $data);
}

/**
 * Simpan konfigurasi ke dalam fail config.json
 */
function save_tong_config($new_config) {
    $defaults = get_default_tong_config();
    $merged = array_merge($defaults, $new_config);
    
    // Pastikan jenis data yang betul
    $merged['offline_threshold_minutes'] = max(1, (int)($merged['offline_threshold_minutes'] ?? 2));
    $merged['capacity_alert_percent']    = min(100, max(10, (float)($merged['capacity_alert_percent'] ?? 80)));
    $merged['capacity_warning_percent']  = min(99, max(5, (float)($merged['capacity_warning_percent'] ?? 50)));
    $merged['bin_height_cm']             = max(10, (float)($merged['bin_height_cm'] ?? 40.0));
    $merged['default_device_id']         = trim($merged['default_device_id'] ?? 'Tong-01');
    $merged['default_location']          = trim($merged['default_location'] ?? 'Kantin Sekolah');
    $merged['admin_password']            = trim($merged['admin_password'] ?? 'admin123');
    $merged['auto_alarm_sound']          = (bool)($merged['auto_alarm_sound'] ?? true);
    $merged['api_key']                   = trim($merged['api_key'] ?? 'YOUR_SECRET_API_KEY');

    @file_put_contents(
        CONFIG_FILE_PATH, 
        json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 
        LOCK_EX
    );
    return $merged;
}

/**
 * Dapatkan sambungan pangkalan data MySQL
 * Menggunakan placeholder selamat untuk GitHub dan menyokong override dari config.local.php
 */
function get_tong_db() {
    $isLocalhost = (php_sapi_name() === 'cli') || 
                   in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || 
                   strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
                   strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;

    // Nilai lalai placeholder (selamat untuk GitHub / Open Source)
    $db_host = $isLocalhost ? '127.0.0.1' : 'localhost';
    $db_port = 3306;
    $db_user = $isLocalhost ? 'root' : 'YOUR_DB_USER';
    $db_pass = $isLocalhost ? '' : 'YOUR_DB_PASSWORD';
    $db_name = $isLocalhost ? 'smart_dustbin' : 'YOUR_DB_NAME';

    // Muat tetapan persendirian / produksi jika fail config.local.php wujud (diabaikan oleh git)
    if (file_exists(__DIR__ . '/config.local.php')) {
        include __DIR__ . '/config.local.php';
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
    if (!$conn->connect_error) {
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}
