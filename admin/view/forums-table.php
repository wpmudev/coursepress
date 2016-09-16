<?php
$id = 0;
$reset_url = remove_query_arg(
	array(
		'view',
		'_wpnonce',
		'id',
	)
);

$new_url = add_query_arg( 'action', 'edit', $reset_url );
CoursePress_Admin_Forums::init();
?>
<div class="wrap coursepress_wrapper coursepress-discussions">
<h2><?php
echo CoursePress_Admin_Forums::get_label_by_name( 'name' );
CoursePress_Admin_Forums::add_button_add_new();
?></h2>
	<hr />
	<form method="post">
		<?php
		wp_nonce_field( 'coursepress_discussion_list' );
		$this->list_forums->display(); ?>
	</form>
</div>
