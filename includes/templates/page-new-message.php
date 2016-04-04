<section id="primary" class="content-area page-new-message">
	<main id="main" class="site-main" role="main">
		<?php
		if ( get_option( 'show_messaging', 0 ) == 1 ) {
			echo do_shortcode( '[messaging_submenu]' );
			if ( function_exists( 'messaging_new_page_output' ) ) {
				?>
				<div class="cp_messaging_wrap">
					<?php messaging_new_page_output(); ?>
				</div>

			<?php
			} else {
				_e( 'Messaging plugin is not active.', 'cp' );
			}
		} else {
			_e( 'Messaging is not allowed.', 'cp' );
		}
		?>
	</main>
	<!-- #main -->
</section><!-- #primary -->