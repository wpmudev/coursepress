<?php
/**
 * The Template for displaying instructor profile.
 *
 * @package CoursePress
 */
get_header();

$user = $vars['user']; //get user info from the CoursePress plugin

$instructor = new Instructor($user->ID);
$assigned_courses = $instructor->get_assigned_courses_ids('publish');
?>

<div id="primary" class="content-area content-instructor-profile">
    <main id="main" class="site-main" role="main">
        <h1 class="h1-instructor-title"><?php echo $instructor->display_name; ?></h1>
        <?php
        // Avatar
	echo do_shortcode( '[course_instructor_avatar instructor_id="' . $user->ID . '" thumb_size="235" class="instructor_avatar_full"]' );
        
        // Bio
	echo get_user_meta( $user->ID, 'description', true );
        ?>

        <h2 class="h2-instructor-bio"><?php _e('Courses', 'cp'); ?></h2>

        <?php
        // Course List
        echo do_shortcode('[course_list instructor="' . $user->ID . '" class="course" left_class="enroll-box-left" right_class="enroll-box-right" course_class="enroll-box" title_link="yes"]');
        ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php //get_sidebar( 'footer' ); ?>
<?php get_footer(); ?>