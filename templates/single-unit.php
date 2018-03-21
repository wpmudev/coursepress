<?php
/**
 * The template use for course unit.
 *
 * @since 3.0
 * @package CoursePress
 */
$course = coursepress_get_course();
$unit = coursepress_get_unit();
$student = coursepress_get_user();
$unit_progress = $student->get_unit_progress( $course->ID, $unit->ID );
$coursep = $student->get_completion_data( $course->ID );
$show_progress = $student->is_enrolled_at( $course->ID );
//error_log( print_r( $coursep, true ) );
get_header(); ?>

    <div class="coursepress-wrap course-unit">
        <div class="container">
            <div class="content-area">
                <header class="page-header">
                    <h1 class="page-title"><?php echo coursepress_get_course_title(); ?></h1>

                    <h2 class="entry-title course-title"><?php echo coursepress_get_unit_title(); ?></h2>

                    <?php if ( $show_progress ) { ?>
          						<div class="course-unit-progress">
          							<?php echo coursepress_progress_wheel( array(
          									'class' => 'per-unit-progress',
          									'data-value' => $unit_progress,
          									'data-size' => 62,
          								) );
          							?>
          						</div>
          					<?php } ?>

                    <?php coursepress_breadcrumb(); ?>

                </header>

                <div class="course-content-template">
                    <div class="course-structure course-structure-nav">
			            <?php echo coursepress_get_unit_structure(); ?>
                    </div>
                    <div class="course-content">
                      <?php echo coursepress_get_current_course_cycle(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php get_footer();
