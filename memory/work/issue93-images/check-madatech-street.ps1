[xml]$map = (Invoke-WebRequest -Uri 'https://api.openstreetmap.org/api/0.6/map?bbox=34.9945,32.8085,34.9980,32.8110' -TimeoutSec 30).Content
$nodeById = @{}
foreach($n in $map.osm.node){$nodeById[$n.id]=$n}
$street=@($map.osm.way | Where-Object { @($_.tag | Where-Object {$_.k -eq 'name' -and $_.v -eq 'שמריהו לוין'}).Count -gt 0 })
foreach($w in $street) { "STREET $($w.id)"; foreach($nd in $w.nd){$n=$nodeById[$nd.ref]; '{0} {1}' -f $n.lat,$n.lon} }
foreach($id in @('14153040372','14153040373','14153040379')){$n=$nodeById[$id]; "ENTRANCE $id $($n.lat) $($n.lon)"}
