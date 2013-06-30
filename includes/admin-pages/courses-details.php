<?php
global $action, $page;
wp_reset_vars(array('action', 'page'));
?>

<div class="wrap nosubsub">
    <div class="icon32" id="icon-themes"><br></div>
    <h2><?php _e('New Course', 'cp'); ?></h2>

    <?php
    $tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';
    if (empty($tab)) {
        $tab = 'overview';
    }
    ?>

    <?php
    $menus = array();
    $menus['overview'] = __('Course Overview', 'cp');
    $menus['units'] = __('Units', 'cp');
    $menus['students'] = __('Students', 'cp');
    $menus = apply_filters('coursepress_course_new_menus', $menus);
    ?>

    <h3 class="nav-tab-wrapper">
        <?php
        foreach ($menus as $key => $menu) {
            ?>
            <a class="nav-tab<?php if ($tab == $key)
            echo ' nav-tab-active'; ?>" href="admin.php?page=<?php echo $page; ?>&amp;tab=<?php echo $key; ?>"><?php echo $menu; ?></a>
               <?php
           }
           ?>
           <?php
           if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
               $course = new Course($_GET['course_id']);
               if ($course->can_show_permalink()) {
                   ?>
                <a class="view-course-link" href="<?php echo get_permalink($_GET['course_id']); ?>" target="_new">View Course</a>
            <?php }
        } ?>
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
