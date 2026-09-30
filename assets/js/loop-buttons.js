/**
 * KM Storefront Builder: product card buttons.
 * Quick view -> the theme's quick view. Add to cart (size-only products) -> pick a size in place, added by AJAX.
 */
(function ($) {
	'use strict';

	var L10N = window.mstLoopButtons || {};
	var phone = window.matchMedia('(max-width: 767px)');
	var picker = null;
	var backdrop = null;
	var current = null;

	function closest(el, selector) {
		return el && el.closest ? el.closest(selector) : null;
	}

	/* ---------- Quick view ---------- */
	function quickView(button) {
		var id = button.getAttribute('data-product_id');
		var card = closest(button, '.product, .etheme-product-grid-item, li');
		var trigger = (card && card.querySelector('.show-quickly')) || document.querySelector('.show-quickly[data-prodid="' + id + '"]');

		if (trigger && $) {
			$(trigger).trigger('click');
		} else if (trigger) {
			trigger.click();
		} else {
			window.location.href = button.href; // No quick view on this grid: open the product.
		}
	}

	/* ---------- Size picker ---------- */
	function build() {
		backdrop = document.createElement('div');
		backdrop.className = 'kmst-lb-backdrop';
		backdrop.hidden = true;

		picker = document.createElement('div');
		picker.className = 'kmst-lb-picker';
		picker.setAttribute('role', 'dialog');
		picker.setAttribute('aria-modal', 'false');
		picker.hidden = true;
		picker.innerHTML =
			'<div class="kmst-lb-head"><span class="kmst-lb-title"></span><button type="button" class="kmst-lb-close" aria-label="' + (L10N.close || 'Close') + '">&times;</button></div>' +
			'<div class="kmst-lb-grid"></div>' +
			'<div class="kmst-lb-status" role="status" aria-live="polite"></div>';

		document.body.appendChild(backdrop);
		document.body.appendChild(picker);

		backdrop.addEventListener('click', close);
		picker.querySelector('.kmst-lb-close').addEventListener('click', close);
		picker.addEventListener('click', function (e) {
			var size = closest(e.target, '.kmst-lb-size');
			if (size && !size.disabled && !picker.classList.contains('is-busy')) {
				add(size);
			}
		});
	}

	function open(button) {
		if (!picker) {
			build();
		}
		var sizes;
		try {
			sizes = JSON.parse(button.getAttribute('data-kmst-sizes') || '[]');
		} catch (e) {
			sizes = [];
		}
		if (!sizes.length) {
			window.location.href = button.href;
			return;
		}

		current = button;
		var label = button.getAttribute('data-kmst-label') || '';
		picker.querySelector('.kmst-lb-title').textContent = (L10N.choose || 'Select %s').replace('%s', label);
		picker.setAttribute('aria-label', picker.querySelector('.kmst-lb-title').textContent);
		picker.querySelector('.kmst-lb-status').textContent = '';
		picker.classList.remove('is-busy', 'is-done', 'is-error');

		var grid = picker.querySelector('.kmst-lb-grid');
		grid.innerHTML = '';
		sizes.forEach(function (size) {
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'kmst-lb-size';
			b.textContent = size.l;
			b.setAttribute('data-value', size.v);
			b.setAttribute('data-variation', size.id);
			if (!size.s || !size.id) {
				b.disabled = true;
				b.setAttribute('aria-label', size.l + ' – ' + (L10N.soldOut || 'Sold out'));
			}
			grid.appendChild(b);
		});

		picker.classList.toggle('is-sheet', phone.matches);
		picker.hidden = false;
		backdrop.hidden = !phone.matches;
		document.documentElement.classList.toggle('kmst-lb-locked', phone.matches);
		place();

		var first = grid.querySelector('.kmst-lb-size:not([disabled])');
		if (first) {
			first.focus({ preventScroll: true });
		}
	}

	/* Desktop: float above the button (below if there's no room). Phones: bottom sheet via CSS. */
	function place() {
		if (!picker || picker.hidden || !current) {
			return;
		}
		if (phone.matches) {
			picker.style.left = '';
			picker.style.top = '';
			return;
		}
		var r = current.getBoundingClientRect();
		var w = picker.offsetWidth;
		var h = picker.offsetHeight;
		var left = Math.min(Math.max(8, r.left + r.width / 2 - w / 2), window.innerWidth - w - 8);
		var top = r.top - h - 10;
		picker.classList.toggle('is-below', top < 8);
		if (top < 8) {
			top = r.bottom + 10;
		}
		picker.style.left = Math.round(left + window.scrollX) + 'px';
		picker.style.top = Math.round(top + window.scrollY) + 'px';
		picker.style.setProperty('--kmst-lb-arrow', Math.round(r.left + r.width / 2 - left) + 'px');
	}

	function close() {
		if (!picker || picker.hidden) {
			return;
		}
		picker.hidden = true;
		backdrop.hidden = true;
		document.documentElement.classList.remove('kmst-lb-locked');
		if (current && document.activeElement && picker.contains(document.activeElement)) {
			current.focus({ preventScroll: true });
		}
		current = null;
	}

	function add(size) {
		var button = current;
		var status = picker.querySelector('.kmst-lb-status');
		var body = new URLSearchParams();
		body.set('product_id', button.getAttribute('data-product_id'));
		body.set('variation_id', size.getAttribute('data-variation'));
		body.set('attribute', button.getAttribute('data-kmst-attr') || '');
		body.set('value', size.getAttribute('data-value'));

		picker.classList.add('is-busy');
		size.classList.add('is-loading');
		status.textContent = '';
		if ($) {
			$(document.body).trigger('adding_to_cart', [$(button), {}]);
		}

		fetch(L10N.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (data) {
				size.classList.remove('is-loading');
				picker.classList.remove('is-busy');

				if (!data || data.error || !data.fragments) {
					picker.classList.add('is-error');
					status.innerHTML = '';
					status.appendChild(document.createTextNode((data && data.message) || L10N.error || 'Error'));
					if (data && data.product_url) {
						var link = document.createElement('a');
						link.href = data.product_url;
						link.textContent = L10N.view || 'View product';
						status.appendChild(document.createTextNode(' '));
						status.appendChild(link);
					}
					return;
				}

				size.classList.add('is-added');
				picker.classList.add('is-done');
				status.textContent = L10N.added || 'Added to cart';

				// Let WooCommerce and the theme refresh the mini cart, counters and "added" popups.
				if ($) {
					$(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash, $(button)]);
				}
				setTimeout(close, 900);
			})
			.catch(function () {
				size.classList.remove('is-loading');
				picker.classList.remove('is-busy');
				picker.classList.add('is-error');
				status.textContent = L10N.error || 'Error';
			});
	}

	/* ---------- Wiring ---------- */
	// Capture phase, so the theme's own handlers on these links don't also run.
	document.addEventListener(
		'click',
		function (e) {
			var button = closest(e.target, 'a.kmst-lb--quickview, a.kmst-lb--sizes');
			if (!button) {
				if (picker && !picker.hidden && !closest(e.target, '.kmst-lb-picker')) {
					close();
				}
				return;
			}
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.button > 0) {
				return; // Let "open in new tab" work.
			}
			e.preventDefault();
			e.stopImmediatePropagation();

			if (button.getAttribute('data-kmst-mode') === 'quickview') {
				close();
				quickView(button);
			} else if (picker && !picker.hidden && current === button) {
				close();
			} else {
				open(button);
			}
		},
		true
	);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			close();
		}
	});
	window.addEventListener('resize', function () {
		if (picker && !picker.hidden && picker.classList.contains('is-sheet') !== phone.matches) {
			close();
		} else {
			place();
		}
	});
	window.addEventListener('scroll', place, { passive: true });

	/* ================================================================== */
	/* Card color swatches: pick a color, the card photo switches to it   */
	/* ================================================================== */
	var MAX_DOTS = 5;
	var fine = window.matchMedia('(hover: hover) and (pointer: fine)');

	function setImage(img, source) {
		if (source.srcset) {
			img.setAttribute('srcset', source.srcset);
		} else {
			img.removeAttribute('srcset');
		}
		if (source.sizes) {
			img.setAttribute('sizes', source.sizes);
		}
		img.setAttribute('src', source.src);
	}

	/* Keep the card image box exactly as it is, whatever the proportions of the swapped photo. */
	function lockBox(state) {
		var img = state.img;
		if (state.locked || !img.clientWidth || !img.clientHeight) {
			return;
		}
		img.style.aspectRatio = img.clientWidth + ' / ' + img.clientHeight;
		img.style.objectFit = 'cover';
		img.style.width = '100%';
		img.style.height = 'auto';
		state.locked = true;
	}

	function preload(state) {
		if (state.preloaded) {
			return;
		}
		state.preloaded = true;
		state.colors.forEach(function (color) {
			if (color.img && color.img.src) {
				var im = new Image();
				if (color.img.sizes) {
					im.sizes = color.img.sizes;
				}
				if (color.img.srcset) {
					im.srcset = color.img.srcset;
				}
				im.src = color.img.src;
			}
		});
	}

	function showColor(state, color) {
		lockBox(state);
		// The card's second photo slides over the first one while the mouse is on the card, and the mouse
		// has to be on the card to reach the dots. So whenever a color's photo is shown, picked or only
		// pointed at, the card is marked and the CSS keeps that second photo out of the way.
		state.card.classList.toggle('kmst-cs-picked', !!(color && color.img));
		setImage(state.img, color && color.img ? color.img : state.original);
	}

	function selectColor(state, dot) {
		var color = dot ? dot._mstColor : null;
		state.selected = color;

		state.row.querySelectorAll('.kmst-cs-dot').forEach(function (d) {
			var on = d === dot;
			d.classList.toggle('is-selected', on);
			d.setAttribute('aria-checked', on ? 'true' : 'false');
		});

		showColor(state, color);

		// Open the product page with this color already chosen.
		state.links.forEach(function (entry) {
			try {
				var url = new URL(entry.href, window.location.href);
				if (color) {
					url.searchParams.set(state.attr, color.v);
				}
				entry.el.href = color ? url.toString() : entry.href;
			} catch (e) {}
		});
	}

	function buildCard(anchor) {
		var card = closest(anchor, '.etheme-product-grid-item, li.product, .product');
		if (!card || card.getAttribute('data-kmst-cs')) {
			return;
		}
		var colors;
		try {
			colors = JSON.parse(anchor.getAttribute('data-kmst-colors') || '[]');
		} catch (e) {
			colors = [];
		}
		if (!colors.length) {
			return;
		}

		var box = card.querySelector('.etheme-product-grid-image');
		var img = (box || card).querySelector('img');
		if (!img) {
			return;
		}
		card.setAttribute('data-kmst-cs', '1');

		var state = {
			card: card,
			img: img,
			attr: anchor.getAttribute('data-kmst-color-attr'),
			colors: colors,
			original: { src: img.getAttribute('src'), srcset: img.getAttribute('srcset') || '', sizes: img.getAttribute('sizes') || '' },
			links: [],
			selected: null
		};

		var productUrl = anchor.href.split('?')[0];
		card.querySelectorAll('a[href]').forEach(function (a) {
			if (a.href.split('?')[0] === productUrl && !a.classList.contains('kmst-cs-more')) {
				state.links.push({ el: a, href: a.href });
			}
		});

		var row = document.createElement('div');
		var below = document.body.classList.contains('kmst-cs-below') || !box;
		row.className = 'kmst-cs ' + (below ? 'kmst-cs--below' : 'kmst-cs--inside');
		row.setAttribute('role', 'radiogroup');
		row.setAttribute('aria-label', anchor.getAttribute('data-kmst-color-label') || 'Color');

		colors.slice(0, MAX_DOTS).forEach(function (color) {
			var dot = document.createElement('button');
			dot.type = 'button';
			dot.className = 'kmst-cs-dot' + (color.lt ? ' is-light' : '') + (color.s ? '' : ' is-out') + (color.c ? '' : ' is-unknown');
			if (color.c) {
				dot.style.background = color.c;
			}
			dot.title = color.l;
			dot.setAttribute('aria-label', color.l);
			dot.setAttribute('role', 'radio');
			dot.setAttribute('aria-checked', 'false');
			dot._mstColor = color;
			row.appendChild(dot);
		});

		if (colors.length > MAX_DOTS) {
			var more = document.createElement('a');
			more.className = 'kmst-cs-more';
			more.href = anchor.href;
			more.textContent = '+' + (colors.length - MAX_DOTS);
			row.appendChild(more);
		}

		if (below) {
			var content = card.querySelector('.etheme-product-grid-content');
			if (content) {
				content.insertBefore(row, content.firstChild);
			} else if (box) {
				box.parentNode.insertBefore(row, box.nextSibling);
			} else {
				img.parentNode.parentNode.insertBefore(row, img.parentNode.nextSibling);
			}
		} else {
			box.appendChild(row);
		}

		row._mstState = state;
		// selectColor() walks the dots through state.row, so the row has to be on the state as well.
		state.row = row;

		// Load the color photos as soon as the shopper shows interest, so the switch is instant.
		card.addEventListener('pointerenter', function () {
			preload(state);
		});
		card.addEventListener('touchstart', function () {
			preload(state);
		}, { passive: true });

		row.addEventListener('click', function (e) {
			var dot = closest(e.target, '.kmst-cs-dot');
			if (!dot) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			preload(state);
			selectColor(state, dot.classList.contains('is-selected') ? null : dot);
		});

		// Desktop: pointing at a dot previews that color; leaving the dots goes back to the chosen one.
		row.addEventListener('mouseover', function (e) {
			var dot = closest(e.target, '.kmst-cs-dot');
			if (dot && fine.matches) {
				preload(state);
				showColor(state, dot._mstColor);
			}
		});
		row.addEventListener('mouseleave', function () {
			if (fine.matches) {
				showColor(state, state.selected);
			}
		});
	}

	function buildAll(root) {
		var scope = root || document;

		// Carousels (related products, upsells, the category slider) copy their slides to loop. A copy
		// brings the dots and the "already done" marker with it, but none of the JavaScript behind them,
		// so it looked built and did nothing. Drop those dead rows and let them be built again.
		scope.querySelectorAll('[data-kmst-cs]').forEach(function (card) {
			var row = card.querySelector('.kmst-cs');
			if (!row || row._mstState) {
				return;
			}
			if (row.parentNode) {
				row.parentNode.removeChild(row);
			}
			card.removeAttribute('data-kmst-cs');
		});

		scope.querySelectorAll('a[data-kmst-colors]').forEach(buildCard);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			buildAll();
		});
	} else {
		buildAll();
	}

	// Product grids loaded later (AJAX pagination, filters, carousels).
	if (window.MutationObserver) {
		var pending = false;
		new MutationObserver(function () {
			if (pending) {
				return;
			}
			pending = true;
			// A timer, not requestAnimationFrame: a background tab stops painting, and carousels that
			// clone their slides while the tab is hidden would otherwise stay unbuilt until it is shown.
			window.setTimeout(function () {
				pending = false;
				buildAll();
			}, 50);
		}).observe(document.body, { childList: true, subtree: true });
	}
	if ($) {
		$(document.body).on('updated_wc_div', function () {
			buildAll();
		});
	}
})(window.jQuery);
