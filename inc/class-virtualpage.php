<?php
/**
 * Class CoursePress_VirtualPage
 *
 * @since 2.0
 * @package CoursePress
 */
final class CoursePress_VirtualPage extends CoursePress_Utility {
	/**
	 * @var array
	 */
	protected $breadcrumb = array();

	public $type = '';

	/**
	 * @var array
	 */
	protected $templates = array(
		'archive-course' => 'archive-course.php',
		'archive' => 'archive-course.php',
		'completion-status' => 'page-course-completion.php',
		'completion' => 'page-course-completion.php',
		'forum-new' => 'page-course-discussion-new.php',
		'forum-single' => 'page-course-discussion-single.php',
		'forum' => 'page-course-discussion.php',
		'grades' => 'page-course-grades.php',
		'instructor' => 'course-instructor.php',
		'module' => 'single-unit.php',
		'notifications' => 'page-course-notifications.php',
		'single-course' => 'single-course.php',
		'step-comment' => 'content-discussion.php',
		'step' => 'single-unit.php',
		'student-dashboard' => 'page-student-dashboard.php',
		'student-login' => 'page-student-login-form.php',
		'student-settings' => 'page-student-settings.php',
		'unit-archive' => 'archive-unit.php',
		'unit' => 'single-unit.php',
		'workbook' => 'page-course-workbook.php',
	);

