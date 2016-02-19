<?php

class CoursePress_Helper_Integration_MarketPress {

	private static $updated = false;
	private static $course_id = 0;

	public static function init() {

		// If MarketPress is not activated, just exit
		if ( ! CoursePress_Helper_Extensions_MarketPress::activated() ) {
			return false;
		}

		// Enable Payment Support
		add_filter( 'coursepress_payment_supported', array( __CLASS__, 'enable_payment' ) );

		// Add additional fields to Course Setup Step 6 if paid is checked
		add_filter( 'coursepress_course_setup_step_6_paid', array( __CLASS__, 'product_settings' ), 10, 2 );

		// Add MP Product if needed
		add_filter( 'coursepress_course_update_meta', array( __CLASS__, 'maybe_create_product' ), 10, 2 );

		// Respond to Course Update/Create
		add_action( 'coursepress_course_created', array( __CLASS__, 'update_product_from_course' ), 10, 2 );
		add_action( 'coursepress_course_updated', array( __CLASS__, 'update_product_from_course' ), 10, 2 );

		// If for whatever reason the course gets updated in MarketPress, reflect those changes in the course
		add_action( 'post_updated', array( __CLASS__, 'update_course_from_product' ), 10, 3 );

		add_filter( 'coursepress_shortcode_course_cost', array( __CLASS__, 'shortcode_cost' ), 10, 2 );

		add_filter( 'mp_order_notification_subject', 'cp_mp_order_notification_subject', 10, 2 );

		add_filter( 'mp_order_notification_body', 'cp_mp_order_notification_body', 10, 2 );
	}



	public static function enable_payment( $payment_supported ) {
		$payment_supported = true;
		return $payment_supported;
	}

	public static function product_settings( $content, $course_id ) {

		// TIP: Use fields with meta_ prefix to automatically add it to the course meta
		$mp_content = '
			<div class="wide">
				<label>' .
					esc_html__( 'MarketPress Product Settings', CoursePress::TD ) .
					'</label>
				<p class="description">' . esc_html__( 'Your course will be a new product in MarketPress. Enter your course\'s payment settings below.', CoursePress::TD ) . '</p>

				<label class="normal required">
					' . esc_html__( 'Full Price', CoursePress::TD ) . '
				</label>
				<input type="text" name="meta_mp_product_price" value="' . CoursePress_Model_Course::get_setting( $course_id, 'mp_product_price', '' ) . '" />


				<label class="normal">
					' . esc_html__( 'Sale Price', CoursePress::TD ) . '
				</label>
				<input type="text" name="meta_mp_product_sale_price" value="' . CoursePress_Model_Course::get_setting( $course_id, 'mp_product_sale_price', '' ) . '" /><br >

				<label class="checkbox narrow">
					<input type="checkbox" name="meta_mp_sale_price_enabled" ' . CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'mp_sale_price_enabled', false ) ) . ' />
					<span>' . esc_html__( 'Enable Sale Price', CoursePress::TD ) . '</span>
				</label>

