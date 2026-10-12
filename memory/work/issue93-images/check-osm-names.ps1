$boxes = @(
  @{ name='madatech'; bbox='34.9945,32.8085,34.9970,32.8110' },
  @{ name='akko'; bbox='35.0695,32.9225,35.0725,32.9250' }
)
foreach ($b in $boxes) {
  [xml]$map = (Invoke-WebRequest -Uri ('https://api.openstreetmap.org/api/0.6/map?bbox=' + $b.bbox) -TimeoutSec 30).Content
  "AREA $($b.name)"
  foreach ($obj in @($map.osm.node)+@($map.osm.way)) {
    $tags = @{}
    foreach ($t in $obj.tag) { $tags[$t.k] = $t.v }
    if (($tags.name -match 'שמריהו|Shmary|Shemer|Weiz|ויצמן|מבקרים|Visitor|גן הקסום|Enchanted') -or $tags.tourism -eq 'information' -or $tags.information -eq 'office') {
      '{0} {1} {2} {3} {4}' -f $obj.LocalName,$obj.id,$obj.lat,$obj.lon,($tags|ConvertTo-Json -Compress)
    }
  }
}
