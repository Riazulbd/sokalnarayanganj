/* Header ticker pause button (include/ticker.php). Owner: header agent. */
(function () {
	'use strict';

	var tickers = document.querySelectorAll('.sokal-ticker');
	if (!tickers.length) {
		return;
	}

	var PAUSE = '⏸︎';
	var PLAY = '▶︎';

	Array.prototype.forEach.call(tickers, function (t) {
		var btn = t.querySelector('.sokal-ticker__pause');
		if (!btn) {
			return;
		}
		t.classList.add('is-ready');
		btn.addEventListener('click', function () {
			var paused = !t.classList.contains('is-paused');
			t.classList.toggle('is-paused', paused);
			btn.setAttribute('aria-pressed', paused ? 'true' : 'false');
			btn.textContent = paused ? PLAY : PAUSE;
		});
	});
})();
