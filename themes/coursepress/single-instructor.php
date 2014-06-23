<?php
/**
 * The Template for displaying instructor profile.
 *
 * @package CoursePress
 */
get_header( );

$user = $vars['user'];//get user info from the CoursePress plugin

$instructor = new Instructor( $user->ID );
$assigned_courses = $instructor->get_assigned_courses_ids( 'publish' );
?>

<div id="primary" class="content-area content-instructor-profile">
    <main id="main" class="site-main" role="main">
        <h1 class="h1-instructor-title"><?php echo $instructor->display_name;?></h1>
        <?php
        echo do_shortcode( '[course_instructor_avatar instructor_id="' . $user->ID . '" thumb_size="235" class="instructor_avatar_full"]' );
        echo get_user_meta( $user->ID, 'description', true );
        ?>

        <h2 class="h2-instructor-bio"><?php _e( 'Courses', 'cp' ); ?></h2>

        <?php
        foreach ( $assigned_courses as $course_id ) {

            $course = new Course( $course_id );
            $course_details = $course->get_course( );

            if ( $course_details ) {
                ?>

                <div class="course">

                    <div class="enroll-box">
                        <h3><a href="<?php echo $course->get_permalink( ); ?>"><?php echo $course_details->post_title; ?></a></h3>
                        <div class="enroll-box-left">
                            <div class="course-box">
                                <span class="strong"><?php _e( 'Course Dates: ', 'cp' ); ?></span><?php echo do_shortcode( '[course_details field="course_start_date" course_id="' . $course_details->ID . '"]' ) . ' - ' . do_shortcode( '[course_details field="course_end_date" course_id="' . $course_details->ID . '"]' ); ?><br />
                                <span class="strong"><?php _e( 'Enrollment Dates: ', 'cp' ); ?></span><?php echo do_shortcode( '[course_details field="enrollment_start_date" course_id="' . $course_details->ID . '"]' ) . ' - ' . do_shortcode( '[course_details field="enrollment_end_date" course_id="' . $course_details->ID . '"]' ); ?><br />
                                <span class="strong"><?php _e( 'Class Size: ', 'cp' ); ?></span><?php echo do_shortcode( '[course_details field="class_size" course_id="' . $course_details->ID . '"]' ); ?><br />
                                <span class="strong"><?php _e( 'Price: ', 'cp' ); ?></span><?php echo do_shortcode( '[course_details field="price" course_id="' . $course_details->ID . '"]' ); ?>
                            </div></div>

                        <div class="enroll-box-right">
                            <form name="enrollment-process" method="post" action="<?php echo trailingslashit( site_url( ) . '/' . get_option( 'enrollment_process_slug', 'enrollment-process' ) ); ?>">
                                <div class="apply-box">
        <?php echo do_shortcode( '[course_details field="button" course_id="' . $course_details->ID . '"]' ); ?>
                                </div>
                            </form>
                        </div>

                    </div>

                </div><div class="devider"></div>

        <?php
    }
}
if ( count( $assigned_courses ) == 0 ) {
    _e( 'The Instructor does not have any courses assigned yet.', 'cp' );
}
?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php //get_sidebar( 'footer' ); ?>
<?php get_footer( ); ?>