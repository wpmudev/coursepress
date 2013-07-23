<?php
$course_id = $_GET['course_id'];

/* Enroll student or move to a different class */

if(isset($_POST['students']) && is_numeric($_POST['students'])){
    $student = new Student($_POST['students']);
    $student->enroll_in_course($course_id, $_POST['class_name']);
}

/* Add new course class */
if (isset($_POST['add_student_class'])) {
    sort($_POST['course_classes']);
    $groups = $_POST['course_classes'];
    update_post_meta($course_id, 'course_classes', $groups);
}

/* Delete a Class and Change student's group to Default */
if (isset($_GET['delete_class'])) {

    $old_class = urldecode($_GET['delete_class']);
    if ($old_class == 'Default') {
        $old_class = '';
    }

    $args = array(
        'meta_query' => array(
            array(
                'key' => 'enrolled_course_class_' . $course_id,
                'value' => $old_class,
            ))
    );

    $wp_user_search = new WP_User_Query($args);

    if ($wp_user_search->get_results()) {
        foreach ($wp_user_search->get_results() as $user) {
            $student = new Student($user->ID);
            $student->update_student_class($course_id, '');
        }
    }
    $course_classes = get_post_meta($course_id, 'course_classes', true);

    if (($key = array_search($old_class, $course_classes)) !== false) {
        unset($course_classes[$key]);
        update_post_meta($course_id, 'course_classes', $course_classes);
    }

    wp_redirect('?page=course_details&tab=students&course_id=' . $course_id);
}

$course_classes = get_post_meta($course_id, 'course_classes', true);


/* Un-enroll all students in the Class */
if (isset($_GET['unenroll_all'])) {

    $old_class = urldecode($_GET['unenroll_all']);
    if ($old_class == 'Default') {
        $old_class = '';
    }

    $args = array(
        'meta_query' => array(
            array(
                'key' => 'enrolled_course_class_' . $course_id,
                'value' => $old_class,
            ))
    );

    $wp_user_search = new WP_User_Query($args);

    if ($wp_user_search->get_results()) {
        foreach ($wp_user_search->get_results() as $user) {
            $student = new Student($user->ID);
            $student->unenroll_from_course($course_id, '');
        }
    }

    wp_redirect('?page=course_details&tab=students&course_id=' . $course_id);
}

/* Un-enroll a Student from class */
if (isset($_GET['unenroll']) && is_numeric($_GET['unenroll'])) {
    $student = new Student($_GET['unenroll']);
    $student->unenroll_from_course($course_id);
}

$columns = array(
    "ID" => __('Student ID', 'cp'),
    "user_firstname" => __('First Name', 'cp'),
    "user_lastname" => __('Surname', 'cp'),
    "group" => __('Group', 'cp'),
    "edit" => __('Edit', 'cp'),
    "delete" => __('Un-enroll', 'cp'),
);

