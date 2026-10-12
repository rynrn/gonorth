import urllib.request,xml.etree.ElementTree as E
url='https://api.openstreetmap.org/api/0.6/map?bbox=34.9635,32.6695,34.9675,32.6725'
r=E.fromstring(urllib.request.urlopen(url,timeout=30).read())
for typ in ('node','way'):
    for x in r.findall(typ):
        tags={t.get('k'):t.get('v') for t in x.findall('tag')}
        if any(k in tags for k in ('tourism','amenity','parking','entrance','name')):
            if any(word in str(tags) for word in ('מערות','Mearot','Me\u2019arot','parking','information','entrance','חניה')) or 'entrance' in tags:
                print(typ,x.get('id'),x.get('lat'),x.get('lon'),{k:v for k,v in tags.items() if k in ('name','name:en','tourism','amenity','parking','entrance','building','access')})
