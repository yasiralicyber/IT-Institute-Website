<section class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="<?= url('/labs') ?>" class="text-sm font-semibold text-brand-600 hover:underline">← All labs</a>
            <h1 class="flex items-center gap-2 text-2xl font-black text-slate-900 dark:text-white"><?= icon('server','h-7 w-7 text-brand-600') ?> Network Lab (CCNA) <span class="rounded-full bg-brand-600 px-2 py-0.5 text-xs font-bold text-white">ADVANCED</span></h1>
            <p class="text-sm text-slate-500">Drag devices, cable them port-to-port with Cat6, assign IP / mask / gateway, then watch live validation: <span class="font-bold text-emerald-600">green = reachable</span>, <span class="font-bold text-red-600">red = misconfigured</span>.</p>
        </div>
        <div id="sysStatus" class="rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3 text-center dark:border-red-500/40 dark:bg-red-500/10">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Network Health</p>
            <p id="sysStatusText" class="text-lg font-black text-red-600">Empty</p>
        </div>
    </div>

    <div class="grid gap-3 lg:grid-cols-[210px_1fr_290px]">
        <aside class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Devices</h3>
            <div id="palette" class="space-y-1.5"></div>
            <p class="mt-3 text-[11px] text-slate-400">Click an Ethernet port, then a matching port on another device to run a Cat6 cable.</p>
            <button onclick="NET.clear()" class="mt-3 w-full rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Clear board</button>
        </aside>

        <div class="relative overflow-hidden rounded-2xl border border-slate-300 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:22px_22px] dark:border-white/10 dark:bg-slate-950" style="min-height:560px">
            <svg id="wires" class="pointer-events-none absolute inset-0 h-full w-full" style="z-index:5"></svg>
            <div id="board" class="absolute inset-0" style="z-index:10"></div>
            <p id="hint" class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-sm text-slate-400">Add devices from the palette to start building.</p>
        </div>

        <aside class="space-y-3">
            <div id="inspector" class="hidden rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Configure</h3>
                <div id="inspectorBody"></div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Live Validation</h3>
                <div id="report" class="space-y-1.5 text-sm"><p class="text-slate-400">Add at least two hosts and a switch.</p></div>
            </div>
        </aside>
    </div>
</section>

