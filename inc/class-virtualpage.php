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
		$this->__set( 'template', $template_type );

		add_filter( 'the_content', array( $this, 'setUpContent' ) );
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
		$template = '[course_media]';
		$template .= '[course_instructors label=""]';
		$description_label = $this->create_html( 'h3', array( 'class' => '' ), __( 'About this course', 'cp' ) );
		$template .= sprintf( '[course_description label="%s"]', $description_label );
		$template .= '[course_structure]';

		$attr = array( 'class' => 'course-overview' );

		$template = $this->create_html( 'div', $attr, $template );

		return $template;
	}

	function setUpCourseOverview() {}
	function setUpUnitsOverview() {
		return 'HEY';
	}

	function setUnitView() {
		return 'UNIT VIEW';
	}
	function setUpModuleView() {}

}