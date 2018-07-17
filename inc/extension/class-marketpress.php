<?php
/**
 * Class CoursePress_Extension_MarketPress
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension_MarketPress {

	/**
	 * CPT name of the plugin.
	 *
	 * @var string
	 */
	private static $product_ctp = 'product';

	/**
	 * Base paths for plugin pro and free versions.
	 *
	 * @var array
	 */
	private $base_path = array(
		'pro' => 'marketpress/marketpress.php',
		'free' => 'wordpress-ecommerce/marketpress.php',
	);

	/**
	 * Initialize the class.
	 *
	 * Register all action and filter hooks if plugin is active.
	 *
	 * @return void
	 */
	public function __construct() {
		// No need if plugin not enabled.
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Add or update product.
		add_action( 'coursepress_course_updated', array( $this, 'maybe_create_product' ), 10, 3 );
		// Hook to course price.
		add_filter( 'coursepress_course_cost', array( $this, 'course_cost' ), 10, 3 );
		// If for whatever reason the course gets updated in MarketPress,
		// reflect those changes in the course.
		add_action( 'post_updated', array( $this, 'update_course_from_product' ), 10, 3 );
		add_filter( 'coursepress_enroll_button', array( $this, 'enroll_button' ), 10, 4 );
		// Enroll upon pay.
		// Reference to order ID, will need to get the actual product using the MarketPress Order class.
		add_action( 'mp_order_order_paid', array( $this, 'course_paid_3pt0' ) );
		// Update course after a product is updated.
		add_action( 'coursepress_update_from_course', array( $this, 'update_course_data_from_product' ) );
		// This action is documented in WordPress file: /wp-includes/template-loader.php
		add_action( 'template_redirect', array( $this, 'redirect_to_product' ) );
		// Set an action when a course is deleted.
		add_action( 'coursepress_course_deleted', array( $this, 'maybe_delete_product' ), 10, 2 );
		// Trigger an action when a course status is changed.
		add_action( 'coursepress_course_status_changed', array( $this, 'change_product_status' ), 10, 2 );
		// Set a flag that MarketPress is active.
		add_filter( 'coursepress_is_marketpress_active', '__return_true' );
		/**
		 * cost for shortcode
		 */
		add_filter( 'coursepress_shortcode_course_cost', array( $this, 'coursepress_shortcode_course_cost' ), 10, 2 );
	}

	public function coursepress_shortcode_course_cost( $content, $course_id ) {
		$course = coursepress_get_course( $course_id );
		return $this->course_cost( $content, null, $course );
	}

	/**
	 * Check if current plugin is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		// Check if extension is enabled in settings.
		$settings = coursepress_get_setting( 'marketpress' );
		if ( ! empty( $settings ) && ! empty( $settings['enabled'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Installed scope of the plugin.
	 *
	 * Pro or Free version is installed?
	 *
	 * @return int|string
	 */
	public function installed_scope() {
		$scope = '';
		foreach ( $this->base_path as $key => $path ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $path;
			$plugin_mu_dir = WP_CONTENT_DIR . '/mu-plugins/' . $path;
			$location = file_exists( $plugin_dir ) ? trailingslashit( WP_PLUGIN_DIR ) : ( file_exists( $plugin_mu_dir ) ?  WP_CONTENT_DIR . '/mu-plugins/' : '' ) ;
			$scope = empty( $location ) ? $scope : $key;
		}
		return $scope;
	}

	/**
	 * Is plugin installed?
	 *
	 * Check if MarketPress plugin is installed in normal way or via mu-plugins.
	 *
	 * @return bool
	 */
	public function installed() {
		$scope = $this->installed_scope();
		return ! empty( $scope );
	}

	/**
	 * Is plugin active?
	 *
	 * Check if current plugin is active, not just installed.
	 * is_plugin_active() Will not check mu-plugins. So use `Marketpress`
	 * class to check if MarketPress is active.
	 *
	 * @return bool
	 */
	public function activated() {
		$scope = $this->installed_scope();
		return empty( $scope ) ? false : class_exists( 'Marketpress' );
	}

	/**
	 * Get the course price html.
	 *
	 * If a paid course, return the product shortcode.
	 *
	 * @param string $price_html Price html.
	 * @param float $price Course price.
	 * @param object $course CoursePress_Course object.
	 *
	 * @return string
	 */
	public function course_cost( $price_html, $price, $course ) {
		$is_paid_course = $course->is_paid_course();
		// If paid course, get the product shortcode.
		if ( $is_paid_course ) {
			$product_id = $course->get_product_id();
			if ( ! empty( $product_id ) ) {
				return do_shortcode( '[mp_product_price product_id="' . $product_id . '" label=""]' );
			}
		}
		return $price_html;
	}

	/**
	 * Create product from course.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return int|WP_Error
	 */
	public function create_product_from_course( $course_id ) {
		$course = get_post( $course_id );
		$course->ID = 0;
		$course->post_type = self::$product_ctp;
		$product_id = wp_insert_post( $course );
		update_post_meta( $product_id, 'mp_course_id', $course_id );
		return $product_id;
	}

	/**
	 * Update product data from course.
	 *
	 * @param int $product_id Product ID.
	 * @param int $course_id Course ID.
	 *
	 * @return int|WP_Error
	 */
	public function update_product_from_course( $product_id, $course_id ) {
		$product = get_post( $product_id );
		$course = get_post( $course_id );
		if ( $course ) {
			foreach ( $course as $key => $value ) {
				if ( 'post_type' !== $key && 'ID' !== $key ) {
					$product->{$key} = $value;
				}
			}
		}
		$product_id = wp_update_post( $product );
		return $product_id;
	}

	/**
	 * Check if product exists.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return array|null|WP_Post
	 */
	public function product_exist( $product_id ) {
		$product = get_post( $product_id );
		return $product;
	}

	/**
	 * Update product meta data from course data.
	 *
	 * @param int $product_id Product ID.
	 * @param array $settings Product settings.
	 * @param int $course_id Course ID.
	 *
	 * @return array
	 */
	public function update_product_meta_from_course( $product_id, $settings, $course_id ) {
		// Update featured image
		if ( ! empty( $settings['listing_image_thumbnail_id'] ) ) {
			set_post_thumbnail( $product_id, $settings['listing_image_thumbnail_id'] );
		} else {
			delete_post_thumbnail( $product_id );
		}
		// Update the meta
		$is_sale = ! empty( $settings['mp_sale_price_enabled'] ) ? '1' : '';
		$product_meta = array(
			'sku' => $settings['mp_sku'],
			'regular_price' => $settings['mp_product_price'],
			'has_sale' => $settings['mp_sale_price_enabled'],
			'sale_price_amount' => $settings['mp_product_sale_price'],
			'sort_price' => '' !== $settings['mp_product_sale_price'] ? $settings['mp_product_sale_price'] : $settings['mp_product_price'],
			'mp_course_id' => $course_id,
			'mp_price' => $settings['mp_product_price'],
			'mp_sale_price' => $settings['mp_product_sale_price'],
			'mp_sku' => $settings['mp_sku'],
			'mp_is_sale' => $is_sale,
		);
		// Create Auto SKU
		if ( ! empty( $settings['mp_auto_sku'] ) || empty( $settings['mp_sku'] ) ) {
			$sku_prefix = apply_filters( 'coursepress_course_sku_prefix', 'CP-' );
			$product_meta['sku'] = $sku_prefix . str_pad( $course_id, 5, '0', STR_PAD_LEFT );
			$product_meta['mp_sku'] = $product_meta['sku'];
		}
		// Update all metas.
		foreach ( $product_meta as $key => $value ) {
			update_post_meta( $product_id, $key, $value );
		}
		return $product_meta;
	}

	/**
	 * Create product if Ok.
	 *
	 * @param int $course_id Course ID.
	 * @param array $course_meta Course meta.
	 */
	public function maybe_create_product( $course_id, $course_meta ) {
		$course = coursepress_get_course( $course_id, false );
		$product_id = $course->get_product_id();
		if ( $course->is_paid_course() ) {
			// Check product was not deleted
			if ( ! $this->product_exist( $product_id ) ) {
				$product_id = false;
			}
			if ( ! $product_id ) {
				$product_id = $this->create_product_from_course( $course_id );
			} else {
				$product_id = $this->update_product_from_course( $product_id, $course_id );
			}
			// Update product meta.
			$product_meta = $this->update_product_meta_from_course( $product_id, $course_meta, $course_id );
			// Avoid over loop!
			remove_action( 'coursepress_course_updated', array( $this, 'maybe_create_product' ), 10, 3 );
			$course->update_setting( 'mp_product_id', $product_id );
			if ( ! empty( $product_meta['mp_sku'] ) ) {
				$course->update_setting( 'mp_sku', $product_meta['mp_sku'] );
			}
		} else {
			if ( ! empty( $product_id ) ) {
				$this->maybe_change_product( $product_id, $course_id, $course );
			}
		}
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
	public function get_course_id_by_product( $product ) {
		$product_id = is_object( $product ) ? $product->ID : $product;
		if ( empty( $product_id ) ) {
			return 0;
		}
		return intval( get_post_meta( $product_id, 'mp_course_id', true ) );
	}

	/**
	 * Update course from product.
	 *
	 * @param int $product_id
	 * @param object $post WP_Post object.
	 *
	 * @return void
	 */
	public function update_course_from_product( $product_id, $post ) {
		// If its not a product, exit
		if ( self::$product_ctp !== $post->post_type ) {
			return;
		}
		$course_id = $this->get_course_id_by_product( $product_id );
		$course = coursepress_get_course( $course_id );
		if ( is_wp_error( $course ) ) {
			return;
		}
		$settings = $course->get_settings();
		$course_thumbnail = $course->__get( 'listing_image_thumbnail_id' );
		$thumbnail = get_post_thumbnail_id( $product_id );
		if ( $course_thumbnail != $thumbnail ) {
			$settings['listing_image_thumbnail_id'] = $thumbnail;
			$image = wp_get_attachment_image_src( $thumbnail );
			if ( $image ) {
				$settings['listing_image'] = $image[0];
			}
		}
		$sku = get_post_meta( $product_id, 'sku', true );
		$price = get_post_meta( $product_id, 'regular_price', true );
		$sale_price = get_post_meta( $product_id, 'sale_price_amount', true );
		$is_sale = get_post_meta( $product_id, 'has_sale', true );
		$settings = coursepress_set_array_val( $settings, 'mp_sku', $sku );
		$settings = coursepress_set_array_val( $settings, 'mp_product_price', $price );
		$settings = coursepress_set_array_val( $settings, 'mp_product_sale_price', $sale_price );
		$settings = coursepress_set_array_val( $settings, 'mp_sale_price_enabled', $is_sale );
		// Avoid over loop !
		remove_action( 'coursepress_course_updated', array( $this, 'maybe_create_product' ), 10, 3 );
		$course->update_setting( true, $settings );
		$course_object = get_post( $course_id );
		$course_object->post_content = $post->post_content;
		$course_object->post_excerpt = $post->post_excerpt;
		$course_object->post_status = $post->post_status;
		wp_schedule_single_event( time() + 5, 'coursepress_update_from_course', array( 'course' => $course_object ) );
	}

	/**
	 * Update course data from course object.
	 *
	 * @param object $course CoursePress_Course object.
	 */
	public function update_course_data_from_product( $course ) {
		wp_update_post( $course );
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
	public function enroll_button( $content, $course_id, $user_id, $button_option ) {
		$course = coursepress_get_course( $course_id );
		// Do not chane for free courses.
		if ( ! $course->is_paid_course() ) {
			return $content;
		}
		// Change button only when when really need to do it.
		if ( 'enroll' !== $button_option ) {
			return $content;
		}
		// If already purchased, then return too
		//if ( self::is_user_purchased_course( false, $course_id, $user_id ) ) {
		//	return $content;
		//}
		$user = coursepress_get_user( $user_id );
		if ( ! $user->is_enrolled_at( $course_id ) ) {
			$content = $this->_get_add_to_cart_button_by_course_id( $course_id );
		}
		return $content;
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
	private function _get_add_to_cart_button_by_course_id( $course_id ) {
		$course = coursepress_get_course( $course_id );
		$product_id = $course->get_product_id();
		$shortcode = sprintf( '[mp_buy_button product_id="%s"]', $product_id );
		return do_shortcode( $shortcode );
	}

	/**
	 * Enroll upon course purchase.
	 *
	 * @param object $order Order class.
	 */
	public function course_paid_3pt0( $order ) {
		$cart = $order->get_meta( 'mp_cart_info' );
		if ( $cart ) {
			$items = $cart->get_items();
			if ( $items ) {
				foreach ( $items as $product_id => $info ) {
					$course_id = (int) get_post_meta( $product_id, 'mp_course_id', true );
					$user_id = $order->post_author;
					$course = coursepress_get_course( $course_id );
					if ( ! is_wp_error( $course ) ) {
						$user = coursepress_get_user( $user_id );
						if ( ! $user->is_enrolled_at( $course_id ) ) {
							coursepress_add_student( $user_id, $course_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Redirect product from product to course.
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post $post current post
	 * @global WP_Query $wp_query
	 *
	 * @return void
	 */
	public function redirect_to_product() {
		global $post, $wp_query;
		if ( ! $post || self::$product_ctp !== $post->post_type ) {
			return;
		}
		if ( ! $wp_query->is_single || ! $wp_query->is_singular ) {
			return;
		}
		// Only when redirect option is on.
		$use_redirect = coursepress_get_setting( 'marketpress/redirect', false );
		if ( ! $use_redirect ) {
			return;
		}
		// Redirect if course exists.
		$course_id = $this->get_course_id_by_product( $post );
		$course = coursepress_get_course( $course_id );
		if ( ! is_wp_error( $course ) ) {
			$redirect = $course->get_permalink();
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Delete product if Ok to delete.
	 *
	 * @param int $course_id Course ID.
	 * @param object $course CoursePress_Course.
	 */
	public function maybe_delete_product( $course_id, $course ) {
		$product_id = $course->get_product_id();
		$delete_action = coursepress_get_setting( 'marketpress/delete' );
		if ( 'delete' === $delete_action ) {
			wp_delete_post( $product_id );
			// Avoid over loop !
			remove_action( 'coursepress_course_updated', array( $this, 'maybe_create_product' ), 10, 3 );
			$course->update_setting( 'mp_product_id', false );
		} elseif ( 'change_status' === $delete_action ) {
			wp_update_post( array(
				'ID' => $product_id,
				'post_status' => 'draft',
			) );
			update_post_meta( $product_id, '_stock_status', 'outofstock' );
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
	}

	/**
	 * Change product status if course removed.
	 *
	 * @param int $product_id Product ID.
	 * @param int $course_id Course ID.
	 * @param object $course CoursePress_Course
	 */
	public function maybe_change_product( $product_id, $course_id, $course ) {
		$delete_action = coursepress_get_setting( 'marketpress/unpaid' );
		if ( 'delete' === $delete_action ) {
			wp_delete_post( $product_id );
			// Avoid over loop!
			remove_action( 'coursepress_course_updated', array( $this, 'maybe_create_product' ), 10, 3 );
			$course->update_setting( 'mp_product_id', false );
		} elseif ( 'change_status' === $delete_action ) {
			wp_update_post( array(
				'ID' => $product_id,
				'post_status' => 'draft',
			) );
			update_post_meta( $product_id, '_stock_status', 'outofstock' );
		}
	}
}
