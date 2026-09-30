<?php
/**
 * XStore mobile bottom bar (Home / Shop / Wishlist / Account) in each Polylang language.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * XStore builds the bar from Customizer settings, which hold one label per item for every language, and its
 * links can point at the wrong language. This keeps the theme's bar (icons, wishlist count, styling) and
 * swaps each item's label and link for the page's language:
 *
 * - labels come from WooCommerce > Settings > Products > Store tools > Mobile bottom bar, one row per language
 * - links are moved to the current language (/ar/..., /en/... or no prefix for the default language)
 */
final class KMST_Mobile_Panel {

	/**
	 * Items of the bar that have their own label fields, key => English name.
	 *
	 * @return array
	 */
	public static function items() {
		return array(
			'home'        => __( 'Home', 'km-storefront-builder' ),
			'shop'        => __( 'Shop', 'km-storefront-builder' ),
			'cart'        => __( 'Cart', 'km-storefront-builder' ),
			'wishlist'    => __( 'Wishlist', 'km-storefront-builder' ),
			'compare'     => __( 'Compare', 'km-storefront-builder' ),
			'search'      => __( 'Search', 'km-storefront-builder' ),
			'account'     => __( 'Account (signed in)', 'km-storefront-builder' ),
			'account_out' => __( 'Account (signed out)', 'km-storefront-builder' ),
		);
	}

	/**
	 * Built-in labels, used until a field is filled in.
	 *
	 * @param string $lang Language slug.
	 * @return array
	 */
	public static function default_labels( $lang ) {
		if ( 'ar' === $lang ) {
			return array(
				'home'        => 'الرئيسية',
				'shop'        => 'المتجر',
				'cart'        => 'السلة',
				'wishlist'    => 'المفضلة',
				'compare'     => 'المقارنة',
				'search'      => 'بحث',
				'account'     => 'حسابي',
				'account_out' => 'تسجيل الدخول',
			);
		}
		return array(
			'home'        => 'Home',
			'shop'        => 'Shop',
			'cart'        => 'Cart',
			'wishlist'    => 'Wishlist',
			'compare'     => 'Compare',
			'search'      => 'Search',
			'account'     => 'My account',
			'account_out' => 'Sign in',
		);
	}

