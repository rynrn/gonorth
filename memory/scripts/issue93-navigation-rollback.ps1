param([switch]$Apply)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$items = @(
    @{ Name='functions.php'; Backup='memory/work/issue93-navigation/functions.before.php'; Original='aeb1a129ab3b004275aaa257e8f141dfdeb594e92bfdb7965329797e9bb6fd3f'; Staged='6d603eee842e81a214c991a3de36a701facc4b885dda0a721c2fe9d610fbe7f1'; Temp='/tmp/gn-functions-navigation-rollback.php' },
    @{ Name='single-gd_place.php'; Backup='memory/work/issue93-navigation/single-gd_place.before.php'; Original='6127219391534dba5cd43a98328010d08fb7374d8e97b84e4c944d93cf0009bf'; Staged='b541f6cfcb293b09733622f5f0d08860fcbb431f86da62fbc42b4381efb7942e'; Temp='/tmp/gn-single-navigation-rollback.php' }
)
$theme = '/var/www/gonorth/wp-content/themes/gonorth-child'
foreach ($item in $items) {
    $local = Join-Path $root $item.Backup
    if (-not (Test-Path -LiteralPath $local)) { throw "Missing local backup: $local" }
    $backupHash = (Get-FileHash -LiteralPath $local -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($backupHash -ne $item.Original) { throw "Backup hash mismatch: $($item.Name)" }
    $path = "$theme/$($item.Name)"
    $line = & ssh -o BatchMode=yes gonorth "sha256sum $path"
    if ($LASTEXITCODE -ne 0 -or $line -notmatch '^([0-9a-f]{64})\s') { throw "Cannot hash live file: $path" }
    $current = $Matches[1]
    if ($current -ne $item.Original -and $current -ne $item.Staged) { throw "Unexpected live hash: $($item.Name)" }
    $item['Current'] = $current
    Write-Output "$($item.Name): $current"
}
if (-not $Apply) { Write-Output 'Dry run: guard passed; no files changed.'; exit 0 }
foreach ($item in $items) {
    if ($item.Current -eq $item.Original) { continue }
    $local = Join-Path $root $item.Backup
    $path = "$theme/$($item.Name)"
    & scp -q $local "gonorth:$($item.Temp)"
    if ($LASTEXITCODE -ne 0) { throw "Could not upload backup: $($item.Name)" }
    & ssh -o BatchMode=yes gonorth "php -l $($item.Temp) >/dev/null && cp $($item.Temp) $path && php -l $path && sha256sum $path"
    if ($LASTEXITCODE -ne 0) { throw "Could not restore: $($item.Name)" }
}
& ssh -o BatchMode=yes gonorth 'wp cache flush --path=/var/www/gonorth --allow-root'
if ($LASTEXITCODE -ne 0) { throw 'Cache flush failed after rollback' }
Write-Output 'Rollback applied.'
