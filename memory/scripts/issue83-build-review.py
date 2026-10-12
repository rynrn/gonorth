from pathlib import Path
import difflib
pre=Path('memory/data/2026-10-04-issue-83-prechange')
post=Path('memory/scripts/issue83-staged')
parts=[]
for name in ['header.php','functions.php','gonorth.css','place-analytics.js','privacy-post.html']:
 a=(pre/name).read_text(encoding='utf-8').splitlines(keepends=True)
 b=(post/name).read_text(encoding='utf-8').splitlines(keepends=True)
 parts.extend(difflib.unified_diff(a,b,fromfile='before/'+name,tofile='after/'+name,n=5))
Path('memory/data/2026-10-04-issue-83-review.patch').write_text(''.join(parts),encoding='utf-8')
print('patch lines',len(parts))