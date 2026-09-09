# ==============================================================================
# Deploy script untuk Tong Sampah Pintar PWA & API
# ==============================================================================
param(
    [switch]$Force
)

# Nilai lalai placeholder (selamat untuk GitHub / Open Source)
$user = 'YOUR_FTP_USERNAME'
$pass = 'YOUR_FTP_PASSWORD'
$localBase = $PSScriptRoot
$remoteBase = 'ftp://your-domain.com/public_html/tong'
$webBaseUrl = 'https://your-domain.com/tong'

# Muat tetapan persendirian / produksi daripada deploy.local.ps1 jika wujud (diabaikan oleh git)
$localDeployConfig = Join-Path $localBase "deploy.local.ps1"
if (Test-Path $localDeployConfig) {
    . $localDeployConfig
}

$filesToDeploy = @(
    "index.php",
    "about.php",
    "simulate.php",
    "settings.php",
    "config.php",
    "config.local.php",
    "config.json",
    "esp32.php",
    "manifest.json",
    "sw.js",
    "icon-192.png",
    "icon-512.png",
    "icon-maskable-192.png",
    "icon-maskable-512.png",
    "apple-touch-icon.png",
    "favicon.png",
    "esp32_firmware/esp32_firmware.ino",
    "log_tong.txt"
)

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " MEMULAKAN UPLOAD SEMUA KE SASARAN                        " -ForegroundColor Cyan
Write-Host " Sasaran FTP: $remoteBase                                 " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# Pastikan folder tong wujud di remote
try {
    $req = [System.Net.FtpWebRequest]::Create($remoteBase)
    $req.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
    $req.UsePassive = $true
    $req.Timeout = 5000
    $resp = $req.GetResponse()
    $resp.Close()
} catch {
    # Abaikan jika folder sudah ada
}

$uploaded = 0
foreach ($fileName in $filesToDeploy) {
    $localFile = Join-Path $localBase $fileName
    if (!(Test-Path $localFile)) {
        continue
    }

    # Pastikan subfolder wujud di remote jika ada
    $subDir = [System.IO.Path]::GetDirectoryName($fileName).Replace('\', '/')
    if ($subDir) {
        try {
            $dirUri = "$remoteBase/$subDir"
            $dReq = [System.Net.FtpWebRequest]::Create($dirUri)
            $dReq.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
            $dReq.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $dReq.UsePassive = $true
            $dReq.Timeout = 5000
            $dResp = $dReq.GetResponse()
            $dResp.Close()
        } catch {}
    }

    $remoteUri = "$remoteBase/$fileName"
    Write-Host " -> Uploading: $fileName ..." -NoNewline

    try {
        $req = [System.Net.FtpWebRequest]::Create($remoteUri)
        $req.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
        $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $req.UseBinary = $true
        $req.UsePassive = $true
        $req.Timeout = 20000

        $fileBytes = [System.IO.File]::ReadAllBytes($localFile)
        $req.ContentLength = $fileBytes.Length

        $reqStream = $req.GetRequestStream()
        $reqStream.Write($fileBytes, 0, $fileBytes.Length)
        $reqStream.Close()

        $resp = $req.GetResponse()
        $resp.Close()
        Write-Host " [BERJAYA]" -ForegroundColor Green
        $uploaded++
    } catch {
        Write-Host " [RALAT: $($_.Exception.Message)]" -ForegroundColor Red
    }
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " SELESAI: $uploaded fail berjaya dimuat naik." -ForegroundColor Green
Write-Host " PWA Mobile:   $webBaseUrl/" -ForegroundColor Yellow
Write-Host " Info Inovasi: $webBaseUrl/about.php" -ForegroundColor Yellow
Write-Host " Simulator:    $webBaseUrl/simulate.php" -ForegroundColor Yellow
Write-Host " Endpoint API: $webBaseUrl/esp32.php" -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan
