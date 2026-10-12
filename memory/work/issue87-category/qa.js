const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const urls=[
 ['home','https://gonorth.co.il/'],
 ['restaurants','https://gonorth.co.il/places/category/restaurants-sub/'],
 ['tours','https://gonorth.co.il/places/category/tours/'],
 ['coffee','https://gonorth.co.il/places/category/agalot-kafe/']
 ];
 for(const width of [390,1280]){
  const page=await browser.newPage({viewport:{width,height:900},deviceScaleFactor:1});
  for(const [name,url] of urls){
   const response=await page.goto(url,{waitUntil:'domcontentloaded',timeout:30000});
   await page.locator('body').waitFor();
   if(name==='home') await page.locator('.gn-cats__grid img').first().waitFor();
   const data=await page.evaluate(()=>{
    const meta=(key)=>document.querySelector('meta[property="'+key+'"]')?.getAttribute('content')||'';
    const cards=[...document.querySelectorAll('.gn-cat__card')].filter(x=>x.className.includes('restaurants-sub')||x.className.includes('tours')).map(x=>({type:x.className,src:x.querySelector('img')?.currentSrc||'',alt:x.querySelector('img')?.alt||'',loaded:x.querySelector('img')?.complete&&x.querySelector('img')?.naturalWidth>0}));
    return {title:document.title,canonical:document.querySelector('link[rel=canonical]')?.href||'',og:meta('og:image'),ogWidth:meta('og:image:width'),ogHeight:meta('og:image:height'),twitter:document.querySelector('meta[name="twitter:card"]')?.content||'',credits:[...document.querySelectorAll('.gn-cat__credits')].map(x=>({text:x.innerText,links:[...x.querySelectorAll('a')].map(a=>a.href)})),cards,overflow:document.documentElement.scrollWidth-innerWidth};
   });
   if(width===390) await page.screenshot({path:'memory/work/issue87-category/'+name+'-390.png',fullPage:false});
   console.log(JSON.stringify({width,name,url,status:response.status(),...data}));
  }
  await page.close();
 }
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
