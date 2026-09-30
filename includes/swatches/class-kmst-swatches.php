<?php
/**
 * Single product page swatches.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replaces variation dropdowns with color circles or label boxes on the single product page only.
 */
final class KMST_Swatches {

	/**
	 * Hook in.
	 */
	public function __construct() {
		// Late priority so the theme's own swatch markup (XStore) is replaced, not wrapped.
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'render' ), 999, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Only the main product on a single product page. Shop, archives, quick view and widgets keep dropdowns.
	 *
	 * @param WC_Product|null $product Product being rendered.
	 * @return bool
	 */
	private function is_enabled( $product = null ) {
		if ( $this->is_quick_view() ) {
			$enabled = true;
		} else {
			$enabled = function_exists( 'is_product' ) && is_product() && ! wp_doing_ajax();

			if ( $enabled && $product instanceof WC_Product ) {
				$enabled = (int) $product->get_id() === (int) get_queried_object_id();
			}
		}

		return (bool) apply_filters( 'kmst_swatches_enabled', $enabled, $product );
	}

	/**
	 * Is this the theme's quick view popup being loaded? (XStore: admin-ajax action etheme_product_quick_view.)
	 *
	 * @return bool
	 */
	private function is_quick_view() {
		if ( ! wp_doing_ajax() || empty( $_REQUEST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}
		$action  = sanitize_key( wp_unslash( $_REQUEST['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$actions = apply_filters( 'kmst_swatches_quick_view_actions', array( 'etheme_product_quick_view' ) );
		return in_array( $action, (array) $actions, true );
	}

	/**
	 * Styles and script. Loaded on every front-end page, because the quick view popup can open from any product grid.
	 */
	public function assets() {
		if ( ! apply_filters( 'kmst_swatches_load_assets', true ) ) {
			return;
		}
		wp_enqueue_style( 'kmst-swatches', KMST_URL . 'assets/css/swatches.css', array(), KMST_VERSION );
		wp_enqueue_script( 'kmst-swatches', KMST_URL . 'assets/js/swatches.js', array( 'jquery' ), KMST_VERSION, true );
	}

	/**
	 * Build the hidden select plus the swatches.
	 *
	 * @param string $html Original dropdown HTML.
	 * @param array  $args Dropdown args.
	 * @return string
	 */
	public function render( $html, $args ) {
		$product   = isset( $args['product'] ) ? $args['product'] : null;
		$attribute = isset( $args['attribute'] ) ? (string) $args['attribute'] : '';

		if ( ! $product instanceof WC_Product || '' === $attribute || ! $this->is_enabled( $product ) ) {
			return $html;
		}

		$options = isset( $args['options'] ) ? $args['options'] : array();
		if ( empty( $options ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = isset( $attributes[ $attribute ] ) ? $attributes[ $attribute ] : array();
		}
		if ( empty( $options ) ) {
			return $html;
		}

		$items = $this->items( $product, $attribute, $options, isset( $args['selected'] ) ? $args['selected'] : '' );
		if ( ! $items ) {
			return $html;
		}

		$label        = wc_attribute_label( $attribute, $product );
		$is_color     = KMST_Colors::is_color_attribute_name( $attribute . ' ' . $label );
		$all_resolved = true;

		foreach ( $items as $i => $item ) {
			$colors = array();

			if ( $item['term_id'] ) {
				$manual = sanitize_hex_color( get_term_meta( $item['term_id'], 'kmvs_color', true ) );
				if ( $manual ) {
					$colors = array( $manual );
				}
			}
			if ( ! $colors ) {
				$colors = KMST_Colors::resolve( $item['raw'], $is_color );
			}
			if ( ! $colors && $item['label'] !== $item['raw'] ) {
				$colors = KMST_Colors::resolve( $item['label'], $is_color );
			}

			$items[ $i ]['colors'] = $colors;
			if ( ! $colors ) {
				$all_resolved = false;
			}
		}

		// A color attribute by name, or an unnamed attribute whose every value is a known color.
		$type = ( $is_color || $all_resolved ) ? 'color' : 'label';
		$type = apply_filters( 'kmst_attribute_type', $type, $attribute, $product, $items );

		$select = $this->select( $args, $attribute, $items );

		$out  = '<div class="kmvs-wrap kmvs-wrap--' . esc_attr( $type ) . '">';
		$out .= $select;
		$out .= '<div class="kmvs-swatches kmvs-swatches--' . esc_attr( $type ) . '" role="radiogroup" aria-label="' . esc_attr( $label ) . '">';

		foreach ( $items as $item ) {
			$out .= 'color' === $type ? $this->color_swatch( $item ) : $this->label_swatch( $item );
		}

		$out .= '</div></div>';

		return $out;
	}

	/**
	 * Normalize options into value/label pairs in the product's term order.
	 *
	 * @param WC_Product $product   Product.
	 * @param string     $attribute Attribute name.
	 * @param array      $options   Option values.
	 * @param string     $selected  Selected value.
	 * @return array
	 */
	private function items( $product, $attribute, $options, $selected ) {
		$items    = array();
		$selected = (string) $selected;

		if ( taxonomy_exists( $attribute ) ) {
			$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
			foreach ( $terms as $term ) {
				if ( ! in_array( $term->slug, $options, true ) ) {
					continue;
				}
				$items[] = array(
					'value'    => $term->slug,
					'raw'      => KMST_Colors::term_name( $term ),
					'label'    => apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ),
					'term_id'  => (int) $term->term_id,
					'selected' => sanitize_title( $selected ) === $term->slug,
				);
			}
			return $items;
		}

		$selected_is_slug = sanitize_title( $selected ) === $selected;
		foreach ( $options as $option ) {
			$items[] = array(
				'value'    => $option,
				'raw'      => $option,
				'label'    => apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ),
				'term_id'  => 0,
				'selected' => $selected_is_slug ? sanitize_title( $option ) === $selected : $option === $selected,
			);
		}
		return $items;
	}

	/**
	 * The standard WooCommerce select, kept in the DOM so the variation script keeps working.
	 *
	 * @param array  $args      Dropdown args.
	 * @param string $attribute Attribute name.
	 * @param array  $items     Items.
	 * @return string
	 */
	private function select( $args, $attribute, $items ) {
		$name      = ! empty( $args['name'] ) ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$id        = ! empty( $args['id'] ) ? $args['id'] : sanitize_title( $attribute );
		$class     = trim( ( isset( $args['class'] ) ? $args['class'] : '' ) . ' kmvs-select' );
		$none_text = ! empty( $args['show_option_none'] ) ? $args['show_option_none'] : __( 'Choose an option', 'km-storefront-builder' );

		$html  = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="' . esc_attr( 'attribute_' . sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( ! empty( $args['show_option_none'] ) ? 'yes' : 'no' ) . '">';
		$html .= '<option value="">' . esc_html( $none_text ) . '</option>';

		foreach ( $items as $item ) {
			$html .= '<option value="' . esc_attr( $item['value'] ) . '"' . ( $item['selected'] ? ' selected="selected"' : '' ) . '>' . esc_html( $item['label'] ) . '</option>';
		}

		return $html . '</select>';
	}

	/**
	 * Circle swatch.
	 *
	 * @param array $item Item.
	 * @return string
	 */
	private function color_swatch( $item ) {
		$classes = array( 'kmvs-swatch', 'kmvs-swatch--color' );
		$fill    = KMST_Colors::background( $item['colors'] );

		if ( ! $fill ) {
			$classes[] = 'is-unknown';
		} elseif ( 1 === count( $item['colors'] ) && KMST_Colors::is_light( $item['colors'][0] ) ) {
			$classes[] = 'is-light';
		}

		$inner = $fill
			? '<span class="kmvs-swatch__fill" style="background:' . esc_attr( $fill ) . '"></span>'
			: '<span class="kmvs-swatch__fill">' . esc_html( function_exists( 'mb_substr' ) ? mb_substr( $item['label'], 0, 1, 'UTF-8' ) : substr( $item['label'], 0, 1 ) ) . '</span>';

		return $this->swatch( $item, $classes, $inner . '<span class="kmvs-tooltip">' . esc_html( $item['label'] ) . '</span>' );
	}

	/**
	 * Box swatch.
	 *
	 * @param array $item Item.
	 * @return string
	 */
	private function label_swatch( $item ) {
		return $this->swatch( $item, array( 'kmvs-swatch', 'kmvs-swatch--label' ), '<span class="kmvs-swatch__text">' . esc_html( $item['label'] ) . '</span>' );
	}

	/**
	 * Shared swatch wrapper. A span, not a button, so theme button styles don't leak in.
	 *
	 * @param array  $item    Item.
	 * @param array  $classes Classes.
	 * @param string $inner   Inner HTML.
	 * @return string
	 */
	private function swatch( $item, $classes, $inner ) {
		if ( $item['selected'] ) {
			$classes[] = 'is-selected';
		}

		return sprintf(
			'<span class="%1$s" role="radio" tabindex="0" aria-checked="%2$s" aria-label="%3$s" data-value="%4$s" data-label="%3$s">%5$s</span>',
			esc_attr( implode( ' ', $classes ) ),
			$item['selected'] ? 'true' : 'false',
			esc_attr( $item['label'] ),
			esc_attr( $item['value'] ),
			$inner
		);
	}
}
