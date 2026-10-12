const { chromium } = require('playwright');
const fs = require('fs');
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
  const home = 'https://gonorth.co.il/';
  const place = 'https://gonorth.co.il/places/%d7%94%d7%9b%d7%a4%d7%a8-%d7%94%d7%90%d7%99%d7%a0%d7%93%d7%99%d7%90%d7%a0%d7%99-%d7%92%d7%9c%d7%9e%d7%a4%d7%99%d7%a0%d7%92-%d7%91%d7%a8%d7%9e%d7%aa-%d7%94%d7%92%d7%95%d7%9c%d7%9f/';
  const results = {};

  async function addNetworkStubs(page, sdkMode, counter) {
    await page.route('**/gtag/js**', route => route.fulfill({ status: 200, contentType: 'application/javascript', body: '' }));
    await page.route('https://news.google.com/swg/js/v1/publisher.js', route => {
      counter.count++;
      if (sdkMode === 'blocked') return route.abort('blockedbyclient');
      return route.fulfill({
        status: 200,
        contentType: 'application/javascript',
        body: "(function(){var q=self.PREFERRED_SOURCE||[];var api={init:function(o){self.__gnPsInit=o;},addPreferredSource:function(){self.__gnPsAdd=(self.__gnPsAdd||0)+1;}};q.forEach(function(cb){cb(api);});})();"
      });
    });
  }

  {
    const context = await browser.newContext({ locale: 'he-IL', viewport: { width: 1440, height: 1000 } });
    const page = await context.newPage();
    const sdk = { count: 0 };
    await addNetworkStubs(page, 'ok', sdk);
    await page.goto(home, { waitUntil: 'networkidle', timeout: 60000 });
    const initial = await page.evaluate(() => ({
      components: document.querySelectorAll('[data-gn-preferred-source-component]').length,
      localScripts: Array.from(document.scripts).filter(s => /preferred-source|autoptimize/.test(s.src)).length,
      sdkTags: Array.from(document.scripts).filter(s => s.src === 'https://news.google.com/swg/js/v1/publisher.js').length,
      href: document.querySelector('[data-gn-preferred-source]')?.href || '',
      dir: document.documentElement.dir || document.body.dir,
      overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
    }));
    await page.evaluate(() => {
      const b = document.querySelector('[data-gn-preferred-source]');
      b.addEventListener('click', e => e.preventDefault(), { once: true });
      b.click();
    });
    const events = await page.evaluate(() => (window.dataLayer || []).map(x => Array.from(x || [])).filter(x => x[0] === 'event' && x[1] === 'preferred_source_click'));
    results.noConsent = { ...initial, sdkRequests: sdk.count, preferredEvents: events.length };
    await context.close();
  }

  {
    const context = await browser.newContext({ locale: 'he-IL', viewport: { width: 1440, height: 1000 } });
    await context.addInitScript(() => { try { localStorage.setItem('gn_cookie_ok', '1'); } catch (e) {} });
    const page = await context.newPage();
    const sdk = { count: 0 };
    const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    await addNetworkStubs(page, 'ok', sdk);
    await page.goto(place, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForFunction(() => document.querySelector('[data-gn-preferred-source]')?.dataset.gnPreferredSourceBound === '1');
    await page.click('[data-gn-preferred-source]');
    await page.waitForTimeout(150);
    const state = await page.evaluate(() => {
      const events = (window.dataLayer || []).map(x => Array.from(x || [])).filter(x => x[0] === 'event' && x[1] === 'preferred_source_click');
      return {
        components: document.querySelectorAll('[data-gn-preferred-source-component]').length,
        sdkTags: Array.from(document.scripts).filter(s => s.src === 'https://news.google.com/swg/js/v1/publisher.js').length,
        sdkControl: document.querySelector('script[src="https://news.google.com/swg/js/v1/publisher.js"]')?.getAttribute('preferred-sources-control') || '',
        init: window.__gnPsInit || null,
        addCalls: window.__gnPsAdd || 0,
        preferredEvents: events.length,
        payload: events[0] ? events[0][2] : null,
        overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
      };
    });
    results.consentPlace = { ...state, sdkRequests: sdk.count, pageErrors: errors };
    await context.close();
  }

  {
    const context = await browser.newContext({ locale: 'he-IL', viewport: { width: 390, height: 844 } });
    await context.addInitScript(() => { try { localStorage.setItem('gn_cookie_ok', '1'); } catch (e) {} });
    const page = await context.newPage();
    const sdk = { count: 0 };
    const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    await addNetworkStubs(page, 'blocked', sdk);
    await page.goto(home, { waitUntil: 'networkidle', timeout: 60000 });
    await page.evaluate(() => {
      const b = document.querySelector('[data-gn-preferred-source]');
      b.addEventListener('click', e => e.preventDefault(), { once: true });
      b.click();
    });
    await page.waitForTimeout(150);
    const state = await page.evaluate(() => {
      const c = document.querySelector('[data-gn-preferred-source-component]');
      const b = document.querySelector('[data-gn-preferred-source]');
      const events = (window.dataLayer || []).map(x => Array.from(x || [])).filter(x => x[0] === 'event' && x[1] === 'preferred_source_click');
      return {
        components: document.querySelectorAll('[data-gn-preferred-source-component]').length,
        preferredEvents: events.length,
        href: b?.href || '',
        bodyPresent: !!document.body,
        overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
        componentWidth: c ? Math.round(c.getBoundingClientRect().width) : 0,
        viewport: document.documentElement.clientWidth
      };
    });
    await page.screenshot({ path: 'memory/screenshots/2026-09-26-issue-47-mobile.png', fullPage: true });
    results.blockedMobile = { ...state, sdkRequests: sdk.count, pageErrors: errors.filter(x => !/ERR_FAILED/.test(x)) };
    await context.close();
  }

  fs.writeFileSync('memory/data/2026-09-26-issue-47-runtime-qa.json', JSON.stringify(results, null, 2));
  console.log(JSON.stringify(results, null, 2));
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
