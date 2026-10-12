import json, urllib.parse, urllib.request
names=[f'File:מלון פסטורל בכפר בלום {n:02}.jpg' for n in (15,26,27)]
q={'action':'query','titles':'|'.join(names),'prop':'imageinfo','iiprop':'url','iiurlwidth':'800','format':'json'}
u='https://commons.wikimedia.org/w/api.php?'+urllib.parse.urlencode(q)
headers={'User-Agent':'GoNorthResearch/1.0 (public media audit)'}
d=json.load(urllib.request.urlopen(urllib.request.Request(u,headers=headers),timeout=30))
for p in d['query']['pages'].values():
    if 'imageinfo' not in p: continue
    n=p['title'].split(' ')[-1].split('.')[0]
    out=f'memory/work/issue93-pastoral-{n}.jpg'
    r=urllib.request.Request(p['imageinfo'][0]['thumburl'],headers=headers)
    with urllib.request.urlopen(r,timeout=30) as response, open(out,'wb') as f: f.write(response.read())
    print(n,out)