				<label class="normal">
					<span> ' . esc_html__( 'Course SKU:', CoursePress::TD ) . '</span>
				</label>
				<input type="text" name="meta_mp_sku" placeholder="' . sprintf( __( 'e.g. %s0001', CoursePress::TD ), apply_filters( 'coursepress_course_sku_prefix', 'CP-' ) ) . '" value="' . CoursePress_Model_Course::get_setting( $course_id, 'mp_sku', '' ) . '" /><br >
				<label class="checkbox narrow">
					<input type="checkbox" name="meta_mp_auto_sku" ' . CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'mp_auto_sku', false ) ) . ' />
					<span>' . esc_html__( 'Automatically generate Stock Keeping Units (SKUs)', CoursePress::TD ) . '</span>
				</label>';

		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'mp_product_id', false );
		$product_id = ! empty( $product_id ) && get_post_status( $product_id ) ? $product_id : false;
		if ( false !== $product_id ) {
			// Add MP product ID as indication
			$mp_content .= '
				<label class="description">' . sprintf( __( 'MarketPress Product ID: %d', CoursePress::TD ), $product_id ) . '</label>
			';
		}

		$mp_content .= '
			</div>
		';

		$content .= $mp_content;

		return $content;
	}


	// Return settings, this is a filter
	public static function maybe_create_product( $settings, $course_id ) {

		// Settings fields will exist because it will be created with this integration in ::product_settings
		$is_paid = isset( $settings['payment_paid_course'] ) ? $settings['payment_paid_course'] : false;
		$is_paid = empty( $is_paid ) || 'off' === $is_paid ? false : true;

		// Check for existance of product id first
		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'mp_product_id', false );

		// Check if the corresponding product exists, if not, set product ID to false. This happens if the product "accidentally" got deleted.
		$product_id = ! empty( $product_id ) && get_post_status( $product_id ) ? $product_id : false;

		// Assume product does not exist and create one
		if ( false === $product_id && $is_paid ) {

			$course = get_post( $course_id );

			$product = array(
				'post_title' => $course->post_title,
				'post_content' => $course->post_content,
				'post_excerpt' => $course->post_excerpt,
				'post_type' => 'product',
				'ping_status' => 'closed',
				'comment_status' => 'closed',
				'post_status' => 'publish',
			);

			$product_id = wp_insert_post( $product );

			// Avoid the looping
			self::$updated = true;
		}

		// If its not paid and a product doesn't exist, do nothing.
		if ( false === $product_id ) {
			return $settings;
		}

		self::update_product_meta( $product_id, $settings, $course_id );

		// Make sure we associate the product ID with the course
		$settings['mp_product_id'] = $product_id;

		return $settings;
	}

	public static function update_product_meta( $product_id, $settings, $course_id ) {
		// Update the meta
		$product_meta = array(
			'mp_sku' => array( $settings['mp_sku'] ),
			'mp_price' => array( $settings['mp_product_price'] ),
			'mp_is_sale' => $settings['mp_sale_price_enabled'],
			'mp_sale_price' => array( $settings['mp_product_sale_price'] ),
			'mp_course_id' => $course_id,
		);

		// Create Auto SKU
		if ( ! empty( $settings['mp_auto_sku'] ) || empty( $settings['mp_sku'] ) ) {
			$sku_prefix = apply_filters( 'coursepress_course_sku_prefix', 'CP-' );
			$product_meta['mp_sku'] = $sku_prefix . str_pad( $course_id, 5, '0', STR_PAD_LEFT );
		}

		foreach ( $product_meta as $key => $value ) {
			update_post_meta( $product_id, $key, $value );
		}

	}

	public static function update_product_from_course( $course_id, $settings ) {

		self::$course_id = $course_id;

		// Avoid possible messy loop
		if ( self::$updated ) {
			self::$updated = false;
			return;
		}

		// If course status is no longer paid, but an MP ID exists, then disable the MP product (don't delete)
		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'mp_product_id', false );
		$product_status = get_post_status( $product_id );
		$product_id = ! empty( $product_id ) && $product_status ? $product_id : false;

		$is_paid = CoursePress_Data_Course::is_paid_course( $course_id );

		// Update and publish
		if ( false !== $product_id && $is_paid ) {
			self::update_product_meta( $product_id, $settings, $course_id );

			if ( ! empty( $product_status ) && 'publish' !== $product_status ) {
				$product = array(
					'ID' => $product_id,
					'post_status' => 'publish',
				);
				self::$updated = true;
				wp_update_post( $product );
			}
		}

		// Update and hide
		if ( false !== $product_id && ! $is_paid ) {
			self::update_product_meta( $product_id, $settings, $course_id );

			if ( ! empty( $product_status ) && 'publish' !== $product_status ) {
				$product = array(
					'ID' => $product_id,
					'post_status' => 'draft',
				);
				self::$updated = true;
				wp_update_post( $product );
			}
		}

	}

	public static function update_course_from_product( $product_id, $post, $before_update ) {

		// If its not a product, exit
		if ( 'product' !== $post->post_type ) {
			return;
		}

		// If update is caused by this class already, then bail
		if ( self::$updated ) {
			self::$updated = false;
			return;
		}

		$course_id = (int) get_post_meta( $product_id, 'mp_course_id', true );

		// No point proceeding if there is no associated course
		if ( empty( $course_id ) ) {
			return;
		}

		$sku = get_post_meta( $product_id, 'mp_sku', true );
		$sku = is_array( $sku ) ? array_shift( $sku ) : $sku;

		$price = get_post_meta( $product_id, 'mp_price', true );
		$price = is_array( $price ) ? array_shift( $price ) : $price;

		$sale_price = get_post_meta( $product_id, 'mp_sale_price', true );
		$sale_price = is_array( $sale_price ) ? array_shift( $sale_price ) : $sale_price;

		$is_sale = get_post_meta( $product_id, 'mp_is_sale', true );

		$is_paid = 'publish' === $post->post_status;

		$settings = CoursePress_Data_Course::get_setting( $course_id );
		CoursePress_Data_Course::set_setting( $settings, 'mp_sku', $sku );
		CoursePress_Data_Course::set_setting( $settings, 'mp_product_price', $price );
		CoursePress_Data_Course::set_setting( $settings, 'mp_product_sale_price', $sale_price );
		CoursePress_Data_Course::set_setting( $settings, 'mp_sale_price_enabled', $is_sale );
		CoursePress_Data_Course::set_setting( $settings, 'payment_paid_course', $is_paid );
		CoursePress_Data_Course::update_setting( $course_id, true, $settings );

		self::$updated = true;

	}

	public static function shortcode_cost( $content, $course_id ) {

		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'mp_product_id', false );

		return do_shortcode( '[mp_product_price product_id="' . $product_id . '" label=""]' );

	}

}


