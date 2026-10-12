$ErrorActionPreference='Stop'
$d=Get-Content -Raw memory/data/2026-09-26-internal-link-content-export-final.json|ConvertFrom-Json
function GetRow([int]$id){$x=$d.rows|Where-Object id -eq $id;if(-not $x){throw "Missing $id"};$x}
$g267=GetRow 267;$g433=GetRow 433;$p229=GetRow 229
$updates=@()
function U($r,[string]$new,[string]$reason){$old=[string]$r.content;if($old -ceq $new){throw "No change $($r.id)"};$script:updates+=[pscustomobject]@{id=[int]$r.id;title=$r.title;reason=$reason;old_content=$old;new_content=$new;old_sha256=([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes($old))).ToLowerInvariant());new_sha256=([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes($new))).ToLowerInvariant())}}
$added267="`n<p>בדרך לכנרת דרך הגליל התחתון אפשר לבדוק גם את <a href=`"$($p229.url)`">שישו קפה במושב הזורעים</a>, ולוודא באתר העסק את שעות הפעילות העדכניות.</p>"
$n267=([string]$g267.content).Replace($added267,'')
if($n267 -match [regex]::Escape([string]$p229.url)){throw '267 still links to 229'}
U $g267 $n267 'הסרת הקישור החדש כדי למנוע פיצול בין מדריכי מסעדות מתחרים'
$oldBack="`n<p>לעוד אפשרויות אוכל לפי אזור וסגנון, עברו אל <a href=`"$($g267.url)`">מדריך המסעדות המומלצות בצפון</a>.</p>"
$newBack="`n<p>לעוד אפשרויות אוכל לפי אזור וסגנון, עברו אל <a href=`"$($g433.url)`">מדריך המסעדות בצפון ישראל</a>.</p>"
$n229=([string]$p229.content).Replace($oldBack,$newBack)
if($n229 -notmatch [regex]::Escape([string]$g433.url)){throw '229 canonical backlink missing'}
U $p229 $n229 'הפניית קישור ה־Hub אל מדריך המסעדות החזק יותר'
$n433=[string]$g433.content
$match=[regex]::Match($n433,'(?is)<p style="margin:0 0 1\.1em">מי שמתכנן יום שלם בגליל העליון.*?</p>')
if(-not $match.Success){throw '433 insertion point missing'}
$extra=$match.Value+'<p style="margin:0 0 1.1em">בגליל התחתון, בדרך לכנרת, אפשר לבדוק גם את <a href="'+$p229.url+'" style="color:#0b5fff;text-decoration:underline">שישו קפה במושב הזורעים</a> ולוודא לפני היציאה את שעות הפעילות העדכניות.</p>'
$n433=$n433.Substring(0,$match.Index)+$extra+$n433.Substring($match.Index+$match.Length)
U $g433 $n433 'קישור הקשרי מבסיס ה־Hub הקנוני לשישו קפה'
$updates|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-26-issue-45-restaurant-canonical-adjustment.json -Encoding utf8
$utf8=[Text.UTF8Encoding]::new($false);foreach($u in $updates){[IO.File]::WriteAllText((Join-Path (Resolve-Path memory/data).Path ("issue-45-$($u.id)-canonical-new.html")),[string]$u.new_content,$utf8)}
$updates|Select-Object id,title,reason|Format-Table -AutoSize -Wrap
