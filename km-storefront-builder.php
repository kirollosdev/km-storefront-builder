<?php
/**
 * Plugin Name:       KM Storefront Builder
 * Plugin URI:        https://github.com/kirollosdev/km-storefront-builder
 * Description:       Store tweaks for the XStore theme: color circle and label box variation swatches on the single product page and in the quick view popup, hides the "Uncategorized" category from the shop, turns the filter +/− toggles into up/down arrows, adds a Horizontal Filters Elementor widget and [kmst_filter_bar] shortcode, and replaces "Select options" on product cards with Quick view or an in-place size picker. Each feature can be switched off in WooCommerce > Settings > Products > Store tools.
 * Version:           2.12.0
 * Author:            Kirollos Magdy
 * Author URI:        https://kirollosmagdy.com
 * License:           Proprietary, all rights reserved
 * License URI:       https://github.com/kirollosdev/km-storefront-builder/blob/main/LICENSE
 * Text Domain:       km-storefront-builder
 * Requires PHP:      7.4
 * Requires at least: 5.9
 * WC requires at least: 6.0
 *
 * @package KM_Storefront_Builder
 *
 * Copyright (c) 2026 Kirollos Magdy. All rights reserved.
 */

defined( 'ABSPATH' ) || exit;

/*
 * This plugin used to be called KM Storefront Tools (folder km-storefront-tools). Both copies declare the same
 * classes, so switching one on while the other runs would be a fatal error. Activating this one switches the
 * old one off; until then, whichever loaded second stays out of the way.
 */
register_activation_hook(
	__FILE__,
	static function () {
		deactivate_plugins( 'km-storefront-tools/km-storefront-tools.php', true );
	}
);

if ( defined( 'KMST_VERSION' ) ) {
	add_action(
		'admin_notices',
		static function () {
			if ( current_user_can( 'activate_plugins' ) ) {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'KM Storefront Builder replaces KM Storefront Tools. Please deactivate and delete KM Storefront Tools.', 'km-storefront-builder' ) . '</p></div>';
			}
		}
	);
	return;
}

define( 'KMST_VERSION', '2.12.0' );
define( 'KMST_FILE', __FILE__ );
define( 'KMST_PATH', plugin_dir_path( __FILE__ ) );
define( 'KMST_URL', plugin_dir_url( __FILE__ ) );

require_once KMST_PATH . 'includes/class-kmst-settings.php';
require_once KMST_PATH . 'includes/class-kmst-hide-uncategorized.php';
require_once KMST_PATH . 'includes/class-kmst-filter-arrows.php';
require_once KMST_PATH . 'includes/swatches/class-kmst-colors.php';
require_once KMST_PATH . 'includes/swatches/class-kmst-swatches.php';
require_once KMST_PATH . 'includes/swatches/class-kmst-swatches-admin.php';
require_once KMST_PATH . 'includes/swatches/class-kmst-swatches-elementor.php';
require_once KMST_PATH . 'includes/filter-bar/class-kmst-filter-bar.php';
require_once KMST_PATH . 'includes/class-kmst-loop-buttons.php';
require_once KMST_PATH . 'includes/class-kmst-card-extras.php';
require_once KMST_PATH . 'includes/class-kmst-polylang-shop.php';
require_once KMST_PATH . 'includes/category-slider/class-kmst-category-slider.php';
require_once KMST_PATH . 'includes/class-kmst-mobile-panel.php';
require_once KMST_PATH . 'includes/class-kmst-badges.php';
require_once KMST_PATH . 'includes/class-kmst-polylang-pages.php';
require_once KMST_PATH . 'includes/class-kmst-arabic-strings.php';
require_once KMST_PATH . 'includes/icons/class-kmst-icons.php';

/**
 * Settings from the store-specific plugin this grew out of (option names starting with mst_) are copied
 * to the new names once, so a site switching over keeps what it had.
 */
register_activation_hook(
	KMST_FILE,
	static function () {
		global $wpdb;

		if ( 'done' === get_option( 'kmst_migrated_from_mst' ) ) {
			return;
		}

		$rows = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'mst\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( (array) $rows as $row ) {
			$new = 'kmst_' . substr( $row->option_name, 4 );
			if ( false === get_option( $new, false ) ) {
				update_option( $new, maybe_unserialize( $row->option_value ) );
			}
		}

		update_option( 'kmst_migrated_from_mst', 'done' );
		flush_rewrite_rules();
	}
);

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', KMST_FILE, true );
		}
	}
);

