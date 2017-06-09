<?php
/**
 * Class CoursePress_VirtualPage
 *
 * @since 2.0
 * @package CoursePress
 */
final class CoursePress_VirtualPage extends CoursePress_Utility {
	protected $breadcrumb = array();

	protected $templates = array(
		'unit-archive' => 'archive-unit.php',
		'workbook' => 'course-workbook.php',
		'notifications' => 'course-notifications.php',
		'forum' => 'course-forum.php',
		'grades' => 'course-grades.php',
		'instructor' => 'course-instructor.php',
		'single-course' => 'single-course.php',
		'unit' => 'single-unit.php',
		'module' => 'single-unit.php',
		'step' => 'single-unit.php',
	);

	public function __construct( $array ) {
		if ( is_array( $array ) )
			foreach ( $array as $key => $value )
				$this->__set( $key, $value );

		// Setup CP template
		add_filter( 'template_include', array( $this, 'load_coursepress_page' ) );
		// Set CP body class
		add_filter( 'body_class', array( $this, 'set_body_class' ) );
		// Set assets
		add_action( 'wp_enqueue_scripts', array( $this, 'set_assets' ) );

		$template_parts = array(
			'course/instructor-bio',
			'course/content',
			'course/submenu',
		);

		foreach ( $template_parts as $part ) {
			$template = locate_template( 'template-parts/' . $part . '.php', false, false );

			if ( ! $template ) {
				add_action( 'get_template_part_template-parts/' . $part, array( $this, 'get_template_part' ) );
			}
		}
	}

	function get_template_part( $part ) {
		coursepress_render( 'views/' . $part );
	}

	function set_body_class( $class ) {
		array_push( $class, 'coursepress' );

		return $class;
	}

	function set_assets() {
		global $CoursePress;

		$version = $CoursePress->version;
		$plugin_url = $CoursePress->plugin_url;
		$css_deps = array( 'dashicons' );
		$deps = array( 'jquery', 'backbone', 'underscore' );
		$page_now = $this->__get( 'type' );

		wp_enqueue_style( 'coursepress-video-css', $plugin_url . 'assets/external/css/video-js.min.css' );
		wp_enqueue_style( 'coursepress', $plugin_url . 'assets/css/front.min.css', $css_deps, $version );

		// Load scripts
		if ( 'single-course' == $page_now ) {
			wp_enqueue_script( 'circle-progress', $plugin_url . 'assets/external/js/circle-progress.min.js', false, false, true );
		}

		wp_enqueue_script( 'coursepress-video-js', $plugin_url . 'assets/external/js/video.min.js', false, false, true );
		wp_enqueue_script( 'coursepress-video-youtube', $plugin_url . 'assets/external/js/video-youtube.min.js', false, false, true );
		wp_enqueue_script( 'coursepress-video-vimeo', $plugin_url . 'assets/external/js/video-vimeo.js', false, false, true );

		wp_enqueue_script( 'coursepress', $plugin_url . 'assets/js/coursepress-front.min.js', $deps, $version, true );

		$local_vars = array(
			'_wpnonce' => wp_create_nonce( 'coursepress-nonce' ),
		);
		wp_localize_script( 'coursepress', '_coursepress', $local_vars );
	}

	/**
	 * Helper method to check if the current theme have CoursePress template.
	 * @param $type
	 *
	 * @return bool|string
	 */
	private function has_template( $type ) {
		if ( ! empty( $this->templates[ $type ] ) ) {
			$template = locate_template( $this->templates[ $type ], false, false );

			if ( $template )
				return $template;
		}

		return false;
	}

	private function get_post_id_by_slug( $slug ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT ID FROM `{$wpdb->posts}` WHERE `post_name`=%s", $slug );

		$post_id = $wpdb->get_var( $sql );

		return $post_id;
	}

	private function add_breadcrumb( $title, $url ) {
		$breadcrumbs = $this->__get( 'breadcrumb' );
		$attr = array( 'href' => esc_url( $url ) );
		$breadcrumbs[] = $this->create_html( 'a', $attr, $title );

		$this->__set( 'breadcrumb', $breadcrumbs );
	}

	private function get_template( $type ) {
		global $CoursePress, $CoursePress_Instructor, $wp_query, $CoursePress_Course, $CoursePress_Unit,
			$_course_module_id, $_course_module, $_course_step;

		if ( ! empty( $this->__get( 'course' ) ) )
			$CoursePress_Course = coursepress_get_course( get_the_ID() );


		$template = $CoursePress->plugin_path . '/views/template-parts/';
		$template .= $this->templates[ $type ];

		if ( 'instructor' == $type ) {
			$instructor = $wp_query->get( 'instructor' );
			$user = get_user_by( 'login', $instructor );

			if ( $user ) {
				$CoursePress_Instructor = new CoursePress_Instructor( $user );
			}
		} elseif ( in_array( $type, array( 'unit', 'module', 'step' ) ) ) {
			$this->add_breadcrumb( $CoursePress_Course->get_the_title(), $CoursePress_Course->get_permalink() );

			$unit = $this->__get( 'unit' );
			$unit_id = $this->get_post_id_by_slug( $unit );

			if ( $unit_id > 0 ) {
				$CoursePress_Unit = new CoursePress_Unit( $unit_id );
				$this->add_breadcrumb( $CoursePress_Unit->get_the_title(), $CoursePress_Unit->get_unit_url() );
				$_course_module_id = 1; // always start module with 1

				$module = $this->__get( 'module' );

				if ( ! empty( $module ) ) {
					$module = $CoursePress_Unit->get_module_by_slug( $module );

					if ( ! empty( $module ) ) {
						$_course_module_id = $module['id'];
						$_course_module = $module;
						$this->add_breadcrumb( $module['title'], $module['url'] );
					}
				}

				$step = $this->__get( 'step' );

				if ( ! empty( $step ) ) {
					$step_id = $this->get_post_id_by_slug( $step );

					if ( $step_id > 0 ) {
						$_course_step = $stepClass = $CoursePress_Unit->get_step_by_id( $step_id );

						if ( ! is_wp_error( $stepClass ) ) {
							$this->add_breadcrumb( $stepClass->get_the_title(), $stepClass->get_permalink() );
						}
					}
				}
			}
		}

		return $template;
	}

	function load_coursepress_page() {
		$type = $this->__get( 'type' );
		$template = $this->has_template( $type );

		if ( ! $template ) {
			// If the theme did not override the template, load CP template
			$page_template = $this->get_template( $type );
		} else {
			$page_template = $template;
		}

		return $page_template;
	}
}
