<section class="course-info">
	<div class="container">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title course-title">', '</h1>' ); ?>

				</header>
			</article>

		<?php endwhile; endif; ?>
	</div>
</section>