<?php
/**
 * Feature switches under WooCommerce > Settings > Products > Store tools.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings section.
 */
final class KMST_Settings {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_filter( 'woocommerce_get_sections_products', array( $this, 'section' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'fields' ), 10, 2 );
	}

	/**
	 * Is a feature on? All features default to on.
	 *
	 * @param string $feature swatches | hide_uncategorized | filter_arrows.
	 * @return bool
	 */
	public static function enabled( $feature ) {
		return 'yes' === get_option( 'kmst_' . $feature, 'yes' );
	}

	/**
	 * Add the section.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public function section( $sections ) {
		$sections['kmst'] = __( 'Store tools', 'km-storefront-builder' );
		return $sections;
	}

	/**
	 * Section fields.
	 *
	 * @param array  $settings Settings.
	 * @param string $section  Section.
	 * @return array
	 */
	public function fields( $settings, $section ) {
		if ( 'kmst' !== $section ) {
			return $settings;
		}

		return array(
			array(
				'title' => __( 'KM Storefront Builder', 'km-storefront-builder' ),
				'type'  => 'title',
				'id'    => 'kmst_options',
			),
			array(
				'title'   => __( 'Variation swatches', 'km-storefront-builder' ),
				'desc'    => __( 'Color circles and label boxes instead of dropdowns on the single product page. Set a color for undetected names in Products > Attributes > Configure terms.', 'km-storefront-builder' ),
				'id'      => 'kmst_swatches',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Hide Uncategorized', 'km-storefront-builder' ),
				'desc'    => __( 'Hide the default "Uncategorized" product category from shop filters and category lists', 'km-storefront-builder' ),
				'id'      => 'kmst_hide_uncategorized',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Filter arrows', 'km-storefront-builder' ),
				'desc'    => __( 'Show up/down arrows instead of +/− on collapsible shop filters', 'km-storefront-builder' ),
				'id'      => 'kmst_filter_arrows',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'    => __( 'Arabic font', 'km-storefront-builder' ),
				'desc'     => __( 'Font for the few places where a heading font has no Arabic letters: the quick view title, mini cart and wishlist names, the empty cart heading and the live search results. A CSS font list, for example: "Alexandria", sans-serif. Empty keeps the theme font everywhere.', 'km-storefront-builder' ),
				'desc_tip' => false,
				'id'       => 'kmst_ar_font',
				'type'     => 'text',
				'default'  => '',
			),
			array(
				'title'   => __( 'KM Icons in Elementor', 'km-storefront-builder' ),
				'desc'    => __( 'Add a "KM Icons" library to the Elementor icon picker (menu, bag, cart, account, search, home, wishlist, filter). Also available as [km_icon name="bag"].', 'km-storefront-builder' ),
				'id'      => 'kmst_km_icons',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Product card buttons', 'km-storefront-builder' ),
				'desc'    => __( 'Replace "Select options": products with colors get "Quick view", products with only sizes get "Add to cart" with an in-place size picker', 'km-storefront-builder' ),
				'id'      => 'kmst_loop_buttons',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'    => __( 'Card button visibility', 'km-storefront-builder' ),
				'desc'     => __( 'Whether the Quick view / Add to cart bar on product images shows all the time or only when hovering. Touch screens always show it.', 'km-storefront-builder' ),
				'desc_tip' => false,
				'id'       => 'kmst_loop_buttons_visibility',
				'type'     => 'select',
				'default'  => 'hover',
				'options'  => array(
					'hover'  => __( 'On hover', 'km-storefront-builder' ),
					'always' => __( 'Always visible', 'km-storefront-builder' ),
				),
			),
			array(
				'title'    => __( 'Quantity box corners', 'km-storefront-builder' ),
				'desc'     => __( 'Corner radius of the quantity box on the product page, the quick view and the cart, so it matches the Add to cart button beside it. For example 10px, or 0 for square corners.', 'km-storefront-builder' ),
				'desc_tip' => false,
				'id'       => 'kmst_qty_radius',
				'type'     => 'text',
				'default'  => '10px',
			),
			array(
				'title'    => __( 'Checkout and cart corners', 'km-storefront-builder' ),
				'desc'     => __( 'Corner radius of the order box, the address fields and the buttons on the checkout and cart pages. For example 10px, or 0 for square corners.', 'km-storefront-builder' ),
				'desc_tip' => false,
				'id'       => 'kmst_form_radius',
				'type'     => 'text',
				'default'  => '10px',
			),
			array(
				'title'    => __( 'Card color swatches', 'km-storefront-builder' ),
				'desc'     => __( 'Color dots on product cards for products with a color option. Choosing a color switches the card photo to that color.', 'km-storefront-builder' ),
				'desc_tip' => false,
				'id'       => 'kmst_card_swatches',
				'type'     => 'select',
				'default'  => 'inside',
				'options'  => array(
					'inside' => __( 'Inside the image, above the button', 'km-storefront-builder' ),
					'below'  => __( 'Below the image', 'km-storefront-builder' ),
					'off'    => __( 'Off', 'km-storefront-builder' ),
				),
			),
			array(
				'title'   => __( 'Card buttons on the homepage', 'km-storefront-builder' ),
				'desc'    => __( 'Show the Quick view / Add to cart buttons and card color swatches on the homepage too (off: the homepage keeps the theme\'s own product cards)', 'km-storefront-builder' ),
				'id'      => 'kmst_loop_buttons_home',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Card hover photo', 'km-storefront-builder' ),
				'desc'    => __( 'On product cards, hovering slides in the product\'s next photo (first gallery image)', 'km-storefront-builder' ),
				'id'      => 'kmst_card_hover_image',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Wishlist icon circle', 'km-storefront-builder' ),
				'desc'    => __( 'Show the wishlist heart on product cards on a white circle (70% opacity)', 'km-storefront-builder' ),
				'id'      => 'kmst_wishlist_circle',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Wishlist icon always visible', 'km-storefront-builder' ),
				'desc'    => __( 'Show the wishlist heart on product cards all the time. Off: it appears on hover, like the theme', 'km-storefront-builder' ),
				'id'      => 'kmst_wishlist_always',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Rounded product labels', 'km-storefront-builder' ),
				'desc'    => __( 'Sale, New, Hot and other product labels get 10px rounded corners, like the card buttons', 'km-storefront-builder' ),
				'id'      => 'kmst_rounded_badges',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Polylang shop languages', 'km-storefront-builder' ),
				'desc'    => __( 'With free Polylang: open the shop, product categories, tags and products under each language\'s URL (e.g. /ar/shop/) instead of redirecting to the default language, so Arabic templates, header and menus are used there. Switched off automatically when Polylang for WooCommerce is active.', 'km-storefront-builder' ),
				'id'      => 'kmst_polylang_shop',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Cart, checkout and account per language', 'km-storefront-builder' ),
				'desc'    => __( 'With free Polylang: WooCommerce uses the cart, checkout, my account and terms page in the visitor\'s language (translated with Polylang). Create the missing pages in WooCommerce > Language pages. Switched off automatically when Polylang for WooCommerce is active.', 'km-storefront-builder' ),
				'id'      => 'kmst_polylang_pages',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Filter bar', 'km-storefront-builder' ),
				'desc'    => __( 'Enable the horizontal filter bar. Add the Horizontal Filters widget (Elementor > Product Archive) above the products, or use the [kmst_filter_bar] shortcode. These settings apply to the shortcode; the widget has its own options.', 'km-storefront-builder' ),
				'id'      => 'kmst_filter_bar',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'kmst_options',
			),
			array(
				'title' => __( 'Filter bar contents', 'km-storefront-builder' ),
				'type'  => 'title',
				'id'    => 'kmst_fb_options',
			),
			array(
				'title'   => __( 'Product Type', 'km-storefront-builder' ),
				'desc'    => __( 'Category filter', 'km-storefront-builder' ),
				'id'      => 'kmst_fb_categories',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'    => __( 'Attributes', 'km-storefront-builder' ),
				'desc'     => __( 'Leave empty to show every product attribute (new attributes appear automatically). Order follows Products > Attributes.', 'km-storefront-builder' ),
				'desc_tip' => false,
				'id'       => 'kmst_fb_attributes',
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'default'  => array(),
				'options'  => $this->attribute_options(),
			),
			array(
				'title'   => __( 'Price', 'km-storefront-builder' ),
				'desc'    => __( 'Price range filter', 'km-storefront-builder' ),
				'id'      => 'kmst_fb_price',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Rating', 'km-storefront-builder' ),
				'desc'    => __( 'Star rating filter (5 stars, 4 stars & up, ...)', 'km-storefront-builder' ),
				'id'      => 'kmst_fb_rating',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'         => __( 'Right side', 'km-storefront-builder' ),
				'desc'          => __( 'Order by', 'km-storefront-builder' ),
				'id'            => 'kmst_fb_orderby',
				'type'          => 'checkbox',
				'default'       => 'yes',
				'checkboxgroup' => 'start',
			),
			array(
				'desc'          => __( 'Share', 'km-storefront-builder' ),
				'id'            => 'kmst_fb_share',
				'type'          => 'checkbox',
				'default'       => 'yes',
				'checkboxgroup' => '',
			),
			array(
				'desc'          => __( 'View (grid columns)', 'km-storefront-builder' ),
				'id'            => 'kmst_fb_view',
				'type'          => 'checkbox',
				'default'       => 'yes',
				'checkboxgroup' => 'end',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'kmst_fb_options',
			),
		);
	}

	/**
	 * Attribute choices for the multiselect.
	 *
	 * @return array name => label
	 */
	private function attribute_options() {
		$options = array();
		if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
			foreach ( wc_get_attribute_taxonomies() as $attribute ) {
				$options[ $attribute->attribute_name ] = $attribute->attribute_label ? $attribute->attribute_label : $attribute->attribute_name;
			}
		}
		return $options;
	}
}
