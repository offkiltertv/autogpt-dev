'use strict';
// A DOM integration test, not layout, network, CMP, or provider-playback emulation.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const { parseHTML } = require('linkedom');
const { mount } = require('../../plugins/offkilter-arcana/assets/js/ok-playback-help.js');

function setup({ legacy = false, hidden = false, protectedPlayer = false, video = true, category = 'performance' } = {}) {
  const native = '<div class="beeteam368-player-control"><div class="beeteam368-player-wrapper-control">Waiting</div></div>';
  const old = '<div class="beeteam368-player-wrapper-control"><div class="beeteam368-player-control">Waiting</div></div>';
  const protectedMarkup = '<div class="beeteam368-player-protect">Membership required</div>';
  const { window, document } = parseHTML('<!doctype html><html><body><main class="is-single-post-main-player">'
    + (protectedPlayer ? protectedMarkup : legacy ? old : native)
    + '</main><footer><a id="termly-consent-preferences" href="#">Privacy</a></footer>'
    + '<div id="termly-code-snippet-support"><button class="termly-floating-preferences">Preferences</button></div></body></html>');
  const choices = { performance: false, analytics: false, advertising: false };
  const listeners = {};
  const intervals = new Map();
  const win = {
    Termly: { getConsentState: () => ({ ...choices }), on: (name, fn) => { (listeners[name] ||= []).push(fn); } },
    MutationObserver: window.MutationObserver,
    setInterval: fn => { const id = intervals.size + 1; intervals.set(id, fn); return id; },
    clearInterval: id => intervals.delete(id),
    location: { reload: () => { win.reloads++; } }, reloads: 0,
  };
  let opens = 0;
  document.querySelector('.termly-floating-preferences').addEventListener('click', () => { opens++; });
  // This DOM has no layout engine. Visibility is explicit test input.
  for (const node of document.querySelectorAll('.beeteam368-player-control, .beeteam368-player-wrapper-control')) {
    node.getClientRects = () => hidden ? [] : [{}];
  }
  const strings = Object.fromEntries(['summary', 'consent', 'consentPresent', 'loading', 'unavailable', 'ready', 'preferences', 'reload', 'reloadNote', 'preferencesRequested', 'preferencesUnavailable'].map(key => [key, key]));
  const destroy = mount(win, document, { video, category, strings });
  return { win, document, window, choices, intervals, destroy,
    emit: name => (listeners[name] || []).forEach(fn => fn()), opens: () => opens,
    widget: () => document.querySelector('.oktv-playback-help'),
    message: () => document.querySelector('.oktv-playback-help__message')?.textContent };
}

test('actual VidMov child-wrapper contract mounts outside the native player', () => {
  const h = setup();
  try {
    assert.ok(h.widget(), 'native player must receive playback help');
    assert.equal(h.widget().previousElementSibling, h.document.querySelector('.beeteam368-player-control'));
    assert.equal(h.document.querySelector('.beeteam368-player-control .oktv-playback-help'), null);
    assert.equal(h.message(), 'consent');
    assert.equal(h.document.querySelectorAll('iframe,video,audio').length, 0);
  } finally { h.destroy(); }
});

test('legacy ancestor-wrapper contract remains supported', () => {
  const h = setup({ legacy: true });
  try { assert.equal(h.widget()?.previousElementSibling, h.document.querySelector('.beeteam368-player-wrapper-control')); }
  finally { h.destroy(); }
});

for (const scenario of [{ hidden: true }, { protectedPlayer: true }, { video: false }]) {
  test('unsupported or unavailable primary player receives no disclosure: ' + JSON.stringify(scenario), () => {
    const h = setup(scenario);
    try { assert.equal(h.widget(), null); assert.equal(h.document.querySelectorAll('iframe,video,audio').length, 0); }
    finally { h.destroy(); }
  });
}

test('native control delegation and explicit reload never set consent', () => {
  const h = setup();
  try {
    h.widget().querySelector('button').click();
    assert.equal(h.opens(), 1);
    h.document.querySelector('#termly-consent-preferences').click();
    assert.equal(h.opens(), 2);
    h.widget().querySelectorAll('button')[1].click();
    assert.equal(h.win.reloads, 1);
    assert.deepEqual(h.choices, { performance: false, analytics: false, advertising: false });
  } finally { h.destroy(); }
});

test('CMP events update native markup without creating media or duplicate widgets', () => {
  const h = setup();
  try {
    h.choices.performance = true;
    h.emit('consent'); h.emit('initialized'); h.emit('consent');
    assert.equal(h.message(), 'loading');
    assert.equal(h.document.querySelectorAll('.oktv-playback-help').length, 1);
    assert.equal(h.document.querySelectorAll('iframe,video,audio').length, 0);
    assert.equal(h.choices.analytics, false); assert.equal(h.choices.advertising, false);
  } finally { h.destroy(); }
});

