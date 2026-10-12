from pathlib import Path
p=Path('memory/scripts/issue83-full-inventory.py')
s=p.read_text(encoding='utf-8')
s=s.replace('import json,requests','import json,urllib.request')
s=s.replace("  r=requests.get(row['url'],timeout=25)\n  return {'id':row['id'],'status':r.status_code,'choice':'id=\"gn-cookie-reject\"' in r.text,'gated':'gn-consent-granted' in r.text}","  with urllib.request.urlopen(row['url'],timeout=25) as r:\n   body=r.read().decode('utf-8','replace')\n   return {'id':row['id'],'status':r.status,'choice':'id=\"gn-cookie-reject\"' in body,'gated':'gn-consent-granted' in body}")
p.write_text(s,encoding='utf-8')