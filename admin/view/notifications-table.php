<?php
$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
$new_notification_url = add_query_arg( 'action', 'edit' );
$post_type = CoursePress_Data_Notification::get_post_type_name();
$notification_type_object = get_post_type_object( $post_type );
$labels = get_post_type_labels( $notification_type_object );
?>
<div class="wrap coursepress_wrapper course-notifications">
<h2><?php
echo $labels->name;
if ( CoursePress_Data_Capabilities::can_add_notifications() ) {
?> <a href="<?php echo $new_notification_url; ?>" class="page-title-action"><?php echo $labels->add_new; ?></a>
<?php
}
?></h2>
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
