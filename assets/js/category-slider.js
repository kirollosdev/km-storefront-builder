/**
 * Category Slider: arrow scrolling, mouse dragging and disabled arrows at the ends.
 * RTL safe (scrollLeft goes negative), and re-initialised inside the Elementor editor.
 */
(function () {
	'use strict';

	function init(slider) {
		if (!slider || slider._mstCs) {
			return;
		}
		slider._mstCs = true;

		var track = slider.querySelector('.kmst-csl-track');
		if (!track) {
			return;
		}

		var prev = slider.querySelector('.kmst-csl-prev');
		var next = slider.querySelector('.kmst-csl-next');
		var rtl = getComputedStyle(slider).direction === 'rtl';
		if (rtl) {
			slider.classList.add('kmst-csl--rtl');
		}

		function step() {
			var card = track.querySelector('.kmst-csl-card');
			if (!card) {
				return track.clientWidth;
			}
			var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
			return card.getBoundingClientRect().width + gap;
		}

		// How far the row can still scroll in each direction, as positive numbers.
		function room() {
			var max = track.scrollWidth - track.clientWidth;
			var pos = Math.abs(track.scrollLeft);
			return { back: pos, forward: Math.max(0, max - pos) };
		}

		// data-step: "end" jumps to the last cards (or back to the first), "page" moves one screen, "card" one card.
		var mode = slider.getAttribute('data-step') || 'end';

		function scrollBy(direction) {
			if (mode === 'end') {
				var max = track.scrollWidth - track.clientWidth;
				var target = direction > 0 ? max : 0;
				// Right-to-left rows scroll into negative numbers.
				track.scrollTo({ left: rtl ? -target : target, behavior: 'smooth' });
			} else {
				var amount = (mode === 'page' ? pageStep() : step()) * direction;
				track.scrollBy({ left: rtl ? -amount : amount, behavior: 'smooth' });
			}
			// Refresh the arrows even if the browser sends no scroll event (instant jumps, background tabs).
			setTimeout(update, 0);
			setTimeout(update, 600);
		}

		// A screen of whole cards, so the next screen starts on a card edge.
		function pageStep() {
			var one = step();
			return one ? Math.max(1, Math.floor((track.clientWidth + 1) / one)) * one : track.clientWidth;
		}

		function update() {
			var space = room();
			if (prev) {
				prev.disabled = space.back < 2;
			}
			if (next) {
				next.disabled = space.forward < 2;
			}
		}

		if (prev) {
			prev.addEventListener('click', function () {
				scrollBy(-1);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				scrollBy(1);
			});
		}

		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();

		// Images can load late and change the scroll width.
		track.querySelectorAll('img').forEach(function (img) {
			if (!img.complete) {
				img.addEventListener('load', update, { once: true });
			}
		});

		if (!slider.classList.contains('kmst-csl--drag')) {
			return;
		}

		var down = false;
		var startX = 0;
		var startScroll = 0;
		var moved = 0;

		track.addEventListener('mousedown', function (event) {
			if (event.button !== 0) {
				return;
			}
			down = true;
			moved = 0;
			startX = event.pageX;
			startScroll = track.scrollLeft;
			track.classList.add('is-dragging');
		});

		window.addEventListener('mousemove', function (event) {
			if (!down) {
				return;
			}
			var distance = event.pageX - startX;
			moved = Math.abs(distance);
			track.scrollLeft = startScroll - distance;
			if (moved > 3) {
				event.preventDefault();
			}
		});

		window.addEventListener('mouseup', function () {
			if (!down) {
				return;
			}
			down = false;
			track.classList.remove('is-dragging');
			update();
		});

		// A drag should not open the card's link.
		track.addEventListener(
			'click',
			function (event) {
				if (moved > 5) {
					event.preventDefault();
					event.stopPropagation();
					moved = 0;
				}
			},
			true
		);

		track.addEventListener('dragstart', function (event) {
			event.preventDefault();
		});
	}

	function initAll(root) {
		(root || document).querySelectorAll('.kmst-csl').forEach(init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initAll();
		});
	} else {
		initAll();
	}

	// Elementor editor: run again when the widget is (re)rendered.
	window.addEventListener('elementor/frontend/init', function () {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/kmst-category-slider.default', function ($scope) {
				initAll($scope && $scope[0] ? $scope[0] : document);
			});
		}
	});
})();
