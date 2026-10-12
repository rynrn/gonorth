$ErrorActionPreference='Stop'
$d=Get-Content -Raw memory/data/2026-09-26-internal-link-content-export-after-batch-03.json|ConvertFrom-Json
$a=$d.rows|Where-Object id -eq 440
$b=$d.rows|Where-Object id -eq 441
$updates=@()
foreach($pair in @(@($a,$b,'לרעיונות לחג נוסף בעונת תשרי, עברו גם אל'),@($b,$a,'מתכננים מראש את חופשת החג הבאה? עברו גם אל'))){
 $src=$pair[0]
 $dst=$pair[1]
 $lead=$pair[2]
 $old=[string]$src.content
 if($old -match [regex]::Escape([string]$dst.url)){throw "Existing cross-link $($src.id) -> $($dst.id)"}
 $sentence="`n  <p>$lead <a href=`"$($dst.url)`">$($dst.title)</a>.</p>`n"
 $trim=$old.TrimEnd()
 if(-not $trim.EndsWith('</div>')){throw "Expected closing div missing in $($src.id)"}
 $new=$trim.Substring(0,$trim.Length-6)+$sentence+'</div>'+"`n"
 $oldHash=([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes($old))).ToLowerInvariant())
 $newHash=([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes($new))).ToLowerInvariant())
 $updates+=[pscustomobject]@{id=[int]$src.id;title=$src.title;url=$src.url;target_id=[int]$dst.id;target_url=$dst.url;old_content=$old;new_content=$new;old_sha256=$oldHash;new_sha256=$newHash}
}
$updates|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-26-issue-45-holiday-crosslinks-backup-and-payload.json -Encoding utf8
$utf8=[Text.UTF8Encoding]::new($false)
foreach($u in $updates){[IO.File]::WriteAllText((Join-Path (Resolve-Path memory/data).Path ("issue-45-$($u.id)-holiday-new.html")),[string]$u.new_content,$utf8)}
$updates|Select-Object id,title,target_id,@{n='old_chars';e={$_.old_content.Length}},@{n='new_chars';e={$_.new_content.Length}}|Format-Table -AutoSize
