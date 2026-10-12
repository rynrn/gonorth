const base='http://127.0.0.1:9225';
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{
 const pages=await(await fetch(base+'/json/list')).json();
 const page=pages.find(p=>p.type==='page'&&p.url.includes('gonorth.co.il'));
 if(!page)throw Error('No GoNorth QA page');
 const ws=new WebSocket(page.webSocketDebuggerUrl);
 await new Promise((resolve,reject)=>{ws.addEventListener('open',resolve,{once:true});ws.addEventListener('error',reject,{once:true})});
 let next=1;const pending=new Map();
 ws.addEventListener('message',ev=>{const m=JSON.parse(ev.data);if(m.id&&pending.has(m.id)){const p=pending.get(m.id);pending.delete(m.id);m.error?p.reject(Error(m.error.message)):p.resolve(m.result)}});
 const send=(method,params={})=>new Promise((resolve,reject)=>{const id=next++;pending.set(id,{resolve,reject});ws.send(JSON.stringify({id,method,params}));setTimeout(()=>{if(pending.has(id)){pending.delete(id);reject(Error('CDP timeout '+method))}},20000)});
 await send('Page.enable');await send('Runtime.enable');
 for(const [name,url] of [
  ['yizreel','https://gonorth.co.il/yizreel-valley-day-trip/?gn_verify=issue94qa'],
  ['kinneret','https://gonorth.co.il/south-kinneret-day-trip/?gn_verify=issue94qa']
 ]){
  for(const width of [375,390,768,1280]){
   await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
   await send('Page.navigate',{url});await sleep(1800);
   const x=await send('Runtime.evaluate',{expression:`(()=>{const t=document.querySelector('.entry-content table, article table, table');if(!t)return {error:'table missing'};const w=t.parentElement, r=t.getBoundingClientRect(), wr=w.getBoundingClientRect();return {viewport:innerWidth,scrollWidth:document.documentElement.scrollWidth,tableWidth:Math.round(r.width),wrapperWidth:Math.round(wr.width),wrapperScroll:w.scrollWidth,wrapperClient:w.clientWidth,wrapperOverflow:getComputedStyle(w).overflowX,h1:document.querySelectorAll('h1').length,rtl:document.documentElement.dir||getComputedStyle(t).direction}})()`,returnByValue:true});
   console.log(JSON.stringify({name,width,...x.result.value}));
  }
 }
 ws.close();
})().catch(e=>{console.error(e);process.exit(1)});

