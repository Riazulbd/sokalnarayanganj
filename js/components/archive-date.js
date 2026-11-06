/*
 * Date archive form: native <input type="date"> (YYYY-MM-DD) submitted as the
 * same query the old jQuery UI datepicker sent: ?m=YYYYMMDD.
 */
(function () {
	'use strict';
	var forms = document.querySelectorAll('form.sokal-archive-date');
	if (!forms.length) {
		return;
	}

	function pad(n) {
		return (n < 10 ? '0' : '') + n;
	}
	var d = new Date();
	var today = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());

	for (var i = 0; i < forms.length; i++) {
		var input = forms[i].querySelector('input[type="date"]');
		if (!input) {
			continue;
		}
		input.max = today;

		forms[i].addEventListener('submit', function (e) {
			var form = e.currentTarget;
			var field = form.querySelector('input[type="date"]');
			var name = field.getAttribute('data-name') || field.name || 'm';
			var digits = String(field.value || '').replace(/[^0-9]/g, '');
			if (digits.length !== 8) {
				e.preventDefault();
				field.focus();
				return;
			}
			var hidden = form.querySelector('input[type="hidden"][name="' + name + '"]');
			if (!hidden) {
				hidden = document.createElement('input');
				hidden.type = 'hidden';
				hidden.name = name;
				form.appendChild(hidden);
			}
			hidden.value = digits;
			// Keep the original field name for the next submit (e.g. after Back), but don't send YYYY-MM-DD.
			field.setAttribute('data-name', name);
			field.removeAttribute('name');
		});
	}
})();
