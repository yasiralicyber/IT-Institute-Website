<section class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="<?= url('/labs') ?>" class="text-sm font-semibold text-brand-600 hover:underline">← All labs</a>
            <h1 class="flex items-center gap-2 text-2xl font-black text-slate-900 dark:text-white"><?= icon('camera','h-7 w-7 text-brand-600') ?> CCTV System Builder <span class="rounded-full bg-brand-600 px-2 py-0.5 text-xs font-bold text-white">ADVANCED</span></h1>
            <p class="text-sm text-slate-500">Drag real components onto the rack, then wire every connector - BNC coax, Cat6/RJ45, DC power, HDMI, SATA. The system validates each link: <span class="font-bold text-emerald-600">green = correct</span>, <span class="font-bold text-red-600">red = wrong / missing</span>.</p>
        </div>
        <div id="sysStatus" class="rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3 text-center dark:border-red-500/40 dark:bg-red-500/10">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">System Status</p>
            <p id="sysStatusText" class="text-lg font-black text-red-600">Not wired</p>
        </div>
    </div>

    <div class="grid gap-3 lg:grid-cols-[230px_1fr_280px]">
        <!-- PALETTE -->
        <aside class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Components</h3>
            <div id="palette" class="space-y-1.5"></div>
            <div class="mt-3 border-t border-slate-100 pt-3 dark:border-white/10">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Cable</h3>
                <div id="cableModes" class="grid grid-cols-2 gap-1.5"></div>
                <p class="mt-2 text-[11px] text-slate-400">Pick a cable, then click a port and a matching port to connect. Wrong matches flag red.</p>
            </div>
            <button onclick="LAB.clear()" class="mt-3 w-full rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Clear board</button>
        </aside>

        <!-- BOARD -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-300 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:22px_22px] dark:border-white/10 dark:bg-slate-950" style="min-height:560px">
            <svg id="wires" class="pointer-events-none absolute inset-0 h-full w-full" style="z-index:5"></svg>
            <div id="board" class="absolute inset-0" style="z-index:10"></div>
            <p id="hint" class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-sm text-slate-400">Drag components here, or tap one in the palette to add it.</p>
        </div>

        <!-- INSPECTOR / VALIDATION -->
        <aside class="space-y-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Live Validation</h3>
                <div id="report" class="space-y-1.5 text-sm"><p class="text-slate-400">Add a camera and a recorder to begin.</p></div>
            </div>
            <div id="inspector" class="hidden rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Selected</h3>
                <div id="inspectorBody"></div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Storage Estimate</h3>
                <select id="bitrate" onchange="LAB.validate()" class="w-full rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <option value="2">1080p (~2 Mbps)</option><option value="4" selected>4MP (~4 Mbps)</option><option value="8">4K (~8 Mbps)</option>
                </select>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <input id="hrs" type="number" value="24" oninput="LAB.validate()" class="rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white" placeholder="hrs/day">
                    <input id="days" type="number" value="30" oninput="LAB.validate()" class="rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white" placeholder="days">
                </div>
                <p id="storeOut" class="mt-2 text-sm font-bold text-brand-700 dark:text-brand-300">Add cameras to estimate.</p>
            </div>
        </aside>
    </div>

    <details class="mx-auto mt-4 max-w-3xl rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-white/10 dark:bg-slate-900">
        <summary class="cursor-pointer font-bold text-slate-700 dark:text-slate-200">How real CCTV wiring works (read me)</summary>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-slate-500">
            <li><b>Analog / HD-TVI cameras</b> send video over <b>coax with BNC</b> connectors into a <b>DVR</b>, and need a separate <b>12V DC</b> power lead.</li>
            <li><b>IP cameras</b> use one <b>Cat6 / RJ45</b> cable for both data and power (<b>PoE</b>) into a <b>PoE switch</b> or directly into an <b>NVR</b> with PoE ports.</li>
            <li>Every recorder needs a <b>hard drive (SATA)</b>, <b>power</b>, and an <b>HDMI</b> link to a <b>monitor</b>.</li>
            <li>Mismatched connectors (e.g. BNC into an RJ45 port) are physically impossible - the lab flags them red.</li>
        </ul>
    </details>
