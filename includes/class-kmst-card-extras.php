<?php
/**
 * Product card extras: second photo sliding in on hover, wishlist icon on a white circle.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Applies to every XStore product card rendered by an Elementor widget (shop grid, carousels, related products).
 */
final class KMST_Card_Extras {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );

		if ( KMST_Settings::enabled( 'card_hover_image' ) ) {
			add_filter( 'elementor/widget/render_content', array( $this, 'add_hover_images' ), 25, 2 );
		}
	}

	/**
	 * Styles, site-wide (product cards can be on any page).
	 */
	public function assets() {
		wp_enqueue_style( 'kmst-card-extras', KMST_URL . 'assets/css/card-extras.css', array(), KMST_VERSION );

		// Corner radius of the quantity box, to match whatever radius the Add to cart button has.
		$vars   = '';
		$radius = trim( preg_replace( '/[^0-9a-z%. ]/i', '', (string) get_option( 'kmst_qty_radius', '10px' ) ) );
		if ( '' !== $radius ) {
			$vars .= '--kmst-qty-radius:' . $radius . ';';
		}

		// Corner radius of the checkout and cart boxes, fields and buttons.
		$form = trim( preg_replace( '/[^0-9a-z%. ]/i', '', (string) get_option( 'kmst_form_radius', '10px' ) ) );
		if ( '' !== $form ) {
			$vars .= '--kmst-form-radius:' . $form . ';';
		}

		// Some heading fonts have no Arabic letters, so a few places can be given one that has.
		$font = trim( preg_replace( '/[^A-Za-z0-9 ,\'"_-]/', '', (string) get_option( 'kmst_ar_font', '' ) ) );
		if ( '' !== $font ) {
			$vars .= '--kmst-ar-font:' . $font . ';';
		}

		if ( '' !== $vars ) {
			wp_add_inline_style( 'kmst-card-extras', ':root{' . $vars . '}' );
		}
	}

	/**
	 * Feature switches as body classes, so the CSS only applies when turned on.
	 *
	 * @param array $classes Classes.
	 * @return array
	 */
	public function body_class( $classes ) {
		if ( KMST_Settings::enabled( 'wishlist_circle' ) ) {
			$classes[] = 'kmst-wishlist-circle';
		}
		if ( 'yes' === get_option( 'kmst_wishlist_always', 'no' ) ) {
			$classes[] = 'kmst-wishlist-always';
		}
		if ( KMST_Settings::enabled( 'rounded_badges' ) ) {
			$classes[] = 'kmst-rounded-badges';
		}
		return $classes;
	}

	/**
	 * Add each product's first gallery photo inside its card image link, for the hover slide.
	 *
	 * @param string $content Widget HTML.
	 * @param object $widget  Widget.
	 * @return string
	 */
	public function add_hover_images( $content, $widget = null ) {
		if ( ! is_string( $content ) || false === strpos( $content, 'etheme-product-grid-image' ) ) {
			return $content;
		}

		$parts = preg_split( '/(<div class="etheme-product-grid-item[^"]*"[^>]*>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! $parts || count( $parts ) < 3 ) {
			return $content;
		}

		$out = $parts[0];
		for ( $i = 1; $i < count( $parts ); $i += 2 ) {
			$open = $parts[ $i ];
			$body = isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '';

			if ( preg_match( '/\bpost-(\d+)\b/', $open, $id ) ) {
				$body = $this->add_hover_image_to_card( $body, (int) $id[1] );
			}

			$out .= $open . $body;
		}

		return $out;
	}

	/**
	 * One card.
	 *
	 * @param string $body       Card HTML after its opening tag.
	 * @param int    $product_id Product ID.
	 * @return string
	 */
	private function add_hover_image_to_card( $body, $product_id ) {
		// The image link: the first <a> right inside the image box.
		if ( ! preg_match( '#<div class="etheme-product-grid-image">\s*(<a\b[^>]*>)#', $body, $m, PREG_OFFSET_CAPTURE ) ) {
			return $body;
		}
		$open    = $m[1][0];
		$open_at = $m[1][1];
		$close   = strpos( $body, '</a>', $open_at );
		if ( false === $close ) {
			return $body;
		}

		$inside = substr( $body, $open_at + strlen( $open ), $close - $open_at - strlen( $open ) );
		if ( false !== strpos( $inside, 'kmst-hover-img' ) || false === strpos( $inside, '<img' ) ) {
			return $body;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product instanceof WC_Product ) {
			return $body;
		}
		$gallery = $product->get_gallery_image_ids();
		if ( empty( $gallery ) ) {
			return $body;
		}

		$hover = wp_get_attachment_image(
			(int) reset( $gallery ),
			'full',
			false,
			array(
				'class'       => 'kmst-hover-img',
				'alt'         => '',
				'loading'     => 'lazy',
				'decoding'    => 'async',
				'aria-hidden' => 'true',
			)
		);
		if ( ! $hover ) {
			return $body;
		}

		if ( preg_match( '/\sclass="[^"]*"/', $open ) ) {
			$new_open = preg_replace( '/\sclass="([^"]*)"/', ' class="$1 kmst-hover-swap"', $open, 1 );
		} else {
			$new_open = preg_replace( '/^<a\b/', '<a class="kmst-hover-swap"', $open, 1 );
		}

		return substr( $body, 0, $open_at ) . $new_open . $inside . $hover . substr( $body, $close );
	}
}
