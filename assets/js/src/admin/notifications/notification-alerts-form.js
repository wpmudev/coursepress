/* global CoursePress */

(function() {
	'use strict';

	CoursePress.Define( 'NotificationAlertsForm', function( $, doc, win ) {
		return CoursePress.View.extend({
			template_id: 'coursepress-notification-alerts-form-tpl',
			el: $('#notification-alerts_form'),
			events: {
				'click .cp-alert-submit': 'createAlert',
				'click .cp-alert-cancel': 'clearForm',
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
			createAlert: function ( ev ) {

				this.$(ev.currentTarget).addClass('cp-progress');
				// Editor content.
				var content = win.tinymce.editors.alert_content.getContent(),
					title = this.$('#alert-title').val(),
					course_id = this.$('#cp-alert-course').val();
				if ( '' !== content && '' !== title && '' !== course_id ) {
					this.request.set( {
						'action': 'create_course_alert',
						'course_id': course_id,
						'title': title,
						'content': content,
					} );
					this.request.save();
				}
			},

			// After creating new alert.
			showSuccess: function () {
				// Hide progress icon.
				this.$('.cp-alert-submit').removeClass('cp-progress');
				this.clearForm();
			},

			// After new alert failed.
			showError: function () {
				// Hide progress icon.
				this.$('.cp-alert-submit').removeClass('cp-progress');
			},

			// Clear field values.
			clearForm: function () {

				win.tinymce.editors.alert_content.setContent('');
				this.$('#alert-title').val('');
			}
		});
	});

})();