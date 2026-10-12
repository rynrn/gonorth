from pathlib import Path
p=Path('memory/work/2026-10-06-issue-88/browser-qa.js')
s=p.read_text(encoding='utf-8-sig')
s=s.replace("getComputedStyle(document.querySelector('.gn-guide-toc ol')).columnCount", "document.querySelector('.gn-guide-toc ol')?getComputedStyle(document.querySelector('.gn-guide-toc ol')).columnCount:null")
p.write_text(s,encoding='utf-8')
