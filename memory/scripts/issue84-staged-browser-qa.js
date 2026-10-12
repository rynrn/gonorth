const { chromium } = require('playwright');
const fs = require('fs');
const staged = fs.readFileSync('wordpress/plugins/gonorth-place-data/assets/place-analytics.js', 'utf8');
const names = new Set(['search_submit', 'filter_apply', 'zero_results', 'guide_to_place_click']);
const base = 'https://gonorth.co.il';
const sentinel = 'GN84_SENTINEL_20261006';
const live = process.env.GN84_LIVE === '1';
const results = [];
function check(label, actual, expected) {
  const ok = JSON.stringify(actual) === JSON.stringify(expected);
  results.push({ label, ok, actual, expected });
}
async function setup(browser, consent, guideId = null) {
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  if (guideId && !live) await context.addInitScript(id => { let cfg; Object.defineProperty(window, 'GoNorthAnalyticsConfig', { configurable:true, get(){ return cfg; }, set(v){ cfg = Object.assign({}, v, {currentGuide:{guide_id:id}}); } }); }, guideId);
  if (consent !== null) await context.addInitScript(v => localStorage.setItem('gn_analytics_consent_v2', v), consent ? '1' : '0');
  const requests = [];
  if (!live) await context.route(/(?:autoptimize_single_c08160bf538e68f97517c11659515f97\.php|\/gonorth-place-data\/assets\/place-analytics\.js)/, route => route.fulfill({status:200, contentType:'text/javascript', body:staged}));
  const page = await context.newPage();
  page.on('request', req => { if (/google-analytics\.com|googletagmanager\.com/.test(req.url())) requests.push(req.url()); });
  const events = async () => page.evaluate(() => (window.dataLayer || []).filter(x => x && x[0] === 'event' && ['search_submit','filter_apply','zero_results','guide_to_place_click'].includes(x[1])).map(x => ({ name:x[1], params:x[2] })));
  return { context, page, requests, events };
}
async function navigate(page,url) { await page.goto(url,{waitUntil:'domcontentloaded',timeout:60000}); await page.waitForTimeout(700); }
async function submit(page,button) { await Promise.all([page.waitForNavigation({waitUntil:'domcontentloaded',timeout:60000}),page.click(button)]); await page.waitForTimeout(700); }
(async()=>{
 const browser = await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 try {
  {
   const t=await setup(browser,true);
   await navigate(t.page,base+'/');
   await t.page.selectOption('#gn-home-category','yakavim');
   await t.page.fill('#gn-home-search',sentinel);
   await submit(t.page,'.gn-hero__search button[type="submit"]');
   const e=await t.events();
   check('home search+filter+zero once',e.map(x=>x.name),['search_submit','filter_apply','zero_results']);
   check('category canonical URL',t.page.url().includes('/places/category/yakavim/'),true);
   check('query excluded from all event params',e.some(x=>JSON.stringify(x).includes(sentinel)),false);
   check('query excluded from GA requests',t.requests.some(x=>x.includes(sentinel)),false);
   check('pending search cleared',await t.page.evaluate(()=>sessionStorage.getItem('gn_search_event_v1')),null);
   await t.context.close();
  }
  {
   const t=await setup(browser,false);
   await navigate(t.page,base+'/');
   await t.page.selectOption('#gn-home-category','yakavim');
   await t.page.fill('#gn-home-search',sentinel);
   await submit(t.page,'.gn-hero__search button[type="submit"]');
   check('rejected consent produces no custom events',(await t.events()).length,0);
   check('rejected consent leaves no pending marker',await t.page.evaluate(()=>sessionStorage.getItem('gn_search_event_v1')),null);
   await t.context.close();
  }
  {
   const t=await setup(browser,true);
   await navigate(t.page,base+'/places/');
   const form=await t.page.locator('#gn-archive-search').count();
   check('archive search form exists',form,1);
   if(form){
     await t.page.selectOption('#gn-archive-search select[name="category"]','yakavim');
     await submit(t.page,'#gn-archive-search button[type="submit"]');
     check('archive filter only emits once',(await t.events()).map(x=>x.name),['filter_apply']);
   }
   await t.context.close();
  }
  {
   const t=await setup(browser,true);
   await navigate(t.page,base+'/places/');
   await t.page.fill('#gn-archive-search input[name="s"]',sentinel);
   await submit(t.page,'#gn-archive-search button[type="submit"]');
   check('archive query empty results emits once',(await t.events()).map(x=>x.name),['search_submit','zero_results']);
   check('archive query not in GA requests',t.requests.some(x=>x.includes(sentinel)),false);
   await t.context.close();
  }
  {
   const t=await setup(browser,true,'516');
   await navigate(t.page,base+'/blog/yizreel-valley-day-trip/');
   const links=await t.page.locator('a[href*="/places/"]').evaluateAll(items=>items.map(a=>a.href).filter(h=>/^https:\/\/gonorth\.co\.il\/places\/[^/]+\/?$/.test(h)));
   check('guide has place link',links.length>0,true);
   if(links.length){
     await t.page.evaluate(href=>{const a=[...document.querySelectorAll('a')].find(x=>x.href===href); if(a){a.addEventListener('click',e=>e.preventDefault());a.click();}},links[0]);
     const e=await t.events();
     check('guide click once',e.map(x=>x.name),['guide_to_place_click']);
     check('guide click sends canonical id only',Object.keys(e[0]?.params||{}).sort(),['guide_id','page_location','target_type']);
     check('guide click has correct id',e[0]?.params?.guide_id,'516');
   }
   await t.context.close();
  }
  {
   const t=await setup(browser,true);
   await navigate(t.page,base+'/places/?s='+sentinel);
   const e=await t.events();
   check('direct empty results once',e.map(x=>x.name),['zero_results']);
   check('direct empty result excludes query',e.some(x=>JSON.stringify(x).includes(sentinel)),false);
   await t.context.close();
  }
 } finally { await browser.close(); }
 console.log(JSON.stringify({passed:results.filter(x=>x.ok).length,total:results.length,failures:results.filter(x=>!x.ok)},null,2));
 if(results.some(x=>!x.ok)) process.exit(1);
})().catch(e=>{console.error(e.stack||e.message);process.exit(1)});
