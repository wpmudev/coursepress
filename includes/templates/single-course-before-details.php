<?php
the_excerpt();
?>

<div class="instructors-box">
    <?php
    $course = new Course( get_the_ID() );
    //Get instructors count for this course
    $instructors = do_shortcode( '[course_instructors count="true"]' );

    if ( $instructors > 0 ) {
        if ( $instructors >= 2 ) {
            ?>
            <h2><?php _e( 'About Instructors', 'cp' ); ?></h2>
            <?php
        } else {
            ?>
            <h2><?php _e( 'About Instructor', 'cp' ); ?></h2>
            <?php
        }
    }

    $course_language = $course->details->course_language;
    
    //List of instructors
    echo do_shortcode( '[course_instructors]' );
    ?>
</div><div class="devider"></div>

<div class="enroll-box">
    <div class="enroll-box-left">
        <div class="course-box">
            <?php echo do_shortcode( '[course_dates course_id="' . $course_details->ID . '"]' ); ?>
            <?php echo do_shortcode( '[course_enrollment_dates course_id="' . $course_details->ID . '"]' ); ?>
			<?php echo do_shortcode( '[course_class_size course_id="' . $course_details->ID . '"]' ); ?>
			<?php echo do_shortcode( '[course_enrollment_type course_id="' . $course_details->ID . '"]' ); ?>
			<?php echo do_shortcode( '[course_language course_id="' . $course_details->ID . '"]' ); ?>
        </div>
	</div>
    <div class="enroll-box-right">
        <div class="apply-box">
			<?php echo do_shortcode('[course_join_button]'); ?>
            <?php // echo do_shortcode( '[course_details field="button"]' ); ?>
        </div>
    </div>
</div>
<div class="devider"></div>