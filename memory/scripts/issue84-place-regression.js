const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 try{
  const context=await browser.newContext({viewport:{width:390,height:844}});
  await context.addInitScript(()=>localStorage.setItem('gn_analytics_consent_v2','1'));
  const page=await context.newPage();
  await page.goto('https://gonorth.co.il/yizreel-valley-day-trip/',{waitUntil:'domcontentloaded',timeout:60000});
  const href=await page.locator('a[href*="/places/"]').evaluateAll(items=>items.map(a=>a.href).find(h=>/^https:\/\/gonorth\.co\.il\/places\/[^/]+\/?$/.test(h)));
  if(!href) throw Error('No guide place link');
  await page.goto(href,{waitUntil:'domcontentloaded',timeout:60000});
  await page.waitForTimeout(800);
  const views=await page.evaluate(()=>(window.dataLayer||[]).filter(x=>x&&x[0]==='event'&&x[1]==='place_view').length);
  const action=page.locator('.gn-place-overview__actions a[href]').first();
  const actionExists=await action.count();
  if(actionExists) await action.evaluate(a=>{a.addEventListener('click',e=>e.preventDefault());a.click()});
  const ctas=await page.evaluate(()=>(window.dataLayer||[]).filter(x=>x&&x[0]==='event'&&x[1]==='place_cta_click').length);
  const out={views,actionExists:!!actionExists,ctas,pass:views===1&&(!actionExists||ctas===1)};
  console.log(JSON.stringify(out));
  if(!out.pass) process.exitCode=1;
 }finally{await browser.close()}
})().catch(e=>{console.error(e.message);process.exit(1)});
