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
get_header(); ?>
    <div class="coursepress-wrap course-unit">
        <div class="">
            <div class="content-area">
                <header class="page-header">
                    <h3 class="course-title course-title-4"><span itemprop="name"><?php echo esc_html( coursepress_get_course_title() ); ?></span></h3>
                    <?php
                    /**
                     * To override course submenu template to your theme or a child-theme,
                     * create a template `course-submenu.php` and it will be loaded instead.
                     *
                     * @since 3.0
                     */
                    coursepress_get_template( 'course', 'submenu' );
					?>
                    <div class="course-after-title">
                    <h2 class="entry-title course-title"><?php echo esc_html( coursepress_get_unit_title() ); ?></h2>

                    <?php if ( $show_progress ) { ?>
          						<div class="course-unit-progress">
          							<?php
										$wheel = coursepress_progress_wheel( array(
          									'class' => 'per-unit-progress',
          									'data-value' => $unit_progress,
          									'data-size' => 62,
          								) );
										echo esc_html( $wheel );
          							?>
          						</div>
          					<?php } ?>

                    <?php coursepress_breadcrumb(); ?>

                  </div>

                </header>

                <div class="course-content-template">
                    <div class="course-structure course-structure-nav">
			            <?php echo esc_html( coursepress_get_unit_structure() ); ?>
                    </div>
                    <div class="course-content">
                      <?php echo esc_html( coursepress_get_current_course_cycle() ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();
