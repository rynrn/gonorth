const { chromium } = require('playwright');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const url='https://gonorth.co.il/hanukkah-north-israel/';
 const out=[];
 for(const cfg of [{name:'desktop',w:1440,h:1000},{name:'mobile',w:390,h:844}]){
  const page=await browser.newPage({viewport:{width:cfg.w,height:cfg.h},locale:'he-IL'});
  const errors=[];
  page.on('pageerror',e=>errors.push(e.message));
  await page.goto(url,{waitUntil:'networkidle',timeout:60000});
  const metrics=await page.evaluate(()=>({
    title:document.title,
    lang:document.documentElement.lang,
    dir:document.documentElement.dir||document.body.dir,
    h1:document.querySelectorAll('h1').length,
    h2:document.querySelectorAll('article h2').length,
    canonical:document.querySelector('link[rel="canonical"]')?.href||'',
    hero:document.querySelector('article img[src*="hanukkah-north-israel-2026"]')?.currentSrc||document.querySelector('img[src*="hanukkah-north-israel-2026"]')?.currentSrc||'',
    heroAlt:document.querySelector('img[src*="hanukkah-north-israel-2026"]')?.alt||'',
    overflow:document.documentElement.scrollWidth>document.documentElement.clientWidth,
    bodyWidth:document.documentElement.scrollWidth,
    viewport:document.documentElement.clientWidth,
    backlinksText:document.body.innerText.includes('עדכון אירועי חנוכה 2026')
  }));
  await page.screenshot({path:'memory/screenshots/2026-09-26-issue-54-'+cfg.name+'.png',fullPage:true});
  out.push({name:cfg.name,...metrics,pageErrors:errors});
  await page.close();
 }
 await browser.close();
 console.log(JSON.stringify(out,null,2));
})().catch(e=>{console.error(e);process.exit(1)});
