from pathlib import Path
p=Path(r'C:\dev\gonorth\memory\work\issue86-woo-simulation.js')
s=p.read_text(encoding='utf-8')
start=s.index('const blockedCss=new Set([')
end=s.index(']);',start)+3
s=s[:start]+"""const blockedCss=new Set([
 '/wp-content/cache/autoptimize/autoptimize_single_f744e4881044ddcf68ff999850aa6f7f.php'
]);"""+s[end:]
s=s.replace('without_woo','without_geo')
s=s.replace("path.includes('/plugins/woocommerce/')||blockedCss.has(path)","path.includes('/plugins/geodirectory/assets/aui/js/geodirectory.min.js')||path.includes('bootstrap.bundle.min.js')||blockedCss.has(path)")
s=s.replace("issue86-'+width+'-'+mode+'.png","issue86-geo-'+width+'-'+mode+'.png")
s=s.replace('2026-10-06-issue-86-woo-simulation.json','2026-10-06-issue-86-geo-simulation.json')
Path(r'C:\dev\gonorth\memory\work\issue86-geo-simulation.js').write_text(s,encoding='utf-8')
print('ready')
