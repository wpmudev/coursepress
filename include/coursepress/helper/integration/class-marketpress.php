<?php
/**
 * Helper functions.
 * Integrate other plugins with CoursePress.
 *
 * @package  CoursePress
 */

/**
 * Integrates MarketPress with CoursePress.
 */
class CoursePress_Helper_Integration_MarketPress {

	private static $updated = false;
	public static $is_active = false;
	private static $product_ctp = 'product';
	private static $looping = false;
	private static $post_args = array();

	/**
	 * Initialize the Integration.
	 *
	 * @since  2.0.0
	 */
	public static function init() {

		/**
		 * Always setup _coursepress javasctipt object.
		 */
		add_filter(
			'coursepress_localize_object',
			array( __CLASS__, 'add_settings_to_js_coursepress' )
		);

		// If MarketPress is not activated just exit.
		if ( ! CoursePress_Helper_Extension_MarketPress::activated() ) {
			return false;
		}
		if ( ! CoursePress_Core::get_setting( 'marketpress/enabled' ) ) {
			return false;
		}
		self::$is_active = true;
		add_filter( 'coursepress_is_marketpress_active', '__return_true' );

		// Enable Payment Support
		add_filter(
			'coursepress_payment_supported',
			array( __CLASS__, 'enable_payment' )
		);

		// Add additional fields to Course Setup Step 6 if paid is checked
		add_filter(
			'coursepress_course_setup_step_6_paid',
			array( __CLASS__, 'product_settings' ),
			10, 2
		);

		// Add MP Product if needed
		add_filter(
			'coursepress_course_update_meta',
			array( __CLASS__, 'maybe_create_product' ),
			10, 2
		);

		// Respond to Course Update/Create
		add_action(
			'coursepress_course_created',
			array( __CLASS__, 'update_product_from_course' ),
			10, 2
		);

		add_action(
			'coursepress_course_updated',
			array( __CLASS__, 'update_product_from_course' ),
			10, 2
		);

		// If for whatever reason the course gets updated in MarketPress,
		// reflect those changes in the course.
		add_action(
			'post_updated',
			array( __CLASS__, 'update_course_from_product' ),
			10, 3
		);

		// Below hook will result to duplicate entry on Products
		// add_action(
			// 'wp_insert_post',
			// array( __CLASS__, 'update_product_from_course_on_wp_insert_post' ),
			// 10, 3
		// );

		add_filter(
			'coursepress_shortcode_course_cost',
			array( __CLASS__, 'shortcode_cost' ),
			10, 2
		);

		add_filter(
			'mp_order_email_body',
			array( __CLASS__, 'order_email_body' ),
			10, 2
		);

		add_action(
			'before_delete_post',
			array( __CLASS__, 'update_product_when_deleting_course' )
		);

		add_action(
			'before_delete_post',
			array( __CLASS__, 'update_course_when_deleting_product' )
		);

		add_filter(
			'coursepress_enroll_button',
			array( __CLASS__, 'enroll_button' ),
			10, 4
		);

		/** This filter is documented in include/coursepress/helper/class-javascript.php */
		add_filter(
			'coursepress_localize_object',
			array( __CLASS__, 'add_cart_url' )
		);

		/**
		 * Enroll upon pay
		 *
		 * Reference to order ID, will need to get the actual product using the MarketPress Order class
		 */
		add_action(
			'mp_order_order_paid',
			array( __CLASS__, 'course_paid_3pt0' )
		);

		/**
		 * Override thumbnail placeholder with course list image.
		 * Note: Typically course products won't have thumbnails, but if a product image is set, this filter
		 * will not override the set product image.
		 */
		add_filter(
			'mp_product_image_show_placeholder',
			array( __CLASS__, 'placeholder_to_course_image' ),
			10, 2
		);

		/**
		 * Return course list image as product image for: `mp_product_images` meta
		 */
		add_filter(
			'get_post_metadata',
			array( __CLASS__, 'course_product_images_meta' ),
			10, 4
		);

		add_filter(
			'mp_order/notification_subject',
			array( __CLASS__, 'order_notification_subject' ),
			10, 2
		);

		add_filter(
			'mp_order/notification_body',
			array( __CLASS__, 'order_email_body' ),
			10, 2
		);

		add_filter(
			'mp_meta/product',
			array( __CLASS__, 'verify_meta' ),
			10, 3
		);

		add_filter(
			'mp_product/on_sale',
			array( __CLASS__, 'fix_mp3_on_sale' ),
			10, 2
		);

		// Fix missing MP3.0 meta fields
		add_filter( 'wpmudev_field/get_value/sku', array( __CLASS__, 'fix_mp3_sku' ), 10, 4 );
		add_filter( 'wpmudev_field/get_value/regular_price', array( __CLASS__, 'fix_mp3_regular_price' ), 10, 4 );
		add_filter( 'wpmudev_field/get_value/has_sale', array( __CLASS__, 'fix_mp3_has_sale' ), 10, 4 );
		add_filter( 'wpmudev_field/get_value/sale_price[amount]', array( __CLASS__, 'fix_mp3_sale_price_amount' ), 10, 4 );
		add_filter( 'wpmudev_field/get_value/file_url', array( __CLASS__, 'fix_mp3_file_url' ), 10, 4 );

		/**
		 * Allow to add step template.
		 *
		 * @since 2.0.0
		 *
		 * @param arrat $atts Configuration array.
		 *
		 */
		add_action(
			'coursepress_registration_form_end',
			array( __CLASS__, 'add_to_cart_template' ),
			10, 1
		);

		/** This filter is documented in include/coursepress/data/class-course.php */
		add_filter(
			'coursepress_enroll_student',
			array( __CLASS__, 'allow_student_to_enroll' ),
			10, 3
		);

		/**
		 * redirect product to course
		 */
		/* This action is documented in WordPress file: /wp-includes/template-loader.php */
		add_action(
			'template_redirect',
			array( __CLASS__, 'redirect_to_product' )
		);

		/**
		 * replace product link to course link
		 */
		/* This filter is documented in WordPress file: /wp-includes/link-template.php */
		add_filter(
			'post_type_link',
			array( __CLASS__, 'change_product_linkt_to_course_link' ),
			10, 2
		);

		/**
		 * Add class to body
		 */
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );

