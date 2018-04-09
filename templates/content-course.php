<?php
/**
 * The template use for courses list.
 *
 * @since 3.0
 * @package CoursePress
 *
 * @var $post CoursePress_Course
 */
global $post;
$course = coursepress_get_course( $post );
$thumbnail = $course->get_feature_image( 'full' );
$course_media = do_shortcode( '[course_media wrapper="figure" list_page="yes"]' );
if ( $course_media ) :
	$extended_class = '';
else :
	$extended_class = 'quick-course-info-extended';
endif;
$coursepress_schema = apply_filters( 'coursepress_schema', '', 'itemscope' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'course-item-box' ); ?>>
	<div class="course-info"<?php echo esc_attr( $coursepress_schema ); ?>>
		<?php
		if ( ! empty( $thumbnail ) ) {
			printf( '<a href="%1$s" class="post-thumbnail" aria-hidden="true">%2$s</a>', esc_url( $course->get_permalink() ), esc_html( $thumbnail ) );
		}
		?>
		<header class="entry-header course-entry-header">
			<?php $title = apply_filters( 'coursepress_schema', get_the_title(), 'title' ); ?>
			<h3 class="entry-title course-title"><a href="<?php echo esc_url( $course->get_permalink() ); ?>"
			                                        rel="bookmark"><?php echo esc_html( $title ); ?></a></h3>
		</header>
		<?php if ( is_search() ) : // Only display Excerpts for Search ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
		<?php else : ?>
			<div class="entry-content <?php echo esc_attr( $extended_class ); ?>">
				<?php
				// Course summary/excerpt
				echo do_shortcode( '[course_summary length="50" class="' . $extended_class . '"]' );
				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . __( 'Pages:', 'cp' ),
						'after'  => '</div>',
					)
				);
				$coursepress_schema = apply_filters( 'coursepress_schema', '', 'itemscope-person' );
				?>
				<div class="instructors-content"<?php echo esc_attr( $coursepress_schema ); ?>>
					<?php
					// Flat hyperlinked list of instructors
					echo do_shortcode( '[course_instructors style="list-flat" link="true"]' );
					?>
				</div>
				<div
					class="quick-course-info <?php echo( isset( $extended_class ) ? esc_attr( $extended_class ) : '' ); ?>">
					<?php
					echo do_shortcode( '[course_start label="" class="course-time"]' );
					echo do_shortcode( '[course_language label="" class="course-lang"]' );
					echo do_shortcode( '[course_cost label="" show_icon="true"]' );
					echo do_shortcode( '[course_join_button details_text="' . __( 'Details', 'cp' ) . '" course_expired_text="' . __( 'Not Available', 'cp' ) . '" list_page="yes"]' );
					?>
					<!--go-to-course-button-->
				</div>
			</div><!-- .entry-content -->

		<?php endif; ?>
	</div>
</article>
