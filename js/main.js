/**
 * Front-end behaviour, no jQuery. Owner: assets agent.
 * Replaces Bootstrap 3 JS for [data-toggle="collapse"|"dropdown"|"tab"],
 * plus scroll-to-top, print buttons, the view-count beacon and hover prefetch.
 */
(function () {
	'use strict';

	var doc = document;

	function closest(el, sel) {
		while (el && el.nodeType === 1) {
			if (el.matches ? el.matches(sel) : el.msMatchesSelector(sel)) { return el; }
			el = el.parentNode;
		}
		return null;
	}

	function targetOf(trigger) {
		var sel = trigger.getAttribute('data-target') || trigger.getAttribute('href') || '';
		if (!sel || sel.charAt(0) !== '#' || sel.length < 2) { return null; }
		try { return doc.querySelector(sel); } catch (e) { return null; }
	}

	/* ---------- Dropdowns ---------- */
	function closeDropdowns(except) {
		var open = doc.querySelectorAll('[data-toggle="dropdown"]');
		for (var i = 0; i < open.length; i++) {
			var li = open[i].parentNode;
			if (li && li !== except && li.classList && li.classList.contains('open')) {
				li.classList.remove('open');
				open[i].setAttribute('aria-expanded', 'false');
			}
		}
	}

	function toggleDropdown(trigger) {
		var li = closest(trigger, 'li') || trigger.parentNode;
		if (!li) { return; }
		var willOpen = !li.classList.contains('open');
		closeDropdowns(li);
		li.classList.toggle('open', willOpen);
		trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
	}

	/* ---------- Collapse ---------- */
	function toggleCollapse(trigger) {
		var target = targetOf(trigger);
		if (!target) { return; }
		var willOpen = !target.classList.contains('in');
		target.classList.toggle('in', willOpen);
		trigger.classList.toggle('collapsed', !willOpen);
		trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
		target.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
	}

	/* ---------- Tabs ---------- */
	function showTab(trigger) {
		var li = closest(trigger, 'li');
		var pane = targetOf(trigger);
		if (!li || !pane || li.classList.contains('active')) { return; }
		var list = li.parentNode;
		var tabs = list.querySelectorAll('[data-toggle="tab"]');
		for (var i = 0; i < tabs.length; i++) {
			var otherLi = closest(tabs[i], 'li');
			if (otherLi) { otherLi.classList.remove('active'); }
			tabs[i].setAttribute('aria-expanded', 'false');
			tabs[i].setAttribute('aria-selected', 'false');
		}
		var container = pane.parentNode;
		var panes = container ? container.children : [];
		for (var j = 0; j < panes.length; j++) {
			if (panes[j].classList.contains('tab-pane')) {
				panes[j].classList.remove('active', 'in');
			}
		}
		li.classList.add('active');
		trigger.setAttribute('aria-expanded', 'true');
		trigger.setAttribute('aria-selected', 'true');
		pane.classList.add('active');
		if (pane.classList.contains('fade')) {
			// Force a reflow so the opacity transition runs, as Bootstrap did.
			void pane.offsetWidth;
		}
		pane.classList.add('in');
	}

	/* ---------- One delegated click handler ---------- */
	doc.addEventListener('click', function (e) {
		var t = e.target;
		if (!t || t.nodeType !== 1) { t = t && t.parentNode; }
		if (!t) { return; }

		if (closest(t, '[data-sokal-print]')) {
			e.preventDefault();
			window.print();
			return;
		}

		var top = closest(t, '.scrollToTop');
		if (top) {
			e.preventDefault();
			var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			try {
				window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
			} catch (err) {
				window.scrollTo(0, 0);
			}
			return;
		}

		var trigger = closest(t, '[data-toggle]');
		if (trigger) {
			var kind = trigger.getAttribute('data-toggle');
			if (kind === 'dropdown') {
				e.preventDefault();
				toggleDropdown(trigger);
				return;
			}
			if (kind === 'collapse') {
				e.preventDefault();
				toggleCollapse(trigger);
			} else if (kind === 'tab' || kind === 'pill') {
				e.preventDefault();
				showTab(trigger);
			}
		}

		// Clicks outside an open dropdown close it.
		if (!closest(t, '.dropdown-menu')) {
			closeDropdowns(null);
		}
	});

	doc.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
			var openLi = doc.querySelector('li.open > [data-toggle="dropdown"]');
			closeDropdowns(null);
			if (openLi && openLi.focus) { openLi.focus(); }
		}
	});

	/* ---------- Scroll-to-top visibility ---------- */
	var btns = doc.querySelectorAll('.scrollToTop');
	if (btns.length) {
		var shown = null;
		var setShown = function (on) {
			if (on === shown) { return; }
			shown = on;
			for (var i = 0; i < btns.length; i++) {
				btns[i].classList.toggle('is-visible', on);
				if (on) {
					btns[i].removeAttribute('tabindex');
					btns[i].removeAttribute('aria-hidden');
				} else {
					btns[i].setAttribute('tabindex', '-1');
					btns[i].setAttribute('aria-hidden', 'true');
				}
			}
		};
		var ticking = false;
		var check = function () {
			ticking = false;
			setShown((window.pageYOffset || doc.documentElement.scrollTop) > 100);
		};
		window.addEventListener('scroll', function () {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(check);
			}
		}, { passive: true });
		check();
	}
}());

/* Most-read counter: one count per article per browser session. */
(function () {
	var cfg = window.sokal || {};
	if (!cfg.view) { return; }
	var key = 'sokal_v_' + cfg.view;
	try {
		if (sessionStorage.getItem(key)) { return; }
		sessionStorage.setItem(key, '1');
	} catch (e) { /* storage blocked: still count */ }
	if (navigator.sendBeacon) {
		navigator.sendBeacon(cfg.view);
	} else if (window.fetch) {
		fetch(cfg.view, { method: 'POST', keepalive: true, credentials: 'omit' });
	}
}());

/* Hover prefetch for browsers without speculation rules. */
(function () {
	if (window.HTMLScriptElement && HTMLScriptElement.supports && HTMLScriptElement.supports('speculationrules')) { return; }
	var link = document.createElement('link');
	if (!(link.relList && link.relList.supports && link.relList.supports('prefetch'))) { return; }
	var done = {};
	function maybePrefetch(e) {
		var a = e.target.closest ? e.target.closest('a[href]') : null;
		if (!a || a.target === '_blank' || a.origin !== location.origin) { return; }
		var url = a.href.split('#')[0];
		if (done[url] || url === location.href.split('#')[0] || a.search || /\/wp-(admin|login|json)/.test(a.pathname)) { return; }
		done[url] = true;
		var l = document.createElement('link');
		l.rel = 'prefetch';
		l.href = url;
		document.head.appendChild(l);
	}
	document.addEventListener('mouseover', maybePrefetch, { passive: true });
	document.addEventListener('touchstart', maybePrefetch, { passive: true });
}());