		/**
		 * Enroll student for new order if order is paid.
		 */
		add_action( 'mp_order/new_order',  array( __CLASS__, 'enroll_student_when_order_is_paid' ) );
	}

	public static function fix_mp3_on_sale( $on_sale, $product ) {
		$course_id = self::get_course_id_by_product( $product );
		if ( ! empty( $course_id ) ) {
			$old = get_post_meta( $course_id, 'mp_is_sale', true );
			if ( '' != $old ) {
				$on_sale = (int) $old;
			}
		}
		return $on_sale;
	}

	public static function verify_meta( $value, $post_id, $name ) {

		$meta_keys = array(
			'sku'               => 'mp_sku',
			'regular_price'     => 'mp_price',
			'has_sale'          => 'mp_is_sale',
			'sale_price_amount' => 'mp_sale_price',
			'course_id'         => 'mp_course_id',
			'file_url'          => 'mp_file',
		);

		if ( array_key_exists( $name, $meta_keys ) ) {

			$course_id = get_post_meta( $post_id, 'course_id', true );
			$course_id = empty( $course_id ) ? get_post_meta( $post_id, 'mp_course_id', true ) : $course_id;

			if ( empty( $course_id ) ) {
				return $value;
			}

			$item_value = get_post_meta( $post_id, $name, true );
			$item_value = empty( $item_value ) ? get_post_meta( $post_id, $meta_keys[ $name ], true ) : $item_value;
			$item_value = is_array( $item_value ) ? $item_value[0] : $item_value;

			return empty( $item_value ) ? $value : $item_value;

		}

		return $value;
	}

	public static function course_product_images_meta( $value, $post_id, $meta_key, $single ) {

		if ( 'mp_product_images' === $meta_key && ! self::$looping ) {

			// Avoid looping, because we're calling this meta again.
			self::$looping = true;

			$product_images = get_post_meta( $post_id, $meta_key, $single );

			if ( empty( $product_images ) ) {
				$course_id    = ! empty( $post_id ) ? get_post_meta( $post_id, 'course_id', true ) : 0;
				$featured_url = ! empty( $course_id ) ? get_post_meta( $course_id, 'featured_url', true ) : '';
				$admin_edit   = isset( $_GET['action'] ) && 'edit' === $_GET['action'];
				$value        = ! empty( $featured_url ) && ! $admin_edit ? $featured_url : $value;
			}

			// No longer looping
			self::$looping = false;
		}

		return $value;
	}

	public static function placeholder_to_course_image( $show, $post_id ) {
		$course_id = self::get_course_id_by_product( $post_id );
		$course_id = ! empty( $post_id ) ? get_post_meta( $post_id, 'mp_course_id', true ) : 0;
		if ( ! empty( $course_id ) ) {
			add_filter( 'mp_default_product_img', array( __CLASS__, 'replace_image' ) );
			return 1;
		}
		return $show;
	}

	/**
	 * Replace featured image if course has one.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Post $post Current post in loop.
	 *
	 * @param string $img_src Source of image.
	 * @return string Source of image.
	 */
	public static function replace_image( $img_src ) {
		global $post;
		if ( empty( $post ) ) {
			return $img_src;
		}
		if ( self::$product_ctp != $post->post_type ) {
			return $img_src;
		}
		$course_id = self::get_course_id_by_product( $post->ID );
		$image = CoursePress_Data_Course::get_setting( $course_id, 'listing_image' );
		if ( ! empty( $image ) ) {
			return $image;
		}
		return $img_src;
	}

	public static function course_paid_3pt0( $order ) {
		$cart = $order->get_meta( 'mp_cart_info' );
		if ( $cart ) {
			$items = $cart->get_items();
			if ( $items ) {
				foreach ( $items as $product_id => $info ) {
					$course_id = (int) get_post_meta( $product_id, 'mp_course_id', true );
					$user_id   = $order->post_author;
					// Remove enrollment restrictions
					remove_all_filters( 'coursepress_enroll_student' );
					// If not enrolled...
					if ( ! CoursePress_Data_Student::is_enrolled_in_course( $user_id, $course_id ) ) {
						//Then enroll..
						CoursePress_Data_Course::enroll_student( $user_id, $course_id );
					}
				}
			}
		}
	}

	public static function enable_payment( $payment_supported ) {
		$payment_supported = true; // TODO: Should this be a setting??
		return $payment_supported;
	}

	public static function product_settings( $content, $course_id ) {
		// Prefix fields with meta_ to automatically add it to the course meta!
		$mp_content = '
			<div class="wide">
				<label>' .
					esc_html__( 'MarketPress Product Settings', 'CP_TD' ) .
					'</label>
				<p class="description">' . esc_html__( 'Your course will be a new product in MarketPress. Enter your course\'s payment settings below.', 'CP_TD' ) . '</p>

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

		$product_id = self::get_product_id( $course_id );

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

	// Return settings, this is a filter
	public static function maybe_create_product( $settings, $course_id ) {
		// Settings fields will exist because it will be created with this integration in ::product_settings
		$is_paid = isset( $settings['payment_paid_course'] ) ? $settings['payment_paid_course'] : false;
		$is_paid = ! $is_paid || 'off' == $is_paid ? false : true;

		// Check for existance of product id first
		$product_id = self::get_product_id( $course_id );

		// Assume product does not exist and create one.
		if ( ! $product_id && $is_paid ) {
			$product_id = self::_update_product_using_course( $course_id );
			// Avoid the looping
			self::$updated = true;
		}

		// If its not paid and a product doesn't exist, do nothing.
		if ( ! $product_id ) {
			return $settings;
		}

		self::update_product_meta( $product_id, $settings, $course_id );

		// Make sure we associate the product ID with the course
		$settings['mp_product_id'] = $product_id;

		return $settings;
	}

	public static function update_product_meta( $product_id, $settings, $course_id ) {
		// Update the meta
		$is_sale = isset( $settings['mp_sale_price_enabled'] ) && ! empty( $settings['mp_sale_price_enabled'] ) ? '1' : '';
		$product_meta = array(
			'sku' => isset( $settings['mp_sku'] )? $settings['mp_sku']:'',
			'regular_price' => isset( $settings['mp_product_price'] )? $settings['mp_product_price'] : '',
			'has_sale' => isset( $settings['mp_sale_price_enabled'] ) && 'on' == $settings['mp_sale_price_enabled']? 1 : 0,
			'sale_price_amount' => isset( $settings['mp_product_sale_price'] )? $settings['mp_product_sale_price']: '',
			'sort_price' => isset( $settings['mp_product_sale_price'] ) && '' !== $settings['mp_product_sale_price'] ? $settings['mp_product_sale_price'] : ( isset( $settings['mp_product_price'] )? $settings['mp_product_price']:'' ),
			'mp_course_id' => $course_id,
			'mp_price' => isset( $settings['mp_product_price'] )? $settings['mp_product_price']:'',
			'mp_sale_price' => isset( $settings['mp_product_sale_price'] )? $settings['mp_product_sale_price']:'',
			'mp_sku' => isset( $settings['mp_sku'] )? $settings['mp_sku']:'',
			'mp_is_sale' => $is_sale,
		);

		// Create Auto SKU
		if (
			isset( $settings['mp_auto_sku'] )
			&& ! empty( $settings['mp_auto_sku'] )
			|| ! isset( $settings['mp_sku'] )
			|| empty( $settings['mp_sku'] )
		) {
			$sku_prefix = apply_filters( 'coursepress_course_sku_prefix', 'CP-' );
			$product_meta['mp_sku'] = $product_meta['sku'] = $sku_prefix . str_pad( $course_id, 5, '0', STR_PAD_LEFT );
		}

		foreach ( $product_meta as $key => $value ) {
			update_post_meta( $product_id, $key, $value );
		}
	}

	public static function update_product_from_course( $course_id, $settings ) {
		// Avoid possible messy loop
		if ( self::$updated ) {
			self::$updated = false;
			return;
		}
		// If course status is no longer paid, but an MP ID exists, then disable the MP product (don't delete)
		$product_id = self::get_product_id( $course_id );
		$product_status = get_post_status( $product_id );
		$product_id = $product_id && $product_status ? $product_id : false;

		if ( ! $product_id ) {
			self::maybe_create_product( $settings, $course_id );
			return;
		}
		$is_paid = CoursePress_Data_Course::is_paid_course( $course_id );
		// Update and publish
		if ( $product_id && $is_paid ) {
			self::update_product_meta( $product_id, $settings, $course_id );
			self::_update_product_using_course( $course_id, $product_id );
			self::$updated = true;
		}
		// Update and hide.
		if ( $product_id && ! $is_paid ) {
			self::update_product_meta( $product_id, $settings, $course_id );
			if ( $product_status && 'publish' != $product_status ) {
				/**
				 * update product
				 */
				self::_update_product_using_course( $course_id, $product_id );
				/**
				 * update status
				 */
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
		if ( ! self::$is_active ) {
			return;
		}

		// If its not a product, exit
		if ( self::$product_ctp != $post->post_type ) {
			return;
		}

		// If update is caused by this class already, then bail
		if ( self::$updated ) {
			self::$updated = false;
			return;
		}

		$course_id = self::get_course_id_by_product( $product_id );

		// No point proceeding if there is no associated course.
		if ( ! $course_id ) {
			return;
		}

		$sku = get_post_meta( $product_id, 'sku', true );
		$price = get_post_meta( $product_id, 'regular_price', true );
		$sale_price = get_post_meta( $product_id, 'sale_price_amount', true );

		$is_sale = get_post_meta( $product_id, 'has_sale', true )? 'on':'off';

		$is_paid = ('publish' == $post->post_status)? 'on' : 'off';

		$settings = CoursePress_Data_Course::get_setting( $course_id );

		CoursePress_Data_Course::set_setting( $settings, 'mp_sku', $sku );
		CoursePress_Data_Course::set_setting( $settings, 'mp_product_price', $price );
		CoursePress_Data_Course::set_setting( $settings, 'mp_product_sale_price', $sale_price );
		CoursePress_Data_Course::set_setting( $settings, 'mp_sale_price_enabled', $is_sale );
		CoursePress_Data_Course::set_setting( $settings, 'payment_paid_course', $is_paid );
		CoursePress_Data_Course::update_setting( $course_id, true, $settings );

		$post_args = array(
			'ID' => $course_id,
			'post_status' => $post->post_status,
			'post_content' => $post->post_content,
		);

		self::$post_args = $post_args;
		add_action( 'shutdown', array( __CLASS__, '__update_course' ) );

		self::$updated = true;
	}

	public static function __update_course() {
		if ( ! empty( self::$post_args ) ) {
			remove_action( 'post_updated', array( __CLASS__, 'update_course_from_product' ), 10, 3 );
			wp_update_post( self::$post_args );
		}
	}

	public static function shortcode_cost( $content, $course_id ) {
		$product_id = CoursePress_Data_Course::get_setting(
			$course_id,
			'mp_product_id',
			false
		);

		return do_shortcode(
			'[mp_product_price product_id="' . $product_id . '" label=""]'
		);
	}

	/**
	 * Allow to take some action when we delete product
	 *
	 * @since 2.0.0
	 *
	 * @param integer $product_id product to check
	 *
	 */
	public static function update_course_when_deleting_product( $product_id ) {
		/**
		 * if we do not use MarketPress, then we should not use this function
		 */
		if ( ! self::$is_active ) {
			return;
		}
		/**
		 * check post type
		 */
		$post_type = get_post_type( $product_id );
		if ( self::$product_ctp != $post_type ) {
			return;
		}
		/**
		 * get course id, return if empty
		 */
		$course_id = get_post_meta( $product_id, 'cp_course_id', true );
		if ( empty( $course_id ) ) {
			return;
		}
		CoursePress_Data_Course::update_setting( $course_id, 'payment_paid_course', 'off' );
		CoursePress_Data_Course::delete_setting( $course_id, 'mp_product_id' );
	}

	/**
	 * Allow to take some action when we delete course
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id course to check
	 *
	 */
	public static function update_product_when_deleting_course( $course_id ) {
		/**
		 * if we do not use MarketPress, then we should not use this function
		 */
		if ( ! self::$is_active ) {
			return;
		}
		/**
		 * check post type
		 */
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
		$product_id = self::get_product_id( $course_id );
		if ( empty( $product_id ) ) {
			return;
		}
		$delete = coursepress_core::get_setting( 'marketpress/delete', 'change_status' );
		if ( 'delete' == $delete ) {
			wp_delete_post( $product_id );
		} else {
			wp_update_post(
				array(
					'ID' => $product_id,
					'post_status' => 'draft',
				)
			);
			update_post_meta( $product_id, '_stock_status', 'outofstock' );
		}
	}

	/**
	 * Get course id from course id
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id course ID
	 *
	 * @return integer product ID
	 */
	public static function get_product_id( $course_id = false ) {
		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'mp_product_id', false );
		/**
		 * Check if the corresponding product exists, if not, set product ID
		 * to false. This happens if the product "accidentally" got deleted.
		 */
		return  $product_id && get_post_status( $product_id ) ? $product_id : false;
	}

	/**
	 * Create new product when we insert new post
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Currently updated entry
	 * @param WP_Post $course object of course
	 * @param boolean is an existing post updated or not
	 *
	 */
	public static function update_product_from_course_on_wp_insert_post( $course_id, $course, $update ) {
		/**
		 * If this is a revision, don't send the email.
		 */
		if ( wp_is_post_revision( $course_id ) ) {
			return;
		}
		/**
		 * if we do not use MarketPress, then we should not use this function
		 */
		if ( ! self::$is_active ) {
			return;
		}
		/**
		 * check post type
		 */
		$course_post_type = CoursePress_Data_Course::get_post_type_name();
		/**
		 * handle only correct post_type
		 */
		if ( $course_post_type != $course->post_type ) {
			return;
		}
		/**
		 * get product
		 */
		$product_id = self::get_product_id( $course_id );
		if ( empty( $product_id ) ) {
			/**
			 * Create product only for paid course
			 */
			if ( CoursePress_Data_Course::is_paid_course( $course_id ) ) {
				$product_id = self::_update_product_using_course( $course_id );
				self::update_product_meta( $product_id, $settings, $course_id );
			}
		}
	}

	/**
	 * Insert product from course
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $course post object or integer Course ID
	 * @param integer $product_id product ID
	 *
	 * @return integer product ID
	 */
	private static function _update_product_using_course( $course, $product_id = 0 ) {
		if ( ! is_object( $course ) ) {
			$course = get_post( $course );
		}
		if ( empty( $course ) ) {
			return 0;
		}
		$product = array(
			'post_title' => $course->post_title,
			'post_content' => $course->post_content,
			'post_excerpt' => $course->post_excerpt,
			'post_type' => self::$product_ctp,
			'ping_status' => 'closed',
			'comment_status' => 'closed',
			'post_parent' => $course->ID,
			'post_status' => $course->post_status,
		);
		/**
		 * if this is exist product, then update instead insert
		 */
		if ( ! empty( $product_id ) ) {
			$product['ID'] = $product_id;
		}
		$product_id = wp_insert_post( $product );
		self::update_product_thumbnail( $product_id, $course->ID );
		return $product_id;
	}

	/**
	 * Allow to check that the user bought the course.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $is_user_purchased_course user purchase course?
	 * @param WP_Post $course current course to check
	 * @param integer i$user_id user to check
	 *
	 * @return boolean
	 */
	public static function is_user_purchased_course( $is_user_purchased_course, $course, $user_id ) {
		$course_id = is_object( $course )? $course->ID : $course;
		return $is_user_purchased_course;
	}

	/**
	 * Allow to change enroll button
	 *
	 * @since 2.0.0
	 *
	 * @param string $content current button string
	 * @param integer $course_id course to check
	 * @param integer $user_id user to check
	 * @param string $button_option button optiopn
	 *
	 * @return string button string
	 */
	public static function enroll_button( $content, $course_id, $user_id, $button_option ) {
		/**
		 * do not change on lists
		 */
		if ( ! CoursePress_Helper_Utility::$is_singular ) {
			return $content;
		}
		/**
		 * do not chane for free courses
		 */
		if ( ! CoursePress_Data_Course::is_paid_course( $course_id ) ) {
			return $content;
		}
		/**
		 * change button only when when really need to do it
		 */
		if ( 'enroll' != $button_option ) {
			return $content;
		}
		/**
		 * if already purchased, then return too
		 */
		if ( self::is_user_purchased_course( false, $course_id, $user_id ) ) {
			return $content;
		}
		return self::_get_add_to_cart_button_by_course_id( $course_id );
	}

	/**
	 * Get add to cart button
	 *
	 * @since 2.0.0
	 *
	 * @access: private
	 *
	 * @param integer $course_id course to check
	 *
	 * @return string html with "add to cart" button
	 */
	private static function _get_add_to_cart_button_by_course_id( $course_id ) {
		$product_id = self::get_product_id( $course_id );
		$shortcode = sprintf( '[mp_buy_button product_id="%s"]', $product_id );

		return do_shortcode( $shortcode );
	}

	/**
	 * Function add MarketPress Cart URL to javascript configuration.
	 *
	 * @since 2.0.0
	 *
	 * @param array $localize_array CoursePress javascript configuration.
	 */
	public static function add_cart_url( $localize_array ) {
		if ( function_exists( 'mp_store_page_url' ) ) {
			$localize_array['marketpress_cart_url'] = mp_store_page_url( 'cart', false );
		}
		return $localize_array;
	}

	/**
	 * Subject for the order-confirmation email, when user purchased access
	 * to a Course.
	 *
	 * @since  1.0.0
	 * @param  string $subject Default subject.
	 * @param  object $order MarketPress order.
	 * @return string Email subject.
	 */
	public static function order_notification_subject( $subject, $order ) {
		if ( self::_get_order_course_id( $order->ID ) ) {
			$subject = get_option( 'mp_order_email_subject', __( 'Order Confirmation', 'CP_TD' ) );
		}
		/**
		 * Allow to change email subject.
		 *
		 * @since 2.0.0
		 *
		 * @param string $subject Email subject.
		 */
		return apply_filters( 'coursepress_order_notification_subject', $subject );
	}

	/**
	 * Get Course ID from product
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer product object or product id
	 *
	 * @return integer Course ID.
	 */
	public static function get_course_id_by_product( $product ) {
		$product_id = is_object( $product ) ? $product->ID : $product;
		if ( empty( $product_id ) ) {
			return 0;
		}
		return intval( get_post_meta( $product_id, 'mp_course_id', true ) );
	}

	public static function fix_mp3_sku( $value, $post_id, $raw, $field ) {
		return self::verify_meta( $value, $post_id, 'sku' );
	}

	public static function fix_mp3_regular_price( $value, $post_id, $raw, $field ) {
		return self::verify_meta( $value, $post_id, 'regular_price' );
	}

	public static function fix_mp3_has_sale( $value, $post_id, $raw, $field ) {
		return self::verify_meta( $value, $post_id, 'has_sale' );
	}

	public static function fix_mp3_sale_price_amount( $value, $post_id, $raw, $field ) {
		return self::verify_meta( $value, $post_id, 'sale_price_amount' );
	}

	public static function fix_mp3_file_url( $value, $post_id, $raw, $field ) {
		return self::verify_meta( $value, $post_id, 'file_url' );
	}

	/**
	 * Email body for the order-confirmation email, when user purchased access
	 * to a Course.
	 *
	 * @since  1.0.0
	 * @param  string $content Default email body.
	 * @param  object $order MarketPress order.
	 * @return string Email body.
	 */
	public static function order_email_body( $content, $order ) {
		$course_id = self::_get_order_course_id( $order->ID );
		if ( empty( $course_id ) ) {
			return $content;
		}
		$course = get_post( $course_id );
		$course_name = $course->post_title;

		$tracking_url = apply_filters(
			'wpml_marketpress_tracking_url',
			mp_orderstatus_link( false, true ) . $order->post_title . '/'
		);

		$vars = array(
			'CUSTOMER_NAME' => $order->mp_shipping_info['name'],
			'COURSE_ADDRESS' => get_permalink( $course_id ),
			'COURSE_TITLE' => $course_name,
			'ORDER_ID' => $order->ID,
			'ORDER_STATUS_URL' => $tracking_url,
		);
		$vars = CoursePress_Helper_Utility::add_site_vars( $vars );

		$order_message_body = CoursePress_Helper_Utility::replace_vars(
			self::_get_order_content_email(),
			apply_filters(
				'coursepress_order_email_vars',
				$vars
			)
		);

		add_filter(
			'wp_mail_from',
			array( __CLASS__, 'order_email_from' ),
			99
		);
		add_filter(
			'wp_mail_from_name',
			array( __CLASS__, 'order_email_from_name' ),
			99
		);

		/**
		 * Allow to change email message.
		 *
		 * @since 2.0.0
		 *
		 * @param string $order_message_body Order email body.
		 */
		return apply_filters( 'coursepress_order_email_body', $order_message_body );
	}

	/**
	 * Custom sender email-address for order-confirmation emails that belong
	 * to a purchased course.
	 *
	 * @since  1.0.0
	 * @param  string $email Default sender email address.
	 * @return string Sender email address.
	 */
	public static function order_email_from( $email ) {
		$order_from_email = get_option( 'mp_order_from_email', get_option( 'admin_email' ) );
		/**
		 * Allow to change sender email.
		 *
		 * @since 2.0.0
		 *
		 * @param string $order_from_email Email of sender.
		 */
		return apply_filters( 'coursepress_order_email_from', $order_from_email );
	}

	/**
	 * Custom sender name for order-confirmation emails that belong
	 * to a purchased course.
	 *
	 * @since  1.0.0
	 * @param  string $name Default sender name.
	 * @return string Sender name.
	 */
	public static function order_email_from_name( $name ) {
		$order_from_name = get_option( 'mp_order_from_name', get_option( 'blogname' ) );
		/**
		 * Allow to change sender name.
		 *
		 * @since 2.0.0
		 *
		 * @param string $order_from_name Name of mail sender.
		 */
		return apply_filters( 'coursepress_order_email_from_name', $order_from_name );
	}

	/**
	 * Allow to add step template.
	 *
	 * @since 2.0.0
	 *
	 * @param arrat $atts Configuration array.
	 */
	public static function add_to_cart_template( $atts ) {
		/**
		 * if we do not use MarketPress, then we should not use this function
		 */
		if ( ! self::$is_active ) {
			return;
		}
		/**
		 * do not add template for free courses
		 */
		if ( ! CoursePress_Data_Course::is_paid_course( $atts['course_id'] ) ) {
			return;
		}
		?>
		<script type="text/template" id="modal-view-mp-template" data-type="modal-step" data-modal-action="paid_enrollment">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Add Course to cart.', 'CP_TD' ); ?>
				</h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'You can now add this course to cart.', 'CP_TD' ); ?></p>
				<p><?php printf(
					'<a href="%s">%s</a>',
					esc_url( get_permalink( $atts['course_id'] ) ),
					sprintf(
						esc_html__( 'Show course: %s', 'CP_TD' ),
						get_the_title( $atts['course_id'] )
					)
				); ?></p>
				<?php /* echo self::_get_add_to_cart_button_by_course_id( $atts['course_id'] ); */ ?>
			</div>
			<div class="bbm-modal__bottombar">
			</div>
		</script>
		<?php
	}

	/**
	 * Return the course-ID that is linked with the specified MarketPress order.
	 * If the order is not related to CoursePress then return is false.
	 *
	 * @since  1.0.0
	 * @param  int $order_id MarketPress order-ID.
	 * @return int Course-ID or false.
	 */
	private static function _get_order_course_id( $order_id ) {
		$mp = Marketpress::get_instance();

		if ( empty( $mp ) ) { return false; }
		$order = new MP_Order( $order_id );
		$cart_info = $order->mp_cart_info;
		if ( ! is_array( $cart_info ) ) { return false; }

		$mp_product_id = key( $cart_info );
		$post_parent = get_post_ancestors( $mp_product_id );

		if ( is_array( $post_parent ) ) {
			return (int) $post_parent[0];
		} else {
			return false;
		}
	}

	private static function _get_order_content_email() {
		$default_mp_order_content_email = sprintf( __( 'Thank you for your order %1$s,

Your order for course "%2$s" has been received!

Please refer to your Order ID (ORDER_ID) whenever contacting us.

You can track the latest status of your order here: ORDER_STATUS_URL

Yours sincerely,
%5$s Team', 'CP_TD' ), 'CUSTOMER_NAME', '<a href="COURSE_ADDRESS">COURSE_TITLE</a>', '<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'CP_TD' ) . '</a>', '<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>', 'BLOG_NAME' );

		return get_option( 'mp_order_content_email', $default_mp_order_content_email );
	}

	/**
	 * Check course if is paid, stop enabled process.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $enroll_student Allow student to enroll? Default true.
	 * @param integer $student_id Student ID.
	 * @param integer $course_id Course ID.
	 *
	 * @return boolean stop or not enrollment process?
	 */
	public static function allow_student_to_enroll( $enroll_student, $student_id, $course_id ) {
		if ( ! self::$is_active ) {
			return $enroll_student;
		}

		return ! CoursePress_Data_Course::is_paid_course( $course_id );
	}

	/**
	 * Return private variable, is extension active or not.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Is extension active?
	 */
	public static function is_active() {
		return self::$is_active;
	}

	/**
	 * Add extension settings to _coursepress javascript array
	 *
	 * @since 2.0.0
	 *
	 * @param array $localize_array Array of settings.
	 */
	public static function add_settings_to_js_coursepress( $localize_array ) {
		$localize_array['marketpress_is_used'] = self::$is_active? 'yes' : 'no';
		return $localize_array;
	}

	/**
	 * rediret product from product to course
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post $post current post
	 * @global WP_Query $wp_query
	 */
	public static function redirect_to_product() {
		global $post, $wp_query;
		if ( ! self::$is_active ) {
			return;
		}
		/**
		 * only when redirect option is on.
		 */
		$use_redirect = CoursePress_Core::get_setting( 'marketpress/redirect', false );
		if ( ! $use_redirect ) {
			return;
		}
		/**
		 * only single!
		 */
		if ( ! $wp_query->is_singular ) {
			return;
		}
		/**
		 * If its not a product, exit
		 */
		if ( self::$product_ctp != $post->post_type ) {
			return;
		}
		/**
		 * redirect if course exists
		 */
		$course_id = self::get_course_id_by_product( $post );
		if ( $course_id ) {
			wp_safe_redirect( get_permalink( $course_id ) );
			exit;
		}
	}

	/**
	 * Allow to replace link to product.
	 *
	 * This function is used in 'post_link' filter to change product link to
	 * reduce number of redirects.
	 *
	 * @since: 2.0.0
	 *
	 * @param string $url Current post url.
	 * @param WP_Post $post Curent post object.
	 *
	 */
	public static function change_product_linkt_to_course_link( $url, $post ) {
		if ( ! self::$is_active ) {
			return $url;
		}
		/**
		/* If its not a product, exit
		 */
		if ( self::$product_ctp != $post->post_type ) {
			return $url;
		}
		/**
		 * only when redirect option is on.
		 */
		$use_redirect = CoursePress_Core::get_setting( 'marketpress/redirect', false );
		if ( ! $use_redirect ) {
			return $url;
		}
		$course_id = self::get_course_id_by_product( $post );
		if ( $course_id ) {
			return get_permalink( $course_id );
		}
		return $url;
	}

	/**
	 * add "marketpress-course" class to body tag if course is paid.
	 *
	 * @since 2.0.0
	 *
	 * @param array $classes Array of body classes.
	 */
	public static function body_class( $classes ) {
		if ( ! self::$is_active ) {
			return $classes;
		}

		if ( CoursePress_Data_Course::is_course() ) {
			global $post;
			$is_paid = CoursePress_Data_Course::is_paid_course( $post->ID );
			if ( $is_paid ) {
				$classes[] = 'marketpress-course';
			}
		}

		return $classes;
	}

	/**
	 * An exception, when we insert already paid course, look at "stripe"
	 * method.
	 *
	 * @since 2.0.3
	 *
	 * @param MP_Order $order MarketPress order object.
	 */
	public static function enroll_student_when_order_is_paid( $order ) {
		$order_status = $order->__get( 'post_status' );
		if ( 'order_paid' == $order_status ) {
			self::course_paid_3pt0( $order );
		}
	}

	/**
	 * Set or update thumbnail.
	 *
	 * @since 2.0.5
	 *
	 * @param integer $product_id Product ID.
	 */
	public static function update_product_thumbnail( $product_id, $course_id = 0 ) {
		$thumbnail_id = get_post_thumbnail_id( $product_id );
		if ( ! empty( $thumbnail_id ) ) {
			return;
		}
		/**
		 * Check is set course?
		 */
		if ( empty( $course_id ) ) {
			$course_id = wp_get_post_parent_id( $product_id );
		}
		if ( empty( $course_id ) ) {
			return;
		}
		/**
		 * Is the course really a course?
		 */
		$is_course = CoursePress_Data_Course::is_course( $course_id );
		if ( ! $is_course ) {
			return;
		}
		/**
		 *  Only works if the course actually has a thumbnail.
		 */
		$thumbnail_url = get_post_meta( $course_id, 'cp_listing_image', true );
		if ( empty( $thumbnail_url ) ) {
			return;
		}
		/**
		 * Get thumbnail id from thumbnail_url, if it is custom image, do not
		 * set thumbnail for product.
		 */
		global $wpdb;
		$thumbnail_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $thumbnail_url ) );
		if ( empty( $thumbnail_id ) ) {
			return;
		}
		/**
		 * Finally ... set product thumbnail.
		 */
		set_post_thumbnail( $product_id, $thumbnail_id );
		$mp_product_images = explode( ',', get_post_meta( $product_id, 'mp_product_images', true ) );
		array_unshift( $mp_product_images, $thumbnail_id );
		$mp_product_images = implode( ',', array_filter( array_unique( $mp_product_images ) ) );
		update_post_meta( $product_id, 'mp_product_images', $mp_product_images );
	}
}

