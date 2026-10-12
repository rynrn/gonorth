import json,urllib.request
from concurrent.futures import ThreadPoolExecutor,as_completed
from pathlib import Path
items=json.loads(Path('memory/data/2026-10-04-issue-83-public-urls.json').read_text(encoding='utf-8-sig'))
def check(row):
 try:
  with urllib.request.urlopen(row['url'],timeout=25) as r:
   body=r.read().decode('utf-8','replace')
   return {'id':row['id'],'status':r.status,'choice':'id="gn-cookie-reject"' in body,'gated':'gn-consent-granted' in body}
 except Exception as e:
  return {'id':row['id'],'status':'error','error':type(e).__name__}
with ThreadPoolExecutor(max_workers=8) as pool:
 rows=list(pool.map(check,items))
failures=[r for r in rows if r['status']!=200 or not r.get('choice') or not r.get('gated')]
out={'checked':len(rows),'passed':len(rows)-len(failures),'failures':failures}
Path('memory/data/2026-10-04-issue-83-full-inventory-regression.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(out,ensure_ascii=False))