/* Live "today" line: Bengali calendar date | English date | time, in Bengali,
   Bangladesh time (Asia/Dhaka, UTC+6). Pages are edge-cached, so the browser
   keeps it current. Server fallback: sokal_today_html() in include/helpers.php. */
(function () {
	'use strict';
	var nodes = document.querySelectorAll('[data-sokal-today]');
	if (!nodes.length) { return; }

	var DIG = '০১২৩৪৫৬৭৮৯';
	var DAYS = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];
	var GM = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
	var BM = ['বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র'];

	function bn(v) {
		return String(v).replace(/\d/g, function (d) { return DIG.charAt(+d); });
	}

	// Current wall-clock time in Dhaka (no DST), as UTC fields.
	function dhaka() {
		return new Date(Date.now() + 6 * 3600000);
	}

	// Bangladesh revised calendar (same rules as sokal_bangla_date()).
	function bangla(y, m, d) {
		var startYear = (m > 3 || (m === 3 && d >= 14)) ? y : y - 1;
		var off = Math.round((Date.UTC(y, m, d) - Date.UTC(startYear, 3, 14)) / 86400000);
		var feb = startYear + 1;
		var leap = (feb % 4 === 0 && feb % 100 !== 0) || feb % 400 === 0;
		var len = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, leap ? 30 : 29, 30];
		var i = 0;
		while (i < 11 && off >= len[i]) { off -= len[i]; i++; }
		return { d: off + 1, m: BM[i], y: startYear - 593 };
	}

	function set(el, sel, text) {
		var t = el.querySelector(sel);
		if (t && t.textContent !== text) { t.textContent = text; }
	}

	function tick() {
		var n = dhaka();
		var y = n.getUTCFullYear(), m = n.getUTCMonth(), d = n.getUTCDate();
		var b = bangla(y, m, d);
		var h = n.getUTCHours(), min = n.getUTCMinutes();
		var bnLine = 'আজ ' + DAYS[n.getUTCDay()] + ', ' + bn(b.d) + ' ' + b.m + ', ' + bn(b.y);
		var enLine = bn(d) + ' ' + GM[m] + ', ' + bn(y);
		var time = bn(h % 12 || 12) + ':' + bn(min < 10 ? '0' + min : min) + ' ' + (h < 12 ? 'পূর্বাহ্ন' : 'অপরাহ্ন');
		for (var i = 0; i < nodes.length; i++) {
			set(nodes[i], '.sokal-today__bn', bnLine);
			set(nodes[i], '.sokal-today__en', enLine);
			set(nodes[i], '.sokal-today__time', time);
		}
	}

	tick();
	setInterval(tick, 20000);
}());
