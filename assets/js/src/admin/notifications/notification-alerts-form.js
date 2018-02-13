/* global CoursePress */

(function() {
	'use strict';

	CoursePress.Define( 'NotificationAlertsForm', function( $, doc, win ) {
		return CoursePress.View.extend({
			template_id: 'coursepress-notification-alerts-form-tpl',
			el: $('#notification-alerts_form'),
			events: {
				'click .cp-alert-submit': 'updateAlert',
				'click .cp-alert-cancel': 'clearForm',
				'change #cp-alert-course': 'showHideReceivers',
			},

			// Initialize.
			initialize: function() {
				this.request = new CoursePress.Request();

				this.on( 'view_rendered', this.setUpUI, this );

				// Update units and students based on selections.
				this.request.on( 'coursepress:success_update_course_alert', this.showSuccess, this );
				this.request.on( 'coursepress:error_update_course_alert', this.showError, this );

				this.render();
			},

			// Setup UI elements.
			setUpUI: function() {
				// Setup select2.
				this.$('select').select2({
					width: '100%',
				});
			},

			// Show or hide receivers.
			showHideReceivers: function ( ev ) {

				var course = this.$(ev.currentTarget).val();
				if ( course === '' || course === 'all' ) {
					this.$('#cp-receivers-div').addClass('inactive');
				} else {
					this.$('#cp-receivers-div').removeClass('inactive');
				}
			},

			// Create or Update course alert.
			updateAlert: function ( ev ) {

				// Add progress.
				this.$(ev.currentTarget).addClass('cp-progress');
				// Editor content.
				var content,
					title = this.$('#alert-title').val(),
					course_id = this.$('#cp-alert-course').val(),
					receivers = this.$('#cp-alert-receivers').val(),
					alert_id = this.$('#alert-id').val();
				if ( undefined === win.tinymce.editors.alert_content ) {
					content = this.$('#alert_content').val();
				} else {
					content = win.tinymce.editors.alert_content.getContent();
				}
				if ( '' !== content && '' !== title && '' !== course_id ) {
					this.request.set( {
						'action': 'update_course_alert',
						'course_id': course_id,
						'alert_id': alert_id,
						'title': title,
						'content': content,
					} );

					// Set receivers only if course is selected.
					if ( course_id !== '' && course_id === 'all' ) {
						this.request.set( 'receivers', receivers );
					}

					this.request.save();
				} else {
					// Remove progress.
					this.$('.cp-alert-submit').removeClass('cp-progress');
				}

				return false;
			},

			// After creating new alert.
			showSuccess: function ( data ) {
				new CoursePress.PopUp({
					type: 'info',
					message: data.message
				});
				// Hide progress icon.
				this.$('.cp-alert-submit').removeClass('cp-progress');
				this.clearForm();
			},

			// After new alert failed.
			showError: function ( data ) {
				new CoursePress.PopUp({
					type: 'error',
					message: data.message
				});
				// Hide progress icon.
				this.$('.cp-alert-submit').removeClass('cp-progress');
			},

			// Clear field values.
			clearForm: function () {
				if ( undefined === win.tinymce.editors.alert_content ) {
					this.$('#alert_content').val('');
				} else {
					win.tinymce.editors.alert_content.setContent('');
				}
				this.$('#alert-title').val('');
				this.$('#alert-id').val('');
				this.$('#cp-alert-course').val('all').trigger('change');
			}
		});
	});

})();
