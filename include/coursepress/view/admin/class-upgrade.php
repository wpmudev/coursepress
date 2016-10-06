<?php
/**
 * Upgrade class.
 *
 * This class is used during upgrade process.
 *
 * @since 2.0.0
 *
 */
class CoursePress_View_Admin_Upgrade {

	private static $slug = 'coursepress_upgrade';
	private static $title = '';
	private static $menu_title = '';

	public static function init() {
		$coursepress_courses_need_update = get_option( 'coursepress_courses_need_update', false );
		if ( $coursepress_courses_need_update ) {
			self::$title = __( 'Upgrade/CoursePress', 'cp' );
			self::$menu_title = __( 'Upgrade Courses', 'cp' );
			add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
			add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
			add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		}
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;
		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			'cap' => 'coursepress_dashboard_cap',
			'order' => 6.32456,
		);
		return $pages;
	}

	public static function render_page( $content ) {
		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'nopaging' => true,
			'ignore_sticky_posts' => true,
			'fields' => 'ids',
			'meta_query' => array(
				'key' => '_cp_updated_to_version_2',
				'compare' => 'NOT EXISTS'
			)
		);
		$query = new WP_Query( $args );

		echo ' <div class="wrap">';
		printf( '<h1>%s</h1>', self::$menu_title );
		if ( 0 == $query->posts ) {
			printf('<p>%s</p>', __( 'Contgratulation! Upgrade is done.', 'cp' ) );
			echo '</div>';
			delete_option( 'coursepress_courses_need_update' );
			return;
		}

		$count = count( $query->posts );
		printf(
			'<p>%s</p>',
			sprintf( _n( 'You have %d course to update.', 'You have %d courses to update.', $count, 'cp'), $count )
		);

		$labels = array(
			'working' => __( 'Working...', 'cp' ),
			'empty-list' => __( 'There is no courses to update!', 'cp' ),
		);

		echo '<div id="coursepress-updater-holder">';
		echo '<form id="coursepress-update-courses-form"';
		foreach( $labels as $key => $label ) {
			printf( ' data-label-%s="%s"', esc_attr( $key ), esc_attr( $label ) );
		}
		echo '>';
		printf( '<input type="hidden" value="%d" name="user_id" />', esc_attr( get_current_user_id() ) );
		foreach( $query->posts as $course_id ) {
			printf( '<input type="hidden" value="%d" name="course[]" class="course" />', esc_attr( $course_id ) );
		}
		$nonce_name = CoursePress_Helper_Upgrade::get_update_nonce();
		wp_nonce_field( $nonce_name );
		submit_button( __( 'Beging update!', 'cp' ) );
		echo '</form></div>';
		echo '</div>';
	}

	public static function get_slug() {
		return self::$slug;
	}

	public static function admin_enqueue_scripts() {
		$script = CoursePress::$url . 'asset/js/admin-upgrade.js';
		wp_enqueue_script( 'coursepress_admin_upgrade_js', $script, array( 'jquery' ), CoursePress::$version, true );
	}

}

