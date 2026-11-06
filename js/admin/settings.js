/**
 * Theme Settings screen: colour pickers, media pickers and the share picture preview.
 * Admin only (jQuery is fine here). Owner: settings agent.
 */
(function ($) {
	'use strict';

	var cfg = window.sokalSettings || { preview: {}, i18n: {} };
	var i18n = cfg.i18n || {};
	var timer = null;

	/* Share picture live preview */
	function val(id) {
		var el = document.getElementById(id);
		return el ? el.value : '';
	}

	function showUnavailable(msg) {
		var box = document.querySelector('.sokal-share-unavailable');
		if (box) {
			box.querySelector('p').textContent = msg;
			box.hidden = false;
		}
	}

	function renderPreview() {
		var canvas = document.getElementById('sokal-share-canvas');
		if (!canvas) {
			return;
		}
		var api = window.SokalSharePicture;
		if (!api || typeof api.render !== 'function') {
			showUnavailable(i18n.unavailable || 'Preview unavailable.');
			return;
		}
		var p = cfg.preview || {};
		var opts = {
			image: p.image || '',
			title: p.title || '',
			date: p.date || '',
			logo: p.logo || '',
			siteName: p.siteName || '',
			style: val('sokal-share_style') || 'classic',
			bg: val('sokal-share_bg'),
			text: val('sokal-share_text'),
			accent: val('sokal-share_accent'),
			bottomLine: val('sokal-share_bottom_line')
		};
		try {
			var out = api.render(canvas, opts);
			if (out && typeof out.then === 'function') {
				out.then(function () {
					var box = document.querySelector('.sokal-share-unavailable');
					if (box) {
						box.hidden = true;
					}
				}, function () {
					showUnavailable(i18n.failed || 'Preview could not be drawn.');
				});
			}
		} catch (e) {
			showUnavailable(i18n.failed || 'Preview could not be drawn.');
		}
	}

	function schedulePreview() {
		if (!document.getElementById('sokal-share-canvas')) {
			return;
		}
		clearTimeout(timer);
		timer = setTimeout(renderPreview, 200);
	}

	$(function () {
		/* Colour pickers */
		if ($.fn.wpColorPicker) {
			$('.sokal-color').each(function () {
				var $input = $(this);
				$input.wpColorPicker({
					change: function (event, ui) {
						$input.val(ui.color.toString());
						schedulePreview();
					},
					clear: function () {
						$input.val('');
						schedulePreview();
					}
				});
			});
		}

		/* Media pickers: store URL + attachment id */
		$(document).on('click', '.sokal-image-pick', function (e) {
			e.preventDefault();
			if (!window.wp || !wp.media) {
				return;
			}
			var $wrap = $(this).closest('.sokal-image');
			var frame = wp.media({
				title: i18n.choose || 'Choose image',
				button: { text: i18n.use || 'Use this image' },
				library: { type: 'image' },
				multiple: false
			});
			frame.on('select', function () {
				var a = frame.state().get('selection').first().toJSON();
				$wrap.find('.sokal-image-url').val(a.url).trigger('change'); // redraws the preview, resets the id
				$wrap.find('.sokal-image-id').val(a.id);
			});
			frame.open();
		});

		$(document).on('click', '.sokal-image-remove', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('.sokal-image');
			$wrap.find('.sokal-image-url, .sokal-image-id').val('');
			$wrap.find('.sokal-image-preview').empty();
		});

		$(document).on('change', '.sokal-image-url', function () {
			var $wrap = $(this).closest('.sokal-image');
			var url = $(this).val();
			$wrap.find('.sokal-image-id').val(''); // typed URL: no attachment id
			$wrap.find('.sokal-image-preview').empty();
			if (url) {
				$wrap.find('.sokal-image-preview').append($('<img alt="">').attr('src', url));
			}
		});

		/* Share preview inputs */
		$('#sokal-share_style, #sokal-share_bottom_line').on('input change', schedulePreview);
		if (document.getElementById('sokal-share-canvas')) {
			// share-picture.js may load after this file; give it a moment.
			if (window.SokalSharePicture) {
				renderPreview();
			} else {
				$(window).on('load', renderPreview);
			}
		}
	});
}(jQuery));
