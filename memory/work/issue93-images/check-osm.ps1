$boxes = @(
  @{ name='madatech'; bbox='34.9945,32.8085,34.9970,32.8110' },
  @{ name='akko'; bbox='35.0695,32.9225,35.0725,32.9250' }
)
foreach ($b in $boxes) {
  try {
    $uri = 'https://api.openstreetmap.org/api/0.6/map?bbox=' + $b.bbox
    [xml]$map = (Invoke-WebRequest -Uri $uri -TimeoutSec 30).Content
    "AREA $($b.name) nodes $($map.osm.node.Count)"
    foreach ($n in $map.osm.node) {
      $tags = @{}
      foreach ($t in $n.tag) { $tags[$t.k] = $t.v }
      if ($tags.ContainsKey('entrance') -or $tags.name -match 'מדע|Madatech|אבירים|Knights|מבקרים|Visitor') {
        '{0} {1} {2} {3}' -f $n.id,$n.lat,$n.lon,($tags | ConvertTo-Json -Compress)
      }
    }
  } catch {
    "ERROR $($b.name): $($_.Exception.Message)"
  }
}
