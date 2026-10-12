# #93 — גן לאומי בית שערים פורסם וקושר מהמדריך

תאריך: 06.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). זהו דוח המשך ל[דוח יצירת הרשומה הממתינה](2026-10-06-issue-93-beit-shearim-pending-0613.md).

לאחר ביקורת מנהל וביקורת תוכן, רשומה 534 פורסמה דרך GeoDirectory REST מאומת, ולאחר מכן גוף התוכן העריכתי עודכן ב־WordPress WP-CLI. הצעד השני נדרש משום ש־GeoDirectory REST הסיר תגיות קישור מהתוכן. כעת [דף המקום](https://gonorth.co.il/places/גן-לאומי-בית-שערים/) מציג 200, קישור לאתר רשות הטבע והגנים, קישור לקובץ וליוצר הצילום ב־Commons וקישור מפורש לרישיון CC BY-SA 4.0. התוכן מוסיף הערה מתוארכת על שיפוץ המבואה, בלי לטעון שהגן סגור. עיר ורחוב נותרו ריקים; GPS, קטגוריה, אזור ותמונה ראשית 535 לא שונו. עובדות משתנות חסרות לא הומצאו.

ב[מדריך הטיול בעמק יזרעאל](https://gonorth.co.il/yizreel-valley-day-trip/) (פוסט 516) שם הגן בקשר המסלול מקשר כעת לדף המקום, וקישור רשות הטבע והגנים נשמר כמקור. הקישור הפנימי נבדק בדף הציבורי (200). תמונת המקום מחזירה 200 JPEG ו־GeoDirectory REST ללא הזדהות מציג רשומה מפורסמת (200). בדיקה קנונית ואחריה בדיקה ציבורית אישרו קישורי רישיון ומקור, סטטוס publish, עיר ריקה והערת השיפוץ. [פלט QA אחרי תיקון הקישורים](../data/2026-10-06-issue-93-beit-shearim-link-qa.json). בדיקה מוקדמת גילתה קישורים שהוסרו; התוצאה המוקדמת נשמרת בנתוני הבדיקה לצורך מעקב, והתיקון אומת מחדש.

מקורות העובדות והרישיון, נבדקו 06.10.2026: [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/%D7%92%D7%9F-%D7%9C%D7%90%D7%95%D7%9E%D7%99-%D7%91%D7%99%D7%AA-%D7%A9%D7%A2%D7%A8%D7%99%D7%9D/), [קובץ Wikimedia Commons](https://commons.wikimedia.org/wiki/File:Catacomb_no._14,_The_Cave_of_Rabbi_Yehuda_HaNasi.jpg), [רישיון CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/).

לפני שינוי נשמרו [snapshot הרשומה](../data/2026-10-06-issue-93-publish-534-before.json), [snapshot המדריך](../data/2026-10-06-issue-93-publish-516-before.json) ו[מצב GeoDirectory](../data/2026-10-06-issue-93-publish-534-before-geodir.json). המצב החדש נשמר ב[צילום הרשומה](../data/2026-10-06-issue-93-publish-534-after-geodir.json) וב[צילום המדריך](../data/2026-10-06-issue-93-publish-516-after-rest.json). [סקריפט השחזור המוגן](../scripts/issue93-beit-shearim-publication-rollback.ps1) בודק את המצב החדש לפני שחזור הרשומה ל־pending והמדריך לתוכנו הקודם. dry-run עבר; Apply לא הופעל.

#93 נשאר פתוח: לשאר 11 המועמדים דרושה ביקורת פרסום וקישורי מדריכים. פארק אדמית נשאר ממתין עד אישור רשמי לפתיחה מחדש ואימות יעד הגעה. אין לשנות עובדות דינמיות ללא place-facts.

```yaml
status: complete
scope: "published listing #534 and linked it from guide #516; parent #93 remains open"
risk_level: L2
canonical_writer: "authenticated GeoDirectory REST for status; WordPress WP-CLI for linked editorial content; authenticated WordPress REST for guide"
findings:
  - "GeoDirectory REST stripped HTML anchors, so WordPress WP-CLI restored reviewed links."
sources:
  - url: "https://www.parks.org.il/reserve-park/גן-לאומי-בית-שערים/"
    checked_at: "2026-10-06"
  - url: "https://commons.wikimedia.org/wiki/File:Catacomb_no._14,_The_Cave_of_Rabbi_Yehuda_HaNasi.jpg"
    checked_at: "2026-10-06"
changes_made:
  - "Published #534 with dated entrance renovation note and visible image attribution."
  - "Linked guide #516 to listing #534."
unchanged:
  - item: "Unverified city, street, hours, price, phone and rating"
    reason: "No verified canonical value"
validation:
  - check: "Canonical record and public listing/guide/image"
    result: pass
    evidence: "memory/data/2026-10-06-issue-93-beit-shearim-link-qa.json and public HTTP 200 checks"
  - check: "Guarded rollback dry run"
    result: pass
    evidence: "Rollback guard passed; no changes made"
snapshot: "memory/data/2026-10-06-issue-93-publish-534-before.json and memory/data/2026-10-06-issue-93-publish-516-before.json"
rollback: "memory/scripts/issue93-beit-shearim-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other candidates in #93 await publication review and guide links."
issue_recommendation: keep-open
```
