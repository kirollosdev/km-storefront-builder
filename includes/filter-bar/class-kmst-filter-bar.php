<?php
/**
 * Horizontal shop filter bar: [kmst_filter_bar].
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product type, attribute and price dropdowns, plus order by, share and view on the right.
 * Filters use WooCommerce's own query parameters (product_cat, filter_{attribute}, min_price, max_price, orderby),
 * so the products widget, pagination and URLs keep working without custom queries.
 */
final class KMST_Filter_Bar {

	/**
	 * Show "(2)" selected counts on toggles.
	 *
	 * @var bool
	 */
	private $show_count = true;

	/**
	 * Hook in.
	 */
	public function __construct() {
		self::$self = $this;
		add_shortcode(
			'kmst_filter_bar',
			function () {
				return $this->render();
			}
		);
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		// Elementor's editor preview enqueues widget dependencies before wp_enqueue_scripts in some flows.
		add_action( 'elementor/frontend/before_register_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
	}

	/**
	 * Register the Elementor widget.
	 *
	 * @param \Elementor\Widgets_Manager $manager Widgets manager.
	 */
	public function register_widget( $manager ) {
		require_once KMST_PATH . 'includes/filter-bar/class-kmst-filter-bar-widget.php';
		$manager->register( new KMST_Filter_Bar_Widget() );
	}

	/**
	 * Register assets; they're enqueued when the shortcode renders.
	 */
	public function register_assets() {
		if ( wp_script_is( 'kmst-filter-bar', 'registered' ) ) {
			return;
		}
		wp_register_style( 'kmst-filter-bar', KMST_URL . 'assets/css/filter-bar.css', array(), KMST_VERSION );
		wp_register_script( 'kmst-filter-bar', KMST_URL . 'assets/js/filter-bar.js', array(), KMST_VERSION, true );
		wp_localize_script(
			'kmst-filter-bar',
			'mstFilterBar',
			array(
				'copied' => __( 'Link copied', 'km-storefront-builder' ),
			)
		);
	}

	/**
	 * Panel ID suffix, so two bars on one page don't share IDs.
	 *
	 * @var int
	 */
	private static $instance = 0;

	/**
	 * Current render's panel ID suffix.
	 *
	 * @var string
	 */
	private $uid = '';

	/**
	 * Singleton used by the shortcode and the Elementor widget.
	 *
	 * @var KMST_Filter_Bar|null
	 */
	private static $self = null;

	/**
	 * Shared instance.
	 *
	 * @return KMST_Filter_Bar|null
	 */
	public static function get() {
		return self::$self;
	}

	/**
	 * Custom texts of the current render (widget "Texts" section).
	 *
	 * @var array
	 */
	private $labels = array();

	/**
	 * Editable fixed texts: key => [title shown in Elementor, default text].
	 *
	 * @return array
	 */
	public static function text_defaults() {
		return array(
			'apply'           => array( 'title' => __( 'Apply button', 'km-storefront-builder' ), 'default' => __( 'Apply', 'km-storefront-builder' ) ),
			'clear'           => array( 'title' => __( 'Clear button', 'km-storefront-builder' ), 'default' => __( 'Clear', 'km-storefront-builder' ) ),
			'clear_all'       => array( 'title' => __( 'Clear all', 'km-storefront-builder' ), 'default' => __( 'Clear all', 'km-storefront-builder' ) ),
			'show_results'    => array( 'title' => __( 'Show results (phone)', 'km-storefront-builder' ), 'default' => __( 'Show results', 'km-storefront-builder' ) ),
			'min'             => array( 'title' => __( 'Price: Min', 'km-storefront-builder' ), 'default' => __( 'Min', 'km-storefront-builder' ) ),
			'max'             => array( 'title' => __( 'Price: Max', 'km-storefront-builder' ), 'default' => __( 'Max', 'km-storefront-builder' ) ),
			'five_stars'      => array( 'title' => __( 'Rating: 5 stars', 'km-storefront-builder' ), 'default' => __( '5 stars', 'km-storefront-builder' ) ),
			'and_up'          => array( 'title' => __( 'Rating: & up', 'km-storefront-builder' ), 'default' => __( '& up', 'km-storefront-builder' ) ),
			'copied'          => array( 'title' => __( 'Share: link copied message', 'km-storefront-builder' ), 'default' => __( 'Link copied', 'km-storefront-builder' ) ),
			'sort_menu_order' => array( 'title' => __( 'Sort: default', 'km-storefront-builder' ), 'default' => __( 'Default sorting', 'km-storefront-builder' ) ),
			'sort_popularity' => array( 'title' => __( 'Sort: popularity', 'km-storefront-builder' ), 'default' => __( 'Sort by popularity', 'km-storefront-builder' ) ),
			'sort_rating'     => array( 'title' => __( 'Sort: average rating', 'km-storefront-builder' ), 'default' => __( 'Sort by average rating', 'km-storefront-builder' ) ),
			'sort_date'       => array( 'title' => __( 'Sort: latest', 'km-storefront-builder' ), 'default' => __( 'Sort by latest', 'km-storefront-builder' ) ),
			'sort_price'      => array( 'title' => __( 'Sort: price low to high', 'km-storefront-builder' ), 'default' => __( 'Sort by price: low to high', 'km-storefront-builder' ) ),
			'sort_price-desc' => array( 'title' => __( 'Sort: price high to low', 'km-storefront-builder' ), 'default' => __( 'Sort by price: high to low', 'km-storefront-builder' ) ),
		);
	}

	/**
	 * A text: the custom one from the widget, or the translated default.
	 *
	 * @param string $key Key from text_defaults().
	 * @return string
	 */
	private function text( $key ) {
		if ( isset( $this->labels[ $key ] ) && '' !== trim( (string) $this->labels[ $key ] ) ) {
			return (string) $this->labels[ $key ];
		}
		$defaults = self::text_defaults();
		return isset( $defaults[ $key ] ) ? $defaults[ $key ]['default'] : '';
	}

	/**
	 * Default render args, from WooCommerce > Settings > Products > Store tools.
	 *
	 * @return array
	 */
	public function default_args() {
		$filters = array();
		if ( 'yes' === get_option( 'kmst_fb_categories', 'yes' ) ) {
			$filters[] = array( 'type' => 'categories' );
		}
		foreach ( $this->attribute_taxonomies() as $attribute ) {
			$filters[] = array( 'type' => 'attribute', 'attribute' => $attribute->attribute_name );
		}
		if ( 'yes' === get_option( 'kmst_fb_price', 'yes' ) ) {
			$filters[] = array( 'type' => 'price' );
		}
		if ( 'yes' === get_option( 'kmst_fb_rating', 'yes' ) ) {
			$filters[] = array( 'type' => 'rating' );
		}

		return array(
			'filters'    => $filters,
			'orderby'    => 'yes' === get_option( 'kmst_fb_orderby', 'yes' ),
			'share'      => 'yes' === get_option( 'kmst_fb_share', 'yes' ),
			'view'       => 'yes' === get_option( 'kmst_fb_view', 'yes' ),
			'chips'      => true,
			'labels'     => array(),
			'show_count' => true,
			'hover'      => true,
		);
	}

	/**
	 * Shortcode / widget output.
	 *
	 * @param array|string $args filters (list of [type => categories|attribute|price, attribute, label]), orderby, share, view, chips, labels, show_count.
	 * @return string
	 */
	public function render( $args = array() ) {
		if ( ! function_exists( 'wc_get_page_permalink' ) ) {
			return '';
		}

		$args      = wp_parse_args( is_array( $args ) ? $args : array(), $this->default_args() );
		$this->uid = '-' . ( ++self::$instance );
		$this->show_count = ! empty( $args['show_count'] );
		$this->labels     = is_array( $args['labels'] ) ? $args['labels'] : array();

		wp_enqueue_style( 'kmst-filter-bar' );
		wp_enqueue_script( 'kmst-filter-bar' );

		$filters    = array();
		$chips      = array();
		$attributes = array();
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$attributes[ $attribute->attribute_name ] = $attribute;
		}

		foreach ( (array) $args['filters'] as $filter ) {
			$label = isset( $filter['label'] ) ? trim( (string) $filter['label'] ) : '';
			switch ( isset( $filter['type'] ) ? $filter['type'] : '' ) {
				case 'categories':
					$filters[] = $this->categories( $chips, $label );
					break;
				case 'attribute':
					if ( ! empty( $filter['attribute'] ) && isset( $attributes[ $filter['attribute'] ] ) ) {
						$filters[] = $this->attribute( $attributes[ $filter['attribute'] ], $chips, $label );
					}
					break;
				case 'price':
					$filters[] = $this->price( $chips, $label );
					break;
				case 'rating':
					$filters[] = $this->rating( $chips, $label );
					break;
			}
		}

		$current_cat = is_product_category() ? get_queried_object()->slug : '';

		$filters_label = ! empty( $args['labels']['filters'] ) ? $args['labels']['filters'] : __( 'Filters', 'km-storefront-builder' );
		$filters_html  = implode( '', array_filter( $filters ) );
		$active_total  = count( $chips );

		$html  = '<div class="kmst-fb" data-current-cat="' . esc_attr( $current_cat ) . '" data-hover="' . ( ! empty( $args['hover'] ) ? 'yes' : 'no' ) . '" data-copied="' . esc_attr( $this->text( 'copied' ) ) . '">';
		$html .= '<a class="kmst-fb-shop" href="' . esc_url( $this->shop_url() ) . '" hidden aria-hidden="true" tabindex="-1"></a>';
		$html .= $this->mobile_toolbar( $args, $filters_label, $active_total, '' !== $filters_html );
		$html .= '<div class="kmst-fb-bar">';

		// On phones this container becomes the filter bottom sheet; on desktop the head, body wrapper and foot are invisible.
		$html .= '<div class="kmst-fb-filters" id="kmst-fb-sheet' . esc_attr( $this->uid ) . '" aria-label="' . esc_attr( $filters_label ) . '">';
		$html .= '<div class="kmst-fb-sheet-head"><span>' . esc_html( $filters_label ) . '</span><button type="button" class="kmst-fb-sheet-close" aria-label="' . esc_attr__( 'Close', 'km-storefront-builder' ) . '">&times;</button></div>';
		$html .= '<div class="kmst-fb-sheet-body">' . $filters_html . '</div>';
		$html .= '<div class="kmst-fb-sheet-foot"><button type="button" class="kmst-fb-sheet-clear">' . esc_html( $this->text( 'clear_all' ) ) . '</button><button type="button" class="kmst-fb-sheet-apply">' . esc_html( $this->text( 'show_results' ) ) . '</button></div>';
		$html .= '</div>';
		$html .= '<div class="kmst-fb-tools">' . $this->tools( $args ) . '</div>';
		$html .= '</div>';

		if ( $chips && ! empty( $args['chips'] ) ) {
			$html .= '<div class="kmst-fb-active">';
			foreach ( $chips as $chip ) {
				$html .= '<button type="button" class="kmst-fb-chip" data-key="' . esc_attr( $chip['key'] ) . '" data-value="' . esc_attr( $chip['value'] ) . '">' . wp_kses_post( $chip['label'] ) . '<span class="kmst-fb-chip-x" aria-hidden="true">&times;</span></button>';
			}
			$html .= '<button type="button" class="kmst-fb-clear-all">' . esc_html( $this->text( 'clear_all' ) ) . '</button>';
			$html .= '</div>';
		}

		$html .= '<div class="kmst-fb-backdrop" aria-hidden="true"></div>';
		$html .= '</div>';

		// Apply the saved column choice before the products paint, so the grid doesn't jump.
		$html .= '<script>(function(){try{var d=localStorage.getItem("kmst_fb_cols"),m=localStorage.getItem("kmst_fb_cols_m");if(d){document.documentElement.classList.add("kmst-cols-"+d)}if(m){document.documentElement.classList.add("kmst-cols-m"+m)}}catch(e){}})();</script>';

		return $html;
	}

