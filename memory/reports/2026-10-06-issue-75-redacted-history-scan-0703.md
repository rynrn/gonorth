# #75 — סריקת היסטוריית Git מקומית ללא חשיפת ערכי אישורים

תאריך: 06.10.2026 07:03 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/75; משימת־אב: https://github.com/rynrn/gonorth/issues/74. סיכון L1, שינוי מקומי בלבד. לא הוחלף או בוטל אישור חי, לא שוכתבה היסטוריה ולא שונה האתר.

נוסף הכלי `tools/scan-git-history-secrets.py`. הוא קורא את כל ה־Git blobs הייחודיים הנגישים מכל ה־refs המקומיים, משתמש בדפוסים של `tools/scan-secrets.py`, מדפיס רק סוג כלל, מספר blobs ומספר התאמות, ומחזיר exit code 1 כשיש ממצאים. אין הדפסת ערכי התאמה או נתוני קובץ. `docs/credential-management.md` תוקן כך שיסביר מתי להריץ אותו ואת גבולותיו. הסורק הקיים של עץ הקבצים נשאר בדיקת CI; סריקת היסטוריה אינה מתאימה כשער CI לפני סיבוב אישורים משום שהיסטוריה קיימת תיכשל שוב בכל ריצה.

בדיקה מקומית: `python -m py_compile tools/scan-git-history-secrets.py` עברה. `python tools/scan-secrets.py` החזיר exit 0: אין דפוסי אישורים בקבצים שבמעקב במצב העבודה הנוכחי. `python tools/scan-git-history-secrets.py` סרק 130 blobs ייחודיים ב־22 commits נגישים והחזיר exit 1 כמצופה בגלל התאמות היסטוריות. סיכום מצומצם: `credential_assignment` בשני blobs (שתי התאמות), `google_api_key` בעשרה blobs (24 התאמות), `wordpress_app_password` בחמישה blobs (חמש התאמות). אותן מחרוזות עשויות לחזור בין גרסאות; ספירת התאמות אינה ספירת אישורים ייחודיים. חלק מהדפוסים עלולים להיות דוגמאות או false positives, ותוקף אישור לא נבדק. הסריקה אינה מכסה refs מרוחקים שאינם מקומיים, blobs בלתי־נגישים, קבצים בלתי־מנוהלים, גיבויים או מערכות חיצוניות.

המשך נדרש: לזהות בסביבה פרטית את בעלי חשבונות Google/WordPress/SSH, להכין חלופות ייעודיות בהרשאה מצומצמת, לבדוק אותן במסלול העבודה האמיתי, לבטל אישורים ישנים ולאמת שאינם עובדים. רק אחרי סיבוב ותיאום עם משתמשי המאגר יש להחליט על שכתוב היסטוריה או טיפול בעותקים. אין לפרסם ל־Issue ערכים או תוצאות פרטניות של אישורים. #75 נשאר פתוח כי תנאי ההחלפה והביטול טרם הוכחו.

```yaml
status: partial
scope: "redacted local Git history credential scan and documentation"
risk_level: L1
canonical_writer: "local tools/scan-git-history-secrets.py and docs/credential-management.md"
findings:
  - "Current tracked working tree scanner passed"
  - "Historical local refs contain credential-pattern matches; validity unknown"
sources:
  - {url: "tools/scan-secrets.py", checked_at: "2026-10-06"}
  - {url: "docs/credential-management.md", checked_at: "2026-10-06"}
  - {url: "local Git refs", checked_at: "2026-10-06"}
changes_made:
  - "Added aggregate-only read-only history scanner"
  - "Documented scope and limitations in credential-management guide"
unchanged:
  - {item: "Live credentials, remote refs and Git history", reason: "Rotation and collaborator coordination not yet complete"}
validation:
  - {check: "py_compile", result: pass, evidence: "no syntax errors"}
  - {check: "current tracked tree scan", result: pass, evidence: "exit 0, no patterns"}
  - {check: "local history scan", result: pass, evidence: "130 blobs; exit 1 expected on aggregate findings; no values printed"}
snapshot: "not-required for L1; exact added guide sentence and new tool are reversible"
rollback: "Remove tools/scan-git-history-secrets.py and the added sentence in docs/credential-management.md after checking for later edits"
blockers:
  - "Credential owners, replacement credentials and revocation workflow not yet confirmed"
remaining_risks:
  - "Historical matches may still represent usable credentials; validity unverified"
issue_recommendation: keep-open
```