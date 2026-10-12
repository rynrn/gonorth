const { chromium }=require('playwright');
const fs=require('fs');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const rows=[];
 try{
  for(const width of [375,390,768])for(const consent of ['1','0']){
   const context=await browser.newContext({viewport:{width,height:844},locale:'he-IL'});
   await context.addInitScript(v=>localStorage.setItem('gn_analytics_consent_v2',v),consent);
   const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message.slice(0,120)));
   await page.goto('https://gonorth.co.il/yizreel-valley-day-trip/',{waitUntil:'domcontentloaded',timeout:60000});
   await page.waitForTimeout(consent==='1'?3700:1200);
   const state=await page.evaluate(()=>{
    const iframe=document.querySelector('iframe[src^="https://news.google.com/swg/ui/v1/serviceiframe"]');
    const link=document.querySelector('[data-gn-preferred-source]');
    const style=Array.from(document.querySelectorAll('link[rel="stylesheet"]')).find(x=>x.id==='gonorth-main-css');
    return {scroll:document.documentElement.scrollWidth,client:document.documentElement.clientWidth,
      iframe:!!iframe,overflow:getComputedStyle(document.documentElement).overflowX,
      sdkRequested:!!window.__goNorthPreferredSourceScriptRequested,
      preferredLink:!!link && link.href.includes('www.google.com/preferences/source'),cssVersion:style?new URL(style.href).searchParams.get('ver'):null};
   });
   rows.push({width,consent,...state,errors});await context.close();
  }
 }finally{await browser.close()}
 const checks={
  all_no_overflow:rows.every(r=>r.scroll===r.client),
  accepted_sdk_present:rows.filter(r=>r.consent==='1').every(r=>r.iframe&&r.sdkRequested&&r.overflow==='clip'),
  rejected_sdk_absent:rows.filter(r=>r.consent==='0').every(r=>!r.iframe&&!r.sdkRequested),
  preferred_link_kept:rows.every(r=>r.preferredLink),
  css_busted:rows.every(r=>r.cssVersion==='2.9.8'),
  no_page_errors:rows.every(r=>!r.errors.length)
 };
 fs.writeFileSync('memory/data/2026-10-06-issue-104-browser.json',JSON.stringify({checkedAt:new Date().toISOString(),checks,rows},null,2));
 console.log(JSON.stringify({checks,rows}));
 if(Object.values(checks).some(v=>!v))process.exit(1);
})().catch(e=>{console.error(e.stack||e.message);process.exit(1)});
