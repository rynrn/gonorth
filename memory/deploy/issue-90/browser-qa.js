const { chromium } = require('playwright');
const base = 'https://gonorth.co.il';
const tests = [];
function check(name, ok, detail='') { tests.push({name,pass:!!ok,detail}); }
async function open(page,path) {
  const response=await page.goto(base+path,{waitUntil:'domcontentloaded',timeout:60000});
  await page.waitForTimeout(650);
  return response;
}
async function state(page) {
  return page.evaluate(()=>{
    const value=s=>document.querySelector(s)?.value ?? null;
    const text=s=>document.querySelector(s)?.textContent.trim() ?? '';
    return {
      category:value('#gn-cat-select'),region:value('#gn-region-select'),
      saturday:value('#gn-saturday-select'),query:value('#gn-search-input'),
      summary:text('.gn-filter-summary'),zero:!!document.querySelector('main#gn-search-results.gn-search-empty'),
      robots:document.querySelector('meta[name="robots"]')?.content||'',
      canonical:document.querySelector('link[rel="canonical"]')?.href||'',
      width:document.documentElement.scrollWidth,viewport:innerWidth,
      labels:['#gn-cat-select','#gn-region-select','#gn-saturday-select','#gn-search-input'].every(s=>{const e=document.querySelector(s);return e&&document.querySelector('label[for="'+e.id+'"]')}),
    };
  });
}
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 try {
  const page=await browser.newPage({viewport:{width:390,height:844}});
  let r=await open(page,'/places/?gn_verify=issue90b');
  let s=await state(page);
  check('01 base archive 200 and controls',r.status()===200 && s.region!==null && s.saturday!==null,s.summary);
  check('02 base count and keyboard labels',s.summary.includes('128') && s.labels,s.summary);
  r=await open(page,'/places/?region=galil&saturday=yes&gn_verify=issue90b');s=await state(page);
  check('03 Galil verified yes 10',r.status()===200&&s.summary.includes('10')&&s.region==='galil'&&s.saturday==='yes',s.summary);
  check('04 filtered SEO',/noindex/.test(s.robots)&&s.canonical===base+'/places/',s.robots+' '+s.canonical);
  check('05 mobile width',s.width<=s.viewport,s.width+'/'+s.viewport);
  r=await open(page,'/places/?region=golan&saturday=no&gn_verify=issue90b');s=await state(page);
  check('06 Golan verified no 16',r.status()===200&&s.summary.includes('16')&&s.saturday==='no',s.summary);
  r=await open(page,'/places/?region=carmel&saturday=unknown&gn_verify=issue90b');s=await state(page);
  check('07 Carmel unknown 2',r.status()===200&&s.summary.includes('2')&&s.saturday==='unknown',s.summary);
  r=await open(page,'/places/?saturday=unknown&gn_verify=issue90b');s=await state(page);
  check('08 all unknown 76',r.status()===200&&s.summary.includes('76'),s.summary);
  r=await open(page,'/places/?region=carmel&saturday=yes&s=zzzxxyyq&gn_verify=issue90b');s=await state(page);
  check('09 empty filtered state 200',r.status()===200&&s.zero&&s.query==='zzzxxyyq',s.summary);
  check('10 empty state noindex',/noindex/.test(s.robots),s.robots);
  r=await open(page,'/places/?gn_verify=issue90b');
  await page.selectOption('#gn-region-select','golan');
  await page.selectOption('#gn-saturday-select','yes');
  await page.selectOption('#gn-cat-select','yakavim');
  await page.waitForURL(/\/places\/category\/yakavim\//,{timeout:15000});
  s=await state(page);
  check('11 category redirect keeps filters',s.region==='golan'&&s.saturday==='yes'&&s.category==='yakavim',page.url());
  await page.goBack({waitUntil:'domcontentloaded'});
  await page.waitForTimeout(500);
  s=await state(page);
  check('12 Back restores controls',s.region===''&&s.saturday==='',page.url());
  await open(page,'/places/?region=galil&saturday=no&gn_verify=issue90b');
  await page.locator('#gn-region-select').focus();
  const focused=await page.evaluate(()=>document.activeElement?.id);
  check('13 keyboard focus',focused==='gn-region-select',focused);
  await page.locator('#gn-filter-help').isVisible().then(v=>check('14 tri-state help visible',v));
  await page.locator('.gn-filter-reset').click();
  await page.waitForURL(url=>url.pathname==='/places/',{timeout:15000});
  s=await state(page);
  check('15 reset clears filters',s.region===''&&s.saturday==='',page.url());
  const wide=await browser.newPage({viewport:{width:1280,height:900}});
  await open(wide,'/places/?region=galil&saturday=yes&gn_verify=issue90b');
  s=await state(wide);
  check('16 desktop width',s.width<=s.viewport,s.width+'/'+s.viewport);
  await wide.close();await page.close();
  const consent=await browser.newContext({viewport:{width:390,height:844}});
  await consent.addInitScript(()=>localStorage.setItem('gn_analytics_consent_v2','1'));
  const p=await consent.newPage();
  await open(p,'/places/?gn_verify=issue90b');
  await p.selectOption('#gn-region-select','golan');
  await p.selectOption('#gn-saturday-select','yes');
  await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded',timeout:15000}),p.click('#gn-archive-search button[type="submit"]')]);
  await p.waitForTimeout(700);
  const events=await p.evaluate(()=>(window.dataLayer||[]).filter(x=>x&&x[0]==='event'&&x[1]==='filter_apply').map(x=>x[2]));
  check('17 approved measurement region and Saturday',events.some(x=>x.filter_name==='region')&&events.some(x=>x.filter_name==='open_saturday'),JSON.stringify(events));
  await consent.close();
  const rejected=await browser.newContext({viewport:{width:390,height:844}});
  await rejected.addInitScript(()=>localStorage.setItem('gn_analytics_consent_v2','0'));
  const q=await rejected.newPage();
  await open(q,'/places/?gn_verify=issue90b');
  await q.selectOption('#gn-region-select','golan');
  await Promise.all([q.waitForNavigation({waitUntil:'domcontentloaded',timeout:15000}),q.click('#gn-archive-search button[type="submit"]')]);
  await q.waitForTimeout(500);
  const rejectedEvents=await q.evaluate(()=>(window.dataLayer||[]).filter(x=>x&&x[0]==='event'&&x[1]==='filter_apply'));
  check('18 rejected consent no filter events',rejectedEvents.length===0,String(rejectedEvents.length));
  await rejected.close();
 } finally { await browser.close(); }
 console.log(JSON.stringify({passed:tests.filter(t=>t.pass).length,total:tests.length,tests},null,2));
 if(tests.some(t=>!t.pass))process.exitCode=1;
})().catch(error=>{console.error(error);process.exit(1)});