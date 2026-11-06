/* Share buttons: copy-link handling (vanilla, defer-safe). */
(function () {
	'use strict';

	var DONE = 'লিংক কপি হয়েছে';
	var FAIL = 'কপি করা যায়নি';

	function legacyCopy(text) {
		var ta = document.createElement('textarea');
		ta.value = text;
		ta.setAttribute('readonly', '');
		ta.style.position = 'fixed';
		ta.style.top = '-1000px';
		ta.style.opacity = '0';
		document.body.appendChild(ta);
		ta.select();
		ta.setSelectionRange(0, text.length);
		var ok = false;
		try {
			ok = document.execCommand('copy');
		} catch (e) {
			ok = false;
		}
		document.body.removeChild(ta);
		return ok;
	}

	function copy(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text).then(
				function () { return true; },
				function () { return legacyCopy(text); }
			);
		}
		return Promise.resolve(legacyCopy(text));
	}

	function flash(btn, msg, ok) {
		var wrap = btn.closest('.sokal-share');
		var status = wrap ? wrap.querySelector('.sokal-share__status') : null;
		if (status) {
			status.textContent = msg;
			status.classList.add('is-visible');
		}
		btn.classList.toggle('is-copied', !!ok);
		clearTimeout(btn._sokalT);
		btn._sokalT = setTimeout(function () {
			btn.classList.remove('is-copied');
			if (status) {
				status.classList.remove('is-visible');
				status.textContent = '';
			}
		}, 2500);
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('[data-sokal-copy]') : null;
		if (!btn) {
			return;
		}
		e.preventDefault();
		var url = btn.getAttribute('data-sokal-copy') || window.location.href;
		copy(url).then(function (ok) {
			flash(btn, ok ? DONE : FAIL, ok);
		});
	});
})();
