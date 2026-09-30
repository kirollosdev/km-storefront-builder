/**
 * KM Storefront Builder: shop filter bar.
 * Desktop: dropdowns. Phones: a Filter / Sort toolbar that opens bottom sheets.
 * Builds WooCommerce filter URLs (product_cat, filter_x + query_type_x, min_price, max_price, orderby) and reloads.
 */
(function () {
	'use strict';

	var L10N = window.mstFilterBar || { copied: 'Link copied' };
	var phone = window.matchMedia('(max-width: 767px)');
	var mouse = window.matchMedia('(hover: hover) and (pointer: fine)');

	/* Hover opening: desktop with a mouse, and enabled on this bar. */
	function canHover(fb) {
		return fb.getAttribute('data-hover') === 'yes' && mouse.matches && !phone.matches;
	}
	var html = document.documentElement;

	/* ---------- Helpers ---------- */
	function panelOf(item) {
		return item.querySelector('.kmst-fb-panel');
	}

	function inSheet(item) {
		return !!item.closest('.kmst-fb-filters');
	}

	/*
	 * Themes like XStore put the page in a wrapper with its own z-index, and their fixed bottom bar outside it,
	 * so nothing inside the page can cover that bar. While a sheet is open, drop those ancestor z-indexes.
	 */
	function liftAncestors(fb, on) {
		if (on) {
			if (fb._lifted) {
				return;
			}
			fb._lifted = [];
			var el = fb.parentElement;
			while (el && el !== document.body) {
				var cs = getComputedStyle(el);
				if (cs.position !== 'static' && cs.zIndex !== 'auto') {
					fb._lifted.push([el, el.style.getPropertyValue('z-index'), el.style.getPropertyPriority('z-index')]);
					el.style.setProperty('z-index', 'auto', 'important');
				}
				el = el.parentElement;
			}
		} else if (fb._lifted) {
			fb._lifted.forEach(function (entry) {
				if (entry[1]) {
					entry[0].style.setProperty('z-index', entry[1], entry[2]);
				} else {
					entry[0].style.removeProperty('z-index');
				}
			});
			fb._lifted = null;
		}
	}

	/* The overlay is on whenever a phone sheet is showing. */
	function syncOverlay(fb) {
		var open = phone.matches && (fb.classList.contains('is-sheet-open') || !!fb.querySelector('.kmst-fb-tools .kmst-fb-item.is-open'));
		fb.classList.toggle('has-overlay', open);
		liftAncestors(fb, open);
		html.classList.toggle('kmst-fb-sheet-open', !!document.querySelector('.kmst-fb.has-overlay'));
	}

	/* ---------- Dropdowns (desktop) and tool sheets (phones) ---------- */
	function setOpen(item, open) {
		var panel = panelOf(item);
		var toggle = item.querySelector('.kmst-fb-toggle');
		if (!panel) {
			return;
		}
		item.classList.toggle('is-open', open);
		panel.hidden = !open;
		if (!open) {
			item._pinned = false;
			item._dirty = false;
		}
		if (toggle) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		}

		if (open && !phone.matches) {
			// Keep the dropdown inside the viewport.
			panel.style.removeProperty('--kmst-fb-shift');
			var rect = panel.getBoundingClientRect();
			var overflow = rect.right - (window.innerWidth - 12);
			var underflow = 12 - rect.left;
			if (overflow > 0) {
				panel.style.setProperty('--kmst-fb-shift', -overflow + 'px');
			} else if (underflow > 0) {
				panel.style.setProperty('--kmst-fb-shift', underflow + 'px');
			}
		}

		syncOverlay(item.closest('.kmst-fb'));
	}

	/* Close dropdowns. Sections inside an open filter sheet stay as they are. */
	function closeAll(except) {
		document.querySelectorAll('.kmst-fb-item.is-open').forEach(function (item) {
			if (item === except) {
				return;
			}
			if (inSheet(item) && item.closest('.kmst-fb').classList.contains('is-sheet-open')) {
				return;
			}
			setOpen(item, false);
		});
	}

	/* ---------- Filter sheet (phones) ---------- */
	function openSheet(fb) {
		closeAll();
		fb.classList.add('is-sheet-open');

		// Expand the sections that have something selected, or the first one.
		var items = fb.querySelectorAll('.kmst-fb-filters .kmst-fb-item');
		var active = fb.querySelectorAll('.kmst-fb-filters .kmst-fb-item.is-active');
		(active.length ? Array.prototype.slice.call(active) : Array.prototype.slice.call(items, 0, 1)).forEach(function (item) {
			setOpen(item, true);
		});

		syncOverlay(fb);
		var close = fb.querySelector('.kmst-fb-sheet-close');
		if (close) {
			close.focus({ preventScroll: true });
		}
	}

	function closeSheet(fb) {
		if (!fb.classList.contains('is-sheet-open')) {
			return;
		}
		fb.classList.remove('is-sheet-open');
		fb.querySelectorAll('.kmst-fb-filters .kmst-fb-item.is-open').forEach(function (item) {
			setOpen(item, false);
		});
		syncOverlay(fb);
		var opener = fb.querySelector('.kmst-fb-open-filters');
		if (opener) {
			opener.focus({ preventScroll: true });
		}
	}

	function closeEverything() {
		document.querySelectorAll('.kmst-fb').forEach(function (fb) {
			closeSheet(fb);
		});
		closeAll();
	}

	/* ---------- URL building ---------- */
	function baseUrl() {
		var url = new URL(window.location.href);
		url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');
		url.searchParams.delete('paged');
		url.searchParams.delete('product-page');
		url.hash = '';
		return url;
	}

	function go(url) {
		// Inside the Elementor editor preview, don't navigate away from the page being edited.
		if (document.body.classList.contains('elementor-editor-active')) {
			closeEverything();
			return;
		}
		window.location.href = url.toString().replace(/%2C/gi, ',');
	}

	function collect(fb, url) {
		var params = url.searchParams;

		fb.querySelectorAll('.kmst-fb-item[data-type="terms"]').forEach(function (item) {
			var key = item.getAttribute('data-key');
			var queryType = item.getAttribute('data-query-type');
			var values = Array.prototype.map.call(item.querySelectorAll('input[type="checkbox"]:checked'), function (input) {
				return input.value;
			});

			params.delete(key);
			if (queryType) {
				params.delete(queryType);
			}
			if (values.length) {
				params.set(key, values.join(','));
				if (queryType) {
					params.set(queryType, 'or');
				}
			}
		});

		// Rating: one choice, sent as WooCommerce's list of included ratings ("4 & up" = 4,5).
		fb.querySelectorAll('.kmst-fb-item[data-type="rating"]').forEach(function (item) {
			params.delete('rating_filter');
			var chosen = item.querySelector('input[type="checkbox"]:checked');
			if (chosen) {
				var list = [];
				for (var n = parseInt(chosen.value, 10); n <= 5; n++) {
					list.push(n);
				}
				params.set('rating_filter', list.join(','));
			}
		});

		var price = fb.querySelector('.kmst-fb-price');
		if (price) {
			var floor = parseFloat(price.getAttribute('data-floor'));
			var ceil = parseFloat(price.getAttribute('data-ceil'));
			var min = parseFloat(price.querySelector('.kmst-fb-price-min').value);
			var max = parseFloat(price.querySelector('.kmst-fb-price-max').value);

			params.delete('min_price');
			params.delete('max_price');
			if (!isNaN(min) && min > floor) {
				params.set('min_price', Math.floor(min));
			}
			if (!isNaN(max) && max < ceil) {
				params.set('max_price', Math.ceil(max));
			}
		}

		// On a category page: keep its URL while only that category is chosen, otherwise filter from the shop page.
		var currentCat = fb.getAttribute('data-current-cat');
		if (currentCat && fb.querySelector('.kmst-fb-item[data-key="product_cat"]')) {
			if (params.get('product_cat') === currentCat) {
				params.delete('product_cat');
			} else {
				var shop = fb.querySelector('.kmst-fb-shop');
				if (shop && shop.href) {
					url.pathname = samePrefix(new URL(shop.href, window.location.href).pathname, window.location.pathname);
				}
			}
		}

		return url;
	}

	// Keep the language prefix of the current page (/en/..., or none for the default language), so leaving a
	// category page never switches language even if the shop link came from another language.
	function langPrefix(path) {
		var m = /^\/([a-z]{2}(?:-[a-z]{2})?)(?=\/|$)/i.exec(path);
		return m ? m[1] : '';
	}

	function samePrefix(target, current) {
		var from = langPrefix(target);
		var to = langPrefix(current);
		if (from === to) {
			return target;
		}
		if (from) {
			target = target.slice(from.length + 1) || '/';
		}
		return to ? '/' + to + target : target;
	}

	function apply(fb) {
		go(collect(fb, baseUrl()));
	}

	function clearItem(item) {
		item.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
			input.checked = false;
		});
		var price = item.querySelector('.kmst-fb-price');
		if (price) {
			setPrice(price, price.getAttribute('data-floor'), price.getAttribute('data-ceil'));
		}
	}

	/* ---------- Price slider ---------- */
	function setPrice(price, min, max) {
		var floor = parseFloat(price.getAttribute('data-floor'));
		var ceil = parseFloat(price.getAttribute('data-ceil'));
		min = Math.max(floor, Math.min(parseFloat(min), ceil));
		max = Math.min(ceil, Math.max(parseFloat(max), floor));
		if (isNaN(min)) {
			min = floor;
		}
		if (isNaN(max)) {
			max = ceil;
		}
		if (min > max) {
			var swap = min;
			min = max;
			max = swap;
		}

		price.querySelector('.kmst-fb-range-min').value = min;
		price.querySelector('.kmst-fb-range-max').value = max;
		price.querySelector('.kmst-fb-price-min').value = Math.floor(min);
		price.querySelector('.kmst-fb-price-max').value = Math.ceil(max);

		var span = ceil - floor || 1;
		var fill = price.querySelector('.kmst-fb-range-fill');
		fill.style.insetInlineStart = ((min - floor) / span) * 100 + '%';
		fill.style.insetInlineEnd = ((ceil - max) / span) * 100 + '%';
	}

	function initPrice(price) {
		var rMin = price.querySelector('.kmst-fb-range-min');
		var rMax = price.querySelector('.kmst-fb-range-max');
		var nMin = price.querySelector('.kmst-fb-price-min');
		var nMax = price.querySelector('.kmst-fb-price-max');

		rMin.addEventListener('input', function () {
			if (parseFloat(rMin.value) > parseFloat(rMax.value)) {
				rMin.value = rMax.value;
			}
			setPrice(price, rMin.value, rMax.value);
		});
		rMax.addEventListener('input', function () {
			if (parseFloat(rMax.value) < parseFloat(rMin.value)) {
				rMax.value = rMin.value;
			}
			setPrice(price, rMin.value, rMax.value);
		});
		nMin.addEventListener('change', function () {
			setPrice(price, nMin.value, nMax.value);
		});
		nMax.addEventListener('change', function () {
			setPrice(price, nMin.value, nMax.value);
		});

		setPrice(price, rMin.value, rMax.value);
	}

	/* ---------- View (columns) ---------- */
	function setColumns(attr, value) {
		var prefix = attr === 'mobile' ? 'kmst-cols-m' : 'kmst-cols-';
		var storageKey = attr === 'mobile' ? 'kmst_fb_cols_m' : 'kmst_fb_cols';

		Array.prototype.slice.call(html.classList).forEach(function (cls) {
			if (cls.indexOf(prefix) === 0 && (attr === 'mobile' || cls.indexOf('kmst-cols-m') !== 0)) {
				html.classList.remove(cls);
			}
		});
		html.classList.add(prefix + value);
		try {
			localStorage.setItem(storageKey, value);
		} catch (e) {}
		markViews();
		window.dispatchEvent(new Event('resize'));
	}

	/* Highlight the current column count; without a saved choice, read it from the product grid. */
	function markViews() {
		var grid = document.querySelector('.etheme-product-grid, ul.products');
		var shown = grid ? getComputedStyle(grid).gridTemplateColumns.split(' ').filter(Boolean).length : 0;
		var savedDesktop = /(?:^|\s)kmst-cols-(\d)(?:\s|$)/.exec(html.className);
		var savedMobile = /(?:^|\s)kmst-cols-m(\d)(?:\s|$)/.exec(html.className);

		document.querySelectorAll('.kmst-fb-view').forEach(function (button) {
			var desktop = button.getAttribute('data-cols');
			var mobile = button.getAttribute('data-cols-mobile');
			var on;
			if (desktop) {
				on = savedDesktop ? savedDesktop[1] === desktop : !phone.matches && String(shown) === desktop;
			} else {
				on = savedMobile ? savedMobile[1] === mobile : phone.matches && String(shown) === mobile;
			}
			button.classList.toggle('is-current', on);
			button.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
	}

	/* ---------- Share ---------- */
	function share(button) {
		var url = window.location.href;
		if (navigator.share && phone.matches) {
			navigator.share({ title: document.title, url: url }).catch(function () {});
			return;
		}
		var toast = button.closest('.kmst-fb-mbar, .kmst-fb-item');
		toast = toast ? toast.querySelector('.kmst-fb-toast') : null;
		var done = function () {
			if (!toast) {
				return;
			}
			var bar = button.closest('.kmst-fb');
			toast.textContent = (bar && bar.getAttribute('data-copied')) || L10N.copied;
			toast.classList.add('is-visible');
			clearTimeout(toast._t);
			toast._t = setTimeout(function () {
				toast.classList.remove('is-visible');
			}, 1800);
		};
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(url).then(done, function () {
				fallbackCopy(url);
				done();
			});
		} else {
			fallbackCopy(url);
			done();
		}
	}

	function fallbackCopy(text) {
		var area = document.createElement('textarea');
		area.value = text;
		area.setAttribute('readonly', '');
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild(area);
		area.select();
		try {
			document.execCommand('copy');
		} catch (e) {}
		document.body.removeChild(area);
	}

	/* ---------- Wiring ---------- */
	function init(fb) {
		if (fb.getAttribute('data-ready')) {
			return;
		}
		fb.setAttribute('data-ready', '1');

		fb.querySelectorAll('.kmst-fb-price').forEach(initPrice);

		// Rating options behave like radio buttons that can also be unticked.
		fb.addEventListener('change', function (e) {
			var input = e.target;
			if (!input.matches || !input.matches('.kmst-fb-item[data-type="rating"] input[type="checkbox"]') || !input.checked) {
				return;
			}
			input.closest('.kmst-fb-item').querySelectorAll('input[type="checkbox"]').forEach(function (other) {
				if (other !== input) {
					other.checked = false;
				}
			});
		});

		// Desktop: open on hover, close shortly after the mouse leaves (the delay covers the gap to the dropdown).
		// A dropdown stays open if it was clicked, has unapplied changes, or has focus (typing a price).
		fb.querySelectorAll('.kmst-fb-bar .kmst-fb-item').forEach(function (item) {
			var panel = panelOf(item);
			if (!panel) {
				return;
			}
			var markDirty = function () {
				item._dirty = true;
			};
			panel.addEventListener('change', markDirty);
			panel.addEventListener('input', markDirty);

			item.addEventListener('mouseenter', function () {
				if (!canHover(fb)) {
					return;
				}
				clearTimeout(item._hoverTimer);
				item._hoverTimer = setTimeout(function () {
					if (item.classList.contains('is-open')) {
						return;
					}
					// Don't snatch focus away from a dropdown the shopper is still working in.
					var busy = fb.querySelector('.kmst-fb-bar .kmst-fb-item.is-open');
					if (busy && busy !== item && (busy._dirty || busy.contains(document.activeElement) && document.activeElement.matches('input'))) {
						return;
					}
					closeAll(item);
					setOpen(item, true);
				}, 80);
			});

			item.addEventListener('mouseleave', function () {
				if (!canHover(fb)) {
					return;
				}
				clearTimeout(item._hoverTimer);
				item._hoverTimer = setTimeout(function () {
					if (!item.classList.contains('is-open') || item._pinned || item._dirty) {
						return;
					}
					if (item.contains(document.activeElement) && document.activeElement.matches('input')) {
						return;
					}
					setOpen(item, false);
				}, 250);
			});
		});
		markViews();

		fb.addEventListener('click', function (e) {
			var target = e.target;

			if (target.closest('.kmst-fb-backdrop')) {
				closeEverything();
				return;
			}
			if (target.closest('.kmst-fb-open-filters')) {
				openSheet(fb);
				return;
			}
			if (target.closest('.kmst-fb-open-sort')) {
				var sortItem = fb.querySelector('.kmst-fb-item[data-key="orderby"]');
				if (sortItem) {
					closeSheet(fb);
					closeAll();
					setOpen(sortItem, true);
				}
				return;
			}
			if (target.closest('.kmst-fb-sheet-close')) {
				closeSheet(fb);
				return;
			}
			if (target.closest('.kmst-fb-sheet-clear')) {
				fb.querySelectorAll('.kmst-fb-filters .kmst-fb-item').forEach(clearItem);
				return;
			}
			if (target.closest('.kmst-fb-sheet-apply')) {
				apply(fb);
				return;
			}
			if (target.closest('.kmst-fb-share')) {
				closeAll();
				share(target.closest('.kmst-fb-share'));
				return;
			}

			var item = target.closest('.kmst-fb-item');

			var toggle = target.closest('.kmst-fb-toggle');
			if (toggle && item) {
				var open = !item.classList.contains('is-open');

				// Already open from hovering: a click pins it open instead of closing it.
				if (!open && canHover(fb) && !item._pinned) {
					item._pinned = true;
					return;
				}
				item._pinned = open;

				// Sections inside the phone filter sheet expand independently; everywhere else only one dropdown is open.
				if (!(inSheet(item) && fb.classList.contains('is-sheet-open'))) {
					closeAll(item);
				}
				setOpen(item, open);
				return;
			}
			if (target.closest('.kmst-fb-close')) {
				setOpen(item, false);
				return;
			}
			if (target.closest('.kmst-fb-apply')) {
				apply(fb);
				return;
			}
			if (target.closest('.kmst-fb-clear')) {
				clearItem(item);
				apply(fb);
				return;
			}

			var sort = target.closest('.kmst-fb-sort');
			if (sort) {
				var url = baseUrl();
				if (sort.getAttribute('data-default') === 'yes') {
					url.searchParams.delete('orderby');
				} else {
					url.searchParams.set('orderby', sort.getAttribute('data-value'));
				}
				go(url);
				return;
			}

			var view = target.closest('.kmst-fb-view');
			if (view) {
				if (view.hasAttribute('data-cols')) {
					setColumns('desktop', view.getAttribute('data-cols'));
				} else {
					setColumns('mobile', view.getAttribute('data-cols-mobile'));
				}
				return;
			}

			var chip = target.closest('.kmst-fb-chip');
			if (chip) {
				var chipItem = fb.querySelector('.kmst-fb-item[data-key="' + chip.getAttribute('data-key') + '"]');
				if (chipItem) {
					if (chip.getAttribute('data-key') === 'price') {
						clearItem(chipItem);
					} else {
						chipItem.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
							if (input.value === chip.getAttribute('data-value')) {
								input.checked = false;
							}
						});
					}
				}
				apply(fb);
				return;
			}

			if (target.closest('.kmst-fb-clear-all')) {
				fb.querySelectorAll('.kmst-fb-item').forEach(clearItem);
				apply(fb);
			}
		});

		// Enter inside the price inputs applies.
		fb.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && e.target.matches('.kmst-fb-price input')) {
				e.preventDefault();
				var price = e.target.closest('.kmst-fb-price');
				setPrice(price, price.querySelector('.kmst-fb-price-min').value, price.querySelector('.kmst-fb-price-max').value);
				apply(fb);
			}
		});
	}

	// Clicks outside a dropdown close it (desktop). Phone sheets close from their backdrop, close button or Escape.
	document.addEventListener('click', function (e) {
		if (e.target.closest('.kmst-fb-item, .kmst-fb-mbar, .kmst-fb-filters, .kmst-fb-backdrop')) {
			return;
		}
		closeAll();
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			closeEverything();
		}
	});

	// Switching between phone and desktop layouts (rotation, resizing): start clean.
	var onLayoutChange = function () {
		closeEverything();
		markViews();
	};
	if (phone.addEventListener) {
		phone.addEventListener('change', onLayoutChange);
	} else if (phone.addListener) {
		phone.addListener(onLayoutChange);
	}

	function initAll() {
		document.querySelectorAll('.kmst-fb').forEach(init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	// Elementor: the widget is re-rendered in the editor and can load via AJAX, so init each time it's ready.
	function hookElementor() {
		if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
			return false;
		}
		window.elementorFrontend.hooks.addAction('frontend/element_ready/kmst-horizontal-filters.default', function ($scope) {
			var scope = $scope && $scope[0] ? $scope[0] : $scope;
			if (scope && scope.querySelectorAll) {
				scope.querySelectorAll('.kmst-fb').forEach(init);
			}
		});
		return true;
	}

	if (!hookElementor()) {
		window.addEventListener('elementor/frontend/init', hookElementor);
	}
})();
