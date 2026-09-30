<?php
/**
 * Shop card buttons for variable products.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replaces "Select options" on product cards:
 * - products with a color option: "Quick view" (opens the theme's quick view),
 * - products with only a size option: "Add to cart", pick a size in place and it's added by AJAX.
 */
final class KMST_Loop_Buttons {

	/**
	 * Per-request cache of product modes.
	 *
	 * @var array
	 */
	private $modes = array();

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'text' ), 20, 2 );
		add_filter( 'woocommerce_product_add_to_cart_description', array( $this, 'description' ), 20, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'link' ), 20, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		// XStore product widgets (carousels, grids) whose cards have no cart button in the image area: add it there.
		add_filter( 'elementor/widget/render_content', array( $this, 'add_to_widget_cards' ), 20, 2 );
		add_action( 'wc_ajax_kmst_add_variation', array( $this, 'ajax_add' ) );
	}

	/**
	 * Assets, site-wide: product cards appear on the shop, categories, home page carousels and related products.
	 */
	public function assets() {
		wp_enqueue_style( 'kmst-loop-buttons', KMST_URL . 'assets/css/loop-buttons.css', array(), KMST_VERSION );
		wp_enqueue_script( 'kmst-loop-buttons', KMST_URL . 'assets/js/loop-buttons.js', array( 'jquery' ), KMST_VERSION, true );
		wp_localize_script(
			'kmst-loop-buttons',
			'mstLoopButtons',
			array(
				'ajaxUrl' => WC_AJAX::get_endpoint( 'kmst_add_variation' ),
				/* translators: %s: attribute name, e.g. Size */
				'choose'  => __( 'Select %s', 'km-storefront-builder' ),
				'added'   => __( 'Added to cart', 'km-storefront-builder' ),
				'soldOut' => __( 'Sold out', 'km-storefront-builder' ),
				'error'   => __( 'Could not add to cart. Please try again.', 'km-storefront-builder' ),
				'view'    => __( 'View product', 'km-storefront-builder' ),
				'close'   => __( 'Close', 'km-storefront-builder' ),
			)
		);
	}

	/**
	 * Put the card button inside the image area of every XStore product card in a widget's output
	 * (homepage carousels, related products, grids), when the widget itself didn't print one there.
	 *
	 * @param string $content Widget HTML.
	 * @param object $widget  Elementor widget.
	 * @return string
	 */
	public function add_to_widget_cards( $content, $widget = null ) {
		if ( ! $this->active_here() || ! is_string( $content ) || false === strpos( $content, 'etheme-product-grid-item' ) || false === strpos( $content, 'footer-inner' ) ) {
			return $content;
		}
		if ( ! apply_filters( 'kmst_loop_buttons_in_widget_cards', true, $widget ) ) {
			return $content;
		}

		// Split at each card's opening tag so every card is handled on its own.
		$parts = preg_split( '/(<div class="etheme-product-grid-item[^"]*"[^>]*>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! $parts || count( $parts ) < 3 ) {
			return $content;
		}

		$out = $parts[0];
		for ( $i = 1; $i < count( $parts ); $i += 2 ) {
			$open = $parts[ $i ];
			$body = isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '';

			if ( preg_match( '/\bpost-(\d+)\b/', $open, $id ) && false !== strpos( $body, '<div class="footer-inner">' ) ) {
				// Only the image area counts: a button under the price is hidden by CSS.
				$footer_start = strpos( $body, '<div class="footer-inner">' );
				$footer_end   = strpos( $body, '</footer>', $footer_start );
				$footer       = false === $footer_end ? '' : substr( $body, $footer_start, $footer_end - $footer_start );

				if ( '' !== $footer && false === strpos( $footer, 'kmst-lb' ) ) {
					$button = $this->loop_button_html( (int) $id[1] );
					if ( '' !== $button ) {
						$insert_at = $footer_start + strlen( '<div class="footer-inner">' );
						$body      = substr( $body, 0, $insert_at ) . $button . substr( $body, $insert_at );
					}
				}
			}

			$out .= $open . $body;
		}

		return $out;
	}

	/**
	 * WooCommerce's loop add-to-cart button for a product, run through our link filter.
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	private function loop_button_html( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		$previous = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
		$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		ob_start();
		woocommerce_template_loop_add_to_cart();
		$html = (string) ob_get_clean();

		$GLOBALS['product'] = $previous; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Only use it if our filter turned it into a card button (variable/simple products we handle).
		if ( false === strpos( $html, 'kmst-lb' ) ) {
			return '';
		}

		// WooCommerce's template prints the text label; the card button shows its label from CSS and needs the bag icon instead.
		$icon = '<svg class="kmst-lb-icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.232 5.352c-.024-.528-.456-.912-.936-.912h-2.736c-.12-2.448-2.112-4.392-4.56-4.392S7.536 1.992 7.44 4.44H4.728c-.528 0-.936.432-.936.936l-.648 16.464c-.024.552.168 1.08.552 1.488.384.408.888.624 1.44.624h13.704c.552 0 1.056-.216 1.44-.624.384-.408.576-.936.552-1.488zM12 1.224c1.8 0 3.288 1.416 3.384 3.216H8.616C8.712 2.64 10.2 1.224 12 1.224zM19.44 22.2c-.144.144-.36.24-.576.24H5.136c-.216 0-.432-.096-.576-.24s-.216-.36-.216-.576L4.992 5.64h2.424v2.592c0 .336.264.6.6.6s.6-.264.6-.6V5.64h6.816v2.592c0 .336.264.6.6.6s.6-.264.6-.6V5.64h2.4l.648 15.984c.024.216-.048.432-.24.576z"/></svg>';
		$replaced = preg_replace( '#(<a\b[^>]*>)(.*?)(</a>)#s', '$1' . $icon . '$3', $html, 1 );

		return is_string( $replaced ) ? $replaced : $html;
	}

	/**
	 * Card buttons and card swatches run everywhere except the homepage, unless turned on there in settings.
	 *
	 * @return bool
	 */
	private function active_here() {
		$active = ! ( is_front_page() && 'yes' !== get_option( 'kmst_loop_buttons_home', 'no' ) );
		return (bool) apply_filters( 'kmst_loop_buttons_active', $active );
	}

	/**
	 * "Always visible" setting.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function body_class( $classes ) {
		if ( 'always' === apply_filters( 'kmst_loop_buttons_visibility', get_option( 'kmst_loop_buttons_visibility', 'hover' ) ) ) {
			$classes[] = 'kmst-lb-always';
		}
		if ( 'below' === get_option( 'kmst_card_swatches', 'inside' ) ) {
			$classes[] = 'kmst-cs-below';
		}
		return $classes;
	}

	/**
	 * quickview | sizes | '' (leave the button alone).
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private function mode( $product ) {
		if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) || ! $product->is_purchasable() ) {
			return '';
		}

		$id = $product->get_id();
		if ( isset( $this->modes[ $id ] ) ) {
			return $this->modes[ $id ];
		}

		$mode       = '';
		$attributes = $product->get_variation_attributes();

		if ( $attributes ) {
			$has_color = false;
			foreach ( array_keys( $attributes ) as $name ) {
				if ( KMST_Colors::is_color_attribute_name( $name . ' ' . wc_attribute_label( $name, $product ) ) ) {
					$has_color = true;
					break;
				}
			}
			// Only one non-color option (size) can be picked in place; anything else needs the full chooser.
			$mode = ( ! $has_color && 1 === count( $attributes ) ) ? 'sizes' : 'quickview';
		}

		$this->modes[ $id ] = apply_filters( 'kmst_loop_button_mode', $mode, $product );
		return $this->modes[ $id ];
	}

	/**
	 * Button text.
	 *
	 * @param string     $text    Text.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function text( $text, $product = null ) {
		if ( ! $this->active_here() ) {
			return $text;
		}
		switch ( $this->mode( $product ) ) {
			case 'quickview':
				return __( 'Quick view', 'km-storefront-builder' );
			case 'sizes':
				return __( 'Add to cart', 'km-storefront-builder' );
		}
		return $text;
	}

	/**
	 * Screen reader label.
	 *
	 * @param string     $description Description.
	 * @param WC_Product $product     Product.
	 * @return string
	 */
	public function description( $description, $product = null ) {
		if ( ! $this->active_here() ) {
			return $description;
		}
		switch ( $this->mode( $product ) ) {
			case 'quickview':
				/* translators: %s: product name */
				return sprintf( __( 'Quick view “%s”', 'km-storefront-builder' ), wp_strip_all_tags( $product->get_name() ) );
			case 'sizes':
				/* translators: %s: product name */
				return sprintf( __( 'Add to cart: “%s”', 'km-storefront-builder' ), wp_strip_all_tags( $product->get_name() ) );
		}
		return $description;
	}

	/**
	 * Mark the card button and attach the size data.
	 *
	 * @param string     $html    Link HTML.
	 * @param WC_Product $product Product.
	 * @param array      $args    Args.
	 * @return string
	 */
	public function link( $html, $product = null, $args = array() ) {
		if ( ! $this->active_here() ) {
			return $html;
		}
		$mode = $this->mode( $product );

		// Simple products keep WooCommerce's own AJAX add to cart, but get the same bar on the image.
		if ( ! $mode && $product instanceof WC_Product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			$mode = 'simple';
		}
		if ( ! $mode ) {
			return $html;
		}

		$attrs = array(
			'data-kmst-mode' => $mode,
			'data-kmst-text' => 'quickview' === $mode ? __( 'Quick view', 'km-storefront-builder' ) : __( 'Add to cart', 'km-storefront-builder' ),
		);

		if ( 'sizes' === $mode ) {
			$sizes = $this->sizes( $product );
			if ( ! $sizes ) {
				return $html;
			}
			$attrs['data-kmst-label'] = $sizes['label'];
			$attrs['data-kmst-attr']  = $sizes['key'];
			$attrs['data-kmst-sizes'] = wp_json_encode( $sizes['items'] );
		}

		if ( 'off' !== get_option( 'kmst_card_swatches', 'inside' ) && $product->is_type( 'variable' ) ) {
			$colors = $this->colors( $product );
			if ( $colors ) {
				$attrs['data-kmst-color-label'] = $colors['label'];
				$attrs['data-kmst-color-attr']  = $colors['key'];
				$attrs['data-kmst-colors']      = wp_json_encode( $colors['items'] );
			}
		}

		$extra = '';
		foreach ( $attrs as $name => $value ) {
			$extra .= ' ' . $name . '="' . esc_attr( $value ) . '"';
		}

		$html = preg_replace( '/<a\b/i', '<a' . $extra, $html, 1 );
		$html = preg_replace( '/class="([^"]*)"/', 'class="$1 kmst-lb kmst-lb--' . $mode . '"', $html, 1 );

		return $html;
	}

	/**
	 * Size options with their variation and stock, in the product's term order.
	 *
	 * @param WC_Product_Variable $product Product.
	 * @return array|null label, key, items[ {v, l, id, s} ]
	 */
	private function sizes( $product ) {
		$attributes = $product->get_variation_attributes();
		$name       = key( $attributes );
		$options    = current( $attributes );
		$key        = 'attribute_' . sanitize_title( $name );

		$labels = array();
		if ( taxonomy_exists( $name ) ) {
			foreach ( wc_get_product_terms( $product->get_id(), $name, array( 'fields' => 'all' ) ) as $term ) {
				if ( in_array( $term->slug, $options, true ) ) {
					$labels[ $term->slug ] = $term->name;
				}
			}
		} else {
			foreach ( $options as $option ) {
				$labels[ $option ] = $option;
			}
		}

		$variations = $product->get_available_variations();
		$items      = array();

		foreach ( $labels as $value => $label ) {
			$match = null;
			foreach ( $variations as $variation ) {
				$set = isset( $variation['attributes'][ $key ] ) ? (string) $variation['attributes'][ $key ] : '';
				// '' is an "Any size" variation.
				if ( '' === $set || $set === (string) $value || sanitize_title( $set ) === sanitize_title( $value ) ) {
					$match = $variation;
					break;
				}
			}

			$in_stock = $match && ! empty( $match['is_in_stock'] ) && ! empty( $match['is_purchasable'] ) && ! empty( $match['variation_is_active'] );

			$items[] = array(
				'v'  => (string) $value,
				'l'  => (string) apply_filters( 'woocommerce_variation_option_name', $label, null, $name, $product ),
				'id' => $match ? (int) $match['variation_id'] : 0,
				's'  => $in_stock ? 1 : 0,
			);
		}

		if ( ! $items ) {
			return null;
		}

		return array(
			'label' => wc_attribute_label( $name, $product ),
			'key'   => $key,
			'items' => $items,
		);
	}

	/**
	 * Color options for the card swatches: swatch fill, stock, and the variation photo to swap in.
	 *
	 * @param WC_Product_Variable $product Product.
	 * @return array|null label, key, items[ {v, l, c, lt, s, img{src, srcset, sizes}} ]
	 */
	private function colors( $product ) {
		$color_attr = '';
		foreach ( array_keys( $product->get_variation_attributes() ) as $name ) {
			if ( KMST_Colors::is_color_attribute_name( $name . ' ' . wc_attribute_label( $name, $product ) ) ) {
				$color_attr = $name;
				break;
			}
		}
		if ( ! $color_attr ) {
			return null;
		}

		$attributes = $product->get_variation_attributes();
		$options    = $attributes[ $color_attr ];
		$key        = 'attribute_' . sanitize_title( $color_attr );
		$is_tax     = taxonomy_exists( $color_attr );

		$terms = array();
		if ( $is_tax ) {
			foreach ( wc_get_product_terms( $product->get_id(), $color_attr, array( 'fields' => 'all' ) ) as $term ) {
				if ( in_array( $term->slug, $options, true ) ) {
					$terms[ $term->slug ] = $term;
				}
			}
		} else {
			foreach ( $options as $option ) {
				$terms[ $option ] = $option;
			}
		}

		$variations = $product->get_available_variations();
		$parent_img = (int) $product->get_image_id();
		$items      = array();

		foreach ( $terms as $value => $term ) {
			$label = $is_tax ? $term->name : (string) $term;

			$image    = null;
			$in_stock = false;
			foreach ( $variations as $variation ) {
				$set = isset( $variation['attributes'][ $key ] ) ? (string) $variation['attributes'][ $key ] : '';
				if ( $set !== (string) $value && sanitize_title( $set ) !== sanitize_title( $value ) ) {
					continue;
				}
				if ( ! empty( $variation['is_in_stock'] ) && ! empty( $variation['variation_is_active'] ) ) {
					$in_stock = true;
				}
				// First variation of this color that has its own photo.
				if ( ! $image && ! empty( $variation['image']['src'] ) && (int) $variation['image_id'] !== $parent_img ) {
					$full  = wp_get_attachment_image_src( (int) $variation['image_id'], 'full' );
					$image = array(
						'src'    => $full ? $full[0] : $variation['image']['src'],
						'srcset' => (string) wp_get_attachment_image_srcset( (int) $variation['image_id'], 'full' ),
						'sizes'  => isset( $variation['image']['sizes'] ) ? (string) $variation['image']['sizes'] : '',
					);
				}
			}

			$fill = array();
			if ( $is_tax ) {
				$manual = sanitize_hex_color( get_term_meta( $term->term_id, 'kmvs_color', true ) );
				if ( $manual ) {
					$fill = array( $manual );
				}
			}
			if ( ! $fill ) {
				$fill = KMST_Colors::resolve( $is_tax ? KMST_Colors::term_name( $term ) : $label );
			}

			$items[] = array(
				'v'   => (string) $value,
				'l'   => (string) apply_filters( 'woocommerce_variation_option_name', $label, $is_tax ? $term : null, $color_attr, $product ),
				'c'   => $fill ? KMST_Colors::background( $fill ) : '',
				'lt'  => ( 1 === count( $fill ) && KMST_Colors::is_light( $fill[0] ) ) ? 1 : 0,
				's'   => $in_stock ? 1 : 0,
				'img' => $image,
			);
		}

		if ( ! $items ) {
			return null;
		}

		return array(
			'label' => wc_attribute_label( $color_attr, $product ),
			'key'   => $key,
			'items' => $items,
		);
	}

	/**
	 * AJAX: add one variation to the cart and return WooCommerce's cart fragments.
	 */
	public function ajax_add() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- same as WooCommerce's own add_to_cart AJAX endpoint.
		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$attribute    = isset( $_POST['attribute'] ) ? wc_clean( wp_unslash( $_POST['attribute'] ) ) : '';
		$value        = isset( $_POST['value'] ) ? wc_clean( wp_unslash( $_POST['value'] ) ) : '';
		// phpcs:enable

		$variation = $variation_id ? wc_get_product( $variation_id ) : null;

		if ( ! $variation || ! $variation->is_type( 'variation' ) || (int) $variation->get_parent_id() !== $product_id ) {
			wp_send_json(
				array(
					'error'       => true,
					'message'     => __( 'This size is not available.', 'km-storefront-builder' ),
					'product_url' => $product_id ? get_permalink( $product_id ) : '',
				)
			);
		}

		$chosen = $variation->get_variation_attributes();
		if ( $attribute && array_key_exists( $attribute, $chosen ) && '' === $chosen[ $attribute ] ) {
			$chosen[ $attribute ] = $value; // Fill an "Any size" variation.
		}

		$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, 1, $variation_id, $chosen );

		if ( $passed && false !== WC()->cart->add_to_cart( $product_id, 1, $variation_id, $chosen ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => 1 ), true );
			}
			WC_AJAX::get_refreshed_fragments(); // Sends JSON and exits.
		}

		$errors  = wc_get_notices( 'error' );
		$message = '';
		if ( $errors ) {
			$first   = reset( $errors );
			$message = wp_strip_all_tags( is_array( $first ) ? $first['notice'] : $first );
		}
		wc_clear_notices();

		wp_send_json(
			array(
				'error'       => true,
				'message'     => $message ? $message : __( 'Could not add to cart. Please try again.', 'km-storefront-builder' ),
				'product_url' => get_permalink( $product_id ),
			)
		);
	}
}
