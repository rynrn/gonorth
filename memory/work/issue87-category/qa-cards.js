const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 for(const width of [390,1280]){
  const p=await browser.newPage({viewport:{width,height:900}});
  await p.goto('https://gonorth.co.il/',{waitUntil:'domcontentloaded'});
  await p.locator('.gn-categories').scrollIntoViewIfNeeded();
  await p.waitForTimeout(1000);
  await p.locator('.gn-cat__card--tours').scrollIntoViewIfNeeded();
  await p.waitForTimeout(1000);
  const data=await p.evaluate(()=>[...document.querySelectorAll('.gn-cat__card--restaurants-sub img,.gn-cat__card--tours img')].map(i=>({src:i.currentSrc,loaded:i.complete&&i.naturalWidth>0,width:i.naturalWidth,alt:i.alt})));
  await p.locator('.gn-categories').screenshot({path:'memory/work/issue87-category/category-grid-'+width+'.png'});
  console.log(JSON.stringify({width,cards:data,overflow:await p.evaluate(()=>document.documentElement.scrollWidth-innerWidth)}));
  await p.close();
 }
 await browser.close();
})();
