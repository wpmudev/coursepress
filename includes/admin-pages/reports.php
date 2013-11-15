<?php
global $coursepress;

$unit_module_main = new Unit_Module();
$page = $_GET['page'];
$s = (isset($_GET['s']) ? $_GET['s'] : '');

if (isset($_GET['action']) && $_GET['action'] == 'report') {
    $report_content = '<div>A content goes here!</div>';
    $coursepress->pdf_report($report_content);
}

if (isset($_POST['action']) && isset($_POST['users'])) {
    check_admin_referer('bulk-students');

    $action = $_POST['action'];
    foreach ($_POST['users'] as $user_value) {

        if (is_numeric($user_value)) {

            $student_id = (int) $user_value;
            $student = new Student($student_id);

            switch (addslashes($action)) {
                case 'delete':
                    if (current_user_can('coursepress_delete_students_cap')) {
                        $student->delete_student();
                        $message = __('Selected students has been removed successfully.', 'cp');
                    }
                    break;

                case 'unenroll':
                    if (current_user_can('coursepress_unenroll_students_cap')) {
                        $student->unenroll_from_all_courses();
                        $message = __('Selected students has been unenrolled from all courses successfully.', 'cp');
                    }
                    break;
            }
        }
    }
}

if (isset($_GET['page_num'])) {
    $page_num = $_GET['page_num'];
} else {
    $page_num = 1;
}

if (isset($_GET['s'])) {
    $usersearch = $_GET['s'];
} else {
    $usersearch = '';
}


