<?php
/**
 * Class CoursePress_Extension_WooCommerce
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension_WooCommerce {

	public function __construct() {
		add_action( 'coursepress_course_updated', array( $this, 'course_update' ), 10, 2 );
		add_filter( 'coursepress_default_course_meta', array( $this, 'add_course_default_fields' ) );
		add_action( 'before_delete_post', array( $this, 'update_product_when_deleting_course' ) );
		add_action( 'before_delete_post', array( $this, 'update_course_when_deleting_product' ) );

		add_shortcode( 'mp_product_price', array( $this, 'product_price' ) );
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
		remove_action( 'coursepress_course_updated', array( $this, 'course_update' ), 10, 2 );
		if ( isset( $course_meta['mp_sale_price_enabled'] ) && 'on' == $course_meta['mp_sale_price_enabled'] ) {
			$course_meta['mp_sku'] = sprintf( 'CP-%d', $course_id );
			update_post_meta( $course_id, 'course_settings', $course_meta );
		}
		$course = get_post( $course_id );
		if ( $course_meta['payment_paid_course'] ) {
			$product_id = $this->update_product( $course, $course_meta );
		} else {
			$product_id = $this->get_product_id( $course_id );
			$action = coursepress_get_setting( 'woocommerce/unpaid', 'change_status' );
			switch ( $action ) {
				case 'delete':
					wp_delete_post( $product_id );
				break;
				default:
					$this->hide_product( $course );
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
		$product_id = get_post_meta( $course_id, 'mp_product_id', true );
		$product = get_post( $product_id );
		if ( is_a( $product, 'WP_Post' ) && 'product' == $product->post_type ) {
			return $product_id;
		}
		return 0;
	}

	public function product_price( $atts ) {

		l( $atts );

		return 'foo';

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
		global $CoursePress_Core;
		/**
		 * check post type
		 */
		$post_type = get_post_type( $course_id );
		/**
		 * handle only correct post_type
		 */
		if ( $CoursePress_Core->course_post_type != $post_type ) {
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
		if ( 'delete' == $delete ) {
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
	public static function update_course_when_deleting_product( $product_id ) {
		/**
		 * check post type
		 */
		$post_type = get_post_type( $product_id );
		if ( 'product' != $post_type ) {
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
}
