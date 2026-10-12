from pathlib import Path
p=Path('memory/scripts/issue83-after-qa.js')
s=p.read_text(encoding='utf-8')
a='out.push({label,state,requests:summary,thirdPartySentinel:requests.filter(x=>x.sentinel),errors});'
b=a+" fs.writeFileSync('memory/data/2026-10-04-issue-83-after-network.json',JSON.stringify(out,null,2));"
assert a in s
p.write_text(s.replace(a,b),encoding='utf-8')