// Query the users
$wp_user_search = new Student_Search($usersearch, $page_num);
?>
<div class="wrap nosubsub">
    <div class="icon32 icon32-posts-page" id="icon-edit-pages"><br></div>
    <h2><?php _e('Reports', 'cp'); ?></h2>

    <?php
    if (isset($message)) {
        ?>
        <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
        <?php
    }
    ?>

    <div class="tablenav">

        <div class="alignright actions new-actions">
            <form method="get" action="?page=<?php echo esc_attr($page); ?>" class="search-form">
                <p class="search-box">
                    <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />
                    <label class="screen-reader-text"><?php _e('Search Students', 'cp'); ?>:</label>
                    <input type="text" value="<?php echo esc_attr($s); ?>" name="s">
                    <input type="submit" class="button" value="<?php _e('Search Students', 'cp'); ?>">
                </p>
            </form>
        </div>

        <!--<form method="post" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">

        <?php wp_nonce_field('bulk-students'); ?>

            <div class="alignleft actions">
        <?php if (current_user_can('coursepress_unenroll_students_cap') || current_user_can('coursepress_delete_students_cap')) { ?>
                                        <select name="action">
                                            <option selected="selected" value=""><?php _e('Bulk Actions', 'cp'); ?></option>
            <?php if (current_user_can('coursepress_delete_students_cap')) { ?>
                                                                    <option value="delete"><?php _e('Delete', 'cp'); ?></option>
            <?php } ?>
            <?php if (current_user_can('coursepress_unenroll_students_cap')) { ?>
                                                                    <option value="unenroll"><?php _e('Unenroll from all courses', 'cp'); ?></option>
            <?php } ?>
                                        </select>
                                        <input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e('Apply', 'membership'); ?>" />
        <?php } ?>
            </div>

        </form>-->


        <br class="clear">

    </div><!--/tablenav-->

    <div class="tablenav">
        <form method="get" id="course-filter">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>" />
            <input type="hidden" name="page_num" value="<?php echo esc_attr($page_num); ?>" />
            <div class="alignleft actions">
                <select name="course_id" id="dynamic_courses">

                    <?php
                    $args = array(
                        'post_type' => 'course',
                        'post_status' => 'any',
                        'posts_per_page' => -1
                    );

                    $courses = get_posts($args);
                    $courses_with_students = 0;
                    $course_num = 0;
                    $first_course_id = 0;

                    foreach ($courses as $course) {
                        if ($course_num == 0) {
                            $first_course_id = $course->ID;
                        }

                        $course_obj = new Course($course->ID);
                        $course_object = $course_obj->get_course();
                        if ($course_obj->get_number_of_students() >= 1) {
                            $courses_with_students++;
                            ?>
                            <option value="<?php echo $course->ID; ?>" <?php echo ((isset($_GET['course_id']) && $_GET['course_id'] == $course->ID) ? 'selected="selected"' : ''); ?>><?php echo $course->post_title; ?></option>
                            <?php
                        }
                        $course_num++;
                    }

                    if ($courses_with_students == 0) {
                        ?>
                        <option value=""><?php _e('0 courses with enrolled students.', 'cp'); ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
                $current_course_id = 0;
                if (isset($_GET['course_id'])) {
                    $current_course_id = $_GET['course_id'];
                } else {
                    $current_course_id = $first_course_id;
                }
                ?>

                <?php
                if ($current_course_id !== 0) {//courses exists, at least one 
                    $course = new Course($current_course_id);
                    $course_units = $course->get_units();

                    if (count($course_units) >= 1) {

                        //search for students
                        if (isset($_GET['classes'])) {
                            $classes = $_GET['classes'];
                        } else {
                            $classes = 'all';
                        }
                        ?>
                        <select name="classes" id="dynamic_classes" name="dynamic_classes">
                            <option value="all" <?php selected($classes, 'all', true); ?>><?php _e('All Classes', 'cp'); ?></option>
                            <option value="" <?php selected($classes, '', true); ?>><?php _e('Default', 'cp'); ?></option>
                            <?php
                            $course_classes = get_post_meta($current_course_id, 'course_classes', true);
                            foreach ($course_classes as $course_class) {
                                ?>
                                <option value="<?php echo $course_class; ?>" <?php selected($classes, $course_class, true); ?>><?php echo $course_class; ?></option>
                                <?php
                            }
                            ?>
                        </select>

                        <?php
                    }
                }
                ?>

            </div>
        </form>
    </div><!--tablenav-->

    <?php
    $columns = array(
        "ID" => __('Student ID', 'cp'),
        "user_firstname" => __('First Name', 'cp'),
        "user_lastname" => __('Surname', 'cp'),
        //"latest_activity" => __('Latest Activity', 'cp'),
        "responses" => __('Responses', 'cp'),
        "avarage_grade" => __('Avarage Grade', 'cp'),
        "report" => __('Report', 'cp'),
    );

    $col_sizes = array(
        '8', '10', '10', '10', '10', '5'//, '15'
    );
    ?>
    <form method="post" id="generate-report">
        <table cellspacing="0" class="widefat fixed shadow-table">
            <thead>
                <tr>
                    <th style="" class="manage-column column-cb check-column" width="1%" id="cb" scope="col"><input type="checkbox"></th>
                    <?php
                    $n = 0;
                    foreach ($columns as $key => $col) {
                        ?>
                        <th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo $col_sizes[$n] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                        <?php
                        $n++;
                    }
                    ?>
                </tr>
            </thead>

            <tbody>
                <?php
                $style = '';

                //search for students
                if (isset($_GET['classes'])) {
                    $classes = $_GET['classes'];
                } else {
                    $classes = 'all';
                }

                if ($classes !== 'all') {
                    $args = array(
                        'meta_query' => array(
                            array(
                                'key' => 'enrolled_course_class_' . $current_course_id,
                                'value' => $classes,
                            ))
                    );
                } else {
                    $args = array(
                        'meta_query' => array(
                            array(
                                'key' => 'enrolled_course_class_' . $current_course_id
                            ))
                    );
                }

                $additional_url_args = array();
                $additional_url_args['course_id'] = $current_course_id;
                $additional_url_args['classes'] = urlencode($classes);

                $student_search = new Student_Search('', $page_num, array(), $args, $additional_url_args);

                foreach ($student_search->get_results() as $user) {

                    $user_object = new Student($user->ID);
                    $roles = $user_object->roles;
                    $role = array_shift($roles);

                    $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                    ?>
                    <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>
                        <th scope='row' class='check-column'>
                            <input type='checkbox' name='users[]' id='user_<?php echo $user_object->ID; ?>' value='<?php echo $user_object->ID; ?>' />
                        </th>
                        <td <?php echo $style; ?>><?php echo $user_object->ID; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->first_name; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->last_name; ?></td>

                        <td <?php echo $style; ?>><?php echo $user_object->get_number_of_responses($current_course_id); ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->get_avarage_response_grade($current_course_id) . '%'; ?></td>
                        <td <?php echo $style; ?>><a href="?page=reports&action=report&student_id=<?php echo $user_object->ID; ?>&course_id=<?php echo $current_course_id; ?>&unit_id=YYY" class="pdf">&nbsp;</a></td>
                    </tr>

                    <?php
                }
                ?>
                <?php
                if (count($wp_user_search->get_results()) == 0) {
                    ?>
                    <tr><td colspan="8"><div class="zero"><?php _e('No students found.', 'cp'); ?></div></td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <div class="tablenav">
            <div class="alignleft actions">
                <select name="units">
                    <option value="all" selected="selected"><?php _e('All Units') ?></option>
                    <option value="edit" class="hide-if-no-js">Edit</option>
                    <option value="trash">Move to Trash</option>
                </select>
                <?php submit_button('Generate Report', 'primary', 'generate_report_button', false); ?>
            </div>
            
            <div class="tablenav-pages"><?php $student_search->page_links(); ?></div>

        </div><!--/tablenav-->
    </form>



</div>