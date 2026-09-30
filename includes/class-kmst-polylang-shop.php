<?php
/**
 * Polylang (free) + WooCommerce: language versions of the shop, product categories/tags and product pages.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Free Polylang doesn't translate WooCommerce, so /ar/shop/ is redirected to /shop/ and Arabic visitors lose
 * the Arabic header, footer and templates. This keeps one set of products, but makes the shop pages reachable
 * under each language's URL prefix, so the page is in that language (templates, menus, strings, RTL):
 *
 * - rewrite rules for /{lang}/shop/, /{lang}/product-category/.../, /{lang}/product-tag/.../, /{lang}/product/.../
 * - no canonical redirect back to the default language on those pages
 * - shop, category, tag and product links keep the current language prefix
 * - the language switcher on those pages links to the same page in the other language
 *
 * Needs Polylang with "The language is set from the directory name in pretty permalinks".
 * Not needed (and switched off) when Polylang for WooCommerce is active.
 */
final class KMST_Polylang_Shop {

	/**
	 * Option storing a hash of the rules last flushed.
	 */
	const RULES_OPTION = 'kmst_pll_shop_rules_hash';

	/**
	 * Store page IDs per language, see store_page_ids().
	 *
	 * @var array
	 */
	private $page_ids = array();

	/**
	 * Is the module able to run?
	 *
	 * @return bool
	 */
	public static function available() {
		if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_languages_list' ) || ! function_exists( 'wc_get_page_id' ) ) {
			return false;
		}
		if ( defined( 'PLLWC_VERSION' ) || class_exists( 'Polylang_Woocommerce' ) ) {
			return false; // Polylang for WooCommerce does this properly.
		}
		$pll = PLL();
		// force_lang 1 = language from the directory name (/ar/...).
		return isset( $pll->options['force_lang'] ) && 1 === (int) $pll->options['force_lang'] && isset( $pll->links_model );
	}

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_rules' ), 30 );
		add_filter( 'pll_check_canonical_url', array( $this, 'keep_language_url' ), 10, 2 );
		add_filter( 'redirect_canonical', array( $this, 'keep_language_url' ), 10, 1 );

		add_filter( 'post_type_archive_link', array( $this, 'archive_link' ), 20, 2 );
		add_filter( 'woocommerce_get_shop_page_permalink', array( $this, 'link_in_current_language' ), 20 );
		add_filter( 'woocommerce_ajax_get_endpoint', array( $this, 'ajax_endpoint' ), 20 );
		add_filter( 'term_link', array( $this, 'term_link' ), 20, 3 );
		add_filter( 'post_type_link', array( $this, 'product_link' ), 20, 2 );
		add_filter( 'page_link', array( $this, 'page_link' ), 20, 2 );

		add_filter( 'pll_translation_url', array( $this, 'switcher_url' ), 20, 2 );
	}

	/* ------------------------------------------------------------------ */
	/* Rewrite rules                                                       */
	/* ------------------------------------------------------------------ */

	/**
	 * Language slugs that appear as a URL prefix.
	 *
	 * @return string[]
	 */
	private function prefixed_languages() {
		$languages = (array) pll_languages_list( array( 'fields' => 'slug' ) );
		$options   = PLL()->options;

		if ( ! empty( $options['hide_default'] ) && ! empty( $options['default_lang'] ) ) {
			$languages = array_diff( $languages, array( $options['default_lang'] ) );
		}

		return array_values( array_filter( array_map( 'sanitize_title', $languages ) ) );
	}

	/**
	 * The rules: pattern => query.
	 *
	 * @return array
	 */
	private function rules() {
		$rules = array();

		$shop_id  = (int) wc_get_page_id( 'shop' );
		$shop_uri = $shop_id > 0 ? get_page_uri( $shop_id ) : '';

		$permalinks = function_exists( 'wc_get_permalink_structure' ) ? wc_get_permalink_structure() : array();
		$cat_base   = ! empty( $permalinks['category_rewrite_slug'] ) ? trim( $permalinks['category_rewrite_slug'], '/' ) : 'product-category';
		$tag_base   = ! empty( $permalinks['tag_rewrite_slug'] ) ? trim( $permalinks['tag_rewrite_slug'], '/' ) : 'product-tag';
		$prod_base  = ! empty( $permalinks['product_rewrite_slug'] ) ? trim( $permalinks['product_rewrite_slug'], '/' ) : 'product';

		foreach ( $this->prefixed_languages() as $lang ) {
			$l = preg_quote( $lang, '#' );

			if ( '' !== $shop_uri ) {
				$shop                                            = preg_quote( $shop_uri, '#' );
				$rules[ '^' . $l . '/' . $shop . '/page/([0-9]{1,})/?$' ] = 'index.php?post_type=product&paged=$matches[1]';
				$rules[ '^' . $l . '/' . $shop . '/?$' ]                  = 'index.php?post_type=product';
			}

			$cat                                                    = preg_quote( $cat_base, '#' );
			$rules[ '^' . $l . '/' . $cat . '/(.+?)/page/([0-9]{1,})/?$' ] = 'index.php?product_cat=$matches[1]&paged=$matches[2]';
			$rules[ '^' . $l . '/' . $cat . '/(.+?)/?$' ]                   = 'index.php?product_cat=$matches[1]';

			$tag                                                    = preg_quote( $tag_base, '#' );
			$rules[ '^' . $l . '/' . $tag . '/(.+?)/page/([0-9]{1,})/?$' ] = 'index.php?product_tag=$matches[1]&paged=$matches[2]';
			$rules[ '^' . $l . '/' . $tag . '/(.+?)/?$' ]                   = 'index.php?product_tag=$matches[1]';

			// Product pages, when the product base is a plain slug (no %product_cat% placeholder).
			if ( false === strpos( $prod_base, '%' ) ) {
				$prod                                          = preg_quote( $prod_base, '#' );
				$rules[ '^' . $l . '/' . $prod . '/([^/]+)/?$' ] = 'index.php?product=$matches[1]&post_type=product&name=$matches[1]';
			}

			// Cart, checkout, my account (with its endpoints: orders, edit-address, ...), wishlist and similar pages.
			foreach ( $this->store_page_ids( $lang ) as $page_id ) {
				$uri = get_page_uri( $page_id );
				if ( ! $uri ) {
					continue;
				}
				$p = preg_quote( $uri, '#' );
				foreach ( $this->endpoints() as $var => $slug ) {
					$rules[ '^' . $l . '/' . $p . '/' . preg_quote( $slug, '#' ) . '(/(.*))?/?$' ] = 'index.php?page_id=' . $page_id . '&' . $var . '=$matches[2]';
				}
				$rules[ '^' . $l . '/' . $p . '/?$' ] = 'index.php?page_id=' . $page_id;
			}
		}

		return apply_filters( 'kmst_pll_shop_rewrite_rules', $rules );
	}

	/**
	 * WooCommerce endpoints (orders, view-order, edit-account, order-received, ...), query var => slug.
	 *
	 * @return array
	 */
	private function endpoints() {
		$endpoints = function_exists( 'WC' ) && WC()->query ? (array) WC()->query->get_query_vars() : array();
		return array_filter( $endpoints, 'strlen' );
	}

	/**
	 * Store pages shared by all languages: WooCommerce's cart, checkout, my account and terms pages, plus
	 * wishlist / compare / order tracking pages. Pages that already have a Polylang translation in $lang
	 * are left to Polylang.
	 *
	 * @param string $lang Language slug; empty for every language.
	 * @return int[]
	 */
	private function store_page_ids( $lang = '' ) {
		if ( isset( $this->page_ids[ $lang ] ) ) {
			return $this->page_ids[ $lang ];
		}

		$ids = array();
		// The shop page is NOT in this list: it is a product archive, not a page. Adding it here would make
		// /en/shop/ a plain page (and send the language switcher to a translated shop page instead of the
		// archive). Its links are handled on their own, in shop_page_id() below.
		foreach ( array( 'myaccount', 'cart', 'checkout', 'terms' ) as $page ) {
			// The page saved in WooCommerce > Settings > Advanced, not the per-language one from KMST_Polylang_Pages.
			$ids[] = class_exists( 'KMST_Polylang_Pages' ) ? KMST_Polylang_Pages::original_page_id( $page ) : (int) get_option( 'woocommerce_' . $page . '_page_id' );
		}
		foreach ( array( 'wishlist', 'compare', 'track-order', 'order-tracking', 'track-your-order' ) as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				$ids[] = (int) $page->ID;
			}
		}

		$ids = array_unique( array_filter( array_map( 'absint', (array) apply_filters( 'kmst_pll_shop_pages', $ids ) ) ) );

		if ( '' !== $lang && function_exists( 'pll_get_post' ) ) {
			$ids = array_filter(
				$ids,
				static function ( $id ) use ( $lang ) {
					$translation = pll_get_post( $id, $lang );
					return ! $translation || (int) $translation === (int) $id;
				}
			);
		}

		$this->page_ids[ $lang ] = array_values( $ids );
		return $this->page_ids[ $lang ];
	}

	/**
	 * Register the rules, and flush once whenever they change (new language, shop slug or permalink base).
	 */
	public function add_rules() {
		$rules = $this->rules();
		foreach ( $rules as $pattern => $query ) {
			add_rewrite_rule( $pattern, $query, 'top' );
		}

		$hash = md5( wp_json_encode( $rules ) );
		if ( get_option( self::RULES_OPTION ) !== $hash ) {
			update_option( self::RULES_OPTION, $hash, false );
			flush_rewrite_rules( false );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Language of the current request                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Shop, product category/tag or product page.
	 *
	 * @return bool
	 */
	private function is_store_request() {
		if ( ! function_exists( 'is_shop' ) ) {
			return false;
		}
		if ( is_shop() || is_product_taxonomy() || is_product() ) {
			return true;
		}
		$lang = $this->current_prefixed_language();
		return '' !== $lang && is_page() && in_array( (int) get_queried_object_id(), $this->store_page_ids( $lang ), true );
	}

	/**
	 * Current language slug, or '' when it's the default language without a prefix.
	 *
	 * @return string
	 */
	private function current_prefixed_language() {
		$current = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
		return in_array( $current, $this->prefixed_languages(), true ) ? $current : '';
	}

	/**
	 * Don't redirect /ar/shop/ (and categories, tags, products) back to the default language.
	 *
	 * @param string|false $redirect_url Redirect target.
	 * @param mixed        $language     Polylang language (pll_check_canonical_url only).
	 * @return string|false
	 */
	public function keep_language_url( $redirect_url, $language = null ) {
		if ( $redirect_url && $this->is_store_request() && '' !== $this->current_prefixed_language() ) {
			return false;
		}
		return $redirect_url;
	}

	/* ------------------------------------------------------------------ */
	/* Links                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Put a URL in a given language (adds, replaces or removes the prefix).
	 *
	 * @param string $url  URL.
	 * @param string $slug Language slug.
	 * @return string
	 */
	private function in_language( $url, $slug ) {
		$pll      = PLL();
		$language = $pll->model->get_language( $slug );
		if ( ! $language || ! method_exists( $pll->links_model, 'switch_language_in_link' ) ) {
			return $url;
		}
		return $pll->links_model->switch_language_in_link( $url, $language );
	}

	/**
	 * On Arabic (non-default) pages, keep store links in that language.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public function link_in_current_language( $url ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $url;
		}
		// Every language, the default one included: the shop page may itself be in another language
		// (an English shop page gives /en/shop/ on Arabic pages). Background requests use the language
		// of the page that sent them.
		$lang = class_exists( 'KMST_Arabic_Strings' ) ? KMST_Arabic_Strings::language() : (string) pll_current_language( 'slug' );
		return '' !== $lang ? $this->in_language( $url, $lang ) : $url;
	}

	/**
	 * The address the checkout and the cart call in the background (wc-ajax). WooCommerce builds it from
	 * the site root, "/?wc-ajax=update_order_review", which has no language prefix. With the default
	 * language served without a prefix, that address *is* an Arabic address, so Polylang answers in Arabic
	 * and the English checkout gets an Arabic order box back. The address now carries the language of the
	 * page that will call it.
	 *
	 * @param string $url Endpoint URL, usually relative.
	 * @return string
	 */
	public function ajax_endpoint( $url ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $url;
		}

		$lang = class_exists( 'KMST_Arabic_Strings' ) ? KMST_Arabic_Strings::language() : (string) pll_current_language( 'slug' );
		if ( '' === $lang || '' === (string) $url ) {
			return $url;
		}

		// WooCommerce hands over a relative address; switching the language needs a whole one.
		$absolute = ( 0 === strpos( $url, 'http' ) ) ? $url : home_url( $url );
		$switched = $this->in_language( $absolute, $lang );

		// Keep it relative if that is how it arrived, so nothing else changes.
		if ( 0 !== strpos( $url, 'http' ) ) {
			$path = wp_parse_url( $switched, PHP_URL_PATH );
			$query = wp_parse_url( $switched, PHP_URL_QUERY );
			return ( null === $path ? '/' : $path ) . ( $query ? '?' . $query : '' );
		}

		return $switched;
	}

	/**
	 * Shop archive link.
	 *
	 * @param string $link      Link.
	 * @param string $post_type Post type.
	 * @return string
	 */
	public function archive_link( $link, $post_type ) {
		return 'product' === $post_type ? $this->link_in_current_language( $link ) : $link;
	}

	/**
	 * Product category / tag links.
	 *
	 * @param string  $link     Link.
	 * @param WP_Term $term     Term.
	 * @param string  $taxonomy Taxonomy.
	 * @return string
	 */
	public function term_link( $link, $term, $taxonomy ) {
		return in_array( $taxonomy, array( 'product_cat', 'product_tag' ), true ) ? $this->link_in_current_language( $link ) : $link;
	}

	/**
	 * Product links.
	 *
	 * @param string  $link Link.
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public function product_link( $link, $post ) {
		return ( $post instanceof WP_Post && 'product' === $post->post_type ) ? $this->link_in_current_language( $link ) : $link;
	}

	/**
	 * Cart, checkout, my account, wishlist links (and every link built from them, like account endpoints and
	 * XStore's my-account/?et-wishlist-page) stay in the current language.
	 *
	 * @param string $link    Link.
	 * @param int    $post_id Page ID.
	 * @return string
	 */
	public function page_link( $link, $post_id ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $link;
		}
		$lang = class_exists( 'KMST_Arabic_Strings' ) ? KMST_Arabic_Strings::language() : (string) pll_current_language( 'slug' );
		if ( '' === $lang ) {
			return $link;
		}
		$is_store_page = in_array( (int) $post_id, $this->store_page_ids( $lang ), true );
		if ( ! $is_store_page && (int) $post_id !== $this->shop_page_id() ) {
			return $link;
		}
		return $this->in_language( $link, $lang );
	}

	/**
	 * The shop page saved in WooCommerce. Links the theme builds from it ("Continue shopping" in the cart,
	 * "Return to shop" in the mini cart) point at the archive, which lives under each language's prefix.
	 *
	 * @return int
	 */
	private function shop_page_id() {
		if ( class_exists( 'KMST_Polylang_Pages' ) ) {
			return KMST_Polylang_Pages::original_page_id( 'shop' );
		}
		return (int) get_option( 'woocommerce_shop_page_id' );
	}

	/**
	 * Language switcher on store pages: the same page in the other language, not that language's homepage.
	 *
	 * @param string $url  Translation URL.
	 * @param string $slug Language slug.
	 * @return string
	 */
	public function switcher_url( $url, $slug ) {
		// A page with its own translation (cart, checkout, my account made in WooCommerce > Language pages):
		// go to that translation, keeping the account section / order-received step and the query string.
		if ( is_page() && function_exists( 'pll_get_post' ) ) {
			$page        = (int) get_queried_object_id();
			$translation = $page ? (int) pll_get_post( $page, $slug ) : 0;
			if ( $translation && $translation !== $page ) {
				return $this->same_place( (string) get_permalink( $translation ) );
			}
		}

		if ( ! $this->is_store_request() || empty( $_SERVER['REQUEST_URI'] ) || empty( $_SERVER['HTTP_HOST'] ) ) {
			return $url;
		}

		$current = ( is_ssl() ? 'https://' : 'http://' )
			. sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) )
			. esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

		// Drop pagination: /ar/shop/page/3/ -> /shop/ in English.
		$current = preg_replace( '#/page/[0-9]+/?(\?|$)#', '/$1', $current );

		return $this->in_language( $current, $slug );
	}

	/**
	 * A translated page's address pointing at the same spot as the current request: the WooCommerce
	 * endpoint (orders, view-order/123, edit-address/billing, order-received/456...) and the query string
	 * (the order key on order-received, XStore's ?et-wishlist-page, ...).
	 *
	 * @param string $base Permalink of the translated page.
	 * @return string
	 */
	private function same_place( $base ) {
		if ( '' === $base ) {
			return $base;
		}

		if ( function_exists( 'WC' ) && WC()->query && function_exists( 'wc_get_endpoint_url' ) ) {
			$endpoint = (string) WC()->query->get_current_endpoint();
			if ( '' !== $endpoint ) {
				$base = wc_get_endpoint_url( $endpoint, (string) get_query_var( $endpoint ), $base );
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only copied into a link.
		$args = isset( $_GET ) ? (array) wp_unslash( $_GET ) : array();
		unset( $args['lang'] );
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) ) {
				unset( $args[ $key ] );
				continue;
			}
			$args[ $key ] = rawurlencode( sanitize_text_field( $value ) );
		}

		return $args ? add_query_arg( $args, $base ) : $base;
	}
}
