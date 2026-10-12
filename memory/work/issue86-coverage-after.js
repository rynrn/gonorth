const { chromium } = require('playwright');
const fs = require('fs');
const pages = [
  ['archive', 'https://gonorth.co.il/places/'],
  ['place', 'https://gonorth.co.il/places/%d7%a7%d7%99%d7%90%d7%a7-%d7%9b%d7%a0%d7%a8%d7%aa/'],
  ['article', 'https://gonorth.co.il/tours-kayaks-north-israel/']
];
(async () => {
  const browser = await chromium.launch({headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  const out = [];
  for (const [kind, url] of pages) {
    const context = await browser.newContext({viewport:{width:390,height:844},locale:'he-IL'});
    const page = await context.newPage();
    await page.coverage.startCSSCoverage({resetOnNavigation:false});
    await page.coverage.startJSCoverage({resetOnNavigation:false});
    const response = await page.goto(url, {waitUntil:'domcontentloaded',timeout:45000});
    await page.waitForTimeout(4500);
    const css = await page.coverage.stopCSSCoverage();
    const js = await page.coverage.stopJSCoverage();
    const resources = await page.evaluate(() => performance.getEntriesByType('resource').filter(x => x.name.startsWith(location.origin)).map(x => ({
      path: new URL(x.name).pathname,
      type: x.initiatorType,
      transfer: x.transferSize,
      decoded: x.decodedBodySize
    })));
    const measure = x => ({path: new URL(x.url).pathname,total:(x.text||"" ).length,used:(x.ranges||[]).reduce((s,r)=>s+r.end-r.start,0)});
    out.push({kind,status:response.status(),resources,css:css.filter(x=>x.url.startsWith('https://gonorth.co.il')).map(measure),js:js.filter(x=>x.url.startsWith('https://gonorth.co.il')).map(x=>({path:new URL(x.url).pathname,total:(x.source||"" ).length,functions:x.functions.length}))});
    await context.close();
  }
  await browser.close();
  fs.writeFileSync('C:/dev/gonorth/memory/data/2026-10-06-issue-86-resource-coverage-after.json', JSON.stringify(out,null,2));
  for (const p of out) {
    const top = p.resources.filter(x=>/\.(css|js)$/.test(x.path)).sort((a,b)=>b.decoded-a.decoded).slice(0,12);
    console.log(JSON.stringify({kind:p.kind,status:p.status,cssFiles:p.css.length,jsFiles:p.js.length,top}));
  }
})().catch(e=>{console.error(e.message);process.exit(1)});




