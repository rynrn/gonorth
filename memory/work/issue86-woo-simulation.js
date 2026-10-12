const {chromium}=require('playwright');
const fs=require('fs');
const url='https://gonorth.co.il/tours-kayaks-north-israel/';
const blockedCss=new Set([
 '/wp-content/cache/autoptimize/autoptimize_single_1fbf35126001b27c9d77720a9e941437.php',
 '/wp-content/cache/autoptimize/autoptimize_single_cd360d29346139a01939fcc0f29f85f8.php',
 '/wp-content/cache/autoptimize/autoptimize_single_bee817902507e0ea9b2a85da3d85d2e3.php',
 '/wp-content/cache/autoptimize/autoptimize_single_c812c49d797de159697f02bafc3d9c51.php'
]);
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const results=[];
 for(const width of [390,1280]){
  for(const mode of ['baseline','without_woo']){
   const context=await browser.newContext({viewport:{width,height:900},locale:'he-IL',reducedMotion:'reduce'});
   const page=await context.newPage();
   const errors=[];
   page.on('pageerror',e=>errors.push(e.message.slice(0,150)));
   let blocked=0;
   if(mode==='without_woo') await page.route('**/*',async route=>{
    const u=new URL(route.request().url());const path=u.pathname;
    if(path.includes('/plugins/woocommerce/')||blockedCss.has(path)){
      blocked++;
      await route.fulfill({status:200,contentType:path.endsWith('.js')?'application/javascript':'text/css',body:''});
    }else await route.continue();
   });
   const response=await page.goto(url,{waitUntil:'domcontentloaded',timeout:45000});
   await page.waitForTimeout(3500);
   const state=await page.evaluate(()=>({
    title:document.title,
    h1:document.querySelector('h1')?.innerText||'',
    docWidth:document.documentElement.scrollWidth,
    bodyWidth:document.body.scrollWidth,
    toc:document.querySelectorAll('.gn-toc a').length,
    placeLinks:[...document.querySelectorAll('a[href*="/places/"]')].length,
    images:[...document.images].filter(x=>x.complete&&x.naturalWidth>0).length
   }));
   const shot='C:/dev/gonorth/memory/work/issue86-'+width+'-'+mode+'.png';
   await page.screenshot({path:shot,fullPage:true});
   results.push({width,mode,status:response.status(),blocked,errors,state,shot});
   await context.close();
  }
 }
 await browser.close();
 fs.writeFileSync('C:/dev/gonorth/memory/data/2026-10-06-issue-86-woo-simulation.json',JSON.stringify(results,null,2));
 console.log(JSON.stringify(results.map(({shot,...r})=>r)));
})().catch(e=>{console.error(e.message);process.exit(1)});
