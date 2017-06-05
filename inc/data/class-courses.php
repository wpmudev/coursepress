<?php
/**
 * Class CoursePress_Data_Courses
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Data_Courses extends CoursePress_Utility {
	protected $cp_post_types = array( 'course', 'unit', 'module' );

	protected $course_post_type = 'course';

	protected $post_type = 'course';

	public function __construct() {
		// Register custom post_type
		add_action( 'init', array( $this, 'register' ) );

		add_filter( 'parse_query', array( $this, 'parse_query' ) );
	}

	function register() {
		// Course
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		register_post_type( $this->post_type, array(
			'public' => true,
			'label' => __( 'CoursePress', 'cp' ),
			'show_ui' => false,
			'show_in_nav_menu' => false,
			'has_archive' => true,
			'can_export' => false, // CP have it's own export mechanism
			'delete_with_user' => false,
			'rewrite' => array(
				'slug' => $course_slug,
			)
		) );

		$category_slug = coursepress_get_setting( 'slugs/category', 'course_category' );

		register_taxonomy( 'course_category',
			array( $this->post_type ),
			array(
				'public' => true,
				'rewrite' => array(
					'slug' => $category_slug,
				)
			)
		);

		// Unit
		register_post_type( 'unit', array(
			'public' => true,
			//'show_ui' => false,
			'hierarchical' => true,
			'can_export' => false,
			'label' => 'Units', // debugging only,
			'rewrite' => array(
				'slug' => $course_slug,
				//'pages' => false,
			),
			'query_var' => false,
			//'has_archive' => true,
			//'query_var' => '/?{course-unit}={single_post_slug}',
			//'publicly_queryable' => false,
		) );

		// Module
		register_post_type( 'module', array(
			'public' => true,
			'show_ui' => false,
			'hierarchical' => true,
			'can_export' => false,
			'label' => 'Modules', // dbugging only
		) );
	}

	function get_course_id_by_name( $coursename ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_name=%s", $coursename );
		$course_id = $wpdb->get_var( $sql );

		return $course_id;
	}

	function reset_wp( $wp, $course_name ) {
		$wp->is_home = false;
		$wp->is_singular = $wp->is_single = true;
		$wp->query_vars = wp_parse_args( array(
			'page' => '',
			'course' => $course_name,
			'post_type' => 'course',
			'name' => $course_name,
		), $wp->query_vars );
	}

	function parse_query( $wp ) {
		global $CoursePress_VirtualPage;

		$coursepress_template = false;

		$post_type = $wp->get( 'post_type' );
		$course_name = $wp->get( 'coursename' );

		if ( $wp->is_main_query() ) {
			//error_log( print_r( $wp, true ) );
		}

		if ( ! empty( $course_name ) ) {
			//$course_id = $this->get_course_id_by_name( $course_name );

			if ( $wp->get( 'unit-archive' ) ) {
				$this->reset_wp( $wp, $course_name );
				$coursepress_template = 'setUpUnitsOverview';
			}
			elseif( ($unit = $wp->get( 'unit' ) ) ) {
				$this->reset_wp( $wp, $course_name );
				$coursepress_template = 'setUnitView';
			}
		}

		if ( in_array( $post_type, $this->__get( 'cp_post_types' ) ) ) {
			if ( $wp->is_archive ) {
				// Reorder courses base on set settings.
				add_filter( 'posts_orderby', array( $this, 'reorder_courses' ) );
				$coursepress_template = 'setCourseArchive';
			} elseif ( $wp->is_single || $wp->is_singular ) {
				$coursepress_template = 'setCourseOverview';
			}
			error_log( 'found');
		}

		if ( $coursepress_template ) {
			$CoursePress_VirtualPage = new CoursePress_VirtualPage( $coursepress_template );
		}

		return $wp;
	}

	function reorder_courses( $orderby ) {
		global $wpdb;

		$course_orderby = coursepress_get_setting( 'course/order_by', 'post_date' );
		$course_order   = coursepress_get_setting( 'course/order_by_direction', 'DESC' );

		if ( 'post_date' == $course_orderby ) {
			$orderby = "{$wpdb->posts}.{$course_orderby} {$course_order}";
		} elseif ( 'start_date' == $course_orderby ) {
			// @todo:
		} elseif ( 'enrollment_start_date' == $course_orderby ) {
			// @todo:
		}

		// Remove CP filter hook
		remove_filter( 'posts_orderby', array( $this, 'reorder_courses' ) );

		return $orderby;
	}

	function get_courses( $args = array() ) {
		$posts_per_page = coursepress_get_option( 'posts_per_page', 20 );
		$ids = ! empty( $args['fields'] ) && 'ids' == $args['fields'];

		$defaults = array(
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'posts_per_page' => $posts_per_page,
			'fields' => 'ids',
		);
		$args = wp_parse_args( $args, $defaults );

		$courses = get_posts( $args );

		if ( ! $ids ) {
			// Transform result into `CoursePress_Course` object
			$courses = array_map( array( $this, 'setCourseObject' ), $courses );
		}

		return $courses;
	}

	function setCourseObject( $course ) {
		return new CoursePress_Course( $course );
	}

	/**
	 * Helper method to add post meta to a course.
	 *
	 * @since 3.0
	 * @param int $course_id
	 * @param string $meta_key
	 * @param mixed $meta_value
	 */
	function add_course_meta( $course_id, $meta_key, $meta_value ) {
		add_post_meta( $course_id, $meta_key, $meta_value );
	}

	/**
	 * Helper method to remove post meta from a course.
	 *
	 * @since 3.0
	 * @param int $course_id
	 * @param string $meta_key
	 * @param mixed $meta_value
	 */
	function delete_course_meta( $course_id, $meta_key, $meta_value ) {
		delete_post_meta( $course_id, $meta_key, $meta_value );
	}
}