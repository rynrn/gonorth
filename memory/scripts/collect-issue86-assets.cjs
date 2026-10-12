// Cold same-origin asset sample for issue #86. No authentication or site writes.
const fs = require('fs');
const { chromium } = require('playwright');

const chrome = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const samples = [
  {kind: 'archive', url: 'https://gonorth.co.il/places/'},
  {kind: 'place', url: 'https://gonorth.co.il/places/%d7%a7%d7%99%d7%90%d7%a7-%d7%9b%d7%a0%d7%a8%d7%aa/'},
  {kind: 'article', url: 'https://gonorth.co.il/tours-kayaks-north-israel/'}
];

(async () => {
  const browser = await chromium.launch({executablePath: chrome, headless: true});
  const rows = [];
  try {
    for (const item of samples) {
      const context = await browser.newContext({viewport: {width: 390, height: 844}, deviceScaleFactor: 1});
      const page = await context.newPage();
      const session = await context.newCDPSession(page);
      await session.send('Network.setCacheDisabled', {cacheDisabled: true});
      const response = await page.goto(item.url, {waitUntil: 'domcontentloaded', timeout: 45000});
      await page.waitForTimeout(3000);
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await page.waitForTimeout(3000);
      const resources = await page.evaluate(() => performance.getEntriesByType('resource')
        .filter(r => { try { return new URL(r.name).origin === location.origin; } catch { return false; } })
        .map(r => ({path: new URL(r.name).pathname, type: r.initiatorType,
          transfer: r.transferSize, decoded: r.decodedBodySize})));
      rows.push({kind: item.kind, url: item.url, status: response.status(),
        checked_at: new Date().toISOString(), viewport: '390x844', resources});
      await context.close();
    }
  } finally {
    await browser.close();
  }
  const output = process.argv[2];
  if (!output) throw new Error('output JSON path required');
  fs.writeFileSync(output, JSON.stringify(rows, null, 2) + '\n');
  console.log(rows.map(r => `${r.kind}: HTTP ${r.status}, ${r.resources.length} resources`).join('\n'));
})().catch(error => { console.error(error.message); process.exitCode = 1; });
