<?php
/**
 * @package CoursePress
 */
?>
<?php
$course				 = new Course( get_the_ID() );
$course_category_id	 = $course->details->course_category;
$course_category	 = get_term_by( 'ID', $course_category_id, 'course_category' );

$course_language = $course->details->course_language;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	$course_media	 = do_shortcode( '[course_media wrapper="figure" list_page="yes"]' );

	if ( $course_media == '' ) {
		$extended_class = 'quick-course-info-extended';
	} else {
		$extended_class = '';
	}
	?>

	<?php
	// Course thumbnail
	echo $course_media;
	//echo do_shortcode('[course_media type="image" img_wrapper="figure"]');
	?>

    <section class='article-content-right <?php echo $extended_class; ?> course-archive'>
        <header class="entry-header">
            <h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
        </header><!-- .entry-header -->

		<?php if ( is_search() ) : // Only display Excerpts for Search   ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
		<?php else : ?>
			<div class="entry-content <?php echo $extended_class; ?>">
				<div class="instructors-content">
					<?php
					// Flat hyperlinked list of instructors
					echo do_shortcode( '[course_instructors style="list-flat" link="true"]' );
					?>
				</div>

				<?php
				// Course summary/excerpt
				echo do_shortcode( '[course_summary length="50" class="' . $extended_class . '"]' );
				?>

				<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'cp' ),
					'after'	 => '</div>',
				) );
				?>
				<div class="quick-course-info <?php echo ( isset( $extended_class ) ? $extended_class : '' ); ?>">
					<?php echo do_shortcode( '[course_start label="" class="course-time"]' ); ?>
					<?php echo do_shortcode( '[course_language label="" class="course-lang"]' ); ?>
					<?php //echo do_shortcode('[course_cost label="" no_cost_text=""]'); ?>
					<?php echo do_shortcode( '[course_cost label="" class="course-cost" show_icon="true"]' ); ?>
					<?php echo do_shortcode( '[course_join_button details_text="' . __( 'Details', 'cp' ) . '" course_expired_text="' . __( 'Not Available', 'cp' ) . '" list_page="yes"]' ); ?>
					<!--go-to-course-button-->
				</div>
			</div><!-- .entry-content -->

		<?php endif; ?>

        <footer class="entry-meta">
			<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search  ?>
				<?php
				/* translators: used between list items, there is a space after the comma */
				$categories_list = get_the_category_list( __( ', ', 'cp' ) );
				if ( $categories_list && coursepress_categorized_blog() ) :
					?>
					<span class="cat-links">
						<?php printf( __( 'Courses in %1$s', 'cp' ), $categories_list ); ?>
					</span>
				<?php endif; // End if categories   ?>

				<?php
				/* translators: used between list items, there is a space after the comma */
				$tags_list = get_the_tag_list( '', __( ', ', 'cp' ) );
				if ( $tags_list ) :
					?>
					<span class="tags-links">
						<?php printf( __( 'Tagged %1$s', 'cp' ), $tags_list ); ?>
					</span>
				<?php endif; // End if $tags_list   ?>
			<?php endif; // End if 'post' == get_post_type()  ?>

			<?php /* if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
			  <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'coursepress' ), __( '1 Comment', 'coursepress' ), __( '% Comments', 'coursepress' ) ); ?></span>
			  <?php endif; */ ?>
        </footer><!-- .entry-meta -->
    </section>
</article><!-- #post-## -->
<br style="clear: both;" />