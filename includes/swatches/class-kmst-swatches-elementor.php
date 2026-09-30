<?php
/**
 * Color swatches as an Elementor widget, so they can be placed anywhere on a product template.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * The swatches the plugin already puts inside WooCommerce's variation form work and are tested: they
 * pick the variation, switch the gallery and keep the Add to cart button in step. Rather than build a
 * second set that would have to repeat all of that, this widget shows the same swatches in its own
 * place: the ones in the form are moved into the widget when the page loads, so there is still one set
 * and one behaviour.
 */
final class KMST_Swatches_Elementor {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/frontend/before_register_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
	}

	/**
	 * Register the script; Elementor enqueues it when the widget is on the page.
	 */
	public function register_assets() {
		if ( wp_script_is( 'kmst-swatches-widget', 'registered' ) ) {
			return;
		}
		wp_register_script( 'kmst-swatches-widget', KMST_URL . 'assets/js/swatches-widget.js', array(), KMST_VERSION, true );
	}

	/**
	 * Register the widget.
	 *
	 * @param \Elementor\Widgets_Manager $manager Widgets manager.
	 */
	public function register_widget( $manager ) {
		require_once KMST_PATH . 'includes/swatches/class-kmst-swatches-widget.php';
		$manager->register( new KMST_Swatches_Widget() );
	}
}
