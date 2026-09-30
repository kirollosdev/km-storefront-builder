<?php
/**
 * Up/down arrows on XStore's collapsible sidebar filters.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * XStore draws the toggle as an icon-font "+" / "−" on .widget-has-toggle > .widget-title::after.
 * This swaps it for a CSS chevron that doesn't depend on the icon font.
 */
final class KMST_Filter_Arrows {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 20 );
	}

	/**
	 * Filters live on shop, category, tag, attribute and search pages, and in off-canvas sidebars, so load everywhere. It's tiny.
	 */
	public function assets() {
		wp_enqueue_style( 'kmst-filter-arrows', KMST_URL . 'assets/css/filter-arrows.css', array(), KMST_VERSION );
	}
}
