<?php
/**
 * The units archive template file
 * 
 * @package CoursePress
 */
global $coursepress;
$course_id	 = do_shortcode( '[get_parent_course_id]' );
$progress	 = do_shortcode( '[course_progress course_id="' . $course_id . '"]' );
//redirect to the parent course page if not enrolled
$coursepress->check_access( $course_id );

get_header();
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1><?php echo do_shortcode( '[course_title course_id="' . $course_id . '"]' ); ?></h1>

        <div class="instructors-content">
			<?php
			// Flat hyperlinked list of instructors
			echo do_shortcode( '[course_instructors style="list-flat" link="true" course_id="' . $course_id . '"]' );
			?>
        </div>

		<?php
		do_shortcode( '[course_unit_archive_submenu]' );
		?>
		<?php
		if ( 100 == (int) $progress ) {
			echo sprintf( '<div class="unit-archive-course-complete">%s %s</div>', '<i class="fa fa-check-circle"></i>', __( 'Course Complete', 'cp' ) );
		}
		?>

        <div class="clearfix"></div>
        <ul class="units-archive-list">
			<?php if ( have_posts() ) { ?>
				<?php
				$args	 = array(
					'order'			 => 'ASC',
					'post_type'		 => 'unit',
					'post_status'	 => (current_user_can( 'manage_options' ) ? 'any' : 'publish'),
					'meta_key'		 => 'unit_order',
					'orderby'		 => 'meta_value_num',
					'posts_per_page' => '-1',
					'meta_query'	 => array(
						'relation' => 'AND',
						array(
							'key'	 => 'course_id',
							'value'	 => $course_id
						),
					)
				);
				$posts	 = query_posts( $args );
				while ( have_posts() ) {
					the_post();

					$additional_class	 = '';
					$additional_li_class = '';

					$is_unit_available = do_shortcode( '[course_unit_details field="is_unit_available" unit_id="' . get_the_ID() . '"]' );

					if ( !$is_unit_available ) {
						$additional_class	 = 'locked-unit';
						$additional_li_class = 'li-locked-unit';
					}

					$input_modules_count			 = do_shortcode( '[course_unit_details field="input_modules_count"]' );
					$assessable_input_modules_count	 = do_shortcode( '[course_unit_details field="assessable_input_modules_count"]' );
					?>
					<li class="<?php echo $additional_li_class; ?>">
						<div class='<?php echo $additional_class; ?>'></div>
						<div class="unit-archive-single">
							<?php echo do_shortcode( '[course_unit_details field="percent" format="true" style="extended"]' ); ?>
							<a class="unit-archive-single-title" href="<?php echo do_shortcode( '[course_unit_details field="permalink" last_visited="true" unit_id="' . get_the_ID() . '"]' ); ?>" rel="bookmark"><?php the_title() . ' ' . (get_post_status() !== 'publish' && current_user_can( 'manage_options' ) ? _e( ' [DRAFT]', 'cp' ) : ''); ?></a>
							<?php do_shortcode( '[module_status format="true" course_id="' . $course_id . '" unit_id="' . get_the_ID() . '"]' ); ?>
						</div>
					</li>
					<?php
				}
			} else {
				?>
				<h1 class="zero-course-units"><?php _e( "0 units in the course currently. Please check back later." ); ?></h1>
				<?php
			}
			wp_reset_postdata();
			?>
        </ul>
    </main><!-- #main -->
</div><!-- #primary -->
<?php get_sidebar( 'footer' ); ?>
<?php get_footer(); ?>