<?php
/**
 * Hide the default product category on the front end.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Removes "Uncategorized" from front-end product_cat term queries: widgets, filters, category lists.
 */
final class KMST_Hide_Uncategorized {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_filter( 'get_terms_args', array( $this, 'exclude' ), 10, 2 );
	}

	/**
	 * Add the default category to the exclude list.
	 *
	 * @param array $args       Term query args.
	 * @param array $taxonomies Taxonomies.
	 * @return array
	 */
	public function exclude( $args, $taxonomies ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $args;
		}
		if ( ! in_array( 'product_cat', (array) $taxonomies, true ) ) {
			return $args;
		}

		$default = (int) get_option( 'default_product_cat', 0 );

		// Never break a query that asks for specific terms by ID.
		if ( ! $default || ! empty( $args['include'] ) ) {
			return $args;
		}

		$exclude         = wp_parse_id_list( isset( $args['exclude'] ) ? $args['exclude'] : array() );
		$exclude[]       = $default;
		$args['exclude'] = array_unique( $exclude );

		return $args;
	}
}