	/**
	 * Polylang languages, slug => name.
	 *
	 * @return array
	 */
	public static function languages() {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return array();
		}
		$slugs = (array) pll_languages_list( array( 'fields' => 'slug' ) );
		$names = (array) pll_languages_list( array( 'fields' => 'name' ) );
		return array_combine( $slugs, $names + array_fill( 0, count( $slugs ), '' ) );
	}

	/**
	 * The label for an item in a language: the saved field, or the built-in default.
	 *
	 * @param string $lang Language slug.
	 * @param string $key  Item key.
	 * @return string
	 */
	public static function label( $lang, $key ) {
		$saved = trim( (string) get_option( 'kmst_mp_' . $lang . '_' . $key, '' ) );
		if ( '' !== $saved ) {
			return $saved;
		}
		$defaults = self::default_labels( $lang );
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'start' ), 1 );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'settings' ), 20, 2 );
	}

	/**
	 * Buffer the page so the bar can be adjusted once it's printed.
	 */
	public function start() {
		if ( is_admin() || wp_doing_ajax() || is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		if ( ! KMST_Settings::enabled( 'mobile_panel' ) ) {
			return;
		}
		if ( ! function_exists( 'pll_current_language' ) || '' === (string) pll_current_language( 'slug' ) ) {
			return;
		}
		ob_start( array( $this, 'filter_page' ) );
	}

	/**
	 * Swap labels and links inside the bar's columns.
	 *
	 * @param string $html Page HTML.
	 * @return string
	 */
	public function filter_page( $html ) {
		if ( false === strpos( $html, 'et_b_mobile-panel-' ) ) {
			return $html;
		}

		$lang = (string) pll_current_language( 'slug' );

		$result = preg_replace_callback(
			'#(<div\s+class="[^"]*\bet_b_mobile-panel-([a-z0-9_-]+)\b[^"]*"[^>]*>\s*<a\s+href=")([^"]*)("[^>]*>.*?<span\s+class="text-nowrap">)(.*?)(</span>)#s',
			function ( $m ) use ( $lang ) {
				$key = str_replace( '-', '_', $m[2] );
				if ( 'account' === $key && ! is_user_logged_in() ) {
					$key = 'account_out';
				}

				$url   = $this->link_in_language( html_entity_decode( $m[3] ), $lang, $m[2] );
				$label = self::label( $lang, $key );
				$text  = '' !== $label ? ' ' . esc_html( $label ) . ' ' : $m[5];

				return $m[1] . esc_url( $url ) . $m[4] . $text . $m[6];
			},
			$html
		);

		return null === $result ? $html : $result;
	}

	/**
	 * A bar link in the page's language.
	 *
	 * @param string $url  Link from the theme.
	 * @param string $lang Language slug.
	 * @param string $item Item key.
	 * @return string
	 */
	private function link_in_language( $url, $lang, $item ) {
		if ( 'home' === $item && function_exists( 'pll_home_url' ) ) {
			return pll_home_url( $lang );
		}

		// The account and cart pages are separate pages per language, not one URL with a prefix: the theme
		// links to the one saved in WooCommerce, which sends an Arabic visitor to the English page.
		$pages = array(
			'account'     => 'myaccount',
			'account-out' => 'myaccount',
			'account_out' => 'myaccount',
			'cart'        => 'cart',
		);
		if ( isset( $pages[ $item ] ) && function_exists( 'pll_get_post' ) ) {
			$key  = $pages[ $item ];
			$page = class_exists( 'KMST_Polylang_Pages' ) ? KMST_Polylang_Pages::original_page_id( $key ) : (int) get_option( 'woocommerce_' . $key . '_page_id' );
			$translated = $page ? (int) pll_get_post( $page, $lang ) : 0;
			if ( $translated && 'publish' === get_post_status( $translated ) ) {
				$link = get_permalink( $translated );
				if ( $link ) {
					return $link;
				}
			}
		}
		if ( '' === $url || '#' === $url[0] || ! function_exists( 'PLL' ) ) {
			return $url;
		}

		$pll = PLL();
		if ( empty( $pll->links_model ) || empty( $pll->model ) || ! method_exists( $pll->links_model, 'switch_language_in_link' ) ) {
			return $url;
		}
		$language = $pll->model->get_language( $lang );
		if ( ! $language ) {
			return $url;
		}

		// Only links on this site.
		$home = wp_parse_url( home_url() );
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( $host && isset( $home['host'] ) && strtolower( $host ) !== strtolower( $home['host'] ) ) {
			return $url;
		}

		return $pll->links_model->switch_language_in_link( $url, $language );
	}

	/**
	 * Store tools settings: one label row per bar item and language.
	 *
	 * @param array  $settings Settings.
	 * @param string $section  Section.
	 * @return array
	 */
	public function settings( $settings, $section ) {
		if ( 'kmst' !== $section ) {
			return $settings;
		}

		$languages = self::languages();
		if ( ! $languages ) {
			return $settings;
		}

		$settings[] = array(
			'title' => __( 'Mobile bottom bar', 'km-storefront-builder' ),
			'type'  => 'title',
			'desc'  => __( 'Labels of XStore\'s mobile bottom bar (Home, Shop, Wishlist, Account...) in each language. Leave a field empty to use the default shown in grey. The links are moved to the page\'s language automatically.', 'km-storefront-builder' ),
			'id'    => 'kmst_mp_options',
		);

		$settings[] = array(
			'title'   => __( 'Translate the bottom bar', 'km-storefront-builder' ),
			'desc'    => __( 'Use the labels below and fix the links for each language', 'km-storefront-builder' ),
			'id'      => 'kmst_mobile_panel',
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		foreach ( $languages as $slug => $name ) {
			$defaults = self::default_labels( $slug );
			foreach ( self::items() as $key => $title ) {
				$settings[] = array(
					/* translators: 1: item, 2: language */
					'title'       => sprintf( __( '%1$s (%2$s)', 'km-storefront-builder' ), $title, $name ? $name : $slug ),
					'id'          => 'kmst_mp_' . $slug . '_' . $key,
					'type'        => 'text',
					'default'     => '',
					'placeholder' => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
					'css'         => 'ar' === $slug ? 'direction:rtl;text-align:right;' : '',
				);
			}
		}

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'kmst_mp_options',
		);

		return $settings;
	}
}
