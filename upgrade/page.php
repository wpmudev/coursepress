<?php
/**
 * Custom Upgrade page
 */
get_header(); ?>

<section class="cp-custom-section">
	<div class="container">
		<?php CoursePress_Upgrade_1x_Data::upgrade_notice( 'frontend-nag' ); ?>
	</div>
</section>

<?php get_footer();

