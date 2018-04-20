<div class="wrap coursepress_wrapper coursepress-students">
	<h2><?php esc_html_e( 'Students', 'coursepress' ); ?></h2>
	<hr />

	<form method="post">
		<?php
		wp_nonce_field( $this->slug, $this->slug );
		$this->students_list->display();
		?>
	</form>
</div>