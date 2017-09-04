/*! CoursePress - v3.0.0
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
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
(function() {
	'use strict';

	/**
	 * Notifications pages including sub pages.
	 */
	CoursePress.Define( 'Notification', function( $ ) {
		var Notification;

		Notification = CoursePress.View.extend( {
			el: $( '#coursepress-notifications' ),
			currentPage: 'emails',
			currentTab: 'emails',
			events: {
				'click .cp-notification-menu-item': 'setNotificationPage',
			},

			// While initializing.
			initialize: function () {
				this.once( 'coursepress:notification_emails', this.getEmailsView, this );
				this.once( 'coursepress:notification_alerts', this.getAlertsView, this );
				this.once( 'coursepress:notification_alerts_form', this.getAlertsFormView, this );

				CoursePress.View.prototype.initialize.apply( this, arguments );
			},

			// On rendering page.
			render: function() {
				this.on( 'coursepress:notification', this.setCurrentPage, this );
				this.setPage( this.currentPage, this.currentTab );
			},

			// Set current page.
			setCurrentPage: function() {
				this.currentMenu = this.$('.cp-notification-menu-item.notification-' + this.currentTab );
				this.currentView = this.$( '#notification-' + this.currentPage );

				this.currentMenu.addClass('active');
				this.currentMenu.siblings().removeClass('active');
				this.currentView.removeClass( 'inactive' );
				this.currentView.siblings().addClass('inactive');
			},

			// Set a page.
			setPage: function( page, tab ) {
				this.currentPage = page;
				this.currentTab = tab;
				this.trigger( 'coursepress:notification', page, tab );
				this.trigger( 'coursepress:notification_' + page );
			},

			// Set a page template.
			setNotificationPage: function( ev ) {
				var target = $( ev.currentTarget ),
					page = target.data('page'),
					tab = target.data('tab');

				this.setPage( page, tab );
			},

			// Email form page.
			getEmailsView: function() {
				new CoursePress.NotificationEmails();
			},

			// Alerts listing page.
			getAlertsView: function() {
				new CoursePress.NotificationAlerts();
			},

			// Alerts form page.
			getAlertsFormView: function() {
				new CoursePress.NotificationAlertsForm();
			},
		} );

		Notification = new Notification();
	});

})();