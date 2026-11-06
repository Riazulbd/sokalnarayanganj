/*
 * Share picture builder (vanilla, no libraries, defer-safe).
 *
 * window.SokalSharePicture.render(canvas, opts) -> Promise<{ canvas, photo, logo }>
 * opts: { image, title, date, logo, siteName, style, bg, text, accent, bottomLine }
 * style: 'card' | 'panel' | 'classic' | 'dark' | 'light' | 'band' (1080x1080)
 *        'photostrip' | 'slogan' | 'strip' (1200x630)
 * extra opts: siteUrl (domain shown on the card), tagline (slogan band)
 */
(function () {
	'use strict';

	var FONT = 'SolaimanLipiNormal';
	var STACK = '"' + FONT + '", "Noto Sans Bengali", "Hind Siliguri", Arial, sans-serif';
	var STYLES = ['card', 'panel', 'photostrip', 'slogan', 'classic', 'dark', 'light', 'band', 'strip'];
	var WIDE = ['strip', 'photostrip', 'slogan'];
	var MSG = {
		working: 'ছবি তৈরি হচ্ছে…',
		noPhoto: 'মূল ছবিটি যোগ করা যায়নি, ছবি ছাড়াই তৈরি হয়েছে।',
		failed: 'ছবি তৈরি করা যায়নি। আবার চেষ্টা করুন।',
		shareFailed: 'শেয়ার করা যায়নি, ছবিটি ডাউনলোড করা হয়েছে।'
	};

	/* ---------- helpers ---------- */

	function hex(v, fallback) {
		v = String(v || '').trim();
		if (/^#[0-9a-f]{3}$/i.test(v)) {
			v = '#' + v[1] + v[1] + v[2] + v[2] + v[3] + v[3];
		}
		return /^#[0-9a-f]{6}$/i.test(v) ? v : fallback;
	}

	function contrast(h) {
		h = hex(h, '#000000').slice(1);
		var lum = 0;
		var w = [0.2126, 0.7152, 0.0722];
		for (var i = 0; i < 3; i++) {
			var c = parseInt(h.substr(i * 2, 2), 16) / 255;
			c = c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
			lum += w[i] * c;
		}
		return lum > 0.179 ? '#111111' : '#ffffff';
	}

	function font(size) {
		return 'normal ' + size + 'px ' + STACK;
	}

	function loadFont() {
		if (!document.fonts || !document.fonts.load) {
			return Promise.resolve();
		}
		var probe = 'বাংলা সংবাদ ১২৩';
		var timeout = new Promise(function (r) { setTimeout(r, 3000); });
		return Promise.race([
			Promise.all([
				document.fonts.load(font(48), probe),
				document.fonts.load(font(28), probe)
			]),
			timeout
		]).then(function () {}, function () {});
	}

	function loadImage(src) {
		return new Promise(function (resolve) {
			if (!src) {
				resolve(null);
				return;
			}
			var img = new Image();
			img.crossOrigin = 'anonymous';
			img.decoding = 'async';
			img.onload = function () { resolve(img.naturalWidth ? img : null); };
			img.onerror = function () { resolve(null); };
			img.src = src;
		});
	}

	// Draw img to cover the box (like CSS object-fit: cover).
	function cover(ctx, img, x, y, w, h) {
		var iw = img.naturalWidth;
		var ih = img.naturalHeight;
		var s = Math.max(w / iw, h / ih);
		var sw = w / s;
		var sh = h / s;
		ctx.drawImage(img, (iw - sw) / 2, (ih - sh) / 2, sw, sh, x, y, w, h);
	}

	// Fit img inside box, aligned to the given side ('left'|'right'|'center').
	function contain(ctx, img, x, y, w, h, align) {
		var s = Math.min(w / img.naturalWidth, h / img.naturalHeight);
		var dw = img.naturalWidth * s;
		var dh = img.naturalHeight * s;
		var dx = align === 'right' ? x + w - dw : align === 'center' ? x + (w - dw) / 2 : x;
		ctx.drawImage(img, dx, y + (h - dh) / 2, dw, dh);
	}

	// Break one over-long word into pieces that fit.
	function splitWord(ctx, word, maxW) {
		var parts = [];
		var cur = '';
		var chars = Array.from ? Array.from(word) : word.split('');
		for (var i = 0; i < chars.length; i++) {
			var next = cur + chars[i];
			if (cur && ctx.measureText(next).width > maxW) {
				parts.push(cur);
				cur = chars[i];
			} else {
				cur = next;
			}
		}
		if (cur) {
			parts.push(cur);
		}
		return parts;
	}

	// Wrap text by measuring space-separated words.
	function wrap(ctx, text, maxW) {
		var words = String(text || '').replace(/\s+/g, ' ').trim().split(' ');
		var lines = [];
		var line = '';
		for (var i = 0; i < words.length; i++) {
			var word = words[i];
			if (!word) {
				continue;
			}
			if (ctx.measureText(word).width > maxW) {
				var pieces = splitWord(ctx, word, maxW);
				word = pieces.pop();
				if (line) {
					lines.push(line);
					line = '';
				}
				lines = lines.concat(pieces);
			}
			var test = line ? line + ' ' + word : word;
			if (line && ctx.measureText(test).width > maxW) {
				lines.push(line);
				line = word;
			} else {
				line = test;
			}
		}
		if (line) {
			lines.push(line);
		}
		return lines;
	}

	// Pick the largest size (max..min) that fits maxLines; truncate with "…" if even min overflows.
	function fitText(ctx, text, maxW, maxH, max, min, maxLines, lh) {
		var size = max;
		var lines;
		for (; size >= min; size -= 2) {
			ctx.font = font(size);
			lines = wrap(ctx, text, maxW);
			if (lines.length <= maxLines && lines.length * size * lh <= maxH) {
				return { size: size, lines: lines };
			}
		}
		size = min;
		ctx.font = font(size);
		lines = wrap(ctx, text, maxW);
		var fit = Math.max(1, Math.min(maxLines, Math.floor(maxH / (size * lh))));
		if (lines.length > fit) {
			lines = lines.slice(0, fit);
			var last = lines[fit - 1];
			while (last && ctx.measureText(last + '…').width > maxW) {
				last = last.slice(0, -1);
			}
			lines[fit - 1] = last.replace(/\s+$/, '') + '…';
		}
		return { size: size, lines: lines };
	}

	function drawLines(ctx, block, x, y, lh, color, align) {
		ctx.font = font(block.size);
		ctx.fillStyle = color;
		ctx.textAlign = align || 'left';
		ctx.textBaseline = 'top';
		for (var i = 0; i < block.lines.length; i++) {
			ctx.fillText(block.lines[i], x, y + i * block.size * lh);
		}
		return block.lines.length * block.size * lh;
	}

	function singleLine(ctx, text, x, y, size, color, align, maxW) {
		ctx.font = font(size);
		var t = String(text || '');
		if (maxW) {
			while (t && ctx.measureText(t).width > maxW) {
				t = t.slice(0, -1);
			}
		}
		ctx.fillStyle = color;
		ctx.textAlign = align || 'left';
		ctx.textBaseline = 'middle';
		ctx.fillText(t, x, y);
	}

	// Logo (or site name if no logo) in a box.
	function brand(ctx, logo, name, x, y, w, h, color, align) {
		if (logo) {
			contain(ctx, logo, x, y, w, h, align);
			return;
		}
		if (name) {
			var tx = align === 'right' ? x + w : align === 'center' ? x + w / 2 : x;
			singleLine(ctx, name, tx, y + h / 2, Math.min(40, Math.round(h * 0.6)), color, align, w);
		}
	}

	// Placeholder when the photo is missing: soft accent gradient.
	function noPhoto(ctx, x, y, w, h, accent, bg) {
		var g = ctx.createLinearGradient(x, y, x + w, y + h);
		g.addColorStop(0, accent);
		g.addColorStop(1, bg);
		ctx.fillStyle = g;
		ctx.fillRect(x, y, w, h);
	}

	function footer(ctx, W, y, h, bg, text) {
		ctx.fillStyle = bg;
		ctx.fillRect(0, y, W, h);
		if (text) {
			singleLine(ctx, text, W / 2, y + h / 2, Math.round(h * 0.42), contrast(bg), 'center', W - 80);
		}
	}

	/* ---------- layouts ---------- */

	// classic / dark / light: 1080x1080, photo top, panel with headline + date/logo, footer band.
	function drawSquare(ctx, o, photo, logo) {
		var W = 1080, H = 1080, P = 60;
		var photoH = 580, rule = 10, footH = 90;
		var panelY = photoH + rule;
		var metaH = 80;

		ctx.fillStyle = o.bg;
		ctx.fillRect(0, 0, W, H);
		if (photo) {
			cover(ctx, photo, 0, 0, W, photoH);
		} else {
			noPhoto(ctx, 0, 0, W, photoH, o.accent, o.bg);
		}
		ctx.fillStyle = o.accent;
		ctx.fillRect(0, photoH, W, rule);

		var textTop = panelY + 34;
		var textH = H - footH - metaH - textTop - 10;
		var block = fitText(ctx, o.title, W - P * 2, textH, 62, 38, 4, 1.38);
		var used = block.lines.length * block.size * 1.38;
		drawLines(ctx, block, P, textTop + Math.max(0, (textH - used) / 2), 1.38, o.text);

		var metaY = H - footH - metaH;
		ctx.fillStyle = o.accent;
		ctx.fillRect(P, metaY + metaH / 2 - 18, 6, 36);
		singleLine(ctx, o.date, P + 20, metaY + metaH / 2, 28, o.muted, 'left', W / 2);
		brand(ctx, logo, o.siteName, W / 2, metaY + 12, W / 2 - P, metaH - 24, o.text, 'right');

		footer(ctx, W, H - footH, footH, o.accent, o.bottomLine);
	}

	// band: 1080x1080, photo top, accent band behind headline.
	function drawBand(ctx, o, photo, logo) {
		var W = 1080, H = 1080, P = 60;
		var photoH = 540, footH = 90, metaH = 90;
		var bandY = photoH, bandH = H - photoH - footH - metaH;

		ctx.fillStyle = o.bg;
		ctx.fillRect(0, 0, W, H);
		if (photo) {
			cover(ctx, photo, 0, 0, W, photoH);
		} else {
			noPhoto(ctx, 0, 0, W, photoH, o.accent, o.bg);
		}

		ctx.fillStyle = o.accent;
		ctx.fillRect(0, bandY, W, bandH);
		var block = fitText(ctx, o.title, W - P * 2, bandH - 60, 62, 38, 4, 1.38);
		var used = block.lines.length * block.size * 1.38;
		drawLines(ctx, block, P, bandY + (bandH - used) / 2, 1.38, contrast(o.accent));

		var metaY = bandY + bandH;
		singleLine(ctx, o.date, P, metaY + metaH / 2, 28, o.muted, 'left', W / 2);
		brand(ctx, logo, o.siteName, W / 2, metaY + 14, W / 2 - P, metaH - 28, o.text, 'right');

		footer(ctx, W, H - footH, footH, o.text, o.bottomLine);
	}

	// strip: 1200x630, photo on the left half, text on the right.
	function drawStrip(ctx, o, photo, logo) {
		var W = 1200, H = 630, footH = 64, P = 44;
		var photoW = 560, rule = 8;
		var bodyH = H - footH;
		var tx = photoW + rule + P;
		var tw = W - tx - P;

		ctx.fillStyle = o.bg;
		ctx.fillRect(0, 0, W, H);
		if (photo) {
			cover(ctx, photo, 0, 0, photoW, bodyH);
		} else {
			noPhoto(ctx, 0, 0, photoW, bodyH, o.accent, o.bg);
		}
		ctx.fillStyle = o.accent;
		ctx.fillRect(photoW, 0, rule, bodyH);

		var logoH = 64;
		brand(ctx, logo, o.siteName, tx, P - 6, tw, logoH, o.text, 'left');

		var dateH = 50;
		var textTop = P + logoH + 20;
		var textH = bodyH - textTop - dateH - 20;
		var block = fitText(ctx, o.title, tw, textH, 50, 30, 5, 1.38);
		var used = block.lines.length * block.size * 1.38;
		drawLines(ctx, block, tx, textTop + Math.max(0, (textH - used) / 2), 1.38, o.text);

		ctx.fillStyle = o.accent;
		ctx.fillRect(tx, bodyH - dateH - 6, 6, 30);
		singleLine(ctx, o.date, tx + 18, bodyH - dateH + 9, 24, o.muted, 'left', tw - 18);

		footer(ctx, W, bodyH, footH, o.accent, o.bottomLine);
	}


	/* ---------- photo-card layouts ---------- */

	function boldFont(size) {
		return 'bold ' + size + 'px ' + STACK;
	}

	// Mix a hex colour with white (amt 0..1 = how much white).
	function tint(h, amt) {
		var n = parseInt(h.slice(1), 16);
		var r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
		r = Math.round(r + (255 - r) * amt);
		g = Math.round(g + (255 - g) * amt);
		b = Math.round(b + (255 - b) * amt);
		return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
	}

	// Darken a hex colour (amt 0..1).
	function shade(h, amt) {
		var n = parseInt(h.slice(1), 16);
		var r = Math.round(((n >> 16) & 255) * (1 - amt));
		var g = Math.round(((n >> 8) & 255) * (1 - amt));
		var b = Math.round((n & 255) * (1 - amt));
		return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
	}

	function roundRect(ctx, x, y, w, h, r) {
		ctx.beginPath();
		ctx.moveTo(x + r, y);
		ctx.lineTo(x + w - r, y);
		ctx.quadraticCurveTo(x + w, y, x + w, y + r);
		ctx.lineTo(x + w, y + h - r);
		ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
		ctx.lineTo(x + r, y + h);
		ctx.quadraticCurveTo(x, y + h, x, y + h - r);
		ctx.lineTo(x, y + r);
		ctx.quadraticCurveTo(x, y, x + r, y);
		ctx.closePath();
	}

	// Bold headline block, centred, largest size that fits.
	function headline(ctx, text, cx, top, maxW, maxH, max, min, lines, color) {
		var lh = 1.4, size = max, wrapped;
		for (; size >= min; size -= 2) {
			ctx.font = boldFont(size);
			wrapped = wrap(ctx, text, maxW);
			if (wrapped.length <= lines && wrapped.length * size * lh <= maxH) {
				break;
			}
		}
		if (size < min) {
			size = min;
			ctx.font = boldFont(size);
			wrapped = wrap(ctx, text, maxW).slice(0, lines);
		}
		var used = wrapped.length * size * lh;
		var y = top + Math.max(0, (maxH - used) / 2);
		ctx.font = boldFont(size);
		ctx.fillStyle = color;
		ctx.textAlign = 'center';
		ctx.textBaseline = 'top';
		for (var i = 0; i < wrapped.length; i++) {
			ctx.fillText(wrapped[i], cx, y + i * size * lh);
		}
	}

	// Logo recoloured white (for dark backgrounds). Works when the logo has transparency;
	// otherwise the logo is drawn on a white rounded plate so it stays readable.
	function whiteLogo(ctx, logo, name, x, y, w, h, align) {
		if (!logo) {
			brand(ctx, null, name, x, y, w, h, '#ffffff', align);
			return;
		}
		var s = Math.min(w / logo.naturalWidth, h / logo.naturalHeight);
		var dw = Math.round(logo.naturalWidth * s), dh = Math.round(logo.naturalHeight * s);
		var dx = align === 'right' ? x + w - dw : align === 'center' ? x + (w - dw) / 2 : x;
		var dy = y + (h - dh) / 2;
		var off = document.createElement('canvas');
		off.width = Math.max(1, dw);
		off.height = Math.max(1, dh);
		var oc = off.getContext('2d');
		oc.drawImage(logo, 0, 0, dw, dh);
		var transparent = false;
		try {
			var px = oc.getImageData(0, 0, 1, 1).data;
			transparent = px[3] < 250;
		} catch (e) { transparent = false; }
		if (transparent) {
			oc.globalCompositeOperation = 'source-in';
			oc.fillStyle = '#ffffff';
			oc.fillRect(0, 0, dw, dh);
			ctx.drawImage(off, dx, dy);
		} else {
			ctx.fillStyle = '#ffffff';
			roundRect(ctx, dx - 14, dy - 10, dw + 28, dh + 20, 12);
			ctx.fill();
			ctx.drawImage(logo, dx, dy, dw, dh);
		}
	}

	function globe(ctx, cx, cy, r, color) {
		ctx.save();
		ctx.strokeStyle = color;
		ctx.lineWidth = 3;
		ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.stroke();
		ctx.beginPath(); ctx.ellipse(cx, cy, r * 0.45, r, 0, 0, Math.PI * 2); ctx.stroke();
		ctx.beginPath(); ctx.moveTo(cx - r, cy); ctx.lineTo(cx + r, cy); ctx.stroke();
		ctx.restore();
	}

	// card: soft tinted background, logo top-right, framed rounded photo, centred bold
	// headline, bottom row = date · "বিস্তারিত কমেন্টে" pill · site address.
	function drawCard(ctx, o, photo, logo) {
		var W = 1080, H = 1080, P = 56;
		var g = ctx.createLinearGradient(0, 0, W, H);
		g.addColorStop(0, tint(o.accent2, 0.9));
		g.addColorStop(1, tint(o.accent2, 0.78));
		ctx.fillStyle = g;
		ctx.fillRect(0, 0, W, H);

		brand(ctx, logo, o.siteName, W - P - 300, 30, 300, 74, o.text, 'right');

		var fx = P, fy = 124, fw = W - P * 2, fh = 560, r = 30, bw = 10;
		ctx.save();
		ctx.shadowColor = 'rgba(0,0,0,.18)';
		ctx.shadowBlur = 24;
		ctx.shadowOffsetY = 8;
		ctx.fillStyle = '#ffffff';
		roundRect(ctx, fx, fy, fw, fh, r);
		ctx.fill();
		ctx.restore();
		ctx.save();
		roundRect(ctx, fx + bw, fy + bw, fw - bw * 2, fh - bw * 2, r - 8);
		ctx.clip();
		if (photo) {
			cover(ctx, photo, fx + bw, fy + bw, fw - bw * 2, fh - bw * 2);
		} else {
			noPhoto(ctx, fx + bw, fy + bw, fw - bw * 2, fh - bw * 2, o.accent, '#ffffff');
		}
		ctx.restore();

		headline(ctx, o.title, W / 2, fy + fh + 30, W - P * 2 - 40, 230, 62, 36, 3, o.text);

		var by = H - 74;
		ctx.fillStyle = 'rgba(255,255,255,.75)';
		ctx.fillRect(0, by - 18, W, 92);
		singleLine(ctx, o.date, P, by + 28, 28, o.text, 'left', 280);
		if (o.bottomLine) {
			ctx.font = boldFont(30);
			var pw = Math.min(420, ctx.measureText(o.bottomLine).width + 56), ph = 56;
			ctx.fillStyle = o.accent;
			roundRect(ctx, (W - pw) / 2, by, pw, ph, 14);
			ctx.fill();
			ctx.strokeStyle = '#ffffff';
			ctx.lineWidth = 3;
			ctx.stroke();
			ctx.font = boldFont(30);
			ctx.fillStyle = contrast(o.accent);
			ctx.textAlign = 'center';
			ctx.textBaseline = 'middle';
			ctx.fillText(o.bottomLine, W / 2, by + ph / 2 + 2);
		}
		if (o.siteUrl) {
			ctx.font = boldFont(26);
			var uw = Math.min(300, ctx.measureText(o.siteUrl).width);
			globe(ctx, W - P - uw - 26, by + 28, 17, o.text);
			singleLine(ctx, o.siteUrl, W - P, by + 28, 26, o.text, 'right', 300);
		}
	}

	// panel: photo on top with the logo over it, deep accent panel with a big white
	// headline, date bottom-left and site address bottom-right.
	function drawPanel(ctx, o, photo, logo) {
		var W = 1080, H = 1080, P = 56, photoH = 660;
		var deep = shade(o.accent, 0.35);
		ctx.fillStyle = deep;
		ctx.fillRect(0, 0, W, H);
		if (photo) {
			cover(ctx, photo, 0, 0, W, photoH);
		} else {
			noPhoto(ctx, 0, 0, W, photoH, o.accent, deep);
		}
		var fade = ctx.createLinearGradient(0, 0, 0, 160);
		fade.addColorStop(0, 'rgba(0,0,0,.45)');
		fade.addColorStop(1, 'rgba(0,0,0,0)');
		ctx.fillStyle = fade;
		ctx.fillRect(0, 0, W, 160);
		whiteLogo(ctx, logo, o.siteName, P, 34, 300, 84, 'left');

		var pg = ctx.createLinearGradient(0, photoH, 0, H);
		pg.addColorStop(0, shade(o.accent, 0.2));
		pg.addColorStop(1, shade(o.accent, 0.45));
		ctx.fillStyle = pg;
		ctx.fillRect(0, photoH, W, H - photoH);

		headline(ctx, o.title, W / 2, photoH + 36, W - P * 2, 250, 66, 38, 3, '#ffffff');

		ctx.fillStyle = 'rgba(0,0,0,.25)';
		ctx.fillRect(0, H - 74, W, 74);
		singleLine(ctx, o.date, P, H - 37, 28, '#ffffff', 'left', W / 2);
		singleLine(ctx, o.siteUrl, W - P, H - 37, 28, '#ffffff', 'right', W / 2 - P);
	}

	// photostrip: wide photo with a white strip at the bottom — accent stripes on the
	// left, logo on the right (no headline).
	function drawPhotoStrip(ctx, o, photo, logo) {
		var W = 1200, H = 630, stripH = 100, photoH = H - stripH;
		if (photo) {
			cover(ctx, photo, 0, 0, W, photoH);
		} else {
			noPhoto(ctx, 0, 0, W, photoH, o.accent, '#ffffff');
		}
		ctx.fillStyle = '#f7f7f7';
		ctx.fillRect(0, photoH, W, stripH);

		// diagonal accent stripes in a block, plus a hatched circle
		ctx.save();
		ctx.beginPath();
		ctx.rect(0, photoH, 130, stripH);
		ctx.clip();
		ctx.strokeStyle = o.accent;
		ctx.lineWidth = 5;
		for (var i = -stripH; i < 130 + stripH; i += 16) {
			ctx.beginPath(); ctx.moveTo(i, photoH + stripH); ctx.lineTo(i + stripH, photoH); ctx.stroke();
		}
		ctx.restore();
		ctx.save();
		ctx.beginPath();
		ctx.arc(232, photoH + stripH - 6, 38, 0, Math.PI * 2);
		ctx.clip();
		ctx.strokeStyle = tint(o.accent, 0.45);
		ctx.lineWidth = 4;
		for (var j = 150; j < 320; j += 11) {
			ctx.beginPath(); ctx.moveTo(j, photoH + stripH + 40); ctx.lineTo(j + 80, photoH + stripH - 60); ctx.stroke();
		}
		ctx.restore();

		brand(ctx, logo, o.siteName, W - 470, photoH + 16, 440, stripH - 32, '#222222', 'right');
	}

	// slogan: wide photo with an accent band — the site's slogan on the left, logo
	// (recoloured white when possible) on the right.
	function drawSlogan(ctx, o, photo, logo) {
		var W = 1200, H = 630, bandH = 110, photoH = H - bandH;
		if (photo) {
			cover(ctx, photo, 0, 0, W, photoH);
		} else {
			noPhoto(ctx, 0, 0, W, photoH, o.accent, '#ffffff');
		}
		var bg = ctx.createLinearGradient(0, photoH, W, H);
		bg.addColorStop(0, shade(o.accent, 0.15));
		bg.addColorStop(1, shade(o.accent, 0.35));
		ctx.fillStyle = bg;
		ctx.fillRect(0, photoH, W, bandH);
		ctx.fillStyle = 'rgba(0,0,0,.15)';
		ctx.fillRect(0, photoH, W, 4);
		var slogan = o.tagline || o.bottomLine;
		if (slogan) {
			ctx.font = 'italic ' + 44 + 'px ' + STACK;
			ctx.fillStyle = '#ffffff';
			ctx.textAlign = 'left';
			ctx.textBaseline = 'middle';
			var t = slogan;
			while (t && ctx.measureText(t).width > 640) { t = t.slice(0, -1); }
			ctx.fillText(t, 60, photoH + bandH / 2 + 2);
		}
		whiteLogo(ctx, logo, o.siteName, W - 420, photoH + 18, 370, bandH - 36, 'right');
	}

	/* ---------- public API ---------- */

	function normalize(opts) {
		opts = opts || {};
		var style = STYLES.indexOf(opts.style) > -1 ? opts.style : 'card';
		var accent = hex(opts.accent, '#c4161c');
		var bg = hex(opts.bg, '#ffffff');
		var text = hex(opts.text, '#111111');
		if (style === 'dark') {
			bg = '#141414';
			text = '#f2f2f2';
		} else if (style === 'light') {
			bg = '#f6f4ef';
			text = '#1a1a1a';
		}
		var muted = contrast(bg) === '#111111' ? '#555555' : '#bbbbbb';
		return {
			style: style,
			image: opts.image || '',
			logo: opts.logo || '',
			title: String(opts.title || ''),
			date: String(opts.date || ''),
			siteName: String(opts.siteName || ''),
			bottomLine: String(opts.bottomLine == null ? 'বিস্তারিত কমেন্টে' : opts.bottomLine),
			bg: bg,
			text: text,
			accent: accent,
			accent2: /^#(?:[0-9a-f]{6})$/i.test(accent) ? accent : '#c4161c',
			muted: muted,
			siteUrl: String(opts.siteUrl || (window.location && window.location.hostname) || '').replace(/^www\./, 'www.'),
			tagline: String(opts.tagline || '')
		};
	}

	function render(canvas, opts) {
		var o = normalize(opts);
		var strip = WIDE.indexOf(o.style) > -1;
		canvas.width = strip ? 1200 : 1080;
		canvas.height = strip ? 630 : 1080;
		var ctx = canvas.getContext('2d');

		return Promise.all([loadFont(), loadImage(o.image), loadImage(o.logo)]).then(function (r) {
			var photo = r[1];
			var logo = r[2];
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			if (o.style === 'card') {
				drawCard(ctx, o, photo, logo);
			} else if (o.style === 'panel') {
				drawPanel(ctx, o, photo, logo);
			} else if (o.style === 'photostrip') {
				drawPhotoStrip(ctx, o, photo, logo);
			} else if (o.style === 'slogan') {
				drawSlogan(ctx, o, photo, logo);
			} else if (o.style === 'strip') {
				drawStrip(ctx, o, photo, logo);
			} else if (o.style === 'band') {
				drawBand(ctx, o, photo, logo);
			} else {
				drawSquare(ctx, o, photo, logo);
			}
			return {
				canvas: canvas,
				photo: !!photo || !o.image,
				logo: !!logo || !o.logo
			};
		});
	}

	window.SokalSharePicture = {
		render: render,
		styles: STYLES.slice(),
		messages: MSG
	};

	/* ---------- front-end buttons ---------- */

	function toBlob(canvas) {
		return new Promise(function (resolve, reject) {
			if (canvas.toBlob) {
				canvas.toBlob(function (b) {
					if (b) {
						resolve(b);
					} else {
						reject(new Error('blob'));
					}
				}, 'image/jpeg', 0.92);
				return;
			}
			try {
				var bin = atob(canvas.toDataURL('image/jpeg', 0.92).split(',')[1]);
				var arr = new Uint8Array(bin.length);
				for (var i = 0; i < bin.length; i++) {
					arr[i] = bin.charCodeAt(i);
				}
				resolve(new Blob([arr], { type: 'image/jpeg' }));
			} catch (e) {
				reject(e);
			}
		});
	}

	function optsFrom(el) {
		var d = el.dataset;
		return {
			image: d.image,
			title: d.title,
			date: d.date,
			logo: d.logo,
			siteName: d.siteName,
			style: d.style,
			bg: d.bg,
			text: d.text,
			accent: d.accent,
			bottomLine: d.bottomLine,
			siteUrl: d.siteUrl,
			tagline: d.tagline
		};
	}

	function status(el, msg) {
		var s = el.querySelector('.sokal-sp__status');
		if (s) {
			s.textContent = msg || '';
			s.classList.toggle('is-visible', !!msg);
		}
	}

	// Build once per wrapper; cache the result so sharing stays inside the user gesture.
	function build(el) {
		if (!el._sokalSp) {
			var canvas = document.createElement('canvas');
			el._sokalSp = render(canvas, optsFrom(el)).then(function (res) {
				return toBlob(canvas).then(function (blob) {
					return { blob: blob, photo: res.photo };
				});
			});
			el._sokalSp.then(null, function () { el._sokalSp = null; });
		}
		return el._sokalSp;
	}

	function fileName(el) {
		return (el.getAttribute('data-slug') || 'news') + '.jpg';
	}

	function download(blob, name) {
		var url = URL.createObjectURL(blob);
		var a = document.createElement('a');
		a.href = url;
		a.download = name;
		a.rel = 'noopener';
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		setTimeout(function () { URL.revokeObjectURL(url); }, 4000);
	}

	function canShareFiles() {
		if (!navigator.canShare || !navigator.share || typeof File === 'undefined') {
			return false;
		}
		try {
			return navigator.canShare({ files: [new File([''], 'x.jpg', { type: 'image/jpeg' })] });
		} catch (e) {
			return false;
		}
	}

	function onClick(el, action) {
		var ready = !!el._sokalSpDone;
		if (!ready) {
			status(el, MSG.working);
		}
		build(el).then(function (res) {
			el._sokalSpDone = true;
			status(el, res.photo ? '' : MSG.noPhoto);
			var name = fileName(el);
			if (action === 'share') {
				var file = new File([res.blob], name, { type: 'image/jpeg' });
				var title = el.getAttribute('data-title') || '';
				return navigator.share({ files: [file], title: title, text: title }).then(null, function (err) {
					if (err && err.name === 'AbortError') {
						return;
					}
					download(res.blob, name);
					status(el, MSG.shareFailed);
				});
			}
			download(res.blob, name);
		}).then(null, function () {
			status(el, MSG.failed);
		});
	}

	function init() {
		var els = document.querySelectorAll('.sokal-sp');
		if (!els.length) {
			return;
		}
		var share = canShareFiles();
		var io = share && 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) {
					io.unobserve(en.target);
					build(en.target).then(function () { en.target._sokalSpDone = true; }, function () {});
				}
			});
		}, { rootMargin: '200px' }) : null;

		Array.prototype.forEach.call(els, function (el) {
			var shareBtn = el.querySelector('[data-sokal-sp="share"]');
			if (shareBtn && share) {
				shareBtn.hidden = false;
				// Pre-build near the viewport so navigator.share() runs within the tap's user activation.
				if (io) {
					io.observe(el);
				}
			}
			el.addEventListener('click', function (e) {
				var btn = e.target.closest ? e.target.closest('[data-sokal-sp]') : null;
				if (!btn || btn.disabled) {
					return;
				}
				e.preventDefault();
				onClick(el, btn.getAttribute('data-sokal-sp'));
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
