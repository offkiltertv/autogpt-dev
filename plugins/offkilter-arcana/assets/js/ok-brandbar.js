/**
 * OKTV brand bar — inject a self-owned fixed bar in the pillar palette and
 * flow it with scroll position (sets --ok-scroll 0..1). Reduced-motion leaves
 * a static on-brand bar. Independent of the theme's own top bar.
 */
(function () {
  if (document.querySelector('.ok-brandbar')) { return; }
  var bar = document.createElement('div');
  bar.className = 'ok-brandbar';
  bar.setAttribute('aria-hidden', 'true');
  (document.body || document.documentElement).appendChild(bar);

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function update() {
    var doc = document.documentElement;
    var max = (doc.scrollHeight - doc.clientHeight) || 1;
    var y = window.scrollY || window.pageYOffset || doc.scrollTop || 0;
    bar.style.setProperty('--ok-scroll', Math.min(1, Math.max(0, y / max)).toFixed(4));
  }

  if (reduce) { update(); return; }

  var ticking = false, idle = null;
  bar.classList.add('ok-bar-idle');
  window.addEventListener('scroll', function () {
    bar.classList.remove('ok-bar-idle');
    if (!ticking) { window.requestAnimationFrame(function () { update(); ticking = false; }); ticking = true; }
    if (idle) { clearTimeout(idle); }
    idle = setTimeout(function () { bar.classList.add('ok-bar-idle'); }, 600);
  }, { passive: true });
  window.addEventListener('resize', update, { passive: true });
  update();
})();
