const { chromium } = require('playwright');
const fs = require('fs');
const sentinel='GN_PRIVACY_TEST_20261004';
const home='https://gonorth.co.il/';
const guide='https://gonorth.co.il/yizreel-valley-day-trip/?private_note='+sentinel+'#private-fragment-'+sentinel;
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const out=[];
 for(const [label,url,consent] of [['pre_home',home,null],['pre_guide',guide,null],['accepted_guide',guide,'1']]){
  const context=await browser.newContext({locale:'he-IL',viewport:{width:390,height:844}});
  if(consent) await context.addInitScript(v=>localStorage.setItem('gn_cookie_ok',v),consent);
  const page=await context.newPage(); const requests=[]; const errors=[];
  page.on('request', req=>{const u=req.url(); if(/google|doubleclick|facebook|meta|hotjar|clarity|gonorth.co.il/.test(u)){let host='';let path='';try{const x=new URL(u);host=x.hostname;path=x.pathname}catch(e){} requests.push({host,path,sentinel:u.includes(sentinel),resource:req.resourceType()});}});
  page.on('pageerror',e=>errors.push(e.message.slice(0,140)));
  await page.goto(url,{waitUntil:'domcontentloaded',timeout:60000});
  await page.waitForTimeout(label==='pre_home'?22000:4500);
  const state=await page.evaluate(()=>({banner:getComputedStyle(document.querySelector('#gn-cookie-banner')).display,consent:localStorage.getItem('gn_cookie_ok'),gtmScripts:document.querySelectorAll('script[src*="googletagmanager.com/gtm.js"]').length,preferredScripts:document.querySelectorAll('script[src*="news.google.com/swg/js/v1/publisher.js"]').length,dataLayerCount:(window.dataLayer||[]).length,dataLayerLeak:(window.dataLayer||[]).some(x=>JSON.stringify(x).includes('GN_PRIVACY_TEST_20261004')),buttons:Array.from(document.querySelectorAll('#gn-cookie-banner button')).map(x=>x.textContent.trim())}));
  out.push({label,state,thirdParty:requests.filter(x=>x.host&&!x.host.endsWith('gonorth.co.il')),sentinelRequestCount:requests.filter(x=>x.sentinel).length,errors});
  await context.close();
 }
 await browser.close();
 fs.writeFileSync('memory/data/2026-10-04-issue-83-baseline-network.json',JSON.stringify(out,null,2));
 console.log(JSON.stringify(out));
})().catch(e=>{console.error(e.message);process.exit(1)});