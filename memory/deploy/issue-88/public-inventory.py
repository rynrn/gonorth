from pathlib import Path
import json, urllib.request, urllib.error
from concurrent.futures import ThreadPoolExecutor
items=json.loads(Path('memory/data/2026-10-04-issue-83-public-urls.json').read_text(encoding='utf-8-sig'))
def check(row):
    try:
        with urllib.request.urlopen(row['url'],timeout=25) as response:
            body=response.read().decode('utf-8','replace')
            return {'id':row['id'],'url':row['url'],'status':response.status,'critical':'שגיאה קריטית' in body,'rtl':'dir="rtl"' in body or 'class="rtl ' in body or '<html dir="rtl"' in body}
    except Exception as error:
        return {'id':row['id'],'url':row['url'],'status':type(error).__name__,'critical':False,'rtl':False}
with ThreadPoolExecutor(max_workers=8) as pool:
    rows=list(pool.map(check,items))
failures=[r for r in rows if r['status']!=200 or r['critical']]
out={'checked':len(rows),'passed':len(rows)-len(failures),'failures':failures}
Path('memory/work/2026-10-06-issue-88/public-inventory.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(out,ensure_ascii=False))
