<?php
/**
 * Free Polylang + WooCommerce: a cart, checkout, my account and terms page per language.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce knows one page for each of cart, checkout, my account and terms. Polylang can translate those
 * pages, but WooCommerce would still send everyone to the original. This:
 *
 * - makes wc_get_page_id() return the page in the visitor's language, so every WooCommerce link, redirect,
 *   is_cart() / is_checkout() check, account section and the order-received page follow the language
 * - works out the language of WooCommerce's AJAX calls (checkout, cart updates) from the page that sent them
 * - adds WooCommerce > Language pages, which creates the missing translations as copies (Elementor design
 *   included) and links them in Polylang
 *
 * What the paid Polylang for WooCommerce does for these pages; switches itself off when that is active.
 */
final class KMST_Polylang_Pages {

	const ADMIN_PAGE = 'kmst-language-pages';

	/**
	 * WooCommerce pages handled here, key => [English title, English slug].
	 *
	 * @return array
	 */
	public static function pages() {
		return array(
			'cart'      => array( __( 'Cart', 'km-storefront-builder' ), 'shopping-cart' ),
			'checkout'  => array( __( 'Checkout', 'km-storefront-builder' ), 'secure-checkout' ),
			'myaccount' => array( __( 'My account', 'km-storefront-builder' ), 'account' ),
			'terms'     => array( __( 'Terms and conditions', 'km-storefront-builder' ), 'terms-and-conditions' ),
		);
	}

	/**
	 * Can this run?
	 *
	 * @return bool
	 */
	public static function available() {
		return function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' )
			&& ! defined( 'PLLWC_VERSION' ) && ! class_exists( 'Polylang_Woocommerce' );
	}

	/**
	 * Language worked out for this request (cached).
	 *
	 * @var string|null
	 */
	private $lang = null;

	/**
	 * Report of the last "create" run.
	 *
	 * @var array
	 */
	private $report = array();

