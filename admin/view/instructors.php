<div class="wrap coursepress_wrapper coursepress-instructors">
	<h2><?php esc_html_e( 'Instructors', 'coursepress' ); ?></h2>
	<hr />

	<form method="post">
		<?php $this->instructors_list->display(); ?>
	</form>
</div>
