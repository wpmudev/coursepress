<?php
/**
 * Units archive template.
 *
 * @since 3.0.0
 * @package CoursePress
 */
get_header(); ?>

<div class="wrap">
	<main role="main" class="site-main">
		<div class="container">
			<section class="course-info">
				<header class="entry-header">
					<h1 class="entry-title course-title">
                        <?php echo coursepress_get_the_title(); ?>
                    </h1>

                    <?php
                        get_template_part( 'course/submenu' );
                    ?>
				</header>
			</section>
		</div>
	</main>
</div>

<?php get_footer();
