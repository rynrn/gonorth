param([switch]$Apply)
$ErrorActionPreference='Stop'
$idFile='memory/data/2026-10-06-issue-93-nahal-id.txt'
if(-not (Test-Path -LiteralPath $idFile)){throw 'ID file absent; no target'}
$id=(Get-Content -LiteralPath $idFile -Raw).Trim()
if($id -notmatch '^\d+$'){throw 'Invalid ID'}
$raw=ssh -o BatchMode=yes gonorth "wp post get $id --format=json --path=/var/www/gonorth --allow-root"
if($LASTEXITCODE -ne 0){throw 'Target not found'}
$p=$raw|ConvertFrom-Json
if($p.ID -ne [int]$id -or $p.post_type -ne 'gd_place' -or $p.post_status -ne 'pending' -or $p.post_title -ne 'שמורת טבע נחל המערות'){throw 'Target guard failed'}
if($p.post_content -or $p.post_excerpt){throw 'Content already changed; manual review needed'}
$thumb=(ssh -o BatchMode=yes gonorth "wp post meta get $id _thumbnail_id --path=/var/www/gonorth --allow-root").Trim()
if($thumb){throw 'Media attached; manual review needed'}
if(-not $Apply){Write-Output "Guard passed for pending probe $id; no changes made";exit 0}
ssh -o BatchMode=yes gonorth "wp post delete $id --force --path=/var/www/gonorth --allow-root" | Out-Null
if($LASTEXITCODE -ne 0){throw 'Deletion failed'}
Write-Output "Deleted guarded pending probe $id"
