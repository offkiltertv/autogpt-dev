(function (root, factory) {
  'use strict';
  var api = factory();
  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  } else if (root.OKTVPlaybackHelpConfig) {
    api.mount(root, root.document, root.OKTVPlaybackHelpConfig);
  }
}(typeof window !== 'undefined' ? window : this, function () {
  'use strict';

  function state(input) {
    if (input.initialized) return input.category === 'performance' && input.consent === false ? 'consentPresent' : 'ready';
    if (input.category === 'performance' && input.consent === false) return 'consent';
    return input.elapsed >= 15000 ? 'unavailable' : 'loading';
  }

  function readConsent(cmp, category) {
    if (!category || !cmp || typeof cmp.getConsentState !== 'function') return null;
    try {
      var choices = cmp.getConsentState();
      return choices && typeof choices[category] === 'boolean' ? choices[category] : null;
    } catch (error) {
      return null;
    }
  }

  function mount(win, doc, config) {
    var strings = config.strings;
    var start = Date.now();
    var timer;
    var subscribed = null;
    var widget = null;
    var player = null;
    var lastState = '';
    var userToggled = false;
    var stopped = false;
    var footerStatus = null;
    var observer = null;

    function initialized() {
      if (!player) return false;
      return Array.prototype.some.call(player.querySelectorAll('iframe, video, audio'), function (media) {
        if (!media.getClientRects().length) return false;
        if (media.tagName === 'IFRAME') {
          var src = media.getAttribute('src');
          return !!src && src !== 'about:blank';
        }
        return media.readyState >= 1;
      });
    }

    function openPreferences(status) {
      // Prefer the documented modal function; never write or simulate consent.
      var trigger = doc.querySelector('#termly-code-snippet-support .termly-floating-preferences');
      if (trigger && (trigger.disabled || trigger.getAttribute('aria-disabled') === 'true'
        || trigger.classList.contains('termly-display-preferences--disabled'))) {
        status.textContent = strings.preferencesUnavailable;
        return;
      }
      // A dispatched action is not proof that the vendor's modal opened.
      status.textContent = strings.preferencesRequested || strings.preferencesUnavailable;
      try {
        if (typeof win.displayPreferenceModal === 'function') {
          win.displayPreferenceModal();
        } else if (trigger) {
          trigger.click();
        } else {
          status.textContent = strings.preferencesUnavailable;
        }
      } catch (error) {
        status.textContent = strings.preferencesUnavailable;
      }
    }

    function makeWidget() {
      if (!config.video || widget) return;
      player = doc.querySelector('.is-single-post-main-player .beeteam368-player-control');
      if (!player || !player.getClientRects().length) {
        player = null;
        return;
      }
      // VidMov 2.3.8 nests its ratio/floating wrapper inside the player root.
      var nativeWrapper = player.querySelector(':scope > .beeteam368-player-wrapper-control');
      var wrapper = nativeWrapper || player.closest('.beeteam368-player-wrapper-control');
      if (!wrapper || !wrapper.getClientRects().length) return;
      var anchor = nativeWrapper ? player : wrapper;
      // Provider initialization may happen after bounded CMP discovery has ended.
      observer = new win.MutationObserver(check);
      observer.observe(player, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });
      player.addEventListener('loadedmetadata', check, true);
      player.addEventListener('load', check, true);
      widget = doc.createElement('details');
      widget.className = 'oktv-playback-help';
      var summary = doc.createElement('summary');
      summary.textContent = strings.summary;
      var message = doc.createElement('p');
      message.className = 'oktv-playback-help__message';
      var actions = doc.createElement('div');
      actions.className = 'oktv-playback-help__actions';
      var preferences = doc.createElement('button');
      preferences.type = 'button';
      preferences.textContent = strings.preferences;
      var reload = doc.createElement('button');
      reload.type = 'button';
      reload.textContent = strings.reload;
      var note = doc.createElement('p');
      note.className = 'oktv-playback-help__note';
      note.textContent = strings.reloadNote;
      var status = doc.createElement('p');
      status.className = 'oktv-playback-help__status';
      status.setAttribute('role', 'status');
      preferences.addEventListener('click', function () { openPreferences(status); });
      reload.addEventListener('click', function () { win.location.reload(); });
      summary.addEventListener('click', function () { userToggled = true; check(); });
      actions.append(preferences, reload);
      widget.append(summary, message, actions, note, status);
      anchor.insertAdjacentElement('afterend', widget);
    }

    function render() {
      if (!widget) return;
      var next = state({
        initialized: initialized(),
        category: config.category,
        consent: readConsent(win.Termly, config.category),
        elapsed: Date.now() - start
      });
      if (next === lastState) return;
      widget.querySelector('.oktv-playback-help__message').textContent = strings[next] || strings.consent;
      // Do not collapse the disclosure or move focus while someone uses it.
      if (!userToggled && next !== 'ready' && next !== 'loading') widget.open = true;
      lastState = next;
    }

    function check() {
      if (stopped) return;
      if (win.Termly && win.Termly !== subscribed && typeof win.Termly.on === 'function') {
        try {
          subscribed = win.Termly;
          subscribed.on('initialized', check);
          subscribed.on('consent', check);
        } catch (error) {
          // Bounded polling still works if the CMP API is temporarily unavailable.
        }
      }
      makeWidget();
      render();
      if (Date.now() - start >= 60000) win.clearInterval(timer);
    }

    function footerClick(event) {
      // Native inline/CMP handlers may already have handled this click.
      if (event.defaultPrevented) return;
      var target = event.target.closest && event.target.closest('#termly-consent-preferences');
      if (!target || target.classList.contains('termly-display-preferences')) return;
      event.preventDefault();
      if (!footerStatus) {
        footerStatus = doc.createElement('span');
        footerStatus.className = 'oktv-privacy-status';
        footerStatus.setAttribute('role', 'status');
        target.insertAdjacentElement('afterend', footerStatus);
      }
      openPreferences(footerStatus);
    }

    function visibility() {
      if (doc.visibilityState === 'visible') check();
    }

    doc.addEventListener('click', footerClick);
    doc.addEventListener('visibilitychange', visibility);
    timer = win.setInterval(check, 1000);
    check();
    return function destroy() {
      stopped = true;
      win.clearInterval(timer);
      doc.removeEventListener('click', footerClick);
      doc.removeEventListener('visibilitychange', visibility);
      if (observer) observer.disconnect();
      if (player) {
        player.removeEventListener('loadedmetadata', check, true);
        player.removeEventListener('load', check, true);
      }
      if (widget) widget.remove();
      if (footerStatus) footerStatus.remove();
    };
  }

  return { state: state, readConsent: readConsent, mount: mount };
}));