test('documented modal function takes priority without requiring floating markup', () => {
  const h = setup();
  try {
    let requests = 0;
    h.win.displayPreferenceModal = () => { requests++; };
    h.widget().querySelector('button').click();
    assert.equal(requests, 1); assert.equal(h.opens(), 0);
    h.document.querySelector('#termly-code-snippet-support').remove();
    h.document.querySelector('#termly-consent-preferences').click();
    assert.equal(requests, 2);
    assert.equal(h.document.querySelector('.oktv-privacy-status').textContent, 'preferencesRequested');
    assert.deepEqual(h.choices, { performance: false, analytics: false, advertising: false });
    assert.equal(h.win.reloads, 0);
    assert.equal(h.document.querySelectorAll('iframe,video,audio').length, 0);
  } finally { h.destroy(); }
});

test('throwing modal function fails visibly without a second provider action', () => {
  const h = setup();
  try {
    h.win.displayPreferenceModal = () => { throw new Error('CMP unavailable'); };
    assert.doesNotThrow(() => h.widget().querySelector('button').click());
    assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesUnavailable');
    assert.equal(h.opens(), 0);
    h.win.displayPreferenceModal = () => {};
    h.widget().querySelector('button').click();
    assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesRequested');
  } finally { h.destroy(); }
});

test('late modal function recovers an earlier unavailable action without polling or reload', () => {
  const h = setup();
  try {
    h.document.querySelector('#termly-code-snippet-support').remove();
    h.widget().querySelector('button').click();
    assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesUnavailable');
    let requests = 0;
    h.win.displayPreferenceModal = () => { requests++; };
    h.widget().querySelector('button').click();
    assert.equal(requests, 1);
    assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesRequested');
    assert.equal(h.intervals.size, 1); assert.equal(h.win.reloads, 0);
  } finally { h.destroy(); }
});

test('throwing or silent fallback does not report a successful dialog', () => {
  const h = setup();
  try {
    const trigger = h.document.querySelector('.termly-floating-preferences');
    trigger.click = () => { throw new Error('Native control unavailable'); };
    assert.doesNotThrow(() => h.widget().querySelector('button').click());
    assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesUnavailable');
    trigger.click = () => {};
    h.widget().querySelector('button').click();
    assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesRequested');
    assert.equal(h.win.reloads, 0);
  } finally { h.destroy(); }
});

for (const disabled of ['disabled', 'aria-disabled', 'termly-display-preferences--disabled']) {
  test('provider-disabled control is not bypassed: ' + disabled, () => {
    const h = setup();
    try {
      const trigger = h.document.querySelector('.termly-floating-preferences');
      if (disabled === 'disabled') trigger.disabled = true;
      else if (disabled === 'aria-disabled') trigger.setAttribute(disabled, 'true');
      else trigger.classList.add(disabled);
      let requests = 0;
      h.win.displayPreferenceModal = () => { requests++; };
      h.widget().querySelector('button').click();
      assert.equal(requests, 0); assert.equal(h.opens(), 0);
      assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesUnavailable');
    } finally { h.destroy(); }
  });
}

test('supported static and already-handled legacy links do not get a second action', () => {
  const h = setup();
  try {
    const footer = h.document.querySelector('#termly-consent-preferences');
    footer.classList.add('termly-display-preferences');
    footer.click();
    assert.equal(h.opens(), 0);
    footer.classList.remove('termly-display-preferences');
    footer.addEventListener('click', event => event.preventDefault());
    footer.click();
    assert.equal(h.opens(), 0);
    assert.equal(h.document.querySelector('.oktv-privacy-status'), null);
  } finally { h.destroy(); }
});

test('late provider initialization is observed without replacing the native player', async () => {
  const h = setup();
  try {
    h.choices.performance = true; h.emit('consent');
    const root = h.document.querySelector('.beeteam368-player-control');
    const frame = h.document.createElement('iframe');
    frame.getClientRects = () => [{}];
    frame.setAttribute('src', 'https://provider.invalid/local-dom-only');
    root.querySelector('.beeteam368-player-wrapper-control').append(frame);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(h.message(), 'ready');
    assert.equal(h.document.querySelector('.beeteam368-player-control'), root);
    assert.equal(h.document.querySelectorAll('iframe').length, 1);
    assert.equal(h.widget().previousElementSibling, root);
    h.choices.performance = false; h.emit('consent');
    assert.equal(h.message(), 'consentPresent');
    assert.equal(h.document.querySelectorAll('iframe').length, 1);
    assert.equal(h.win.reloads, 0);
  } finally { h.destroy(); }
});

test('missing preferences control reports unavailability and cleanup is complete', () => {
  const h = setup();
  h.document.querySelector('#termly-code-snippet-support').remove();
  h.widget().querySelector('button').click();
  assert.equal(h.document.querySelector('.oktv-playback-help__status').textContent, 'preferencesUnavailable');
  h.destroy();
  assert.equal(h.widget(), null); assert.equal(h.intervals.size, 0);
  h.emit('consent');
  assert.equal(h.widget(), null);
});
