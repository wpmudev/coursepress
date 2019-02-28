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

	public static $is_active = false;
	private static $updated = false;
	private static $product_ctp = 'product';

	/**
	 * Initialize integration for WooCommerce checkout.
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

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		self::$is_active = true;

		add_filter( 'coursepress_is_woocommerce_active', '__return_true' );
		add_filter( 'coursepress_payment_supported', array( __CLASS__, 'is_payment_supported' ), 10, 2 );

		// Add additional fields to Course Setup Step 6 if paid is checked
		add_filter(
			'coursepress_course_setup_step_6_paid',
			array( __CLASS__, 'product_settings' ),
			10, 2
		);

		/**
		 * This filter allow to set that the user bought the course.
		 *
		 * @since 2.0.0
		 *
		 * @param boolean $is_user_purchased_course user purchase course?
		 * @param WP_Post $course current course to check
		 * @param integer $user_id user to check
		 */
		add_filter(
			'coursepress_is_user_purchased_course',
			array( __CLASS__, 'is_user_purchased_course' ),
			10, 3
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
			'before_delete_post',
			array( __CLASS__, 'update_course_when_deleting_product' )
		);

		add_action(
			'add_meta_boxes',
			array( __CLASS__, 'add_post_parent_metabox' )
		);

		add_action(
			'post_updated',
			array( __CLASS__, 'update_course_from_product' ),
			10, 3
		);

		add_action( 'woocommerce_process_product_meta_simple', array( __CLASS__, 'woo_save_post' ), 999 );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'show_course_message_woocommerce_order_details_after_order_table' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'change_cp_item_name' ), 10, 3 );
		add_filter( 'woocommerce_order_item_name', array( __CLASS__, 'change_cp_order_item_name' ), 10, 2 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'change_order_status' ), 10, 3 );

		add_filter(
			'coursepress_enroll_button',
			array( __CLASS__, 'enroll_button' ),
			10, 4
		);

		add_filter(
			'coursepress_shortcode_course_cost',
			array( __CLASS__, 'get_course_cost_html' ),
			10, 2
		);

		/**
		 * Allow to add step template.
		 *
		 * @since 2.0.0
		 *
		 * @param array $atts Configuration array.
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
			array( __CLASS__, 'change_product_link_to_course_link' ),
			10, 2
		);

		/**
		 * Change product status if course is not available.
		 */
		add_action( 'woocommerce_before_main_content', array( __CLASS__, 'woocommerce_before_main_content' ) );

		/**
		 * WooCommerce change order status
		 */
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'woocommerce_order_status_changed' ), 21, 3 );

		/**
		 * check cart before allow to proceder. Courses can not be buy by guests.
		 */
		add_filter( 'pre_option_woocommerce_enable_guest_checkout', array( __CLASS__, 'check_cart_and_user_login' ) );

		/**
		 * WooCommerce payment complete -> CoursePress enroll student.
		 */
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'payment_complete_enroll_student' ) );
	}

	public static function change_order_status( $order_id, $old_status, $new_status ) {
		/**
		 * if we do not use woo, then we should not use this function
		 */
		if ( ! self::$is_active ) {
			return;
		}
		self::remove_filter_coursepress_enroll_student();
		$order = new WC_order( $order_id );
		$items = $order->get_items();
		$user_id = get_post_meta( $order_id, '_customer_user', true );
		foreach ( $items as $item ) {
			$course_id = self::get_course_id_by_product( $item['product_id'] );
			if ( empty( $course_id ) ) {
				continue;
			}
			$key = sprintf( 'course_%d_woo_payment_status', $course_id );
			update_user_meta( $user_id, $key, $new_status );
			/**
			 * Enroll student to course.
			 */
			if ( 'completed' === $new_status ) {
				CoursePress_Data_Course::enroll_student( $user_id, $course_id );
			}
		}
	}

	public static function is_payment_supported( $payment_supported, $course_id ) {
		return CoursePress_Core::get_setting( 'woocommerce/enabled', false );
	}

	public static function product_settings( $content, $course_id ) {
		// Prefix fields with meta_ to automatically add it to the course meta!
		$mp_content = '
			<div class="wide">
				<label>' .
					esc_html__( 'WooCommerce Product Settings', 'coursepress' ) .
					'</label>
				<p class="description">' . esc_html__( 'Your course will be a new product in WooCommerce. Enter your course\'s payment settings below.', 'coursepress' ) . '</p>

				<label class="normal required">
					' . esc_html__( 'Full Price', 'coursepress' ) . '
				</label>
				<input type="text" name="meta_mp_product_price" value="' . CoursePress_Data_Course::get_setting( $course_id, 'mp_product_price', '' ) . '" />


				<label class="normal">
					' . esc_html__( 'Sale Price', 'coursepress' ) . '
				</label>
				<input type="text" name="meta_mp_product_sale_price" value="' . CoursePress_Data_Course::get_setting( $course_id, 'mp_product_sale_price', '' ) . '" /><br >

				<label class="checkbox narrow">
					<input type="checkbox" name="meta_mp_sale_price_enabled" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'mp_sale_price_enabled', false ) ) . ' />
					<span>' . esc_html__( 'Enable Sale Price', 'coursepress' ) . '</span>
				</label>

				<label class="normal">
					<span> ' . esc_html__( 'Course SKU:', 'coursepress' ) . '</span>
				</label>
				<input type="text" name="meta_mp_sku" placeholder="' . sprintf( __( 'e.g. %s0001', 'coursepress' ), apply_filters( 'coursepress_course_sku_prefix', 'CP-' ) ) . '" value="' . CoursePress_Data_Course::get_setting( $course_id, 'woo/sku', '' ) . '" /><br >
				<label class="checkbox narrow">
					<input type="checkbox" name="meta_mp_auto_sku" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'mp_auto_sku', false ) ) . ' />
					<span>' . esc_html__( 'Automatically generate Stock Keeping Units (SKUs)', 'coursepress' ) . '</span>
				</label>';

		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'woo/product_id', false );
		$product_id = $product_id && get_post_status( $product_id ) ? $product_id : false;

		if ( $product_id ) {
			// Add WooCommerce product ID as indication.
			$mp_content .= ' <p class="description">';
			$mp_content .= sprintf( __( 'WooCommerce Product ID: %d', 'coursepress' ), $product_id );
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
				__( 'Edit WooCommerce Product', 'coursepress' )
			);
			$mp_content .= '</p> ';
		}
		$content .= $mp_content.'</div>';

		return $content;
	}

	/**
	 * Get course id from course id
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id course to check
	 *
	 */
	public static function get_product_id( $course_id = false ) {
		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'woo/product_id', false );
		/**
		 * Check if the corresponding product exists, if not, set product ID
		 * to false. This happens if the product "accidentally" got deleted.
		 */
		return  $product_id && get_post_status( $product_id ) ? $product_id : false;
	}

	public static function update_product( $course_id, $settings ) {

		$automatic_sku_number = 'CP-' . $course_id;

		if ( ! self::$is_active ) {
			do_action( 'coursepress_mp_update_product', $course_id );
			return true;
		}

		$product_id = self::get_product_id( $course_id );

		$course = get_post( $course_id );

		$post = array(
			'post_status'  => $course->post_status,
			'post_title'   => CoursePress_Helper_Utility::filter_content( $course->post_title, true ),
			'post_type'    => self::$product_ctp,
			'post_content' => CoursePress_Helper_Utility::filter_content( $course->post_content, true ),
		);

		// Add or Update a product if its a paid course
		if ( isset( $settings['payment_paid_course'] ) && 'on' == $settings['payment_paid_course'] ) {

			if ( $product_id ) {
				$post['ID'] = $product_id; //If ID is set, wp_insert_post will do the UPDATE instead of insert
			}

			$post_id = wp_insert_post( $post );
			update_post_meta( $post_id, '_stock_status', 'instock' );

			/**
			 * Set or update thumbnail.
			 */
			self::update_product_thumbnail( $post_id );

			$automatic_sku = isset( $settings['mp_auto_sku'] )? $settings['mp_auto_sku']:'';

			if ( 'on' == $automatic_sku ) {
				$sku[0] = $automatic_sku_number;
			} else {
				$sku[0] = CoursePress_Helper_Utility::filter_content( ( ! empty( $settings['mp_sku'] ) ? $settings['mp_sku'] : '' ), true );
			}

			l( $sku );

			if ( self::$is_active ) {
				CoursePress_Data_Course::update_setting( $course_id, 'woo/product_id', $post_id );
				CoursePress_Data_Course::update_setting( $course_id, 'woo/sku', $sku[0] );

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

				// Resave product meta
				CoursePress_Data_Course::update_setting( $course_id, 'mp_product_price', $price );
				CoursePress_Data_Course::update_setting( $course_id, 'mp_product_sale_price', $sale_price );
				/**
				 * Action for WooCommerce product after CoursePress update.
				 *
				 * @since 2.0.5
				 *
				 * @param integer $post_id WooCommerce product ID.
				 * @param integer $course_id CoursePress course ID.
				 */
				do_action( 'coursepress_woocommerce_product_updated', $post_id, $course_id );
			}
			// Remove product if its not a paid course (clean up WooCommerce products)
		} elseif ( isset( $settings['payment_paid_course'] ) && empty( $settings['payment_paid_course'] ) ) {
			if ( $product_id && 0 != $product_id ) {
				if ( self::$is_active ) {
					$unpaid = CoursePress_Core::get_setting( 'woocommerce/unpaid', 'change_status' );
					if ( 'delete' == $unpaid ) {
						CoursePress_Data_Course::delete_setting( $course_id, 'woo' );
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
			}
		}

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
		 * if we do not use WooCommerce, then we should not use this function
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
		$course_id = self::get_course_id_by_product( $product_id );
		if ( empty( $course_id ) ) {
			return;
		}
		CoursePress_Data_Course::delete_setting( $course_id, 'woo' );
		CoursePress_Data_Course::update_setting( $course_id, 'payment_paid_course', 'off' );
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
		 * if we do not use WooCommerce, then we should not use this function
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
		$delete = CoursePress_Core::get_setting( 'woocommerce/delete', 'change_status' );
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

	public static function add_post_parent_metabox() {
		add_meta_box( 'cp_woo_post_parent', __( 'Parent Course', 'coursepress' ), array( __CLASS__, 'cp_woo_post_parent_box_content' ), self::$product_ctp, 'side', 'default' );
	}

	public static function cp_woo_post_parent_box_content() {
		global $post;
		if ( isset( $post->ID ) ) {
?>
            <input type="text" name="parent_course" value="<?php echo esc_attr( self::get_course_id_by_product( $post->ID ) ); ?>" />
<?php
		}
	}

	public static function woo_save_post() {
		global $post;
		if ( $post->post_type != self::$product_ctp ) {
			return;
		}
		if ( isset( $_POST['parent_course'] ) && ! empty( $_POST['parent_course'] ) ) {
			update_post_meta( $post->ID, 'cp_course_id', (int) $_POST['parent_course'] );
		}
		/**
		 * Set or update thumbnail.
		 */
		self::update_product_thumbnail( $post->ID );
	}

	public static function update_course_from_product( $product_id, $post, $before_update ) {
		if ( ! self::$is_active ) {
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

		$course_id = self::get_course_id_by_product( $product_id );

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
		/**
		 * Set or update thumbnail.
		 */
		self::update_product_thumbnail( $product_id );
		self::$updated = true;
	}

	public static function show_course_message_woocommerce_order_details_after_order_table( $order ) {
		$order_id = $order->get_id();
		$order_details		 = new WC_Order( $order_id );
		$order_items		 = $order_details->get_items();
		$purchased_course	 = false;
		foreach ( $order_items as $order_item ) {
			$course_id = self::get_course_id_by_product( $order_item['product_id'] );
			if ( $course_id && get_post_type( $course_id ) == 'course' ) {
				$purchased_course = true;
			}
		}
		if ( ! $purchased_course ) {
			return;
		}
		$order_status = $order->get_status();
		printf( '<h2 class="cp_woo_header">%s</h2>', esc_html__( 'Course', 'coursepress' ) );
		printf( '<p class="cp_woo_thanks">%s</p>', esc_html__( 'Thank you for signing up for the course. We hope you enjoy your experience.', 'coursepress' ) );
		if ( is_user_logged_in() && 'wc-completed' == $order_status ) {
			echo '<p class="cp_woo_dashboard_link">';
			printf(
				__( 'You can find the course in your <a href="%s">Dashboard</a>', 'coursepress' ),
				( method_exists( 'CoursePress_Core', 'get_slug' ) ) ? CoursePress_Core::get_slug( 'student_dashboard', true ) : ''
			);
			echo '</p><hr />';
		}
	}

	public static function change_cp_item_name( $title, $cart_item, $cart_item_key ) {
		$course_id = self::get_course_id_by_product( $cart_item['product_id'] );
		if ( $course_id && get_post_type( $course_id ) == 'course' ) {
			return get_the_title( $course_id );
		}
		return $title;
	}

	public static function change_cp_order_item_name( $name, $item ) {
		$product_id = false;
		if ( is_array( $item ) ) {
			$product_id = isset( $item['item_meta']['_product_id'] ) ? $item['item_meta']['_product_id'] : '';
			$product_id = $product_id[0];
		} else if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
			$product_id = $item->get_product_id();
		}
		if ( is_numeric( $product_id ) ) {
			$course_id = self::get_course_id_by_product( $product_id );
			if ( $course_id && get_post_type( $course_id ) == 'course' ) {
				return get_the_title( $course_id );
			}
		}
		return $name;
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
		$key = sprintf( 'course_%d_woo_payment_status', $course_id );
		return 'wc-completed' == get_user_meta( $user_id, $key, true );
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
	 * Get WOO add to cart button
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
		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'woo/product_id', false );
		if ( empty( $product_id ) ) {
			return '';
		}
		$cart = WC()->cart;
		$cart_data = array();
		if ( method_exists( $cart, 'get_cart' ) ) {
			$cart_data = $cart->get_cart();
		}
		foreach ( $cart_data as $cart_item_key => $values ) {
			$_product = $values['data'];
			$_product_id = $_product->get_id();
			if ( $product_id == $_product_id ) {
				$content = __( 'This course is already in the cart.', 'coursepress' );
				$content .= sprintf(
					' <button data-link="%s" class="single_show_cart_button button">%s</button>',
					esc_url( wc_get_cart_url( $_product_id ) ),
					esc_html__( 'Show cart', 'coursepress' )
				);
				return wpautop( $content );
			}
		}
		$product = new WC_Product( $product_id );
		/**
		 * no or invalid product? any doubts?
		 */
		if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return '';
		}
		$woocommerce_product_id = $product->get_id();
		ob_start();
		do_action( 'woocommerce_before_add_to_cart_form' ); ?>
        <form class="cart" method="post" enctype='multipart/form-data' action="<?php echo esc_url( wc_get_cart_url() ); ?>">
        <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $woocommerce_product_id ); ?>" />
        <button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
        <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
        </form>
<?php
		do_action( 'woocommerce_after_add_to_cart_form' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Get course price, using WooCommerce object
	 *
	 * @since 2.0.0
	 *
	 * @param string $content current cost
	 * @param integer $course_id course to check
	 *
	 * @return string html with product price
	 */
	public static function get_course_cost_html( $content, $course_id ) {
		$product_id = CoursePress_Data_Course::get_setting( $course_id, 'woo/product_id', false );
		$product = new WC_Product( $product_id );
		/**
		 * no or invalid product? any doubts?
		 */
		if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return $content;
		}
		return $product->get_price_html();
	}

	/**
	 * Allow to add step template.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Configuration array.
	 */
	public static function add_to_cart_template( $atts ) {
		/**
		 * if we do not use woo, then we should not use this function
		 */
		if ( ! self::$is_active ) {
			return;
		}
		?>
		<script type="text/template" id="modal-view-woo-template" data-type="modal-step" data-modal-action="paid_enrollment">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Add Course to cart.', 'coursepress' ); ?>
				</h3>
			</div>
			<div class="bbm-modal__section">
				<p>
					<?php esc_html_e( 'You can now add this course to cart.', 'coursepress' ); ?>
				</p>
				<?php echo self::_get_add_to_cart_button_by_course_id( $atts['course_id'] ); ?>
			</div>
			<div class="bbm-modal__bottombar">
			</div>
		</script>
		<?php
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

	public static function use_redirect_woo_to_course() {
		if ( ! self::$is_active ) {
			return false;
		}
		$redirect_woo_to_course = CoursePress_Core::get_setting( 'woocommerce/redirect', false );
		if ( ! $redirect_woo_to_course ) {
			return false;
		}
		return true;
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
		$localize_array['woocommerce_is_used'] = self::$is_active? 'yes' : 'no';
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
		$use_redirect = CoursePress_Core::get_setting( 'woocommerce/redirect', false );
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
		/* If its not a product, exit
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
	 * @return string link.
	 */
	public static function change_product_link_to_course_link( $url, $post ) {
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
		$use_redirect = CoursePress_Core::get_setting( 'woocommerce/redirect', false );
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
		return intval( get_post_meta( $product_id, 'cp_course_id', true ) );
	}

	/**
	 * Set or update thumbnail.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $product_id Product ID.
	 */
	public static function update_product_thumbnail( $product_id ) {
		$thumbnail_id = get_post_thumbnail_id( $product_id );
		if ( ! empty( $thumbnail_id ) ) {
			return;
		}
		/**
		 * Check is set course?
		 */
		$course_id = self::get_course_id_by_product( $product_id );
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
	}

	/**
	 * Change product status to "outofstock" if the course is not
	 * available.
	 *
	 * @since 2.0.0
	 */
	public static function woocommerce_before_main_content() {
		/**
		 * Changed to check only for logged users, becouse some courses
		 * are always not available for not logged users.
		 *
		 * @since 2.0.6
		 */
		if ( ! is_user_logged_in() ) {
			return;
		}
		while ( have_posts() ) {
			the_post();
			$product_id = get_the_ID();
			$product_status = get_post_meta( $product_id, '_stock_status', true );
			if ( 'instock' != $product_status ) {
				continue;
			}
			$course_id = get_post_meta( $product_id, 'cp_course_id', true );
			if ( empty( $course_id ) ) {
				continue;
			}
			$course_status = CoursePress_Data_Course::is_course_available( $course_id );
			if ( $course_status ) {
				continue;
			}
			update_post_meta( get_the_ID(), '_stock_status', 'outofstock' );
		}
		wp_reset_query();
	}

	/**
	 * Change student enrollment status in course, depend on order status.
	 *
	 * @since 2.0.4
	 *
	 * @param integer $order_id WooCommerce order ID.
	 * @param string $old_status Old status of this order.
	 * @param string $new_status New status of this order.
	 */
	public static function woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {
		if ( 'completed' == $new_status ) {
			return;
		}
		$order = new WC_order( $order_id );
		$items = $order->get_items();
		$student_id = get_post_meta( $order_id, '_customer_user', true );
		foreach ( $items as $item ) {
			$course_id = self::get_course_id_by_product( $item['product_id'] );
			if ( empty( $course_id ) ) {
				continue;
			}
			/**
			 * withdraw student from course
		 */
			CoursePress_Data_Course::withdraw_student( $student_id, $course_id );
			/**
		 * change student meta key
		 */
			$key = sprintf( 'course_%d_woo_payment_status', $course_id );
			update_user_meta( $student_id, $key, $new_status );
		}
	}

	/**
	 * Disable WooCommerce "enable_guest_checkout" option.
	 *
	 * Disable WooCommerce "enable_guest_checkout" option when in the cart is
	 * some course, to avoid guest checkout of a course.
	 *
	 * @since 2.0.6
	 *
	 * @param mixed $enable_guest_checkout
	 */
	public static function check_cart_and_user_login( $enable_guest_checkout ) {
		if ( is_user_logged_in() ) {
			return $enable_guest_checkout;
		}
		if ( 'no' == $enable_guest_checkout ) {
			return $enable_guest_checkout;
		}
		$cart = WC()->cart;
		$cart_data = array();
		if ( method_exists( $cart, 'get_cart' ) ) {
			$cart_data = $cart->get_cart();
		}
		foreach ( $cart_data as $cart_item_key => $values ) {
			$_product = $values['data'];
			$course_id = self::get_course_id_by_product( $_product->post->ID );
			if ( CoursePress_Data_Course::is_course( $course_id ) ) {
				return 'no';
			}
		}
		return $enable_guest_checkout;
	}

	/**
	 * Change student enrollment status in course, after payment complete.
	 *
	 * @since 2.0.7
	 *
	 * @param integer $order_id WooCommerce order ID.
	 */
	public static function payment_complete_enroll_student( $order_id ) {
		/**
		 * if we do not use woo, then we should not use this function
		 */
		if ( ! self::$is_active ) {
			return;
		}
		self::remove_filter_coursepress_enroll_student();
		$order = new WC_order( $order_id );
		$items = $order->get_items();
		$user_id = get_post_meta( $order_id, '_customer_user', true );
		foreach ( $items as $item ) {
			$course_id = self::get_course_id_by_product( $item['product_id'] );
			if ( empty( $course_id ) ) {
				continue;
			}
			CoursePress_Data_Course::enroll_student( $user_id, $course_id );
		}
	}

	/**
	 * Remove filter which preventing student to enroll course without paing.
	 *
	 * @since 2.0.7
	 */
	private static function remove_filter_coursepress_enroll_student() {
		/**
		 * remove filter to allow enroll
		 */
		remove_filter(
			'coursepress_enroll_student',
			array( __CLASS__, 'allow_student_to_enroll' ),
			10, 3
		);
	}
}
