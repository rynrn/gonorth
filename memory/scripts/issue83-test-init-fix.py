from pathlib import Path
p=Path('memory/scripts/issue83-after-qa.js')
s=p.read_text(encoding='utf-8')
a="if(initial!==null)await context.addInitScript(v=>localStorage.setItem('gn_cookie_ok',v),initial);"
b="if(initial!==null)await context.addInitScript(v=>{if(!sessionStorage.getItem('gn_privacy_qa_init')){localStorage.setItem('gn_cookie_ok',v);sessionStorage.setItem('gn_privacy_qa_init','1');}},initial);"
assert a in s
p.write_text(s.replace(a,b),encoding='utf-8')