<script>
(function () {
const NS = 'http://www.w3.org/2000/svg';
const board = document.getElementById('board'), wires = document.getElementById('wires'), hint = document.getElementById('hint');

function eth(n, pfx){ return Array.from({length:n}, (_,i)=>({id:pfx+(i+1), kind:'rj45', label:pfx+(i+1)})); }
const CAT = {
  pc:      {name:'PC', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>', cls:'host', color:'#334155', host:true, ports:eth(1,'Eth')},
  laptop:  {name:'Laptop', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>', cls:'host', color:'#3f3f46', host:true, ports:eth(1,'Eth')},
  server:  {name:'Server', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>', cls:'host', color:'#1e3a8a', host:true, ports:eth(1,'Eth')},
  switch:  {name:'L2 Switch (8p)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11l-3-3m3 3l-3 3M20 17H9l3-3m-3 3l3 3"/></svg>', cls:'switch', color:'#065f46', switch:true, ports:eth(8,'Fa')},
  l3switch:{name:'L3 Switch', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11l-3-3m3 3l-3 3M20 17H9l3-3m-3 3l3 3"/></svg>', cls:'switch router', color:'#064e3b', switch:true, router:true, ports:eth(4,'Gi')},
  router:  {name:'Router', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M7 8h.01M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>', cls:'router', color:'#7c2d12', router:true, ports:eth(3,'Gi')},
  firewall:{name:'Firewall', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>', cls:'router firewall', color:'#9f1239', router:true, ports:[{id:'in',kind:'rj45',label:'LAN'},{id:'out',kind:'rj45',label:'WAN'}]},
  ap:      {name:'Wireless AP', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M5.05 12.873a9 9 0 0113.9 0M2 9.5a13 13 0 0120 0M12 20h.01"/></svg>', cls:'host', color:'#155e75', host:true, ports:eth(1,'Eth')},
  cloud:   {name:'Internet', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999A6 6 0 003 15z"/></svg>', cls:'cloud', color:'#0c4a6e', ports:[{id:'wan',kind:'rj45',label:'WAN'}]}
};
const ORDER = ['pc','laptop','server','switch','l3switch','router','firewall','ap','cloud'];
let nodes = [], links = [], nid = 1, pendingPort = null;

ORDER.forEach(type => {
  const c = CAT[type];
  const b = document.createElement('button');
  b.className = 'flex w-full items-center gap-2 rounded-lg border border-slate-200 px-2 py-1.5 text-left text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/5';
  b.innerHTML = '<span class="text-base">'+c.icon+'</span>'+c.name;
  b.onclick = () => add(type, 50+Math.random()*120, 40+Math.random()*150);
  document.getElementById('palette').appendChild(b);
});

function add(type, x, y){
  hint.style.display='none';
  const c = CAT[type];
  const n = { id:nid++, type, x:x|0, y:y|0, label: c.name.split(' ')[0].toUpperCase()+(nodes.filter(z=>z.type===type).length+1), ip:'', mask:24, gw:'' };
  nodes.push(n); renderNode(n); validate();
}

function renderNode(n){
  const c = CAT[n.type];
  const el = document.createElement('div');
  el.className='absolute select-none rounded-xl border-2 border-white/70 px-2 py-1.5 text-white shadow-lg';
  el.style.cssText='left:'+n.x+'px;top:'+n.y+'px;background:'+c.color+';width:128px;cursor:grab;touch-action:none';
  el.dataset.node=n.id;
  el.innerHTML='<div class="flex items-center gap-1 text-[11px] font-bold"><span>'+c.icon+'</span><span class="label truncate"></span></div>'
    +'<div class="ipline text-[9px] opacity-80"></div>'
    +'<div class="statusline text-[9px] font-semibold"></div>'
    +'<div class="ports mt-1 flex flex-wrap gap-1"></div>';
  const pw = el.querySelector('.ports');
  c.ports.forEach(p => {
    const dot=document.createElement('button');
    dot.className='port rounded px-1 py-0.5 text-[8px] font-bold';
    dot.style.cssText='background:rgba(255,255,255,.15)';
    dot.dataset.node=n.id; dot.dataset.port=p.id;
    dot.innerHTML='<span style="display:inline-block;width:7px;height:7px;border-radius:9px;background:#3b82f6;margin-right:2px"></span>'+p.label;
    dot.onclick=(e)=>{ e.stopPropagation(); clickPort(n.id,p,dot); };
    pw.appendChild(dot);
  });
  el.addEventListener('pointerdown', e=>startDrag(e,n,el));
  el.addEventListener('click', ()=>{ if(!el.dataset.moved) select(n); });
  board.appendChild(el);
  paintNode(n);
}
function paintNode(n){
  const el=board.querySelector('[data-node="'+n.id+'"]'); if(!el)return;
  el.querySelector('.label').textContent=n.label;
  el.querySelector('.ipline').textContent = CAT[n.type].host||CAT[n.type].router ? (n.ip? n.ip+'/'+n.mask : 'no IP') : '';
}
function startDrag(e,n,el){
  if(e.target.classList.contains('port'))return;
  e.preventDefault(); el.setPointerCapture(e.pointerId); el.dataset.moved='';
  const ox=n.x,oy=n.y,sx=e.clientX,sy=e.clientY;
  const mv=ev=>{ n.x=Math.max(0,ox+(ev.clientX-sx)); n.y=Math.max(0,oy+(ev.clientY-sy)); el.style.left=n.x+'px'; el.style.top=n.y+'px'; el.dataset.moved='1'; drawWires(); };
  const up=()=>{ el.removeEventListener('pointermove',mv); el.removeEventListener('pointerup',up); setTimeout(()=>{el.dataset.moved='';},50); };
  el.addEventListener('pointermove',mv); el.addEventListener('pointerup',up);
}
function clickPort(nodeId,port,dot){
  // one cable per port (except switches allow many - but each port still single link)
  if (links.some(l => (l.a.nodeId===nodeId&&l.a.port===port.id)||(l.b.nodeId===nodeId&&l.b.port===port.id))) {
    // disconnect existing on this port
    links = links.filter(l => !((l.a.nodeId===nodeId&&l.a.port===port.id)||(l.b.nodeId===nodeId&&l.b.port===port.id)));
    drawWires(); validate(); return;
  }
  if(!pendingPort){ pendingPort={nodeId,port,dot}; dot.style.outline='2px solid #fff'; return; }
  if(pendingPort.nodeId===nodeId){ pendingPort.dot.style.outline=''; pendingPort=null; return; }
  links.push({ a:{nodeId:pendingPort.nodeId,port:pendingPort.port.id}, b:{nodeId,port:port.id}, valid:true });
  pendingPort.dot.style.outline=''; pendingPort=null; drawWires(); validate();
}
function portPos(nodeId,portId){
  const dot=board.querySelector('[data-node="'+nodeId+'"] [data-port="'+portId+'"]'); if(!dot)return null;
  const r=dot.getBoundingClientRect(), br=board.getBoundingClientRect();
  return { x:r.left+r.width/2-br.left, y:r.top+r.height/2-br.top };
}
function drawWires(){
  wires.innerHTML='';
  links.forEach((l,i)=>{
    const p1=portPos(l.a.nodeId,l.a.port), p2=portPos(l.b.nodeId,l.b.port); if(!p1||!p2)return;
    const path=document.createElementNS(NS,'path'); const mx=(p1.x+p2.x)/2;
    path.setAttribute('d','M '+p1.x+' '+p1.y+' C '+mx+' '+p1.y+', '+mx+' '+p2.y+', '+p2.x+' '+p2.y);
    path.setAttribute('fill','none'); path.setAttribute('stroke', l.valid?'#22c55e':'#ef4444'); path.setAttribute('stroke-width','3'); path.setAttribute('stroke-linecap','round');
    path.style.cursor='pointer'; path.style.pointerEvents='stroke';
    path.onclick=()=>{ links.splice(i,1); drawWires(); validate(); };
    wires.appendChild(path);
  });
}

// ---- IP helpers + connectivity ----
function ipInt(ip){ const p=(ip||'').split('.').map(Number); if(p.length!==4||p.some(n=>isNaN(n)||n<0||n>255))return null; return ((p[0]<<24)>>>0)+(p[1]<<16)+(p[2]<<8)+p[3]; }
function net(ip,cidr){ const v=ipInt(ip); if(v===null)return null; const m=cidr===0?0:(0xFFFFFFFF<<(32-cidr))>>>0; return (v&m)>>>0; }
const byId = id => nodes.find(n=>n.id===id);
function linkNeighbours(id){ const out=[]; links.forEach(l=>{ if(l.a.nodeId===id)out.push(l.b.nodeId); if(l.b.nodeId===id)out.push(l.a.nodeId); }); return out; }

// L2 reachability: flood through switches to find all hosts/routers in the same broadcast domain.
function l2Domain(startId){
  const seen=new Set([startId]), domain=new Set(), q=[startId];
  while(q.length){ const id=q.shift(); linkNeighbours(id).forEach(nb=>{ if(seen.has(nb))return; seen.add(nb); const c=CAT[byId(nb).type]; if(c.switch){ q.push(nb); } else { domain.add(nb); } }); }
  return domain;
}

function validate(){
  let report=[], issues=0, hosts=0, okHosts=0;
  // duplicate IP detection
  const ipMap={}; nodes.forEach(n=>{ if(n.ip && ipInt(n.ip)!==null){ (ipMap[n.ip]=ipMap[n.ip]||[]).push(n.label); } });

  nodes.forEach(n=>{
    const c=CAT[n.type]; const warn=[];
    if(c.host || c.router){
      if(!n.ip) warn.push('No IP address');
      else if(ipInt(n.ip)===null) warn.push('Invalid IP address');
      else if(ipMap[n.ip] && ipMap[n.ip].length>1) warn.push('Duplicate IP ('+n.ip+')');
      if(c.host){
        if(!linkNeighbours(n.id).length) warn.push('Not cabled to any device');
        if(n.gw){ if(ipInt(n.gw)===null) warn.push('Invalid gateway'); else if(n.ip && net(n.ip,n.mask)!==net(n.gw,n.mask)) warn.push('Gateway not in this subnet'); }
        else warn.push('No default gateway set');
        // same-subnet reachability with a neighbour host in L2 domain
        if(n.ip && ipInt(n.ip)!==null){
          const dom=l2Domain(n.id); let reachable=false, gwFound=false;
          dom.forEach(id=>{ const o=byId(id); if(!o.ip||ipInt(o.ip)===null)return; if(net(o.ip,n.mask)===net(n.ip,n.mask)){ if(o.id!==n.id) reachable=true; if(CAT[o.type].router && n.gw && o.ip===n.gw) gwFound=true; } });
          if(n.gw && net(n.ip,n.mask)===net(n.gw,n.mask) && !gwFound) warn.push('Gateway '+n.gw+' not reachable on this LAN');
        }
      }
    }
    if(c.cls==='cloud'){ if(!linkNeighbours(n.id).length) warn.push('Internet not connected to a router/firewall'); }
    // paint
    const el=board.querySelector('[data-node="'+n.id+'"]');
    if(el){ const sl=el.querySelector('.statusline'); const ok=warn.length===0;
      el.style.boxShadow = ok ? '0 0 0 2px #22c55e' : '0 0 0 2px #ef4444';
      sl.textContent = ok ? '● ok' : '▲ '+warn[0]; sl.style.color = ok?'#86efac':'#fca5a5';
      el.querySelector('.ipline').textContent = (c.host||c.router) ? (n.ip? n.ip+'/'+n.mask:'no IP') : '';
    }
    if((c.host||c.router) ){ hosts += c.host?1:0; if(c.host && warn.length===0) okHosts++; }
    if(warn.length){ report.push({name:n.label+' ('+c.name+')', warn}); issues+=warn.length; }
  });

  // duplicate-IP summary
  Object.keys(ipMap).forEach(ip=>{ if(ipMap[ip].length>1) report.unshift({name:'IP conflict: '+ip, warn:[ipMap[ip].join(', ')+' share the same address']}); });

  const rep=document.getElementById('report');
  if(!nodes.length){ rep.innerHTML='<p class="text-slate-400">Add at least two hosts and a switch.</p>'; }
  else if(!report.length && hosts){ rep.innerHTML='<div class="rounded-lg bg-emerald-50 px-3 py-2 font-bold text-emerald-700 dark:bg-emerald-500/10">✓ All '+hosts+' host(s) configured and reachable.</div>'; }
  else { rep.innerHTML = report.map(r=>'<div class="rounded-lg bg-red-50 px-3 py-2 dark:bg-red-500/10"><p class="font-bold text-red-700 dark:text-red-300">▲ '+r.name+'</p><ul class="ml-3 list-disc text-xs text-red-600 dark:text-red-300">'+r.warn.map(w=>'<li>'+w+'</li>').join('')+'</ul></div>').join('') || '<p class="text-slate-400">Configure your devices.</p>'; }

  const badge=document.getElementById('sysStatus'), txt=document.getElementById('sysStatusText');
  if(!report.length && hosts){ badge.className='rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-5 py-3 text-center dark:border-emerald-500/40 dark:bg-emerald-500/10'; txt.className='text-lg font-black text-emerald-600'; txt.textContent='✓ Healthy'; }
  else { badge.className='rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3 text-center dark:border-red-500/40 dark:bg-red-500/10'; txt.className='text-lg font-black text-red-600'; txt.textContent = nodes.length? issues+' issue(s)':'Empty'; }
  drawWires();
}

function select(n){
  const c=CAT[n.type];
  document.getElementById('inspector').classList.remove('hidden');
  let html='<p class="font-bold text-slate-900 dark:text-white">'+c.icon+' '+c.name+'</p>'
    +'<label class="mt-2 block text-xs font-bold text-slate-500">Name</label><input id="f_label" value="'+n.label+'" class="w-full rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">';
  if(c.host||c.router){
    html+='<label class="mt-2 block text-xs font-bold text-slate-500">IP address</label><input id="f_ip" value="'+n.ip+'" placeholder="192.168.1.10" class="w-full rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">'
      +'<label class="mt-2 block text-xs font-bold text-slate-500">Subnet (CIDR /)</label><input id="f_mask" type="number" min="1" max="32" value="'+n.mask+'" class="w-full rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">';
  }
  if(c.host){ html+='<label class="mt-2 block text-xs font-bold text-slate-500">Default gateway</label><input id="f_gw" value="'+n.gw+'" placeholder="192.168.1.1" class="w-full rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">'; }
  html+='<div class="mt-2 flex gap-2"><button onclick="NET.apply('+n.id+')" class="flex-1 rounded-lg bg-brand-600 py-1.5 text-sm font-bold text-white hover:bg-brand-700">Apply</button>'
    +'<button onclick="NET.del('+n.id+')" class="rounded-lg border border-red-300 px-3 py-1.5 text-sm font-bold text-red-600 dark:border-red-500/40">Delete</button></div>';
  document.getElementById('inspectorBody').innerHTML=html;
}

window.NET = {
  apply(id){ const n=byId(id); if(!n)return; const g=x=>document.getElementById(x);
    n.label=g('f_label').value||n.label; if(g('f_ip'))n.ip=g('f_ip').value.trim(); if(g('f_mask'))n.mask=Math.min(32,Math.max(1,parseInt(g('f_mask').value)||24)); if(g('f_gw'))n.gw=g('f_gw').value.trim();
    paintNode(n); validate(); },
  del(id){ nodes=nodes.filter(n=>n.id!==id); links=links.filter(l=>l.a.nodeId!==id&&l.b.nodeId!==id); const el=board.querySelector('[data-node="'+id+'"]'); if(el)el.remove(); document.getElementById('inspector').classList.add('hidden'); drawWires(); validate(); },
  clear(){ nodes=[];links=[];board.innerHTML='';wires.innerHTML=''; document.getElementById('inspector').classList.add('hidden'); hint.style.display=''; validate(); }
};

// Starter scene: 2 PCs + a switch (unconfigured so warnings show how to fix).
add('pc',40,60); add('pc',40,250); add('switch',300,150);
window.addEventListener('resize', drawWires);
})();
</script>
