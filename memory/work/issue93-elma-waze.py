import json,re,urllib.request,pathlib
url='https://www.waze.com/he/live-map/directions/il/%D7%9E%D7%97%D7%95%D7%96-%D7%97%D7%99%D7%A4%D7%94/%D7%96%D7%9B%D7%A8%D7%95%D7%9F-%D7%99%D7%A2%D7%A7%D7%91/%D7%9E%D7%9C%D7%95%D7%9F-%D7%90%D7%9C%D7%9E%D7%90?to=place.ChIJ4yveaisKHRURCtflCqcmv4I'
s=urllib.request.urlopen(urllib.request.Request(url,headers={'User-Agent':'Mozilla/5.0'}),timeout=30).read().decode('utf-8','replace')
for m in re.finditer('latLng":' + r'(\{[^}]+\})' + ',"title":"([^"]+)"',s):
    print(json.dumps({'coordinates':json.loads(m.group(1)),'title':m.group(2)},ensure_ascii=True))

