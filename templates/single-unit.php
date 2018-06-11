<?php
/**
 * The template use for course unit.
 *
 * @since 3.0
 * @package CoursePress
 */
$course        = coursepress_get_course();
$unit          = coursepress_get_unit();
$student       = coursepress_get_user();
$unit_progress = $student->get_unit_progress( $course->ID, $unit->ID );
$coursep       = $student->get_completion_data( $course->ID );
get_header(); ?>
    <div class="coursepress-wrap course-unit">
        <div class="container">
            <div class="content-area">
                <header class="page-header">
<?php
/**
 * To override course submenu template to your theme or a child-theme,
 * create a template `course-submenu.php` and it will be loaded instead.
 *
 * @since 3.0
 */
coursepress_get_template( 'course', 'submenu' );
coursepress_breadcrumb();
?>
                    <div class="course-after-title">
                    <h2 class="entry-title course-title"><?php echo coursepress_get_unit_title(); ?></h2>
                    </div>
                </header>
                <div class="course-content-template">
                    <div class="course-structure course-structure-nav">
                        <?php echo coursepress_get_unit_structure(); ?>
                    </div>
                    <div class="course-content">
<?php
$args = array(
	'navigation' => 'top',
	'container_next' => 'span',
	'container_previous' => 'span',
	'navigation_separator' => '',
	'previous' => __( 'Prev', 'cp' ),
);
echo coursepress_get_current_course_cycle( $args );
?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();
