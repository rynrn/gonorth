import json
from pathlib import Path
src=Path('memory/data/2026-10-06-issue-91-media-rights-audit.json')
rows=json.loads(src.read_text(encoding='utf-8-sig'))
observations=[
'שרידי כלי משוריין בתוך ערוץ מים',
'עגלה ורודה ומתחם ישיבה חיצוני',
'עגלת אוכל ממותגת תחת עצים',
'כמה אוהלים במתחם קמפינג מיוער',
'מבנה אירוח, שביל ומדשאה',
'חלל ישיבה פנימי של מסעדה',
'פלג מים וגשרון בפארק',
'ילדים ומדריכה ליד תצוגת מוזיאון',
'בריכת מים טבעית בין עצים',
'בריכת מלון וחזית מתחם אירוח',
'בריכת מים צלולה בתוך צמחייה',
'חזית בית קפה עם שם המותג',
'בריכת מתחם אירוח ובקתות',
'חלל ישיבה פנימי של מסעדה',
'דלת כניסה לחנות/מבנה',
'מנות אוכל ומשקאות על שולחן',
'קבוצת רוכבים על כלי רכיבה ממונעים',
'חלל ישיבה פנימי עם דלפק יין',
'אזור ישיבה מקורה בגינה',
'יחידות אוהל במתחם גלמפינג',
]
assert len(rows)==len(observations)==20
out=[]
for row,seen in zip(rows,observations):
    out.append({'place_id':row['place_id'],'media_id':row['media_id'],'seen_in_image':seen,'alt_subject_consistency':'plausible','place_identity_verified_from_photo':False,'usage_permission':row['usage_permission'],'checked_at':'2026-10-06','evidence':'memory/work/issue91-contact-'+str(len(out)//5+1)+'.png'})
Path('memory/data/2026-10-06-issue-91-visual-check-0400.json').write_text(json.dumps(out,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
print('records',len(out),'subject-consistent',sum(x['alt_subject_consistency']=='plausible' for x in out),'place-identity-verified',sum(x['place_identity_verified_from_photo'] for x in out))
