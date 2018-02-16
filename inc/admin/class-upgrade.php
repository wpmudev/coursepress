<?php
/**
 * Class CoursePress_Upgrade
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Upgrade  extends CoursePress_Admin_Page {

	protected $cp;
	private $status;
	private $count = 0;

	public function __construct( CoursePress $cp ) {
		$this->status = get_option( 'coursepress_upgrade', 'no upgrade required' );
		$this->cp = $cp;
		if ( 'need to be upgraded' !== $this->status ) {
			return;
		}
		add_action( 'init', array( $this, 'count_courses' ), PHP_INT_MAX );
		add_action( 'admin_notices', array( $this, 'upgrade_is_needed_notice' ) );
		add_filter( 'coursepress_admin_menu_screens', array( $this, 'add_admin_submenu' ), 11 );
	}

	public function add_admin_submenu( $screens ) {
		$menu = $this->add_submenu(
			__( 'Upgrade courses', 'cp' ),
			'coursepress_create_course_cap',
			'coursepress_upgrade',
			'get_upgrade_page'
		);
		array_unshift( $screens, $menu );
		return $screens;
	}

	public function process_page() {
	}

	public function get_upgrade_page() {
		$args = array(
			'count' => $this->count,
			'courses' => coursepress_get_accessible_courses( false ),
			'nonce' => wp_create_nonce( __CLASS__ ),
		);
		coursepress_render( 'views/admin/upgrade', $args );
	}

	public function count_courses() {
		global $CoursePress_Core;
		$post_type = $CoursePress_Core->__get( 'course_post_type' );
		$count = wp_count_posts( 'course' );
		$this->count = 0;
		foreach ( $count as $type => $number ) {
			$this->count += $number;
		}
	}

	public function upgrade_is_needed_notice() {
		if ( 1 > $this->count ) {
			return;
		}
		$screen_id = get_current_screen()->id;
		if ( preg_match( '/page_coursepress_upgrade$/', $screen_id ) ) {
			return;
		}

		$class = 'notice notice-error';
		$message = esc_html( sprintf( _n( 'You have %d course to update.', 'You have %d courses to update.', $this->count, 'cp' ), $this->count ) );
		$message .= PHP_EOL.PHP_EOL;
		$message .= sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'page', 'coursepress_upgrade', admin_url( 'admin.php' ) ) ),
			esc_html__( 'Go to CoursePress Upgrade page.', 'cp' )
		);
		printf( '<div class="%s">', esc_attr( $class ) );
		printf( '<h2>%s</h2>', esc_html__( 'CoursePress Upgrade', 'cp' ) );
		echo wpautop( $message );
		echo '</div>';
	}
}
