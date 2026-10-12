const {chromium}=require('playwright'),fs=require('fs');
const urls=['https://gonorth.co.il/tours-kayaks-north-israel/','https://gonorth.co.il/places/','https://gonorth.co.il/'];
const isTarget=p=>/autoptimize_single_f744e4881044ddcf68ff999850aa6f7f|geodirectory.min.js|bootstrap.bundle.min.js/.test(p);
(async()=>{const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),out=[];
for(const width of [390,1280])for(const url of urls){const context=await browser.newContext({viewport:{width,height:900},locale:'he-IL'}),page=await context.newPage(),errors=[];
page.on('pageerror',e=>errors.push(e.message.slice(0,150)));
const r=await page.goto(url,{waitUntil:'domcontentloaded',timeout:45000});await page.waitForTimeout(1500);await page.evaluate(()=>window.scrollTo(0,document.body.scrollHeight));await page.waitForTimeout(350);
const x=await page.evaluate(()=>({h1:document.querySelector('h1')?.innerText||'',docWidth:document.documentElement.scrollWidth,footer:!!document.querySelector('footer'),links:document.querySelectorAll('a[href]').length,images:[...document.images].filter(i=>i.complete&&i.naturalWidth>0).length,assets:[...document.querySelectorAll('link[href],script[src]')].map(e=>e.href||e.src).filter(p=>/autoptimize_single_f744e4881044ddcf68ff999850aa6f7f|geodirectory.min.js|bootstrap.bundle.min.js/.test(p))}));
out.push({width,url,status:r.status(),errors,...x});await context.close()}
await browser.close();fs.writeFileSync('C:/dev/gonorth/memory/data/2026-10-06-issue-86-geo-canary-live.json',JSON.stringify(out,null,2));console.log(JSON.stringify(out.map(({h1,...r})=>r)))})().catch(e=>{console.error(e);process.exit(1)});
