<?php
/**
 * Elementor widget: Horizontal Filters.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Drag-and-drop version of [kmst_filter_bar] with its own filter order, labels and styles.
 */
class KMST_Filter_Bar_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'kmst-horizontal-filters';
	}

	/**
	 * Panel title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Horizontal Filters', 'km-storefront-builder' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-filter';
	}

	/**
	 * Show it in "Product Archive" when that group exists (Elementor Pro / Pro Elements), otherwise WooCommerce or General.
	 *
	 * @return array
	 */
	public function get_categories() {
		$registered = array();
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->elements_manager ) ) {
			$registered = \Elementor\Plugin::$instance->elements_manager->get_categories();
		}
		foreach ( array( 'woocommerce-elements-archive', 'woocommerce-elements' ) as $category ) {
			if ( isset( $registered[ $category ] ) ) {
				return array( $category );
			}
		}
		return array( 'general' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'filter', 'filters', 'horizontal', 'shop', 'attribute', 'price', 'sort', 'woocommerce', 'store' );
	}

	/**
	 * Styles.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'kmst-filter-bar' );
	}

	/**
	 * Scripts.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'kmst-filter-bar' );
	}

	/**
	 * Attribute choices.
	 *
	 * @return array
	 */
	private function attribute_options() {
		$options = array();
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$label                                = $attribute->attribute_label ? $attribute->attribute_label : $attribute->attribute_name;
			$options[ $attribute->attribute_name ] = ucfirst( $label );
		}
		return $options;
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$attributes = $this->attribute_options();

		/* ---------------- Content: filters ---------------- */
		$this->start_controls_section( 'section_filters', array( 'label' => __( 'Filters', 'km-storefront-builder' ) ) );

		$repeater = new Repeater();
		$repeater->add_control(
			'type',
			array(
				'label'   => __( 'Filter', 'km-storefront-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'attribute',
				'options' => array(
					'categories' => __( 'Product Type (categories)', 'km-storefront-builder' ),
					'attribute'  => __( 'Attribute', 'km-storefront-builder' ),
					'price'      => __( 'Price', 'km-storefront-builder' ),
					'rating'     => __( 'Rating', 'km-storefront-builder' ),
				),
			)
		);
		$repeater->add_control(
			'attribute',
			array(
				'label'     => __( 'Attribute', 'km-storefront-builder' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $attributes,
				'default'   => $attributes ? key( $attributes ) : '',
				'condition' => array( 'type' => 'attribute' ),
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'km-storefront-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Default label', 'km-storefront-builder' ),
				'description' => __( 'Leave empty for the default name.', 'km-storefront-builder' ),
			)
		);

		$defaults = array( array( 'type' => 'categories', 'label' => '' ) );
		foreach ( array_keys( $attributes ) as $name ) {
			$defaults[] = array( 'type' => 'attribute', 'attribute' => $name, 'label' => '' );
		}
		$defaults[] = array( 'type' => 'price', 'label' => '' );
		$defaults[] = array( 'type' => 'rating', 'label' => '' );

		$this->add_control(
			'filters',
			array(
				'label'       => __( 'Filters (drag to reorder)', 'km-storefront-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $defaults,
				'title_field' => '{{{ label ? label : ( type === "categories" ? "' . esc_js( __( 'Product Type', 'km-storefront-builder' ) ) . '" : ( type === "price" ? "' . esc_js( __( 'Price', 'km-storefront-builder' ) ) . '" : ( type === "rating" ? "' . esc_js( __( 'Rating', 'km-storefront-builder' ) ) . '" : attribute.charAt( 0 ).toUpperCase() + attribute.slice( 1 ) ) ) ) }}}',
			)
		);

		$this->add_control(
			'show_chips',
			array(
				'label'        => __( 'Selected filter chips', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);
		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Selected count on labels', 'km-storefront-builder' ),
				'description'  => __( 'Shows "Size (2)" when two sizes are chosen.', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'open_on_hover',
			array(
				'label'        => __( 'Open dropdowns on hover', 'km-storefront-builder' ),
				'description'  => __( 'Desktop only. Clicking still opens them, and a clicked dropdown stays open.', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		/* ---------------- Content: right side ---------------- */
		$this->start_controls_section( 'section_tools', array( 'label' => __( 'Order by, Share, View', 'km-storefront-builder' ) ) );

		foreach (
			array(
				'orderby' => __( 'Order by', 'km-storefront-builder' ),
				'share'   => __( 'Share', 'km-storefront-builder' ),
				'view'    => __( 'View', 'km-storefront-builder' ),
			) as $key => $title
		) {
			$this->add_control(
				'show_' . $key,
				array(
					'label'        => $title,
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
				)
			);
			$this->add_control(
				'label_' . $key,
				array(
					'label'       => __( 'Label', 'km-storefront-builder' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => $title,
					'condition'   => array( 'show_' . $key => 'yes' ),
				)
			);
		}

		$this->end_controls_section();

		/* ---------------- Content: texts ---------------- */
		$this->start_controls_section( 'section_texts', array( 'label' => __( 'Texts', 'km-storefront-builder' ) ) );

		$this->add_control(
			'texts_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Leave a field empty to use the default text (translated automatically on Arabic pages).', 'km-storefront-builder' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		foreach ( KMST_Filter_Bar::text_defaults() as $key => $text ) {
			$this->add_control(
				'text_' . $key,
				array(
					'label'       => $text['title'],
					'type'        => Controls_Manager::TEXT,
					'placeholder' => $text['default'],
					'separator'   => 'sort_menu_order' === $key ? 'before' : 'none',
				)
			);
		}

		$this->end_controls_section();

		/* ---------------- Style: bar ---------------- */
		$this->start_controls_section(
			'section_style_bar',
			array(
				'label' => __( 'Bar', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'filters_gap',
			array(
				'label'      => __( 'Space between filters', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .kmst-fb .kmst-fb-filters' => 'column-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .kmst-fb .kmst-fb-tools'   => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'bar_height',
			array(
				'label'      => __( 'Minimum height', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 30, 'max' => 120 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmst-fb .kmst-fb-bar' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'bar_padding',
			array(
				'label'      => __( 'Padding', 'km-storefront-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmst-fb .kmst-fb-bar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'bar_border',
				'selector' => '{{WRAPPER}} .kmst-fb .kmst-fb-bar',
			)
		);
		$this->add_control(
			'bar_background',
			array(
				'label'     => __( 'Background', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb .kmst-fb-bar' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: labels (Categories, Color, ... Order by, Share, View) ---------------- */
		$this->start_controls_section(
			'section_style_toggles',
			array(
				'label' => __( 'Filter labels', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$toggle = '{{WRAPPER}} .kmst-fb .kmst-fb-toggle';

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'toggle_typography',
				'selector' => $toggle,
			)
		);

		$this->start_controls_tabs( 'toggle_tabs' );

		$states = array(
			'normal' => array( __( 'Normal', 'km-storefront-builder' ), $toggle ),
			'hover'  => array( __( 'Hover', 'km-storefront-builder' ), $toggle . ':hover' ),
			'open'   => array( __( 'Open / active', 'km-storefront-builder' ), '{{WRAPPER}} .kmst-fb .kmst-fb-item.is-open > .kmst-fb-toggle, {{WRAPPER}} .kmst-fb .kmst-fb-item.is-active > .kmst-fb-toggle' ),
		);

		foreach ( $states as $state => $info ) {
			$this->start_controls_tab( 'toggle_tab_' . $state, array( 'label' => $info[0] ) );

			$this->add_control(
				'toggle_color_' . $state,
				array(
					'label'     => __( 'Text color', 'km-storefront-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'color: {{VALUE}};' ),
				)
			);
			$this->add_control(
				'toggle_bg_' . $state,
				array(
					'label'     => __( 'Background color', 'km-storefront-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'background-color: {{VALUE}};' ),
				)
			);
			$this->add_control(
				'toggle_border_color_' . $state,
				array(
					'label'     => __( 'Border color', 'km-storefront-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'border-color: {{VALUE}};' ),
				)
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'toggle_border',
				'selector'  => $toggle,
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'toggle_radius',
			array(
				'label'      => __( 'Border radius', 'km-storefront-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( $toggle => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'toggle_padding',
			array(
				'label'      => __( 'Padding', 'km-storefront-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $toggle => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'toggle_icon_size',
			array(
				'label'      => __( 'Icon size', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 32 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmst-fb .kmst-fb-toggle svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'toggle_icon_gap',
			array(
				'label'      => __( 'Space between text and icon', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'selectors'  => array( $toggle => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: dropdowns ---------------- */
		$this->start_controls_section(
			'section_style_panel',
			array(
				'label' => __( 'Dropdowns', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'accent_color',
			array(
				'label'       => __( 'Accent color', 'km-storefront-builder' ),
				'description' => __( 'Checked boxes, selected sizes, slider and Apply button.', 'km-storefront-builder' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( '{{WRAPPER}} .kmst-fb' => '--kmst-fb-ink: {{VALUE}}; --kmst-fb-accent: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'line_color',
			array(
				'label'     => __( 'Border color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb' => '--kmst-fb-line: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'panel_background',
			array(
				'label'     => __( 'Background', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb' => '--kmst-fb-bg: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'panel_width',
			array(
				'label'      => __( 'Width', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 180, 'max' => 520 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmst-fb .kmst-fb-panel' => 'min-width: {{SIZE}}{{UNIT}}; max-width: min({{SIZE}}{{UNIT}}, calc(100vw - 24px));' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'option_typography',
				'label'    => __( 'Option typography', 'km-storefront-builder' ),
				'selector' => '{{WRAPPER}} .kmst-fb .kmst-fb-opt, {{WRAPPER}} .kmst-fb .kmst-fb-sort',
			)
		);
		$this->add_control(
			'option_color',
			array(
				'label'     => __( 'Option color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb .kmst-fb-opt:not(:has(input:checked)), {{WRAPPER}} .kmst-fb .kmst-fb-sort' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		/* ---------------- Style: Clear / Apply buttons ---------------- */
		$this->start_controls_section(
			'section_style_buttons',
			array(
				'label' => __( 'Clear / Apply buttons', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .kmst-fb .kmst-fb-actions button',
			)
		);

		foreach (
			array(
				'apply' => __( 'Apply', 'km-storefront-builder' ),
				'clear' => __( 'Clear', 'km-storefront-builder' ),
			) as $button => $title
		) {
			$base = '{{WRAPPER}} .kmst-fb .kmst-fb-actions .kmst-fb-' . $button;

			$this->add_control(
				'heading_' . $button,
				array(
					'label'     => $title,
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->start_controls_tabs( $button . '_tabs' );
			foreach (
				array(
					'normal' => array( __( 'Normal', 'km-storefront-builder' ), $base ),
					'hover'  => array( __( 'Hover', 'km-storefront-builder' ), $base . ':hover' ),
				) as $state => $info
			) {
				$this->start_controls_tab( $button . '_tab_' . $state, array( 'label' => $info[0] ) );
				$this->add_control(
					$button . '_color_' . $state,
					array(
						'label'     => __( 'Text color', 'km-storefront-builder' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array( $info[1] => 'color: {{VALUE}};' ),
					)
				);
				$this->add_control(
					$button . '_bg_' . $state,
					array(
						'label'     => __( 'Background color', 'km-storefront-builder' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array( $info[1] => 'background-color: {{VALUE}};' ),
					)
				);
				$this->add_control(
					$button . '_border_' . $state,
					array(
						'label'     => __( 'Border color', 'km-storefront-builder' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array( $info[1] => 'border-color: {{VALUE}};' ),
					)
				);
				$this->end_controls_tab();
			}
			$this->end_controls_tabs();
		}

		$this->add_responsive_control(
			'button_radius',
			array(
				'label'      => __( 'Border radius', 'km-storefront-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .kmst-fb .kmst-fb-actions button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'button_height',
			array(
				'label'      => __( 'Height', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 28, 'max' => 70 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmst-fb .kmst-fb-actions button' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: phone toolbar ---------------- */
		$this->start_controls_section(
			'section_style_mobile',
			array(
				'label' => __( 'Phone toolbar', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'mobile_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Below 768px the bar becomes a Filter / Sort toolbar that opens bottom sheets. Switch Elementor to mobile view to preview it.', 'km-storefront-builder' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control(
			'label_filters',
			array(
				'label'       => __( 'Filter button label', 'km-storefront-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Filters', 'km-storefront-builder' ),
			)
		);

		$mbtn = '{{WRAPPER}} .kmst-fb .kmst-fb-mbtn, {{WRAPPER}} .kmst-fb .kmst-fb-micon';

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'mobile_typography',
				'selector' => '{{WRAPPER}} .kmst-fb .kmst-fb-mbtn',
			)
		);
		$this->add_control(
			'mobile_color',
			array(
				'label'     => __( 'Text color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $mbtn => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'mobile_bg',
			array(
				'label'     => __( 'Background color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $mbtn => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'mobile_border',
			array(
				'label'     => __( 'Border color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $mbtn => 'border-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'mobile_badge',
			array(
				'label'     => __( 'Count badge color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb .kmst-fb-mcount' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'mobile_radius',
			array(
				'label'      => __( 'Border radius', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'selectors'  => array( $mbtn => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'mobile_height',
			array(
				'label'      => __( 'Height', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 36, 'max' => 64 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .kmst-fb .kmst-fb-mbtn'  => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .kmst-fb .kmst-fb-micon' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; flex-basis: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: chips ---------------- */
		$this->start_controls_section(
			'section_style_chips',
			array(
				'label'     => __( 'Selected filter chips', 'km-storefront-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_chips' => 'yes' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'chip_typography',
				'selector' => '{{WRAPPER}} .kmst-fb .kmst-fb-chip, {{WRAPPER}} .kmst-fb .kmst-fb-clear-all',
			)
		);
		$this->add_control(
			'chip_color',
			array(
				'label'     => __( 'Text color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb .kmst-fb-chip, {{WRAPPER}} .kmst-fb .kmst-fb-clear-all' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'chip_bg',
			array(
				'label'     => __( 'Background color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb .kmst-fb-chip' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'chip_border',
			array(
				'label'     => __( 'Border color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-fb .kmst-fb-chip' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'chip_radius',
			array(
				'label'      => __( 'Border radius', 'km-storefront-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmst-fb .kmst-fb-chip' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$bar = KMST_Filter_Bar::get();
		if ( ! $bar ) {
			return;
		}

		$s       = $this->get_settings_for_display();
		$filters = array();
		foreach ( (array) $s['filters'] as $row ) {
			$filters[] = array(
				'type'      => isset( $row['type'] ) ? $row['type'] : '',
				'attribute' => isset( $row['attribute'] ) ? $row['attribute'] : '',
				'label'     => isset( $row['label'] ) ? $row['label'] : '',
			);
		}

		$labels = array(
			'filters' => $s['label_filters'],
			'orderby' => $s['label_orderby'],
			'share'   => $s['label_share'],
			'view'    => $s['label_view'],
		);
		foreach ( array_keys( KMST_Filter_Bar::text_defaults() ) as $key ) {
			$labels[ $key ] = isset( $s[ 'text_' . $key ] ) ? $s[ 'text_' . $key ] : '';
		}

		$html = $bar->render(
			array(
				'filters'    => $filters,
				'orderby'    => 'yes' === $s['show_orderby'],
				'share'      => 'yes' === $s['show_share'],
				'view'       => 'yes' === $s['show_view'],
				'chips'      => 'yes' === $s['show_chips'],
				'show_count' => 'yes' === $s['show_count'],
				'hover'      => 'yes' === $s['open_on_hover'],
				'labels'     => $labels,
			)
		);

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in KMST_Filter_Bar::render().
	}
}
