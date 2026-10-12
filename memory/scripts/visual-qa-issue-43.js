const { chromium } = require('playwright');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const url='https://gonorth.co.il/open-saturday/';
 const out=[];
 for(const cfg of [{name:'desktop',w:1440,h:1000},{name:'mobile',w:390,h:844}]){
  const page=await browser.newPage({viewport:{width:cfg.w,height:cfg.h},locale:'he-IL'});
  await page.goto(url,{waitUntil:'networkidle',timeout:60000});
  const metrics=await page.evaluate(()=>({title:document.title,dir:document.documentElement.dir||document.body.dir,h1:document.querySelectorAll('h1').length,cards:document.querySelectorAll('.gn-attribute-card').length,overflow:document.documentElement.scrollWidth>document.documentElement.clientWidth,bodyWidth:document.documentElement.scrollWidth,viewport:document.documentElement.clientWidth}));
  await page.screenshot({path:`memory/screenshots/2026-09-26-issue-43-${cfg.name}.png`,fullPage:true});
  out.push({name:cfg.name,...metrics});
  await page.close();
 }
 await browser.close();
 console.log(JSON.stringify(out,null,2));
})().catch(e=>{console.error(e);process.exit(1)});