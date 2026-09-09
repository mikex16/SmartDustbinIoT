<?php
/**
 * ============================================================================
 * PPRZ.net - Halaman Tetapan Sistem Tong Sampah Pintar ESP32 (Password Protected)
 * Sasaran: http://localhost/tong/settings.php & https://your-domain.com/tong/settings.php
 * ============================================================================
 */

require_once __DIR__ . '/config.php';

$config = get_tong_config();

// Kendalikan Log Keluar
if (isset($_GET['logout'])) {
    unset($_SESSION['sim_auth']);
    session_destroy();
    header("Location: settings.php");
    exit;
}

// Kendalikan Log Masuk
$auth_error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === $config['admin_password']) {
        $_SESSION['sim_auth'] = true;
        header("Location: settings.php");
        exit;
    } else {
        $auth_error = 'Kata laluan salah. Sila cuba lagi.';
    }
}

$is_authenticated = !empty($_SESSION['sim_auth']);

// ============================================================================
// JIKA BELUM LOG MASUK: PAPARKAN BORANG KATA LALUAN
// ============================================================================
if (!$is_authenticated):
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDustbin | Log Masuk Tetapan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans flex items-center justify-center p-4">
    <div class="max-w-sm w-full bg-slate-900 border border-slate-800 rounded-3xl p-7 shadow-2xl space-y-6 relative">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 mx-auto text-xl shadow-lg shadow-amber-500/10">
                <i class="fas fa-gear"></i>
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide">SmartDustbin Tetapan</h1>
            <p class="text-xs text-slate-400">Masukkan kata laluan pentadbir untuk mengubah konfigurasi tong pintar SK Kelatuan.</p>
        </div>

        <?php if ($auth_error): ?>
            <div class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-rose-400"></i>
                <span><?php echo htmlspecialchars($auth_error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="settings.php" class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Kata Laluan Pentadbir:</label>
                <input type="password" name="login_pass" required autofocus placeholder="Masukkan kata laluan..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-sm focus:outline-none focus:border-amber-500 transition placeholder:text-slate-600">
            </div>
            <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-amber-600/20 active:scale-95">
                Buka Tetapan
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
            <a href="index.php" class="hover:text-cyan-400 transition inline-flex items-center gap-1">
                <i class="fas fa-arrow-left text-[10px]"></i> Paparan PWA
            </a>
            <a href="simulate.php" class="hover:text-cyan-400 transition inline-flex items-center gap-1">
                Simulator <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</body>
</html>
<?php
exit;
endif;

// ============================================================================
// JIKA SUDAH LOG MASUK: KENDALIKAN SIMPANAN TETAPAN
// ============================================================================
$success_msg = '';
$error_msg = '';

// Kendalikan Reset ke Nilai Asal (Defaults)
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    $defaults = get_default_tong_config();
    save_tong_config($defaults);
    header("Location: settings.php?msg=reset_ok");
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] === 'reset_ok') {
    $success_msg = 'Semua tetapan telah dikembalikan kepada nilai asal.';
    $config = get_tong_config();
}

