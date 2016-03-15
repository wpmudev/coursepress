<?php
/**
 * Helper functions.
 * Integrate other plugins with CoursePress.
 *
 * Note: This file is only loaded, if the Woo-Integration is enabled in
 * CoursePress - so we do not need to check if setting 'woocommerce/use' is true.
 *
 * @package  CoursePress
 */

/**
 * Integrates WooCommerce with CoursePress.
 */
class CoursePress_Helper_Integration_WooCommerce {

	static private $use_woo = false;

	/**
	 * Initialize integration for WooCommerce checkout.
	 *
	 * @since  2.0.0
	 */
	static public function init() {
		// NOT DONE YET...
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return;
		}
		self::$use_woo = true;

		add_filter( 'coursepress_payment_supported', array( __CLASS__, 'is_payment_supported' ), 10, 2 );

		// Add additional fields to Course Setup Step 6 if paid is checked
		add_filter(
			'coursepress_course_setup_step_6_paid',
			array( __CLASS__, 'product_settings' ),
			10, 2
		);

		add_action(
			'coursepress_course_updated',
			array( __CLASS__, 'update_product' ),
			10, 2
		);
	}

	public static function is_payment_supported( $payment_supported, $course_id ) {
		return CoursePress_Core::get_setting( 'woocommerce/enabled', true );
	}

	public static function product_settings( $content, $course_id ) {
		// Prefix fields with meta_ to automatically add it to the course meta!
		$mp_content = '
			<div class="wide">
				<label>' .
					esc_html__( 'WooCommerce Product Settings', 'CP_TD' ) .
					'</label>
				<p class="description">' . esc_html__( 'Your course will be a new product in WooCommerce. Enter your course\'s payment settings below.', 'CP_TD' ) . '</p>

				<label class="normal required">
					' . esc_html__( 'Full Price', 'CP_TD' ) . '
				</label>
				<input type="text" name="meta_mp_product_price" value="' . CoursePress_Data_Course::get_setting( $course_id, 'mp_product_price', '' ) . '" />


				<label class="normal">
					' . esc_html__( 'Sale Price', 'CP_TD' ) . '
				</label>
				<input type="text" name="meta_mp_product_sale_price" value="' . CoursePress_Data_Course::get_setting( $course_id, 'mp_product_sale_price', '' ) . '" /><br >

				<label class="checkbox narrow">
					<input type="checkbox" name="meta_mp_sale_price_enabled" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'mp_sale_price_enabled', false ) ) . ' />
					<span>' . esc_html__( 'Enable Sale Price', 'CP_TD' ) . '</span>
				</label>

				<label class="normal">
					<span> ' . esc_html__( 'Course SKU:', 'CP_TD' ) . '</span>
				</label>
				<input type="text" name="meta_mp_sku" placeholder="' . sprintf( __( 'e.g. %s0001', 'CP_TD' ), apply_filters( 'coursepress_course_sku_prefix', 'CP-' ) ) . '" value="' . CoursePress_Data_Course::get_setting( $course_id, 'mp_sku', '' ) . '" /><br >
				<label class="checkbox narrow">
					<input type="checkbox" name="meta_mp_auto_sku" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'mp_auto_sku', false ) ) . ' />
					<span>' . esc_html__( 'Automatically generate Stock Keeping Units (SKUs)', 'CP_TD' ) . '</span>
				</label>';

		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'mp_product_id', false );
		$product_id = $product_id && get_post_status( $product_id ) ? $product_id : false;

		if ( $product_id ) {
			// Add MP product ID as indication.
			$mp_content .= '
				<label class="description">' . sprintf( __( 'MarketPress Product ID: %d', 'CP_TD' ), $product_id ) . '</label>
			';
		}

		$mp_content .= '
			</div>
		';

		$content .= $mp_content;

		return $content;
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

		if ( isset( $products[0] ) ) {
			return (int) $products[0];
		} else {
			return false;
		}
	}

	public static function update_product( $course_id, $settings ) {
		$automatic_sku_number = 'CP-' . $course_id;

		if ( self::$use_woo ) {
			$mp_product_id = self::woo_product_id( $course_id );
		} else {
			do_action( 'coursepress_mp_update_product', $course_id );
			return true;
		}

		$course = get_post( $course_id );

		$post = array(
			'post_status'  => 'publish',
			'post_title'   => CoursePress_Helper_Utility::filter_content( $course->post_title, true ),
			'post_type'	=> 'product',
			'post_parent'  => $course_id,
			'post_content' => CoursePress_Helper_Utility::filter_content( $course->post_content, true ),
		);

		// Add or Update a product if its a paid course
		if ( isset( $settings['payment_paid_course'] ) && 'on' == $settings['payment_paid_course'] ) {

			if ( $mp_product_id ) {
				$post['ID'] = $mp_product_id; //If ID is set, wp_insert_post will do the UPDATE instead of insert
			}

			$post_id = wp_insert_post( $post );

			// Only works if the course actually has a thumbnail.
			set_post_thumbnail( $post_id, get_post_thumbnail_id( $course_id ) );

			$automatic_sku = $settings['mp_auto_sku'];

			if ( $automatic_sku == 'on' ) {
				$sku[0] = $automatic_sku_number;
			} else {
				$sku[0] = CoursePress_Helper_Utility::filter_content( ( ! empty( $settings['mp_sku'] ) ? $settings['mp_sku'] : '' ), true );
			}

			if ( self::$use_woo ) {
				update_post_meta( $course_id, 'woo_product_id', $post_id );
				update_post_meta( $course_id, 'woo_product', $post_id );

				$price	  = CoursePress_Helper_Utility::filter_content( ( ! empty( $settings['mp_product_price'] ) ? $settings['mp_product_price'] : 0 ), true );
				$sale_price = CoursePress_Helper_Utility::filter_content( ( ! empty( $settings['mp_product_sale_price'] ) ? $settings['mp_product_sale_price'] : 0 ), true );

				update_post_meta( $post_id, '_virtual', 'yes' );
				update_post_meta( $post_id, '_sold_individually', 'yes' );
				update_post_meta( $post_id, '_sku', $sku[0] );
				update_post_meta( $post_id, '_regular_price', $price );
				update_post_meta( $post_id, '_visibility', 'visible' );

				if ( ! empty( $settings['mp_sale_price_enabled'] ) ) {
					update_post_meta( $post_id, '_sale_price', $sale_price );
					update_post_meta( $post_id, '_price', $sale_price );
				} else {
					update_post_meta( $post_id, '_price', $price );
				}

				update_post_meta( $post_id, 'mp_sale_price_enabled', CoursePress_Helper_Utility::filter_content( ( ! empty( $settings['mp_sale_price_enabled'] ) ? $settings['mp_sale_price_enabled'] : '' ), true ) );
				update_post_meta( $post_id, 'cp_course_id', $course_id );
			}
			// Remove product if its not a paid course (clean up MarketPress products)
		} elseif ( isset( $settings['payment_paid_course'] ) && empty( $settings['payment_paid_course'] ) ) {
			if ( $mp_product_id && 0 != $mp_product_id ) {
				if ( self::$use_woo ) {
					$unpaid = CoursePress_Core::get_setting( 'woocommerce/unpaid', 'change_status' );
					if ( 'delete' == $unpaid ) {
						delete_post_meta( $course_id, 'woo_product_id' );
						delete_post_meta( $course_id, 'woo_product' );
						wp_delete_post( $mp_product_id );
					} else {
						wp_update_post(
							array(
								'ID' => $mp_product_id,
								'post_status' => 'draft',
							)
						);
					}
				}
			}
		}
	}
}

/**
 * Template functions
 */

if ( ! function_exists( 'cp_use_woo' ) ) {
	$active_plugins = apply_filters(
		'active_plugins',
		get_option( 'active_plugins' )
	);

	if ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
		function cp_use_woo() {
			return true;
		}
	} else {
		function cp_use_woo() {
			return false;
		}
	}
}

if ( ! function_exists( 'cp_redirect_woo_to_course' ) ) {
	function cp_redirect_woo_to_course() {
		$redirect_woo_to_course = get_option( 'redirect_woo_to_course', 0 );
		if ( ! $redirect_woo_to_course ) {
			return false;
		} else {
			return true;
		}
	}
}
