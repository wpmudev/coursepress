<?php

class CoursePress_Helper_Integration_MarketPress {

	private static $updated = false;
	private static $course_id = 0;

	public static function init() {

		// If MarketPress is not activated, just exit
		if( ! CoursePress_Helper_Extensions_MarketPress::activated() ) {
			return false;
		}

		// Enable Payment Support
		add_filter( 'coursepress_payment_supported', array( __CLASS__, 'enable_payment' ) );

		// Add additional fields to Course Setup Step 6 if paid is checked
		add_filter( 'coursepress_course_setup_step_6_paid', array( __CLASS__, 'product_settings'), 10, 2 );

		// Add MP Product if needed
		add_filter( 'coursepress_course_update_meta', array( __CLASS__, 'maybe_create_product' ), 10, 2 );

		// Respond to Course Update/Create
		add_action( 'coursepress_course_created', array( __CLASS__, 'update_product_from_course' ), 10, 2 );
		add_action( 'coursepress_course_updated', array( __CLASS__, 'update_product_from_course' ), 10, 2 );

		// If for whatever reason the course gets updated in MarketPress, reflect those changes in the course
		add_action( 'post_updated', array( __CLASS__, 'update_course_from_product' ), 10, 3 );

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

		$product_id = CoursePress_Model_Course::get_setting( $course_id, 'mp_product_id', false );
		$product_id = ! empty( $product_id ) && get_post_status( $product_id ) ? $product_id : false;
		if( false !== $product_id ) {
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
		$product_id = CoursePress_Model_Course::get_setting( $course_id, 'mp_product_id', false );

		// Check if the corresponding product exists, if not, set product ID to false. This happens if the product "accidentally" got deleted.
		$product_id = ! empty( $product_id ) && get_post_status( $product_id ) ? $product_id : false;

		// Assume product does not exist and create one
		if( false === $product_id && $is_paid ) {

			$course = get_post( $course_id );

			$product = array(
				'post_title' => $course->post_title,
				'post_content' => $course->post_content,
				'post_excerpt' => $course->post_excerpt,
				'post_type' => 'product',
				'ping_status' => 'closed',
				'comment_status' => 'closed',
				'post_status' => 'publish'
			);

			$product_id = wp_insert_post( $product );

			// Avoid the looping
			self::$updated = true;
		}

		// If its not paid and a product doesn't exist, do nothing.
		if( false === $product_id ) {
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
			'mp_course_id' => $course_id
		);

		// Create Auto SKU
		if( ! empty( $settings['mp_auto_sku'] ) || empty( $settings['mp_sku'] ) ) {
			$sku_prefix = apply_filters( 'coursepress_course_sku_prefix', 'CP-' );
			$product_meta['mp_sku'] = $sku_prefix . str_pad( $course_id, 5, "0", STR_PAD_LEFT );
		}

		foreach ( $product_meta as $key => $value ) {
			update_post_meta( $product_id, $key, $value );
		}

	}

	public static function update_product_from_course( $course_id, $settings ) {

		self::$course_id = $course_id;

		// Avoid possible messy loop
		if( self::$updated ) {
			self::$updated = false;
			return;
		}

		// If course status is no longer paid, but an MP ID exists, then disable the MP product (don't delete)
		$product_id = CoursePress_Model_Course::get_setting( $course_id, 'mp_product_id', false );
		$product_status = get_post_status( $product_id );
		$product_id = ! empty( $product_id ) && $product_status ? $product_id : false;

		$is_paid = CoursePress_Model_Course::is_paid_course( $course_id );

		// Update and publish
		if( false !== $product_id && $is_paid ) {
			self::update_product_meta( $product_id, $settings, $course_id );

			if( ! empty( $product_status ) && 'publish' !== $product_status ) {
				$product = array(
					'ID' => $product_id,
					'post_status' => 'publish'
				);
				self::$updated = true;
				wp_update_post( $product );
			}
		}

		// Update and hide
		if( false !== $product_id && ! $is_paid ) {
			self::update_product_meta( $product_id, $settings, $course_id );

			if( ! empty( $product_status ) && 'publish' !== $product_status ) {
				$product = array(
					'ID' => $product_id,
					'post_status' => 'draft'
				);
				self::$updated = true;
				wp_update_post( $product );
			}
		}

	}

	public static function update_course_from_product( $product_id, $post, $before_update ) {

		// If its not a product, exit
		if( 'product' !== $post->post_type ) {
			return;
		}

		// If update is caused by this class already, then bail
		if( self::$updated ) {
			self::$updated = false;
			return;
		}

		$course_id = (int) get_post_meta( $product_id, 'mp_course_id', true );

		// No point proceeding if there is no associated course
		if( empty( $course_id ) ) {
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

		$settings = CoursePress_Model_Course::get_setting( $course_id );
		CoursePress_Model_Course::set_setting( $settings, 'mp_sku', $sku );
		CoursePress_Model_Course::set_setting( $settings, 'mp_product_price', $price );
		CoursePress_Model_Course::set_setting( $settings, 'mp_product_sale_price', $sale_price );
		CoursePress_Model_Course::set_setting( $settings, 'mp_sale_price_enabled', $is_sale );
		CoursePress_Model_Course::set_setting( $settings, 'payment_paid_course', $is_paid );
		CoursePress_Model_Course::update_setting( $course_id, true, $settings );

		self::$updated = true;

	}



}