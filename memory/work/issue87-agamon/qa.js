const { chromium } = require('playwright');
(async()=>{
  const b=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  const results=[];
  for(const width of [390,1280]){
    const p=await b.newPage({viewport:{width,height:850},deviceScaleFactor:1});
    const r=await p.goto('https://gonorth.co.il/agamon-hahula-guide/',{waitUntil:'domcontentloaded',timeout:30000});
    await p.waitForTimeout(800);
    const x=await p.evaluate(()=>{
      const html=document.documentElement;
      const imgs=[...document.images].filter(i=>i.currentSrc.includes('agamon-hula-zeev-stein-cc-by-25-2026'));
      const credit=[...document.querySelectorAll('.gn-photo-credit')];
      const h1=document.querySelector('h1');
      return {status:document.readyState,title:h1?.textContent?.trim(),img_count:imgs.length,img_loaded:imgs.every(i=>i.complete&&i.naturalWidth>0),credit_count:credit.length,credit_visible:credit.every(e=>e.getBoundingClientRect().width>0),old_image:document.documentElement.innerHTML.includes('place_photo-25.jpg'),overflow:html.scrollWidth-innerWidth,og:document.querySelector('meta[property="og:image"]')?.content,canonical:document.querySelector('link[rel="canonical"]')?.href};
    });
    await p.screenshot({path:'C:/dev/gonorth/memory/work/issue87-agamon/public-'+width+'.png',fullPage:false});
    results.push({width,http:r.status(),...x});
    await p.close();
  }
  await b.close();
  console.log(JSON.stringify(results));
})().catch(e=>{console.error(e.message);process.exit(1)});

