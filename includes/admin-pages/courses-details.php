<?php
global $action, $page;
wp_reset_vars(array('action', 'page'));

$course_id = '';
if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
}

$course = new Course($course_id);
$units = $course->get_units();
$students_count = $course->get_number_of_students();

?>

<div class="wrap nosubsub">
    <div class="icon32" id="icon-themes"><br></div>
    <?php
    $tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';
    if (empty($tab)) {
        $tab = 'overview';
    }
    ?>

    <h2><?php
    if ($course_id == '') {
        _e('New Course', 'cp');
    }
    if ($course_id != '') {
        _e('Course', 'cp');
    }
    if ($tab != 'overview') {
        echo ' » ' . $course->details->post_title . ' » ' . ucfirst($tab);
    } else {
        echo ' » ' . $course->details->post_title;
    }
    ?>
    </h2>

    <?php
    $message['ca'] = __('New Course added successfully!', 'cp');
    $message['cu'] = __('Course updated successfully.', 'cp');
    $message['usc'] = __('Unit status changed successfully', 'cp');
    $message['ud'] = __('Unit deleted successfully', 'cp');
    $message['ua'] = __('New Unit added successfully!', 'cp');
    $message['uu'] = __('Unit updated successfully.', 'cp');
    $message['as'] = __('Student added to the class successfully.', 'cp');
    $message['ac'] = __('New class has been added successfully.', 'cp');
    $message['dc'] = __('Selected class has been deleted successfully.', 'cp');
    $message['us'] = __('Selected student has been unenrolled successfully from the course.', 'cp');
    $message['usl'] = __('Selected students has been unenrolled successfully from the course.', 'cp');
    $message['is'] = __('Invitation sent sucessfully.', 'cp');

    if (isset($_GET['unit_id']) && isset($_GET['new_status'])) {
        $_GET['ms'] = 'usc';
    }

    if (isset($_GET['unit_id']) && isset($_GET['action']) && $_GET['action'] == 'delete_unit') {
        $_GET['ms'] = 'ud';
    }



    $ms = $_GET['ms'];

    if (isset($ms)) {
        ?>
        <div id="message" class="updated fade"><p><?php echo $message[$ms]; ?></p></div>
        <?php
    }
    ?>

    <?php
    $menus = array();
    $menus['overview'] = __('Course Overview', 'cp');
    $menus['units'] = __('Units', 'cp') . (count($units) >= 1 ? ' (' . count($units) . ')' : '');
    $menus['students'] = __('Students', 'cp'). ($students_count >= 1 ? ' (' . $students_count . ')' : '');
    $menus = apply_filters('coursepress_course_new_menus', $menus);
    ?>

    <h3 class="nav-tab-wrapper">
        <?php
        foreach ($menus as $key => $menu) {
            if ($key == 'overview' || ($key != 'overview' && $course_id != '')) {
                ?>
                <a class="nav-tab<?php
                   if ($tab == $key)
                       echo ' nav-tab-active';
                   ?>" href="admin.php?page=<?php echo $page; ?>&amp;tab=<?php echo $key; ?>&amp;course_id=<?php echo $course_id; ?>"><?php echo $menu; ?></a>
                   <?php
               }
           }
           ?>
           <?php
           if ($course_id != '') {
               $course = new Course($course_id);
               if ($course->can_show_permalink()) {
                   ?>
                <a class="view-course-link" href="<?php echo get_permalink($course_id); ?>" target="_new">View Course</a>
        <?php
        }
    }
    ?>
    </h3>

    <?php
    switch ($tab) {

        case 'overview': $this->show_courses_details_overview();
            break;

        case 'units': $this->show_courses_details_units();
            break;

        case 'students': $this->show_courses_details_students();
            break;

        default: do_action('coursepress_courses_details_menu_' . $tab);
            break;
    }
    ?>

</div>
