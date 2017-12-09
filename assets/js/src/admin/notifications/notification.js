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
				// If pagination args available, show alerts page.
				if ( win._coursepress.is_paginated > 0 ) {
					this.currentPage = this.currentTab = 'alerts';
				}
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