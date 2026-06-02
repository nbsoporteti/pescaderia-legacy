#Requires -RunAsAdministrator
<#
.SYNOPSIS
  Corrige acceso a pescaderia.nbsoporteti.com cuando el DNS del ISP no resuelve el dominio.

.DESCRIPTION
  1) Configura DNS 8.8.8.8 y 1.1.1.1 en adaptadores IPv4 conectados
  2) Agrega entrada en hosts (respaldo)
  3) Limpia caché DNS y comprueba resolución

  Distribuir: clic derecho -> Ejecutar con PowerShell (como administrador)
  O ejecutar: fix-dns-pescaderia.bat
#>

$ErrorActionPreference = 'Stop'
$Dominio = 'pescaderia.nbsoporteti.com'
$DominioAlt = 'pescaderia.amjsoft.com'
$IpVps = '177.7.58.246'
$DnsPrimario = '8.8.8.8'
$DnsSecundario = '1.1.1.1'
$HostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"
$Marcador = '# fix-dns-pescaderia-amjsoft'

function Write-Ok($msg) { Write-Host "[OK] $msg" -ForegroundColor Green }
function Write-Warn($msg) { Write-Host "[!] $msg" -ForegroundColor Yellow }
function Write-Err($msg) { Write-Host "[X] $msg" -ForegroundColor Red }

Write-Host ""
Write-Host "=== Reparar DNS - $Dominio ===" -ForegroundColor Cyan
Write-Host ""

# --- 1. DNS en adaptadores conectados ---
$adaptadores = Get-NetAdapter | Where-Object { $_.Status -eq 'Up' }
$cfgDns = 0

foreach ($a in $adaptadores) {
    $ifIndex = $a.ifIndex
    $v4 = Get-NetIPAddress -InterfaceIndex $ifIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue |
        Where-Object { $_.IPAddress -notlike '169.254*' }
    if (-not $v4) { continue }

    try {
        Set-DnsClientServerAddress -InterfaceIndex $ifIndex -ServerAddresses ($DnsPrimario, $DnsSecundario) -ErrorAction Stop
        Write-Ok "DNS actualizado en: $($a.Name)"
        $cfgDns++
    }
    catch {
        Write-Warn "No se pudo cambiar DNS en $($a.Name): $($_.Exception.Message)"
    }
}

if ($cfgDns -eq 0) {
    Write-Warn "Intentando con netsh en adaptadores Up..."
    foreach ($a in $adaptadores) {
        $nombre = $a.Name
        $null = netsh interface ipv4 set dns name="$nombre" static $DnsPrimario primary 2>&1
        $null = netsh interface ipv4 add dns name="$nombre" $DnsSecundario index=2 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Ok "DNS (netsh) en: $nombre"
            $cfgDns++
        }
    }
}

# --- 2. Archivo hosts (respaldo) ---
$hosts = Get-Content $HostsPath -Raw -ErrorAction SilentlyContinue
$entradas = @(
    @{ Dominio = $Dominio; Linea = "$IpVps $Dominio $Marcador" },
    @{ Dominio = $DominioAlt; Linea = "$IpVps $DominioAlt $Marcador" }
)
foreach ($e in $entradas) {
    if ($hosts -notmatch [regex]::Escape($e.Dominio)) {
        Add-Content -Path $HostsPath -Value $e.Linea -Encoding ASCII
        Write-Ok "Entrada hosts: $($e.Dominio)"
    }
    else {
        Write-Ok "Hosts OK: $($e.Dominio)"
    }
}

# --- 3. Limpiar caché DNS ---
ipconfig /flushdns | Out-Null
Clear-DnsClientCache -ErrorAction SilentlyContinue
Write-Ok "Caché DNS limpiada"

# --- 4. Verificar ---
Write-Host ""
Write-Host "Comprobando..." -ForegroundColor Cyan
Start-Sleep -Seconds 1

$resuelto = $false
try {
    $r = Resolve-DnsName -Name $Dominio -Type A -ErrorAction Stop | Where-Object { $_.Type -eq 'A' } | Select-Object -First 1
    if ($r.IPAddress -eq $IpVps) {
        $resuelto = $true
        Write-Ok "$Dominio -> $($r.IPAddress)"
    }
    else {
        Write-Warn "$Dominio -> $($r.IPAddress) (esperado $IpVps)"
        $resuelto = $true
    }
}
catch {
    Write-Err "No se pudo resolver $Dominio : $($_.Exception.Message)"
}

Write-Host ""
if ($resuelto) {
    Write-Host "Listo. Abrí en el navegador:" -ForegroundColor Green
    Write-Host "  https://$Dominio/login.php" -ForegroundColor White
    Write-Host "  https://$DominioAlt/login.php  (alternativa si nbsoporteti falla)" -ForegroundColor White
}
else {
    Write-Host "Revisá conexión a internet o ejecutá de nuevo como administrador." -ForegroundColor Yellow
}

Write-Host ""
Read-Host "Presioná Enter para cerrar"
