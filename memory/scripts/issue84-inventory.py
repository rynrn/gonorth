from pathlib import Path
import json, re, urllib.request
from concurrent.futures import ThreadPoolExecutor
items=json.loads(Path('memory/data/2026-10-04-issue-83-public-urls.json').read_text(encoding='utf-8-sig'))
def check(row):
    try:
        with urllib.request.urlopen(row['url'],timeout=25) as response:
            body=response.read().decode('utf-8','replace')
            match=re.search(r'<script[^>]+id="gonorth-place-analytics-js"[^>]+src="([^"]+)"',body)
            src=match.group(1) if match else ''
            return {'id':row['id'],'status':response.status,'analytics_current':'ver=1.18.9' in src,'consent_choice':'id="gn-cookie-reject"' in body}
    except Exception as error:
        return {'id':row['id'],'status':type(error).__name__,'analytics_current':False,'consent_choice':False}
with ThreadPoolExecutor(max_workers=8) as pool:
    rows=list(pool.map(check,items))
failures=[r for r in rows if r['status']!=200 or not r['analytics_current'] or not r['consent_choice']]
out={'checked':len(rows),'passed':len(rows)-len(failures),'failures':failures}
Path('memory/data/2026-10-06-issue-84-inventory.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(out,ensure_ascii=False))