$col_sizes = array(
    '8', '26', '26', '27', '6', '12'
);
?>
<div id="students_accordion">

    <div class="sidebar-name no-movecursor">
        <h3><?php _e('Default', 'cp'); ?></h3>
    </div>

    <?php
    $search_args['meta_key'] = 'enrolled_course_group_' . $course_id;
    $search_args['meta_value'] = $class;

    $args = array(
        'meta_query' => array(
            array(
                'key' => 'enrolled_course_class_' . $course_id,
                'value' => '',
            ))
    );
    $wp_user_search = new WP_User_Query($args);

    if ($wp_user_search->get_results()) {
        ?>
        <div>
            <table cellspacing="0" class="widefat">
                <thead>
                    <tr>
                        <?php
                        $n = 0;
                        foreach ($columns as $key => $col) {
                            ?>
                            <th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col" width="<?php echo $col_sizes[$n] . '%'; ?>"><?php echo $col; ?></th>
                            <?php
                            $n++;
                        }
                        ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $style = '';

                    foreach ($wp_user_search->get_results() as $user) {

                        $user_object = new Student($user->ID);
                        $roles = $user_object->roles;
                        $role = array_shift($roles);

                        $style = ( 'alternate' == $style ) ? '' : 'alternate';
                        ?>
                        <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>

                            <td class="<?php echo $style; ?>"><?php echo $user_object->ID; ?></td>
                            <td class="<?php echo $style; ?>"><?php echo $user_object->first_name; ?></td>
                            <td class="<?php echo $style; ?>"><?php echo $user_object->last_name; ?></td>
                            <td class="<?php echo $style; ?>"><?php echo ($user_object->{'enrolled_course_group_' . $course_id} == '' ? __('Default', 'cp') : $user_object->{'enrolled_course_group_' . $course_id}); ?></td>
                            <td class="<?php echo $style . ' edit-button-student-td'; ?>"><a href="?page=students&action=view&student_id=<?php echo $user_object->ID; ?>" class="button button-settings"><?php _e('Edit', 'cp'); ?></a></td>
                            <td class="<?php echo $style . ' delete-button-student-td'; ?>"><a href="?page=course_details&tab=students&course_id=55&unenroll=<?php echo $user_object->ID; ?>" onclick="return unenrollStudent();" class="remove-button-student"></a></td>

                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

            <div class="additional_class_actions">
                <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&unenroll_all=<?php echo urlencode($class); ?>" onClick="return unenrollAllFromClass();" title="<?php _e('Un-enroll all students from the course', 'cp'); ?>"><?php _e('Un-enroll all students', 'cp'); ?></a>
            </div>

            <div class="additional_class_actions_add_student">
                <form name="add_new_student_to_class_<?php echo $class; ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>" method="post">
                    <input type="hidden" name="class_name" value="" />
                    <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                </form>
            </div>
        </div>
    <?php } else { ?>
        <div>
            <table cellspacing="0" class="widefat">
                <tr>
                    <td>
                        <div class="zero-students"><?php _e('0 Students in this class', 'cp'); ?></div>
                    </td>
                </tr>
            </table>
            <div class="additional_class_actions">
                <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&delete_class=default" onClick="return deleteClass();" title="<?php _e('Delete Class and move students to Default class', 'cp'); ?>"><?php _e('Delete Class', 'cp'); ?></a>
            </div>

            <div class="additional_class_actions_add_student">
                <form name="add_new_student_to_class_<?php echo $class; ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>" method="post">
                    <input type="hidden" name="class_name" value="" />
                    <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                </form>
            </div>
        </div>
    <?php } ?>
    <?php
    if (!empty($course_classes)) {
        foreach ($course_classes as $class) {
            ?>
            <div class="sidebar-name no-movecursor" area-selected="true">
                <h3><?php echo $class; ?></h3>
            </div>
            <?php
            $search_args['meta_key'] = 'enrolled_course_group_' . $course_id;
            $search_args['meta_value'] = $class;
            //$wp_user_search = new Student_Search($usersearch, $userspage, $search_args);

            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'enrolled_course_class_' . $course_id,
                        'value' => $class,
                    ))
            );
            $wp_user_search = new WP_User_Query($args);

            if ($wp_user_search->get_results()) {
                ?>

                <div>
                    <table cellspacing="0" class="widefat">
                        <thead>
                            <tr>
                                <?php
                                $n = 0;
                                foreach ($columns as $key => $col) {
                                    ?>
                                    <th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col" width="<?php echo $col_sizes[$n] . '%'; ?>"><?php echo $col; ?></th>
                                    <?php
                                    $n++;
                                }
                                ?>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $style = '';

                            foreach ($wp_user_search->get_results() as $user) {

                                $user_object = new Student($user->ID);
                                $roles = $user_object->roles;
                                $role = array_shift($roles);

                                $style = ( 'alternate' == $style ) ? '' : 'alternate';
                                ?>
                                <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>

                                    <td class="<?php echo $style; ?>"><?php echo $user_object->ID; ?></td>
                                    <td class="<?php echo $style; ?>"><?php echo $user_object->first_name; ?></td>
                                    <td class="<?php echo $style; ?>"><?php echo $user_object->last_name; ?></td>
                                    <td class="<?php echo $style; ?>"><?php echo ($user_object->{'enrolled_course_group_' . $course_id} == '' ? __('Default', 'cp') : $user_object->{'enrolled_course_group_' . $course_id}); ?></td>
                                    <td class="<?php echo $style . ' edit-button-student-td'; ?>"><a href="?page=students&action=view&student_id=<?php echo $user_object->ID; ?>" class="button button-settings"><?php _e('Edit', 'cp'); ?></a></td>
                                    <td class="<?php echo $style . ' delete-button-student-td'; ?>"><a href="?page=course_details&tab=students&course_id=55&unenroll=<?php echo $user_object->ID; ?>" onclick="return unenrollStudent();" class="remove-button-student"></a></td>

                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>

                    <div class="additional_class_actions">
                        <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&delete_class=<?php echo urlencode($class); ?>" onClick="return deleteClass();" title="<?php _e('Delete Class and move students to Default class', 'cp'); ?>"><?php _e('Delete Class', 'cp'); ?></a> | <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&delete_class=<?php echo urlencode($class); ?>" onClick="return unenrollAllFromClass();" title="<?php _e('Un-enroll all students from the course', 'cp'); ?>"><?php _e('Un-enroll all students', 'cp'); ?></a>
                    </div>

                    <div class="additional_class_actions_add_student">
                        <form name="add_new_student_to_class_<?php echo $class; ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>" method="post">
                            <input type="hidden" name="class_name" value="<?php echo $class; ?>" />
                            <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                        </form>
                    </div>

                </div>
                <?php
            } else {
                ?>
                <div>
                    <table cellspacing="0" class="widefat">
                        <tr>
                            <td>
                                <div class="zero-students"><?php _e('0 Students in this class', 'cp'); ?></div>
                            </td>
                        </tr>
                    </table>
                    <div class="additional_class_actions">
                        <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&delete_class=<?php echo urlencode($class); ?>" onClick="return deleteClass();" title="<?php _e('Delete Class', 'cp'); ?>"><?php _e('Delete Class', 'cp'); ?></a>
                    </div>

                    <div class="additional_class_actions_add_student">
                        <form name="add_new_student_to_class_<?php echo $class; ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>" method="post">
                            <input type="hidden" name="class_name" value="<?php echo $class; ?>" />
                            <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                        </form>
                    </div>
                </div>
                <?php
            }
        }
    }
    ?>
</div>

<form name="" method="post">
    <?php
    if (!empty($course_classes)) {
        foreach ($course_classes as $class) {
            ?>
            <input type="hidden" name="course_classes[]" value="<?php echo $class; ?>" />
            <?php
        }
    }
    wp_nonce_field('add-new-student-class');
    ?>

    <div class="add-student-class-area">
        <h2><?php _e('New Class', 'cp'); ?></h2>
        <label><?php _e('New Class name', 'cp'); ?>
            <input type="text" name="course_classes[]" value="" />
        </label>
        <?php submit_button(__('Add New Class', 'cp'), 'primary', 'add_student_class', ''); ?>
    </div>

    <div class="invite_student_area">
        <h2><?php _e('Invite Student', 'cp'); ?></h2>
        <label><?php _e('First Name', 'cp'); ?>
            <input type="text" name="first_name" value="" />
        </label>

        <label><?php _e('Last Name', 'cp'); ?>
            <input type="text" name="last_name" value="" />
        </label>

        <label><?php _e('E-Mail', 'cp'); ?>
            <input type="text" name="email" value="" />
        </label>
        <?php submit_button(__('Invite', 'cp'), 'primary', 'invite_student', ''); ?>
    </div>

</form>