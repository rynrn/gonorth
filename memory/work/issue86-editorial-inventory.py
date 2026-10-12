import json, urllib.request
base='https://gonorth.co.il/wp-json/wp/v2/'
rows=[]
for kind in ('posts','pages'):
    page=1
    while True:
        url=f'{base}{kind}?per_page=100&page={page}&_fields=id,link,slug,status'
        req=urllib.request.Request(url,headers={'User-Agent':'GoNorth performance audit'})
        try:
            with urllib.request.urlopen(req,timeout=30) as response:
                chunk=json.load(response)
        except urllib.error.HTTPError as exc:
            if exc.code==400 and page>1:
                break
            raise
        rows.extend([{'type':kind[:-1],'id':x['id'],'url':x['link'],'slug':x['slug']} for x in chunk])
        if len(chunk)<100: break
        page+=1
with open('memory/data/2026-10-06-issue-86-editorial-inventory.json','w',encoding='utf-8') as f:
    json.dump(rows,f,ensure_ascii=False,indent=2)
print('posts',sum(x['type']=='post' for x in rows),'pages',sum(x['type']=='page' for x in rows))
