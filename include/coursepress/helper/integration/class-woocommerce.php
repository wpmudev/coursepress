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

	private static $use_woo = false;
	private static $updated = false;
	private static $product_ctp = 'product';

	/**
	 * Initialize integration for WooCommerce checkout.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
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

		add_action(
			'before_delete_post',
			array( __CLASS__, 'update_product_when_deleting_course' )
		);

		add_action(
			'add_meta_boxes',
			array( __CLASS__, 'add_post_parent_metaboxe' )
		);

		add_action(
			'post_updated',
			array( __CLASS__, 'update_course_from_product' ),
			10, 3
		);
	}

	public static function is_payment_supported( $payment_supported, $course_id ) {
		return CoursePress_Core::get_setting( 'woocommerce/enabled', false );
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

		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'woo/product_id', false );
		$product_id = $product_id && get_post_status( $product_id ) ? $product_id : false;

		if ( $product_id ) {
			// Add WooCommerce product ID as indication.
			$mp_content .= ' <p class="description">';
			$mp_content .= sprintf( __( 'WooCommerce Product ID: %d', 'CP_TD' ), $product_id );
			$mp_content .= sprintf(
				' <a href="%s" target="_blank">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'id' => $product_id,
							'action' => 'edit',
						),
						admin_url( 'post.php' )
					)
				),
				__( 'Edit WooCommerce Product', 'CP_TD' )
			);
			$mp_content .= '</p> ';
		}
		$content .= $mp_content.'</div>';

		return $content;
	}

	public static function woo_product_id( $course_id = false ) {
		$args = array(
			'posts_per_page' => 1,
			'post_type'		 => self::$product_ctp,
			'post_parent'	 => $course_id,
			'post_status'	 => 'any',
			'fields'		 => 'ids',
		);

		$products = get_posts( $args );

		if ( isset( $products[0] ) ) {
			return (int) $products[0];
		} else {
			return false;
		}
		// copmare to this value!:
		//CoursePress_Data_Course::get_setting( $course_id, 'woo/product_id' );
	}

	public static function update_product( $course_id, $settings ) {
		$automatic_sku_number = 'CP-' . $course_id;

		if ( ! self::$use_woo ) {
			do_action( 'coursepress_mp_update_product', $course_id );
			return true;
		}

		$mp_product_id = self::woo_product_id( $course_id );

		$course = get_post( $course_id );

		$post = array(
			'post_status'  => 'publish',
			'post_title'   => CoursePress_Helper_Utility::filter_content( $course->post_title, true ),
			'post_type'    => self::$product_ctp,
			'post_parent'  => $course_id,
			'post_content' => CoursePress_Helper_Utility::filter_content( $course->post_content, true ),
		);

		// Add or Update a product if its a paid course
		if ( isset( $settings['payment_paid_course'] ) && 'on' == $settings['payment_paid_course'] ) {

			if ( $mp_product_id ) {
				$post['ID'] = $mp_product_id; //If ID is set, wp_insert_post will do the UPDATE instead of insert
			}

			$post_id = wp_insert_post( $post );
			update_post_meta( $post_id, '_stock_status', 'instock' );

			// Only works if the course actually has a thumbnail.
			set_post_thumbnail( $post_id, get_post_thumbnail_id( $course_id ) );

			$automatic_sku = $settings['mp_auto_sku'];

			if ( $automatic_sku == 'on' ) {
				$sku[0] = $automatic_sku_number;
			} else {
				$sku[0] = CoursePress_Helper_Utility::filter_content( ( ! empty( $settings['mp_sku'] ) ? $settings['mp_sku'] : '' ), true );
			}

			if ( self::$use_woo ) {
				CoursePress_Data_Course::update_setting( $course_id, 'woo/product_id', $post_id );

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
						CoursePress_Data_Course::delete_setting( $course_id, 'woo' );
						wp_delete_post( $mp_product_id );
					} else {
						wp_update_post(
							array(
								'ID' => $mp_product_id,
								'post_status' => 'draft',
							)
						);
						update_post_meta( $mp_product_id, '_stock_status', 'outofstock' );
					}
				}
			}
		}
	}

	public static function update_product_when_deleting_course( $course_id ) {
		/**
		 * if we do not use woo, then we should not use this function
		 */
		if ( ! self::$use_woo ) {
			return;
		}
		$post_type = get_post_type( $course_id );
		$course_post_type = CoursePress_Data_Course::get_post_type_name();
		/**
		 * handle only correct post_type
		 */
		if ( $course_post_type != $post_type ) {
			return;
		}
		/**
		 * get product
		 */
		$mp_product_id = self::woo_product_id( $course_id );
		if ( empty( $mp_product_id ) ) {
			return;
		}
		$delete = CoursePress_Core::get_setting( 'woocommerce/delete', 'change_status' );
		if ( 'delete' == $delete ) {
			CoursePress_Data_Course::delete_setting( $course_id, 'woo' );
			wp_delete_post( $mp_product_id );
		} else {
			wp_update_post(
				array(
					'ID' => $mp_product_id,
					'post_status' => 'draft',
				)
			);
			update_post_meta( $mp_product_id, '_stock_status', 'outofstock' );
		}
	}

	public static function add_post_parent_metaboxe() {
		add_meta_box( 'cp_woo_post_parent', __( 'Parent Course', 'cp' ), array( __CLASS__, 'cp_woo_post_parent_box_content' ), self::$product_ctp, 'side', 'default' );
	}

	public static function cp_woo_post_parent_box_content() {
		global $post;
		if ( isset( $post->ID ) ) {
?>
                    <input type="text" name="parent_course" value="<?php echo esc_attr( wp_get_post_parent_id( $post->ID ) ); ?>" />
<?php
		}
	}

	public static function update_course_from_product( $product_id, $post, $before_update ) {
		if ( ! self::$use_woo ) {
			return;
		}
		$x = '';
		// If its not a product, exit
		if ( self::$product_ctp !== $post->post_type  ) {
			return;
		}

		// If update is caused by this class already, then bail
		if ( self::$updated ) {
			self::$updated = false;
			return;
		}

		$course_id = (int) get_post_meta( $product_id, 'cp_course_id', true );

		// No point proceeding if there is no associated course
		if ( empty( $course_id ) ) {
			return;
		}

		$meta = array(
			'mp_product_price'      => get_post_meta( $product_id, '_regular_price', true ),
			'mp_product_sale_price' => get_post_meta( $product_id, '_sale_price', true ),
			'mp_sku'                => get_post_meta( $product_id, '_sku', true ),
		);

		foreach ( $meta as $key => $value ) {
			CoursePress_Data_Course::update_setting( $course_id, $key, $value );
		}
		self::$updated = true;
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
