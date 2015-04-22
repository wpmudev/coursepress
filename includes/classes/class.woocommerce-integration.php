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
				add_filter( 'woocommerce_cart_item_name', array( &$this, 'change_cp_item_name' ), 10, 3 );
				add_filter( 'woocommerce_order_item_name', array( &$this, 'change_cp_order_item_name' ), 10, 2 );
			}

			function change_cp_item_name( $title, $cart_item, $cart_item_key ) {
				$course_id = wp_get_post_parent_id( $cart_item[ 'product_id' ] );
				if ( $course_id && get_post_type( $course_id ) == 'course' ) {
					return get_the_title( $course_id );
				}
				return $title;
			}

			function change_cp_order_item_name( $name, $item ) {
				$product_id = isset( $item[ 'item_meta' ][ '_product_id' ] ) ? $item[ 'item_meta' ][ '_product_id' ] : '';
				$product_id = $product_id[0];
				if ( is_numeric( $product_id ) ) {
					$course_id = wp_get_post_parent_id( $product_id );
					if ( $course_id && get_post_type( $course_id ) == 'course' ) {
						return get_the_title( $course_id );
					}
				}
				return $name;
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

								<tr valign="top">
									<th scope="row"><?php _e( 'Redirect WooCommerce product post to a parent course post', 'cp' ); ?></th>
									<td>
										<a class="help-icon" href="javascript:;"></a>

										<div class="tooltip">
											<div class="tooltip-before"></div>
											<div class="tooltip-button">&times;</div>
											<div class="tooltip-content">
												<?php _e( 'If checked, visitors who try to access WooCommerce single post will be automatically redirected to a parent course single post.', 'cp' ) ?>
											</div>
										</div>
										<input type='checkbox' name='option_redirect_woo_to_course' <?php echo( ( get_option( 'redirect_woo_to_course', 0 ) ) ? 'checked' : '' ); ?> />
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

					if ( isset( $post[ 'option_redirect_woo_to_course' ] ) ) {
						update_option( 'redirect_woo_to_course', 1 );
					} else {
						update_option( 'redirect_woo_to_course', 0 );
					}
				}
			}

			public static function woo_product_id( $course_id = false ) {
				$args = array(
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

			public static function add_product_to_cart( $product_id ) {
				global $woocommerce;
				$found = false;

				//check if product already in cart
				if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
					foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
						$_product	 = $values[ 'data' ];
						if ( $_product->id == $product_id )
							$found		 = true;
					}
					// if product not found, add it
					if ( !$found )
						$woocommerce->cart->add_to_cart( $product_id );
				} else {
					// if no products in cart, add it
					$woocommerce->cart->add_to_cart( $product_id );
				}
			}

		}

		$cp_woo = new CP_WooCommerce_Integration();
	} else {
		update_option( 'use_woo', 0 ); //if user deactivate woocommerce plugin
	}
}
