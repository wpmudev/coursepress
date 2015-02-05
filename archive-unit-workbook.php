<?php
/**
 * The units archive / grades template file
 *
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode('[get_parent_course_id]');
$course_id = (int) $course_id;
$progress = do_shortcode('[course_progress course_id="' . $course_id . '"]');
//redirect to the parent course page if not enrolled
$coursepress->check_access($course_id);

get_header();

add_thickbox();
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
		<h1 class="workbook-title">
			<?php echo do_shortcode('[course_title course_id="' . $course_id . '" title_tag=""]'); ?>
			<?php if ( 100 > (int) $progress ) { ?>
				<span class="workbook-course-progress"><?php echo esc_html( $progress ); ?>% <?php esc_html_e('completed', 'cp'); ?></span>
			<?php } ?>
		</h1>

        <div class="instructors-content">
            <?php
            // Flat hyperlinked list of instructors
            echo do_shortcode('[course_instructors style="list-flat" link="true" course_id="' . $course_id . '"]');
            ?>
        </div>

        <?php
        echo do_shortcode('[course_unit_archive_submenu]');
        ?>
		<?php
			if( 100 == (int) $progress) {
				echo sprintf( '<div class="unit-archive-course-complete">%s %s</div>', '<i class="fa fa-check-circle"></i>', __( 'Course Complete', 'cp' ) );
			}
		?>

        <div class="clearfix"></div>

        <?php
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                ?>
                <div class="workbook_units">
                    <div class="unit_title">
                        <h3><?php the_title(); ?>
                            <span><?php echo do_shortcode('[course_unit_progress course_id="' . $course_id . '" unit_id="' . get_the_ID() . '"]'); ?>% <?php _e('completed', 'cp'); ?></span>
                        </h3>
                    </div>
                    <div class="accordion-inner">
                        <?php echo do_shortcode('[student_workbook_table]'); ?>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="zero-courses"><?php _e('0 Units in the course', 'cp'); ?></div>
            <?php
        }
        ?>

        <!--<ul class="units-archive-list">
        <?php if ( have_posts() ) { ?>
            <?php
            $grades = 0;
            $units = 0;
            while ( have_posts() ) {
                the_post();
                $grades = $grades + do_shortcode('[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]');
                ?>
                <a class="unit-archive-single-title" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                <?php if ( do_shortcode('[course_unit_details field="input_modules_count"]') > 0 ) { ?>
                                                                                                                                                                                                                                                        <span class="unit-archive-single-module-status"><?php echo do_shortcode('[course_unit_details field="student_module_responses"]'); ?> <?php _e('of', 'cp'); ?> <?php echo do_shortcode('[course_unit_details field="mandatory_input_modules_count"]'); ?> <?php _e('mandatory elements completed', 'cp'); ?> | <?php echo do_shortcode('[course_unit_details field="student_unit_modules_graded" unit_id="' . get_the_ID() . '"]'); ?> <?php _e('of', 'cp'); ?> <?php echo do_shortcode('[course_unit_details field="input_modules_count"]'); ?> <?php _e('elements graded', 'cp'); ?></span>
                <?php } else { ?>
                                                                                                                                                                                                                                                        <span class="unit-archive-single-module-status read-only-module"><?php _e('Read only'); ?></span>
                <?php } ?>
                                                                                                                                                                        </div>
                                                                                                                                                                    </li>
                <?php
                $units++;
            }
            ?>
                    <div class="total_grade"><?php echo apply_filters('coursepress_grade_caption', ( __('TOTAL:', 'cp'))); ?> <?php echo apply_filters('coursepress_grade_total', ( $grades > 0 ? ( round($grades / $units, 0) ) : 0 ) . '%'); ?></div>
            <?php
        } else {
            ?>
                    <h1 class="zero-course-units"><?php _e("0 units in the course currently. Please check back later."); ?></h1>
            <?php
        }
        ?>
        </ul>-->
    </main><!-- #main -->
</div><!-- #primary -->
<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>