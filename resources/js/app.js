import '../css/app.css';

document.addEventListener('DOMContentLoaded', () => {

      // Mega menu (accessible: click/focus/touch)
  document.querySelectorAll('.has-mega > a').forEach(trigger => {
    const panel = trigger.nextElementSibling;
    if (!panel) return;

    const closeAll = () => {
      document.querySelectorAll('.has-mega > a[aria-expanded="true"]').forEach(a=>{
        a.setAttribute('aria-expanded','false');
        a.nextElementSibling?.classList.remove('open');
        a.nextElementSibling?.setAttribute('hidden','');
      });
    };

    // prepare
    panel.setAttribute('hidden','');

    const open = () => {
      closeAll();
      trigger.setAttribute('aria-expanded','true');
      panel.classList.add('open');
      panel.removeAttribute('hidden');
    };
    const close = () => {
      trigger.setAttribute('aria-expanded','false');
      panel.classList.remove('open');
      panel.setAttribute('hidden','');
    };

    trigger.addEventListener('click', (e)=>{
      // on mobile when menu collapsed, let it act as section opener rather than navigate away
      if (window.innerWidth <= 980) e.preventDefault();
      trigger.getAttribute('aria-expanded') === 'true' ? close() : open();
    });

    // keyboard: open on focus
    trigger.addEventListener('focus', open);
    panel.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') close(); });

    // click outside closes
    document.addEventListener('click', (e)=>{
      if (!panel.contains(e.target) && e.target !== trigger) close();
    });
  });

  // Mobile menu
  const burger = document.getElementById('hh-burger');
  const menu = document.querySelector('.hh-menu');
  if (burger && menu) burger.addEventListener('click', () => menu.classList.toggle('show'));

  // Shrink header on scroll + progress bar + active anchor
  const body = document.body;
  const prog = document.getElementById('hh-progress');
  const sections = ['about','explore','app','contact']
    .map(id => document.getElementById(id)).filter(Boolean);
  const navLinks = [...document.querySelectorAll('.hh-menu a')];

  const onScroll = () => {
    const y = window.scrollY || window.pageYOffset;
    // shrink
    if (y > 10) body.classList.add('shrink'); else body.classList.remove('shrink');
    // progress
    if (prog) {
      const h = document.documentElement;
      const scrolled = (h.scrollTop) / (h.scrollHeight - h.clientHeight);
      prog.style.width = (scrolled * 100) + '%';
    }
    // active anchor
    if (sections.length) {
      const pos = y + 130;
      let current = null;
      for (const s of sections) if (s.offsetTop <= pos) current = s.id;
      navLinks.forEach(a => {
        if (!a.hash) return;
        a.classList.toggle('active', a.hash.replace('#','') === current);
      });
    }
  };
  onScroll();
  window.addEventListener('scroll', onScroll);
    // ===== Craft Origin Map Interactions =====
  const tip = document.getElementById('map-tip');
  const panel = document.getElementById('craft-panel');
  const panelTitle = document.getElementById('panel-title');
  const panelList = document.getElementById('panel-list');
  const panelClose = document.getElementById('panel-close');
  const viewAllBtn = document.getElementById('panel-view-all');

  // Division -> Districts & Crafts data
  const craftData = {
    Dhaka: [
      { district: 'Tangail', crafts: ['Tangail Saree', 'Handloom'] },
      { district: 'Narayanganj', crafts: ['Jamdani Saree'] },
      { district: 'Dhaka', crafts: ['Rickshaw Art', 'Traditional Metalwork'] },
      { district: 'Manikganj', crafts: ['Brass & Copper'] },
    ],
    Mymensingh: [
      { district: 'Mymensingh', crafts: ['Nakshi Kantha', 'Folk Music Instruments'] },
      { district: 'Jamalpur', crafts: ['Hand Embroidery'] },
      { district: 'Netrokona', crafts: ['Bamboo & Cane'] },
      { district: 'Sherpur', crafts: ['Rural Weaves'] },
    ],
    Rajshahi: [
      { district: 'Rajshahi', crafts: ['Rajshahi Silk'] },
      { district: 'Natore', crafts: ['Nakshi Kantha'] },
      { district: 'Chapainababganj', crafts: ['Terracotta'] },
      { district: 'Pabna', crafts: ['Clay Pottery'] },
    ],
    Rangpur: [
      { district: 'Rangpur', crafts: ['Bamboo Crafts'] },
      { district: 'Lalmonirhat', crafts: ['Cane Work'] },
      { district: 'Kurigram', crafts: ['Folk Weaving'] },
      { district: 'Dinajpur', crafts: ['Woodcraft'] },
    ],
    Sylhet: [
      { district: 'Sylhet', crafts: ['Cane & Shital Pati'] },
      { district: 'Sunamganj', crafts: ['Cane & Bamboo'] },
      { district: 'Habiganj', crafts: ['Clay Toys'] },
      { district: 'Moulvibazar', crafts: ['Tribal Textiles'] },
    ],
    Khulna: [
      { district: 'Khulna', crafts: ['Shital Pati', 'Wood Inlay'] },
      { district: 'Jessore', crafts: ['Terracotta'] },
      { district: 'Bagerhat', crafts: ['Brass & Copper'] },
      { district: 'Satkhira', crafts: ['Sea Shell Crafts'] },
    ],
    Barisal: [
      { district: 'Barisal', crafts: ['Woodcraft'] },
      { district: 'Pirojpur', crafts: ['Cane & Bamboo'] },
      { district: 'Bhola', crafts: ['Coconut Crafts'] },
      { district: 'Jhalokathi', crafts: ['Lacquer Work'] },
    ],
    Chittagong: [
      { district: 'Chattogram', crafts: ['Shipbreaking Metal Art', 'Wood Carving'] },
      { district: 'Cox’s Bazar', crafts: ['Shell & Sea Crafts', 'Bamboo'] },
      { district: 'Bandarban', crafts: ['Tribal Weaving'] },
      { district: 'Rangamati', crafts: ['Tribal Textiles & Jewelry'] },
    ],
  };

  // map hover tooltip
  const divisions = document.querySelectorAll('.bd-map .division');
  const nameFromId = (id) => {
    const n = {
      dhaka:'Dhaka', mymensingh:'Mymensingh', rajshahi:'Rajshahi', rangpur:'Rangpur',
      sylhet:'Sylhet', khulna:'Khulna', barisal:'Barisal', chittagong:'Chittagong'
    };
    return n[id] || id;
  };

  divisions.forEach(d => {
    const showTip = (e) => {
      const name = nameFromId(d.id);
      tip.textContent = name;
      tip.hidden = false;
      tip.style.left = e.pageX + 'px';
      tip.style.top = e.pageY + 'px';
    };
    d.addEventListener('mousemove', showTip);
    d.addEventListener('mouseenter', (e)=>{ d.classList.add('hover'); showTip(e); });
    d.addEventListener('mouseleave', ()=>{ d.classList.remove('hover'); tip.hidden = true; });

    d.addEventListener('click', () => {
      // toggle selection
      divisions.forEach(x => x.classList.remove('active'));
      d.classList.add('active');

      const divName = nameFromId(d.id);
      const entries = craftData[divName] || [];
      panelTitle.textContent = `${divName} Division`;
      panelList.innerHTML = entries.map(({district, crafts}) =>
        `<div class="panel-item"><b>${district}</b><br><small>${crafts.join(', ')}</small></div>`
      ).join('');

      // link to shop with division filter (optional param)
      if (viewAllBtn) viewAllBtn.href = `${viewAllBtn.href.split('?')[0]}?division=${encodeURIComponent(divName)}`;

      panel.classList.add('open');
    });
  });

  // close panel
  if (panelClose) panelClose.addEventListener('click', ()=> panel.classList.remove('open'));
  document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') panel.classList.remove('open'); });

});
