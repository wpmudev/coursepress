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

	$(document)
		.on( 'click', '.cp-withdraw-student', confirmWithdrawal );

})(jQuery);