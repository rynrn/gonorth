# ראיות דפדפן ומשלימות — 26.09.2026

ב־22:37–22:41 שעון ישראל נפתחו ב־Chrome ארכיון https://gonorth.co.il/places/ והכתבה https://gonorth.co.il/tours-kayaks-north-israel/.

בארכיון DOM כלל14 stylesheets, בקשת Heebo800 ל־fonts.googleapis.com, קובצי WooCommerce add-to-cart/order-attribution ו־Bootstrap. תמונות10 כרטיסי המקום כללו loading=lazy ו־srcset. currentSrc של הכרטיסים הפנה לחלופות jpg.webp, ברובן600px. רוחב התצוגה373px. אין iframe בארכיון. זה אינו waterfall או מדידת CLS.

בכתבה DOM כלל15 stylesheets. נמצאו WooCommerce/GeoDirectory/Bootstrap ו־GTM. gtag.js נטען עם פרמטר gtm, לכן נוכחותו לצד GTM אינה הוכחה להטמעה כפולה. תמונת הכתבה: currentSrc=https://gonorth.co.il/wp-content/uploads/2026/09/gonorth-tours-kayaks-hero-1536x864.webp, רוחב תצוגה1265px, fetchpriority=high, loading=auto, srcset קיים. אין כאן מדידת משקל החלופה הזאת.

ב־22:41:08 HEAD עם curl --compressed לשני הקבצים הבאים החזיר200 ללא Content-Encoding וללא Cache-Control, עם ETag ו־Last-Modified. הדבר תואם GET שנשמר ב־performance-evidence-live.json:

1. https://gonorth.co.il/wp-includes/css/dist/block-library/style-rtl.min.css?ver=7.1.2 — Content-Type:text/css, Content-Length:140886.
2. https://gonorth.co.il/wp-content/plugins/geodirectory/vendor/ayecode/wp-ayecode-ui/assets-v5/js/bootstrap.bundle.min.js?ver=0.2.47 — Content-Type:application/javascript, Content-Length:136517.

לא נשמר HTML גולמי לאחר חילוץ כדי לא לשמור מפתחות API ציבוריים או nonce אקראיים שאינם דרושים לביקורת; קובצי html הם סמני פעולה בלבד. ספירת משאבי JSON נוגעת למקור HTML; ספירת DOM יכולה להיות שונה בגלל טעינה דינמית.
