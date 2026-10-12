const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
for(const width of [375,390,768]){const c=await b.newContext({viewport:{width,height:844}});await c.addInitScript(()=>localStorage.setItem('gn_analytics_consent_v2','1'));const p=await c.newPage();await p.goto('https://gonorth.co.il/yizreel-valley-day-trip/',{waitUntil:'domcontentloaded'});await p.waitForTimeout(3800);
const before=await p.evaluate(()=>({scroll:document.documentElement.scrollWidth,client:document.documentElement.clientWidth,iframes:Array.from(document.querySelectorAll('iframe')).map(x=>({src:x.src.slice(0,100),css:x.getAttribute('style')?.slice(0,150)}))}));
await p.addStyleTag({content:'html:has(iframe[src*="news.google.com/swg/ui/v1/serviceiframe"]) { overflow-x: clip; }'});
const after=await p.evaluate(()=>({scroll:document.documentElement.scrollWidth,client:document.documentElement.clientWidth,overflow:getComputedStyle(document.documentElement).overflowX}));
console.log(JSON.stringify({width,before,after}));await c.close()}await b.close()})().catch(e=>{console.error(e.message);process.exit(1)});
