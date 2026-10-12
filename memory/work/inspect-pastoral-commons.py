import json, re, html, urllib.parse, urllib.request
q={'action':'query','generator':'categorymembers','gcmtitle':'Category:Pastoral Hotel','gcmlimit':'100','gcmtype':'file','prop':'imageinfo','iiprop':'extmetadata','format':'json'}
u='https://commons.wikimedia.org/w/api.php?'+urllib.parse.urlencode(q)
r=urllib.request.Request(u,headers={'User-Agent':'GoNorthResearch/1.0 (public media audit)'})
d=json.load(urllib.request.urlopen(r,timeout=30))
for p in sorted(d['query']['pages'].values(), key=lambda x:x['title']):
    if 'מלון פסטורל בכפר בלום' not in p['title']:
        continue
    md=p.get('imageinfo',[{}])[0].get('extmetadata',{})
    desc=html.unescape(re.sub('<[^>]+>',' ',md.get('ImageDescription',{}).get('value',''))).strip()
    date=md.get('DateTimeOriginal',{}).get('value','')
    print((p['title']+' | '+date+' | '+desc[:120]).encode('ascii','backslashreplace').decode())
