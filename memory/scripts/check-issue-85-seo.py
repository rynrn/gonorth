"""Read-only public SEO regression for GoNorth issue #85."""
import datetime as dt
import html.parser
import json
import re
import urllib.error
import urllib.request
from pathlib import Path
from urllib.parse import urljoin
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[2]
BASE = 'https://gonorth.co.il'
TARGETS = [
    '/places/', '/tours-kayaks-north-israel/', '/hanukkah-north-israel/',
    '/sukkot-batzafon/', '/rosh-hashanah-batzafon/',
    '/coffee-carts-cafes-north-israel/', '/open-saturday/',
    '/yekavim-batzafon/', '/shavuot-north-israel/',
    '/places/category/ochel/', '/places/category/batei-kafe/', '/places/category/uncategorized/',
]

class Parser(html.parser.HTMLParser):
    def __init__(self):
        super().__init__()
        self.meta = []
        self.links = []
        self.h1 = 0
        self.scripts = []
        self.in_script = False
        self.script_text = ''
        self.in_title = False
        self.title = ''
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'title': self.in_title = True
        if tag == 'meta': self.meta.append(a)
        if tag == 'link': self.links.append(a)
        if tag == 'h1': self.h1 += 1
        if tag == 'script' and a.get('type') == 'application/ld+json':
            self.in_script = True
            self.script_text = ''
    def handle_data(self, text):
        if self.in_script: self.script_text += text
        if self.in_title: self.title += text
    def handle_endtag(self, tag):
        if tag == 'title': self.in_title = False
        if tag == 'script' and self.in_script:
            try: self.scripts.append(json.loads(self.script_text))
            except json.JSONDecodeError: self.scripts.append('invalid-json')
            self.in_script = False
    def named(self, name):
        return [a.get('content') for a in self.meta if a.get('name', '').lower() == name.lower()]
    def canonical(self):
        return [a.get('href') for a in self.links if 'canonical' in a.get('rel', '').split()]

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, request, fp, code, msg, headers, newurl):
        return None

def fetch(url, *, follow=True):
    opener = urllib.request.build_opener() if follow else urllib.request.build_opener(NoRedirect)
    req = urllib.request.Request(url, headers={'User-Agent':'GoNorth-SEO-regression/1.0'})
    try: response = opener.open(req, timeout=20)
    except urllib.error.HTTPError as error: response = error
    with response:
        return response.status, response.url, dict(response.headers), response.read().decode('utf-8', 'replace')

def main():
    result = {'checked_at':dt.datetime.now(dt.timezone.utc).isoformat(), 'issue':85,
              'scope':'public HTTP/HTML only', 'pages':[], 'redirects':[], 'sitemaps':[], 'robots':{}, 'failures':[]}
    for path in TARGETS:
        url=urljoin(BASE,path)
        try:
            status, final, headers, body = fetch(url)
            parser=Parser(); parser.feed(body)
            nodes=[]
            for item in parser.scripts:
                if isinstance(item,dict): nodes.extend(item.get('@graph',[item]))
                elif isinstance(item,list): nodes.extend(item)
            crumbs=[n.get('itemListElement',[]) for n in nodes if isinstance(n,dict) and n.get('@type')=='BreadcrumbList']
            record={'url':url,'status':status,'final_url':final,'canonical':parser.canonical(),
                    'robots_meta':parser.named('robots'),'x_robots_tag':headers.get('X-Robots-Tag'),
                    'title':parser.title.strip(), 'meta_description':parser.named('description'),
                    'h1_count':parser.h1,'jsonld_count':len(parser.scripts),
                    'schema_types':[n.get('@type') for n in nodes if isinstance(n,dict)],
                    'breadcrumb_labels':[[part.get('name') for part in trail] for trail in crumbs],
                    'jsonld_invalid':sum(x == 'invalid-json' for x in parser.scripts if isinstance(x,str))}
            result['pages'].append(record)
            if status != 200: result['failures'].append({'url':url,'field':'http_status','actual':status})
            if path not in ('/places/category/ochel/','/places/category/batei-kafe/','/places/category/uncategorized/') and parser.canonical() != [url]:
                result['failures'].append({'url':url,'field':'canonical','actual':parser.canonical()})
            if path == '/places/category/uncategorized/' and not any('noindex' in str(x).lower() for x in parser.named('robots')):
                result['failures'].append({'url':url,'field':'empty_category_robots','actual':parser.named('robots')})
            if path == '/places/' and any('מאות' in str(x) for x in parser.named('description')):
                result['failures'].append({'url':url,'field':'meta_description','actual':'contains obsolete hundreds claim'})
            if record['jsonld_invalid']:
                result['failures'].append({'url':url,'field':'jsonld','actual':'invalid JSON'})
        except Exception as error:
            result['failures'].append({'url':url,'field':'fetch','actual':type(error).__name__})
    old=json.loads((ROOT/'memory/data/2026-09-26-issue-73-redirect-qa.json').read_text(encoding='utf-8-sig'))
    for item in old:
        try:
            status, _, headers, _ = fetch(item['source'],follow=False)
            location=headers.get('Location')
            result['redirects'].append({'id':item['ID'],'source':item['source'],'status':status,'location':location,'expected':item['expected']})
            if status != 301 or location != item['expected']:
                result['failures'].append({'url':item['source'],'field':'redirect','actual':f'{status} {location}'})
        except Exception as error:
            result['failures'].append({'url':item['source'],'field':'redirect_fetch','actual':type(error).__name__})
    for path in ['/sitemap_index.xml','/post-sitemap.xml','/page-sitemap.xml','/gd_place-sitemap.xml']:
        url=urljoin(BASE,path)
        try:
            status, _, _, body=fetch(url)
            root=ET.fromstring(body)
            locs=[x.text for x in root.findall('.//{*}loc')]
            result['sitemaps'].append({'url':url,'status':status,'loc_count':len(locs),'page_urls':[x for x in locs if x and x.startswith(BASE) and '/wp-content/' not in x]})
            if status != 200: result['failures'].append({'url':url,'field':'sitemap_status','actual':status})
        except Exception as error:
            result['failures'].append({'url':url,'field':'sitemap_parse','actual':type(error).__name__})
    try:
        status, _, _, body=fetch(BASE+'/robots.txt')
        result['robots']={'url':BASE+'/robots.txt','status':status,'content':body}
        if status != 200 or 'Sitemap: https://gonorth.co.il/sitemap_index.xml' not in body:
            result['failures'].append({'url':BASE+'/robots.txt','field':'robots','actual':'missing expected sitemap'})
    except Exception as error:
        result['failures'].append({'url':BASE+'/robots.txt','field':'fetch','actual':type(error).__name__})
    out=ROOT/'memory/data/2026-10-02-issue-85-seo-regression.json'
    out.write_text(json.dumps(result,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    print(json.dumps({'pages':len(result['pages']),'redirects':len(result['redirects']),
                      'sitemaps':len(result['sitemaps']),'failures':result['failures'],'evidence':str(out)},ensure_ascii=False))
if __name__ == '__main__': main()
