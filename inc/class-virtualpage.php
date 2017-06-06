<?php
/**
 * Class CoursePress_VirtualPage
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_VirtualPage extends CoursePress_Utility {
	protected $cp_post_types = array( 'course', 'unit', 'module' );
	protected $post_type;
	protected $template;

	public function __construct( $template_type ) {
		add_filter( 'template_include', array( $this, $template_type ) );

		$template_parts = array(
			'course/title',
			'course/submenu',
		);

		foreach ( $template_parts as $part ) {
			add_action( 'get_template_part_' . $part, array( $this, 'get_course_template_part' ), 10, 2 );
		}
	}

	function get_course_template_part( $part ) {
		coursepress_render( 'views/templates/' . $part );
	}

	function has_template( $template ) {
		$template = locate_template( $template, false, false );

		if ( $template ) {
			return $template;
		}

		return false;
	}

	function setUpContent( $content ) {
		if ( is_main_query() ) {
			$content = call_user_func( array( $this, $this->__get( 'template' ) ) );
		}

		return $content;
	}

	function setCourseArchive() {
		return '[course_list_box show_title="no"]';
	}

	function setCourseOverview() {
		global $CoursePress, $CoursePress_Course, $post;

		$CoursePress_Course = coursepress_get_course( $post->ID );
		$template = $this->has_template( 'single-course.php' );

		if ( ! $template ) {
			error_log( 'count' );
			$template = $CoursePress->plugin_path . '/views/templates/single-course.php';
		}

		return $template;
	}

	function setUpUnitsOverview() {
		global $CoursePress;

		// Check if the current theme have `archive-units.php`
		$template = $this->has_template( 'archive-units.php' );

		if ( ! $template ) {
			$template = $CoursePress->plugin_path . '/views/templates/archive-units.php';
		}

		return $template;
	}

	function setUnitView() {
		return 'UNIT VIEW';
	}

	function setModuleView() {
		return 'MODULE VIEW';
	}

	function setStepView() {
		return 'STEP VIEW';
	}

	function setUpModuleView() {}

}