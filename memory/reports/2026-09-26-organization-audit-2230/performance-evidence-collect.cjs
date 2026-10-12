const fs=require('fs');
const {execFileSync}=require('child_process');
const dir=__dirname;
const places=JSON.parse(fs.readFileSync(dir+'/shared-places.json','utf8'));
const samples=[['archive','https://gonorth.co.il/places/'],['place',places.find(p=>Number(p.id)===423).link],['article','https://gonorth.co.il/tours-kayaks-north-israel/']];
const attr=(tag,key)=>{const m=tag.match(new RegExp('\\b'+key+'=["\x27]([^"\x27]*)["\x27]','i'));return m?m[1].replaceAll('&#038;','&').replaceAll('&amp;','&'):null;};
(async()=>{let out=[];for(const [name,url]of samples){
 const htmlFile=dir+'/performance-evidence-'+name+'.html';const headFile=dir+'/performance-evidence-'+name+'-headers.txt';
 const metrics=JSON.parse(execFileSync('curl.exe',['-L','-sS','--compressed','--max-time','30','-D',headFile,'-o',htmlFile,'-w','%{json}',url],{encoding:'utf8'}));
 const h=fs.readFileSync(htmlFile,'utf8');const css=[...h.matchAll(/<link\b[^>]*>/gi)].map(m=>m[0]).filter(t=>attr(t,'rel')==='stylesheet').map(t=>attr(t,'href'));
 const js=[...h.matchAll(/<script\b[^>]*>/gi)].map(m=>attr(m[0],'src')).filter(u=>u&&u.startsWith('http'));
 const images=[...h.matchAll(/<img\b[^>]*>/gi)].map(m=>({src:attr(m[0],'src'),loading:attr(m[0],'loading'),fetchpriority:attr(m[0],'fetchpriority'),width:attr(m[0],'width'),height:attr(m[0],'height'),srcset:!!attr(m[0],'srcset')}));
 // Keep public content only; inline scripts can contain public keys and nonces, so do not retain raw HTML.
 fs.writeFileSync(htmlFile, '<!-- Raw page removed after extraction to avoid retaining incidental public API keys/nonces. Inventory is in performance-evidence-live.json. -->');
 out.push({name,url,at:new Date().toISOString(),http:metrics.http_code,ttfb_seconds:metrics.time_starttransfer,total_seconds:metrics.time_total,transferred_bytes:metrics.size_download,html_utf8_bytes:Buffer.byteLength(h),css,js,images});
 }
 const assetUrls=[...new Set(out.flatMap(p=>[...p.css,...p.js,...p.images.map(x=>x.src).filter(x=>x&&x.startsWith('http'))]))].filter(u=>u.startsWith('https://gonorth.co.il'));
 const assets=[];for(const url of assetUrls){try{let r=await fetch(url);let a=await r.arrayBuffer();assets.push({url,status:r.status,decoded_bytes:a.byteLength,content_type:r.headers.get('content-type'),encoding:r.headers.get('content-encoding'),cache_control:r.headers.get('cache-control')});}catch(e){assets.push({url,error:e.message});}}
 fs.writeFileSync(dir+'/performance-evidence-live.json',JSON.stringify({method:'Three serial curl GETs, compression enabled; DOM source inventory; one fetch per unique first-party static asset. Asset bytes decoded not browser transfer; excludes srcset alternatives CSS subresources and dynamic loading.',pages:out,assets},null,2));
 console.log(JSON.stringify(out.map(p=>({name:p.name,http:p.http,ttfb:p.ttfb_seconds,html_bytes:p.html_utf8_bytes,transfer:p.transferred_bytes,css:p.css.length,js:p.js.length,images:p.images.length,css_decoded:assets.filter(a=>p.css.includes(a.url)).reduce((s,a)=>s+(a.decoded_bytes||0),0),js_decoded:assets.filter(a=>p.js.includes(a.url)).reduce((s,a)=>s+(a.decoded_bytes||0),0)})),null,2));
 console.log(JSON.stringify(assets.filter(a=>a.content_type?.startsWith('image')).sort((a,b)=>b.decoded_bytes-a.decoded_bytes).slice(0,8),null,2));
})();
