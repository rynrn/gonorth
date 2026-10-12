המשך #86 (06.10.2026): אחרי ביקורת עצמאית וניסוי מדורג, הסרתי את AyeCode UI CSS, Bootstrap bundle ו־GeoDirectory JS רק מעמוד 436 ומ־25 כתבות קיימות שנבדקו. guard נפרד משאיר את הנכסים אם יתווסף תוכן GeoDirectory/AyeCode או יוגדר `gn_keep_geodir_assets`; כתבות עתידיות אינן נכללות אוטומטית.

אימות ציבורי: 26/26 URL ב־390 וב־1280 החזירו 200, ללא pageerror, גלילה או H1/footer חסרים. שלושת הנכסים אינם נטענים בכתבות, אך ממשיכים להיטען בארכיון ובדף מקום. תפריט מובייל וסרגל OneTap נפתחו. מדידת Chrome בעמוד 436: CSS/JS ירדו מ־753,277 ל־483,475 בתים בהעברה — 269,802 בתים ו־3 בקשות פחות. זו מדידת מעבדה, לא מדדי שדה.

נשמרו snapshots ושחזור מוגן checksum; PHP lint ו־rollback check-only עברו. דוח: `memory/reports/2026-10-06-issue-86-editorial-geo-assets.md`. הכרטיס נשאר פתוח לדחיסה/Cache-Control ב־Nginx (עבודת ROOT נדחתה לפי החלטת בעל האתר), תקציבי נכסים ו־LCP/INP/CLS בשדה.
