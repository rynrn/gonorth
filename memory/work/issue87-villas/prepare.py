import json,re,pathlib,hashlib
d=pathlib.Path(r"C:\dev\gonorth\memory\work\issue87-villas")
j=json.loads((d/"post-431-before.json").read_text(encoding="utf-8-sig"))
old=j["post_content"]
pat=r'^<figure\b[^>]*><img\b[^>]*src="https://gonorth\.co\.il/wp-content/uploads/2026/07/place_photo-33\.jpg"[^>]*\s*/></figure>\s*'
credit='<p class="gn-photo-credit" dir="rtl">בתמונה: מתחם החוץ של וילת המלך שלמה בעבדון, כדוגמה לווילה בגליל המערבי. צילום: <a href="https://commons.wikimedia.org/wiki/File:King_Solomon_Villa_exterior_at_dusk,_Avdon,_Israel.jpg">איתי סולימני</a>, <a href="https://creativecommons.org/licenses/by-sa/4.0/">CC BY-SA 4.0</a>. התמונה הוקטנה ונחתכה לתצוגה; הרישיון חל גם על גרסאותיה המותאמות.</p>\n'
new,n=re.subn(pat,credit,old,count=1,flags=re.S)
assert n==1 and 'place_photo-33.jpg' not in new and new.count('gn-photo-credit')==1
(d/"post-431-after.html").write_text(new,encoding="utf-8")
print(json.dumps({"old_sha256":hashlib.sha256(old.encode()).hexdigest(),"new_sha256":hashlib.sha256(new.encode()).hexdigest(),"old_chars":len(old),"new_chars":len(new),"replacements":n},ensure_ascii=False))