// Kendalikan Simpan Tetapan
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_settings'])) {
    $new_config = [
        'offline_threshold_minutes' => max(1, (int)($_POST['offline_threshold_minutes'] ?? 2)),
        'capacity_alert_percent'    => min(100, max(10, (float)($_POST['capacity_alert_percent'] ?? 80))),
        'capacity_warning_percent'  => min(99, max(5, (float)($_POST['capacity_warning_percent'] ?? 50))),
        'bin_height_cm'             => max(10, (float)($_POST['bin_height_cm'] ?? 40.0)),
        'default_device_id'         => trim($_POST['default_device_id'] ?? 'Tong-01'),
        'default_location'          => trim($_POST['default_location'] ?? 'Kantin Sekolah'),
        'admin_password'            => !empty($_POST['admin_password']) ? trim($_POST['admin_password']) : $config['admin_password'],
        'auto_alarm_sound'          => isset($_POST['auto_alarm_sound']),
        'api_key'                   => trim($_POST['api_key'] ?? 'YOUR_SECRET_API_KEY')
    ];

    $config = save_tong_config($new_config);
    $success_msg = 'Tetapan berjaya disimpan dan dikemas kini ke seluruh sistem!';
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDustbin | Tetapan Sistem</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased selection:bg-amber-500 selection:text-white pb-12">

    <!-- Header Atas Tetapan -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-30">
        <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="about.php" class="flex items-center space-x-2.5 group hover:opacity-95 transition" title="Info Inovasi SK Kelatuan">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xs group-hover:border-amber-400/60 transition">
                    <i class="fas fa-gear"></i>
                </div>
                <div>
                    <h1 class="text-xs font-bold text-white tracking-wide group-hover:text-amber-400 transition">SmartDustbin Tetapan</h1>
                    <p class="text-[10px] text-slate-400">Konfigurasi Parameter SK Kelatuan</p>
                </div>
            </a>

            <div class="flex items-center space-x-2 text-xs">
                <a href="about.php" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold transition flex items-center gap-1 shadow" title="Info Projek SK Kelatuan">
                    <i class="fas fa-circle-info text-[10px]"></i> <span>Info</span>
                </a>
                <a href="index.php" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition flex items-center gap-1 shadow">
                    <i class="fas fa-mobile-screen text-[10px]"></i> <span>PWA</span>
                </a>
                <a href="simulate.php" class="px-2.5 py-1 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white font-semibold transition flex items-center gap-1 shadow">
                    <i class="fas fa-sliders text-[10px]"></i> <span>Simulator</span>
                </a>
                <a href="settings.php?logout=1" title="Log Keluar" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-slate-700 transition flex items-center justify-center text-xs">
                    <i class="fas fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6 space-y-6">

        <!-- Notifikasi Berjaya / Ralat -->
        <?php if ($success_msg): ?>
            <div class="p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between shadow-lg">
                <div class="flex items-center space-x-2.5">
                    <div class="w-6 h-6 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="font-medium"><?php echo htmlspecialchars($success_msg); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white text-xs">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Borang Konfigurasi Utama -->
        <form method="POST" action="settings.php" class="space-y-6">
            <input type="hidden" name="save_settings" value="1">

            <!-- ============================================================ -->
            <!-- 1. PENGESANAN SAMBUNGAN & OFFLINE                           -->
            <!-- ============================================================ -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
                <div class="flex items-center space-x-2.5 border-b border-slate-800/80 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-xs">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-white uppercase tracking-wider">Pengesanan Sambungan & Offline</h2>
                        <p class="text-[11px] text-slate-400">Tentukan bila peranti tong dikira terputus sambungan</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">
                            Berapa Minit Peranti Dikira Offline:
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="offline_threshold_minutes" min="1" max="120" step="1" 
                                   value="<?php echo (int)($config['offline_threshold_minutes'] ?? 2); ?>" 
                                   class="w-32 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono font-bold text-sm focus:outline-none focus:border-amber-500">
                            <span class="text-slate-400 font-medium">Minit (<?php echo (int)($config['offline_threshold_minutes'] ?? 2) * 60; ?> saat)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                            💡 <em>Sekiranya modul ESP32 tidak menghantar sebarang data melebihi tempoh ini, pelayan akan menukar statusnya kepada <strong>OFFLINE</strong> serta memaparkan amaran masa terputus pada PWA dan Simulator.</em>
                        </p>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 2. AMBANG KAPASITI & AMARAN PARAS TONG                      -->
            <!-- ============================================================ -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
                <div class="flex items-center space-x-2.5 border-b border-slate-800/80 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 text-xs">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-white uppercase tracking-wider">Ambang Amaran & Paras Tong</h2>
                        <p class="text-[11px] text-slate-400">Kawal paras peratusan amaran dan saiz tong sampah</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">
                            Ambang Amaran Penuh (Alarm Merah):
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="capacity_alert_percent" min="50" max="100" step="1" 
                                   value="<?php echo (float)($config['capacity_alert_percent'] ?? 80); ?>" 
                                   class="w-28 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono font-bold text-sm focus:outline-none focus:border-amber-500">
                            <span class="text-rose-400 font-bold">% Kapasiti</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Status bertukar merah dan banner amaran muncul.</p>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">
                            Ambang Separuh Penuh (Amaran Kuning):
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="capacity_warning_percent" min="20" max="90" step="1" 
                                   value="<?php echo (float)($config['capacity_warning_percent'] ?? 50); ?>" 
                                   class="w-28 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono font-bold text-sm focus:outline-none focus:border-amber-500">
                            <span class="text-amber-400 font-bold">% Kapasiti</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Status bertukar warna kuning/oren.</p>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">
                            Ketinggian Fizikal Tong (cm):
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="bin_height_cm" min="10" max="300" step="0.5" 
                                   value="<?php echo (float)($config['bin_height_cm'] ?? 40.0); ?>" 
                                   class="w-28 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono font-bold text-sm focus:outline-none focus:border-amber-500">
                            <span class="text-slate-400">cm</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Jarak maksimum dari sensor ke dasar tong kosong.</p>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-950 border border-slate-800">
                        <div>
                            <span class="block text-white font-semibold text-xs">Bunyi Penggera Automatik</span>
                            <span class="text-[10px] text-slate-400">Bunyikan chime apabila tong penuh</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_alarm_sound" value="1" class="sr-only peer" <?php echo !empty($config['auto_alarm_sound']) ? 'checked' : ''; ?>>
                            <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 3. MAKLUMAT PERANTI & LOKASI LALAI                          -->
            <!-- ============================================================ -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
                <div class="flex items-center space-x-2.5 border-b border-slate-800/80 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-xs">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-white uppercase tracking-wider">Maklumat Peranti & Lokasi Lalai</h2>
                        <p class="text-[11px] text-slate-400">Nama paparan utama pada papan pemuka</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">ID Peranti Lalai:</label>
                        <input type="text" name="default_device_id" value="<?php echo htmlspecialchars($config['default_device_id'] ?? 'Tong-PPRZ-01'); ?>" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Lokasi Tong Lalai:</label>
                        <input type="text" name="default_location" value="<?php echo htmlspecialchars($config['default_location'] ?? 'PPRZ Papar'); ?>" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-amber-500">
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 4. KESELAMATAN & KATA LALUAN                                -->
            <!-- ============================================================ -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
                <div class="flex items-center space-x-2.5 border-b border-slate-800/80 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-xs">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-white uppercase tracking-wider">Keselamatan & Kunci Akses</h2>
                        <p class="text-[11px] text-slate-400">Tukar kata laluan untuk mengakses Tetapan dan Simulator</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Kata Laluan Pentadbir:</label>
                        <input type="text" name="admin_password" value="<?php echo htmlspecialchars($config['admin_password'] ?? 'admin123'); ?>" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-amber-300 font-mono font-bold focus:outline-none focus:border-amber-500">
                        <p class="text-[10px] text-slate-500 mt-1">Digunakan untuk membuka simulate.php dan settings.php</p>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">API Key ESP32:</label>
                        <input type="text" name="api_key" value="<?php echo htmlspecialchars($config['api_key'] ?? 'YOUR_SECRET_API_KEY'); ?>" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-cyan-300 font-mono focus:outline-none focus:border-amber-500">
                        <p class="text-[10px] text-slate-500 mt-1">Kunci rahsia penghantaran data dari mikropengawal</p>
                    </div>
                </div>
            </div>

            <!-- Butang Simpan & Reset -->
            <div class="flex items-center justify-between pt-2">
                <a href="settings.php?action=reset" onclick="return confirm('Adakah anda pasti ingin mengembalikan semua tetapan ke nilai asal?')" class="text-xs text-rose-400 hover:text-rose-300 transition font-medium">
                    <i class="fas fa-rotate-left mr-1"></i> Reset ke Asal
                </a>

                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-lg shadow-amber-600/25 active:scale-95">
                    <i class="fas fa-floppy-disk"></i>
                    <span>Simpan Semua Tetapan</span>
                </button>
            </div>
        </form>

    </main>

</body>
</html>