	/**
	 * The shop page in the language of the page the bar is on. "Clear all" and picking another category on
	 * a category page go there; with Polylang the shop page has one language (for example English, giving
	 * /en/shop/ on Arabic pages), so its address is moved into the current language here.
	 *
	 * @return string
	 */
	private function shop_url() {
		$url = (string) wc_get_page_permalink( 'shop' );
		if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_current_language' ) ) {
			return $url;
		}

		$lang = class_exists( 'KMST_Arabic_Strings' ) ? KMST_Arabic_Strings::language() : (string) pll_current_language( 'slug' );
		$pll  = PLL();
		if ( '' === $lang || empty( $pll->model ) || empty( $pll->links_model ) || ! method_exists( $pll->links_model, 'switch_language_in_link' ) ) {
			return $url;
		}

		$language = $pll->model->get_language( $lang );
		return $language ? (string) $pll->links_model->switch_language_in_link( $url, $language ) : $url;
	}

	/* ------------------------------------------------------------------ */
	/* Filters                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Product type (categories).
	 *
	 * @param array $chips Active chips, appended to.
	 * @return string
	 */
	private function categories( array &$chips, $label = '' ) {
		$selected = $this->get_list( 'product_cat' );
		if ( ! $selected && is_product_category() ) {
			$selected = array( get_queried_object()->slug );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'orderby'    => 'menu_order',
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}

		$options = '';
		foreach ( $this->sort_hierarchy( $terms ) as $row ) {
			$term    = $row['term'];
			$checked = in_array( $term->slug, $selected, true );
			$options .= $this->checkbox( $term->slug, esc_html( $term->name ), $checked, 'kmst-fb-opt--depth-' . min( 3, $row['depth'] ) );
			if ( $checked ) {
				$chips[] = array( 'key' => 'product_cat', 'value' => $term->slug, 'label' => esc_html( $term->name ) );
			}
		}

		return $this->item(
			array(
				'key'      => 'product_cat',
				'type'     => 'terms',
				'label'    => '' !== $label ? $label : __( 'Product Type', 'km-storefront-builder' ),
				'count'    => count( $selected ),
				'body'     => '<div class="kmst-fb-options kmst-fb-options--list">' . $options . '</div>',
			)
		);
	}

	/**
	 * One product attribute: color dots for color attributes, boxes for everything else.
	 *
	 * @param object $attribute Attribute taxonomy row.
	 * @param array  $chips     Active chips, appended to.
	 * @return string
	 */
	private function attribute( $attribute, array &$chips, $custom_label = '' ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
		$key      = 'filter_' . $attribute->attribute_name;
		$selected = $this->get_list( $key );

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}

		$counts   = $this->filtered_counts( $taxonomy, $terms );
		$label    = $this->nice_label( wc_attribute_label( $taxonomy ) );
		$is_color = class_exists( 'KMST_Colors' ) && KMST_Colors::is_color_attribute_name( $attribute->attribute_name . ' ' . $label );
		if ( '' !== $custom_label ) {
			$label = $custom_label;
		}

		$options = '';
		foreach ( $terms as $term ) {
			$checked = in_array( $term->slug, $selected, true );
			if ( ! $checked && null !== $counts && empty( $counts[ $term->term_id ] ) ) {
				continue; // Nothing to find with this value in the current results.
			}

			if ( $is_color ) {
				$options .= $this->checkbox( $term->slug, $this->color_dot( $term ) . '<span class="kmst-fb-opt-text">' . esc_html( $term->name ) . '</span>', $checked, 'kmst-fb-opt--color' );
			} else {
				$options .= $this->checkbox( $term->slug, '<span class="kmst-fb-opt-text">' . esc_html( $term->name ) . '</span>', $checked, 'kmst-fb-opt--box' );
			}

			if ( $checked ) {
				$chips[] = array( 'key' => $key, 'value' => $term->slug, 'label' => esc_html( $term->name ) );
			}
		}

		if ( '' === $options ) {
			return '';
		}

		return $this->item(
			array(
				'key'        => $key,
				'type'       => 'terms',
				'query_type' => 'query_type_' . $attribute->attribute_name,
				'label'      => $label,
				'count'      => count( $selected ),
				'body'       => '<div class="kmst-fb-options ' . ( $is_color ? 'kmst-fb-options--list' : 'kmst-fb-options--boxes' ) . '">' . $options . '</div>',
			)
		);
	}

	/**
	 * Price range with a two-handle slider.
	 *
	 * @param array $chips Active chips, appended to.
	 * @return string
	 */
	private function rating( array &$chips, $label = '' ) {
		if ( ! wc_review_ratings_enabled() ) {
			return '';
		}

		// WooCommerce's rating_filter lists the exact (rounded) ratings to include; "4 & up" is 4,5.
		$selected = array_filter( array_map( 'absint', $this->get_list( 'rating_filter' ) ) );
		$current  = $selected ? min( $selected ) : 0;

		// Products per rounded rating, from WooCommerce's rated-1 ... rated-5 visibility terms.
		$per_rating = array();
		for ( $n = 1; $n <= 5; $n++ ) {
			$term             = get_term_by( 'slug', 'rated-' . $n, 'product_visibility' );
			$per_rating[ $n ] = $term ? (int) $term->count : 0;
		}

		$options = '';
		for ( $n = 5; $n >= 1; $n-- ) {
			$at_least = 0;
			for ( $k = $n; $k <= 5; $k++ ) {
				$at_least += $per_rating[ $k ];
			}
			$checked = $current === $n;
			if ( ! $at_least && ! $checked ) {
				continue; // No product would match.
			}

			if ( 5 === $n ) {
				$text = $this->text( 'five_stars' );
			} elseif ( ! empty( $this->labels['and_up'] ) ) {
				$text = $n . ' ★ ' . $this->text( 'and_up' );
			} else {
				/* translators: %d: number of stars */
				$text = sprintf( _n( '%d star & up', '%d stars & up', $n, 'km-storefront-builder' ), $n );
			}

			$inner = '<span class="kmst-fb-stars" style="--kmst-fb-rating:' . (int) $n . '" role="img" aria-label="' . esc_attr( $text ) . '"></span>'
				. ( 5 === $n ? '' : '<span class="kmst-fb-opt-text">' . esc_html( $this->text( 'and_up' ) ) . '</span>' );

			$options .= $this->checkbox( (string) $n, $inner, $checked, 'kmst-fb-opt--rating' );

			if ( $checked ) {
				$chips[] = array(
					'key'   => 'rating_filter',
					'value' => (string) $n,
					'label' => esc_html( $text ),
				);
			}
		}

		if ( '' === $options ) {
			return '';
		}

		return $this->item(
			array(
				'key'   => 'rating_filter',
				'type'  => 'rating',
				'label' => '' !== $label ? $label : __( 'Rating', 'km-storefront-builder' ),
				'count' => $current ? 1 : 0,
				'body'  => '<div class="kmst-fb-options kmst-fb-options--list">' . $options . '</div>',
			)
		);
	}

	/**
	 * Price range with a two-handle slider.
	 *
	 * @param array  $chips Active chips, appended to.
	 * @param string $label Custom label.
	 * @return string
	 */
	private function price( array &$chips, $label = '' ) {
		list( $floor, $ceil ) = $this->price_bounds();
		if ( $ceil <= $floor ) {
			return '';
		}

		$min = isset( $_GET['min_price'] ) ? max( $floor, (float) wc_clean( wp_unslash( $_GET['min_price'] ) ) ) : $floor; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$max = isset( $_GET['max_price'] ) ? min( $ceil, (float) wc_clean( wp_unslash( $_GET['max_price'] ) ) ) : $ceil; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$on  = $min > $floor || $max < $ceil;

		if ( $on ) {
			$chips[] = array(
				'key'   => 'price',
				'value' => '',
				'label' => wc_price( $min, array( 'decimals' => 0 ) ) . ' – ' . wc_price( $max, array( 'decimals' => 0 ) ),
			);
		}

		$body = sprintf(
			'<div class="kmst-fb-price" data-floor="%1$s" data-ceil="%2$s">
				<div class="kmst-fb-range">
					<span class="kmst-fb-range-track"><span class="kmst-fb-range-fill"></span></span>
					<input type="range" class="kmst-fb-range-min" min="%1$s" max="%2$s" step="1" value="%3$s" aria-label="%5$s" />
					<input type="range" class="kmst-fb-range-max" min="%1$s" max="%2$s" step="1" value="%4$s" aria-label="%6$s" />
				</div>
				<div class="kmst-fb-price-inputs">
					<label><span>%5$s</span><input type="number" class="kmst-fb-price-min" min="%1$s" max="%2$s" step="1" value="%3$s" inputmode="numeric" /></label>
					<span class="kmst-fb-price-sep" aria-hidden="true">–</span>
					<label><span>%6$s</span><input type="number" class="kmst-fb-price-max" min="%1$s" max="%2$s" step="1" value="%4$s" inputmode="numeric" /></label>
				</div>
			</div>',
			esc_attr( $floor ),
			esc_attr( $ceil ),
			esc_attr( floor( $min ) ),
			esc_attr( ceil( $max ) ),
			esc_attr( $this->text( 'min' ) ),
			esc_attr( $this->text( 'max' ) )
		);

		return $this->item(
			array(
				'key'   => 'price',
				'type'  => 'price',
				'label' => '' !== $label ? $label : __( 'Price', 'km-storefront-builder' ),
				'count' => $on ? 1 : 0,
				'body'  => $body,
			)
		);
	}

	/* ------------------------------------------------------------------ */
	/* Right side: order by, share, view                                   */
	/* ------------------------------------------------------------------ */

	/**
	 * Order by, share and view controls.
	 *
	 * @return string
	 */
	private function tools( array $args ) {
		$html = '';

		if ( ! empty( $args['orderby'] ) ) {
			$default = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
			$current = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$options = apply_filters(
				'woocommerce_catalog_orderby',
				array(
					'menu_order' => $this->text( 'sort_menu_order' ),
					'popularity' => $this->text( 'sort_popularity' ),
					'rating'     => $this->text( 'sort_rating' ),
					'date'       => $this->text( 'sort_date' ),
					'price'      => $this->text( 'sort_price' ),
					'price-desc' => $this->text( 'sort_price-desc' ),
				)
			);
			if ( ! wc_review_ratings_enabled() ) {
				unset( $options['rating'] );
			}

			$body = '<div class="kmst-fb-options kmst-fb-options--menu" role="menu">';
			foreach ( $options as $value => $label ) {
				$body .= '<button type="button" class="kmst-fb-sort' . ( $value === $current ? ' is-current' : '' ) . '" role="menuitemradio" aria-checked="' . ( $value === $current ? 'true' : 'false' ) . '" data-value="' . esc_attr( $value ) . '" data-default="' . ( $value === $default ? 'yes' : 'no' ) . '">' . esc_html( $label ) . '</button>';
			}
			$body .= '</div>';

			$html .= $this->item(
				array(
					'key'     => 'orderby',
					'type'    => 'menu',
					'label'   => ! empty( $args['labels']['orderby'] ) ? $args['labels']['orderby'] : __( 'Order by', 'km-storefront-builder' ),
					'icon'    => '<svg class="kmst-fb-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M6 3v13M2.5 12.5 6 16l3.5-3.5M14 17V4m-3.5 3.5L14 4l3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
					'body'    => $body,
					'actions' => false,
					'align'   => 'end',
				)
			);
		}

		if ( ! empty( $args['share'] ) ) {
			$html .= '<div class="kmst-fb-item kmst-fb-item--share"><button type="button" class="kmst-fb-toggle kmst-fb-share">' . esc_html( ! empty( $args['labels']['share'] ) ? $args['labels']['share'] : __( 'Share', 'km-storefront-builder' ) ) . '<svg class="kmst-fb-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 12.5V2.5M6.5 6 10 2.5 13.5 6M6 9H4.5v8.5h11V9H14" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button><span class="kmst-fb-toast" role="status" aria-live="polite"></span></div>';
		}

		if ( ! empty( $args['view'] ) ) {
			$body  = '<div class="kmst-fb-options kmst-fb-options--views">';
			$body .= '<span class="kmst-fb-views kmst-fb-views--desktop">';
			foreach ( array( 2, 3, 4 ) as $cols ) {
				/* translators: %d: number of columns */
				$body .= '<button type="button" class="kmst-fb-view" data-cols="' . $cols . '" aria-label="' . esc_attr( sprintf( __( '%d columns', 'km-storefront-builder' ), $cols ) ) . '">' . $this->bars_icon( $cols ) . '</button>';
			}
			$body .= '</span></div>';

			$html .= $this->item(
				array(
					'key'     => 'view',
					'type'    => 'view',
					'label'   => ! empty( $args['labels']['view'] ) ? $args['labels']['view'] : __( 'View', 'km-storefront-builder' ),
					'icon'    => '<svg class="kmst-fb-icon kmst-fb-icon--view" viewBox="0 0 20 20" aria-hidden="true"><path d="M4 3v14M8 3v14M12 3v14M16 3v14" fill="none" stroke="currentColor" stroke-width="1.2"/></svg>',
					'body'    => $body,
					'actions' => false,
					'align'   => 'end',
				)
			);
		}

		return $html;
	}

	/**
	 * Phone toolbar: Filter and Sort buttons, share icon, 1/2 column switch.
	 *
	 * @param array  $args          Render args.
	 * @param string $filters_label "Filters" label.
	 * @param int    $active_total  Number of active filters.
	 * @param bool   $has_filters   Any filter dropdowns rendered.
	 * @return string
	 */
	private function mobile_toolbar( array $args, $filters_label, $active_total, $has_filters ) {
		$html = '<div class="kmst-fb-mbar">';

		if ( $has_filters ) {
			$html .= '<button type="button" class="kmst-fb-mbtn kmst-fb-open-filters" aria-haspopup="dialog" aria-controls="kmst-fb-sheet' . esc_attr( $this->uid ) . '">';
			$html .= '<svg class="kmst-fb-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M3 5h14M5.5 10h9M8 15h4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
			$html .= '<span>' . esc_html( $filters_label ) . '</span>';
			if ( $active_total ) {
				$html .= '<span class="kmst-fb-mcount">' . (int) $active_total . '</span>';
			}
			$html .= '</button>';
		}

		if ( ! empty( $args['orderby'] ) ) {
			$html .= '<button type="button" class="kmst-fb-mbtn kmst-fb-open-sort" aria-haspopup="dialog">';
			$html .= '<svg class="kmst-fb-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M6 3v13M2.5 12.5 6 16l3.5-3.5M14 17V4m-3.5 3.5L14 4l3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			$html .= '<span>' . esc_html( ! empty( $args['labels']['orderby'] ) ? $args['labels']['orderby'] : __( 'Order by', 'km-storefront-builder' ) ) . '</span>';
			$html .= '</button>';
		}

		if ( ! empty( $args['share'] ) ) {
			$html .= '<button type="button" class="kmst-fb-micon kmst-fb-share" aria-label="' . esc_attr( ! empty( $args['labels']['share'] ) ? $args['labels']['share'] : __( 'Share', 'km-storefront-builder' ) ) . '"><svg class="kmst-fb-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 12.5V2.5M6.5 6 10 2.5 13.5 6M6 9H4.5v8.5h11V9H14" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		}

		if ( ! empty( $args['view'] ) ) {
			$html .= '<span class="kmst-fb-mviews" role="group" aria-label="' . esc_attr( ! empty( $args['labels']['view'] ) ? $args['labels']['view'] : __( 'View', 'km-storefront-builder' ) ) . '">';
			foreach ( array( 1, 2 ) as $cols ) {
				/* translators: %d: number of columns */
				$html .= '<button type="button" class="kmst-fb-micon kmst-fb-view" data-cols-mobile="' . $cols . '" aria-label="' . esc_attr( sprintf( _n( '%d column', '%d columns', $cols, 'km-storefront-builder' ), $cols ) ) . '">' . $this->bars_icon( $cols ) . '</button>';
			}
			$html .= '</span>';
		}

		$html .= '<span class="kmst-fb-toast" role="status" aria-live="polite"></span>';

		return $html . '</div>';
	}

	/* ------------------------------------------------------------------ */
	/* Helpers                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Dropdown wrapper.
	 *
	 * @param array $args key, type, label, count, body, query_type, icon, actions, align.
	 * @return string
	 */
	private function item( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'query_type' => '',
				'count'      => 0,
				'icon'       => '',
				'actions'    => true,
				'align'      => 'start',
			)
		);

		$id      = 'kmst-fb-panel-' . sanitize_html_class( $args['key'] ) . $this->uid;
		$chevron = '<svg class="kmst-fb-chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$count   = $args['count'] && $this->show_count ? '<span class="kmst-fb-count">(' . (int) $args['count'] . ')</span>' : '';

		$html  = '<div class="kmst-fb-item kmst-fb-item--' . esc_attr( $args['type'] ) . ' kmst-fb-align-' . esc_attr( $args['align'] ) . ( $args['count'] ? ' is-active' : '' ) . '" data-key="' . esc_attr( $args['key'] ) . '" data-type="' . esc_attr( $args['type'] ) . '"' . ( $args['query_type'] ? ' data-query-type="' . esc_attr( $args['query_type'] ) . '"' : '' ) . '>';
		$html .= '<button type="button" class="kmst-fb-toggle" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '">' . esc_html( $args['label'] ) . $count . ( $args['icon'] ? $args['icon'] : $chevron ) . '</button>';
		$html .= '<div class="kmst-fb-panel" id="' . esc_attr( $id ) . '" hidden>';
		$html .= '<div class="kmst-fb-panel-head"><span>' . esc_html( $args['label'] ) . '</span><button type="button" class="kmst-fb-close" aria-label="' . esc_attr__( 'Close', 'km-storefront-builder' ) . '">&times;</button></div>';
		$html .= '<div class="kmst-fb-panel-body">' . $args['body'] . '</div>';

		if ( $args['actions'] ) {
			$html .= '<div class="kmst-fb-actions"><button type="button" class="kmst-fb-clear">' . esc_html( $this->text( 'clear' ) ) . '</button><button type="button" class="kmst-fb-apply">' . esc_html( $this->text( 'apply' ) ) . '</button></div>';
		}

		return $html . '</div></div>';
	}

	/**
	 * Checkbox option.
	 *
	 * @param string $value   Value.
	 * @param string $inner   Escaped label HTML.
	 * @param bool   $checked Checked.
	 * @param string $class   Extra class.
	 * @return string
	 */
	private function checkbox( $value, $inner, $checked, $class ) {
		return '<label class="kmst-fb-opt ' . esc_attr( $class ) . '"><input type="checkbox" value="' . esc_attr( $value ) . '"' . checked( $checked, true, false ) . ' /><span class="kmst-fb-check" aria-hidden="true"></span>' . $inner . '</label>';
	}

	/**
	 * Color dot for a term: manual swatch color, else detected from the name.
	 *
	 * @param WP_Term $term Term.
	 * @return string
	 */
	private function color_dot( $term ) {
		$colors = array();
		$manual = sanitize_hex_color( get_term_meta( $term->term_id, 'kmvs_color', true ) );
		if ( $manual ) {
			$colors = array( $manual );
		} elseif ( class_exists( 'KMST_Colors' ) ) {
			$colors = KMST_Colors::resolve( KMST_Colors::term_name( $term ) );
		}

		$fill  = $colors ? KMST_Colors::background( $colors ) : '';
		$light = 1 === count( $colors ) && KMST_Colors::is_light( $colors[0] );

		return '<span class="kmst-fb-dot' . ( $fill ? '' : ' is-unknown' ) . ( $light ? ' is-light' : '' ) . '"' . ( $fill ? ' style="background:' . esc_attr( $fill ) . '"' : '' ) . ' aria-hidden="true"></span>';
	}

	/**
	 * Vertical bars icon for the view switcher.
	 *
	 * @param int $count Bars.
	 * @return string
	 */
	private function bars_icon( $count ) {
		$gap   = 2;
		$width = ( 18 - $gap * ( $count - 1 ) ) / $count;
		$rects = '';
		for ( $i = 0; $i < $count; $i++ ) {
			$rects .= '<rect x="' . round( 1 + $i * ( $width + $gap ), 2 ) . '" y="3" width="' . round( $width, 2 ) . '" height="14" rx="0.5"/>';
		}
		return '<svg class="kmst-fb-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">' . $rects . '</svg>';
	}

	/**
	 * Attribute taxonomies to show: all of them, or the ones picked in settings.
	 *
	 * @return array
	 */
	private function attribute_taxonomies() {
		$all    = wc_get_attribute_taxonomies();
		$picked = array_filter( (array) get_option( 'kmst_fb_attributes', array() ) );

		if ( $picked ) {
			$all = array_filter(
				$all,
				static function ( $attribute ) use ( $picked ) {
					return in_array( $attribute->attribute_name, $picked, true );
				}
			);
		}

		return apply_filters( 'kmst_filter_bar_attributes', array_values( $all ) );
	}

	/**
	 * Product counts per term for the current results (WooCommerce's layered nav logic), or null when unavailable.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param array  $terms    Terms.
	 * @return array|null term_id => count.
	 */
	private function filtered_counts( $taxonomy, array $terms ) {
		$class = '\Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer';
		if ( ! function_exists( 'wc_get_container' ) || ! class_exists( $class ) || ! ( is_shop() || is_product_taxonomy() ) ) {
			return null;
		}

		try {
			$counts = wc_get_container()->get( $class )->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, 'or' );
			return is_array( $counts ) ? $counts : null;
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/**
	 * Lowest and highest product price in the store.
	 *
	 * @return array [floor, ceil]
	 */
	private function price_bounds() {
		$bounds = wp_cache_get( 'kmst_fb_price_bounds', 'km-storefront-builder' );
		if ( false === $bounds ) {
			global $wpdb;
			$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT MIN( lookup.min_price ) AS min_price, MAX( lookup.max_price ) AS max_price
				FROM {$wpdb->wc_product_meta_lookup} lookup
				INNER JOIN {$wpdb->posts} p ON p.ID = lookup.product_id
				WHERE p.post_type = 'product' AND p.post_status = 'publish'"
			);
			$bounds = array( $row ? (int) floor( (float) $row->min_price ) : 0, $row ? (int) ceil( (float) $row->max_price ) : 0 );
			wp_cache_set( 'kmst_fb_price_bounds', $bounds, 'km-storefront-builder', HOUR_IN_SECONDS );
		}
		return apply_filters( 'kmst_filter_bar_price_bounds', $bounds );
	}

	/**
	 * Comma list from the query string.
	 *
	 * @param string $key Query key.
	 * @return string[]
	 */
	private function get_list( $key ) {
		if ( empty( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return array();
		}
		$raw = wc_clean( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return array_values( array_filter( array_map( 'sanitize_title', explode( ',', is_array( $raw ) ? implode( ',', $raw ) : $raw ) ) ) );
	}

	/**
	 * Parents followed by their children.
	 *
	 * @param WP_Term[] $terms  Terms.
	 * @param int       $parent Parent ID.
	 * @param int       $depth  Depth.
	 * @return array
	 */
	private function sort_hierarchy( array $terms, $parent = 0, $depth = 0 ) {
		$ids  = wp_list_pluck( $terms, 'term_id' );
		$rows = array();
		foreach ( $terms as $term ) {
			// Treat a term whose parent isn't in the list (hidden or empty) as top level.
			$term_parent = in_array( (int) $term->parent, $ids, true ) ? (int) $term->parent : 0;
			if ( $term_parent !== (int) $parent ) {
				continue;
			}
			$rows[] = array( 'term' => $term, 'depth' => $depth );
			$rows   = array_merge( $rows, $this->sort_hierarchy( $terms, $term->term_id, $depth + 1 ) );
		}
		return $rows;
	}

	/**
	 * "color" -> "Color". Labels typed with capitals are left alone.
	 *
	 * @param string $label Label.
	 * @return string
	 */
	private function nice_label( $label ) {
		return preg_match( '/^[a-z0-9 _-]+$/', $label ) ? ucwords( str_replace( array( '-', '_' ), ' ', $label ) ) : $label;
	}
}
