<?php
require($this->plugin_dir . 'includes/classes/class.course.php');

$course = new Course(get_the_ID());
$course_details = $course->get_course();
?>

<?php
the_excerpt();
?>

<div class="instructors-box">
    <?php
    $instructors = $course->get_course_instructors();

    if (count($instructors >= 0)) {
        if (count($instructors >= 1)) {
            ?>
            <h2><?php _e('About Instructors', 'cp'); ?></h2>
            <?php
        } else {
            ?>
            <h2><?php _e('About Instructor', 'cp'); ?></h2>
            <?php
        }
    }

    foreach ($instructors as $instructor) {
        $avatar_url = preg_match('@src="([^"]+)"@', get_avatar($instructor->ID, 80), $match);
        $avatar_url = $match[1];
        ?>
        <div class="instructor">
            <div class="small-circle-profile-image" style="background: url(<?php echo $avatar_url; ?>);"></div>
            <div class="instructor-name"><?php echo $instructor->display_name; ?></div>
        </div>
        <?php
    }
    ?>
</div><br clear="all" />