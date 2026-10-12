# #96 — בסיס עלויות מאומת חלקית והחלטת מסחור זמנית

תאריך: 06.10.2026, 09:55 ישראל. סיכון L1: קובצי זיכרון וסקיל מקומיים בלבד; באתר בוצעו שאילתות קריאה מצרפיות, ללא שינוי, רכישה, פרסום מחיר או גבייה. תקופת ההשוואה שנבחרה: **ספטמבר 2026 — חודש קלנדרי מלא**. [כרטיס #96](https://github.com/rynrn/gonorth/issues/96); תלות במדידה [#84](https://github.com/rynrn/gonorth/issues/84) וב־Search Console [#46](https://github.com/rynrn/gonorth/issues/46).

## מה אומת

[שאילתת WooCommerce מצרפית](../data/2026-10-06-issue-96-woocommerce-aggregate.json) החזירה אפס הזמנות בסטטוסים pending, processing, completed, on-hold, cancelled, refunded ו־failed במועד הבדיקה. לא נקראו שמות, כתובות או פרטי לקוחות. זהו נתון על **הזמנות באתר בלבד**; הוא אינו מוכיח היעדר הכנסות מחוץ לאתר או בחשבונות אחרים. [מלאי תוספים](../data/2026-10-06-issue-96-plugin-inventory.json) מראה 13 תוספים פעילים ושניים לא פעילים; התקנה או הפעלה אינה הוכחת רישיון בתשלום או עלות.

לא אותרו בפרויקט חשבוניות אירוח, דומיין, רישיונות, צילום, שיווק או דוח חיוב Google Cloud לספטמבר. אין גישה מאומתת לחשבונות החיוב, ולכן **סכום העלויות בפועל נשאר `null`, לא ₪0**. נוצר [יומן עלויות חודשי](../data/2026-10-06-issue-96-cost-ledger.json) עם שמונה שורות: אירוח, דומיין, תוספים, API, מדיה, עבודה, שיווק וגיבוי. כל שורה מציינת מקור נדרש, בעל אחריות ומועד חידוש לא ידוע; אין בו מחיר מומצא. סכומי ה־₪2,300 והמחירים בדוח CFO מ־26.09 הם הנחות תכנון בלבד, לא הוצאה, חשבונית או רווח בפועל.

לפי [דוח #84](2026-10-06-issue-84-analytics-instrumentation.md), אירועי חיפוש ומקום נשלחים בדפדפן בהסכמה, אך קליטתם בנכס GA4 הנכון ובסיס 28 ימים לא אומתו. אין נתוני מבקרים או פניות איכותיות מאומתים. `place_cta_click` הוא לחיצה, לא שיחה, הזמנה או לקוח משלם. #46 פתוח ללא דוח Search Console מאומת. מידע חסר אינו אפס.

[Google Maps Platform](https://developers.google.com/maps/billing-and-pricing/manage-costs) מבהירה ש־Cloud Billing Dashboard הוא מקור לחיובים סופיים, ושעשוי להיות עיכוב של עד 48 שעות. תקציב והתראות אינם תקרת חיוב; מכסה עשויה להגביל שימוש אך מכסה נמוכה מדי עלולה לפגוע בשירות. לכן לא נטען שכבר קיימת תקרת עלות API ולא ישונה כרגע שום quota בלי נתוני שימוש ובדיקת השפעה.

## שער החלטה מעשי

בשלב זה ההחלטה היא **להמשיך בחינם**. לא להפעיל קידום בתשלום, חבילות, באנרים, הזמנות או גבייה אוטומטית. זו החלטת תפעול זמנית לפי הראיות החסרות, לא קביעה שהמודל העסקי אינו אפשרי. ספי התנועה והמחירים הישנים בסקיל הם כללי תכנון בלבד. לפני פיילוט בתשלום נדרשים: (1) חודש מלא של חיובים מאומתים ממקורות הספק; (2) 28 ימים מלאים מאותו נכס GA4 עם הבחנה בין מסכימי מדידה לבין כלל המבקרים; (3) הגדרה ומדידה של *פנייה איכותית* — פנייה ייחודית שבעל עסק אישר שהיא רלוונטית ושמקורה באתר, ללא פרסום פרטים אישיים בדוח; (4) עלות שירות מדודה לפנייה ולעסק כולל זמן מחקר, תמיכה, מדיה, סליקה וחלק יחסי בעלויות קבועות; (5) תקציב נפרד, משך פיילוט, סימון מסחרי גלוי ותנאי עצירה. אפשר להמשיך בחינם גם אחרי איסוף הנתונים אם הערך אינו ברור.

לצורך השלמת שורות העלות: קבצי חשבונית/דוח חיוב לחודש ספטמבר מכל ספק, מועד חידוש ובעל חשבון; Google Cloud Billing לפי פרויקט ושירות; יומן שעות והסכמי תעריף/עלות פנימית. אין צורך להעלות לכאן מפתחות, פרטי אשראי או פרטי לקוחות. מידע כספי חסר מונע את תנאי הקבלה של דוח עלויות **בפועל** ואת סגירת #96. בינתיים תוקן [סקיל wp-monetize](../../.agents/skills/wp-monetize/SKILL.md) כדי לסמן מחירים ותחזיות ישנים כהמחשה בלבד ולהחייב הפרדה בין actual, assumption ו־unknown.

```yaml
status: partial
scope: "Issue #96 September 2026 cost baseline and interim monetization decision"
risk_level: L1
canonical_writer: "local finance ledger/report and wp-monetize skill; read-only WP-CLI aggregate"
findings:
  - "WooCommerce has zero orders in seven checked statuses at this snapshot; offsite revenue remains unknown"
  - "No verified supplier invoices or Cloud Billing export in project; actual total is unknown"
  - "GA4 property collection and qualified leads remain unverified"
sources:
  - {url: "memory/data/2026-10-06-issue-96-woocommerce-aggregate.json", checked_at: "2026-10-06"}
  - {url: "memory/data/2026-10-06-issue-96-plugin-inventory.json", checked_at: "2026-10-06"}
  - {url: "memory/reports/2026-10-06-issue-84-analytics-instrumentation.md", checked_at: "2026-10-06"}
  - {url: "https://developers.google.com/maps/billing-and-pricing/manage-costs", checked_at: "2026-10-06"}
changes_made:
  - "Created monthly cost ledger with unknown values left null"
  - "Updated wp-monetize skill evidence gate"
  - "Recorded interim free-service decision and data requirements"
unchanged:
  - {item: "WordPress commerce, Cloud Billing, paid products, prices and invoices", reason: "read-only task and no verified billing/traffic data"}
validation:
  - {check: "WooCommerce aggregate read", result: pass, evidence: "seven statuses, each count 0, no PII"}
  - {check: "ledger JSON structure", result: pass, evidence: "8 categories, 0 verified amounts, total null"}
  - {check: "skill diff check", result: pass, evidence: "git diff --check clean"}
snapshot: "not-required for local L1; dated source files retained"
rollback: "restore prior .agents/skills/wp-monetize/SKILL.md and remove new dated ledger/report if needed"
blockers:
  - "Supplier invoices/finalized billing export and verified GA4 data are unavailable"
remaining_risks:
  - "Offsite income and labor cost may exist; zero onsite orders is not zero total revenue"
issue_recommendation: keep-open
```
