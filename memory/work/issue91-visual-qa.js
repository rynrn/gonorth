const {chromium}=require('playwright');
const fs=require('fs');
const rows=JSON.parse(fs.readFileSync('memory/data/2026-10-06-issue-91-media-rights-audit.json','utf8'));
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const results=[];
 for(let start=0;start<rows.length;start+=5){
  const batch=rows.slice(start,start+5);
  const page=await browser.newPage({viewport:{width:1240,height:900},deviceScaleFactor:1});
  const cards=batch.map((r,i)=>'<article><h3>'+String(start+i+1)+'. '+r.place_title+' (#'+r.place_id+')</h3><img src="'+r.media_url+'" alt="'+r.what_shown_per_alt+'"><p>ALT: '+r.what_shown_per_alt+'</p></article>').join('');
  await page.setContent('<html lang="he" dir="rtl"><meta charset="utf-8"><style>body{font-family:Arial;margin:20px;background:#eee}.grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}article{background:#fff;padding:8px;border:1px solid #ccc}h3{height:58px;font-size:16px;margin:4px}img{width:100%;height:200px;object-fit:contain;background:#ddd}p{font-size:14px}</style><div class="grid">'+cards+'</div></html>');
  await page.waitForTimeout(1500);
  const loaded=await page.locator('img').evaluateAll(imgs=>imgs.map(x=>({ok:x.complete&&x.naturalWidth>0,w:x.naturalWidth,h:x.naturalHeight})));
  await page.screenshot({path:'memory/work/issue91-contact-'+(start/5+1)+'.png',fullPage:true});
  results.push({batch:start/5+1,loaded});
  await page.close();
 }
 await browser.close();
 console.log(JSON.stringify(results));
})().catch(e=>{console.error(e);process.exit(1)});
