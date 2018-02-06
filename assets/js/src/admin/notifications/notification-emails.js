/* global CoursePress, tinyMCE */

(function() {
	'use strict';

	CoursePress.Define( 'NotificationEmails', function( $, doc, win ) {
		return CoursePress.View.extend({
			template_id: 'coursepress-notification-emails-tpl',
			el: $('#notification-emails'),
			events: {
				'change #cp-course': 'getUnitsStudents',
				'change #cp-unit': 'getStudents',
				'click #cp-add-student-btn': 'selectStudent',
				'click ul#cp-notifications-students li': 'unSelectStudent',
				'click .cp-send-email': 'sendEmail',
			},

			// Initialize.
			initialize: function() {
				this.request = new CoursePress.Request();
				this.on( 'view_rendered', this.setUpUI, this );
				// Update units and students based on selections.
				this.request.on( 'coursepress:success_get_notification_units_students', this.updateUnitsStudents, this );
				this.request.on( 'coursepress:success_get_notification_students', this.updateStudents, this );
				this.render();
			},

			// Setup UI elements.
			setUpUI: function() {
				// Setup course select.
				this.$('#cp-course').select2({
					width: '100%'
				});
				// Setup units and students select.
				this.$('#cp-unit, #cp-student').select2({
					width: '100%',
					data: []
				});
                win.setTimeout( function() {
                    if ( tinyMCE.get( 'notification_content' ) ) {
                        this.contentEditor = tinyMCE.get( 'notification_content' );
                    }
                }, 1000 );
			},

			// Get units and students on course selection.
			getUnitsStudents: function ( ev ) {

				// Get the updated list of units.
				var course_id = $( ev.currentTarget ).val();
				if ( '' !== course_id ) {
					this.request.set( {
						'action': 'get_notification_units_students',
						'course_id': course_id,
					} );
					this.request.save();
				}
			},

			// Get students list on unit select.
			getStudents: function () {

				var unit_id = this.$('#cp-unit').val();
				if ( '0' !== unit_id && '' !== unit_id ) {
					this.request.set( {
						'action': 'get_notification_students',
						'unit_id': unit_id,
					} );
					this.request.save();
				}
			},

			// Update units and students options.
			updateUnitsStudents: function ( response ) {

				// Update units value.
				this.$('#cp-unit').empty().select2({
					data: _.isEmpty( response.units ) ? [] : response.units
				});

				// Update students options.
				this.updateStudents( response );
			},

			// Update students options.
			updateStudents: function ( response ) {

				this.$('#cp-student').empty().select2({
					data: _.isEmpty( response.students ) ? [] : response.students
				});
			},

			// Select student for notifications.
			selectStudent: function () {

				var student_id = this.$('#cp-student').val();
				var student_name = this.$('#cp-student :selected').text();
				// If user assigned, add them to the tags.
				if ( ! _.isEmpty( student_id ) && ! _.isEmpty( student_name ) ) {
					this.setSelectedStudents( student_id, student_name );
				}
			},

			// Unselect student for notifications.
			unSelectStudent: function ( ev ) {

				// Remove student from selection.
				$( ev.currentTarget ).remove();
				// If all students are removed, add default option.
				if ( this.$('#cp-notifications-students li').length === 0 ) {
					this.setSelectedStudents( 0, win._coursepress.text.all_students );
				}
			},

			setSelectedStudents: function ( id, name ) {
				this.$('ul#cp-notifications-students').append('<li data-user-id="' + id + '">' + name + '</li>');
			},

			// Send email notification.
			sendEmail: function ( ev ) {

				this.$(ev.currentTarget).addClass('cp-progress');
				var content = win.tinymce.editors.notification_content.getContent(),
					title = this.$('#notification-title').val(),
					students = [],
					selector = this.$('#cp-notifications-students li');
				if ( selector.length !== 0 ) {
					selector.each(function () {
						if ( typeof $( this ).data('user-id') !== 'undefined' ) {
							students.push( $( this ).data('user-id') );
						}
					});
				}

				if ( ! _.isEmpty( students ) ) {
					this.request.set( {
						'action': 'send_notification_email',
						'students': students,
						'title': title,
						'content': content,
					} );
					this.request.on( 'coursepress:success_send_notification_email', this.afterEmail, this );
					this.request.on( 'coursepress:error_send_notification_email', this.afterEmail, this );
					this.request.save();
				}
			},

			// After email notification.
			afterEmail: function () {
				// Hide progress icon.
				this.$('.cp-send-email').removeClass('cp-progress');
			}

		});
	});

})();
