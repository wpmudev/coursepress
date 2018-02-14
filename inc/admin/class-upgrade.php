<?php
/**
 * Class CoursePress_Upgrade
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Upgrade {
	protected $cp;
	private $status;

	public function __construct( CoursePress $cp ) {
		$this->status = get_option( 'coursepress_upgrade', 'no upgrade required' );
		$this->cp = $cp;
		if ( 'need to be upgraded' !== $this->status ) {
			return;
		}
		$count = $this->count_courses();

		add_action( 'admin_notices', array( $this, 'upgrade_is_needed_notice' ) );
	}

	private function count_courses() {
		global $CoursePress_Core;
		$post_type = $CoursePress_Core->__get( 'course_post_type' );
		l( $post_type );
		$count = wp_count_posts( 'course' );
		l( $count );
		$count = wp_count_posts( 'post' );
		l( $count );
	}

	public function upgrade_is_needed_notice() {
		$class = 'notice notice-error';
		$message = __( 'Irks! An error has occurred.', 'sample-text-domain' );

		l( 'a' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}
