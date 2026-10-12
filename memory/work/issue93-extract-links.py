import re, html
s=open('memory/work/issue93-nahal-mearot-official.html',encoding='utf-8').read()
links=re.findall(r'href=["\x27]([^"\x27]+)',s,re.I)
for x in links:
    if any(y in x.lower() for y in ('waze','maps.google','goo.gl/maps','maps.app','google.com/maps')):
        print(html.unescape(x))
