const { chromium } = require('playwright');
const fs = require('fs');
const sentinel='GN_PRIVACY_TEST_20261004';
const guide='https://gonorth.co.il/yizreel-valley-day-trip/?private_note='+sentinel+'#private-fragment-'+sentinel;
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const out=[];
 async function run(label,initial,action){
  const context=await browser.newContext({locale:'he-IL',viewport:{width:390,height:844}});
  if(initial!==null)await context.addInitScript(v=>{if(!sessionStorage.getItem('gn_privacy_qa_init')){localStorage.setItem('gn_cookie_ok',v);sessionStorage.setItem('gn_privacy_qa_init','1');}},initial);
  const page=await context.newPage();const requests=[];const errors=[];
  page.on('request',req=>{let u;try{u=new URL(req.url())}catch(e){return} if(u.hostname.endsWith('gonorth.co.il'))return;requests.push({host:u.hostname,path:u.pathname.length>55?u.pathname.slice(0,55):u.pathname,sentinel:req.url().includes(sentinel)});});
  page.on('pageerror',e=>errors.push(e.message.slice(0,140)));
  await page.goto(guide,{waitUntil:'domcontentloaded',timeout:60000});
  await page.waitForTimeout(3500);
  if(action==='reject'){await page.click('#gn-cookie-reject');await page.waitForTimeout(2500)}
  if(action==='accept'){await page.click('#gn-cookie-accept');await page.waitForTimeout(3500)}
  if(action==='revoke'){await page.click('#gn-cookie-settings');await page.click('#gn-cookie-reject');await page.waitForTimeout(2500)}
  const state=await page.evaluate(()=>({choice:localStorage.getItem('gn_cookie_ok'),banner:getComputedStyle(document.querySelector('#gn-cookie-banner')).display,gtmScripts:document.querySelectorAll('script[src*="googletagmanager.com/gtm.js"]').length,preferredScripts:document.querySelectorAll('script[src*="news.google.com/swg/js/v1/publisher.js"]').length,settings:!!document.querySelector('#gn-cookie-settings'),buttons:Array.from(document.querySelectorAll('#gn-cookie-banner button')).map(x=>x.textContent.trim()),overflow:document.documentElement.scrollWidth>document.documentElement.clientWidth}));
  const summary={};for(const x of requests){const key=x.host+x.path;summary[key]=(summary[key]||0)+1}
  out.push({label,state,requests:summary,thirdPartySentinel:requests.filter(x=>x.sentinel),errors}); fs.writeFileSync('memory/data/2026-10-04-issue-83-after-network.json',JSON.stringify(out,null,2));
  await context.close();
 }
 await run('before_choice',null,null);
 await run('rejected','0',null);
 await run('accepted','1',null);
 await run('accept_now',null,'accept');
 await run('revoke_now','1','revoke');
 await browser.close();
 fs.writeFileSync('memory/data/2026-10-04-issue-83-after-network.json',JSON.stringify(out,null,2));
 console.log(JSON.stringify(out));
})().catch(e=>{console.error(e.message);process.exit(1)});