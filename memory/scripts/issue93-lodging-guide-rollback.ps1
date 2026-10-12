param([switch]$Apply)
$ErrorActionPreference='Stop'
$base='memory/data/2026-10-08-issue-93-pastoral-publish'
$before=Get-Content ($base+'-97-guide-before.json') -Raw -Encoding UTF8|ConvertFrom-Json
$after=Get-Content ($base+'-97-guide-after.json') -Raw -Encoding UTF8|ConvertFrom-Json
$cfg=Get-Content '.claude/.mcp.json' -Raw -Encoding UTF8|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$h=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$current=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/posts/97?context=edit' -Headers $h -TimeoutSec 30
if($current.id -ne 97 -or $current.status -ne 'publish' -or $current.content.raw -cne $after.post_content){throw 'Guide changed since this update; stop'}
if(-not $Apply){Write-Output 'Guide rollback guard passed; no changes made';exit 0}
$body=@{content=$before.post_content}|ConvertTo-Json -Depth 3
$restored=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/posts/97' -Method Post -Headers $h -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body)) -TimeoutSec 30
if($restored.content.raw -cne $before.post_content){throw 'Guide restore verification failed'}
Write-Output 'Restored guide #97 previous content'
