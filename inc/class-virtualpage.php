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

	public function __construct() {
		// Check for CP pages
		add_filter( 'template_include', array( $this, 'is_coursepress_page' ) );
	}

	function is_coursepress_page( $template ) {
		global $wp_query;

		$post_type = $wp_query->get( 'post_type' );

		if ( in_array( $post_type, $this->cp_post_types ) ) {
			$this->__set( 'post_type', $post_type );

			if ( $wp_query->is_single )
				if ( 'course' == $post_type )
					$this->setUpCourseOverview();
				elseif ( 'unit' == $post_type )
					$this->setUpUnitsOverview();
				elseif ( 'module' == $post_type )
					$this->setUpModuleView();
		}

		return $template;
	}

	function setUpCourseOverview() {}
	function setUpUnitsOverview() {}
	function setUpModuleView() {}
}