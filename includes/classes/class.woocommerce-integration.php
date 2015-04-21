<?php
/*
 * Integration with WooCommerce plugin
 * https://wordpress.org/plugins/woocommerce/
 * 
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( !class_exists( 'CP_WooCommerce_Integration' ) ) {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		class CP_WooCommerce_Integration {

			function __construct() {
				add_action( 'coursepress_general_options_page', array( &$this, 'add_woocommerce_general_option' ) );
				add_action( 'coursepress_update_settings', array( &$this, 'save_woocommerce_general_option' ), 10, 2 );
			}

			function add_woocommerce_general_option() {
				?>
				<div class="postbox">
					<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'WooCommerce Integration', 'cp' ); ?></span></h3>

					<div class="inside">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><?php _e( 'Use WooCommerce to sell courses', 'cp' ); ?></th>
									<td>
										<a class="help-icon" href="javascript:;"></a>

										<div class="tooltip">
											<div class="tooltip-before"></div>
											<div class="tooltip-button">&times;</div>
											<div class="tooltip-content">
												<?php _e( 'If checked, WooCommerce will be use instead of the MarketPress for selling courses', 'cp' ) ?>
											</div>
										</div>
										<input type='checkbox' name='option_use_woo' <?php echo( ( get_option( 'use_woo', 0 ) ) ? 'checked' : '' ); ?> />
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			}

			function save_woocommerce_general_option( $tab, $post ) {
				if ( $tab == 'general' ) {
					if ( isset( $post[ 'option_use_woo' ] ) ) {
						update_option( 'use_woo', 1 );
					} else {
						update_option( 'use_woo', 0 );
					}
				}
			}
			
			public static function woo_product_id( $course_id = false ) {
					$args		 = array(
						'posts_per_page' => 1,
						'post_type'		 => 'product',
						'post_parent'	 => $course_id,
						'post_status'	 => 'publish',
						'fields'		 => 'ids',
					);

					$products = get_posts( $args );
					
					if ( isset( $products[ 0 ] ) ) {
						return (int) $products[ 0 ];
					} else {
						return false;
					}
				}

		}

		$cp_woo = new CP_WooCommerce_Integration();
	}
}
