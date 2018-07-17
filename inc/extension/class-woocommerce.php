<?php
/**
 * Class CoursePress_Extension_WooCommerce
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension_WooCommerce {

	/**
	 * Base path for the Woocommerce plugin.
	 *
	 * @var array
	 */
	private $base_path = 'woocommerce/woocommerce.php';
	private $active = false;

	/**
	 * Initialize the class.
	 *
	 * Register all action and filter hooks if plugin is active.
	 *
	 * @return void
	 */
	public function __construct() {

		// Do not continue if Woocommerce is not enabled.
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'coursepress_course_updated', array( $this, 'course_update' ), 10, 2 );
		add_filter( 'coursepress_default_course_meta', array( $this, 'add_course_default_fields' ) );
		add_action( 'before_delete_post', array( $this, 'update_product_when_deleting_course' ) );
		add_action( 'before_delete_post', array( $this, 'update_course_when_deleting_product' ) );
		// Trigger an action when a course status is changed.
		add_action( 'coursepress_course_status_changed', array( $this, 'change_product_status' ), 10, 2 );

		/**
		 * This filter allow to set that the user bought the course.
		 *
		 * @since 2.0.0
		 *
		 * @param boolean $is_user_purchased_course user purchase course?
		 * @param WP_Post $course current course to check
		 * @param integer $user_id user to check
		 */
		add_filter( 'coursepress_is_user_purchased_course', array( $this, 'is_user_purchased_course' ), 10, 3 );

		add_shortcode( 'mp_product_price', array( $this, 'product_price' ) );
		add_shortcode( 'mp_buy_button', array( $this, 'add_to_cart_template' ) );

		/**
		 * replace product link to course link
		 */
		/* This filter is documented in WordPress file: /wp-includes/link-template.php */
		add_filter( 'post_type_link', array( $this, 'change_product_link_to_course_link' ), 10, 2 );

		/**
		 * redirect product to course
		 */
		/* This action is documented in WordPress file: /wp-includes/template-loader.php */
		add_action( 'template_redirect', array( $this, 'redirect_to_product' ) );

		/**
		 * WooCommerce filters
		 */
		add_action( 'woocommerce_process_product_meta_simple', array( $this, 'woo_save_post' ), 999 );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'show_course_message_woocommerce_order_details_after_order_table' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'change_cp_item_name' ), 10, 3 );
		add_filter( 'woocommerce_order_item_name', array( $this, 'change_cp_order_item_name' ), 10, 2 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'change_order_status' ), 10, 3 );
		/**
		 * Change product status if course is not available.
		 */
		add_action( 'woocommerce_before_main_content', array( $this, 'woocommerce_before_main_content' ) );
		/**
		 * WooCommerce change order status
		 */
		add_action( 'woocommerce_order_status_changed', array( $this, 'woocommerce_order_status_changed' ), 21, 3 );
		/**
		 * check cart before allow to proceder. Courses can not be buy by guests.
		 */
		add_filter( 'pre_option_woocommerce_enable_guest_checkout', array( $this, 'check_cart_and_user_login' ) );

		/**
		 * WooCommerce payment complete -> CoursePress enroll student.
		 */
		add_action( 'woocommerce_payment_complete', array( $this, 'payment_complete_enroll_student' ) );
		/**
		 * cost for shortcode
		 */
		add_filter( 'coursepress_shortcode_course_cost', array( $this, 'get_course_cost_html' ), 10, 2 );
	}


	/**
	 * Check if current plugin is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		// Check if extension is enabled in settings.
		$settings = coursepress_get_setting( 'woocommerce' );
		if ( ! empty( $settings ) && ! empty( $settings['enabled'] ) ) {
			$this->active = class_exists( 'WC_Product' );
			return $this->active;
		}
		$this->active = false;
		return $this->active;
	}

	/**
	 * Is plugin installed?
	 *
	 * Check if Woocommerce plugin is installed in normal way or via mu-plugins.
	 *
	 * @return bool
	 */
	public function installed() {

		$plugin_dir = WP_PLUGIN_DIR . '/' . $this->base_path;
		$plugin_mu_dir = WP_CONTENT_DIR . '/mu-plugins/' . $this->base_path;
		$location = file_exists( $plugin_dir ) ? trailingslashit( WP_PLUGIN_DIR ) : ( file_exists( $plugin_mu_dir ) ?  WP_CONTENT_DIR . '/mu-plugins/' : '' ) ;

		return empty( $location ) ? false : true;

	}

	/**
	 * Is plugin active?
	 *
	 * Check if current plugin is active, not just installed.
	 * is_plugin_active() Will not check mu-plugins. So use `WooCommerce`
	 * class to check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function activated() {
		return class_exists( 'WooCommerce' );
	}

	public function add_course_default_fields( $course_meta ) {
		/**
		 * paid course defaults
		 */
		$course_meta['mp_auto_sku'] = true;
		$course_meta['mp_product_price'] = '';
		$course_meta['mp_product_sale_price'] = '';
		$course_meta['mp_sale_price_enabled'] = false;
		$course_meta['mp_sku'] = '';
		return $course_meta;
	}

	public function course_update( $course_id, $course_meta ) {
		if ( ! $this->active ) {
			return;
		}
		remove_action( 'coursepress_course_updated', array( $this, 'course_update' ), 10, 2 );
		if ( isset( $course_meta['mp_sale_price_enabled'] ) && 'on' === $course_meta['mp_sale_price_enabled'] ) {
			$course_meta['mp_sku'] = sprintf( 'CP-%d', $course_id );
			update_post_meta( $course_id, 'course_settings', $course_meta );
		}
		$course = coursepress_get_course( $course_id );
		if ( $course_meta['payment_paid_course'] ) {
			$product_id = $this->update_product( $course, $course_meta );
			$course->update_setting( 'mp_product_id', $product_id );
			update_post_meta( $course_id, 'mp_product_id', $product_id );
			/**
			 * set post parent for product
			 */
			wp_update_post( array(
				'ID' => $product_id,
				'post_parent' => $course_id,
			) );
		} else {
			$product_id = $this->get_product_id( $course_id );
			$action = coursepress_get_setting( 'woocommerce/unpaid', 'change_status' );
			switch ( $action ) {
				case 'delete':
					wp_delete_post( $product_id );
					break;
				default:
					$this->hide_product( $product_id );
					break;
			}
		}
	}

	private function hide_product( $product_id ) {
		if ( $product_id ) {
			wp_update_post(
				array(
					'ID' => $product_id,
					'post_status' => 'draft',
					'meta_input' => array(
						'_stock_status' => 'outofstock',
					),
				)
			);
		}
	}

	private function update_product( $course, $course_meta ) {
		$post = array(
			'post_type' => 'product',
			'post_status'  => $course->post_status,
			'post_title'   => $course->post_title,
			'post_parent'  => $course->ID,
			'post_content' => $course->post_content,
			'meta_input' => array(
				'_stock_status' => 'instock',
				'_virtual' => 'yes',
				'_sold_individually' => 'yes',
				'_sku' => $course_meta['mp_sku'],
				'_regular_price' => $course_meta['mp_product_price'],
				'_price' => $course_meta['mp_product_price'],
				'_visibility' => 'visible',
			),
		);
		if ( isset( $course_meta['mp_sale_price_enabled'] ) && $course_meta['mp_sale_price_enabled'] ) {
			$post['meta_input']['_price'] = $course_meta['mp_product_sale_price'];
			$post['meta_input']['_sale_price'] = $course_meta['mp_product_sale_price'];
		}
		$product_id = $this->get_product_id( $course->ID );
		if ( $product_id ) {
			$post['ID'] = $product_id;
		}
		return wp_insert_post( $post );
	}

	public function get_product_id( $course_id ) {
		if ( ! $this->active ) {
			return 0;
		}
		$product_id = get_post_meta( $course_id, 'mp_product_id', true );
		$product = get_post( $product_id );
		if ( is_a( $product, 'WP_Post' ) && 'product' === $product->post_type ) {
			return $product_id;
		}
		return 0;
	}

	public function product_price( $atts, $content ) {
		if ( ! $this->active ) {
			return '';
		}
		global $post;
		return $this->get_course_cost_html( $content, $post->ID );
	}

	/**
	 * Allow to take some action when we delete course
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id course to check
	 *
	 */
	public function update_product_when_deleting_course( $course_id ) {
		if ( ! $this->active ) {
			return;
		}
		global $coursepress_core;
		/**
		 * check post type
		 */
		$post_type = get_post_type( $course_id );
		/**
		 * handle only correct post_type
		 */
		if ( $coursepress_core->course_post_type !== $post_type ) {
			return;
		}
		/**
		 * get product
		 */
		$product_id = $this->get_product_id( $course_id );
		if ( empty( $product_id ) ) {
			return;
		}
		$delete = coursepress_get_setting( 'woocommerce/delete', 'change_status' );
		if ( 'delete' === $delete ) {
			wp_delete_post( $product_id );
		} else {
			$this->hide_product( $course_id );
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
	public function update_course_when_deleting_product( $product_id ) {
		if ( ! $this->active ) {
			return;
		}
		/**
		 * check post type
		 */
		$post_type = get_post_type( $product_id );
		if ( 'product' !== $post_type ) {
			return;
		}
		/**
		 * get course id, return if empty
		 */
		$course_id = wp_get_post_parent_id( $product_id );
		if ( empty( $course_id ) ) {
			return;
		}
		coursepress_course_update_setting( $course_id, 'mp_product_id', 0 );
		coursepress_course_update_setting( $course_id, 'payment_paid_course', 'off' );
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
	public function get_course_cost_html( $content, $course_id ) {
		if ( ! $this->active ) {
			return '';
		}
		$product_id = $this->get_product_id( $course_id );
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
	 *
	 * @return mixed
	 */
	public function add_to_cart_template( $atts ) {
		if ( ! $this->active ) {
			return '';
		}
		$product_id = 0;
		if ( isset( $atts['product_id'] ) ) {
			$product_id = $atts['product_id'];
		}
		if ( empty( $product_id ) ) {
			return;
		}
		$cart_data = WC()->cart->get_cart();
		foreach ( $cart_data as $cart_item_key => $values ) {
			$_product = $values['data'];
			if ( $product_id == $_product->id ) {
				$content = __( 'This course is already in the cart.', 'cp' );
				global $woocommerce;
				$content .= sprintf(
					' <a href="%s" class="single_show_cart_button button">%s</a>',
					esc_url( $woocommerce->cart->get_cart_url() ),
					esc_html__( 'Show cart', 'cp' )
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
		$args = array(
			'product_id' => $product_id,
			'wc_cart_url' => wc_get_cart_url(),
			'wc_button' => $product->single_add_to_cart_text(),
		);
		return coursepress_render( 'views/extensions/woocommerce/front/button', $args, false );
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
	public function is_user_purchased_course( $is_user_purchased_course, $course, $user_id ) {
		if ( ! $this->active ) {
			return false;
		}
		$course_id = is_object( $course )? $course->ID : $course;
		$key = sprintf( 'course_%d_woo_payment_status', $course_id );
		return 'wc-completed' === get_user_meta( $user_id, $key, true );
	}

	public function woo_save_post() {
		if ( ! $this->active ) {
			return;
		}
		global $post;
		if ( 'product' === $post->post_type ) {
			return;
		}
		$parent_course = filter_input( INPUT_POST, 'parent_course', FILTER_VALIDATE_INT );
		if ( $parent_course ) {
			wp_update_post( array(
				'ID' => $post->ID,
				'post_parent' => $parent_course,
			) );
		}
		/**
		 * Set or update thumbnail.
		 */
		$this->update_product_thumbnail( $post->ID );
	}

	/**
	 * Set or update thumbnail.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $product_id Product ID.
	 */
	public function update_product_thumbnail( $product_id ) {
		if ( ! $this->active ) {
			return;
		}
		$thumbnail_id = get_post_thumbnail_id( $product_id );
		if ( ! empty( $thumbnail_id ) ) {
			return;
		}
		/**
		 * Check is set course?
		 */
		$course_id = wp_get_post_parent_id( $product_id );
		if ( empty( $course_id ) ) {
			return;
		}
		/**
		 * Is the course really a course?
		 */
		$is_course = coursepress_is_course( $course_id );
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
		$thumbnail_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", $thumbnail_url ) );
		if ( empty( $thumbnail_id ) ) {
			return;
		}
		/**
		 * Finally ... set product thumbnail.
		 */
		set_post_thumbnail( $product_id, $thumbnail_id );
	}

	public function show_course_message_woocommerce_order_details_after_order_table( $order ) {
		if ( ! $this->active ) {
			return;
		}
		$order_details		 = new WC_Order( $order->id );
		$order_items		 = $order_details->get_items();
		$purchased_course	 = false;
		foreach ( $order_items as $order_item ) {
			$course_id = wp_get_post_parent_id( $order_item['product_id'] );
			if ( $course_id && get_post_type( $course_id ) === 'course' ) {
				$purchased_course = true;
			}
		}
		if ( ! $purchased_course ) {
			return;
		}
		$args = array(
			'show_dashboard_link' => is_user_logged_in() && 'wc-completed' === $order->post_status,
			'dashboard_link' => coursepress_get_setting( 'slugs/student_dashboard', 'courses-dashboard' ),
		);
		coursepress_render( 'views/extensions/woocommerce/front/message-after-order', $args );
	}

	public function change_cp_item_name( $title, $cart_item, $cart_item_key ) {
		if ( ! $this->active ) {
			return $title;
		}
		$course_id = wp_get_post_parent_id( $cart_item['product_id'] );
		if ( $course_id && get_post_type( $course_id ) === 'course' ) {
			return get_the_title( $course_id );
		}
		return $title;
	}

	public function change_cp_order_item_name( $name, $item ) {
		if ( ! $this->active ) {
			return $name;
		}
		$product_id = $item->get_product_id();
		$course_id = wp_get_post_parent_id( $product_id );
		if ( $course_id && get_post_type( $course_id ) === 'course' ) {
			return get_the_title( $course_id );
		}
		return $name;
	}

	public function change_order_status( $order_id, $old_status, $new_status ) {
		if ( ! $this->active ) {
			return;
		}
		$this->remove_filter_coursepress_enroll_student();
		$order = new WC_order( $order_id );
		$items = $order->get_items();
		$user_id = get_post_meta( $order_id, '_customer_user', true );
		foreach ( $items as $item ) {
			$course_id = wp_get_post_parent_id( $item['product_id'] );
			if ( empty( $course_id ) ) {
				continue;
			}
			$key = sprintf( 'course_%d_woo_payment_status', $course_id );
			update_user_meta( $user_id, $key, $new_status );
			/**
			 * Enroll student to course.
			 */
			if ( 'completed' === $new_status ) {
				coursepress_add_student( $user_id, $course_id );
			}
		}
	}

	/**
	 * Update product status.
	 *
	 * @param int $course_id Course ID.
	 * @param string $status Status.
	 */
	public function change_product_status( $course_id, $status ) {
		$course = coursepress_get_course( $course_id );
		$is_paid = $course->is_paid_course();
		$product_id = $course->get_product_id();
		// Do not publish if not paid anymore.
		if ( 'publish' === $status && ! $is_paid ) {
			return;
		}
		wp_update_post(
			array(
				'ID' => $product_id,
				'post_status' => $status,
			)
		);
		$stock = 'publish' === $status ? 'instock' : 'outofstock';
		update_post_meta( $product_id, '_stock_status', $stock );
	}

	/**
	 * Remove filter which preventing student to enroll course without paing.
	 *
	 * @since 2.0.7
	 */
	private function remove_filter_coursepress_enroll_student() {
		/**
		 * remove filter to allow enroll
		 */
		remove_filter(
			'coursepress_enroll_student',
			array( $this, 'allow_student_to_enroll' ),
			10, 3
		);
	}

	/**
	 * Change product status to "outofstock" if the course is not
	 * available.
	 *
	 * @since 2.0.0
	 */
	public function woocommerce_before_main_content() {
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
			if ( 'instock' !== $product_status ) {
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
		wp_reset_postdata();
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
	public function woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {
		if ( 'completed' === $new_status ) {
			return;
		}
		$order = new WC_order( $order_id );
		$items = $order->get_items();
		$student_id = get_post_meta( $order_id, '_customer_user', true );
		foreach ( $items as $item ) {
			$course_id = wp_get_post_parent_id( $item['product_id'] );
			if ( empty( $course_id ) ) {
				continue;
			}
			/**
			 * withdraw student from course
			 */
			coursepress_delete_student( $student_id, $course_id );
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
	 *
	 * @return mixed
	 */
	public function check_cart_and_user_login( $enable_guest_checkout ) {
		if ( is_user_logged_in() ) {
			return $enable_guest_checkout;
		}
		if ( 'no' === $enable_guest_checkout ) {
			return $enable_guest_checkout;
		}
		$cart_data = WC()->cart->get_cart();
		foreach ( $cart_data as $cart_item_key => $values ) {
			$_product = $values['data'];
			if ( coursepress_is_course( $_product->post->post_parent ) ) {
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
	public function payment_complete_enroll_student( $order_id ) {
		$this->remove_filter_coursepress_enroll_student();
		$order = new WC_order( $order_id );
		$items = $order->get_items();
		$user_id = get_post_meta( $order_id, '_customer_user', true );
		foreach ( $items as $item ) {
			$course_id = wp_get_post_parent_id( $item['product_id'] );
			if ( empty( $course_id ) ) {
				continue;
			}
			coursepress_add_student( $user_id, $course_id );
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
	public function change_product_link_to_course_link( $url, $post ) {
		/**
		/* If its not a product, exit
		 */
		if ( 'product' !== $post->post_type ) {
			return $url;
		}
		/**
		 * only when redirect option is on.
		 */
		$use_redirect = coursepress_get_setting( 'woocommerce/redirect', false );
		if ( ! $use_redirect ) {
			return $url;
		}
		$course_id = wp_get_post_parent_id( $post );
		if ( $course_id ) {
			return get_permalink( $course_id );
		}
		return $url;
	}

	/**
	 * rediret product from product to course
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post $post current post
	 * @global WP_Query $wp_query
	 */
	public function redirect_to_product() {
		global $post, $wp_query;
		/**
		/* If its not a product, exit
		 */
		if ( ! $post || 'product' !== $post->post_type ) {
			return;
		}
		/**
		 * only single!
		 */
		if ( ! $wp_query->is_singular ) {
			return;
		}
		/**
		 * only when redirect option is on.
		 */
		$use_redirect = coursepress_get_setting( 'woocommerce/redirect', false );
		if ( ! $use_redirect ) {
			return;
		}
		/**
		 * redirect if course exists
		 */
		$course_id = wp_get_post_parent_id( $post->ID );
		if ( $course_id ) {
			wp_safe_redirect( get_permalink( $course_id ) );
			exit;
		}
	}
}
