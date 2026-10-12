const {chromium}=require('playwright'),fs=require('fs');
const items=JSON.parse(fs.readFileSync('C:/dev/gonorth/memory/data/2026-10-06-issue-86-editorial-inventory.json','utf8')).filter(x=>x.type==='post'||x.id===436);
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 let index=0;const out=[];
 async function worker(){
  const context=await browser.newContext({viewport:{width:390,height:844},locale:'he-IL'});
  const page=await context.newPage();
  let pageErrors=[];page.on('pageerror',e=>pageErrors.push(e.message.slice(0,120)));
  while(index<items.length){
   const item=items[index++];pageErrors=[];
   try{
    const response=await page.goto(item.url,{waitUntil:'domcontentloaded',timeout:30000});
    await page.waitForTimeout(400);
    await page.evaluate(()=>window.scrollTo(0,document.body.scrollHeight));
    await page.waitForTimeout(350);
    const state=await page.evaluate(()=>({
      docWidth:document.documentElement.scrollWidth,
      h1:!!document.querySelector('h1'),
      footer:!!document.querySelector('footer'),
      loadedImages:[...document.images].filter(x=>x.complete&&x.naturalWidth>0).length,
      placeLinks:document.querySelectorAll('main a[href*="/places/"]').length,
      commerceNodes:document.querySelectorAll('main .woocommerce, main [class*="wc-block"], main [data-block-name*="woocommerce"], main form.cart, main .product').length,
      geodirNodes:document.querySelectorAll('main [class*="geodir"], main [id*="geodir"]').length,
      wooCss:[...document.querySelectorAll('link[id]')].filter(e=>/^(woocommerce-|wc-blocks)/.test(e.id)).length,
      wooJs:[...document.querySelectorAll('script[src][id]')].filter(e=>/^(wc-|woocommerce-|sourcebuster)/.test(e.id)).length
    }));
    out.push({...item,status:response.status(),pageErrors:[...pageErrors],...state});
   }catch(e){out.push({...item,error:e.message.slice(0,180)})}
  }
  await context.close();
 }
 await Promise.all([worker(),worker(),worker()]);
 await browser.close();
 out.sort((a,b)=>a.id-b.id);
 fs.writeFileSync('C:/dev/gonorth/memory/data/2026-10-06-issue-86-all25-live-390.json',JSON.stringify(out,null,2));
 console.log(JSON.stringify({count:out.length,errors:out.filter(x=>x.error).length,commerce:out.filter(x=>x.commerceNodes>0).map(x=>({id:x.id,slug:x.slug,n:x.commerceNodes})),geodir:out.filter(x=>x.geodirNodes>0).map(x=>({id:x.id,slug:x.slug,n:x.geodirNodes})),woo:out.filter(x=>x.wooCss>0||x.wooJs>0).length}));
})().catch(e=>{console.error(e.message);process.exit(1)});