if ( !function_exists( 'cp_use_woo' ) ) {

	function cp_use_woo() {
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$use_woo = get_option( 'use_woo', 0 );
			if ( $use_woo == 0 ) {
				return false;
			} else {
				return true;
			}
		}
	}

}

if ( !function_exists( 'cp_redirect_woo_to_course' ) ) {

	function cp_redirect_woo_to_course() {
		$redirect_woo_to_course = get_option( 'redirect_woo_to_course', 0 );
		if ( $redirect_woo_to_course == 0 ) {
			return false;
		} else {
			return true;
		}
	}

}

if ( !function_exists( 'cp_student_login_address' ) ) {

	function cp_student_login_address() {
		global $coursepress;
		$student_login_address = get_option( 'use_custom_login_form', 1 ) ? trailingslashit( home_url() . '/' . get_option( 'login_slug', 'student-login' ) ) : wp_login_url();

		return $student_login_address;
	}

	/* get_user_option() fix */

}

if ( !function_exists( 'is_mac' ) ) {

	function is_mac() {
		$user_agent = getenv( "HTTP_USER_AGENT" );
		if ( strpos( $user_agent, "Mac" ) !== false ) {
			return true;
		}
	}

}

if ( !function_exists( 'cp_admin_ajax_url' ) ) {

	function cp_admin_ajax_url() {
		$scheme = ( is_ssl() || force_ssl_admin() ? 'https' : 'http' );

		return admin_url( "admin-ajax.php", $scheme );
	}

}

if ( !function_exists( 'cp_get_user_option' ) ) {

	function cp_get_user_option( $option, $user_id = false ) {
		global $wpdb;

		$blog_id = get_current_blog_id();

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( is_multisite() ) {

			if ( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == $blog_id ) {
				return get_user_meta( $user_id, $wpdb->base_prefix . $option, true );
			}

			return get_user_meta( $user_id, $wpdb->prefix . $option, true );
		} else {
			return get_user_option( $option, $user_id );
		}
	}

}

if ( !function_exists( 'cp_unit_uses_new_pagination' ) ) {

	function cp_unit_uses_new_pagination( $unit_id = false ) {
		$unit_pagination_meta = get_post_meta( $unit_id, 'unit_pagination', true );
		$unit_pagination = isset( $unit_pagination_meta ) && !empty( $unit_pagination_meta ) && $unit_pagination_meta !== false ? true : false;

		return $unit_pagination;
	}

}

if ( !function_exists( 'cp_get_id_by_post_name' ) ) {

	function cp_get_id_by_post_name( $post_name, $post_parent = 0, $type = 'unit' ) {
		global $wpdb;
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = '%s' AND post_type='%s' AND post_parent=%d", $post_name, $type, $post_parent ) );

		return $id;
	}

}

if ( !function_exists( 'cp_can_see_unit_draft' ) ) {

	function cp_can_see_unit_draft() {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_course_unit_cap' ) ) {
			return true;
		} else {
			return false;
		}
	}

}

if ( !function_exists( 'cp_user_can_register' ) ) {

	function cp_user_can_register() {
		if ( is_multisite() ) {
			return users_can_register_signup_filter();
		} else {
			return get_option( 'users_can_register' );
		}
	}

}

if ( !function_exists( 'cp_filter_content' ) ) {

	function cp_filter_content( $content, $none_allowed = false ) {
		if ( $none_allowed ) {
			if ( is_array( $content ) ) {
				foreach ( $content as $content_key => $content_value ) {
					$content[ $content_key ] = wp_filter_nohtml_kses( $content_value );
				}
			} else {
				$content = wp_filter_nohtml_kses( $content );
			}
		} else {
			if ( current_user_can( 'unfiltered_html' ) ) {
				$content = $content;
			} else {
				if ( is_array( $content ) ) {
					foreach ( $content as $content_key => $content_value ) {
						$content[ $content_key ] = wp_kses( $content_value, cp_allowed_post_tags() );
					}
				} else {
					$content = wp_kses( $content, cp_allowed_post_tags() );
				}
			}
		}

		return $content;
	}

}

