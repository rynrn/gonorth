import urllib.request,xml.etree.ElementTree as E,json
r=E.fromstring(urllib.request.urlopen('https://api.openstreetmap.org/api/0.6/map?bbox=34.9635,32.6695,34.9675,32.6725',timeout=30).read())
n={x.get('id'):(float(x.get('lat')),float(x.get('lon'))) for x in r.findall('node')}
out=[]
for wid in ('117880693','76816007'):
    w=next(x for x in r.findall('way') if x.get('id')==wid)
    p=[n[nd.get('ref')] for nd in w.findall('nd')]
    tags={t.get('k'):t.get('v') for t in w.findall('tag')}
    out.append({'way':wid,'vertices':len(p)-1,'center_lat':round(sum(a for a,b in p[:-1])/(len(p)-1),7),'center_lon':round(sum(b for a,b in p[:-1])/(len(p)-1),7),'tags':tags})
print(json.dumps(out,ensure_ascii=False))
