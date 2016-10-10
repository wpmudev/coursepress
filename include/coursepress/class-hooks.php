<?php
/**
 * CourePress Hooks
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Hooks {
	static $warning_message = '';

	public static function init() {
		// Listen to course withdrawal request
		add_action( 'init', array( 'CoursePress_Data_Student', 'withdraw_from_course' ) );

		// Listen to module submission
		add_action( 'init', array( 'CoursePress_Module', 'process_submission' ) );

		// Listen to enrollment request
		add_action( 'init', array( 'CoursePress_Template_Student', 'process_enrollment' ) );

		// Listen to comment submission
		add_action( 'init', array( 'CoursePress_Data_Discussion', 'init' ), 100 );

		// Map course capabilities
//		add_action( 'init', array( 'CoursePress_Data_Capabilities', 'course_capabilities' ) );

		// Hook to admin ajax request
		add_action( 'wp_ajax_coursepress_request', array( __CLASS__, 'process_request' ) );
		add_action( 'wp_ajax_nopriv_coursepress_request', array( __CLASS__, 'process_request' ) );

		// Set front scripts
		add_action( 'wp_enqueue_scripts', array( 'CoursePress_Helper_Javascript', 'front_assets' ) );
	}

	/**
	 * Receives ajax request.
	 **/
	public static function process_request() {
		$input = json_decode( file_get_contents( 'php://input' ) );

		if ( ! empty( $input->cpnonce ) && wp_verify_nonce( $input->cpnonce, 'coursepress_nonce' ) ) {
			$method = $input->method;
			$class = $input->className;

			if ( class_exists( $class ) && method_exists( $class, $method ) ) {
				$input = CoursePress_Helper_Utility::object_to_array( $input );

				call_user_func( array( $class, $method ), $input );

				exit;
			}
		}
	}
}
