/**
 * Light/dark switch (.sokal-mode-toggle). Owner: mode agent.
 *
 * Uses window.sokalMode from the inline head script (include/mode.php). The
 * choice is stored in localStorage until the next automatic switch time.
 * aria-pressed="true" = dark mode on.
 */
(function () {
	'use strict';

	var d = document;
	var h = d.documentElement;
	var LABEL = 'রাতের মোড';
	var TIP_TO_DARK = 'রাতের মোড চালু করুন';
	var TIP_TO_LIGHT = 'দিনের মোড চালু করুন';

	function current() {
		return h.getAttribute('data-mode') === 'dark' ? 'dark' : 'light';
	}

	// Fallback if the head script is missing (e.g. stripped by an optimiser).
	function setMode(m) {
		if (window.sokalMode && typeof window.sokalMode.set === 'function') {
			window.sokalMode.set(m);
			return;
		}
		h.setAttribute('data-mode', m);
		h.style.colorScheme = m;
		try {
			var tomorrow = new Date();
			tomorrow.setHours(24, 0, 0, 0);
			window.localStorage.setItem('sokal_mode', JSON.stringify({ m: m, x: tomorrow.getTime() }));
		} catch (e) { /* storage blocked */ }
		sync();
	}

	function sync() {
		var dark = current() === 'dark';
		var btns = d.querySelectorAll('.sokal-mode-toggle');
		for (var i = 0; i < btns.length; i++) {
			btns[i].setAttribute('aria-pressed', dark ? 'true' : 'false');
			btns[i].setAttribute('aria-label', LABEL);
			btns[i].setAttribute('title', dark ? TIP_TO_LIGHT : TIP_TO_DARK);
		}
	}

	d.addEventListener('click', function (e) {
		var t = e.target;
		var btn = t && t.closest ? t.closest('.sokal-mode-toggle') : null;
		if (!btn) {
			return;
		}
		e.preventDefault();
		setMode(current() === 'dark' ? 'light' : 'dark');
	});

	d.addEventListener('sokal:mode', sync);

	if (d.readyState === 'loading') {
		d.addEventListener('DOMContentLoaded', sync);
	} else {
		sync();
	}
})();
