/**
 * Color Swatches widget: show the product's swatches wherever the widget is placed.
 *
 * The row inside WooCommerce's variation form is the one that really works: it picks the variation,
 * switches the gallery and keeps Add to cart in step. This shows a copy of that row in the widget and
 * passes clicks to the original, so there is still only one thing deciding what is selected.
 *
 * Two rules keep it safe:
 * - the original row is hidden only once a copy is actually on screen, so a product this cannot read
 *   still shows its swatches in the form;
 * - the copy is rebuilt whenever the form's row changes (the theme redraws it when stock changes).
 */
(function () {
	'use strict';

	function forms() {
		return document.querySelectorAll('form.variations_form');
	}

	/**
	 * The swatch row for an attribute: the named one, or the first row of the product.
	 */
	function findWrap(attribute) {
		var all = [];
		forms().forEach(function (f) {
			f.querySelectorAll('.kmvs-wrap').forEach(function (wrap) {
				all.push(wrap);
			});
		});
		if (!all.length) {
			return null;
		}
		if (!attribute) {
			return all[0];
		}

		var wanted = attribute.indexOf('attribute_') === 0 ? attribute : 'attribute_' + attribute;
		for (var i = 0; i < all.length; i++) {
			var select = all[i].querySelector('select');
			if (select && select.name === wanted) {
				return all[i];
			}
		}
		return null;
	}

	/**
	 * The name of the color that is chosen right now, read from the select the swatches drive.
	 */
	function selectedName(wrap) {
		var select = wrap.querySelector('select');
		if (!select || select.selectedIndex < 0) {
			return '';
		}
		var option = select.options[select.selectedIndex];
		if (!option || '' === option.value) {
			return ''; // "Choose an option".
		}
		return (option.textContent || '').trim();
	}

	/**
	 * Keep the name above the swatches in step with what is chosen.
	 */
	function showName(widget, wrap) {
		var holder = widget.querySelector('.kmvs-label-value');
		if (holder) {
			holder.textContent = selectedName(wrap);
		}
	}

	/**
	 * What the row looks like now, so a redraw can be noticed.
	 */
	function signature(swatches) {
		var out = [];
		swatches.querySelectorAll('.kmvs-swatch').forEach(function (s) {
			out.push((s.getAttribute('data-value') || '') + (s.className || ''));
		});
		return out.join('|');
	}

	/**
	 * The wrap's own type class (color or label), so the copy is styled the same way.
	 */
	function wrapTypeClass(wrap) {
		var match = (wrap.className || '').match(/kmvs-wrap--[a-z]+/);
		return match ? match[0] : '';
	}

	function copyInto(widget, wrap) {
		var swatches = wrap.querySelector('.kmvs-swatches');
		if (!swatches) {
			return false;
		}

		var sign = signature(swatches);
		if (widget.getAttribute('data-kmst-sign') === sign) {
			return true; // Already showing this exact row.
		}

		widget.textContent = '';

		// The name of the chosen color, not the attribute's own name ("Color"), which the page already
		// says elsewhere.
		if (!widget.classList.contains('kmst-sw-widget--no-label')) {
			var head = document.createElement('div');
			head.className = 'kmvs-label';
			head.appendChild(document.createElement('span')).className = 'kmvs-label-value';
			widget.appendChild(head);
		}

		// The swatch sizes and colors are CSS variables declared on .kmvs-wrap, so the copy is put inside
		// one of its own. Without it the dots come out a few pixels wide and colorless.
		var holder = document.createElement('div');
		holder.className = 'kmvs-wrap kmvs-wrap--copy ' + wrapTypeClass(wrap);

		var copy = swatches.cloneNode(true);
		copy.classList.add('kmvs-swatches--copy');
		holder.appendChild(copy);
		widget.appendChild(holder);
		widget.setAttribute('data-kmst-sign', sign);

		// Clicking a copy clicks the real one, which carries all the behaviour.
		copy.querySelectorAll('.kmvs-swatch').forEach(function (dot, index) {
			dot.addEventListener('click', function (e) {
				e.preventDefault();
				var originals = wrap.querySelectorAll('.kmvs-swatch');
				if (originals[index]) {
					originals[index].click();
				}
			});
		});

		showName(widget, wrap);

		// The select changes whenever a swatch is picked, in the form or here.
		var select = wrap.querySelector('select');
		if (select && !select._mstNameBound) {
			select._mstNameBound = true;
			select.addEventListener('change', function () {
				document.querySelectorAll('.kmst-sw-widget').forEach(function (other) {
					showName(other, wrap);
				});
			});
		}

		return !!copy.querySelector('.kmvs-swatch');
	}

	function fill(widget) {
		var wrap = findWrap(widget.getAttribute('data-attribute') || '');
		if (!wrap) {
			return;
		}

		var shown = copyInto(widget, wrap);

		// Hide the row in the form only when the widget really has something to show.
		var row = wrap.closest('tr') || wrap;
		if (shown && '1' === widget.getAttribute('data-hide-original')) {
			row.classList.add('kmst-sw-moved');
			row.style.display = 'none';
		} else if (!shown) {
			row.classList.remove('kmst-sw-moved');
			row.style.removeProperty('display');
		}
	}

	function run() {
		document.querySelectorAll('.kmst-sw-widget').forEach(fill);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}

	// The form can arrive late (Elementor templates, quick view), and the theme redraws the row when the
	// chosen variation changes.
	if (window.MutationObserver) {
		var pending = false;
		new MutationObserver(function () {
			if (pending) {
				return;
			}
			pending = true;
			window.setTimeout(function () {
				pending = false;
				run();
			}, 60);
		}).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
	}
})();