	/**
	 * Hook in.
	 */
	public function __construct() {
		foreach ( array_keys( self::pages() ) as $page ) {
			add_filter( 'woocommerce_get_' . $page . '_page_id', array( $this, 'page_in_language' ), 20 );
			// XStore (header account panel, mobile menu, wishlist / compare links) reads the saved setting
			// directly with get_option( 'woocommerce_myaccount_page_id' ), so the setting follows the language too.
			add_filter( 'option_woocommerce_' . $page . '_page_id', array( $this, 'option_in_language' ), 20 );
		}

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 70 );
		}
	}

	/**
	 * Set while reading the saved (original) page IDs, so the option filter leaves them alone.
	 *
	 * @var bool
	 */
	private static $reading_original = false;

	/**
	 * The page saved in WooCommerce > Settings > Advanced, whatever the visitor's language.
	 *
	 * @param string $key cart, checkout, myaccount or terms.
	 * @return int
	 */
	public static function original_page_id( $key ) {
		self::$reading_original = true;
		$id = (int) get_option( 'woocommerce_' . $key . '_page_id' );
		self::$reading_original = false;
		return $id;
	}

	/**
	 * get_option( 'woocommerce_{page}_page_id' ) on the front end: the page in the visitor's language.
	 *
	 * @param mixed $value Saved page ID.
	 * @return mixed
	 */
	public function option_in_language( $value ) {
		if ( self::$reading_original || ! is_numeric( $value ) ) {
			return $value;
		}
		return (string) $this->page_in_language( (int) $value );
	}

	/* ------------------------------------------------------------------ */
	/* WooCommerce page IDs per language                                   */
	/* ------------------------------------------------------------------ */

	/**
	 * The visitor's language: Polylang's, or for WooCommerce AJAX calls (which go to /?wc-ajax=... without a
	 * language prefix) the language of the page that made the call.
	 *
	 * @return string
	 */
	private function language() {
		if ( null !== $this->lang ) {
			return $this->lang;
		}

		$lang = '';

		// admin-ajax, WooCommerce's ?wc-ajax= calls, and the REST API (block checkout: /wp-json/wc/store/...).
		// Detected from the URL so it also works before WordPress defines REST_REQUEST.
		$uri     = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$rest    = function_exists( 'rest_get_url_prefix' ) && false !== strpos( $uri, '/' . rest_get_url_prefix() . '/' );
		$is_ajax = wp_doing_ajax() || isset( $_GET['wc-ajax'] ) || isset( $_GET['rest_route'] ) || $rest; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $is_ajax && ! empty( $_SERVER['HTTP_REFERER'] ) && function_exists( 'PLL' ) ) {
			$pll = PLL();
			if ( ! empty( $pll->links_model ) && method_exists( $pll->links_model, 'get_language_from_url' ) ) {
				$lang = (string) $pll->links_model->get_language_from_url( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
			}
			if ( '' === $lang && function_exists( 'pll_default_language' ) ) {
				$lang = (string) pll_default_language( 'slug' ); // No prefix in the referring URL: the default language.
			}
		}

		if ( '' === $lang ) {
			$lang = (string) pll_current_language( 'slug' );
		}

		// Don't remember an answer from before Polylang picked the language.
		if ( '' !== $lang ) {
			$this->lang = $lang;
		}

		return $lang;
	}

	/**
	 * The page in the visitor's language, or the original when it has no translation.
	 *
	 * @param int $page_id Page saved in WooCommerce > Settings > Advanced.
	 * @return int
	 */
	public function page_in_language( $page_id ) {
		$page_id = (int) $page_id;
		if ( $page_id <= 0 || ( is_admin() && ! wp_doing_ajax() ) ) {
			return $page_id;
		}

		$lang = $this->language();
		if ( '' === $lang ) {
			return $page_id;
		}

		$translated = (int) pll_get_post( $page_id, $lang );
		return ( $translated && 'publish' === get_post_status( $translated ) ) ? $translated : $page_id;
	}

	/* ------------------------------------------------------------------ */
	/* WooCommerce > Language pages                                        */
	/* ------------------------------------------------------------------ */

	/**
	 * Menu entry.
	 */
	public function admin_menu() {
		$hook = add_submenu_page(
			'woocommerce',
			__( 'Language pages', 'km-storefront-builder' ),
			__( 'Language pages', 'km-storefront-builder' ),
			'manage_woocommerce',
			self::ADMIN_PAGE,
			array( $this, 'render' )
		);
		add_action( 'load-' . $hook, array( $this, 'handle' ) );
	}

	/**
	 * "Create missing translations" button.
	 */
	public function handle() {
		if ( empty( $_POST['kmst_create_pages'] ) ) {
			return;
		}
		check_admin_referer( 'kmst_language_pages' );
		if ( ! current_user_can( 'manage_woocommerce' ) || ! current_user_can( 'publish_pages' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'km-storefront-builder' ) );
		}
		$this->create_missing();
	}

	/**
	 * Languages, slug => name.
	 *
	 * @return array
	 */
	private function languages() {
		$slugs = (array) pll_languages_list( array( 'fields' => 'slug' ) );
		$names = (array) pll_languages_list( array( 'fields' => 'name' ) );
		$out   = array();
		foreach ( $slugs as $i => $slug ) {
			$out[ $slug ] = isset( $names[ $i ] ) ? $names[ $i ] : $slug;
		}
		return $out;
	}

	/**
	 * Create a translation for every WooCommerce page and language that lacks one.
	 */
	private function create_missing() {
		$languages = $this->languages();
		$default   = function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : '';

		foreach ( self::pages() as $key => $info ) {
			$source = self::original_page_id( $key );
			if ( $source <= 0 || ! get_post( $source ) ) {
				/* translators: %s: page */
				$this->report[] = array( 'warning', sprintf( __( '%s: no page is set in WooCommerce > Settings > Advanced, skipped.', 'km-storefront-builder' ), $info[0] ) );
				continue;
			}

			// The original needs a language first; pages created before Polylang may have none.
			$source_lang = (string) pll_get_post_language( $source, 'slug' );
			if ( '' === $source_lang && '' !== $default ) {
				pll_set_post_language( $source, $default );
				$source_lang = $default;
				/* translators: 1: page title, 2: language */
				$this->report[] = array( 'info', sprintf( __( '"%1$s" had no language; set to %2$s.', 'km-storefront-builder' ), get_the_title( $source ), $languages[ $default ] ?? $default ) );
			}

			$translations                 = (array) pll_get_post_translations( $source );
			$translations[ $source_lang ] = $source;

			foreach ( $languages as $lang => $name ) {
				if ( ! empty( $translations[ $lang ] ) && get_post( $translations[ $lang ] ) ) {
					continue;
				}

				$copy = $this->copy_page( $source, $lang, $info );
				if ( ! $copy ) {
					/* translators: 1: page, 2: language */
					$this->report[] = array( 'error', sprintf( __( 'Could not create %1$s (%2$s).', 'km-storefront-builder' ), $info[0], $name ) );
					continue;
				}

				pll_set_post_language( $copy, $lang );
				$translations[ $lang ] = $copy;
				pll_save_post_translations( $translations );

				/* translators: 1: page, 2: language */
				$this->report[] = array( 'created', sprintf( __( '%1$s (%2$s) created.', 'km-storefront-builder' ), get_the_title( $copy ), $name ), $copy );
			}
		}

		if ( class_exists( '\Elementor\Plugin' ) && ! empty( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		flush_rewrite_rules( false );

		if ( ! $this->report ) {
			$this->report[] = array( 'info', __( 'Every WooCommerce page already has its translations. Nothing to create.', 'km-storefront-builder' ) );
		}
	}

	/**
	 * Copy a page with its content and design (Elementor data, template, XStore page options).
	 *
	 * @param int    $source Page.
	 * @param string $lang   Language of the copy.
	 * @param array  $info   [English title, English slug].
	 * @return int
	 */
	private function copy_page( $source, $lang, array $info ) {
		$post = get_post( $source );
		if ( ! $post ) {
			return 0;
		}

		$english = 'en' === $lang || 0 === strpos( $lang, 'en' );
		$title   = $english ? $info[0] : $post->post_title . ' (' . strtoupper( $lang ) . ')';
		$slug    = $english ? $info[1] : $post->post_name . '-' . $lang;

		$copy = wp_insert_post(
			wp_slash(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_title'     => $title,
					'post_name'      => $slug,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_parent'    => $post->post_parent,
					'menu_order'     => $post->menu_order,
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_author'    => get_current_user_id(),
				)
			),
			true
		);
		if ( is_wp_error( $copy ) || ! $copy ) {
			return 0;
		}

		// Everything that shapes the page, but not locks, old slugs, caches or Polylang's own data.
		$skip = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date', '_elementor_css', '_elementor_element_cache', '_elementor_page_assets' );
		foreach ( (array) get_post_meta( $source ) as $key => $values ) {
			if ( in_array( $key, $skip, true ) || 0 === strpos( $key, '_pll_' ) ) {
				continue;
			}
			foreach ( (array) $values as $value ) {
				add_post_meta( $copy, $key, wp_slash( maybe_unserialize( $value ) ) );
			}
		}

		return (int) $copy;
	}

	/**
	 * The screen.
	 */
	public function render() {
		$languages = $this->languages();

		echo '<div class="wrap"><h1>' . esc_html__( 'Language pages', 'km-storefront-builder' ) . '</h1>';
		echo '<p style="max-width:820px">' . esc_html__( 'A cart, checkout, my account and terms page for each language. WooCommerce uses the page in the visitor\'s language for every link, redirect, account section and the order-received page. Each copy starts with the same design as the original; edit it with Elementor to change its texts.', 'km-storefront-builder' ) . '</p>';

		if ( $this->report ) {
			echo '<div class="notice notice-success" style="padding:10px 14px">';
			foreach ( $this->report as $row ) {
				$icon = array(
					'created' => '✅',
					'info'    => 'ℹ️',
					'warning' => '⚠️',
					'error'   => '❌',
				);
				echo '<p style="margin:4px 0">' . esc_html( ( $icon[ $row[0] ] ?? '' ) . ' ' . $row[1] );
				if ( ! empty( $row[2] ) ) {
					echo ' &mdash; <a href="' . esc_url( admin_url( 'post.php?post=' . (int) $row[2] . '&action=elementor' ) ) . '" target="_blank">' . esc_html__( 'Edit with Elementor', 'km-storefront-builder' ) . '</a>';
					echo ' &middot; <a href="' . esc_url( get_permalink( (int) $row[2] ) ) . '" target="_blank">' . esc_html__( 'View', 'km-storefront-builder' ) . '</a>';
				}
				echo '</p>';
			}
			echo '</div>';
		}

		echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>' . esc_html__( 'Page', 'km-storefront-builder' ) . '</th>';
		foreach ( $languages as $name ) {
			echo '<th>' . esc_html( $name ) . '</th>';
		}
		echo '</tr></thead><tbody>';

		$missing = 0;
		foreach ( self::pages() as $key => $info ) {
			$source = self::original_page_id( $key );
			echo '<tr><td><strong>' . esc_html( $info[0] ) . '</strong></td>';
			$translations = $source ? (array) pll_get_post_translations( $source ) : array();
			$source_lang  = $source ? (string) pll_get_post_language( $source, 'slug' ) : '';
			if ( $source && '' !== $source_lang ) {
				$translations[ $source_lang ] = $source;
			}
			foreach ( array_keys( $languages ) as $lang ) {
				$id = isset( $translations[ $lang ] ) ? (int) $translations[ $lang ] : 0;
				if ( $id && get_post( $id ) ) {
					echo '<td><a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a> <code>' . esc_html( wp_make_link_relative( get_permalink( $id ) ) ) . '</code></td>';
				} else {
					echo '<td><em>' . esc_html__( 'missing', 'km-storefront-builder' ) . '</em></td>';
					$missing++;
				}
			}
			echo '</tr>';
		}
		echo '</tbody></table>';

		echo '<form method="post" style="margin-top:16px">';
		wp_nonce_field( 'kmst_language_pages' );
		submit_button(
			$missing ? __( 'Create the missing pages', 'km-storefront-builder' ) : __( 'All pages exist', 'km-storefront-builder' ),
			'primary',
			'kmst_create_pages',
			false,
			$missing ? array() : array( 'disabled' => 'disabled' )
		);
		echo '</form>';

		echo '<h2>' . esc_html__( 'After creating', 'km-storefront-builder' ) . '</h2><ol style="max-width:820px">';
		echo '<li>' . esc_html__( 'Open each new page with Elementor and change any text you typed into the design (headings, notes). WooCommerce\'s own texts, like "Proceed to checkout" and the form labels, switch language by themselves.', 'km-storefront-builder' ) . '</li>';
		echo '<li>' . esc_html__( 'If a menu, header or footer links to the cart or account page as a custom link, point the English one at the English page.', 'km-storefront-builder' ) . '</li>';
		echo '<li>' . esc_html__( 'Clear the site cache.', 'km-storefront-builder' ) . '</li>';
		echo '</ol></div>';
	}
}
