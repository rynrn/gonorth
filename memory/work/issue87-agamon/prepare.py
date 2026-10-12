import json,re,pathlib
d=pathlib.Path(r"C:\dev\gonorth\memory\work\issue87-agamon")
j=json.loads((d/"post-432-before.json").read_text(encoding="utf-8-sig"))
old=j["post_content"]
pat=r'^<figure\b[^>]*><img\b[^>]*src="https://gonorth\.co\.il/wp-content/uploads/2026/07/place_photo-25\.jpg"[^>]*\s*/></figure>\s*'
newcredit='<p class="gn-photo-credit" dir="rtl">צילום אוויר של אגמון החולה: <a href="https://commons.wikimedia.org/wiki/File:124765_agmon_hula_PikiWiki_Israel.jpg">זאב שטיין / PikiWiki Israel</a>, ברישיון <a href="https://creativecommons.org/licenses/by/2.5/">CC BY 2.5</a>. התמונה הוקטנה לעמוד זה.</p>\n'
new,n=re.subn(pat,newcredit,old,count=1,flags=re.S)
assert n==1
assert 'place_photo-25.jpg' not in new
assert new.count('gn-photo-credit')==1
(d/"post-432-after.html").write_text(new,encoding="utf-8")
print(json.dumps({"old_chars":len(old),"new_chars":len(new),"replacements":n,"old_image_removed":"place_photo-25.jpg" not in new},ensure_ascii=False))

