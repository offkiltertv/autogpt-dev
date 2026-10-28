'use strict';
const { test } = require('node:test');
const assert = require('node:assert/strict');
const { state, readConsent } = require('../../plugins/offkilter-arcana/assets/js/ok-playback-help.js');

const waiting = { initialized: false, category: 'performance', consent: null, elapsed: 0 };
for (const [name, input, expected] of [
  ['initial load', {}, 'loading'],
  ['declined functionality', { consent: false }, 'consent'],
  ['accepted functionality waits for player', { consent: true }, 'loading'],
  ['missing CMP at deadline is not called a consent failure', { elapsed: 15000 }, 'unavailable'],
  ['accepted consent but provider failure', { consent: true, elapsed: 30000 }, 'unavailable'],
  ['unknown provider does not inherit YouTube classification', { category: '', consent: false }, 'loading'],
  ['unrecognized category stays generic', { category: 'advertising', consent: false, elapsed: 20000 }, 'unavailable'],
  ['existing player does not override current functionality denial', { initialized: true, consent: false }, 'consentPresent'],
  ['initialized unknown provider keeps a neutral player state', { initialized: true, category: '', consent: false }, 'ready'],
  ['initialized after timeout', { initialized: true, elapsed: 30000 }, 'ready'],
]) {
  test(name, () => assert.equal(state({ ...waiting, ...input }), expected));
}

test('consent reader uses only the requested boolean; does not mutate choices', () => {
  const choices = Object.freeze({ performance: true, analytics: false, advertising: false });
  assert.equal(readConsent({ getConsentState: () => choices }, 'performance'), true);
  assert.deepEqual(choices, { performance: true, analytics: false, advertising: false });
});
test('unknown, missing, nonboolean and throwing consent APIs are not denials', () => {
  for (const cmp of [null, {}, { getConsentState: () => null },
    { getConsentState: () => ({ performance: 'false' }) },
    { getConsentState: () => { throw new Error('CMP not initialized'); } }]) {
    assert.equal(readConsent(cmp, 'performance'), null);
  }
  assert.equal(readConsent({ getConsentState: () => ({ performance: false }) }, ''), null);
});
test('explicit denial is retained', () => {
  assert.equal(readConsent({ getConsentState: () => ({ performance: false }) }, 'performance'), false);
});
