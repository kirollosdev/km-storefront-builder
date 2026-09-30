<?php
/**
 * Optional manual color per attribute term.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds a color picker to Products > Attributes > Configure terms, for names the detector can't guess.
 */
final class KMST_Swatches_Admin {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Register fields for every product attribute taxonomy.
	 */
	public function register() {
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return;
		}

		foreach ( wc_get_attribute_taxonomies() as $tax ) {
			$taxonomy = wc_attribute_taxonomy_name( $tax->attribute_name );

			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_field' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_field' ) );
			add_action( "created_{$taxonomy}", array( $this, 'save' ) );
			add_action( "edited_{$taxonomy}", array( $this, 'save' ) );
			add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'columns' ) );
			add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'column' ), 10, 3 );
		}
	}

	/**
	 * Color picker on attribute term screens only.
	 *
	 * @param string $hook Admin page.
	 */
	public function assets( $hook ) {
		if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
			return;
		}
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 0 !== strpos( $taxonomy, 'pa_' ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script(
			'wp-color-picker',
			"jQuery(function($){
				$('.kmvs-color-field').wpColorPicker();
				$(document).ajaxComplete(function(e, xhr, s){
					if (s && typeof s.data === 'string' && s.data.indexOf('action=add-tag') !== -1) {
						$('.kmvs-color-field').val('');
						$('#addtag .wp-color-result').css('background-color', '');
					}
				});
			});"
		);
		wp_add_inline_style( 'wp-color-picker', '.kmvs-dot{display:inline-block;width:22px;height:22px;border-radius:50%;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);vertical-align:middle}.kmvs-auto{margin-left:6px;color:#8c8f94;font-size:11px}' );
	}

	/**
	 * Field on "Add new term".
	 */
	public function add_field() {
		wp_nonce_field( 'kmvs_save_color', 'kmvs_color_nonce' );
		?>
		<div class="form-field">
			<label for="kmvs-color"><?php esc_html_e( 'Swatch color', 'km-storefront-builder' ); ?></label>
			<input type="text" id="kmvs-color" name="kmvs_color" class="kmvs-color-field" value="" />
			<p class="description"><?php esc_html_e( 'Optional. Leave empty to detect the color from the term name.', 'km-storefront-builder' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Field on "Edit term".
	 *
	 * @param WP_Term $term Term.
	 */
	public function edit_field( $term ) {
		$color = sanitize_hex_color( get_term_meta( $term->term_id, 'kmvs_color', true ) );
		?>
		<tr class="form-field">
			<th scope="row"><label for="kmvs-color"><?php esc_html_e( 'Swatch color', 'km-storefront-builder' ); ?></label></th>
			<td>
				<?php wp_nonce_field( 'kmvs_save_color', 'kmvs_color_nonce' ); ?>
				<input type="text" id="kmvs-color" name="kmvs_color" class="kmvs-color-field" value="<?php echo esc_attr( $color ); ?>" />
				<p class="description"><?php esc_html_e( 'Optional. Leave empty to detect the color from the term name.', 'km-storefront-builder' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save the color.
	 *
	 * @param int $term_id Term ID.
	 */
	public function save( $term_id ) {
		if ( ! isset( $_POST['kmvs_color_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kmvs_color_nonce'] ) ), 'kmvs_save_color' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		$color = isset( $_POST['kmvs_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['kmvs_color'] ) ) : '';

		if ( $color ) {
			update_term_meta( $term_id, 'kmvs_color', $color );
		} else {
			delete_term_meta( $term_id, 'kmvs_color' );
		}
	}

	/**
	 * Add a preview column after the checkbox.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$out = array();
		foreach ( $columns as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'cb' === $key ) {
				$out['kmvs_color'] = __( 'Swatch', 'km-storefront-builder' );
			}
		}
		if ( ! isset( $out['kmvs_color'] ) ) {
			$out['kmvs_color'] = __( 'Swatch', 'km-storefront-builder' );
		}
		return $out;
	}

	/**
	 * Preview column content: manual color, or the detected one marked "auto".
	 *
	 * @param string $content Content.
	 * @param string $column  Column.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public function column( $content, $column, $term_id ) {
		if ( 'kmvs_color' !== $column ) {
			return $content;
		}

		$manual = sanitize_hex_color( get_term_meta( $term_id, 'kmvs_color', true ) );
		if ( $manual ) {
			return '<span class="kmvs-dot" style="background:' . esc_attr( $manual ) . '"></span>';
		}

		$term   = get_term( $term_id );
		$colors = $term instanceof WP_Term ? KMST_Colors::resolve( $term->name ) : array();
		if ( ! $colors ) {
			return '&mdash;';
		}

		return '<span class="kmvs-dot" style="background:' . esc_attr( KMST_Colors::background( $colors ) ) . '"></span><span class="kmvs-auto">' . esc_html__( 'auto', 'km-storefront-builder' ) . '</span>';
	}
}
