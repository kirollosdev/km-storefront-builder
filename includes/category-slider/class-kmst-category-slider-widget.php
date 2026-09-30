<?php
/**
 * Elementor widget: Category Slider (static cards).
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Image + title cards in a horizontal slider. Every card is one link, and the image zooms in on hover.
 */
class KMST_Category_Slider_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'kmst-category-slider';
	}

	/**
	 * Panel title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Category Slider', 'km-storefront-builder' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-slider-push';
	}

	/**
	 * Panel group.
	 *
	 * @return array
	 */
	public function get_categories() {
		$registered = array();
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->elements_manager ) ) {
			$registered = \Elementor\Plugin::$instance->elements_manager->get_categories();
		}
		return isset( $registered['woocommerce-elements'] ) ? array( 'woocommerce-elements', 'general' ) : array( 'general' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'category', 'categories', 'slider', 'carousel', 'shop by', 'image', 'store' );
	}

	/**
	 * Styles.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'kmst-category-slider' );
	}

	/**
	 * Scripts.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'kmst-category-slider' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {

		/* ---------------- Content: heading ---------------- */
		$this->start_controls_section( 'section_heading', array( 'label' => __( 'Heading', 'km-storefront-builder' ) ) );

		$this->add_control(
			'heading_title',
			array(
				'label'       => __( 'Title', 'km-storefront-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'Shop by category', 'km-storefront-builder' ),
				'description' => __( 'Leave empty for no heading.', 'km-storefront-builder' ),
			)
		);
		$this->add_control(
			'heading_subtitle',
			array(
				'label'       => __( 'Subtitle', 'km-storefront-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);
		$this->add_control(
			'heading_tag',
			array(
				'label'   => __( 'Title HTML tag', 'km-storefront-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
				),
			)
		);

		$this->end_controls_section();

		/* ---------------- Content: cards ---------------- */
		$this->start_controls_section( 'section_items', array( 'label' => __( 'Cards', 'km-storefront-builder' ) ) );

		$item = new Repeater();

		$item->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'km-storefront-builder' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ),
			)
		);
		$item->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'km-storefront-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Category', 'km-storefront-builder' ),
				'label_block' => true,
			)
		);
		$item->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'km-storefront-builder' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'description' => __( 'The whole card opens this link.', 'km-storefront-builder' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Cards', 'km-storefront-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $item->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => __( 'Bras', 'km-storefront-builder' ) ),
					array( 'title' => __( 'Panties', 'km-storefront-builder' ) ),
					array( 'title' => __( 'Pyjamas', 'km-storefront-builder' ) ),
					array( 'title' => __( 'Gowns', 'km-storefront-builder' ) ),
					array( 'title' => __( 'Home Wear', 'km-storefront-builder' ) ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'thumb',
				'default' => 'large',
			)
		);

		$this->end_controls_section();

		/* ---------------- Content: slider ---------------- */
		$this->start_controls_section( 'section_slider', array( 'label' => __( 'Slider', 'km-storefront-builder' ) ) );

		$this->add_responsive_control(
			'per_view',
			array(
				'label'              => __( 'Cards per view', 'km-storefront-builder' ),
				'description'        => __( 'Halves show a part of the next card, like 4.5.', 'km-storefront-builder' ),
				'type'               => Controls_Manager::SLIDER,
				'range'              => array( 'px' => array( 'min' => 1, 'max' => 8, 'step' => 0.5 ) ),
				'default'            => array( 'size' => 4.5 ),
				'tablet_default'     => array( 'size' => 3.5 ),
				'mobile_default'     => array( 'size' => 1.8 ),
				'selectors'          => array( '{{WRAPPER}} .kmst-csl' => '--kmst-csl-per: {{SIZE}};' ),
				'frontend_available' => true,
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Space between cards', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'size' => 16 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl' => '--kmst-csl-gap: {{SIZE}}px;' ),
			)
		);
		$this->add_control(
			'show_arrows',
			array(
				'label'        => __( 'Arrows', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'arrows_position',
			array(
				'label'     => __( 'Arrows position', 'km-storefront-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'top',
				'options'   => array(
					'top'   => __( 'In the heading row', 'km-storefront-builder' ),
					'sides' => __( 'Floating on the sides of the cards', 'km-storefront-builder' ),
				),
				'condition' => array( 'show_arrows' => 'yes' ),
			)
		);
		$this->add_control(
			'arrow_step',
			array(
				'label'     => __( 'Arrow click moves', 'km-storefront-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'end',
				'options'   => array(
					'end'  => __( 'To the end / back to the start', 'km-storefront-builder' ),
					'page' => __( 'One screen of cards', 'km-storefront-builder' ),
					'card' => __( 'One card', 'km-storefront-builder' ),
				),
				'condition' => array( 'show_arrows' => 'yes' ),
			)
		);
		$this->add_control(
			'arrows_align',
			array(
				'label'     => __( 'Arrows alignment', 'km-storefront-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'start',
				'options'   => array(
					'start'  => array( 'title' => __( 'Start', 'km-storefront-builder' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Center', 'km-storefront-builder' ), 'icon' => 'eicon-text-align-center' ),
					'end'    => array( 'title' => __( 'End', 'km-storefront-builder' ), 'icon' => 'eicon-text-align-right' ),
				),
				'description' => __( 'Used when there is no heading; with a heading the arrows sit on the opposite side of it.', 'km-storefront-builder' ),
				'selectors' => array( '{{WRAPPER}} .kmst-csl--no-heading .kmst-csl-head' => 'justify-content: {{VALUE}};' ),
				'condition' => array( 'show_arrows' => 'yes', 'arrows_position' => 'top', 'heading_title' => '' ),
			)
		);
		$this->add_control(
			'drag',
			array(
				'label'        => __( 'Swipe / drag', 'km-storefront-builder' ),
				'description'  => __( 'Cards can always be swiped on touch screens; this adds dragging with the mouse.', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: heading ---------------- */
		$this->start_controls_section(
			'section_style_heading',
			array(
				'label' => __( 'Heading', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'label'    => __( 'Title typography', 'km-storefront-builder' ),
				'selector' => '{{WRAPPER}} .kmst-csl-heading-title',
			)
		);
		$this->add_control(
			'heading_color',
			array(
				'label'     => __( 'Title color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-heading-title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'subtitle_typography',
				'label'     => __( 'Subtitle typography', 'km-storefront-builder' ),
				'selector'  => '{{WRAPPER}} .kmst-csl-heading-sub',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'subtitle_color',
			array(
				'label'     => __( 'Subtitle color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-heading-sub' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'heading_sub_gap',
			array(
				'label'      => __( 'Space between title and subtitle', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'size' => 4 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-heading' => 'gap: {{SIZE}}px;' ),
			)
		);
		$this->add_responsive_control(
			'head_gap',
			array(
				'label'      => __( 'Space below the heading row', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'size' => 20 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-head' => 'margin-bottom: {{SIZE}}px;' ),
			)
		);
		$this->add_control(
			'head_stack',
			array(
				'label'        => __( 'Arrows under the heading on phones', 'km-storefront-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: card ---------------- */
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'Card', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => __( 'Image height', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'min' => 120, 'max' => 700 ) ),
				'default'    => array( 'size' => 330 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-media' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'image_fit',
			array(
				'label'     => __( 'Image fit', 'km-storefront-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'contain',
				'options'   => array(
					'contain' => __( 'Contain (whole image)', 'km-storefront-builder' ),
					'cover'   => __( 'Cover (fill the box)', 'km-storefront-builder' ),
				),
				'selectors' => array( '{{WRAPPER}} .kmst-csl-media img' => 'object-fit: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'media_background',
			array(
				'label'     => __( 'Image background', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F5F5F5',
				'selectors' => array( '{{WRAPPER}} .kmst-csl-media' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'media_padding',
			array(
				'label'      => __( 'Image padding', 'km-storefront-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-media' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'media_radius',
			array(
				'label'      => __( 'Corner radius', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-media' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'media_border',
				'selector' => '{{WRAPPER}} .kmst-csl-media',
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'media_shadow',
				'selector' => '{{WRAPPER}} .kmst-csl-media',
			)
		);
		$this->add_control(
			'zoom',
			array(
				'label'       => __( 'Hover zoom', 'km-storefront-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 1, 'max' => 1.5, 'step' => 0.01 ) ),
				'default'     => array( 'size' => 1.08 ),
				'description' => __( '1 turns the zoom off.', 'km-storefront-builder' ),
				'selectors'   => array( '{{WRAPPER}} .kmst-csl' => '--kmst-csl-zoom: {{SIZE}};' ),
			)
		);
		$this->add_control(
			'zoom_speed',
			array(
				'label'      => __( 'Zoom speed', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'ms' ),
				'range'      => array( 'ms' => array( 'min' => 100, 'max' => 2000, 'step' => 50 ) ),
				'default'    => array( 'size' => 500, 'unit' => 'ms' ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl' => '--kmst-csl-zoom-speed: {{SIZE}}ms;' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: title ---------------- */
		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => __( 'Title', 'km-storefront-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .kmst-csl-title',
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'title_hover_color',
			array(
				'label'     => __( 'Hover color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-card:hover .kmst-csl-title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'title_gap',
			array(
				'label'      => __( 'Space above the title', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'size' => 12 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-title' => 'margin-top: {{SIZE}}px;' ),
			)
		);
		$this->add_responsive_control(
			'title_align',
			array(
				'label'     => __( 'Alignment', 'km-storefront-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'center',
				'options'   => array(
					'left'   => array( 'title' => __( 'Left', 'km-storefront-builder' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Center', 'km-storefront-builder' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => __( 'Right', 'km-storefront-builder' ), 'icon' => 'eicon-text-align-right' ),
				),
				'selectors' => array( '{{WRAPPER}} .kmst-csl-title' => 'text-align: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: arrows ---------------- */
		$this->start_controls_section(
			'section_style_arrows',
			array(
				'label'     => __( 'Arrows', 'km-storefront-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_arrows' => 'yes' ),
			)
		);

		$this->add_control(
			'arrow_size',
			array(
				'label'      => __( 'Button size', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 20, 'max' => 90 ) ),
				'default'    => array( 'size' => 36 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl' => '--kmst-csl-arrow-size: {{SIZE}}px;' ),
			)
		);
		$this->add_control(
			'arrow_icon_size',
			array(
				'label'      => __( 'Icon size', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 40 ) ),
				'default'    => array( 'size' => 18 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl' => '--kmst-csl-arrow-icon: {{SIZE}}px;' ),
			)
		);
		$this->add_control(
			'arrow_gap',
			array(
				'label'      => __( 'Space between the buttons', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'size' => 8 ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-nav' => 'gap: {{SIZE}}px;' ),
				'condition'  => array( 'arrows_position' => 'top' ),
			)
		);
		$this->start_controls_tabs( 'arrow_tabs' );

		$this->start_controls_tab( 'arrow_normal', array( 'label' => __( 'Normal', 'km-storefront-builder' ) ) );
		$this->add_control(
			'arrow_color',
			array(
				'label'     => __( 'Icon color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-arrow' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrow_bg',
			array(
				'label'     => __( 'Background', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-arrow' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'arrow_hover', array( 'label' => __( 'Hover', 'km-storefront-builder' ) ) );
		$this->add_control(
			'arrow_color_hover',
			array(
				'label'     => __( 'Icon color', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-arrow:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrow_bg_hover',
			array(
				'label'     => __( 'Background', 'km-storefront-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmst-csl-arrow:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'arrow_border',
				'selector' => '{{WRAPPER}} .kmst-csl-arrow',
			)
		);
		$this->add_control(
			'arrow_radius',
			array(
				'label'      => __( 'Corner radius', 'km-storefront-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmst-csl-arrow' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = ! empty( $settings['items'] ) ? (array) $settings['items'] : array();
		if ( ! $items ) {
			return;
		}

		$arrows = 'yes' === ( isset( $settings['show_arrows'] ) ? $settings['show_arrows'] : 'yes' );
		$sides  = $arrows && 'sides' === ( isset( $settings['arrows_position'] ) ? $settings['arrows_position'] : 'top' );

		$heading_title = isset( $settings['heading_title'] ) ? trim( (string) $settings['heading_title'] ) : '';
		$subtitle      = isset( $settings['heading_subtitle'] ) ? trim( (string) $settings['heading_subtitle'] ) : '';
		$has_heading   = '' !== $heading_title || '' !== $subtitle;

		$classes = 'kmst-csl';
		$classes .= $sides ? ' kmst-csl--arrows-sides' : '';
		$classes .= 'yes' === ( isset( $settings['drag'] ) ? $settings['drag'] : 'yes' ) ? ' kmst-csl--drag' : '';
		$classes .= $has_heading ? ' kmst-csl--has-heading' : ' kmst-csl--no-heading';
		$classes .= 'yes' === ( isset( $settings['head_stack'] ) ? $settings['head_stack'] : 'yes' ) ? ' kmst-csl--head-stack' : '';

		$prev = '<button type="button" class="kmst-csl-arrow kmst-csl-prev" aria-label="' . esc_attr__( 'Previous', 'km-storefront-builder' ) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 4 7 12l8 8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		$next = '<button type="button" class="kmst-csl-arrow kmst-csl-next" aria-label="' . esc_attr__( 'Next', 'km-storefront-builder' ) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';

		$step = isset( $settings['arrow_step'] ) && in_array( $settings['arrow_step'], array( 'end', 'page', 'card' ), true ) ? $settings['arrow_step'] : 'end';

		echo '<div class="' . esc_attr( $classes ) . '" data-step="' . esc_attr( $step ) . '">';

		// The heading and the arrows share one row: title on one side, arrows on the other.
		if ( $has_heading || ( $arrows && ! $sides ) ) {
			echo '<div class="kmst-csl-head">';

			if ( $has_heading ) {
				$tag = isset( $settings['heading_tag'] ) ? $settings['heading_tag'] : 'h2';
				$tag = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span' ), true ) ? $tag : 'h2';

				echo '<div class="kmst-csl-heading">';
				if ( '' !== $heading_title ) {
					echo '<' . esc_attr( $tag ) . ' class="kmst-csl-heading-title">' . esc_html( $heading_title ) . '</' . esc_attr( $tag ) . '>';
				}
				if ( '' !== $subtitle ) {
					echo '<span class="kmst-csl-heading-sub">' . esc_html( $subtitle ) . '</span>';
				}
				echo '</div>';
			}

			if ( $arrows && ! $sides ) {
				echo '<div class="kmst-csl-nav">' . $prev . $next . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above.
			}

			echo '</div>';
		}

		echo '<div class="kmst-csl-viewport">';

		if ( $arrows && $sides ) {
			echo '<div class="kmst-csl-nav kmst-csl-nav--sides">' . $prev . $next . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above.
		}

		echo '<div class="kmst-csl-track" tabindex="0">';

		foreach ( $items as $index => $item ) {
			$title = isset( $item['title'] ) ? $item['title'] : '';
			$url   = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '';
			$tag   = $url ? 'a' : 'div';

			$attributes = 'class="kmst-csl-card"';
			if ( $url ) {
				$attributes .= ' href="' . esc_url( $url ) . '"';
				if ( ! empty( $item['link']['is_external'] ) ) {
					$attributes .= ' target="_blank"';
				}
				if ( ! empty( $item['link']['nofollow'] ) ) {
					$attributes .= ' rel="nofollow"';
				}
			}

			$image = '';
			if ( ! empty( $item['image']['id'] ) ) {
				$image = Group_Control_Image_Size::get_attachment_image_html(
					array_merge( $settings, array( 'image' => $item['image'] ) ),
					'thumb',
					'image'
				);
			} elseif ( ! empty( $item['image']['url'] ) ) {
				$image = '<img src="' . esc_url( $item['image']['url'] ) . '" alt="' . esc_attr( $title ) . '" />';
			}

			echo '<' . esc_attr( $tag ) . ' ' . $attributes . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			echo '<span class="kmst-csl-media">' . $image . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress image HTML.
			if ( '' !== $title ) {
				echo '<span class="kmst-csl-title">' . esc_html( $title ) . '</span>';
			}
			echo '</' . esc_attr( $tag ) . '>';

			unset( $index );
		}

		echo '</div>'; // track.
		echo '</div>'; // viewport.
		echo '</div>'; // kmst-csl.
	}
}
