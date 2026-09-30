<?php
/**
 * Elementor widget: Color Swatches.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

/**
 * Shows the product's color (or size) swatches wherever it is placed on a product template. The
 * swatches themselves are the ones WooCommerce's variation form already holds: this widget takes them
 * over, so picking a color here does everything picking it in the form does.
 */
class KMST_Swatches_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'kmst-color-swatches';
	}

	/**
	 * Panel title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Color Swatches', 'km-storefront-builder' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-product-add-to-cart';
	}

	/**
	 * Where it sits in the panel.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'woocommerce-elements', 'general' );
	}

	/**
	 * Words that find it in the panel search.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'swatch', 'color', 'colour', 'variation', 'attribute', 'product' );
	}

	/**
	 * Scripts this widget needs.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'kmst-swatches-widget' );
	}

	/**
	 * The widget shows live product data, so it is never cached as static HTML.
	 *
	 * @return bool
	 */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content',
			array(
				'label' => __( 'Swatches', 'km-storefront-builder' ),
			)
		);

		$this->add_control(
			'attribute',
			array(
				'label'       => __( 'Attribute', 'km-storefront-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'pa_color',
				'description' => __( 'Leave empty for the first attribute of the product (usually the color). Otherwise the attribute name, for example pa_color or pa_size.', 'km-storefront-builder' ),
			)
		);

		$this->add_control(
			'show_label',
			array(
				'label'        => __( 'Show the chosen color name', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'description'  => __( 'The name of the color that is selected, shown above the swatches.', 'km-storefront-builder' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'hide_original',
			array(
				'label'        => __( 'Hide the row in the Add to cart form', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'description'  => __( 'Off leaves the Add to cart form exactly as it is and shows the swatches here as well.', 'km-storefront-builder' ),
				'return_value' => 'yes',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'km-storefront-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Start', 'km-storefront-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'km-storefront-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'End', 'km-storefront-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .kmvs-swatches' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .kmvs-label'    => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Style', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'size',
			array(
				'label'      => __( 'Swatch size', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmvs-swatch--color' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Space between', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmvs-swatches' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Corner radius', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
					'%'  => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmvs-swatch' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$attribute = sanitize_text_field( (string) $settings['attribute'] );
		$editing   = \Elementor\Plugin::$instance->editor->is_edit_mode();

		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;
		if ( ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) && ! $editing ) {
			return;
		}

		$classes = 'kmst-sw-widget';
		if ( 'yes' !== $settings['show_label'] ) {
			$classes .= ' kmst-sw-widget--no-label';
		}

		printf(
			'<div class="%1$s" data-attribute="%2$s" data-hide-original="%3$s"></div>',
			esc_attr( $classes ),
			esc_attr( $attribute ),
			esc_attr( 'yes' === $settings['hide_original'] ? '1' : '0' )
		);

		if ( $editing ) {
			echo '<p class="kmst-sw-widget-hint" style="opacity:.6;font-size:12px;margin:6px 0 0">'
				. esc_html__( 'The swatches of the product appear here on the site. This note is only shown while editing.', 'km-storefront-builder' )
				. '</p>';
		}
	}
}
