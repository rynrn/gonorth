const {chromium}=require('playwright'),{PNG}=require('pngjs'),pixelmatch=require('pixelmatch').default,fs=require('fs');
const items=JSON.parse(fs.readFileSync('C:/dev/gonorth/memory/data/2026-10-06-issue-86-editorial-inventory.json','utf8')).filter(x=>[517].includes(x.id));
const blocked=p=>p.includes('autoptimize_single_f744e4881044ddcf68ff999850aa6f7f.php')||p.includes('/plugins/geodirectory/assets/aui/js/geodirectory.min.js')||p.includes('bootstrap.bundle.min.js');
(async()=>{const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),out=[];
for(const item of items)for(const width of [390,1280]){const images=[];
for(const mode of ['baseline','baseline']){const context=await browser.newContext({viewport:{width,height:900},locale:'he-IL',reducedMotion:'reduce'}),page=await context.newPage(),errors=[];let blockedCount=0;
page.on('pageerror',e=>errors.push(e.message.slice(0,150)));
if(mode==='without_geo')await page.route('**/*',async route=>{const p=new URL(route.request().url()).pathname;if(blocked(p)){blockedCount++;await route.fulfill({status:200,contentType:p.endsWith('.js')?'application/javascript':'text/css',body:''})}else await route.continue()});
const response=await page.goto(item.url,{waitUntil:'domcontentloaded',timeout:45000});await page.waitForTimeout(3000);const shot=await page.screenshot({fullPage:true,animations:'disabled'});fs.writeFileSync('C:/dev/gonorth/memory/work/issue86-geo-baseline-repeat-517-'+width+'-'+mode+'.png',shot);images.push(PNG.sync.read(shot));
out.push({id:item.id,width,mode,status:response.status(),errors,blockedCount,dimensions:[images.at(-1).width,images.at(-1).height]});await context.close()}
const [a,b]=images,r=out.at(-1);const diff=new PNG({width:a.width,height:a.height});r.changedPixels=a.width===b.width&&a.height===b.height?pixelmatch(a.data,b.data,diff.data,a.width,a.height,{threshold:0.1}):'dimension mismatch';if(typeof r.changedPixels==='number')fs.writeFileSync('C:/dev/gonorth/memory/work/issue86-geo-visual-517-'+width+'-diff.png',PNG.sync.write(diff));r.totalPixels=a.width*a.height;console.log(JSON.stringify(r))}
await browser.close();fs.writeFileSync('C:/dev/gonorth/memory/data/2026-10-06-issue-86-geo-baseline-repeat.json',JSON.stringify(out,null,2))})().catch(e=>{console.error(e);process.exit(1)});



