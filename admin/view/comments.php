<div class="wrap coursepress_wrapper coursepress-comments">
	<h1><?php esc_html_e( 'Comments', 'coursepress' ); ?></h1>
	<hr />

	<form method="post">
		<?php
		wp_nonce_field( $this->slug, $this->slug );
		$this->comments_list->display();
		?>
	</form>
</div>
