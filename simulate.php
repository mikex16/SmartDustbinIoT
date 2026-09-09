<?php
/**
 * ============================================================================
 * PPRZ.net - Simulator & Pengujian Tong Sampah Pintar ESP32
 * Ringkas, Padat, Mudah Digunakan & Dilindungi Kata Laluan
 * ============================================================================
 */

date_default_timezone_set("Asia/Kuala_Lumpur");

require_once __DIR__ . '/config.php';

$config = get_tong_config();

// Kendalikan Log Keluar
if (isset($_GET['logout'])) {
    unset($_SESSION['sim_auth']);
    session_destroy();
    header("Location: simulate.php");
    exit;
}

// Kendalikan Pengesahan Log Masuk
$auth_error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === ($config['admin_password'] ?? 'admin123')) {
        $_SESSION['sim_auth'] = true;
        header("Location: simulate.php");
        exit;
    } else {
        $auth_error = 'Kata laluan salah. Sila cuba lagi.';
    }
}

$is_authenticated = !empty($_SESSION['sim_auth']);

// Fungsi Mengambil Data Semasa dari Pangkalan Data
function get_simulate_data() {
    $conn = get_tong_db();
    $db_connected = false;
    $recent_records = [];
    $latest_data = null;

    if (!$conn->connect_error) {
        $db_connected = true;
        $conn->set_charset("utf8mb4");
        
        $conn->query("
            CREATE TABLE IF NOT EXISTS `iot_tong_sampah` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `device_id` VARCHAR(50) NOT NULL DEFAULT 'Tong-01',
                `lokasi` VARCHAR(100) NOT NULL DEFAULT 'PPRZ Papar',
                `kapasiti` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
                `jarak_cm` DECIMAL(6, 2) NOT NULL DEFAULT 0.00,
                `status` VARCHAR(50) NOT NULL DEFAULT 'Normal',
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`device_id`),
                INDEX (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $res = $conn->query("SELECT * FROM `iot_tong_sampah` ORDER BY `id` DESC LIMIT 10");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $recent_records[] = $row;
            }
        }
        if (!empty($recent_records)) {
            $latest_data = $recent_records[0];
        }
        $conn->close();
    }

    $kapasiti_semasa = $latest_data ? floatval($latest_data['kapasiti']) : 0;
    $status_semasa = $latest_data ? $latest_data['status'] : 'Menunggu Data...';
    $jarak_semasa = $latest_data ? floatval($latest_data['jarak_cm']) : 0;
    $masa_terkini = $latest_data ? $latest_data['created_at'] : '-';
    $lokasi_tong = $latest_data ? $latest_data['lokasi'] : 'PPRZ Papar';
    $device_id = $latest_data ? $latest_data['device_id'] : 'Tong-PPRZ-01';

    $is_online = false;
    $offline_time_str = '-';
    $offline_duration_str = '-';
    if ($masa_terkini && $masa_terkini !== '-') {
        $last_ts = strtotime($masa_terkini);
        $diff_seconds = max(0, time() - $last_ts);
        $is_online = ($diff_seconds <= 60);
        $offline_time_str = date("h:i:s A", $last_ts);
        if ($diff_seconds < 60) {
            $offline_duration_str = "$diff_seconds saat lalu";
        } elseif ($diff_seconds < 3600) {
            $offline_duration_str = floor($diff_seconds / 60) . " minit lalu";
        } elseif ($diff_seconds < 86400) {
            $offline_duration_str = floor($diff_seconds / 3600) . " jam lalu";
        } else {
            $offline_duration_str = floor($diff_seconds / 86400) . " hari lalu";
        }
    }

    return [
        'db_connected' => $db_connected,
        'recent_records' => $recent_records,
        'latest_data' => $latest_data,
        'kapasiti_semasa' => $kapasiti_semasa,
        'status_semasa' => $status_semasa,
        'jarak_semasa' => $jarak_semasa,
        'masa_terkini' => $masa_terkini,
        'lokasi_tong' => $lokasi_tong,
        'device_id' => $device_id,
        'is_online' => $is_online,
        'offline_time_str' => $offline_time_str,
        'offline_duration_str' => $offline_duration_str
    ];
}

