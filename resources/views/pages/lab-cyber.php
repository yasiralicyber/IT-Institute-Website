<section class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="<?= url('/labs') ?>" class="text-sm font-semibold text-brand-600 hover:underline">← All labs</a>
            <h1 class="flex items-center gap-2 text-2xl font-black text-slate-900 dark:text-white"><?= icon('shield','h-7 w-7 text-brand-600') ?> Security Architecture Builder <span class="rounded-full bg-brand-600 px-2 py-0.5 text-xs font-bold text-white">ADVANCED</span></h1>
            <p class="text-sm text-slate-500">Design a hardened network. Place defenses and assets, wire the traffic path, and the lab audits your security posture: <span class="font-bold text-emerald-600">green = secure</span>, <span class="font-bold text-red-600">red = vulnerability</span>.</p>
        </div>
        <div id="sysStatus" class="rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3 text-center dark:border-red-500/40 dark:bg-red-500/10">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Security Score</p>
            <p id="sysStatusText" class="text-lg font-black text-red-600">—</p>
        </div>
    </div>

    <div class="grid gap-3 lg:grid-cols-[220px_1fr_300px]">
        <aside class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Defenses &amp; Assets</h3>
            <div id="palette" class="space-y-1.5"></div>
            <p class="mt-3 text-[11px] text-slate-400">Click a port, then another, to wire the traffic path (Internet → defenses → assets).</p>
            <button onclick="SEC.clear()" class="mt-3 w-full rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Clear board</button>
        </aside>

        <div class="relative overflow-hidden rounded-2xl border border-slate-300 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:22px_22px] dark:border-white/10 dark:bg-slate-950" style="min-height:560px">
            <svg id="wires" class="pointer-events-none absolute inset-0 h-full w-full" style="z-index:5"></svg>
            <div id="board" class="absolute inset-0" style="z-index:10"></div>
            <p id="hint" class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-sm text-slate-400">Add the Internet, a Firewall and a server to begin.</p>
        </div>

        <aside class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Security Audit</h3>
            <div id="report" class="space-y-1.5 text-sm"><p class="text-slate-400">Build a path from Internet to your assets.</p></div>
        </aside>
    </div>
</section>

