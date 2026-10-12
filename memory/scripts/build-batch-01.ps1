$ErrorActionPreference='Stop'
$backup=Get-Content memory/data/2026-09-17-batch-01-before.json -Raw | ConvertFrom-Json
$plan=@()
foreach($id in 244,246,248) {
 $record=$backup.records | Where-Object id -eq $id
 $draft=Get-ChildItem memory/drafts -Filter "$id-*.md" | Select-Object -First 1
 $text=Get-Content $draft.FullName -Raw
 $body=[regex]::Match($text,'(?s)## תוכן מוצע\s+(.*?)\s+## מקור ובקרת עובדות').Groups[1].Value
 if (-not $body) { throw "Missing approved content $id" }
 $parts=@()
 foreach($block in ($body -split '\r?\n\s*\r?\n')) {
  $block=$block.Trim()
  $heading=$block.StartsWith('### ')
  if($heading){$block=$block.Substring(4)}
  $block=[System.Net.WebUtility]::HtmlEncode($block)
  $block=[regex]::Replace($block,'\[([^\]]+)\]\((https://gonorth\.co\.il/[^)]+)\)','<a href="$2">$1</a>')
  $parts+=if($heading){'<h2>'+$block+'</h2>'}else{'<p>'+$block+'</p>'}
 }
 $plan += [pscustomobject]@{id=$id;endpoint=$record.endpoint;content=($parts -join "`n`n")}
}
$reverse=@(
 @{id=268;place=244;anchor='יקב בן זמרה';sentence='מתכננים עצירה בכרם בן זמרה? קראו על אפשרויות הביקור ב{LINK} לפני התיאום.';before='<h2>מקורות ועדכונים</h2>'},
 @{id=270;place=246;anchor='אינדי פארק';sentence='לשילוב פעילויות באותה עצירה, בדקו את פרטי {LINK} ואת התאמת הפעילויות להרכב המשפחה.';before='<h2>מקורות ועדכונים</h2>'},
 @{id=95;place=248;anchor='תצפית כוכבים של מטאור';sentence='נשארים באזור גם בערב? אפשר לבדוק {LINK} לפי המועדים הזמינים.';before='<h2>טיפים לטיול מוצלח בגולן</h2>'}
)
foreach($item in $reverse){
 $record=$backup.records | Where-Object id -eq $item.id
 $place=$backup.records | Where-Object id -eq $item.place
 if(-not $record.content.Contains($item.before)){throw "Missing insertion point $($item.id)"}
 $link='<a href="'+[System.Net.WebUtility]::HtmlEncode($place.url)+'">'+$item.anchor+'</a>'
 $paragraph='<p>'+$item.sentence.Replace('{LINK}',$link)+'</p>'
 $content=$record.content.Replace($item.before,$paragraph+"`n`n"+$item.before)
 $plan += [pscustomobject]@{id=$item.id;endpoint=$record.endpoint;content=$content}
}
$plan | ConvertTo-Json -Depth 5 | Set-Content memory/data/2026-09-17-batch-01-payloads.json -Encoding utf8
$plan | ForEach-Object { [pscustomobject]@{id=$_.id;chars=$_.content.Length;h2=([regex]::Matches($_.content,'<h2>')).Count} } | ConvertTo-Json
