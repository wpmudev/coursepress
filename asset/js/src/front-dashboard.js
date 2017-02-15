/* global _coursepress */
/* global CoursePress */

(function($){
	var confirmWithdrawal = function() {
		var href = $(this).attr( 'href' ),
			win = new CoursePress.WindowAlert({
			type: 'prompt',
			message: _coursepress.confirmed_withdraw,
			callback: function() {
				window.location = href;
			}
		});
		return false;
	};

	var confirmManage= function() {
		var href = $(this).data( 'link' ),
			win = new CoursePress.WindowAlert({
			type: 'prompt',
			message: _coursepress.confirmed_edit,
			callback: function() {
				window.location = href;
			}
		});

		return false;
	};

	$(document)
		.on( 'click', '.cp-withdraw-student', confirmWithdrawal )
		.on( 'click', '.coursepress-course-link', confirmManage );

})(jQuery);
