const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const url='https://gonorth.co.il/places/%d7%94%d7%98%d7%a0%d7%a7-%d7%94%d7%a1%d7%95%d7%a8%d7%99-%d7%94%d7%94%d7%a4%d7%95%d7%9a/';
 for(const width of [390,1280]){
  const p=await browser.newPage({viewport:{width,height:900},locale:'he-IL'}), errors=[];
  p.on('pageerror',e=>errors.push(e.message));
  const response=await p.goto(url,{waitUntil:'networkidle',timeout:60000});
  const result=await p.evaluate(()=>{
   const gallery=document.querySelector('.gn-place-gallery');
   const images=[...(gallery?.querySelectorAll('img')||[])].map(i=>({alt:i.alt,src:i.currentSrc,srcset:!!i.getAttribute('srcset'),lazy:i.loading,naturalWidth:i.naturalWidth}));
   const hero=document.querySelector('main img');
   const captions=[...(gallery?.querySelectorAll('figcaption')||[])].map(c=>({text:c.innerText,links:[...c.querySelectorAll('a')].map(a=>a.href)}));
   return {title:document.querySelector('h1')?.innerText,hero:hero?.currentSrc,images,captions,official:!!gallery?.querySelector('a[href="https://www.parks.org.il/new/syrian-tank/"]'),overflow:document.documentElement.scrollWidth>innerWidth,scrollWidth:document.documentElement.scrollWidth};
  });
  console.log(JSON.stringify({width,status:response.status(),errors,...result}));
  await p.close();
 }
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
