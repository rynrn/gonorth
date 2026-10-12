const { chromium } = require('playwright');
const fs = require('fs');
const URL = 'https://gonorth.co.il/yizreel-valley-day-trip/?private_note=GN_PRIVACY_TEST_20261006#private-fragment';
const OUT = 'memory/data/2026-10-06-issue-83-legacy-consent-browser.json';
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
  const results = [];
  const scenarios = [
    { name: 'legacy_only', legacy: '1', consent: null, action: null, wait: 22000 },
    { name: 'new_visitor', legacy: null, consent: null, action: null, wait: 2500 },
    { name: 'rejected', legacy: '1', consent: '0', action: null, wait: 2500 },
    { name: 'accepted', legacy: '1', consent: '1', action: null, wait: 3500 },
    { name: 'accept_now', legacy: '1', consent: null, action: 'accept', wait: 3500 },
    { name: 'revoke_now', legacy: '1', consent: '1', action: 'revoke', wait: 2500 },
  ];
  try {
    for (const scenario of scenarios) {
      const context = await browser.newContext({ viewport: { width: 390, height: 844 }, locale: 'he-IL' });
      await context.addInitScript(({legacy,consent}) => {
        if (sessionStorage.getItem('gn_qa_seeded')) return;
        if (legacy !== null) localStorage.setItem('gn_cookie_ok', legacy);
        if (consent !== null) localStorage.setItem('gn_analytics_consent_v2', consent);
        sessionStorage.setItem('gn_qa_seeded', '1');
      }, { legacy: scenario.legacy, consent: scenario.consent });
      const page = await context.newPage();
      const requests = [];
      const errors = [];
      page.on('request', req => {
        try {
          const u = new global.URL(req.url());
          if (/googletagmanager\.com|google-analytics\.com|news\.google\.com/.test(u.hostname))
            requests.push({ host: u.hostname, path: u.pathname, sentinel: req.url().includes('GN_PRIVACY_TEST_20261006') });
        } catch (e) {}
      });
      page.on('pageerror', e => errors.push(e.message.slice(0, 160)));
      await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
      await page.waitForTimeout(scenario.wait);
      const before = await page.evaluate(() => ({
        choice: localStorage.getItem('gn_analytics_consent_v2'),
        legacy: localStorage.getItem('gn_cookie_ok'),
        banner: getComputedStyle(document.querySelector('#gn-cookie-banner')).display,
        gtmScripts: document.querySelectorAll('script[src*="googletagmanager.com/gtm.js"]').length,
        preferredScripts: document.querySelectorAll('script[src*="news.google.com/swg/js/v1/publisher.js"]').length,
      }));
      const requestsBeforeAction = requests.length;
      if (scenario.action === 'accept') {
        await page.click('#gn-cookie-accept');
        await page.waitForTimeout(3500);
      } else if (scenario.action === 'revoke') {
        await page.click('#gn-cookie-settings');
        await page.click('#gn-cookie-reject');
        await page.waitForTimeout(3500);
      }
      const after = await page.evaluate(() => ({
        choice: localStorage.getItem('gn_analytics_consent_v2'),
        legacy: localStorage.getItem('gn_cookie_ok'),
        banner: getComputedStyle(document.querySelector('#gn-cookie-banner')).display,
        gtmScripts: document.querySelectorAll('script[src*="googletagmanager.com/gtm.js"]').length,
        preferredScripts: document.querySelectorAll('script[src*="news.google.com/swg/js/v1/publisher.js"]').length,
      }));
      const googleBefore = requests.slice(0, requestsBeforeAction);
      results.push({ name: scenario.name, before, after, googleBefore, googleAfter: requests.slice(requestsBeforeAction), errors });
      await context.close();
    }
  } finally { await browser.close(); }
  const byName = Object.fromEntries(results.map(r => [r.name,r]));
  function noGoogle(r) { return r.googleBefore.length === 0 && r.before.gtmScripts === 0 && r.before.preferredScripts === 0; }
  const checks = {
    legacy_prompts: byName.legacy_only.before.banner !== 'none' && byName.legacy_only.before.choice === null,
    legacy_does_not_load_google: noGoogle(byName.legacy_only),
    new_visitor_does_not_load_google: noGoogle(byName.new_visitor),
    rejected_does_not_load_google: noGoogle(byName.rejected),
    accepted_loads_gtm: byName.accepted.before.gtmScripts === 1 && byName.accepted.googleBefore.some(r => r.host === 'www.googletagmanager.com'),
    accept_now_loads_gtm: byName.accept_now.after.choice === '1' && byName.accept_now.after.gtmScripts === 1,
    revoke_blocks_next_load: byName.revoke_now.after.choice === '0' && byName.revoke_now.after.gtmScripts === 0 && byName.revoke_now.after.preferredScripts === 0,
    no_sentinel_to_google: results.every(r => [...r.googleBefore,...r.googleAfter].every(x => !x.sentinel)),
    no_page_errors: results.every(r => r.errors.length === 0)
  };
  fs.writeFileSync(OUT, JSON.stringify({ checkedAt: new Date().toISOString(), checks, scenarios: results }, null, 2));
  console.log(JSON.stringify({ checks, scenarios: results.map(r => ({ name:r.name, before:r.before, after:r.after, googleBeforeCount:r.googleBefore.length, googleAfterCount:r.googleAfter.length, errors:r.errors })) }));
  if (Object.values(checks).some(x => !x)) process.exit(1);
})().catch(e => { console.error(e.stack || e.message); process.exit(1); });