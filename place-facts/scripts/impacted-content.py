#!/usr/bin/env python3
"""Find editorial pages that may need review after an approved place-fact change."""
import argparse, json, re, sys
from pathlib import Path
if hasattr(sys.stdout, "reconfigure"): sys.stdout.reconfigure(encoding="utf-8")
def normalize(value): return re.sub(r"[^w֐-׿]+","",str(value or "").casefold())
def find_impacts(changes,rows):
    impacts=[]
    for change in changes:
        place_id=int(change["wordpress_id"]); name_key=normalize(change.get("name")); place_url=str(change.get("url") or "")
        for row in rows:
            if int(row.get("id",-1))==place_id and row.get("type")=="gd_place": continue
            content=str(row.get("content") or "")
            if (place_url and place_url in content) or (name_key and name_key in normalize(content)):
                impacts.append({"wordpress_id":place_id,"field":change.get("field"),"content_id":row.get("id"),"content_type":row.get("type"),"title":row.get("title"),"url":row.get("url")})
    return impacts
def main():
    p=argparse.ArgumentParser(); p.add_argument("--changes",type=Path,required=True); p.add_argument("--content-export",type=Path,required=True); p.add_argument("--output",type=Path,required=True); a=p.parse_args()
    changes=json.loads(a.changes.read_text(encoding="utf-8-sig")); content=json.loads(a.content_export.read_text(encoding="utf-8-sig")); rows=content.get("rows",content)
    result={"changes":len(changes),"impacts":find_impacts(changes,rows)}; a.output.parent.mkdir(parents=True,exist_ok=True)
    a.output.write_text(json.dumps(result,ensure_ascii=False,indent=2)+"\n",encoding="utf-8")
    print(json.dumps({"changes":result["changes"],"impacts":len(result["impacts"])},ensure_ascii=False))
if __name__=="__main__": main()
