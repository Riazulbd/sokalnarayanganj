/* Phone header: ☰ full menu, search row, current category scrolled into view. */
(function () {
	'use strict';
	var head = document.querySelector('.sokal-mhead');
	if (!head) { return; }

	var menuBtn = head.querySelector('.sokal-mhead__menu');
	var searchBtn = head.querySelector('.sokal-mhead__search-btn');
	var drawer = document.getElementById('sokal-mdrawer');
	var search = document.getElementById('sokal-msearch');

	function setOpen(btn, panel, open) {
		if (!btn || !panel) { return; }
		if (open) { panel.removeAttribute('hidden'); } else { panel.setAttribute('hidden', ''); }
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}
	function isOpen(panel) { return panel && !panel.hasAttribute('hidden'); }

	if (menuBtn && drawer) {
		menuBtn.addEventListener('click', function () {
			var open = !isOpen(drawer);
			setOpen(searchBtn, search, false);
			setOpen(menuBtn, drawer, open);
		});
	}
	if (searchBtn && search) {
		searchBtn.addEventListener('click', function () {
			var open = !isOpen(search);
			setOpen(menuBtn, drawer, false);
			setOpen(searchBtn, search, open);
			if (open) {
				var input = search.querySelector('input');
				if (input) { input.focus(); }
			}
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			if (isOpen(drawer)) { setOpen(menuBtn, drawer, false); menuBtn.focus(); }
			if (isOpen(search)) { setOpen(searchBtn, search, false); searchBtn.focus(); }
		}
	});
	document.addEventListener('click', function (e) {
		if (isOpen(drawer) && !head.contains(e.target)) { setOpen(menuBtn, drawer, false); }
	});

	// Bring the current category into view in the swipeable menu.
	var nav = head.querySelector('.sokal-mhead__nav');
	var current = nav && nav.querySelector('.current-menu-item, .current-menu-ancestor, .current_page_item');
	if (nav && current && current !== nav.querySelector('li')) {
		nav.scrollLeft = Math.max(0, current.offsetLeft - 16);
	}
}());
