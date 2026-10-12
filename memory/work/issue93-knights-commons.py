import json, pathlib, urllib.parse, urllib.request
names=[
'File:אולמות האבירים עכו.jpg',
'File:אולמות האבירים. אולם, מבצר עכו.jpg',
'File:Acre - Akko 2 - Knights Halls - entrance (6658880249).jpg',
]
root=pathlib.Path('memory/work/issue93-knights')
root.mkdir(parents=True,exist_ok=True)
records=[]
for ix,name in enumerate(names,1):
 q=urllib.parse.urlencode({'action':'query','titles':name,'prop':'imageinfo','iiprop':'url|extmetadata|size','iiurlwidth':640,'format':'json'})
 req=urllib.request.Request('https://commons.wikimedia.org/w/api.php?'+q,headers={'User-Agent':'GoNorthEditor/1.0 (editorial research)'})
 data=json.load(urllib.request.urlopen(req,timeout=30))
 page=next(iter(data['query']['pages'].values()))
 info=page.get('imageinfo',[{}])[0]
 if not info or not info.get('thumburl'): raise RuntimeError(f'No image info for candidate {ix}')
 out=root/f'candidate-{ix}.jpg'
 urllib.request.urlretrieve(info['thumburl'],out)
 ext=info.get('extmetadata',{})
 records.append({'candidate':ix,'title':name,'description_url':info.get('descriptionurl'),'original_url':info.get('url'),'thumbnail_file':str(out),'width':info.get('width'),'height':info.get('height'),'mime':info.get('mime'),'license':ext.get('LicenseShortName',{}).get('value'),'license_url':ext.get('LicenseUrl',{}).get('value'),'artist':ext.get('Artist',{}).get('value'),'date':ext.get('DateTimeOriginal',{}).get('value')})
(root/'metadata.json').write_text(json.dumps(records,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps([{k:v for k,v in r.items() if k not in ('original_url','artist')} for r in records],ensure_ascii=False))