if ( !function_exists( 'cp_allowed_post_tags' ) ) {

	function cp_allowed_post_tags() {
		$allowed_tags = wp_kses_allowed_html( 'post' );

		return apply_filters( 'coursepress_allowed_post_tags', $allowed_tags );
	}


}

if ( !function_exists( 'cp_is_course_visited' ) ) {

	function cp_is_course_visited( $course_id, $student_id = false ) {
		if ( !$student_id ) {
			$student_id = get_current_user_ID();
		}

		$visited_courses = get_user_option( 'visited_course_units_' . $course_id, $student_id );

		if ( $visited_courses ) {
			$visited_courses = ( explode( ',', $visited_courses ) );
			if ( is_array( $visited_courses ) ) {
				if ( in_array( $course_id, $visited_courses ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				if ( $visited_courses == $course_id ) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}

}

if ( !function_exists( 'cp_get_number_of_unit_pages_visited' ) ) {

	function cp_get_number_of_unit_pages_visited( $unit_id = false, $student_id = false ) {
		if ( !$student_id ) {
			$student_id = get_current_user_ID();
		}
		$visited_pages = get_user_option( 'visited_unit_pages_' . $unit_id . '_page', $student_id );
		if ( $visited_pages ) {
			return count( explode( ',', $visited_pages ) ) - 1;
		} else {
			return 0;
		}
	}

}

if ( !function_exists( 'cp_get_last_visited_unit_page' ) ) {

	function cp_get_last_visited_unit_page( $unit_id, $student_id = false ) {
		if ( !$student_id ) {
			$student_id = get_current_user_ID();
		}
		$last_visited_unit_page = get_user_option( 'last_visited_unit_' . $unit_id . '_page', $student_id );
		if ( $last_visited_unit_page ) {
			return $last_visited_unit_page;
		} else {
			return 1;
		}
	}

}

if ( !function_exists( 'cp_get_order_course_id' ) ) {

	function cp_get_order_course_id( $order_id ) {
		global $mp;
		$cart_info = $mp->get_order( $order_id )->mp_cart_info;
		if( ! is_array( $cart_info ) ) {
			return false;
		}
		$mp_product_id = key( $cart_info );
		$post_parent = get_post_ancestors( $mp_product_id );
		if ( is_array( $post_parent ) ) {
			return $post_parent[ 0 ];
		} else {
			return false;
		}
	}

}

if ( !function_exists( 'cp_mp_order_notification_subject' ) ) {

	function cp_mp_order_notification_subject( $subject, $order ) {
		if ( cp_get_order_course_id( $order->ID ) ) {
			return coursepress_get_mp_order_email_subject();
		} else {
			return $subject;
		}
	}

}

if ( !function_exists( 'cp_mp_order_notification_body' ) ) {

	function cp_mp_order_notification_body( $content, $order ) {
		if ( cp_get_order_course_id( $order->ID ) ) {
			$course_id = cp_get_order_course_id( $order->ID );
			$course = new Course( $course_id );

			$tracking_url = apply_filters( 'wpml_marketpress_tracking_url', mp_orderstatus_link( false, true ) . $order->post_title . '/' );

			$tags = array(
				'CUSTOMER_NAME',
				'BLOG_NAME',
				'LOGIN_ADDRESS',
				'WEBSITE_ADDRESS',
				'COURSE_ADDRESS',
				'COURSE_TITLE',
				'ORDER_ID',
				'ORDER_STATUS_URL',
			);
			$tags_replaces = array(
				$order->mp_shipping_info[ 'name' ],
				get_bloginfo(),
				cp_student_login_address(),
				home_url(),
				$course->get_permalink(),
				$course->details->post_title,
				$order->ID,
				$tracking_url,
			);

			$message = coursepress_get_mp_order_content_email();

			$message = str_replace( $tags, $tags_replaces, $message );

			add_filter( 'wp_mail_from', 'my_mail_from_function', 99 );

			if ( !function_exists( 'my_mail_from_function' ) ) {
				function my_mail_from_function( $email ) {
					return coursepress_get_mp_order_from_email();
				}
			}

			add_filter( 'wp_mail_from_name', 'my_mail_from_name_function', 99 );

			if ( !function_exists( 'my_mail_from_name_function' ) ) {
				function my_mail_from_name_function( $name ) {
					return coursepress_get_mp_order_from_name();
				}
			}

			return $message;
		} else {
			return $content;
		}
	}
}
