from pathlib import Path
base=Path('memory/data/2026-10-04-issue-83-prechange')
out=Path('memory/scripts/issue83-staged'); out.mkdir(parents=True,exist_ok=True)
header=(base/'header.php').read_text(encoding='utf-8')
start=header.index('<!-- Google Tag Manager -->')
end=header.index('<!-- End Google Tag Manager -->')+len('<!-- End Google Tag Manager -->')
new_header='''<!-- Consent-gated Google Tag Manager: no Google analytics request before opt-in. -->
<script data-noptimize="1">
(function(w,d){
  var loaded=false;
  function approved(){try{return w.localStorage.getItem('gn_cookie_ok')==='1';}catch(e){return false;}}
  function safeUrl(value){
    if(!value)return '';
    try{var u=new URL(value,w.location.href);return /^https?:$/.test(u.protocol)?u.origin+u.pathname:'';}catch(e){return '';}
  }
  function loadGtm(){
    if(loaded||!approved())return;
    loaded=true;
    w.dataLayer=w.dataLayer||[];
    w.gtag=w.gtag||function(){w.dataLayer.push(arguments);};
    w.gtag('consent','default',{
      analytics_storage:'granted',ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied'
    });
    w.gtag('set',{page_location:safeUrl(w.location.href),page_referrer:safeUrl(d.referrer)});
    w.dataLayer.push({'gtm.start':new Date().getTime(),event:'gtm.js'});
    var j=d.createElement('script');j.async=true;
    j.src='https://www.googletagmanager.com/gtm.js?id=GTM-WM5VRDCR';
    d.head.appendChild(j);
  }
  w.addEventListener('gn-consent-granted',loadGtm);
  loadGtm();
})(window,document);
</script>
<!-- End consent-gated Google Tag Manager -->'''
header=header[:start]+new_header+header[end:]
start=header.index('<!-- Google Tag Manager (noscript) -->')
end=header.index('<!-- End Google Tag Manager (noscript) -->')+len('<!-- End Google Tag Manager (noscript) -->')
header=header[:start]+header[end:]
(out/'header.php').write_text(header,encoding='utf-8')
functions=(base/'functions.php').read_text(encoding='utf-8')
assert "$main_dependencies, '2.9.3'" in functions
functions=functions.replace("$main_dependencies, '2.9.3'", "$main_dependencies, '2.9.4'",1)
start=functions.index('// ─── Cookie Consent Banner')
end=functions.index('// ─── Google Places API Integration',start)
new_banner='''// ─── Optional measurement consent ─────────────────────────────────────────
add_action( 'wp_footer', 'gonorth_cookie_banner' );
function gonorth_cookie_banner() {
    $privacy_url = esc_url( home_url( '/mediniyut-pratiyut/' ) );
    echo <<<HTML
<button type="button" id="gn-cookie-settings" class="gn-cookie-settings" aria-controls="gn-cookie-banner" aria-expanded="false">העדפות פרטיות</button>
<div id="gn-cookie-banner" class="gn-cookie-banner" role="dialog" aria-modal="false" aria-labelledby="gn-cookie-title" dir="rtl">
  <div class="gn-cookie-copy">
    <h2 id="gn-cookie-title" tabindex="-1">בחירת מדידה באתר</h2>
    <p>קובצי עוגיות חיוניים מאפשרים את פעולת האתר. רק אם תאשרו נטען Google Analytics למדידת שימוש ואת רכיב Google Preferred Sources. אפשר לסרב ולשנות בחירה בכל עת. <a href="{$privacy_url}">מדיניות פרטיות</a></p>
  </div>
  <div class="gn-cookie-actions">
    <button type="button" id="gn-cookie-accept" class="gn-cookie-accept">מאשר/ת מדידה</button>
    <button type="button" id="gn-cookie-reject" class="gn-cookie-reject">ללא מדידה</button>
  </div>
</div>
<script data-noptimize="1">
(function(){
  var b=document.getElementById('gn-cookie-banner');
  var settings=document.getElementById('gn-cookie-settings');
  if(!b||!settings)return;
  function choice(){try{return localStorage.getItem('gn_cookie_ok');}catch(e){return null;}}
  function show(){b.style.display='flex';settings.setAttribute('aria-expanded','true');document.getElementById('gn-cookie-title').focus();}
  function hide(){b.style.display='none';settings.setAttribute('aria-expanded','false');settings.focus();}
  function clearAnalyticsCookies(){
    document.cookie.split(';').forEach(function(part){
      var name=part.split('=')[0].trim();
      if(!/^(_ga(?:_|$)|_gid$|_gat(?:_|$))/.test(name))return;
      document.cookie=name+'=; Max-Age=0; path=/';
      document.cookie=name+'=; Max-Age=0; path=/; domain=.gonorth.co.il';
    });
  }
  if(choice()!=='1'&&choice()!=='0')show();
  settings.addEventListener('click',show);
  document.getElementById('gn-cookie-accept').addEventListener('click',function(){
    try{localStorage.setItem('gn_cookie_ok','1');}catch(e){return;}
    hide();window.dispatchEvent(new Event('gn-consent-granted'));
  });
  document.getElementById('gn-cookie-reject').addEventListener('click',function(){
    try{localStorage.setItem('gn_cookie_ok','0');}catch(e){return;}
    clearAnalyticsCookies();hide();window.location.reload();
  });
})();
</script>
HTML;
}

'''
functions=functions[:start]+new_banner+functions[end:]
(out/'functions.php').write_text(functions,encoding='utf-8')
css=(base/'gonorth.css').read_text(encoding='utf-8')
start=css.index('/* ── Cookie Consent Banner')
end=css.index('/* ── Google Places Integration',start)
new_css='''/* Optional measurement choice, accessible in RTL and on mobile. */
.gn-cookie-banner {
  display:none;position:fixed;inset-inline:0;bottom:0;z-index:99999;
  background:#1a3a2a;color:#fff;padding:16px 24px;
  flex-direction:row;align-items:center;justify-content:center;gap:20px;
  font-family:'Heebo',sans-serif;direction:rtl;text-align:right;
  box-shadow:0 -2px 16px rgba(0,0,0,.25)
}
.gn-cookie-copy{max-width:720px}
.gn-cookie-copy h2{margin:0 0 4px;color:#fff;font-size:1.05rem}
.gn-cookie-copy p{margin:0;color:#fff;font-size:.9rem;line-height:1.5}
.gn-cookie-copy a{color:#83e7ff;text-decoration:underline}
.gn-cookie-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
.gn-cookie-actions button,.gn-cookie-settings{border:1px solid #fff;border-radius:6px;padding:9px 16px;font:600 .9rem 'Heebo',sans-serif;cursor:pointer}
.gn-cookie-accept{background:#40916c;color:#fff}
.gn-cookie-reject{background:#fff;color:#1a3a2a}
.gn-cookie-settings{position:fixed;inset-inline-start:12px;bottom:12px;z-index:99998;background:#fff;color:#1a3a2a;border-color:#2d6a4f;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.gn-cookie-actions button:focus-visible,.gn-cookie-settings:focus-visible,.gn-cookie-banner a:focus-visible{outline:3px solid #e9c46a;outline-offset:2px}
@media(max-width:600px){
  .gn-cookie-banner{flex-direction:column;align-items:stretch;gap:12px;padding:16px;max-height:75vh;overflow:auto}
  .gn-cookie-copy h2,.gn-cookie-copy p{text-align:right}
  .gn-cookie-actions button{flex:1 1 120px}
  .gn-cookie-settings{bottom:8px;inset-inline-start:8px;padding:7px 10px;font-size:.8rem}
}

'''
css=css[:start]+new_css+css[end:]
(out/'gonorth.css').write_text(css,encoding='utf-8')
analytics=(base/'place-analytics.js').read_text(encoding='utf-8')
needle="\tfunction send( eventName, parameters ) {\n\t\tconst payload = clean( Object.assign( {}, parameters, {\n\t\t\tpage_location: window.location.href,"
repl="\tfunction send( eventName, parameters ) {\n\t\tlet consentGranted = false;\n\t\ttry { consentGranted = '1' === window.localStorage.getItem( 'gn_cookie_ok' ); } catch ( error ) {}\n\t\tif ( ! consentGranted ) return;\n\t\tconst payload = clean( Object.assign( {}, parameters, {\n\t\t\tpage_location: window.location.origin + window.location.pathname,"
assert needle in analytics
analytics=analytics.replace(needle,repl,1)
(out/'place-analytics.js').write_text(analytics,encoding='utf-8')
print('staged four files')