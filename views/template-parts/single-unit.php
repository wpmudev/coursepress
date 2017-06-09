<?php
/**
 * The template use for course unit.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="wrap coursepress-wrap">
		<div class="container">
			<main class="site-main" role="main">
				<?php coursepress_breadcrumb(); ?>

				<article id="post-<?php get_the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="page-title"><?php echo coursepress_get_the_title(); ?></h1>
						<h2 class="entry-title course-title"><?php echo coursepress_get_unit_title(); ?></h2>
					</header>

                    <div class="course-content-template">
                        <div class="course-structure course-structure-nav">
                            <?php echo coursepress_get_unit_structure(); ?>
                        </div>
                        <div class="course-content">
	                        <?php echo coursepress_get_the_content(); ?>

                            <div class="course-step-nav">
                                <div class="course-previous-item">
			                        <?php echo coursepress_get_previous_item_link(); ?>
                                </div>

                                <div class="course-next-item">
			                        <?php echo coursepress_get_next_item_link(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

				</article>
			</main>
		</div>
	</div>

<?php get_footer();