// Translations (languages/km-storefront-builder-ar.mo). Loaded early so strings in settings and widgets are translated too.
add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'km-storefront-builder', false, dirname( plugin_basename( KMST_FILE ) ) . '/languages' );
	},
	0
);

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'KM Storefront Builder needs WooCommerce to be active.', 'km-storefront-builder' ) . '</p></div>';
				}
			);
			return;
		}

		new KMST_Settings();

		// The separate plugins this one replaces. Their features stay off here until they're deactivated, so nothing runs twice.
		$old = kmst_old_plugins();

		if ( KMST_Settings::enabled( 'swatches' ) && ! isset( $old['km-variation-swatches/km-variation-swatches.php'] ) ) {
			new KMST_Swatches();
			new KMST_Swatches_Elementor();
			if ( is_admin() ) {
				new KMST_Swatches_Admin();
			}
		}

		if ( KMST_Settings::enabled( 'hide_uncategorized' ) && ! isset( $old['km-hide-uncategorized/km-hide-uncategorized.php'] ) ) {
			new KMST_Hide_Uncategorized();
		}

		if ( KMST_Settings::enabled( 'filter_arrows' ) ) {
			new KMST_Filter_Arrows();
		}

		if ( KMST_Settings::enabled( 'filter_bar' ) ) {
			new KMST_Filter_Bar();
		}

		if ( KMST_Settings::enabled( 'loop_buttons' ) ) {
			new KMST_Loop_Buttons();
		}

		new KMST_Card_Extras();
		new KMST_Category_Slider();
		new KMST_Mobile_Panel();
		new KMST_Badges();
		new KMST_Arabic_Strings();

		if ( KMST_Settings::enabled( 'km_icons' ) ) {
			new KMST_Icons();
		}

		// Arabic (and other language) shop URLs with free Polylang. Polylang fires pll_init on plugins_loaded
		// at priority 1, usually before this runs, so start now if it already happened.
		if ( KMST_Settings::enabled( 'polylang_shop' ) ) {
			$start_pll_shop = static function () {
				if ( KMST_Polylang_Shop::available() ) {
					new KMST_Polylang_Shop();
				}
			};
			if ( did_action( 'pll_init' ) ) {
				$start_pll_shop();
			} else {
				add_action( 'pll_init', $start_pll_shop );
			}
		}

		// A cart, checkout, my account and terms page per language.
		if ( KMST_Settings::enabled( 'polylang_pages' ) ) {
			$start_pll_pages = static function () {
				if ( KMST_Polylang_Pages::available() ) {
					new KMST_Polylang_Pages();
				}
			};
			if ( did_action( 'pll_init' ) ) {
				$start_pll_pages();
			} else {
				add_action( 'pll_init', $start_pll_pages );
			}
		}

		if ( $old ) {
			add_action(
				'admin_notices',
				static function () use ( $old ) {
					if ( ! current_user_can( 'activate_plugins' ) ) {
						return;
					}
					echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'KM Storefront Builder', 'km-storefront-builder' ) . ':</strong> ';
					echo esc_html__( 'please deactivate and delete these plugins, their features are now included here:', 'km-storefront-builder' ) . ' ';
					echo esc_html( implode( ', ', $old ) ) . '</p></div>';
				}
			);
		}
	}
);

/**
 * Active plugins that KM Storefront Builder replaces, file => name.
 *
 * @return array
 */
function kmst_old_plugins() {
	$replaced = array(
		'km-variation-swatches/km-variation-swatches.php'     => 'KM Variation Swatches',
		'km-hide-uncategorized/km-hide-uncategorized.php'     => 'KM Hide Uncategorized',
		'km-product-grid-gallery/km-product-grid-gallery.php' => 'KM Product Grid Gallery',
	);

	$active = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
	}

	return array_intersect_key( $replaced, array_flip( $active ) );
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	static function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=products&section=mst' ) ) . '">' . esc_html__( 'Settings', 'km-storefront-builder' ) . '</a>' );
		return $links;
	}
);