	/**
	 * CoursePress_VirtualPage constructor.
	 *
	 * @param $array
	 */
	public function __construct( $array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				$this->__set( $key, $value );
			}
		}
		/**
		 * Set proper type for forum
		 */
		if ( isset( $array['type'] ) && 'forum' === $array['type'] && isset( $array['topic'] ) && '' !== $array['topic'] ) {
			if ( 'new' === $array['topic'] ) {
				$this->__set( 'type', 'forum-new' );
			} else {
				$this->__set( 'type', 'forum-single' );
			}
		}
		// Setup CP template
		add_filter( 'template_include', array( $this, 'load_coursepress_page' ) );
		// Set dummy post object on selected template
		add_filter( 'posts_results', array( $this, 'set_post_object' ), 10, 2 );
		/**
		 * check course, unit, module
		 */
		add_action( 'wp', array( $this, 'check_exists' ) );
		/**
		 * add title
		 */
		add_filter( 'document_title_parts', array( $this, 'add_title_parts' ) );
	}

	/**
	 * Add title parts
	 *
	 * @since 3.0.0
	 */
	public function add_title_parts( $title ) {
		global $wp_query;
		$type = $this->__get( 'type' );
		switch ( $type ) {
			case 'instructor':
				$instructor = $wp_query->get( 'instructor' );
				$user = get_user_by( 'login', $instructor );
				if ( empty( $user ) ) {
					$user = CoursePress_Data_Instructor::instructor_by_hash( $instructor );
				}
				if ( $user ) {
					$coursepress_instructor = new CoursePress_User( $user );
					if ( is_a( $coursepress_instructor, 'CoursePress_User' ) ) {
						$title['title'] = $coursepress_instructor->display_name;
					}
				}
				break;
		}
		return $title;
	}

	/**
	 * Check is course, unit module and step. If not, try to return 404 error.
	 *
	 * @since 3.0.0
	 */
	public function check_exists() {
		$is_404 = false;
		$type = $this->__get( 'type' );
		$course_id = ! empty( $_REQUEST['course_id'] ) ? $_REQUEST['course_id'] : get_the_ID();

		if (
			'single-course' === $type
			&& ! empty( $course_id )
			&& isset( $_REQUEST['action'] )
			&& 'coursepress_enroll' === $_REQUEST['action']
		) {
			$result = coursepress_try_to_add_student( $course_id );
			if ( true === $result ) {
				$course = coursepress_get_course( $course_id );
				$redirect = $course->get_units_url();
				wp_safe_redirect( $redirect );
				exit;
			}
		}

		switch ( $type ) {
			case 'single-course':
			case 'unit-archive':
				$course = $this->__get( 'course' );
				$course_id = $this->get_post_id_by_slug( $course, 'course' );
				if ( empty( $course_id ) ) {
					$is_404 = true;
				}
				break;
			case 'unit':
			case 'module':
				$course = $this->__get( 'course' );
				$unit = $this->__get( 'unit' );
				$course_id = $this->get_post_id_by_slug( $course, 'course' );
				$unit_id = $this->get_post_id_by_slug( $unit, 'unit', $course_id );
				if ( empty( $unit_id ) ) {
					$is_404 = true;
				} elseif ( 'module' === $type ) {
					$module = $this->__get( 'module' );
					$coursepress_unit = new CoursePress_Unit( $unit_id );
					$module = $coursepress_unit->get_module_by_slug( $module, 'module' );
					if ( false === $module ) {
						$is_404 = true;
					}
				}
				break;
		}
		/**
		 * Course, unit, module exists?
		 */
		if ( $is_404 ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
		}

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
			if ( $template ) {
				return $template;
			}
		}
		return false;
	}

	private function get_post_id_by_slug( $slug, $post_type, $post_parent = 0 ) {
		global $wpdb;
		$sql = "SELECT ID FROM `{$wpdb->posts}` WHERE `post_name`=%s AND `post_type`=%s";
		$args = array(
			$slug,
			$post_type,
		);
		if ( (int) $post_parent > 0 ) {
			$sql .= ' AND `post_parent`=%d';
			$args[] = $post_parent;
		}
		$sql = $wpdb->prepare( $sql, $args );
		$post_id = $wpdb->get_var( $sql );
		return $post_id;
	}

	private function add_breadcrumb( $title, $url = null ) {
		$breadcrumbs = $this->__get( 'breadcrumb' );
		if ( empty( $url ) ) {
			$breadcrumbs[] = $this->create_html( 'span', array(), $title );
		} else {
			$attr = array( 'href' => esc_url( $url ) );
			$breadcrumbs[] = $this->create_html( 'a', $attr, $title );
		}
		$this->__set( 'breadcrumb', $breadcrumbs );
	}

	private function get_template( $type ) {
		global $cp_coursepress, $coursepress_instructor, $wp_query, $coursepress_course, $coursepress_unit,
			$_course_module_id, $_course_module, $_course_step, $_coursepress_type_now, $wp_query;
		$course = false;
		if ( $this->__get( 'course' ) || 'single-course' === $type ) {
			$coursepress_course = coursepress_get_course();
			$course = $coursepress_course;
			if ( ! isset( $course->ID ) ) {
				return false;
			}
		}
		$template = $cp_coursepress->plugin_path . 'templates/';
		$template .= $this->templates[ $type ];
		$with_modules = $coursepress_course instanceof CoursePress_Course ? $coursepress_course->is_with_modules() : false;
		switch ( $type ) {
			case 'instructor':
				$instructor = $wp_query->get( 'instructor' );
				$user = get_user_by( 'login', $instructor );
				if ( empty( $user ) ) {
					$user = CoursePress_Data_Instructor::instructor_by_hash( $instructor );
				}
				if ( $user ) {
					$coursepress_instructor = new CoursePress_User( $user );
					if ( is_a( $coursepress_instructor, 'CoursePress_User' ) ) {
						$wp_query->is_404 = false;
					}
				}
				break;
			case 'unit':
			case 'module':
			case 'step':
			case 'step-comment':
				$this->add_breadcrumb( $coursepress_course->get_the_title(), $coursepress_course->get_permalink() );
				$this->add_breadcrumb( __( 'Units', 'cp' ), $coursepress_course->get_units_url() );
				$unit = $this->__get( 'unit' );
				$unit_id = $this->get_post_id_by_slug( $unit, 'unit', $coursepress_course->ID );
				if ( empty( $unit_id ) ) {
					return false;
				}
				$coursepress_unit = new CoursePress_Unit( $unit_id );
				$this->add_breadcrumb( $coursepress_unit->get_the_title(), $coursepress_unit->get_unit_url() );
				$_course_module_id = 1; // always start module with 1
				$_coursepress_type_now = 'unit';
				$module = $this->__get( 'module' );
				if ( ! empty( $module ) ) {
					$module = $coursepress_unit->get_module_by_slug( $module, 'module' );
					if ( ! empty( $module ) ) {
						$_coursepress_type_now = 'module';
						$_course_module_id = $module['id'];
						$_course_module = $module;
						$this->add_breadcrumb( $module['title'], $module['url'] );
					}
				} else {
					$_course_module = $coursepress_unit->get_module_by_id( 1 );
				}
				$step = $this->__get( 'step' );
				if ( ! $with_modules ) {
					$this->__set( 'type', $type );
					$_coursepress_type_now = 'step';
				}
				$step_id = null;
				if ( ! empty( $step ) ) {
					$step_id = $this->get_post_id_by_slug( $step, 'module', $unit_id );
					if ( empty( $step_id ) ) {
						return false;
					} else {
						$_coursepress_type_now = 'step';
						$_course_step = $coursepress_unit->get_step_by_id( $step_id );
						$step_lass = $_course_step;
						if ( ! is_wp_error( $step_lass ) ) {
							$this->add_breadcrumb( $step_lass->get_the_title(), $step_lass->get_permalink() );
						}
					}
				}
				do_action( 'coursepress_get_template', $_coursepress_type_now, $course->ID, $unit_id, $step_id, $_course_module_id );
				break;
			case 'completion':
				// Validate here
				$user = coursepress_get_user();
				$completion_url = $user->get_course_completion_url( $coursepress_course->ID );
				wp_redirect( $completion_url );
				exit;
			case 'unit-archive':
				// Check if user is logged in
				if ( ! is_user_logged_in() ) {
					// Redirect back to course overview
					wp_safe_redirect( $coursepress_course->get_permalink() );
					exit;
				}
				$this->add_breadcrumb( $coursepress_course->get_the_title(), $coursepress_course->get_permalink() );
				$this->add_breadcrumb( __( 'Units', 'cp' ), $coursepress_course->get_units_url() );
				break;
			case 'grades':
			case 'workbook':
			case 'unit-archive':
				if ( ! is_user_logged_in() ) {
					wp_safe_redirect( $coursepress_course->get_permalink() );
					exit;
				}
				$user = coursepress_get_user();
				$is_enrolled = $user->is_enrolled_at( $coursepress_course->ID );
				if ( ! $is_enrolled ) {
					wp_safe_redirect( $coursepress_course->get_permalink() );
					exit;
				}
				$this->add_breadcrumb( $coursepress_course->get_the_title(), $coursepress_course->get_permalink() );
				switch ( $type ) {
					case 'grades':
						$this->add_breadcrumb( __( 'Grades', 'cp' ), $coursepress_course->get_grades_url() );
					break;
					case 'workbook':
						$this->add_breadcrumb( __( 'Workbook', 'cp' ), $coursepress_course->get_workbook_url() );
					break;
				}
				break;
			case 'notifications':
				// Check if user is logged in
				if ( ! is_user_logged_in() ) {
					// Redirect back to course overview
					wp_safe_redirect( $coursepress_course->get_permalink() );
					exit;
				}
				$this->add_breadcrumb( $coursepress_course->get_the_title(), $coursepress_course->get_permalink() );
				$this->add_breadcrumb( __( 'Notifications', 'cp' ), $coursepress_course->get_notifications_url() );
				break;
			case 'forum':
			case 'forum-new':
			case 'forum-single':
				// Check if user is logged in
				if ( ! is_user_logged_in() ) {
					// Redirect back to course overview
					wp_safe_redirect( $coursepress_course->get_permalink() );
					exit;
				}
				$this->add_breadcrumb( $coursepress_course->get_the_title(), $coursepress_course->get_permalink() );
				$this->add_breadcrumb( __( 'Forum', 'cp' ), $coursepress_course->get_discussion_url() );
				if ( 'forum-new' === $type ) {
					$this->add_breadcrumb( __( 'Start a new Discussion', 'cp' ) );
				} else {
					$discussion = coursepress_get_discussion( $coursepress_course );
					if ( ! empty( $discussion ) ) {
						$title = sprintf(
							__( 'Discussion: %s', 'cp' ),
							$discussion->post_title
						);
						$this->add_breadcrumb( esc_html( $title ), $discussion->url );
					}
				}
				break;

			case 'single-course':
				if ( is_user_logged_in() ) {
					$this->add_breadcrumb( $coursepress_course->get_the_title(), $coursepress_course->get_permalink() );
					$this->add_breadcrumb( __( 'Course Details', 'cp' ), $coursepress_course->get_permalink() );
				}
				break;
		}
		return $template;
	}

	public function load_coursepress_page( $template ) {
		$type = $this->__get( 'type' );
		$new_template = $this->has_template( $type );
		if ( ! $new_template ) {
			// If the theme did not override the template, load CP template
			$page_template = $this->get_template( $type );
		} else {
			$page_template = $new_template;
		}
		if ( false === $page_template ) {
			return $template;
		}
		return $page_template;
	}

	private function the_post( $post, $args = array() ) {
		foreach ( $args as $key => $value ) {
			$post->{$key} = $value;
		}
		$post->comment_status = 'closed';
		$post->post_status = 'publish';
		return $post;
	}

	public function set_post_object( $posts, $wp ) {
		if ( ! $wp->is_main_query() ) {
			return $posts;
		}
		if ( empty( $posts ) ) {
			return $posts;
		}
		$type = $this->__get( 'type' );
		$post = array_shift( $posts );
		if ( 'student-dashboard' === $type ) {
			$post = $this->the_post( $post, array(
				'post_title' => __( 'My Courses', 'cp' ),
				'post_type' => 'page',
			) );
		} elseif ( 'student-settings' === $type ) {
			$post = $this->the_post( $post, array(
				'post_title' => __( 'My Profile', 'cp' ),
				'post_type' => 'page',
			) );
		} elseif ( 'student-login' === $type ) {
			$post = $this->the_post( $post, array(
				'post_title' => __( 'Student Login', 'cp' ),
				'post_type' => 'page',
			) );
		}
		array_unshift( $posts, $post );
		return $posts;
	}
}
