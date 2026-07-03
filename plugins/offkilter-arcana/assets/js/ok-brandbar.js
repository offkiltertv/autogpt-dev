/**
 * OKTV brand bar — flow the top color bar with scroll position.
 * Sets --ok-scroll (0..1) on the bar; CSS (Section AG) reacts.
 * Adds .ok-bar-idle when the page is at rest so an idle shimmer can play.
 */
(function () {
  var bar = document.querySelector('.beeteam368_color_bar');
  if (!bar) { return; }

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var ticking = false;
  var idleTimer = null;

  function update() {
    var doc = document.documentElement;
    var max = (doc.scrollHeight - doc.clientHeight) || 1;
    var y = window.scrollY || window.pageYOffset || doc.scrollTop || 0;
    var p = Math.min(1, Math.max(0, y / max));
    bar.style.setProperty('--ok-scroll', p.toFixed(4));
    ticking = false;
  }

  if (reduce) { return; } // leave the static on-brand bar; no motion

  bar.classList.add('ok-bar-idle');
  window.addEventListener('scroll', function () {
    bar.classList.remove('ok-bar-idle');
    if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
    if (idleTimer) { clearTimeout(idleTimer); }
    idleTimer = setTimeout(function () { bar.classList.add('ok-bar-idle'); }, 600);
  }, { passive: true });

  update();
})();
