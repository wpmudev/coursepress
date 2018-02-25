/* global CoursePress */

(function() {
	'use strict';

	/**
	 * Notifications pages including sub pages.
	 */
	CoursePress.Define( 'Notification', function( $, doc, win ) {
		var Notification;

		Notification = CoursePress.View.extend( {
			el: $( '#coursepress-notifications' ),
			currentPage: 'alerts',
			currentTab: 'alerts',
			events: {
				'click .cp-notification-menu-item': 'setNotificationPage',
				'click .cp_edit_alert': 'setNotificationPage',
			},

			// While initializing.
			initialize: function () {
                                this.request = new CoursePress.Request();

				this.once( 'coursepress:notification_emails', this.getEmailsView, this );
				this.once( 'coursepress:notification_alerts', this.getAlertsView, this );
				this.once( 'coursepress:notification_alerts_form', this.getAlertsFormView, this );
				this.request.on( 'coursepress:success_get_course_alert', this.setAlertData, this );

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
					alert_id = target.data('id'),
					tab = target.data('tab');

				this.setPage( page, tab );
				//Clear form
				$('.cp-alert-cancel').trigger('click');
				if ( undefined !== alert_id ) {
					//set existing alert data
					this.request.set( {
						'action': 'get_course_alert',
						'alert_id': alert_id,
					} );
					this.request.save();
				}
			},

			//set Alert Data
			setAlertData: function( data ) {
				this.$('#alert-id').val( data.id );
				this.$('#alert-title').val( data.title );
				this.$('#cp-alert-course').val( data.course_id ).trigger('change');

				if ( undefined === win.tinymce.editors.alert_content ) {
					this.$('#alert_content').val( data.content );
				} else {
					win.tinymce.editors.alert_content.setContent( data.content );
				}
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