</section>

<script>
(function () {
const NS = 'http://www.w3.org/2000/svg';
const board = document.getElementById('board'), wires = document.getElementById('wires'), hint = document.getElementById('hint');

// ---- Component catalogue. ports: {id,kind,dir,label}. kind drives cable compatibility. ----
const KINDS = { bnc:{c:'#f59e0b',n:'BNC coax'}, rj45:{c:'#3b82f6',n:'Cat6 RJ45'}, dc:{c:'#ef4444',n:'DC power'}, hdmi:{c:'#a855f7',n:'HDMI'}, sata:{c:'#10b981',n:'SATA'} };
const CABLES = ['bnc','rj45','dc','hdmi','sata'];
const CAT = {
  cam_bullet:  {name:'Analog Bullet Cam', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', cls:'analog camera', color:'#1e293b', ports:[{id:'v',kind:'bnc',dir:'out',label:'BNC'},{id:'p',kind:'dc',dir:'in',label:'12V'}]},
  cam_dome:    {name:'Analog Dome Cam',   icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', cls:'analog camera', color:'#1e293b', ports:[{id:'v',kind:'bnc',dir:'out',label:'BNC'},{id:'p',kind:'dc',dir:'in',label:'12V'}]},
  cam_tvi:     {name:'HD-TVI Bullet Cam', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', cls:'analog camera', color:'#0f3d5c', ports:[{id:'v',kind:'bnc',dir:'out',label:'BNC'},{id:'p',kind:'dc',dir:'in',label:'12V'}]},
  cam_ip:      {name:'IP Bullet Cam (PoE)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M5.05 12.873a9 9 0 0113.9 0M2 9.5a13 13 0 0120 0M12 20h.01"/></svg>', cls:'ip camera', color:'#134e4a', ports:[{id:'n',kind:'rj45',dir:'out',label:'LAN/PoE'}]},
  cam_ipdome:  {name:'IP Dome Cam (PoE)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', cls:'ip camera', color:'#134e4a', ports:[{id:'n',kind:'rj45',dir:'out',label:'LAN/PoE'}]},
  cam_ptz:     {name:'PTZ IP Cam (PoE+)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', cls:'ip camera', color:'#3b0764', ports:[{id:'n',kind:'rj45',dir:'out',label:'LAN/PoE'}]},
  dvr:         {name:'DVR (analog/TVI)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z M8 9h.01M8 15h8"/></svg>', cls:'recorder dvr', color:'#334155', ports:[{id:'i1',kind:'bnc',dir:'in',label:'IN1'},{id:'i2',kind:'bnc',dir:'in',label:'IN2'},{id:'i3',kind:'bnc',dir:'in',label:'IN3'},{id:'i4',kind:'bnc',dir:'in',label:'IN4'},{id:'pw',kind:'dc',dir:'in',label:'PWR'},{id:'hd',kind:'sata',dir:'in',label:'HDD'},{id:'out',kind:'hdmi',dir:'out',label:'HDMI'}]},
  nvr:         {name:'NVR (IP, PoE)', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>', cls:'recorder nvr', color:'#1e3a8a', ports:[{id:'i1',kind:'rj45',dir:'in',label:'PoE1'},{id:'i2',kind:'rj45',dir:'in',label:'PoE2'},{id:'i3',kind:'rj45',dir:'in',label:'PoE3'},{id:'i4',kind:'rj45',dir:'in',label:'PoE4'},{id:'pw',kind:'dc',dir:'in',label:'PWR'},{id:'hd',kind:'sata',dir:'in',label:'HDD'},{id:'out',kind:'hdmi',dir:'out',label:'HDMI'}]},
  poe:         {name:'PoE Switch', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>', cls:'switch', color:'#155e75', ports:[{id:'p1',kind:'rj45',dir:'in',label:'P1'},{id:'p2',kind:'rj45',dir:'in',label:'P2'},{id:'p3',kind:'rj45',dir:'in',label:'P3'},{id:'p4',kind:'rj45',dir:'in',label:'P4'},{id:'up',kind:'rj45',dir:'out',label:'Uplink'}]},
  power:       {name:'12V Power Supply', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>', cls:'power', color:'#7f1d1d', ports:[{id:'o1',kind:'dc',dir:'out',label:'DC1'},{id:'o2',kind:'dc',dir:'out',label:'DC2'},{id:'o3',kind:'dc',dir:'out',label:'DC3'},{id:'o4',kind:'dc',dir:'out',label:'DC4'}]},
  hdd:         {name:'Hard Drive', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7z M8 17h.01"/></svg>', cls:'storage', color:'#064e3b', ports:[{id:'s',kind:'sata',dir:'out',label:'SATA'}]},
  monitor:     {name:'Monitor', icon:'<svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v2m6-2v2M4 5h16a1 1 0 011 1v9a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>', cls:'monitor', color:'#0c4a6e', ports:[{id:'in',kind:'hdmi',dir:'in',label:'HDMI'}]}
};
const PALETTE_ORDER = ['cam_bullet','cam_dome','cam_tvi','cam_ip','cam_ipdome','cam_ptz','dvr','nvr','poe','power','hdd','monitor'];

let nodes = [], links = [], nid = 1, cable = 'bnc', pendingPort = null, selected = null;

// ---- Build palette ----
PALETTE_ORDER.forEach(type => {
  const c = CAT[type];
  const b = document.createElement('button');
  b.className = 'flex w-full items-center gap-2 rounded-lg border border-slate-200 px-2 py-1.5 text-left text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/5';
  b.innerHTML = '<span class="text-base">' + c.icon + '</span>' + c.name;
  b.onclick = () => addNode(type, 60 + Math.random()*120, 40 + Math.random()*120);
  document.getElementById('palette').appendChild(b);
});
CABLES.forEach(k => {
  const b = document.createElement('button');
  b.dataset.k = k;
  b.className = 'cablebtn rounded-lg border px-2 py-1.5 text-[11px] font-bold';
  b.style.borderColor = KINDS[k].c;
  b.innerHTML = '<span style="display:inline-block;width:8px;height:8px;border-radius:9px;background:'+KINDS[k].c+';margin-right:4px"></span>' + KINDS[k].n;
  b.onclick = () => { cable = k; document.querySelectorAll('.cablebtn').forEach(x=>x.classList.remove('ring-2')); b.classList.add('ring-2'); b.style.setProperty('--tw-ring-color', KINDS[k].c); };
  document.getElementById('cableModes').appendChild(b);
});
document.querySelector('.cablebtn').classList.add('ring-2');

function addNode(type, x, y) {
  hint.style.display = 'none';
  const node = { id: nid++, type, x: x|0, y: y|0 };
  nodes.push(node);
  renderNode(node);
  validate();
}

function renderNode(node) {
  const c = CAT[node.type];
  const el = document.createElement('div');
  el.className = 'absolute select-none rounded-xl border-2 border-white/70 px-2 py-1.5 text-white shadow-lg';
  el.style.cssText = 'left:'+node.x+'px;top:'+node.y+'px;background:'+c.color+';width:120px;cursor:grab;touch-action:none';
  el.dataset.node = node.id;
  el.innerHTML = '<div class="flex items-center gap-1 text-[11px] font-bold"><span>'+c.icon+'</span><span class="truncate">'+c.name+'</span></div>'
    + '<div class="statusline mt-0.5 text-[9px] font-semibold opacity-80">·</div>'
    + '<div class="ports mt-1 flex flex-wrap gap-1"></div>';
  const pw = el.querySelector('.ports');
  c.ports.forEach(p => {
    const dot = document.createElement('button');
    dot.className = 'port flex items-center gap-0.5 rounded px-1 py-0.5 text-[8px] font-bold';
    dot.style.cssText = 'background:rgba(255,255,255,.15)';
    dot.dataset.node = node.id; dot.dataset.port = p.id; dot.dataset.kind = p.kind;
    dot.innerHTML = '<span style="display:inline-block;width:7px;height:7px;border-radius:9px;background:'+KINDS[p.kind].c+'"></span>'+p.label;
    dot.onclick = (e) => { e.stopPropagation(); clickPort(node.id, p, dot); };
    pw.appendChild(dot);
  });
  el.addEventListener('pointerdown', e => startDrag(e, node, el));
  el.addEventListener('click', e => { if (!el.dataset.moved) select(node); });
  board.appendChild(el);
}

function startDrag(e, node, el) {
  if (e.target.classList.contains('port')) return;
  e.preventDefault(); el.setPointerCapture(e.pointerId); el.dataset.moved = '';
  const r = board.getBoundingClientRect(), ox = node.x, oy = node.y, sx = e.clientX, sy = e.clientY;
  const mv = ev => { node.x = Math.max(0, ox + (ev.clientX - sx)); node.y = Math.max(0, oy + (ev.clientY - sy)); el.style.left = node.x+'px'; el.style.top = node.y+'px'; el.dataset.moved='1'; drawWires(); };
  const up = ev => { el.releasePointerCapture(e.pointerId); el.removeEventListener('pointermove', mv); el.removeEventListener('pointerup', up); setTimeout(()=>{el.dataset.moved='';}, 50); };
  el.addEventListener('pointermove', mv); el.addEventListener('pointerup', up);
}

function clickPort(nodeId, port, dot) {
  if (!pendingPort) { pendingPort = { nodeId, port, dot }; dot.style.outline = '2px solid #fff'; return; }
  if (pendingPort.nodeId === nodeId && pendingPort.port.id === port.id) { pendingPort.dot.style.outline=''; pendingPort = null; return; }
  // create link
  const a = pendingPort, b = { nodeId, port };
  const okKind = a.port.kind === b.port.kind && a.port.kind === cable;
  links.push({ a: {nodeId:a.nodeId, port:a.port.id, kind:a.port.kind}, b:{nodeId:b.nodeId, port:b.port.id, kind:b.port.kind}, cable, valid: okKind });
  a.dot.style.outline = ''; pendingPort = null;
  drawWires(); validate();
}

function portPos(nodeId, portId) {
  const dot = board.querySelector('[data-node="'+nodeId+'"] [data-port="'+portId+'"]');
  if (!dot) return null;
  const r = dot.getBoundingClientRect(), br = board.getBoundingClientRect();
  return { x: r.left + r.width/2 - br.left, y: r.top + r.height/2 - br.top };
}

function drawWires() {
  wires.innerHTML = '';
  links.forEach((l, i) => {
    const p1 = portPos(l.a.nodeId, l.a.port), p2 = portPos(l.b.nodeId, l.b.port);
    if (!p1 || !p2) return;
    const col = l.valid ? KINDS[l.cable].c : '#ef4444';
    const path = document.createElementNS(NS, 'path');
    const mx = (p1.x + p2.x) / 2;
    path.setAttribute('d', 'M '+p1.x+' '+p1.y+' C '+mx+' '+p1.y+', '+mx+' '+p2.y+', '+p2.x+' '+p2.y);
    path.setAttribute('fill', 'none'); path.setAttribute('stroke', col); path.setAttribute('stroke-width', l.valid?'3':'3');
    path.setAttribute('stroke-linecap','round'); if (!l.valid) path.setAttribute('stroke-dasharray','7 5');
    path.style.cursor='pointer'; path.style.pointerEvents='stroke';
    path.onclick = () => { links.splice(i,1); drawWires(); validate(); };
    wires.appendChild(path);
  });
}

// ---- The validation engine ----
function neighbours(nodeId, portId) {
  // returns [{node, port, kind, valid}]
  const out = [];
  links.forEach(l => {
    if (l.a.nodeId === nodeId && (portId===undefined || l.a.port===portId)) out.push({ node: l.b.nodeId, kind: l.b.kind, valid: l.valid });
    if (l.b.nodeId === nodeId && (portId===undefined || l.b.port===portId)) out.push({ node: l.a.nodeId, kind: l.a.kind, valid: l.valid });
  });
  return out;
}
const nodeById = id => nodes.find(n => n.id === id);
const isType = (id, ...t) => { const n = nodeById(id); return n && t.includes(n.type); };

function validate() {
  let report = [], onlineCams = 0, totalCams = 0, sysOk = true, issues = 0;
  const recorders = nodes.filter(n => CAT[n.type].cls.includes('recorder'));

  nodes.forEach(node => {
    const c = CAT[node.type];
    let st = { ok: true, msg: 'OK', warn: [] };

    if (c.cls.includes('camera')) {
      totalCams++;
      if (c.cls.includes('analog')) {
        const vid = neighbours(node.id, 'v').filter(x=>x.valid);
        const pwr = neighbours(node.id, 'p').filter(x=>x.valid);
        const toDvr = vid.some(x => isType(x.node,'dvr'));
        if (!vid.length) st.warn.push('BNC video not connected');
        else if (!toDvr) st.warn.push('BNC must go to a DVR');
        if (!pwr.some(x => isType(x.node,'power'))) st.warn.push('No 12V DC power');
      } else { // ip
        const net = neighbours(node.id, 'n').filter(x=>x.valid);
        const toNvr = net.some(x => isType(x.node,'nvr'));
        const toPoe = net.some(x => isType(x.node,'poe'));
        if (!net.length) st.warn.push('LAN/PoE not connected');
        else if (!toNvr && !toPoe) st.warn.push('Connect to a PoE switch or NVR');
      }
      st.ok = st.warn.length === 0;
      if (st.ok) onlineCams++; else issues += st.warn.length;
    }
    else if (c.cls.includes('recorder')) {
      if (!neighbours(node.id,'hd').filter(x=>x.valid).some(x=>isType(x.node,'hdd'))) st.warn.push('No hard drive (SATA)');
      if (!neighbours(node.id,'pw').filter(x=>x.valid).some(x=>isType(x.node,'power'))) st.warn.push('No power');
      if (!neighbours(node.id,'out').filter(x=>x.valid).some(x=>isType(x.node,'monitor'))) st.warn.push('No monitor (HDMI)');
      st.ok = st.warn.length === 0; if(!st.ok) issues += st.warn.length;
    }
    else if (c.cls === 'switch') {
      if (!neighbours(node.id,'up').filter(x=>x.valid).some(x=>isType(x.node,'nvr'))) st.warn.push('Uplink not connected to NVR');
      st.ok = st.warn.length===0; if(!st.ok) issues++;
    }
    else { st.ok = true; st.msg = 'Ready'; }

    // paint node status
    const el = board.querySelector('[data-node="'+node.id+'"]');
    if (el) {
      const sl = el.querySelector('.statusline');
      el.style.boxShadow = st.ok ? '0 0 0 2px #10b981' : (st.warn.length ? '0 0 0 2px #ef4444' : '');
      sl.textContent = st.ok ? '● online' : (st.warn.length ? '▲ '+st.warn[0] : '·');
      sl.style.color = st.ok ? '#6ee7b7' : (st.warn.length ? '#fca5a5' : '');
    }
    if ((c.cls.includes('camera')||c.cls.includes('recorder')) && st.warn.length) {
      report.push({ name: c.name+' #'+node.id, warn: st.warn });
      sysOk = false;
    }
  });

  // invalid cables
  const badCables = links.filter(l => !l.valid).length;
  if (badCables) { report.unshift({ name: badCables+' wrong cable connection(s)', warn: ['Connector types do not match (e.g. BNC into RJ45).'] }); sysOk = false; }
  if (!recorders.length && totalCams) { report.push({ name: 'No recorder', warn: ['Add a DVR (analog) or NVR (IP) to record.'] }); sysOk = false; }

  // render report
  const rep = document.getElementById('report');
  if (!nodes.length) { rep.innerHTML = '<p class="text-slate-400">Add a camera and a recorder to begin.</p>'; }
  else if (sysOk && totalCams) { rep.innerHTML = '<div class="rounded-lg bg-emerald-50 px-3 py-2 font-bold text-emerald-700 dark:bg-emerald-500/10">✓ All '+totalCams+' camera(s) online and recording correctly.</div>'; }
  else {
    rep.innerHTML = report.map(r => '<div class="rounded-lg bg-red-50 px-3 py-2 dark:bg-red-500/10"><p class="font-bold text-red-700 dark:text-red-300">▲ '+r.name+'</p><ul class="ml-3 list-disc text-xs text-red-600 dark:text-red-300">'+r.warn.map(w=>'<li>'+w+'</li>').join('')+'</ul></div>').join('') || '<p class="text-slate-400">Wire your components.</p>';
  }

  // system status badge
  const badge = document.getElementById('sysStatus'), txt = document.getElementById('sysStatusText');
  if (sysOk && totalCams) { badge.className = 'rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-5 py-3 text-center dark:border-emerald-500/40 dark:bg-emerald-500/10'; txt.className='text-lg font-black text-emerald-600'; txt.textContent='✓ Operational'; }
  else { badge.className='rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3 text-center dark:border-red-500/40 dark:bg-red-500/10'; txt.className='text-lg font-black text-red-600'; txt.textContent = totalCams? (issues+badCables)+' issue(s)' : 'Not wired'; }

  drawWires();
  storage(totalCams);
}

function storage(cams) {
  const br = +document.getElementById('bitrate').value, hrs = +document.getElementById('hrs').value||0, days = +document.getElementById('days').value||0;
  const gb = cams * br/8 * 3600 * hrs * days / 1024;
  document.getElementById('storeOut').textContent = cams ? gb.toFixed(0)+' GB ('+(gb/1024).toFixed(1)+' TB) for '+cams+' camera(s)' : 'Add cameras to estimate.';
}

function select(node) {
  selected = node; const c = CAT[node.type];
  document.getElementById('inspector').classList.remove('hidden');
  document.getElementById('inspectorBody').innerHTML =
    '<p class="font-bold text-slate-900 dark:text-white">'+c.icon+' '+c.name+'</p>'
    + '<p class="mt-1 text-xs text-slate-500">Ports: '+c.ports.map(p=>p.label+' ('+KINDS[p.kind].n+')').join(', ')+'</p>'
    + '<button onclick="LAB.del('+node.id+')" class="mt-2 rounded-lg border border-red-300 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Delete component</button>';
}

window.LAB = {
  validate,
  del(id){ nodes = nodes.filter(n=>n.id!==id); links = links.filter(l=>l.a.nodeId!==id && l.b.nodeId!==id); const el=board.querySelector('[data-node="'+id+'"]'); if(el)el.remove(); document.getElementById('inspector').classList.add('hidden'); drawWires(); validate(); },
  clear(){ nodes=[]; links=[]; board.innerHTML=''; wires.innerHTML=''; document.getElementById('inspector').classList.add('hidden'); hint.style.display=''; validate(); }
};

// Starter scene so it's obviously usable.
addNode('cam_bullet', 40, 40);
addNode('dvr', 320, 60);
addNode('power', 40, 230);
addNode('hdd', 320, 300);
addNode('monitor', 560, 80);
window.addEventListener('resize', drawWires);
})();
</script>
