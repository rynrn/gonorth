import json, re, time, urllib.request, gzip
from pathlib import Path
urls={
'archive':'https://gonorth.co.il/places/',
'place':'https://gonorth.co.il/places/%d7%a7%d7%99%d7%90%d7%a7-%d7%9b%d7%a0%d7%a8%d7%aa/',
'article':'https://gonorth.co.il/tours-kayaks-north-israel/',
}
rows=[]
for name,url in urls.items():
 for i in range(3):
  req=urllib.request.Request(url,headers={'Accept-Encoding':'gzip, br','User-Agent':'GoNorth performance audit'})
  start=time.perf_counter()
  with urllib.request.urlopen(req,timeout=40) as response:
   ttfb=round((time.perf_counter()-start)*1000)
   raw=response.read()
   encoding=response.headers.get('Content-Encoding','none')
   decoded=gzip.decompress(raw) if encoding=='gzip' else raw
   body=decoded.decode('utf-8','replace')
   assets=re.findall(r'(?:href|src)="(https://gonorth\.co\.il/[^"]+\.(?:css|js)(?:\?[^"]*)?)"',body)
   rows.append({'template':name,'run':i+1,'status':response.status,'ttfb_ms':ttfb,'transfer_bytes':len(raw),'html_decoded_bytes':len(decoded),'content_encoding':encoding,'cache_control':response.headers.get('Cache-Control'),'asset_urls':assets if i==0 else None})
out={'checked_at':'2026-10-06','method':'fresh HTTPS GET, gzip/br accepted, three serial runs; TTFB includes network/DNS/TLS','rows':rows}
Path('memory/data/2026-10-06-issue-86-after-editorial.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
for name in urls:
 sample=[r for r in rows if r['template']==name]
 print(name,'TTFB median',sorted(x['ttfb_ms'] for x in sample)[1],'transfer KB median',round(sorted(x['transfer_bytes'] for x in sample)[1]/1000),'assets',len(sample[0]['asset_urls']))

