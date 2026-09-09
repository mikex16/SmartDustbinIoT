<?php
/**
 * ============================================================================
 * PPRZ.net - Aplikasi Web Progresif (PWA) Tong Sampah Pintar ESP32
 * Fail: index.php (Mobile-first, PWA Installable, Pengesanan Sambungan & Notifikasi)
 * ============================================================================
 */

date_default_timezone_set("Asia/Kuala_Lumpur");

require_once __DIR__ . '/config.php';
$app_config = get_tong_config();

$latest = null;
$conn = get_tong_db();
if ($conn && !$conn->connect_error) {
    $res = $conn->query("SELECT * FROM `iot_tong_sampah` ORDER BY `id` DESC LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $latest = $row;
    }
    $conn->close();
}

// Sandaran membaca fail log_tong.txt jika DB tiada rekod
if (!$latest && file_exists(__DIR__ . '/log_tong.txt')) {
    $lines = array_filter(array_map('trim', explode("\n", file_get_contents(__DIR__ . '/log_tong.txt'))));
    if (!empty($lines)) {
        $last_line = end($lines);
        if (preg_match('/Masa:\s*([0-9\-:\s]+)\|/', $last_line, $m)) {
            $log_time = trim($m[1]);
        } else {
            $log_time = date('Y-m-d H:i:s');
        }
        $latest = [
            'kapasiti' => '0',
            'status' => 'Data dari Log',
            'device_id' => $app_config['default_device_id'] ?? 'Tong-01',
            'lokasi' => $app_config['default_location'] ?? 'Kantin Sekolah',
            'jarak_cm' => '0',
            'created_at' => $log_time
        ];
    }
}

$kapasiti = $latest ? floatval($latest['kapasiti']) : 0;
$status   = $latest ? $latest['status'] : 'Menunggu Sambungan...';
$jarak    = $latest ? floatval($latest['jarak_cm']) : 0;
$device   = $latest ? $latest['device_id'] : ($app_config['default_device_id'] ?? 'Tong-PPRZ-01');
$lokasi   = $latest ? $latest['lokasi'] : ($app_config['default_location'] ?? 'PPRZ Papar');
$masa     = $latest ? $latest['created_at'] : '-';

// Ambang Pengesanan Sambungan Dinamik dari Tetapan (Minit x 60)
$threshold_minutes = max(1, (int)($app_config['offline_threshold_minutes'] ?? 2));
define('HEARTBEAT_THRESHOLD_SECONDS', $threshold_minutes * 60);
$alert_threshold = (float)($app_config['capacity_alert_percent'] ?? 80);
$warn_threshold = (float)($app_config['capacity_warning_percent'] ?? 50);

$diff_seconds = 999999;
$is_online = false;
$offline_time_str = '-';
$offline_date_str = '-';
$offline_duration_str = '-';

