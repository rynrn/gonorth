import json,re,urllib.request,pathlib
url='https://www.waze.com/live-map/directions?to=place.ws.il.84305.1'
s=urllib.request.urlopen(urllib.request.Request(url,headers={'User-Agent':'Mozilla/5.0'}),timeout=30).read().decode('utf-8','replace')
m=re.search('latLng":' + r'(\{[^}]+\})' + ',"title":"([^"]+)"',s)
if not m: raise RuntimeError('Named address destination not found')
coords=json.loads(m.group(1))
title=m.group(2)
if title!='1 ויצמן': raise RuntimeError('Unexpected Waze destination title')
data={'source':url,'checked_at':'2026-10-06','kind':'operator-recommended street-address navigation destination, not a verified door','title':title,'latitude':str(coords['lat']),'longitude':str(coords['lng'])}
pathlib.Path('memory/data/2026-10-06-issue-93-knights-waze-address.json').write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(data,ensure_ascii=True))
