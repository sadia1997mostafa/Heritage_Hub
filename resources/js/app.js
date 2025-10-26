import '../css/app.css';
import '../css/themes.css';
import '../css/ux-3d.css';
import '../css/ui-3d-advanced.css';

document.addEventListener('DOMContentLoaded', () => {

      
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

  
  const body = document.body;
  const prog = document.getElementById('hh-progress');
  const sections = ['about','explore','app','contact']
    .map(id => document.getElementById(id)).filter(Boolean);
  const navLinks = [...document.querySelectorAll('.hh-menu a')];

  const onScroll = () => {
    const y = window.scrollY || window.pageYOffset;
    
    if (y > 10) body.classList.add('shrink'); else body.classList.remove('shrink');
    
    if (prog) {
      const h = document.documentElement;
      const scrolled = (h.scrollTop) / (h.scrollHeight - h.clientHeight);
      prog.style.width = (scrolled * 100) + '%';
    }
  
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

  // Theme selector: apply persisted theme or default
  const applyTheme = (theme) => {
    document.body.classList.remove('theme-saffron','theme-teal','theme-forest');
    if (theme && theme !== 'default') document.body.classList.add('theme-'+theme);
  };
  const saved = localStorage.getItem('hh:theme') || 'default';
  applyTheme(saved);
  const themeSelect = document.getElementById('hh-theme-select');
  if (themeSelect) {
    themeSelect.value = saved;
    themeSelect.addEventListener('change', (e)=>{
      const t = e.target.value;
      localStorage.setItem('hh:theme', t);
      applyTheme(t);
    });
  }

  // Lightweight 3D tilt for cards (desktop only)
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const supportsPointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  // Respect user's reduced motion preference
  if (prefersReduced) return;
  if (supportsPointer) {
    const tiltCards = [...document.querySelectorAll('.tilt-card')];
    tiltCards.forEach(card => {
      const inner = card.querySelector('.tilt-inner') || card;
      let rect = null;
      card.addEventListener('mousemove', (e) => {
        rect = rect || card.getBoundingClientRect();
        const cx = rect.left + rect.width/2;
        const cy = rect.top + rect.height/2;
        const dx = (e.clientX - cx) / rect.width;
        const dy = (e.clientY - cy) / rect.height;
        // Reduced tilt strength for performance and subtlety
        const strength = 5; // degrees max
        const rx = (-dy * strength).toFixed(2);
        const ry = (dx * strength).toFixed(2);
        inner.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) translateZ(0)`;
        card.classList.add('tilt-active');
      });
      card.addEventListener('mouseleave', () => {
        inner.style.transform = '';
        card.classList.remove('tilt-active');
        rect = null;
      });
    });

    // Parallax hero effect: small translate for background layer
    const heroes = [...document.querySelectorAll('.parallax-hero')];
    heroes.forEach(hero => {
      const layer = hero.querySelector('.parallax-layer');
      if (!layer) return;
      hero.addEventListener('mousemove', (e) => {
        const r = hero.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        // clamp values to avoid large transforms
        const tx = Math.max(-1, Math.min(1, px)) * 6;
        const ty = Math.max(-1, Math.min(1, py)) * 6;
        layer.style.transform = `translate3d(${tx}px, ${ty}px, -40px) scale(1.05)`;
      });
      hero.addEventListener('mouseleave', ()=>{ layer.style.transform = ''; });
    });
  }
  // Soft depth lighting: subtle highlight that follows pointer for large cards
  const depthTargets = [...document.querySelectorAll('.rim-light')];
  if (!prefersReduced && supportsPointer && depthTargets.length) {
    depthTargets.forEach(target=>{
      const onMove = (e) => {
        const r = target.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        target.style.setProperty('--light-x', (px*40)+'px');
        target.style.setProperty('--light-y', (py*40)+'px');
        target.style.filter = `brightness(${1 + Math.abs(px)*0.06})`;
      };
      target.addEventListener('mousemove', onMove);
      target.addEventListener('mouseleave', ()=>{ target.style.filter=''; });
    });
  }

  // Shop page 3D rotator: auto-rotate, manual prev/next, respects reduced motion
  const rotators = [...document.querySelectorAll('.rotator')];
  rotators.forEach(rot => {
    const slides = [...rot.querySelectorAll('.rotor-slide')];
    if (!slides.length) return;
    let idx = 0;

    const refreshClasses = (centerIndex) => {
      slides.forEach((s, i)=>{
        s.classList.remove('is-center','side-left','side-right');
        if (i === centerIndex) s.classList.add('is-center');
        else if (i < centerIndex) s.classList.add('side-left');
        else s.classList.add('side-right');
      });
      // center the rotator by translating so center slide is visible
      const centerSlide = slides[centerIndex];
      const offset = centerSlide ? centerSlide.offsetLeft - (rot.parentElement.offsetWidth/2 - centerSlide.offsetWidth/2) : 0;
      rot.style.transform = `translateZ(-120px) translateX(${ -offset }px)`;
    };

    refreshClasses(idx);

    // autoplay if supported and not reduced motion
    let timer = null;
    if (!prefersReduced) timer = setInterval(()=>{ idx = (idx+1) % slides.length; refreshClasses(idx); }, 3200);

    // controls
    const parent = rot.closest('.shop-rotator');
    if (parent) {
      const prev = parent.querySelector('.rot-prev');
      const next = parent.querySelector('.rot-next');
      if (prev) prev.addEventListener('click', (e)=>{ e.preventDefault(); idx = (idx - 1 + slides.length) % slides.length; refreshClasses(idx); if (timer){clearInterval(timer); timer=null;} });
      if (next) next.addEventListener('click', (e)=>{ e.preventDefault(); idx = (idx + 1) % slides.length; refreshClasses(idx); if (timer){clearInterval(timer); timer=null;} });
      // pause on hover
      parent.addEventListener('mouseenter', ()=>{ if (timer){ clearInterval(timer); timer=null;} });
      parent.addEventListener('mouseleave', ()=>{ if (!prefersReduced && !timer) timer = setInterval(()=>{ idx = (idx+1) % slides.length; refreshClasses(idx); }, 3200); });
    }
  });
    // Craft Origin Map Interactions 
 // Craft Origin Map Interactions 
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
    if (!tip) return;
    tip.textContent = name;
    tip.hidden = false;
    tip.style.left = e.pageX + 'px';
    tip.style.top = e.pageY + 'px';
  };
  d.addEventListener('mousemove', showTip);
  d.addEventListener('mouseenter', (e)=>{ d.classList.add('hover'); showTip(e); });
  d.addEventListener('mouseleave', ()=>{ d.classList.remove('hover'); if (tip) tip.hidden = true; });

  
  d.addEventListener('click', () => {
    
    divisions.forEach(x => x.classList.remove('active'));
    d.classList.add('active');

    const divName = nameFromId(d.id);

    
    if (panel) panel.dataset.division = divName;

    
    if (panelTitle) panelTitle.textContent = `${divName} Division`;

    const entries = craftData[divName] || [];
    if (panelList) {
  panelList.innerHTML = entries.map(({ district, crafts }) => `
    <a
      class="panel-item"
      data-district="${district}"
      href="/district/${encodeURIComponent(district.toLowerCase())}"
    >
      <b>${district}</b><br>
      <small>${crafts.join(', ')}</small>
    </a>
  `).join('');


    }

    // link to shop with division filter (optional param)
    if (viewAllBtn) {
      const base = viewAllBtn.href.split('?')[0];
      viewAllBtn.href = `${base}?division=${encodeURIComponent(divName)}`;
    }

  
    if (panel) panel.classList.add('open');
  });
});


if (panelClose && panel) {
  panelClose.addEventListener('click', ()=> panel.classList.remove('open'));
  document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') panel.classList.remove('open'); });
}

// District click => go to full page
if (panelList && panel) {
  panelList.addEventListener('click', (e) => {
    const item = e.target.closest('[data-district]');
    if (!item) return;

    const division = panel.dataset.division;   
    const district = item.getAttribute('data-district');

    if (!division || !district) return;

    // Navigate to SSR page (you added this route already)
    window.location.href = `/heritage/${encodeURIComponent(division)}/${encodeURIComponent(district)}`;
  });
}

});
