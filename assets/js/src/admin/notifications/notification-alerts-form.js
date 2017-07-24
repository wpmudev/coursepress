/* global CoursePress */

(function() {
	'use strict';

	CoursePress.Define( 'NotificationAlertsForm', function( $, doc, win ) {
		return CoursePress.View.extend({
			template_id: 'coursepress-notification-alerts-form-tpl',
			el: $('#notification-alerts_form'),
			events: {
				'click .cp-alert-submit': 'createAlert',
			},

			// Initialize.
			initialize: function() {
				this.request = new CoursePress.Request();

				this.on( 'view_rendered', this.setUpUI, this );

				// Update units and students based on selections.
				this.request.on( 'coursepress:success_create_course_alert', this.showSuccess, this );
				this.request.on( 'coursepress:error_create_course_alert', this.showError, this );

				this.render();
			},

			// Setup UI elements.
			setUpUI: function() {
				// Setup select2.
				this.$('select').select2({
					width: '100%',
				});
			},

			// Create new course alert.
			createAlert: function () {

				// Editor content.
				var content = win.tinymce.editors.alert_content.getContent();
				var title = this.$('#alert-title').val();
				if ( '' !== content && '' !== title ) {
					this.request.set( {
						'action': 'create_course_alert',
						'title': title,
						'content': content,
					} );
					this.request.save();
				}
			},

			// Show success notification after creating new alert.
			showSuccess: function () {

			},

			// Show error message when new alert failed.
			showError: function () {

			}
		});
	});

})();