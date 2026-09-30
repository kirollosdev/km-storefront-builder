<?php
/**
 * The KM Icons library inside Elementor's icon picker.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Elementor's icon picker shows Font Awesome and XStore Icons. This adds a "KM Icons" library with the
 * store's own icons next to them.
 *
 * They are not an icon font: each one is its own SVG in assets/svg, drawn through a CSS mask filled with
 * currentColor. The result behaves like a font icon (it takes the colour, hover colour and size set on
 * the widget) and stays sharp at any size.
 *
 * Outside Elementor: [km_icon name="bag"], or [km_icon name="bag" size="28px"].
 */
final class KMST_Icons {

	const HANDLE = 'kmst-km-icons';

	/**
	 * The set: name used in the class (km-bag) => the name shown in the picker.
	 *
	 * To add one: put the SVG in assets/svg, add a line here, and add its rule to assets/css/km-icons.css
	 * the same way as the others (the SVG url-encoded as the mask image).
	 *
	 * @return array
	 */
	public static function icons() {
		return apply_filters(
			'kmst_km_icons',
			array(
				'menu'   => __( 'Menu', 'km-storefront-builder' ),
				'bag'    => __( 'Bag', 'km-storefront-builder' ),
				'cart'   => __( 'Cart', 'km-storefront-builder' ),
				'user'   => __( 'Account', 'km-storefront-builder' ),
				'search' => __( 'Search', 'km-storefront-builder' ),
				'home'   => __( 'Home', 'km-storefront-builder' ),
				'heart'  => __( 'Wishlist', 'km-storefront-builder' ),
				'filter' => __( 'Filter', 'km-storefront-builder' ),
			)
		);
	}

	/**
	 * The stylesheet that draws them.
	 *
	 * @return string
	 */
	public static function css_url() {
		return KMST_URL . 'assets/css/km-icons.css';
	}

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_filter( 'elementor/icons_manager/additional_tabs', array( $this, 'tab' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue' ) );
		add_shortcode( 'km_icon', array( $this, 'shortcode' ) );
	}

	/**
	 * The library in the picker. Elementor writes <i class="km km-bag"></i>, which the stylesheet draws.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function tab( $tabs ) {
		$tabs['km-icons'] = array(
			'name'          => 'km-icons',
			'label'         => __( 'KM Icons', 'km-storefront-builder' ),
			'labelIcon'     => 'eicon-star',
			'url'           => self::css_url(),
			'enqueue'       => array( self::css_url() ),
			'prefix'        => 'km-',
			'displayPrefix' => 'km',
			'ver'           => KMST_VERSION,
			'fetchJson'     => '',
			'native'        => false,
			'icons'         => array_keys( self::icons() ),
		);
		return $tabs;
	}

	/**
	 * Load the stylesheet on the site, in the editor and in the editor's preview.
	 */
	public function enqueue() {
		wp_enqueue_style( self::HANDLE, self::css_url(), array(), KMST_VERSION );
	}

	/**
	 * [km_icon name="bag" size="28px"] for places outside Elementor.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'name' => 'menu',
				'size' => '',
			),
			$atts,
			'km_icon'
		);

		$name = sanitize_key( $atts['name'] );
		if ( ! isset( self::icons()[ $name ] ) ) {
			return '';
		}

		$this->enqueue();
		$style = '' !== $atts['size'] ? ' style="font-size:' . esc_attr( $atts['size'] ) . '"' : '';

		return '<i class="km km-' . esc_attr( $name ) . '" aria-hidden="true"' . $style . '></i>';
	}
}