if ($masa && $masa !== '-') {
    $last_ts = strtotime($masa);
    $diff_seconds = max(0, time() - $last_ts);
    $is_online = ($diff_seconds <= HEARTBEAT_THRESHOLD_SECONDS);
    $offline_time_str = date("h:i:s A", $last_ts);
    $offline_date_str = date("d/m/Y", $last_ts);

    if ($diff_seconds < 60) {
        $offline_duration_str = "$diff_seconds saat lalu";
    } elseif ($diff_seconds < 3600) {
        $offline_duration_str = floor($diff_seconds / 60) . " minit lalu";
    } elseif ($diff_seconds < 86400) {
        $hours = floor($diff_seconds / 3600);
        $mins = floor(($diff_seconds % 3600) / 60);
        $offline_duration_str = "$hours jam $mins minit lalu";
    } else {
        $days = floor($diff_seconds / 86400);
        $offline_duration_str = "$days hari lalu";
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SmartDustbin | SK Kelatuan</title>

    <!-- Meta Tag PWA Mobile & Standalone -->
    <meta name="description" content="Sistem Pemantauan Tong Sampah Pintar ESP32 - SK Kelatuan Papar">
    <meta name="theme-color" content="#020617">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SmartDustbin">
    
    <!-- Pautan Manifest & Ikon -->
    <link rel="manifest" href="manifest.json?v=2">
    <link rel="icon" type="image/png" sizes="192x192" href="icon-192.png?v=2">
    <link rel="icon" type="image/png" sizes="64x64" href="favicon.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png?v=2">

    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS Snap Scroll */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .snap-viewport {
            height: 100dvh;
            height: 100vh;
            overflow-y: auto;
            scroll-snap-type: y mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
        .snap-screen {
            height: 100dvh;
            height: 100vh;
            scroll-snap-align: start;
            scroll-snap-stop: always;
        }
        .pulse-danger {
            box-shadow: 0 0 25px rgba(244, 63, 94, 0.45);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased select-none h-full">

    <!-- Bekas Snap Scroll -->
    <div class="snap-viewport">

        <!-- ================================================================ -->
        <!-- SKRIN 1: PAPARAN UTAMA (Muat dalam 1 Skrin Penuh Tanpa Perlu Scroll)-->
        <!-- ================================================================ -->
        <section class="snap-screen flex flex-col justify-between p-3.5 max-w-md mx-auto relative">

            <!-- Header Atas PWA -->
            <header class="bg-slate-900/90 backdrop-blur border-b border-slate-800 rounded-2xl px-3.5 py-2.5 shrink-0 shadow-lg">
                <div class="flex items-center justify-between">
                    <a href="about.php" class="flex items-center space-x-2.5 group hover:opacity-95 transition" title="Info Projek SmartDustbin SK Kelatuan">
                        <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700/80 flex items-center justify-center shadow-md shadow-slate-950 overflow-hidden shrink-0 group-hover:border-blue-500/60 transition">
                            <img src="favicon.png?v=2" alt="Logo" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h1 class="text-xs font-bold text-white tracking-wide group-hover:text-blue-400 transition">
                                SmartDustbin
                            </h1>
                            <p class="text-[10px] text-slate-400 font-mono flex items-center gap-1" id="headerSub">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo $is_online ? 'bg-emerald-400 animate-ping' : 'bg-rose-500'; ?>"></span>
                                <span><?php echo htmlspecialchars($device); ?></span>
                            </p>
                        </div>
                    </a>

                    <!-- Butang Tindakan Header (Icon Sambungan SVG, Notifikasi, Info, Simulator, Tetapan) -->
                    <div class="flex items-center space-x-1.5">
                        <!-- ICON SAMBUNGAN RINGKAS SVG: Tekan untuk lihat jam data terakhir -->
                        <button id="btnConnIcon" onclick="openConnModal()" title="Status Sambungan Tong (Klik untuk semak)" class="relative w-8 h-8 rounded-xl flex items-center justify-center transition border text-sm active:scale-95 shadow-sm <?php echo $is_online ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400' : 'bg-rose-500/15 border-rose-500/30 text-rose-400 animate-pulse'; ?>">
                            <span id="connSvgHolder" class="flex items-center justify-center">
                                <?php if ($is_online): ?>
                                    <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                                <?php else: ?>
                                    <svg class="w-4 h-4 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                                <?php endif; ?>
                            </span>
                            <span id="connHeaderDot" class="absolute -top-1 -right-1 w-2 h-2 rounded-full <?php echo $is_online ? 'bg-emerald-400 animate-ping' : 'bg-rose-500'; ?>"></span>
                        </button>

                        <!-- Butang Notifikasi -->
                        <button id="btnNotify" onclick="toggleNotificationPermission()" title="Tetapan Notifikasi" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700/60 transition text-xs flex items-center justify-center">
                            <i id="notifyIcon" class="fas fa-bell text-[11px]"></i>
                        </button>

                        <!-- Butang Info Projek Inovasi SK Kelatuan -->
                        <a href="about.php" title="Mengenai Inovasi SmartDustbin SK Kelatuan" class="w-8 h-8 rounded-xl bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 hover:text-blue-300 border border-blue-500/30 transition text-xs flex items-center justify-center shadow-sm">
                            <i class="fas fa-circle-info text-[11px]"></i>
                        </a>

                        <!-- Butang Simulator -->
                        <a href="simulate.php" title="Buka Simulator" class="w-8 h-8 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition flex items-center justify-center shadow-md shadow-cyan-600/20">
                            <i class="fas fa-sliders text-[11px]"></i>
                        </a>

                        <!-- Butang Tetapan Sistem -->
                        <a href="settings.php" title="Tetapan Sistem (Password)" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-amber-400 border border-slate-700/60 transition text-xs flex items-center justify-center">
                            <i class="fas fa-gear text-[11px]"></i>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Banner Amaran Penuh Kompak (Hanya Muncul Jika Kapasiti >= 80%) -->
            <div id="fullAlertBanner" class="<?php echo $kapasiti >= 80 ? '' : 'hidden'; ?> w-full mt-2 shrink-0">
                <div class="p-2 px-3 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 flex items-center justify-between pulse-danger">
                    <div class="flex items-center space-x-2 truncate">
                        <div class="w-6 h-6 rounded-lg bg-rose-500/20 flex items-center justify-center text-rose-400 shrink-0 animate-bounce">
                            <i class="fas fa-triangle-exclamation text-[10px]"></i>
                        </div>
                        <div class="truncate">
                            <h4 class="text-[11px] font-bold text-white leading-tight">Tong Hampir Penuh!</h4>
                            <p class="text-[10px] text-rose-200/90 truncate" id="alertBannerText">Kapasiti <?php echo number_format($kapasiti, 1); ?>%. Sila kosongkan.</p>
                        </div>
                    </div>
                    <button onclick="bunyikanAlarm()" class="ml-2 px-2 py-1 bg-rose-600 hover:bg-rose-500 text-white text-[10px] font-bold rounded-lg transition shrink-0">
                        <i class="fas fa-volume-high"></i>
                    </button>
                </div>
            </div>

            <!-- Badan Utama: Visual Tong Sampah Pintar & Metrik (Muat Sempurna 1-Skrin) -->
            <div class="flex-1 flex flex-col justify-center my-auto py-1">
                <div class="bg-slate-900/80 rounded-3xl p-4 border border-slate-800 shadow-2xl relative overflow-hidden backdrop-blur flex flex-col justify-between">
                    
                    <!-- Tajuk Lokasi & Pill Status -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="truncate mr-2">
                            <span class="text-[10px] text-slate-400 block font-medium flex items-center gap-1">
                                <i class="fas fa-location-dot text-rose-400 text-[10px]"></i>
                                <span id="displayLocation"><?php echo htmlspecialchars($lokasi); ?></span>
                            </span>
                        </div>
                        <!-- Pill Status (Boleh Tekan Buka Modal Sambungan) -->
                        <button onclick="openConnModal()" id="cardConnPill" class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition shrink-0 <?php echo $is_online ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30 animate-pulse'; ?>">
                            <span id="cardConnSvg" class="flex items-center justify-center">
                                <?php if ($is_online): ?>
                                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                                <?php else: ?>
                                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                                <?php endif; ?>
                            </span>
                            <span id="cardConnText"><?php echo $is_online ? 'Online' : 'Offline'; ?></span>
                        </button>
                    </div>

                    <!-- Visual Tong Sampah Pintar 3D (Dimensi Sesuai Satu Skrin Penuh) -->
                    <div class="flex justify-center my-2">
                        <div class="relative w-32 h-40 rounded-b-3xl rounded-t-md border-4 border-slate-700/80 bg-slate-950/90 overflow-hidden shadow-inner flex flex-col justify-end">
                            
                            <!-- Penutup Tong -->
                            <div class="absolute top-0 left-0 right-0 h-3 bg-slate-700 flex items-center justify-center border-b border-slate-600 z-10">
                                <div class="w-10 h-1 bg-slate-500 rounded-full"></div>
                            </div>

                            <!-- Paras Sampah Berwarna (Animated Liquid) -->
                            <div id="binFillLevel" class="w-full transition-all duration-1000 relative flex items-center justify-center <?php echo $kapasiti >= 80 ? 'bg-gradient-to-t from-rose-600 via-rose-500 to-red-400' : ($kapasiti >= 50 ? 'bg-gradient-to-t from-amber-600 via-amber-500 to-yellow-400' : 'bg-gradient-to-t from-emerald-600 via-emerald-500 to-teal-400'); ?>" style="height: <?php echo min(100, max(6, $kapasiti)); ?>%;">
                                <div class="absolute top-0 left-0 right-0 h-1.5 bg-white/25"></div>
                            </div>

                            <!-- Teks Peratusan di Tengah -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none drop-shadow-md z-20">
                                <span id="displayCapacity" class="text-3xl font-black text-white tracking-tight">
                                    <?php echo number_format($kapasiti, 1); ?><span class="text-base text-white/80">%</span>
                                </span>
                                <span id="badgeStatus" class="text-[9px] text-white/80 uppercase font-bold tracking-wider mt-0.5 px-1.5 py-0.2 bg-black/40 rounded">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </div>

                            <!-- Garisan Paras 25%, 50%, 75% -->
                            <div class="absolute left-2 right-2 top-1/4 border-b border-white/10 text-[8px] text-white/30 text-right pr-0.5">75%</div>
                            <div class="absolute left-2 right-2 top-2/4 border-b border-white/10 text-[8px] text-white/30 text-right pr-0.5">50%</div>
                            <div class="absolute left-2 right-2 top-3/4 border-b border-white/10 text-[8px] text-white/30 text-right pr-0.5">25%</div>
                        </div>
                    </div>

                    <!-- Jalur Metrik Pantas Bawah Tong -->
                    <div class="grid grid-cols-2 gap-2 pt-2.5 border-t border-slate-800/80 text-xs">
                        <div class="bg-slate-950/70 p-2 rounded-xl border border-slate-800 text-center">
                            <span class="text-slate-400 block text-[10px]">Jarak Sensor</span>
                            <span class="text-white font-mono font-bold text-xs" id="displayDistance"><?php echo number_format($jarak, 1); ?> cm</span>
                        </div>
                        <!-- Boleh ditekan untuk melihat dialog data terakhir -->
                        <div onclick="openConnModal()" class="bg-slate-950/70 p-2 rounded-xl border border-slate-800 text-center cursor-pointer hover:border-cyan-500/50 transition">
                            <span class="text-slate-400 block text-[10px]">Data Terakhir</span>
                            <span class="text-cyan-300 font-mono font-bold text-xs block truncate" id="displayTimestampShort"><?php echo $offline_time_str; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kawalan Pantas Bawah & Penunjuk Leret (Swipe Snap Indicator) -->
            <div class="shrink-0 space-y-1.5 pt-1 pb-1">
                <!-- Bar Butang Kawalan Mini -->
                <div class="flex items-center justify-between px-1">
                    <button onclick="bunyikanAlarm(true)" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-amber-300 font-semibold rounded-xl text-[11px] border border-slate-800 transition flex items-center gap-1.5">
                        <i class="fas fa-volume-high text-[10px]"></i>
                        <span>Uji Bunyi</span>
                    </button>

                    <button onclick="fetchLatestData()" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold rounded-xl text-[11px] border border-slate-800 transition flex items-center gap-1.5">
                        <i class="fas fa-arrows-rotate text-[10px]"></i>
                        <span id="pollStatusMini">Segar</span>
                    </button>
                </div>

                <!-- Petunjuk Snap Scroll ke Skrin 2 -->
                <div class="text-center">
                    <button onclick="document.getElementById('screen2').scrollIntoView({behavior:'smooth'})" class="text-[10px] text-slate-500 hover:text-cyan-400 font-medium transition inline-flex items-center gap-1">
                        <span>Leret ke atas untuk butiran lanjut</span>
                        <i class="fas fa-chevron-down animate-bounce text-[9px]"></i>
                    </button>
                </div>
            </div>

        </section>

        <!-- ================================================================ -->
        <!-- SKRIN 2: MAKLUMAT LANJUT & LOG SAMBUNGAN (Snap Scroll)            -->
        <!-- ================================================================ -->
        <section id="screen2" class="snap-screen flex flex-col justify-between p-4 max-w-md mx-auto relative bg-slate-950/80">
            
            <!-- Header Skrin 2 -->
            <div class="pt-2 pb-2 border-b border-slate-800 flex items-center justify-between shrink-0">
                <button onclick="document.querySelector('.snap-viewport').scrollTo({top:0,behavior:'smooth'})" class="text-xs text-cyan-400 hover:text-cyan-300 flex items-center gap-1 font-bold">
                    <i class="fas fa-arrow-up text-[10px]"></i>
                    <span>Kembali ke Utama</span>
                </button>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-mono">Skrin 2/2</span>
            </div>

            <!-- Kandungan Butiran Lanjut -->
            <div class="flex-1 overflow-y-auto py-3 space-y-3">
                
                <!-- Kad Status Sambungan Lengkap -->
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-white flex items-center gap-2">
                            <i class="fas fa-network-wired text-cyan-400"></i> Butiran Sambungan ESP32
                        </h3>
                        <span id="screen2StatusBadge" class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase <?php echo $is_online ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300'; ?>">
                            <?php echo $is_online ? 'ONLINE' : 'OFFLINE'; ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800/80">
                            <span class="text-slate-500 text-[10px] block">Masa Terakhir:</span>
                            <span class="text-white font-bold text-sm" id="screen2Time"><?php echo $offline_time_str; ?></span>
                        </div>
                        <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800/80">
                            <span class="text-slate-500 text-[10px] block">Tempoh:</span>
                            <span class="text-rose-300 font-bold text-xs truncate block" id="screen2Ago"><?php echo $offline_duration_str; ?></span>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-400 space-y-1 pt-1 border-t border-slate-800/60 font-sans">
                        <div class="flex justify-between">
                            <span>Tarikh Rekod:</span>
                            <span class="text-slate-200 font-mono" id="screen2Date"><?php echo $offline_date_str; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Ambang Terputus:</span>
                            <span class="text-slate-200 font-mono" id="screen2Threshold">&gt; <?php echo $threshold_minutes; ?> minit tanpa data</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Endpoint API:</span>
                            <span class="text-cyan-400 font-mono text-[10px]">esp32.php</span>
                        </div>
                    </div>
                </div>

                <!-- Kad Info Inovasi Murid SK Kelatuan -->
                <a href="about.php" class="p-3.5 rounded-2xl bg-gradient-to-r from-blue-950/60 via-slate-900 to-slate-900 border border-blue-500/30 flex items-center justify-between shadow-lg hover:border-blue-400/60 transition group">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/20 border border-blue-500/40 flex items-center justify-center text-blue-400 shrink-0 shadow-sm">
                            <i class="fas fa-recycle text-xs"></i>
                        </div>
                        <div>
                            <h5 class="text-xs font-bold text-white group-hover:text-blue-300 transition">Inovasi Murid SK Kelatuan</h5>
                            <p class="text-[10px] text-slate-400">Rangka Terbuang &bull; ESP32 IoT &bull; AI Vibe Coding</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-blue-400 font-bold flex items-center gap-1 group-hover:translate-x-0.5 transition">
                        Baca <i class="fas fa-chevron-right text-[9px]"></i>
                    </span>
                </a>

                <!-- Pasang PWA ke Skrin Telefon -->
                <div id="pwaInstallCard" class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-950/50 to-slate-900 border border-emerald-500/30 flex items-center justify-between shadow-lg">
                    <div class="flex items-center space-x-2.5">
                        <img src="icon-192.png" alt="Icon" class="w-8 h-8 rounded-lg shadow">
                        <div>
                            <h5 class="text-xs font-bold text-white">Pasang Aplikasi Mobile</h5>
                            <p class="text-[10px] text-slate-400">Akses terus seperti aplikasi telefon</p>
                        </div>
                    </div>
                    <button id="btnInstallPwa" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition">
                        Pasang
                    </button>
                </div>

                <!-- Panduan Sambungan -->
                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800 text-[11px] text-slate-400 space-y-1.5">
                    <span class="font-bold text-white block text-xs flex items-center gap-1.5">
                        <i class="fas fa-lightbulb text-amber-400"></i> Panduan Sambungan ESP32
                    </span>
                    <p>Sekiranya peranti berada dalam status <strong>OFFLINE</strong>:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[10px] text-slate-400">
                        <li>Pastikan modul ESP32 mendapat bekalan kuasa yang mencukupi.</li>
                        <li>Pastikan Wi-Fi tong berfungsi dan mempunyai capaian internet.</li>
                        <li>Tekan butang reset pada ESP32 untuk memulakan semula sambungan.</li>
                    </ul>
                </div>
            </div>

            <!-- Footer Skrin 2 -->
            <div class="text-center pt-2 pb-2 border-t border-slate-800/80 shrink-0">
                <p class="text-[10px] text-slate-500 font-medium">
                    PPRZ.net &bull; Smart Bussiness Network &bull; Papar, Sabah
                </p>
                <p class="text-[9px] text-slate-600">
                    PWA v1.3.0 &bull; 1-Screen Snap Viewport
                </p>
            </div>
        </section>
    </div>

    <!-- ==================================================================== -->
    <!-- MODAL RINGKAS INFO SAMBUNGAN (DIPAPARKAN APABILA ICON DITEKAN)       -->
    <!-- ==================================================================== -->
    <div id="connModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" onclick="closeConnModal(event)">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl relative space-y-4" onclick="event.stopPropagation()">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2.5">
                    <div id="modalStatusIconBg" class="w-9 h-9 rounded-xl flex items-center justify-center <?php echo $is_online ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400 animate-pulse'; ?>">
                        <span id="modalSvgHolder" class="flex items-center justify-center">
                            <?php if ($is_online): ?>
                                <svg class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                            <?php else: ?>
                                <svg class="w-5 h-5 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Status Sambungan Tong</h3>
                        <span id="modalStatusBadge" class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase <?php echo $is_online ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300'; ?>">
                            <?php echo $is_online ? 'ONLINE' : 'OFFLINE'; ?>
                        </span>
                    </div>
                </div>
                <button onclick="closeConnModalDirect()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition text-xs">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <!-- Butiran Jam Data Terakhir (Paling Jelas & Utama) -->
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800/80 space-y-3 text-center">
                <span class="text-[11px] text-slate-400 block uppercase tracking-wider font-semibold">Data Terakhir Diterima Pada:</span>
                <div>
                    <span id="modalTime" class="text-3xl font-black font-mono text-white block tracking-tight"><?php echo $offline_time_str; ?></span>
                    <span id="modalAgo" class="text-xs text-rose-300 font-medium block mt-1"><?php echo $offline_duration_str; ?></span>
                </div>
                <div class="text-[11px] text-slate-500 font-mono border-t border-slate-800/60 pt-2" id="modalDate">
                    Tarikh: <?php echo $offline_date_str; ?>
                </div>
            </div>

            <!-- Maklumat Tambahan Peranti -->
            <div class="bg-slate-950/50 p-3 rounded-2xl border border-slate-800/60 text-xs text-slate-400 space-y-1.5">
                <div class="flex justify-between">
                    <span>ID Peranti:</span>
                    <strong class="text-slate-200 font-mono" id="modalDevice"><?php echo htmlspecialchars($device); ?></strong>
                </div>
                <div class="flex justify-between">
                    <span>Lokasi:</span>
                    <strong class="text-slate-200" id="modalLoc"><?php echo htmlspecialchars($lokasi); ?></strong>
                </div>
                <div class="flex justify-between">
                    <span>Ambang Offline:</span>
                    <span class="text-slate-400 font-mono" id="modalThreshold">&gt; <?php echo $threshold_minutes; ?> minit tanpa data</span>
                </div>
            </div>

            <!-- Penerangan Ringkas -->
            <p id="modalDesc" class="text-[11px] text-slate-400 text-center leading-relaxed">
                <?php echo $is_online ? 'Peranti aktif berkomunikasi secara normal dengan pelayan.' : 'Peranti tidak menghantar isyarat melebihi ' . $threshold_minutes . ' minit. Sila semak bekalan kuasa atau Wi-Fi tong.'; ?>
            </p>

            <button onclick="closeConnModalDirect()" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition">
                Tutup
            </button>
        </div>
    </div>

    <!-- Skrip JavaScript PWA, Pengesanan Sambungan & Notifikasi -->
    <script>
    // ------------------------------------------------------------------------
    // 1. Pendaftaran Service Worker (PWA Offline & Install)
    // ------------------------------------------------------------------------
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
                .then(reg => console.log('PWA Service Worker aktif:', reg.scope))
                .catch(err => console.log('PWA SW gagal:', err));
        });
    }

    // Tangkap event pemasangan PWA (Install Prompt)
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        const banner = document.getElementById('pwaInstallBanner');
        if (banner) banner.classList.remove('hidden');
    });

    document.getElementById('btnInstallPwa')?.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log('PWA Install Outcome:', outcome);
            deferredPrompt = null;
            document.getElementById('pwaInstallBanner')?.classList.add('hidden');
        }
    });

    // ------------------------------------------------------------------------
    // 2. Sistem Notifikasi Pelayar (Push / Browser Notification)
    // ------------------------------------------------------------------------
    let notificationEnabled = (Notification && Notification.permission === 'granted');
    let hasAlertedForCurrentFull = false;
    let wasPreviouslyOnline = <?php echo $is_online ? 'true' : 'false'; ?>;

    function updateNotifyIcon() {
        const icon = document.getElementById('notifyIcon');
        const btn = document.getElementById('btnNotify');
        if (Notification.permission === 'granted') {
            icon.className = 'fas fa-bell text-emerald-400';
            btn.classList.add('border-emerald-500/40', 'bg-emerald-500/10');
            notificationEnabled = true;
        } else if (Notification.permission === 'denied') {
            icon.className = 'fas fa-bell-slash text-rose-400';
            btn.classList.add('border-rose-500/40');
            notificationEnabled = false;
        } else {
            icon.className = 'fas fa-bell text-slate-400';
            notificationEnabled = false;
        }
    }
    updateNotifyIcon();

    async function toggleNotificationPermission() {
        if (!('Notification' in window)) {
            alert('Pelayar anda tidak menyokong notifikasi.');
            return;
        }

        if (Notification.permission !== 'granted') {
            const permission = await Notification.requestPermission();
            updateNotifyIcon();
            if (permission === 'granted') {
                new Notification('🔔 Notifikasi PPRZ Diaktifkan', {
                    body: 'Anda akan menerima amaran automatik sekiranya tong penuh atau terputus sambungan!',
                    icon: 'icon-192.png'
                });
            }
        } else {
            alert('Notifikasi sudah diaktifkan untuk peranti ini.');
        }
    }

    // ------------------------------------------------------------------------
    // 3. Web Audio API Buzzer (Bunyi Chime Penggera)
    // ------------------------------------------------------------------------
    function bunyikanAlarm(isTest = false) {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // 880Hz
            osc.frequency.exponentialRampToValueAtTime(440, audioCtx.currentTime + 0.35);

            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start();
            osc.stop(audioCtx.currentTime + 0.4);

            if ('vibrate' in navigator) {
                navigator.vibrate([250, 100, 250]);
            }
        } catch (e) {
            console.log('Audio Context error:', e);
        }
    }

    // ------------------------------------------------------------------------
    // 4. Pengurusan Modal Info Sambungan & Ikon SVG
    // ------------------------------------------------------------------------
    const SVG_ONLINE_SM = '<svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';
    const SVG_OFFLINE_SM = '<svg class="w-4 h-4 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';
    const SVG_ONLINE_LG = '<svg class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';
    const SVG_OFFLINE_LG = '<svg class="w-5 h-5 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';
    const SVG_ONLINE_PILL = '<svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';
    const SVG_OFFLINE_PILL = '<svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>';

    let connDetails = {
        is_online: <?php echo $is_online ? 'true' : 'false'; ?>,
        last_seen_time: "<?php echo $offline_time_str; ?>",
        last_seen_date: "<?php echo $offline_date_str; ?>",
        offline_duration: "<?php echo $offline_duration_str; ?>",
        device: "<?php echo htmlspecialchars($device); ?>",
        location: "<?php echo htmlspecialchars($lokasi); ?>"
    };

    function openConnModal() {
        const modal = document.getElementById('connModal');
        const badge = document.getElementById('modalStatusBadge');
        const iconBg = document.getElementById('modalStatusIconBg');
        const svgHolder = document.getElementById('modalSvgHolder');
        const mTime = document.getElementById('modalTime');
        const mAgo = document.getElementById('modalAgo');
        const mDate = document.getElementById('modalDate');
        const mDesc = document.getElementById('modalDesc');

        mTime.textContent = connDetails.last_seen_time || '-';
        mAgo.textContent = connDetails.offline_duration || '-';
        mDate.textContent = 'Tarikh: ' + (connDetails.last_seen_date || '-');

        if (connDetails.is_online) {
            badge.className = 'text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-emerald-500/20 text-emerald-300';
            badge.textContent = 'ONLINE';
            iconBg.className = 'w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-500/20 text-emerald-400';
            if (svgHolder) svgHolder.innerHTML = SVG_ONLINE_LG;
            mAgo.className = 'text-xs text-emerald-300 font-medium block mt-1';
            mDesc.textContent = 'Peranti aktif berkomunikasi secara normal dengan pelayan.';
        } else {
            badge.className = 'text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-rose-500/20 text-rose-300';
            badge.textContent = 'OFFLINE';
            iconBg.className = 'w-9 h-9 rounded-xl flex items-center justify-center bg-rose-500/20 text-rose-400 animate-pulse';
            if (svgHolder) svgHolder.innerHTML = SVG_OFFLINE_LG;
            mAgo.className = 'text-xs text-rose-300 font-medium block mt-1';
            mDesc.textContent = 'Peranti tidak menghantar data baharu melebihi 60 saat. Sila semak bekalan kuasa atau Wi-Fi tong.';
        }

        modal.classList.remove('hidden');
    }

    function closeConnModal(e) {
        if (e.target.id === 'connModal') {
            closeConnModalDirect();
        }
    }

    function closeConnModalDirect() {
        document.getElementById('connModal').classList.add('hidden');
    }

    // ------------------------------------------------------------------------
    // 5. Polling Segerak & Pengesanan Sambungan Masa Nyata dari esp32.php
    // ------------------------------------------------------------------------
    let lastSeenTimestampStr = "<?php echo $masa; ?>";

    async function fetchLatestData() {
        try {
            const res = await fetch('esp32.php?t=' + Date.now(), { cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            // A. KEMASKINI STATUS SAMBUNGAN BERASASKAN ICON
            if (data && data.device_connection) {
                const conn = data.device_connection;
                const isOnline = conn.is_online;

                connDetails.is_online = isOnline;
                connDetails.last_seen_time = conn.last_seen_time || '-';
                connDetails.last_seen_date = conn.last_seen_date || '-';
                connDetails.offline_duration = conn.offline_duration || (conn.seconds_since_last_ping + 's lalu');

                const btnIcon = document.getElementById('btnConnIcon');
                const connSvgHolder = document.getElementById('connSvgHolder');
                const headDot = document.getElementById('connHeaderDot');
                const cardConnPill = document.getElementById('cardConnPill');
                const cardConnSvg = document.getElementById('cardConnSvg');
                const cardConnText = document.getElementById('cardConnText');
                const displayTimestampShort = document.getElementById('displayTimestampShort');

                // Elemen Skrin 2
                const screen2StatusBadge = document.getElementById('screen2StatusBadge');
                const screen2Time = document.getElementById('screen2Time');
                const screen2Ago = document.getElementById('screen2Ago');
                const screen2Date = document.getElementById('screen2Date');

                if (screen2StatusBadge) {
                    screen2StatusBadge.className = isOnline 
                        ? 'text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-emerald-500/20 text-emerald-300' 
                        : 'text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-rose-500/20 text-rose-300';
                    screen2StatusBadge.textContent = isOnline ? 'ONLINE' : 'OFFLINE';
                }
                if (screen2Time) screen2Time.textContent = connDetails.last_seen_time;
                if (screen2Ago) screen2Ago.textContent = connDetails.offline_duration;
                if (screen2Date) screen2Date.textContent = connDetails.last_seen_date;
                if (displayTimestampShort) displayTimestampShort.textContent = connDetails.last_seen_time;

                if (isOnline) {
                    if (btnIcon) btnIcon.className = 'relative w-8 h-8 rounded-xl flex items-center justify-center transition border text-sm active:scale-95 shadow-sm bg-emerald-500/15 border-emerald-500/30 text-emerald-400';
                    if (connSvgHolder) connSvgHolder.innerHTML = SVG_ONLINE_SM;
                    if (headDot) headDot.className = 'absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping';
                    if (cardConnPill) cardConnPill.className = 'inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition shrink-0 bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
                    if (cardConnSvg) cardConnSvg.innerHTML = SVG_ONLINE_PILL;
                    if (cardConnText) cardConnText.textContent = 'Online';
                    wasPreviouslyOnline = true;
                } else {
                    if (btnIcon) btnIcon.className = 'relative w-8 h-8 rounded-xl flex items-center justify-center transition border text-sm active:scale-95 shadow-sm bg-rose-500/15 border-rose-500/30 text-rose-400 animate-pulse';
                    if (connSvgHolder) connSvgHolder.innerHTML = SVG_OFFLINE_SM;
                    if (headDot) headDot.className = 'absolute -top-1 -right-1 w-2 h-2 rounded-full bg-rose-500';
                    if (cardConnPill) cardConnPill.className = 'inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition shrink-0 bg-rose-500/15 text-rose-400 border border-rose-500/30 animate-pulse';
                    if (cardConnSvg) cardConnSvg.innerHTML = SVG_OFFLINE_PILL;
                    if (cardConnText) cardConnText.textContent = 'Offline';

                    // Notifikasi jika peranti baru bertukar ke offline
                    if (wasPreviouslyOnline) {
                        wasPreviouslyOnline = false;
                        bunyikanAlarm();
                        if (notificationEnabled) {
                            new Notification('⚠️ AMARAN: Tong Terputus Sambungan!', {
                                body: 'Peranti tong offline sejak jam ' + (conn.offline_since || connDetails.last_seen_time || 'sebentar tadi') + '.',
                                icon: 'icon-192.png'
                            });
                        }
                    }
                }
            }

            // B. KEMASKINI DATA SENSOR & PARAS TONG
            if (data && data.latest_data && typeof data.latest_data === 'object') {
                const item = data.latest_data;
                const capacity = parseFloat(item.kapasiti || 0);
                const distance = parseFloat(item.jarak_cm || 0);
                const status = item.status || 'Normal';
                const device = item.device_id || 'Tong-PPRZ-01';
                const location = item.lokasi || 'PPRZ Papar';
                const timestamp = item.created_at || new Date().toLocaleString();
                lastSeenTimestampStr = timestamp;

                const elCap = document.getElementById('displayCapacity');
                if (elCap) elCap.innerHTML = capacity.toFixed(1) + '<span class="text-xl text-white/80">%</span>';
                
                const elDist = document.getElementById('displayDistance');
                if (elDist) elDist.textContent = distance.toFixed(1) + ' cm';
                
                const elTime = document.getElementById('displayTimestampShort');
                if (elTime && connDetails.last_seen_time) elTime.textContent = connDetails.last_seen_time;
                
                const elDev = document.getElementById('displayDevice');
                if (elDev) elDev.textContent = device;
                
                const elLoc = document.getElementById('displayLocation');
                if (elLoc) elLoc.innerHTML = '<i class="fas fa-location-dot text-rose-400 text-xs mr-1"></i>' + location;

                const fillEl = document.getElementById('binFillLevel');
                if (fillEl) fillEl.style.height = Math.min(100, Math.max(5, capacity)) + '%';

                const badgeEl = document.getElementById('badgeStatus');
                if (badgeEl) badgeEl.textContent = status;

                const alertBanner = document.getElementById('fullAlertBanner');
                const alertText = document.getElementById('alertBannerText');

                if (capacity >= 80) {
                    if (fillEl) fillEl.className = 'w-full transition-all duration-1000 relative flex items-center justify-center bg-gradient-to-t from-rose-600 via-rose-500 to-red-400';
                    if (badgeEl) badgeEl.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase transition-all duration-300 bg-rose-500/20 text-rose-300 border border-rose-500/30 animate-pulse';

                    if (alertBanner) alertBanner.classList.remove('hidden');
                    if (alertText) alertText.textContent = 'Kapasiti telah mencapai ' + capacity.toFixed(1) + '% (' + status + '). Sila kosongkan segera!';

                    if (!hasAlertedForCurrentFull) {
                        hasAlertedForCurrentFull = true;
                        bunyikanAlarm();

                        if (notificationEnabled) {
                            new Notification('⚠️ AMARAN: Tong Sampah Penuh!', {
                                body: 'Tong ' + device + ' kini ' + capacity.toFixed(1) + '% penuh!',
                                icon: 'icon-192.png'
                            });
                        }
                    }
                } else {
                    hasAlertedForCurrentFull = false;
                    if (alertBanner) alertBanner.classList.add('hidden');

                    if (capacity >= 50) {
                        if (fillEl) fillEl.className = 'w-full transition-all duration-1000 relative flex items-center justify-center bg-gradient-to-t from-amber-600 via-amber-500 to-yellow-400';
                        if (badgeEl) badgeEl.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase transition-all duration-300 bg-amber-500/20 text-amber-300 border border-amber-500/30';
                    } else {
                        if (fillEl) fillEl.className = 'w-full transition-all duration-1000 relative flex items-center justify-center bg-gradient-to-t from-emerald-600 via-emerald-500 to-teal-400';
                        if (badgeEl) badgeEl.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase transition-all duration-300 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                    }
                }

                const pollMini = document.getElementById('pollStatusMini');
                if (pollMini) pollMini.textContent = 'Segar';
            }
        } catch (e) {
            console.log('Polling ralat:', e);
            const pollMini = document.getElementById('pollStatusMini');
            if (pollMini) pollMini.textContent = 'Ralat';
        }
    }

    // Mula polling automatik setiap 4 saat
    setInterval(fetchLatestData, 4000);
    </script>
</body>
</html>
