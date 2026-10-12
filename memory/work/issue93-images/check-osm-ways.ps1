$boxes = @(
  @{ name='madatech'; bbox='34.9945,32.8085,34.9970,32.8110' },
  @{ name='akko'; bbox='35.0695,32.9225,35.0725,32.9250' }
)
foreach ($b in $boxes) {
  [xml]$map = (Invoke-WebRequest -Uri ('https://api.openstreetmap.org/api/0.6/map?bbox=' + $b.bbox) -TimeoutSec 30).Content
  "AREA $($b.name)"
  foreach ($w in $map.osm.way) {
    $tags = @{}
    foreach ($t in $w.tag) { $tags[$t.k] = $t.v }
    if ($tags.name -match 'מדע|Madatech|אבירים|Knights|מבקרים|Visitor|טכניון|Technion' -or $tags.tourism -eq 'museum') {
      $ids = @($w.nd | ForEach-Object { $_.ref })
      '{0} {1} nodes={2}' -f $w.id,($tags|ConvertTo-Json -Compress),($ids -join ',')
    }
  }
  foreach ($id in @('14153040372','14153040373','14153040379','6937823183','6937841488')) {
    $members = @($map.osm.way | Where-Object { @($_.nd.ref) -contains $id })
    foreach ($w in $members) {
      $tags = @{}
      foreach ($t in $w.tag) { $tags[$t.k] = $t.v }
      'entrance-node={0} way={1} {2}' -f $id,$w.id,($tags|ConvertTo-Json -Compress)
    }
  }
}
