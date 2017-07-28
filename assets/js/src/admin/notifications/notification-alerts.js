/* global CoursePress */

(function() {
	'use strict';

	CoursePress.Define( 'NotificationAlerts', function( $ ) {
		return CoursePress.View.extend({
			template_id: 'coursepress-notification-alerts-tpl',
			el: $('#notification-alerts'),
			events: {
				'change .cp-toggle-alert-status': 'toggleAlertStatus',
			},

			initialize: function() {
				this.request = new CoursePress.Request();

				// On status toggle fail.
				this.request.on( 'coursepress:error_alert_status_toggle', this.revertStatusToggle, this );

				this.render();
			},

			/**
			 * Toggle course status.
			 */
			toggleAlertStatus: function( ev ) {
				this.request.selector = $(ev.target);
				var status = this.request.selector.prop('checked') ? 'publish' : 'draft';
				this.request.set( {
					'action' : 'alert_status_toggle',
					'alert_id' : this.request.selector.val(),
					'status' : status
				} );
				this.request.save();
			},

			/**
			 * Revert toggled status.
			 */
			revertStatusToggle: function() {
				var checked = this.request.selector.prop('checked');
				this.request.selector.prop('checked', !checked);
			},
		});
	});

})();