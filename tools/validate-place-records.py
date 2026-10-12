#!/usr/bin/env python3
"""Read-only validation for GoNorth place records."""
import argparse, json, re, sys
from collections import defaultdict
from pathlib import Path
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")
ISRAEL_COUNTRIES={"israel","ישראל","il"}; NORTH_REGIONS={"צפון","north","northern district","מחוז הצפון"}
BLOCKED_COUNTRIES={"united states","usa","us","ארצות הברית"}; BLOCKED_REGIONS={"pennsylvania","פנסילבניה"}
def norm(v): return re.sub(r"[^\w\u0590-\u05ff]+","",str(v or "").casefold())
def pick(r,*keys):
    for k in keys:
        if r.get(k) not in (None,""): return r[k]
def validate(records):
    errors=[]; warnings=[]; names=defaultdict(list); ids=defaultdict(list)
    for i,r in enumerate(records):
        label=pick(r,"name","title","post_title") or f"record-{i+1}"; nk=norm(label)
        if nk: names[nk].append(label)
        else: errors.append({"code":"missing_name","record":label})
        country=str(pick(r,"country","geodir_post_country") or "").strip(); region=str(pick(r,"region","geodir_post_region") or "").strip()
        ck=country.casefold(); rk=region.casefold()
        if ck in BLOCKED_COUNTRIES: errors.append({"code":"blocked_country","record":label,"value":country})
        elif ck not in ISRAEL_COUNTRIES: errors.append({"code":"unsupported_country","record":label,"value":country})
        if rk in BLOCKED_REGIONS: errors.append({"code":"blocked_region","record":label,"value":region})
        elif rk not in NORTH_REGIONS: errors.append({"code":"unsupported_region","record":label,"value":region})
        lat=pick(r,"latitude","lat","geodir_post_latitude"); lng=pick(r,"longitude","lng","geodir_post_longitude")
        try:
            a,b=float(lat),float(lng)
            if not (29<=a<=34 and 34<=b<=36): errors.append({"code":"coordinates_outside_israel","record":label,"latitude":a,"longitude":b})
        except (TypeError,ValueError): errors.append({"code":"missing_or_invalid_coordinates","record":label})
        pid=norm(pick(r,"google_place_id","place_id"))
        if pid: ids[pid].append(label)
        elif not pick(r,"source_url","official_source_url"): warnings.append({"code":"missing_verification_source","record":label})
    for values in names.values():
        if len(values)>1: errors.append({"code":"duplicate_normalized_name","records":values})
    for values in ids.values():
        if len(values)>1: errors.append({"code":"duplicate_google_place_id","records":values})
    return {"ok":not errors,"records":len(records),"errors":errors,"warnings":warnings}
def main():
    p=argparse.ArgumentParser(); p.add_argument("input",type=Path); p.add_argument("--output",type=Path); a=p.parse_args()
    payload=json.loads(a.input.read_text(encoding="utf-8-sig")); records=payload.get("places",payload) if isinstance(payload,dict) else payload
    if not isinstance(records,list) or not all(isinstance(x,dict) for x in records): raise SystemExit("Input must be a JSON array or an object with a places array")
    result=validate(records); rendered=json.dumps(result,ensure_ascii=False,indent=2)
    if a.output: a.output.parent.mkdir(parents=True,exist_ok=True); a.output.write_text(rendered+"\n",encoding="utf-8")
    print(rendered); return 0 if result["ok"] else 1
if __name__=="__main__": sys.exit(main())