// 2. Kendalikan Permintaan AJAX (Hanya Jika Berjaya Log Masuk)
if (isset($_GET['ajax'])) {
    header("Content-Type: application/json; charset=utf-8");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    if (!$is_authenticated) {
        http_response_code(401);
        echo json_encode(["status" => "unauthorized", "message" => "Sila log masuk dahulu."]);
        exit;
    }
    $data = get_simulate_data();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// JIKA BELUM LOG MASUK: PAPARKAN BORANG KATA LALUAN YANG KEMAS & MINIMALIS
// ============================================================================
if (!$is_authenticated):
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDustbin | Log Masuk Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans flex items-center justify-center p-4">
    <div class="max-w-sm w-full bg-slate-900 border border-slate-800 rounded-3xl p-7 shadow-2xl space-y-6 relative">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mx-auto text-xl shadow-lg shadow-cyan-500/10">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide">SmartDustbin Simulator</h1>
            <p class="text-xs text-slate-400">Masukkan kata laluan untuk mengakses alat simulasi tong sampah pintar SK Kelatuan.</p>
        </div>

        <?php if ($auth_error): ?>
            <div class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-rose-400"></i>
                <span><?php echo htmlspecialchars($auth_error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="simulate.php" class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Kata Laluan:</label>
                <input type="password" name="login_pass" required autofocus placeholder="Masukkan kata laluan..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-sm focus:outline-none focus:border-cyan-500 transition placeholder:text-slate-600">
            </div>
            <button type="submit" class="w-full py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-cyan-600/20 active:scale-95">
                Buka Simulator
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-800/80">
            <a href="index.php" class="text-xs text-slate-500 hover:text-cyan-400 transition inline-flex items-center gap-1.5 font-medium">
                <i class="fas fa-arrow-left text-[10px]"></i> Kembali ke Paparan PWA
            </a>
        </div>
    </div>
</body>
</html>
<?php
exit;
endif;

// ============================================================================
// JIKA BERJAYA LOG MASUK: PAPARKAN SIMULATOR RINGKAS & PADAT
// ============================================================================
$data = get_simulate_data();
extract($data);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDustbin | Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased selection:bg-cyan-500 selection:text-white pb-8">

    <!-- Header Ringkas -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="about.php" class="flex items-center space-x-2.5 group hover:opacity-95 transition" title="Info Inovasi SK Kelatuan">
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-xs group-hover:border-cyan-400/60 transition">
                    <i class="fas fa-sliders"></i>
                </div>
                <div>
                    <h1 class="text-xs font-bold text-white tracking-wide group-hover:text-cyan-400 transition">SmartDustbin Simulator</h1>
                    <p class="text-[10px] text-slate-400 font-mono" id="headerDevice"><?php echo htmlspecialchars($device_id); ?> &bull; <?php echo htmlspecialchars($lokasi_tong); ?></p>
                </div>
            </a>

            <div class="flex items-center space-x-2">
                <!-- Status Sambungan Mini -->
                <div class="flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700/60 text-xs">
                    <span id="connSvgHolder" class="flex items-center justify-center">
                        <?php if ($is_online): ?>
                            <svg class="w-3.5 h-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                        <?php else: ?>
                            <svg class="w-3.5 h-3.5 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                        <?php endif; ?>
                    </span>
                    <span id="connBadge" class="text-[10px] font-bold uppercase <?php echo $is_online ? 'text-emerald-400' : 'text-rose-400'; ?>">
                        <?php echo $is_online ? 'Online' : 'Offline'; ?>
                    </span>
                </div>

                <a href="about.php" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition flex items-center gap-1 shadow" title="Info Projek SK Kelatuan">
                    <i class="fas fa-circle-info text-[10px]"></i> <span>Info</span>
                </a>

                <a href="index.php" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition flex items-center gap-1 shadow">
                    <i class="fas fa-mobile-screen text-[10px]"></i> <span>PWA</span>
                </a>

                <a href="settings.php" class="px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs transition flex items-center gap-1 shadow">
                    <i class="fas fa-gear text-[10px]"></i> <span>Tetapan</span>
                </a>

                <a href="simulate.php?logout=1" title="Log Keluar" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-slate-700 transition flex items-center justify-center text-xs">
                    <i class="fas fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-5 space-y-5">

        <!-- KAD UTAMA: STATUS SEMASA & KAWALAN SIMULASI (All-In-One Compact) -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-5">
            
            <!-- Bahagian A: Paparan Status Semasa Terkini -->
            <div class="bg-slate-950/70 p-4 rounded-2xl border border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="text-center sm:text-left">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Kapasiti Terkini</span>
                        <div class="flex items-baseline space-x-2">
                            <span id="displayCapacity" class="text-4xl font-black text-white font-mono"><?php echo number_format($kapasiti_semasa, 1); ?><span class="text-xl text-slate-400">%</span></span>
                            <span id="displayStatus" class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase <?php echo $kapasiti_semasa >= 80 ? 'bg-rose-500/20 text-rose-300' : ($kapasiti_semasa >= 50 ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300'); ?>">
                                <?php echo htmlspecialchars($status_semasa); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Jalur Paras Bar & Metrik Ringkas -->
                <div class="w-full sm:w-1/2 space-y-2">
                    <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden border border-slate-700/50">
                        <div id="displayBar" class="h-full rounded-full transition-all duration-700 <?php echo $kapasiti_semasa >= 80 ? 'bg-gradient-to-r from-rose-500 to-red-600' : ($kapasiti_semasa >= 50 ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-emerald-400 to-teal-500'); ?>" style="width: <?php echo min(100, max(0, $kapasiti_semasa)); ?>%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                        <span>Jarak: <strong id="displayDistance" class="text-slate-200"><?php echo number_format($jarak_semasa, 1); ?> cm</strong></span>
                        <span>Masa: <strong id="displayTime" class="text-slate-200"><?php echo $offline_time_str; ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Bahagian B: Kawalan Pengujian Simulasi (Pantas & Ringkas) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-wand-magic-sparkles text-cyan-400"></i> Uji Paras Tong
                    </h2>
                    <span class="text-[10px] text-slate-500 font-mono">Kemaskini Segera (AJAX)</span>
                </div>

                <!-- 4 Butang Pratetap Pantas -->
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setPreset(15, 'Normal')" class="py-2 rounded-xl bg-slate-950 hover:bg-emerald-500/10 border border-slate-800 hover:border-emerald-500/40 text-slate-300 hover:text-emerald-300 text-xs font-semibold transition active:scale-95 text-center">
                        <span class="block text-[10px] text-slate-500 font-normal">Kosong</span> 15%
                    </button>
                    <button type="button" onclick="setPreset(50, 'Separuh Penuh')" class="py-2 rounded-xl bg-slate-950 hover:bg-amber-500/10 border border-slate-800 hover:border-amber-500/40 text-slate-300 hover:text-amber-300 text-xs font-semibold transition active:scale-95 text-center">
                        <span class="block text-[10px] text-slate-500 font-normal">Separuh</span> 50%
                    </button>
                    <button type="button" onclick="setPreset(85, 'PENUH - Perlu Dikosongkan')" class="py-2 rounded-xl bg-slate-950 hover:bg-rose-500/10 border border-slate-800 hover:border-rose-500/40 text-slate-300 hover:text-rose-300 text-xs font-semibold transition active:scale-95 text-center">
                        <span class="block text-[10px] text-slate-500 font-normal">Penuh</span> 85%
                    </button>
                    <button type="button" onclick="setPreset(98, 'PENUH - Perlu Dikosongkan')" class="py-2 rounded-xl bg-slate-950 hover:bg-red-600/15 border border-slate-800 hover:border-red-500/40 text-slate-300 hover:text-red-400 text-xs font-semibold transition active:scale-95 text-center">
                        <span class="block text-[10px] text-slate-500 font-normal">Kritikal</span> 98%
                    </button>
                </div>

                <!-- Input Slider & Nombor Kapasiti -->
                <div class="p-3.5 bg-slate-950/60 rounded-2xl border border-slate-800/80 space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="text-xs text-slate-400 block mb-1">Tarik Slider Kapasiti:</label>
                            <input type="range" id="simSlider" min="0" max="100" value="<?php echo (int)$kapasiti_semasa; ?>" class="w-full accent-cyan-500 cursor-pointer h-2 bg-slate-800 rounded-lg" oninput="onSliderChange(this.value)">
                        </div>
                        <div class="w-24 shrink-0">
                            <label class="text-[10px] text-slate-400 block mb-0.5 font-mono">Nilai %:</label>
                            <input type="number" id="simCapacity" min="0" max="100" step="0.5" value="<?php echo (float)$kapasiti_semasa; ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-2.5 py-1 text-white font-mono font-bold text-center text-sm focus:outline-none focus:border-cyan-500" oninput="onNumberChange(this.value)">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs pt-1 border-t border-slate-800/60">
                        <div>
                            <span class="text-[10px] text-slate-500 block">Status Ditugaskan:</span>
                            <input type="text" id="simStatus" value="<?php echo htmlspecialchars($status_semasa); ?>" class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2.5 py-1 text-slate-200 text-xs font-medium focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block">Jarak Sensor (cm):</span>
                            <input type="number" id="simDistance" step="0.1" value="<?php echo (float)$jarak_semasa; ?>" class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2.5 py-1 text-slate-200 text-xs font-mono focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                </div>

                <!-- Butang Hantar Ujian & Mesej Status -->
                <div class="flex items-center justify-between gap-3 pt-1">
                    <div id="simFeedback" class="text-xs text-slate-400 font-mono truncate">
                        Sedia untuk membuat simulasi.
                    </div>
                    <button type="button" id="btnSubmit" onclick="hantarSimulasi()" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-lg shadow-cyan-600/25 active:scale-95 shrink-0">
                        <i class="fas fa-paper-plane text-xs"></i>
                        <span>Hantar Ujian</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- KAD REKOD: 10 REKOD TERAKHIR (Ringkas & Bersih) -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-4 shadow-xl space-y-3">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fas fa-clock-rotate-left text-emerald-400"></i> Rekod Terkini
                </h3>
                <span id="recordCount" class="text-[10px] font-mono text-slate-400"><?php echo count($recent_records); ?> rekod</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-800/80">
                <table class="w-full text-left text-xs">
                    <thead class="text-slate-400 bg-slate-950 border-b border-slate-800 text-[10px] uppercase font-semibold font-mono">
                        <tr>
                            <th class="py-2 px-3">Masa</th>
                            <th class="py-2 px-3 text-center">Kapasiti</th>
                            <th class="py-2 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-800/50 font-mono text-xs">
                        <?php if (empty($recent_records)): ?>
                            <tr>
                                <td colspan="3" class="py-4 text-center text-slate-500 font-sans">Belum ada rekod.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_records as $r): ?>
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-2 px-3 text-slate-400"><?php echo $r['created_at']; ?></td>
                                    <td class="py-2 px-3 text-center font-bold <?php echo floatval($r['kapasiti']) >= 80 ? 'text-rose-400' : 'text-emerald-400'; ?>">
                                        <?php echo number_format($r['kapasiti'], 1); ?>%
                                    </td>
                                    <td class="py-2 px-3 text-slate-300 truncate max-w-xs"><?php echo htmlspecialchars($r['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
    const SVG_ONLINE = '<svg class="w-3.5 h-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';
    const SVG_OFFLINE = '<svg class="w-3.5 h-3.5 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';

    function calcDistance(cap) {
        return Math.max(3, Number((40 - (cap * 0.36)).toFixed(1)));
    }

    function calcStatus(cap) {
        if (cap >= 80) return 'PENUH - Perlu Dikosongkan';
        if (cap >= 50) return 'Separuh Penuh';
        return 'Normal';
    }

    function onSliderChange(val) {
        const num = parseFloat(val) || 0;
        document.getElementById('simCapacity').value = num;
        document.getElementById('simDistance').value = calcDistance(num);
        document.getElementById('simStatus').value = calcStatus(num);
    }

    function onNumberChange(val) {
        const num = Math.min(100, Math.max(0, parseFloat(val) || 0));
        document.getElementById('simSlider').value = num;
        document.getElementById('simDistance').value = calcDistance(num);
        document.getElementById('simStatus').value = calcStatus(num);
    }

    function setPreset(cap, status) {
        document.getElementById('simSlider').value = cap;
        document.getElementById('simCapacity').value = cap;
        document.getElementById('simDistance').value = calcDistance(cap);
        document.getElementById('simStatus').value = status || calcStatus(cap);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ------------------------------------------------------------------------
    // SISTEM KEMASKINI AJAX (SEMAKAN LATAR BELAKANG TANPA GANGGU INPUT)
    // ------------------------------------------------------------------------
    let isPolling = false;

    async function fetchDataAjax() {
        if (isPolling) return;
        isPolling = true;

        try {
            const res = await fetch('simulate.php?ajax=1&t=' + Date.now(), { cache: 'no-store' });
            if (res.status === 401) {
                window.location.reload();
                return;
            }
            if (!res.ok) return;

            const d = await res.json();
            const cap = parseFloat(d.kapasiti_semasa || 0);

            // Update Connection Header
            const svgHolder = document.getElementById('connSvgHolder');
            const badge = document.getElementById('connBadge');
            if (svgHolder) svgHolder.innerHTML = d.is_online ? SVG_ONLINE : SVG_OFFLINE;
            if (badge) {
                badge.className = 'text-[10px] font-bold uppercase ' + (d.is_online ? 'text-emerald-400' : 'text-rose-400');
                badge.textContent = d.is_online ? 'Online' : 'Offline';
            }

            // Update Status Semasa
            const dispCap = document.getElementById('displayCapacity');
            if (dispCap) dispCap.innerHTML = cap.toFixed(1) + '<span class="text-xl text-slate-400">%</span>';

            const dispStat = document.getElementById('displayStatus');
            if (dispStat) {
                dispStat.textContent = d.status_semasa;
                dispStat.className = 'text-xs px-2.5 py-0.5 rounded-full font-bold uppercase ' + 
                    (cap >= 80 ? 'bg-rose-500/20 text-rose-300' : (cap >= 50 ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300'));
            }

            const dispBar = document.getElementById('displayBar');
            if (dispBar) {
                dispBar.style.width = Math.min(100, Math.max(0, cap)) + '%';
                dispBar.className = 'h-full rounded-full transition-all duration-700 ' + 
                    (cap >= 80 ? 'bg-gradient-to-r from-rose-500 to-red-600' : (cap >= 50 ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-emerald-400 to-teal-500'));
            }

            const dispDist = document.getElementById('displayDistance');
            if (dispDist) dispDist.textContent = parseFloat(d.jarak_semasa || 0).toFixed(1) + ' cm';

            const dispTime = document.getElementById('displayTime');
            if (dispTime) dispTime.textContent = d.offline_time_str || '-';

            // Update Table
            const tbody = document.getElementById('tableBody');
            const countEl = document.getElementById('recordCount');
            if (countEl && d.recent_records) countEl.textContent = d.recent_records.length + ' rekod';

            if (tbody && Array.isArray(d.recent_records)) {
                if (d.recent_records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="py-4 text-center text-slate-500 font-sans">Belum ada rekod.</td></tr>';
                } else {
                    tbody.innerHTML = d.recent_records.map(r => {
                        const val = parseFloat(r.kapasiti || 0);
                        const clr = val >= 80 ? 'text-rose-400' : 'text-emerald-400';
                        return `<tr class="hover:bg-slate-800/30 transition">
                            <td class="py-2 px-3 text-slate-400">${escapeHtml(r.created_at)}</td>
                            <td class="py-2 px-3 text-center font-bold ${clr}">${val.toFixed(1)}%</td>
                            <td class="py-2 px-3 text-slate-300 truncate max-w-xs">${escapeHtml(r.status)}</td>
                        </tr>`;
                    }).join('');
                }
            }

        } catch (e) {
            console.log('Polling AJAX error:', e);
        } finally {
            isPolling = false;
        }
    }

    // Polling setiap 3 saat
    setInterval(fetchDataAjax, 3000);

    // ------------------------------------------------------------------------
    // PENGHANTARAN POST SIMULASI (AJAX - KEKALKAN INPUT PENGGUNA)
    // ------------------------------------------------------------------------
    async function hantarSimulasi() {
        const capacity = document.getElementById('simCapacity').value;
        const distance = document.getElementById('simDistance').value;
        const status = document.getElementById('simStatus').value;
        const feedback = document.getElementById('simFeedback');
        const btn = document.getElementById('btnSubmit');

        feedback.innerHTML = '<span class="text-cyan-400"><i class="fas fa-spinner fa-spin mr-1"></i> Menghantar data...</span>';
        if (btn) btn.disabled = true;

        const formData = new URLSearchParams();
        formData.append('api_key', <?php echo json_encode($config['api_key'] ?? 'YOUR_SECRET_API_KEY'); ?>);
        formData.append('capacity', capacity);
        formData.append('jarak_cm', distance);
        formData.append('status', status);
        formData.append('device_id', <?php echo json_encode($config['default_device_id'] ?? 'Tong-01'); ?>);
        formData.append('lokasi', <?php echo json_encode($config['default_location'] ?? 'Kantin Sekolah'); ?>);

        try {
            const res = await fetch('esp32.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });

            const data = await res.json();
            if (res.ok && data.status === 'success') {
                feedback.innerHTML = '<span class="text-emerald-400 font-semibold"><i class="fas fa-circle-check mr-1"></i> Berjaya dihantar! (' + capacity + '%)</span>';
                await fetchDataAjax();
            } else {
                feedback.innerHTML = '<span class="text-rose-400 font-semibold"><i class="fas fa-circle-xmark mr-1"></i> Ralat: ' + (data.message || res.statusText) + '</span>';
            }
        } catch (err) {
            feedback.innerHTML = '<span class="text-rose-400 font-semibold"><i class="fas fa-triangle-exclamation mr-1"></i> Gagal: ' + err.message + '</span>';
        } finally {
            if (btn) btn.disabled = false;
        }
    }
    </script>
</body>
</html>
