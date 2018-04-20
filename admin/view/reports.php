<div class="wrap coursepress_wrapper coursepress-reports">
	<h2><?php esc_html_e( 'Reports', 'coursepress' ); ?></h2>
	<hr />

	<form method="post">
		<?php
		wp_nonce_field( 'coursepress_report' );
		$this->reports_table->display();
		?>
	</form>
</div>