<?php
/**
 * Category Slider: assets and widget registration.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * A slider of hand-made cards (image + title + link). Nothing is read from WooCommerce, so the cards
 * can point anywhere: a category, a landing page or an external link.
 */
final class KMST_Category_Slider {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/frontend/before_register_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		add_action( 'wp_loaded', array( $this, 'refresh_elementor_css' ) );
	}

	/**
	 * After a plugin update, have Elementor rebuild its cached page CSS once, so widget styles saved with
	 * older class names (2.5.0 and earlier used .kmst-cs) apply straight away.
	 */
	public function refresh_elementor_css() {
		if ( KMST_VERSION === get_option( 'kmst_elementor_css_version' ) ) {
			return;
		}
		if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->files_manager ) ) {
			return;
		}
		\Elementor\Plugin::$instance->files_manager->clear_cache();
		update_option( 'kmst_elementor_css_version', KMST_VERSION, false );
	}

	/**
	 * Register the styles and script; Elementor enqueues them when the widget is on the page.
	 */
	public function register_assets() {
		if ( wp_script_is( 'kmst-category-slider', 'registered' ) ) {
			return;
		}
		wp_register_style( 'kmst-category-slider', KMST_URL . 'assets/css/category-slider.css', array(), KMST_VERSION );
		wp_register_script( 'kmst-category-slider', KMST_URL . 'assets/js/category-slider.js', array(), KMST_VERSION, true );
	}

	/**
	 * Register the widget.
	 *
	 * @param \Elementor\Widgets_Manager $manager Widgets manager.
	 */
	public function register_widget( $manager ) {
		require_once KMST_PATH . 'includes/category-slider/class-kmst-category-slider-widget.php';
		$manager->register( new KMST_Category_Slider_Widget() );
	}
}
