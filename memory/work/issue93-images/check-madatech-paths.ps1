[xml]$map = (Invoke-WebRequest -Uri 'https://api.openstreetmap.org/api/0.6/map?bbox=34.9945,32.8085,34.9980,32.8110' -TimeoutSec 30).Content
$nodeById=@{}; foreach($n in $map.osm.node){$nodeById[$n.id]=$n}
foreach($wid in @('1555887773','1555887776','1555887777','217459581')) {
 $w=$map.osm.way | Where-Object {$_.id -eq $wid}
 "WAY $wid"
 foreach($nd in $w.nd){$n=$nodeById[$nd.ref]; '{0} {1} {2}' -f $n.id,$n.lat,$n.lon}
}
