param([switch]$CheckOnly)
$ErrorActionPreference = 'Stop'
$target = '/var/www/gonorth/wp-content/themes/gonorth-child/functions.php'
$expectedCurrent = 'a3ec4e487089355f50abbb858469312f037b33422a0d736a9d7d8b654b432e23'
$expectedBackup = 'a3699dcfad6af4e2764a6e192e560618354f6c349f2fc70421a686474ad78d4c'
$backup = 'C:\dev\gonorth\memory\work\issue86-functions-before-batch.php'
if (-not (Test-Path -LiteralPath $backup)) { throw 'Rollback copy missing' }
$backupHash = (Get-FileHash -Algorithm SHA256 -LiteralPath $backup).Hash.ToLowerInvariant()
if ($backupHash -ne $expectedBackup) { throw 'Rollback copy checksum mismatch' }
$remoteLine = (& ssh -o BatchMode=yes gonorth "sha256sum $target")
if ($LASTEXITCODE -ne 0) { throw 'Remote checksum failed' }
$currentHash = ($remoteLine -split '\s+')[0].ToLowerInvariant()
if ($currentHash -ne $expectedCurrent) { throw 'Live file changed; refusing to overwrite' }
if ($CheckOnly) { Write-Output 'Rollback guard passed; no files changed'; exit 0 }
& scp $backup 'gonorth:/var/www/gonorth/wp-content/themes/gonorth-child/functions.php.issue86.batch-restore'
if ($LASTEXITCODE -ne 0) { throw 'Rollback transfer failed' }
& ssh -o BatchMode=yes gonorth 'set -e; php -l /var/www/gonorth/wp-content/themes/gonorth-child/functions.php.issue86.batch-restore; mv /var/www/gonorth/wp-content/themes/gonorth-child/functions.php.issue86.batch-restore /var/www/gonorth/wp-content/themes/gonorth-child/functions.php; wp cache flush --path=/var/www/gonorth --allow-root'
if ($LASTEXITCODE -ne 0) { throw 'Rollback command failed; inspect server state' }
Write-Output 'Rollback restored the previous functions.php'


