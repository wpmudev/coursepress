/* global CoursePress */

(function() {
	'use strict';

	/**
	 * Notifications pages including sub pages.
	 */
	CoursePress.Define( 'Notification', function( $ ) {
		var Notification;

		Notification = CoursePress.View.extend( {
			el: $( '#coursepress-notifications' ),
			currentPage: 'alerts',
			currentTab: 'alerts',
			events: {
				'click .cp-notification-menu-item': 'setNotificationPage',
				'click .cp_edit_alert': 'setNotificationPage',
				'click .cp-btn-cancel': 'setNotificationPage',
			},

			// While initializing.
			initialize: function () {
                                this.request = new CoursePress.Request();
                                this.alert_form = '';
                                this.notification_email = '';

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
					alert_id = target.data('id'),
					tab = target.data('tab');

                                if ( undefined === alert_id && 'alerts' === page ) {
                                    this.reloadAlerts();
                                    return;
                                }
				this.setPage( page, tab );
				//Clear form
				// $('.cp-alert-cancel').trigger('click');
				if ( this.alert_form ) {
				    if ( undefined !== alert_id ) {
					this.alert_form.getAlertData( alert_id );
				    } else {
					this.alert_form.clearForm();
				    }
				} else if ( this.notification_email ) {
				    this.notification_email.clearForm();
				}
				ev.preventDefault();
			},

			// Email form page.
			getEmailsView: function() {
				this.notification_email = new CoursePress.NotificationEmails();
			},

			// Alerts listing page.
			getAlertsView: function() {
				new CoursePress.NotificationAlerts();
			},

			// Alerts form page.
			getAlertsFormView: function() {
				this.alert_form = new CoursePress.NotificationAlertsForm();
			},

                        /**
                         * Reload the current page.
                         */
                        reloadAlerts: function() {
                            location.reload();
                        }
		} );

		Notification = new Notification();
	});

})();
