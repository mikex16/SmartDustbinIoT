<?php
/**
 * ============================================================================
 * SmartDustbin - Latar Belakang & Maklumat Inovasi Projek
 * Fail: about.php
 * Dihasilkan oleh Murid SK Kelatuan Papar & Dibangunkan Bersama AI (Vibe Coding)
 * Status: 100% Sumber Terbuka (Open Source)
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDustbin | Inovasi SK Kelatuan (Open Source)</title>

    <!-- Meta & PWA Icons -->
    <meta name="theme-color" content="#020617">
    <link rel="manifest" href="manifest.json?v=2">
    <link rel="icon" type="image/png" sizes="192x192" href="icon-192.png?v=2">
    <link rel="icon" type="image/png" sizes="64x64" href="favicon.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png?v=2">

    <!-- Tailwind CSS & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .glow-blue {
            box-shadow: 0 0 35px rgba(2, 132, 199, 0.25);
        }
        .glow-emerald {
            box-shadow: 0 0 35px rgba(16, 185, 129, 0.2);
        }
        .glow-amber {
            box-shadow: 0 0 35px rgba(245, 158, 11, 0.2);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased selection:bg-blue-600 selection:text-white pb-14">

    <!-- Header Navigasi Atas -->
    <header class="border-b border-slate-800 bg-slate-900/95 backdrop-blur sticky top-0 z-30 shadow-lg">
        <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <a href="index.php" class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700/80 flex items-center justify-center shadow-md shadow-slate-950 overflow-hidden shrink-0 hover:scale-105 transition">
                    <img src="favicon.png?v=2" alt="Logo SK Kelatuan" class="w-full h-full object-cover">
                </a>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h1 class="text-xs font-bold text-white tracking-wide">SmartDustbin</h1>
                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30">Open Source</span>
                    </div>
                    <p class="text-[10px] text-slate-400">SK Kelatuan Papar, Sabah</p>
                </div>
            </div>

            <!-- Pautan Pantas -->
            <div class="flex items-center space-x-2 text-xs">
                <a href="index.php" class="px-2.5 py-1 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold transition flex items-center gap-1.5 shadow-md shadow-blue-600/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> <span>Utama</span>
                </a>
                <a href="https://github.com/mikex16/SmartDustbinIoT" target="_blank" rel="noopener noreferrer" title="Kod Sumber GitHub (Open Source)" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 transition flex items-center justify-center">
                    <i class="fab fa-github text-sm"></i>
                </a>
                <a href="simulate.php" title="Buka Simulator" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 border border-slate-700/60 transition flex items-center justify-center">
                    <i class="fas fa-sliders text-xs"></i>
                </a>
                <a href="settings.php" title="Tetapan" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700/60 transition flex items-center justify-center">
                    <i class="fas fa-gear text-xs"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Kandungan Utama -->
    <main class="max-w-3xl mx-auto px-4 pt-6 space-y-6">

        <!-- ================================================================ -->
        <!-- KAD HERO / TAJUK UTAMA PROJEK                                    -->
        <!-- ================================================================ -->
        <section class="p-6 rounded-3xl bg-gradient-to-br from-blue-950/40 via-slate-900 to-slate-950 border border-blue-500/30 glow-blue text-center relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-44 h-44 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -bottom-10 w-44 h-44 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col items-center">
                <!-- Lencana Sekolah -->
                <div class="w-20 h-20 rounded-2xl bg-white/5 p-1 border border-white/15 shadow-2xl mb-3.5">
                    <img src="icon-192.png" alt="SK Kelatuan Logo" class="w-full h-full rounded-xl object-contain shadow-lg">
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[11px] font-semibold mb-2">
                    <i class="fas fa-recycle text-[10px]"></i> Budaya Kitar Semula &bull; Inovasi STEM Murid
                </div>

                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    SmartDustbin SK Kelatuan
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-xl leading-relaxed">
                    Sistem pemantauan tong sampah pintar yang direka dan dibangunkan oleh murid-murid <strong>SK Kelatuan Papar</strong> untuk memupuk budaya kitar semula, menggabungkan rangka bahan terbuang, sensor <strong>IoT ESP32</strong>, dan pembangunan perisian menggunakan <strong>AI Vibe Coding</strong>.
                </p>

                <!-- Tag Sorotan -->
                <div class="flex flex-wrap justify-center gap-2 mt-4 text-[11px]">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                        <i class="fas fa-box-archive text-amber-400"></i> Rangka Bahan Terbuang
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                        <i class="fas fa-microchip text-cyan-400"></i> ESP32 &amp; Ultrasonik
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                        <i class="fas fa-wand-magic-sparkles text-fuchsia-400"></i> AI Vibe Coding
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                        <i class="fab fa-github text-emerald-400"></i> 100% Sumber Terbuka
                    </span>
                </div>
            </div>
        </section>


        <!-- ================================================================ -->
        <!-- OBJEKTIF & MISI PROJEK                                           -->
        <!-- ================================================================ -->
        <section class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-md space-y-3">
            <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                <i class="fas fa-bullseye"></i> Misi &amp; Matlamat Utama
            </div>
            <h3 class="text-base font-bold text-white">
                Membudayakan Amalan Kitar Semula Di Kalangan Murid Sekolah
            </h3>
            <p class="text-xs text-slate-300 leading-relaxed">
                Objektif utama projek ini adalah untuk menanam rasa tanggungjawab terhadap kebersihan persekitaran sekolah dan menerapkan <strong>budaya 3R (Reduce, Reuse, Recycle)</strong> dalam kalangan murid sekolah rendah bermula dari bilik darjah. Melalui SmartDustbin, aktiviti membuang dan mengasingkan sampah kitar semula menjadi pengalaman yang interaktif, berteknologi, dan menyeronokkan!
            </p>
        </section>


        <!-- ================================================================ -->
        <!-- 3 ELEMEN UTAMA PEMBANGUNAN (THE 3 CORE PILLARS)                  -->
        <!-- ================================================================ -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2 px-1">
                <i class="fas fa-cubes text-blue-400"></i> 3 Tonggak Pembangunan SmartDustbin
            </h3>

            <!-- Fasa 1: Rangka Bahan Terbuang -->
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-amber-500/40 transition shadow-md">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0 text-base shadow-sm">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-white">1. Rangka Tong dari Bahan Terbuang (Upcycling)</h4>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/10 text-amber-300 border border-amber-500/20 font-mono">Bahan Kitar Semula</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Projek dimulakan dengan mengumpul bahan-bahan terbuang seperti kotak kadbod terpakai, plastik, dan bahan kitar semula di persekitaran sekolah. Murid-murid SK Kelatuan sendiri yang mengukur, memotong, mencantum, dan membentuk rangka tong sampah pintar ini.
                        </p>
                        <div class="pt-1 text-[11px] text-amber-300/90 font-medium flex items-center gap-1">
                            <i class="fas fa-check-circle text-[10px]"></i> Mengaplikasikan konsep "Daripada sisa kepada teknologi berguna".
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fasa 2: Elemen IoT & ESP32 -->
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-cyan-500/40 transition shadow-md">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center shrink-0 text-base shadow-sm">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-white">2. Integrasi Perkakasan IoT (ESP32 &amp; Sensor)</h4>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 font-mono">Hardware IoT</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Rangka fizikal kemudiannya dilengkapkan dengan mikropengawal <strong>ESP32</strong> (dengan modul Wi-Fi terbina dalam) dan <strong>Sensor Ultrasonik (HC-SR04)</strong> yang diletakkan pada bahagian dalam penutup tong. Sensor memancarkan gelombang bunyi untuk mengira jarak aras sampah dan menterjemahkannya kepada peratusan kapasiti secara langsung.
                        </p>
                        <div class="grid grid-cols-3 gap-2 pt-2 text-center text-[10px] font-mono">
                            <div class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-emerald-400">
                                <span class="block font-bold">0% - 49%</span>
                                <span class="text-slate-400 text-[9px]">Normal / Sedia</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-amber-400">
                                <span class="block font-bold">50% - 79%</span>
                                <span class="text-slate-400 text-[9px]">Sederhana</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-rose-400">
                                <span class="block font-bold">&gt;= 80%</span>
                                <span class="text-slate-400 text-[9px]">Amaran Penuh!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fasa 3: Programming AI Vibe Coding -->
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-fuchsia-500/40 transition shadow-md">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-fuchsia-500/15 border border-fuchsia-500/30 text-fuchsia-400 flex items-center justify-center shrink-0 text-base shadow-sm">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-white">3. Pengaturcaraan Frontend &amp; Backend via AI (Vibe Coding)</h4>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-fuchsia-500/10 text-fuchsia-300 border border-fuchsia-500/20 font-mono">AI Vibe Coding</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Aplikasi web progresif (PWA), pelayan API, dan pangkalan data dibangunkan menggunakan kaedah moden <strong>AI Vibe Coding</strong>. Melalui interaksi bahasa semulajadi (*natural language*) bersama sistem AI terkini, murid dan pembimbing berjaya merangka, menguji, dan melancarkan:
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-xs text-slate-300 pt-1">
                            <li><strong>Frontend PWA:</strong> Antara muka responsif, moden (*1-Screen Snap Viewport*), dan boleh dipasang ke skrin telefon pintar murid &amp; guru.</li>
                            <li><strong>Backend REST API &amp; DB:</strong> Endpoint <code class="text-cyan-300">esp32.php</code> untuk menerima data secara automatik dari mikropengawal.</li>
                            <li><strong>Sistem Pengesanan Sambungan:</strong> Menjejak status sambungan tong dan merekodkan waktu terputus sekiranya tong berada di luar talian (*offline*).</li>
                            <li><strong>Sistem Notifikasi &amp; Bunyi:</strong> Amaran tolak (*Push Notification*) dan nada penggera pintar apabila tong penuh.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>


        <!-- ================================================================ -->
        <!-- CADANGAN PENAMBAHBAIKAN MASA HADAPAN (FUTURE ROADMAP)            -->
        <!-- ================================================================ -->
        <section class="p-5 rounded-2xl bg-gradient-to-b from-slate-900 to-slate-900/90 border border-slate-800 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <span class="text-amber-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-lightbulb"></i> Hala Tuju Inovasi
                    </span>
                    <h3 class="text-base font-bold text-white mt-0.5">
                        Cadangan Penambahbaikan SmartDustbin
                    </h3>
                </div>
                <span class="text-[10px] px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/30 font-semibold">
                    Idea Generasi Seterusnya
                </span>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Projek ini membuka ruang yang luas untuk diterokai dan ditambah baik. Berikut adalah beberapa idea inovasi menarik yang boleh diperkembangkan:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <!-- 1. Saiz Tong Lebih Besar -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-amber-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-expand"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">1. Tong Sampah Skala Besar (120L / 240L)</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Menaik taraf kapasiti daripada model bilik darjah kepada tong beroda saiz komersial untuk kegunaan di kantin sekolah, dewan perhimpunan, mahupun tempat awam.
                    </p>
                </div>

                <!-- 2. Pengasingan Sisa Automatik (Sorting) -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-cyan-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-boxes-stacked"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">2. Pengasingan Sisa Pintar (Waste Sorting)</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Membina kompartmen berasingan untuk mengasingkan bahan secara automatik atau semi-automatik mengikut kategori: <strong>plastik, tin aluminium, kertas, dan sisa makanan</strong>.
                    </p>
                </div>

                <!-- 3. Pengecaman Objek AI (Computer Vision) -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-fuchsia-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-fuchsia-500/15 border border-fuchsia-500/30 text-fuchsia-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">3. Pengecaman Kamera AI (ESP32-CAM)</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Menggunakan modul kamera dan model <em>Edge AI / TinyML</em> untuk mengecam bentuk sampah (contoh: botol vs kertas) dan membuka pintu ruang yang betul secara automatik.
                    </p>
                </div>

                <!-- 4. Penutup Automatik Tanpa Sentuh -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-emerald-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-hands"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">4. Penutup Automatik Tanpa Sentuh (Hands-Free)</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Menambah motor servo dan sensor gerakan inframerah supaya penutup tong terbuka sendiri apabila tangan murid mendekat, demi menjaga kebersihan murid.
                    </p>
                </div>

                <!-- 5. Pemampat Sampah Automatik (Compactor) -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-rose-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-compress"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">5. Sistem Pemampat Sampah (Compactor)</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Mekanisme mekanikal pemampat motor untuk memampatkan botol plastik atau tin minuman supaya ruang tong dapat dijimatkan sehingga 3 kali ganda sebelum penuh.
                    </p>
                </div>

                <!-- 6. Gamifikasi & Mata Ganjaran RFID -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-blue-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/15 border border-blue-500/30 text-blue-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">6. Ganjaran Kitar Semula &bull; Imbasan RFID</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Murid boleh mengimbas kad pelajar atau kod QR setiap kali membuang sisa kitar semula untuk mengumpul mata ganjaran (*reward points*) yang boleh ditebus di koperasi sekolah!
                    </p>
                </div>

                <!-- 7. Kuasa Tenaga Suria (Solar Powered) -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-yellow-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-yellow-500/15 border border-yellow-500/30 text-yellow-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-solar-panel"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">7. Tenaga Suria Mesra Alam (Solar Power)</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Memasang panel solar mini bersama bateri Li-ion 18650 untuk membolehkan tong beroperasi di padang atau kawasan terbuka tanpa memerlukan punca elektrik dinding.
                    </p>
                </div>

                <!-- 8. Penderia Bau & Kebersihan Udara -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-teal-500/40 transition space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-teal-500/15 border border-teal-500/30 text-teal-400 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-wind"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">8. Penderia Bau Gas (MQ-135 / Air Quality)</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Mengesan bau busuk atau gas metana akibat pembusukan sisa organik bagi mencetuskan amaran pembersihan lebih awal demi kesihatan warga sekolah.
                    </p>
                </div>
            </div>
        </section>


        <!-- ================================================================ -->
        <!-- PROJEK SUMBER TERBUKA (OPEN SOURCE PROCLAMATION)                  -->
        <!-- ================================================================ -->
        <section class="p-6 rounded-3xl bg-gradient-to-br from-emerald-950/40 via-slate-900 to-slate-950 border border-emerald-500/30 glow-emerald space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-xl shrink-0 shadow-lg shadow-emerald-950">
                    <i class="fab fa-osi"></i>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/30">
                        100% Sumber Terbuka &bull; Open Source
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-white">
                        Projek Ini Terbuka Untuk Sesiapa Sahaja
                    </h3>
                </div>
            </div>

            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                Kami percaya bahawa <strong>ilmu dan teknologi adalah untuk dikongsi bersama</strong>. Projek <strong>SmartDustbin</strong> ini diisytiharkan sebagai <strong>projek sumber terbuka (*Open Source Project*)</strong>.
            </p>

            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2.5 text-xs text-slate-300">
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-emerald-400 mt-0.5 shrink-0"></i>
                    <span><strong>Bebas Digunakan:</strong> Mana-mana sekolah, kelab STEM, guru-guru inovasi, persatuan komuniti, atau pembuat (*makers*) bebas memuat turun dan menggunakannya.</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-emerald-400 mt-0.5 shrink-0"></i>
                    <span><strong>Bebas Diubah Suai:</strong> Anda dialu-alukan untuk menambah baik kod sumber, menukar saiz tong, menambah sensor baharu, atau menyesuaikan mengikut keperluan persekitaran anda.</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-emerald-400 mt-0.5 shrink-0"></i>
                    <span><strong>Kerjasama Global:</strong> Kod sumber penuh bagi aplikasi PWA, backend API, dan firmware ESP32 disimpan secara terbuka di repositori GitHub rasmi.</span>
                </div>
            </div>

            <!-- Butang Tindakan GitHub -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <a href="https://github.com/mikex16/SmartDustbinIoT" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition flex items-center gap-2 shadow-lg shadow-emerald-600/30">
                    <i class="fab fa-github text-sm"></i> <span>Akses Repositori GitHub</span>
                </a>
                <a href="https://github.com/mikex16/SmartDustbinIoT/tree/main/esp32_firmware" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition flex items-center gap-2">
                    <i class="fas fa-microchip text-cyan-400"></i> <span>Kod Firmware ESP32</span>
                </a>
            </div>
        </section>


        <!-- ================================================================ -->
        <!-- KATA-KATA SEMANGAT & MOTIVASI (INSPIRATIONAL CORNER)              -->
        <!-- ================================================================ -->
        <section class="p-6 rounded-3xl bg-gradient-to-br from-amber-950/30 via-slate-900 to-blue-950/40 border border-amber-500/30 glow-amber text-center relative overflow-hidden space-y-4">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 mb-1 shadow-lg">
                <i class="fas fa-rocket text-lg animate-bounce"></i>
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest block">
                    Pesanan Inspirasi Buat Murid &amp; Pencipta Muda
                </span>
                <h3 class="text-lg sm:text-xl font-black text-white tracking-tight">
                    "Don't Limit Your Imagination!"
                </h3>
            </div>

            <!-- Petikan Semangat -->
            <blockquote class="text-xs sm:text-sm text-slate-200 max-w-xl mx-auto leading-relaxed italic border-y border-slate-800/80 py-3.5 px-2">
                "Inovasi yang hebat tidak semestinya bermula dengan alat yang mahal atau makmal yang serba canggih. Ia sering bermula daripada secebis bahan terbuang di tepi bilik darjah, rasa ingin tahu seorang kanak-kanak, dan keberanian untuk mencuba sesuatu yang baharu. Jangan biarkan sesiapa mengehadkan imaginasi anda!"
            </blockquote>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] text-slate-300 max-w-xl mx-auto text-left">
                <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                    <span class="font-bold text-amber-300 block mb-1">🌱 Berani Mula</span>
                    <p class="text-[10px] text-slate-400">Daripada kotak terbuang, murid SK Kelatuan membuktikan ia boleh diubah menjadi alat pintar.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                    <span class="font-bold text-cyan-300 block mb-1">💡 Jangan Takut Gagal</span>
                    <p class="text-[10px] text-slate-400">Setiap ralat dalam kod dan litar adalah guru yang mendidik kita menuju kejayaan.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                    <span class="font-bold text-emerald-300 block mb-1">🌍 Sayangi Bumi</span>
                    <p class="text-[10px] text-slate-400">Setiap bahan kitar semula yang diselamatkan adalah nafas baharu untuk masa hadapan dunia.</p>
                </div>
            </div>

            <div class="pt-2">
                <span class="text-[11px] px-3.5 py-1.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 font-bold tracking-wide inline-flex items-center gap-1.5">
                    <i class="fas fa-graduation-cap"></i> Berusaha Berilmu &bull; Dari Papar Untuk Dunia
                </span>
            </div>
        </section>


        <!-- ================================================================ -->
        <!-- BUTANG NAVIGASI BAWAH                                            -->
        <!-- ================================================================ -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-2 pt-2">
            <a href="index.php" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs text-center transition shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2">
                <i class="fas fa-mobile-screen"></i> Buka Aplikasi PWA (Utama)
            </a>
            <a href="https://github.com/mikex16/SmartDustbinIoT" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-emerald-400 font-semibold text-xs text-center border border-slate-800 transition flex items-center justify-center gap-2">
                <i class="fab fa-github"></i> GitHub Repo
            </a>
            <a href="simulate.php" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-cyan-400 font-semibold text-xs text-center border border-slate-800 transition flex items-center justify-center gap-2">
                <i class="fas fa-sliders"></i> Simulator
            </a>
            <a href="settings.php" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-amber-400 font-semibold text-xs text-center border border-slate-800 transition flex items-center justify-center gap-2">
                <i class="fas fa-gear"></i> Tetapan
            </a>
        </div>

    </main>

    <!-- Footer Bawah -->
    <footer class="max-w-3xl mx-auto px-4 mt-8 pt-4 border-t border-slate-800/80 text-center space-y-1">
        <p class="text-[11px] text-slate-400 font-medium">
            SmartDustbin &bull; SK Kelatuan Papar, Sabah &bull; PPRZ.net
        </p>
        <p class="text-[10px] text-slate-500">
            Projek Sumber Terbuka (Open Source) &bull; Dikuasakan oleh ESP32 IoT &amp; AI Vibe Coding &bull; 2026
        </p>
    </footer>

</body>
</html>