<script>
(function () {
const NS='http://www.w3.org/2000/svg';
const board=document.getElementById('board'), wires=document.getElementById('wires'), hint=document.getElementById('hint');
const P=(...ids)=>ids.map(id=>({id,kind:'net',label:id}));
const CAT={
  internet: {name:'Internet', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999A6 6 0 003 15z"/></svg>', cls:'internet', color:'#0c4a6e', ports:P('out')},
  firewall: {name:'Firewall', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>', cls:'firewall', color:'#9f1239', ports:P('wan','lan','dmz')},
  ids:      {name:'IDS / IPS', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M5.05 12.873a9 9 0 0113.9 0M2 9.5a13 13 0 0120 0M12 20h.01"/></svg>', cls:'ids', color:'#7c2d12', ports:P('a','b')},
  waf:      {name:'WAF', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>', cls:'waf', color:'#9a3412', ports:P('a','b')},
  vpn:      {name:'VPN Gateway', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>', cls:'vpn', color:'#3730a3', ports:P('a','b')},
  dmz:      {name:'DMZ Switch', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11l-3-3m3 3l-3 3M20 17H9l3-3m-3 3l3 3"/></svg>', cls:'dmz', color:'#854d0e', switch:true, ports:P('p1','p2','p3','p4')},
  lan:      {name:'LAN Switch', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11l-3-3m3 3l-3 3M20 17H9l3-3m-3 3l3 3"/></svg>', cls:'lan', color:'#065f46', switch:true, ports:P('p1','p2','p3','p4')},
  web:      {name:'Web Server (public)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>', cls:'asset', zone:'public', color:'#1e3a8a', ports:P('nic')},
  db:       {name:'Database (private)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3-3.582 3-8 3-8-1.343-8-3z M4 7v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7 M4 12c0 1.657 3.582 3 8 3s8-1.343 8-3"/></svg>', cls:'asset', zone:'private', color:'#4c1d95', ports:P('nic')},
  pc:       {name:'Workstation', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>', cls:'asset', zone:'private', color:'#334155', ports:P('nic')},
  edr:      {name:'EDR / Antivirus', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>', cls:'edr', color:'#166534', ports:P('host')},
  siem:     {name:'SIEM (logging)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', cls:'siem', color:'#155e75', ports:P('feed')}
};
const ORDER=['internet','firewall','ids','waf','vpn','dmz','lan','web','db','pc','edr','siem'];
let nodes=[],links=[],nid=1,pendingPort=null;

ORDER.forEach(type=>{ const c=CAT[type]; const b=document.createElement('button');
  b.className='flex w-full items-center gap-2 rounded-lg border border-slate-200 px-2 py-1.5 text-left text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/5';
  b.innerHTML='<span class="text-base">'+c.icon+'</span>'+c.name; b.onclick=()=>add(type,50+Math.random()*120,40+Math.random()*150);
  document.getElementById('palette').appendChild(b); });

function add(type,x,y){ hint.style.display='none'; const c=CAT[type];
  nodes.push({id:nid++,type,x:x|0,y:y|0,label:c.name}); renderNode(nodes[nodes.length-1]); validate(); }
function renderNode(n){ const c=CAT[n.type]; const el=document.createElement('div');
  el.className='absolute select-none rounded-xl border-2 border-white/70 px-2 py-1.5 text-white shadow-lg';
  el.style.cssText='left:'+n.x+'px;top:'+n.y+'px;background:'+c.color+';width:130px;cursor:grab;touch-action:none'; el.dataset.node=n.id;
  el.innerHTML='<div class="flex items-center gap-1 text-[11px] font-bold"><span>'+c.icon+'</span><span class="truncate">'+c.name+'</span></div><div class="statusline text-[9px] font-semibold opacity-80">·</div><div class="ports mt-1 flex flex-wrap gap-1"></div>';
  const pw=el.querySelector('.ports');
  c.ports.forEach(p=>{ const dot=document.createElement('button'); dot.className='port rounded px-1 py-0.5 text-[8px] font-bold'; dot.style.cssText='background:rgba(255,255,255,.15)'; dot.dataset.node=n.id; dot.dataset.port=p.id;
    dot.innerHTML='<span style="display:inline-block;width:7px;height:7px;border-radius:9px;background:#38bdf8;margin-right:2px"></span>'+p.label;
    dot.onclick=e=>{ e.stopPropagation(); clickPort(n.id,p,dot); }; pw.appendChild(dot); });
  el.addEventListener('pointerdown',e=>startDrag(e,n,el)); board.appendChild(el);
}
function startDrag(e,n,el){ if(e.target.classList.contains('port'))return; e.preventDefault(); el.setPointerCapture(e.pointerId);
  const ox=n.x,oy=n.y,sx=e.clientX,sy=e.clientY;
  const mv=ev=>{ n.x=Math.max(0,ox+(ev.clientX-sx)); n.y=Math.max(0,oy+(ev.clientY-sy)); el.style.left=n.x+'px'; el.style.top=n.y+'px'; drawWires(); };
  const up=()=>{ el.removeEventListener('pointermove',mv); el.removeEventListener('pointerup',up); }; el.addEventListener('pointermove',mv); el.addEventListener('pointerup',up); }
function clickPort(nodeId,port,dot){
  if(links.some(l=>(l.a.nodeId===nodeId&&l.a.port===port.id)||(l.b.nodeId===nodeId&&l.b.port===port.id))){ links=links.filter(l=>!((l.a.nodeId===nodeId&&l.a.port===port.id)||(l.b.nodeId===nodeId&&l.b.port===port.id))); drawWires(); validate(); return; }
  if(!pendingPort){ pendingPort={nodeId,port,dot}; dot.style.outline='2px solid #fff'; return; }
  if(pendingPort.nodeId===nodeId){ pendingPort.dot.style.outline=''; pendingPort=null; return; }
  links.push({a:{nodeId:pendingPort.nodeId,port:pendingPort.port.id},b:{nodeId,port:port.id}}); pendingPort.dot.style.outline=''; pendingPort=null; drawWires(); validate();
}
function portPos(nodeId,portId){ const dot=board.querySelector('[data-node="'+nodeId+'"] [data-port="'+portId+'"]'); if(!dot)return null; const r=dot.getBoundingClientRect(),br=board.getBoundingClientRect(); return {x:r.left+r.width/2-br.left,y:r.top+r.height/2-br.top}; }
function drawWires(){ wires.innerHTML=''; links.forEach((l,i)=>{ const p1=portPos(l.a.nodeId,l.a.port),p2=portPos(l.b.nodeId,l.b.port); if(!p1||!p2)return; const path=document.createElementNS(NS,'path'); const mx=(p1.x+p2.x)/2; path.setAttribute('d','M '+p1.x+' '+p1.y+' C '+mx+' '+p1.y+', '+mx+' '+p2.y+', '+p2.x+' '+p2.y); path.setAttribute('fill','none'); path.setAttribute('stroke','#38bdf8'); path.setAttribute('stroke-width','3'); path.setAttribute('stroke-linecap','round'); path.style.cursor='pointer'; path.style.pointerEvents='stroke'; path.onclick=()=>{ links.splice(i,1); drawWires(); validate(); }; wires.appendChild(path); }); }

const byId=id=>nodes.find(n=>n.id===id);
function neighbours(id){ const o=[]; links.forEach(l=>{ if(l.a.nodeId===id)o.push(l.b.nodeId); if(l.b.nodeId===id)o.push(l.a.nodeId); }); return o; }
// path from->to avoiding any node whose type is in blockers
function reaches(fromId,toId,blockers){ const seen=new Set([fromId]),q=[fromId]; while(q.length){ const id=q.shift(); for(const nb of neighbours(id)){ if(seen.has(nb))continue; if(nb===toId)return true; if(blockers.includes(byId(nb).type))continue; seen.add(nb); q.push(nb);} } return false; }
// is there a path from->to passing through a node of mustType
function pathPasses(fromId,toId,mustType){ const seen=new Set(),q=[[fromId,false]]; seen.add(fromId+':0'); while(q.length){ const [id,passed]=q.shift(); for(const nb of neighbours(id)){ const np=passed||byId(nb).type===mustType; if(nb===toId){ if(np)return true; continue; } const key=nb+':'+(np?1:0); if(seen.has(key))continue; seen.add(key); q.push([nb,np]); } } return false; }

function validate(){
  let score=0, max=0; const wins=[], fails=[]; let info='';
  const find=t=>nodes.find(n=>n.type===t);
  const internet=find('internet'), fw=find('firewall');
  const assets=nodes.filter(n=>CAT[n.type].cls==='asset');
  const pub=nodes.filter(n=>n.type==='web'), priv=nodes.filter(n=>n.type==='db');
  function check(cond, ok, fail){ max++; if(cond){ score++; wins.push(ok);} else fails.push(fail); }

  if(internet && assets.length){
    check(!!fw, 'Perimeter firewall present', 'No perimeter firewall - add a Firewall between the Internet and your assets');
    if(fw){
      assets.forEach(a=>{ max++; if(reaches(internet.id,a.id,['firewall'])){ fails.push(a.label+' is reachable from the Internet WITHOUT the firewall (exposed!)'); } else score++; });
      priv.forEach(db=>{ max++; if(!reaches(internet.id,db.id,['lan'])){ score++; } else fails.push(db.label+' (database) is exposed toward the Internet - place it behind the LAN switch, never the DMZ'); });
      pub.forEach(w=>{ max++; if(pathPasses(fw.id,w.id,'dmz')){ score++; } else fails.push(w.label+' should be isolated in a DMZ (via a DMZ Switch), not on the internal LAN'); });
    }
  } else { info='Add the Internet, a Firewall and at least one server to be audited.'; }

  check(!!find('ids'), 'IDS/IPS is monitoring traffic', 'No IDS/IPS - add intrusion detection to spot attacks');
  if(pub.length) check(!!find('waf'), 'WAF protects the web server', 'Public web server has no WAF - add a Web Application Firewall');
  check(!!find('vpn'), 'VPN gateway for secure remote access', 'No VPN - remote access should be over a VPN, not open ports');
  if(find('pc')) check(!!find('edr'), 'Endpoints protected by EDR/antivirus', 'Workstations have no EDR/antivirus');
  check(!!find('siem'), 'SIEM is collecting logs', 'No SIEM - add centralised logging to detect & investigate incidents');

  nodes.forEach(n=>{ const el=board.querySelector('[data-node="'+n.id+'"]'); if(!el)return; const sl=el.querySelector('.statusline'); let bad=false, txt='·';
    if(n.type==='db' && internet && reaches(internet.id,n.id,['lan'])){ bad=true; txt='▲ exposed to internet'; }
    else if(n.type==='web' && fw && neighbours(n.id).length && !pathPasses(fw.id,n.id,'dmz')){ bad=true; txt='▲ not in DMZ'; }
    else if(CAT[n.type].cls==='asset' && internet && fw && reaches(internet.id,n.id,['firewall'])){ bad=true; txt='▲ bypasses firewall'; }
    else if(neighbours(n.id).length){ txt='● ok'; }
    el.style.boxShadow = bad?'0 0 0 2px #ef4444':(neighbours(n.id).length?'0 0 0 2px #22c55e':''); sl.textContent=txt; sl.style.color=bad?'#fca5a5':'#86efac';
  });

  const pct = max? Math.round(score/max*100):0;
  const rep=document.getElementById('report');
  const tone = pct>=80?'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10':pct>=50?'bg-amber-50 text-amber-700 dark:bg-amber-500/10':'bg-red-50 text-red-700 dark:bg-red-500/10';
  let html='';
  if(internet && assets.length) html+='<div class="mb-2 rounded-lg '+tone+' px-3 py-2 font-bold">Posture: '+pct+'% ('+score+'/'+max+' controls)</div>';
  html+=fails.map(f=>'<div class="rounded-lg bg-red-50 px-3 py-1.5 text-red-700 dark:bg-red-500/10 dark:text-red-300"><b>▲</b> '+f+'</div>').join('');
  html+=wins.map(w=>'<div class="rounded-lg bg-emerald-50 px-3 py-1.5 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><b>✓</b> '+w+'</div>').join('');
  if(info) html='<p class="text-slate-400">'+info+'</p>'+html;
  rep.innerHTML = html || '<p class="text-slate-400">Build a path from Internet to your assets.</p>';

  const badge=document.getElementById('sysStatus'), txt2=document.getElementById('sysStatusText');
  if(internet && assets.length){
    const full = pct>=80?'rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-5 py-3 text-center dark:border-emerald-500/40 dark:bg-emerald-500/10'
      : pct>=50?'rounded-2xl border-2 border-amber-300 bg-amber-50 px-5 py-3 text-center dark:border-amber-500/40 dark:bg-amber-500/10'
      : 'rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3 text-center dark:border-red-500/40 dark:bg-red-500/10';
    badge.className=full; txt2.className='text-lg font-black '+(pct>=80?'text-emerald-600':pct>=50?'text-amber-600':'text-red-600'); txt2.textContent=pct+'%';
  } else { badge.className='rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3 text-center dark:border-red-500/40 dark:bg-red-500/10'; txt2.className='text-lg font-black text-red-600'; txt2.textContent='—'; }
  drawWires();
}

window.SEC={ clear(){ nodes=[];links=[];board.innerHTML='';wires.innerHTML=''; hint.style.display=''; validate(); } };
add('internet',40,40); add('firewall',260,60); add('web',480,40); add('db',480,230);
window.addEventListener('resize', drawWires);
})();
</script>
