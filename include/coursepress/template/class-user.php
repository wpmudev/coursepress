<?php

class CoursePress_Template_User {

	public static function render_instructor_page() {
		$instructor_page = sprintf( '<div class="cp-instructor-page">%s</div>', do_shortcode( '[instructor_page]' ) );

		return $instructor_page;
	}

	public static function render_facilitator_page() {
		return do_shortcode( '[facilitator_page]' );
	}

}
