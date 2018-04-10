<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $notifications
 */
?>
<div class="wrap coursepress-wrap coursepress-notifications" id="coursepress-notifications">
	<h1 class="wp-heading-inline">
		<?php _e( 'Notifications', 'cp' ); ?>
	</h1>

	<div class="coursepress-page ">

		<ul class="cp-notification-menu">
			<li class="cp-notification-menu-item notification-alerts" data-page="alerts" data-tab="alerts"><?php _e( 'Course Alerts', 'cp' ); ?></li>
			<li class="cp-notification-menu-item notification-emails" data-page="emails" data-tab="emails"><?php _e( 'Notification Emails', 'cp' ); ?></li>
		</ul>

		<div class="notifications-content">
			<div id="notification-emails" class="notifications-content-tab"></div>
			<div id="notification-alerts" class="notifications-content-tab cp-full-width"></div>
			<div id="notification-alerts_form" class="notifications-content-tab"></div>
		</div>

	</div>
</div>