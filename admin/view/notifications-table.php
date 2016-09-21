<?php
$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
?>
<div class="wrap coursepress_wrapper course-notifications">
<h2><?php
echo CoursePress_Admin_Notifications::get_label_by_name( 'name' );
CoursePress_Admin_Notifications::add_button_add_new();
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
