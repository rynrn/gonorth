import json,urllib.request,gzip
from pathlib import Path
base='https://gonorth.co.il'
paths=[
'/wp-includes/css/dist/block-library/style.min.css?ver=7.1.2',
'/wp-content/plugins/geodirectory/vendor/ayecode/wp-ayecode-ui/assets-v5/js/bootstrap.bundle.min.js?ver=2.8.160',
'/wp-content/themes/astra/assets/js/minified/frontend.min.js?ver=4.13.0',
]
out=[]
for path in paths:
 req=urllib.request.Request(base+path,headers={'Accept-Encoding':'gzip, br','User-Agent':'GoNorth performance audit'})
 with urllib.request.urlopen(req,timeout=30) as r:
  raw=r.read()
  encoding=r.headers.get('Content-Encoding','none')
  expanded=len(gzip.decompress(raw)) if encoding=='gzip' else len(raw)
  out.append({'path':path.split('?')[0],'status':r.status,'encoding':encoding,'cache_control':r.headers.get('Cache-Control'),'etag':bool(r.headers.get('ETag')),'bytes_wire':len(raw),'bytes_expanded':expanded})
Path('memory/data/2026-10-06-issue-86-static-headers.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(out,ensure_ascii=False))
