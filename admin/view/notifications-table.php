<?php
$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
$new_notification_url = add_query_arg( 'action', 'edit' );
?>
<div class="wrap coursepress_wrapper course-notifications">
	<h2><?php esc_html_e( 'Notifications', 'cp' ); ?> <a href="<?php echo $new_notification_url; ?>" class="page-title-action"><?php esc_html_e( 'New Notification', 'cp' ); ?></a></h2>
	<hr />

	<form method="post">
		<?php
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false, false );
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false, false );
		?>
		<div class="nonce-holder" data-nonce="<?php echo $bulk_nonce; ?>"></div>
		<?php $this->list_notification->display(); ?>
	</form>
</div>