/* Live header date/time (header.php .sokal-clock). Owner: header agent. */
(function () {
	'use strict';

	var clocks = document.querySelectorAll('.sokal-clock');
	if (!clocks.length || typeof Intl === 'undefined' || !Intl.DateTimeFormat) {
		return;
	}

	var TZ = 'Asia/Dhaka';
	var fmt = {};

	function getFormatter(lang) {
		if (fmt[lang]) {
			return fmt[lang];
		}
		var locale = lang === 'bn' ? 'bn-BD' : 'en-US';
		try {
			fmt[lang] = {
				date: new Intl.DateTimeFormat(locale, {
					timeZone: TZ, weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
				}),
				time: new Intl.DateTimeFormat(locale, {
					timeZone: TZ, hour: 'numeric', minute: '2-digit', hour12: true
				}),
				hour: new Intl.DateTimeFormat('en-US', {
					timeZone: TZ, hour: 'numeric', hourCycle: 'h23'
				})
			};
		} catch (e) {
			fmt[lang] = null;
		}
		return fmt[lang];
	}

	function timeText(f, lang, now) {
		if (lang !== 'bn' || !f.time.formatToParts) {
			return f.time.format(now);
		}
		// Bengali: use the same day-period words as the server (bn1_date).
		var out = '';
		f.time.formatToParts(now).forEach(function (p) {
			if (p.type !== 'dayPeriod') {
				out += p.value;
			}
		});
		var h = parseInt(f.hour.format(now), 10) % 24;
		return out.trim() + ' ' + (h < 12 ? 'পূর্বাহ্ন' : 'অপরাহ্ন');
	}

	function tick() {
		var now = new Date();
		for (var i = 0; i < clocks.length; i++) {
			var el = clocks[i];
			var lang = el.getAttribute('data-lang') === 'bn' ? 'bn' : 'en';
			var f = getFormatter(lang);
			if (!f) {
				continue;
			}
			var text = f.date.format(now) + ', ' + timeText(f, lang, now);
			if (el.textContent !== text) {
				el.textContent = text;
			}
		}
	}

	tick();
	setInterval(tick, 30000);
})();
