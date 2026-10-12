from pathlib import Path
s=Path(r'C:\dev\gonorth\memory\work\issue86-all-posts-simulation.js').read_text(encoding='utf-8')
start=s.index('const blockedCss=new Set(')
end=s.index(';',start)+1
s=s[:start]+"""const blockedCss=new Set(['/wp-content/cache/autoptimize/autoptimize_single_f744e4881044ddcf68ff999850aa6f7f.php']);"""+s[end:]
s=s.replace(".filter(x=>x.type==='post')",".filter(x=>x.type==='post'||x.id===436)")
s=s.replace("path.includes('/plugins/woocommerce/')||blockedCss.has(path)","path.includes('/plugins/geodirectory/assets/aui/js/geodirectory.min.js')||path.includes('bootstrap.bundle.min.js')||blockedCss.has(path)")
s=s.replace("await page.waitForTimeout(450);const state","await page.waitForTimeout(450);await page.evaluate(()=>window.scrollTo(0,document.body.scrollHeight));await page.waitForTimeout(250);const state")
for width in (390,1280):
    t=s.replace('viewport:{width:390,height:844}',f'viewport:{{width:{width},height:844}}')
    t=t.replace('out.filter(x=>x.width>390)',f'out.filter(x=>x.width>{width})')
    t=t.replace('2026-10-06-issue-86-all-posts-simulation.json',f'2026-10-06-issue-86-geo-all-posts-simulation-{width}.json')
    Path(fr'C:\dev\gonorth\memory\work\issue86-geo-all-posts-{width}.js').write_text(t,encoding='utf-8')
print('ready')
