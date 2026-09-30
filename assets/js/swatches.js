/**
 * KM Storefront Builder: variation swatches — keeps swatches and the hidden WooCommerce selects in sync.
 */
(function ($) {
	'use strict';

	function sync($form) {
		$form.find('.kmvs-wrap').each(function () {
			var $wrap = $(this);
			var $select = $wrap.find('select').first();
			var current = $select.val() || '';
			var selectedLabel = '';

			$wrap.find('.kmvs-swatch').each(function () {
				var $swatch = $(this);
				var value = $swatch.attr('data-value');
				var $option = $select.find('option').filter(function () {
					return this.value === value;
				});
				var available = $option.length > 0 && !$option.prop('disabled');
				var selected = value === current;

				$swatch
					.toggleClass('is-selected', selected)
					.toggleClass('is-disabled', !available)
					.attr('aria-checked', selected ? 'true' : 'false')
					.attr('aria-disabled', available ? 'false' : 'true');

				if (selected) {
					selectedLabel = $swatch.attr('data-label');
				}
			});

			var id = $select.attr('id');
			if (!id) {
				return;
			}
			var $label = $form.find('label[for="' + id + '"]').first();
			if (!$label.length) {
				return;
			}
			var $name = $label.siblings('.kmvs-selected').first();
			if (!$name.length) {
				$name = $('<span class="kmvs-selected" aria-live="polite"></span>').insertAfter($label);
			}
			$name.text(selectedLabel ? selectedLabel : '');
		});
	}

	function choose($swatch) {
		if ($swatch.hasClass('is-disabled')) {
			return;
		}
		var $select = $swatch.closest('.kmvs-wrap').find('select').first();
		var value = $swatch.hasClass('is-selected') ? '' : $swatch.attr('data-value');

		$select.val(value).trigger('change');
		sync($swatch.closest('form'));
	}

	$(document)
		.on('click', '.kmvs-swatch', function (e) {
			e.preventDefault();
			choose($(this));
		})
		.on('keydown', '.kmvs-swatch', function (e) {
			if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
				e.preventDefault();
				choose($(this));
			}
		})
		.on('woocommerce_update_variation_values reset_data found_variation wc_variation_form', '.variations_form', function () {
			sync($(this));
		})
		.on('change', '.kmvs-wrap select', function () {
			sync($(this).closest('form'));
		});

	$(function () {
		$('.variations_form').each(function () {
			sync($(this));
		});
	});
})(jQuery);
