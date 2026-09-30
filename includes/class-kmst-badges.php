<?php
/**
 * Product badges ("Up to 11%", "New", "Sale", "Hot", "Out of stock") in each Polylang language.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * XStore prints its badge texts in English on every language (some from its code, some from Customizer
 * settings). Two layers cover both:
 *
 * - gettext for the theme's and WooCommerce's own strings, which also reaches products loaded by AJAX
 *   (quick view, load more, filters)
 * - on full page loads, the text inside every <span class="onsale ..."> badge, for texts set in the Customizer
 */
final class KMST_Badges {

	/**
	 * Badge texts with their own fields, key => English text as the theme prints it.
	 *
	 * @return array
	 */
	public static function items() {
		return array(
			'up_to'        => 'Up to',
			'new'          => 'New',
			'sale'         => 'Sale',
			'hot'          => 'Hot',
			'out_of_stock' => 'Out of stock',
		);
	}

	/**
	 * Built-in translations.
	 *
	 * @param string $lang Language slug.
	 * @return array
	 */
	public static function default_labels( $lang ) {
		if ( 'ar' === $lang ) {
			return array(
				'up_to'        => 'خصم',
				'new'          => 'جديد',
				'sale'         => 'خصم',
				'hot'          => 'رائج',
				'out_of_stock' => 'نفدت الكمية',
			);
		}
		return array();
	}

	/**
	 * Text for a badge in a language: saved field, then built-in default, then '' (leave the theme's text).
	 *
	 * @param string $lang Language slug.
	 * @param string $key  Badge key.
	 * @return string
	 */
	public static function label( $lang, $key ) {
		$saved = trim( (string) get_option( 'kmst_badge_' . $lang . '_' . $key, '' ) );
		if ( '' !== $saved ) {
			return $saved;
		}
		$defaults = self::default_labels( $lang );
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	/**
	 * English text => translation for the current language, cached per request.
	 *
	 * @var array|null
	 */
	private $map = null;

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );
		add_action( 'template_redirect', array( $this, 'start' ), 2 );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'settings' ), 21, 2 );
	}

	/**
	 * Current language, when Polylang is running on the front end.
	 *
	 * @return string
	 */
	private function language() {
		if ( ( is_admin() && ! wp_doing_ajax() ) || ! function_exists( 'pll_current_language' ) ) {
			return '';
		}
		return (string) pll_current_language( 'slug' );
	}

	/**
	 * English => translation pairs for this request (lowercased keys), including the WooCommerce "Sale!".
	 *
	 * @return array
	 */
	private function map() {
		if ( null !== $this->map ) {
			return $this->map;
		}

		// gettext runs before Polylang has picked the language; don't remember an empty answer from then.
		$lang = $this->language();
		if ( '' === $lang ) {
			return array();
		}

		$this->map = array();
		if ( ! KMST_Settings::enabled( 'badges' ) ) {
			return $this->map;
		}

		foreach ( self::items() as $key => $english ) {
			$label = self::label( $lang, $key );
			if ( '' !== $label ) {
				$this->map[ strtolower( $english ) ] = $label;
			}
		}
		if ( isset( $this->map['sale'] ) ) {
			$this->map['sale!'] = $this->map['sale'];
		}

		return $this->map;
	}

	/**
	 * Theme and WooCommerce strings.
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original text.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	public function gettext( $translation, $text, $domain ) {
		if ( ! in_array( $domain, array( 'xstore', 'xstore-core', 'woocommerce' ), true ) ) {
			return $translation;
		}
		// WooCommerce only for its sale flash; "New" or "Hot" in WooCommerce mean other things.
		if ( 'woocommerce' === $domain && 'Sale!' !== $text ) {
			return $translation;
		}
		$map = $this->map();
		$key = strtolower( trim( $text ) );
		return isset( $map[ $key ] ) ? $map[ $key ] : $translation;
	}

	/**
	 * Buffer full page loads to catch badge texts that come from the Customizer.
	 */
	public function start() {
		if ( is_feed() || wp_doing_ajax() || ! $this->map() ) {
			return;
		}
		ob_start( array( $this, 'filter_page' ) );
	}

	/**
	 * Translate the words inside each badge, leaving numbers and tags alone.
	 *
	 * @param string $html Page HTML.
	 * @return string
	 */
	public function filter_page( $html ) {
		if ( false === strpos( $html, 'onsale' ) ) {
			return $html;
		}

		$map = $this->map();

		// Longest first, so "Out of stock" wins over shorter words.
		uksort(
			$map,
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		$patterns = array();
		foreach ( array_keys( $map ) as $english ) {
			$patterns[ $english ] = '/(?<![\p{L}])' . preg_quote( $english, '/' ) . '(?![\p{L}])/iu';
		}

		$result = preg_replace_callback(
			'#(<span\s+class="[^"]*\bonsale\b[^"]*"[^>]*>)(.*?)(</span>)#s',
			static function ( $m ) use ( $map, $patterns ) {
				// Only change text between tags, never attributes.
				$inner = preg_replace_callback(
					'/(^|>)([^<]+)/',
					static function ( $t ) use ( $map, $patterns ) {
						$text = $t[2];
						foreach ( $patterns as $english => $pattern ) {
							$text = preg_replace( $pattern, esc_html( $map[ $english ] ), $text );
						}
						return $t[1] . $text;
					},
					$m[2]
				);
				return $m[1] . $inner . $m[3];
			},
			$html
		);

		return null === $result ? $html : $result;
	}

	/**
	 * Store tools settings: badge texts per language.
	 *
	 * @param array  $settings Settings.
	 * @param string $section  Section.
	 * @return array
	 */
	public function settings( $settings, $section ) {
		if ( 'kmst' !== $section || ! class_exists( 'KMST_Mobile_Panel' ) ) {
			return $settings;
		}

		$languages = KMST_Mobile_Panel::languages();
		if ( ! $languages ) {
			return $settings;
		}

		$settings[] = array(
			'title' => __( 'Product badges', 'km-storefront-builder' ),
			'type'  => 'title',
			'desc'  => __( 'Text of the product badges (Up to 15%, New, Sale...) in each language. The percentage is added by the theme. Leave a field empty to use the default shown in grey.', 'km-storefront-builder' ),
			'id'    => 'kmst_badge_options',
		);

		$settings[] = array(
			'title'   => __( 'Translate the badges', 'km-storefront-builder' ),
			'desc'    => __( 'Use the texts below for each language', 'km-storefront-builder' ),
			'id'      => 'kmst_badges',
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		foreach ( $languages as $slug => $name ) {
			$defaults = self::default_labels( $slug );
			foreach ( self::items() as $key => $english ) {
				$settings[] = array(
					/* translators: 1: badge text, 2: language */
					'title'       => sprintf( __( '"%1$s" (%2$s)', 'km-storefront-builder' ), $english, $name ? $name : $slug ),
					'id'          => 'kmst_badge_' . $slug . '_' . $key,
					'type'        => 'text',
					'default'     => '',
					'placeholder' => isset( $defaults[ $key ] ) ? $defaults[ $key ] : $english,
					'css'         => 'ar' === $slug ? 'direction:rtl;text-align:right;' : '',
				);
			}
		}

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'kmst_badge_options',
		);

		return $settings;
	}
}
