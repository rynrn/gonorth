from pathlib import Path
src=Path(r'C:\dev\gonorth\memory\work\issue86-editorial-dom-audit.js').read_text(encoding='utf-8')
src=src.replace('const page=await context.newPage();','const page=await context.newPage();\n  let pageErrors=[];page.on(\'pageerror\',e=>pageErrors.push(e.message.slice(0,120)));')
src=src.replace('const item=items[index++];','const item=items[index++];pageErrors=[];')
src=src.replace('await page.waitForTimeout(400);','await page.waitForTimeout(400);\n    await page.evaluate(()=>window.scrollTo(0,document.body.scrollHeight));\n    await page.waitForTimeout(350);')
src=src.replace('commerceNodes:document.querySelectorAll','docWidth:document.documentElement.scrollWidth,\n      h1:!!document.querySelector(\'h1\'),\n      footer:!!document.querySelector(\'footer\'),\n      loadedImages:[...document.images].filter(x=>x.complete&&x.naturalWidth>0).length,\n      placeLinks:document.querySelectorAll(\'main a[href*="/places/"]\').length,\n      commerceNodes:document.querySelectorAll')
src=src.replace('status:response.status(),...state','status:response.status(),pageErrors:[...pageErrors],...state')
for width in (390,1280):
    s=src.replace('viewport:{width:390,height:844}',f'viewport:{{width:{width},height:844}}')
    s=s.replace('2026-10-06-issue-86-editorial-dom-audit.json',f'2026-10-06-issue-86-all25-live-{width}.json')
    dest=Path(fr'C:\dev\gonorth\memory\work\issue86-all25-live-{width}.js')
    dest.write_text(s,encoding='utf-8')
    print(dest.name)
