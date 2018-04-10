<?php
/**
 * The template use for coursepress custom student login/registration.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Student Login', 'cp' ); ?></h1>
				</header>

				<?php coursepress_wp_login_form(); ?>
			</div>
		</div>
	</div>
<?php get_footer();
