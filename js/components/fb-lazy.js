/* Facebook page box: load the SDK only when the box is within 300px of the viewport. */
(function () {
	'use strict';
	var SRC = 'https://connect.facebook.net/bn_IN/sdk.js#xfbml=1&version=v19.0';
	var boxes = document.querySelectorAll('.fb-page');
	if (!boxes.length) {
		return;
	}
	var loaded = false;

	function load() {
		if (loaded || document.getElementById('facebook-jssdk')) {
			loaded = true;
			return;
		}
		loaded = true;
		if (!document.getElementById('fb-root')) {
			var root = document.createElement('div');
			root.id = 'fb-root';
			document.body.insertBefore(root, document.body.firstChild);
		}
		var s = document.createElement('script');
		s.id = 'facebook-jssdk';
		s.src = SRC;
		s.async = true;
		s.defer = true;
		s.crossOrigin = 'anonymous';
		document.body.appendChild(s);
	}

	if (!('IntersectionObserver' in window)) {
		window.addEventListener('load', load);
		return;
	}

	var io = new IntersectionObserver(function (entries) {
		for (var i = 0; i < entries.length; i++) {
			if (entries[i].isIntersecting) {
				io.disconnect();
				load();
				return;
			}
		}
	}, { rootMargin: '300px 0px' });

	for (var j = 0; j < boxes.length; j++) {
		io.observe(boxes[j]);
	}
})();
