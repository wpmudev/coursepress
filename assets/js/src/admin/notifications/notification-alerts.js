/* global CoursePress */

(function() {
	'use strict';

	CoursePress.Define( 'NotificationAlerts', function( $ ) {
		return CoursePress.View.extend({
			template_id: 'coursepress-notification-alerts-tpl',
			el: $('#notification-alerts'),
		});
	});

})();