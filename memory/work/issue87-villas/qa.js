const { chromium } = require('playwright');
(async()=>{
 const b=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const out=[];
 for(const width of [390,1280]){
  const p=await b.newPage({viewport:{width,height:850},deviceScaleFactor:1});
  const r=await p.goto('https://gonorth.co.il/villas-israel-north-guide/',{waitUntil:'domcontentloaded',timeout:30000});
  await p.waitForTimeout(800);
  const x=await p.evaluate(()=>{
   const needle='villa-avdon-itay-sulimani-cc-by-sa-40-2026.jpg';
   const imgs=[...document.images].filter(i=>i.currentSrc.includes(needle));
   const credit=[...document.querySelectorAll('.gn-photo-credit')];
   return {h1:document.querySelector('h1')?.textContent?.trim(),images:imgs.length,loaded:imgs.every(i=>i.complete&&i.naturalWidth>0),credit:credit.length,credit_visible:credit.every(e=>e.getBoundingClientRect().width>0),old_image:document.documentElement.innerHTML.includes('place_photo-33.jpg'),overflow:document.documentElement.scrollWidth-innerWidth,og:document.querySelector('meta[property="og:image"]')?.content,canonical:document.querySelector('link[rel="canonical"]')?.href};
  });
  await p.screenshot({path:'C:/dev/gonorth/memory/work/issue87-villas/public-'+width+'.png',fullPage:false});
  out.push({width,http:r.status(),...x});
  await p.close();
 }
 await b.close();
 console.log(JSON.stringify(out));
})().catch(e=>{console.error(e.message);process.exit(1)});

