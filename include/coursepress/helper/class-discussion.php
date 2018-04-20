<?php
class CoursePress_Helper_Discussion {

	static private $default_key = 'subscribe-reactions';
	static private $field_name = 'coursepress_subscribe';

	/**
	 * Init function
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		add_action( 'comment_form_top', array( __CLASS__, 'add_nonce_to_comment_form' ) );
	}

	/**
	 * Add nonce to comment form if this is a module.
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post $post Current post.
	 */
	public static function add_nonce_to_comment_form() {
		global $post;
		$post_type = CoursePress_Data_Module::get_post_type_name();
		if ( $post_type != $post->post_type ) {
			return;
		}
		$nonce_action = self::_get_nonce_action( 'add' );
		wp_nonce_field( $nonce_action, 'coursepress-add-commment-nonce' );
	}

	/**
	 * Returns status of subscriptions for selected module and current user.
	 *
	 * @since 2.0.0
	 *
	 * @param insteger $module_id Module ID.
	 * @return string Subscription status.
	 */
	public static function get_subscription_status( $module_id ) {
		$user_id = get_current_user_id();
		$meta_key = self::get_user_meta_name( $module_id );
		$user_subscribe = get_user_meta( $user_id, $meta_key, true );
		if ( is_numeric( $user_subscribe ) && $user_subscribe > 0 ) {
			$user_subscribe = 'subscribe-all';
			update_user_meta( $user_id, $meta_key, $user_subscribe );
		}
		if ( empty( $user_subscribe ) ) {
			return self::$default_key;
		}
		return $user_subscribe;
	}

	/**
	 * Returns array of all possible statuses.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of possible statuses.
	 */
	public static function get_subscription_statuses_array() {
		return array(
			'subscribe-reactions' => __( 'Notify me of new posts in this thread', 'coursepress' ),
			'do-not-subscribe' => __( 'Do not notify me of new posts', 'coursepress' ),
			'subscribe-all' => __( 'Notify me of all new posts', 'coursepress' ),
		);
	}

	/**
	 * Sanitize cp_subscribe_to_ key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Key of cp_subscribe_to_.
	 * @return string Sanitized key.
	 */
	public static function sanitize_cp_subscribe_to_key( $key ) {
		$options = self::get_subscription_statuses_array();
		$options_keys = array_keys( $options );
		if ( in_array( $key, $options_keys ) ) {
			return $key;
		}
		return self::$default_key;
	}

	/**
	 * Returns private $default_key value.
	 *
	 * @since 2.0.0
	 *
	 * @return string Default key.
	 */
	public static function get_default_key() {
		return self::$default_key;
	}

	/**
	 * Returns private $field_name value.
	 *
	 * @since 2.0.0
	 *
	 * @return string Default key.
	 */
	public static function get_field_name() {
		return self::$field_name;
	}

	/**
	 * Get value of subscription field
	 *
	 * @since 2.0.0
	 *
	 * @return string/boolean Value of sended subscription stattus.
	 */
	public static function get_value_from_post() {
		if ( isset( $_POST[ self::$field_name ] ) && $_POST[ self::$field_name ] ) {
			return self::sanitize_cp_subscribe_to_key( $_POST[ self::$field_name ] );
		}
		return false;
	}

	/**
	 * Return User Meta key for subscription data.
	 *
	 * @since 2.0.0
	 *
	 * @return string name of user meta.
	 */
	public static function get_user_meta_name( $id ) {
		return sprintf( 'cp_subscribe_to_%d', $id );
	}

	/**
	 * Get comment url.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $comment_id Comment ID.
	 * @param integer $post_id post ID.
	 * @return string Comment url.
	 */
	public static function get_comment_url( $comment_id, $comment_post_id ) {
		$comment_post = get_post( $comment_post_id );
		$url = null;
		if ( CoursePress_Data_Module::get_post_type_name() == $comment_post->post_type ) {
			$unit = get_post( $comment_post->post_parent );
			$course = get_post( $unit->post_parent );
			$base_url = CoursePress_Core::get_slug( 'courses/', true ) . $course->post_name;
			$url = $base_url . '/' . CoursePress_Core::get_slug( 'unit/' ) . $unit->post_name;
			$url .= '#comment-' . $comment_id;
		}
		return $url;
	}

	/**
	 * Verify nonce.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param string $action Action name.
	 * @return boolean Is nonce correct?
	 */
	public static function check_nonce_add( $nonce ) {
		$nonce_action = self::_get_nonce_action( 'add' );
		return wp_verify_nonce( $nonce, $nonce_action );
	}

	/**
	 * Return nonce action name.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param string $action Action name, default 'none'.
	 * @return string Nounce action name.
	 */
	private static function _get_nonce_action( $action = 'none' ) {
		return sprintf(
			'%s_%s_%d',
			__CLASS__,
			$action,
			get_current_user_id()
		);
	}

}
