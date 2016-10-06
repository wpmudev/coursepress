/* global _coursepress */
/* global CoursePress */

(function( $ ) {
	CoursePress.Enroll = function() {
		var form = $(this),
			student_id = $( '[name="student_id"]', form ),
			has_error = false
		;

		if ( 0 < parseInt( student_id ) ) {
			//code
			CoursePress.showError( form.parent().parent() );
			has_error = true;
		}

		if ( has_error ) {
			// Don't enroll
			return false;
		}
	};

	// Hook the events
	$( document )
		.on( 'submit', '.enrollment-process', CoursePress.Enroll );

})(jQuery);