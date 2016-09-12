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
?>
<div class="wrap coursepress_wrapper coursepress-discussions">
	<h2><?php esc_html_e( 'Forums', 'cp' ); ?> <a href="<?php echo esc_url( $new_url ); ?>" class="page-title-action"><?php esc_html_e( 'New Thread', 'cp' ); ?></a></h2>
	<hr />

	<form method="post">
		<?php
		wp_nonce_field( 'coursepress_discussion_list' );
		$this->list_forums->display(); ?>
	</form>
</div>