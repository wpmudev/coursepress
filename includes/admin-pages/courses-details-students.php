<?php
$course_id = $_GET['course_id'];
$course = new Course($course_id);

/* Invite a Student */
if (isset($_POST['invite_student'])) {
    check_admin_referer('student_invitation');
    if ((current_user_can('coursepress_invite_students_cap')) || (current_user_can('coursepress_invite_my_students_cap') && $course->details->post_author == get_current_user_id())) {
        $email_args['email_type'] = 'student_invitation';
        $email_args['course_id'] = $course_id;
        $email_args['student_first_name'] = $_POST['first_name'];
        $email_args['student_last_name'] = $_POST['last_name'];
        $email_args['student_email'] = $_POST['email'];
        $email_args['enroll_type'] = $course->details->enroll_type;
        if (is_email($_POST['email'])) {
            coursepress_send_email($email_args);
            //ob_start();
            wp_redirect('?page=course_details&tab=students&course_id=' . $course_id . '&ms=is');
            exit;
        } else {
            //ob_start();
            wp_redirect('?page=course_details&tab=students&course_id=' . $course_id . '&ems=wrong_email');
            exit;
        }
        //wp_redirect('?page=course_details&tab=students&course_id=' . $course_id);
        //exit;
    }
}

/* Enroll student or move to a different class */
if (isset($_POST['students']) && is_numeric($_POST['students'])) {
    check_admin_referer('student_details');
    $student = new Student($_POST['students']);
    $student->enroll_in_course($course_id, $_POST['class_name']);
    //ob_start();
    wp_redirect('?page=course_details&tab=students&course_id=' . $course_id . '&ms=as');
    exit;
}

/* Add new course class */
if (isset($_POST['add_student_class'])) {
    check_admin_referer('add_student_class');
    if ((current_user_can('coursepress_add_new_classes_cap')) || (current_user_can('coursepress_add_new_my_classes_cap') && $course->details->post_author == get_current_user_id())) {
        sort($_POST['course_classes']);
        $groups = $_POST['course_classes'];
        update_post_meta($course_id, 'course_classes', $groups);
    }
}

/* Delete a Class and Change student's group to Default */
if (isset($_GET['delete_class'])) {

    if ((current_user_can('coursepress_delete_classes_cap')) || (current_user_can('coursepress_delete_my_classes_cap') && $course->details->post_author == get_current_user_id())) {
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
    }
    //ob_start();
    wp_redirect('?page=course_details&tab=students&course_id=' . $course_id . '&ms=dc');
    exit;
}

$course_classes = get_post_meta($course_id, 'course_classes', true);


/* Un-enroll all students in the Class */
if (isset($_GET['unenroll_all'])) {

    if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) {
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
    }
    //ob_start();
    wp_redirect('?page=course_details&tab=students&course_id=' . $course_id . '&ms=usl');
    exit;
}

/* Un-enroll a Student from class */
if (isset($_GET['unenroll']) && is_numeric($_GET['unenroll'])) {
    if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) {
        $student = new Student($_GET['unenroll']);
        $student->unenroll_from_course($course_id);
    }
    //ob_start();
    wp_redirect('?page=course_details&tab=students&course_id=' . $course_id . '&ms=us');
    exit;
}

$columns = array(
    "ID" => __('Student ID', 'cp'),
    "user_firstname" => __('First Name', 'cp'),
    "user_lastname" => __('Surname', 'cp'),
    "group" => __('Group', 'cp'),
    "edit" => __('Edit', 'cp'),
);

$col_sizes = array(
    '8', '26', '26', '27', '6'
);

if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) {
    $columns["delete"] = __('Un-enroll', 'cp');
    $col_sizes[] = '12';
}
?>
<div id="students_accordion">
    <?php
    $search_args['meta_key'] = 'enrolled_course_group_' . $course_id;
    $search_args['meta_value'] = (isset($class) ? $class : '');

    $args = array(
        'meta_query' => array(
            array(
                'key' => 'enrolled_course_class_' . $course_id,
                'value' => '',
            ))
    );

    $wp_user_search = new WP_User_Query($args);
    ?>
    <div class="sidebar-name no-movecursor">
        <h3><?php _e('Default', 'cp'); ?> <span><?php echo (count($wp_user_search->get_results()) >= 1) ? '(' . count($wp_user_search->get_results()) . ')' : ''; ?></span></h3>
    </div>

    <?php
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
                            <td class="<?php echo $style . ' edit-button-student-td'; ?>"><a href="?page=students&action=view&student_id=<?php echo $user_object->ID; ?>" class="button button-units save-unit-button"><?php _e('Edit', 'cp'); ?></a></td>
                            <?php if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                                <td class="<?php echo $style . ' delete-button-student-td'; ?>">
                                    <?php if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                                        <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&unenroll=<?php echo $user_object->ID; ?>" onclick="return unenrollStudent();">
                                            <i class="fa fa-times-circle cp-move-icon remove-btn"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            <?php } ?>

                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

            <div class="additional_class_actions">
                <?php if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                    <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&unenroll_all=<?php echo urlencode((isset($class) ? $class : '')); ?>" onClick="return unenrollAllFromClass();" title="<?php _e('Un-enroll all students from the course', 'cp'); ?>"><?php _e('Un-enroll all students', 'cp'); ?></a>
                <?php } ?>
            </div>

            <div class="additional_class_actions_add_student">
                <?php if ((current_user_can('coursepress_add_move_students_cap')) || (current_user_can('coursepress_add_move_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                    <form name="add_new_student_to_class_<?php
                    echo (isset($class) ? $class : '');
                    ;
                    ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&ms=as" method="post">
                        <input type="hidden" name="class_name" value="" />
                        <input type="hidden" name="active_student_tab" value="0" /> 
                        <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                        <?php wp_nonce_field('student_details'); ?>
                    </form>
                <?php } ?>
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

            <div class="additional_class_actions"></div>

            <div class="additional_class_actions_add_student">
                <?php if ((current_user_can('coursepress_add_move_students_cap')) || (current_user_can('coursepress_add_move_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                    <form name="add_new_student_to_class_<?php echo (isset($class) ? $class : ''); ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&ms=as" method="post">
                        <input type="hidden" name="class_name" value="" />
                        <input type="hidden" name="active_student_tab" value="0" /> 
                        <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                        <?php wp_nonce_field('student_details'); ?>
                    </form>
                <?php } ?>
            </div>

        </div>
    <?php } ?>
    <?php
    if (!empty($course_classes)) {
        $course_num = 1;
        foreach ($course_classes as $class) {
            $search_args['meta_key'] = 'enrolled_course_group_' . $course_id;
            $search_args['meta_value'] = $class;

            $args = array(
                'meta_query' => array(
                    array(
                        'key' => 'enrolled_course_class_' . $course_id,
                        'value' => $class,
                    ))
            );

            $wp_user_search = new WP_User_Query($args);
            ?>
            <div class="sidebar-name no-movecursor" area-selected="true">
                <h3><?php echo (isset($class) ? $class : ''); ?> <span><?php echo (count($wp_user_search->get_results()) >= 1) ? '(' . count($wp_user_search->get_results()) . ')' : ''; ?></span></h3>
            </div>
            <?php
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
                                    <td class="<?php echo $style . ' edit-button-student-td'; ?>"><a href="?page=students&action=view&student_id=<?php echo $user_object->ID; ?>" class="button button-units save-unit-button"><?php _e('Edit', 'cp'); ?></a></td>
                                    <?php if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                                        <td class="<?php echo $style . ' delete-button-student-td'; ?>">
                                            <?php if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                                                <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&unenroll=<?php echo $user_object->ID; ?>" onclick="return unenrollStudent();">
                                                    <i class="fa fa-times-circle cp-move-icon remove-btn"></i>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    <?php } ?>

                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>


                    <div class="additional_class_actions">
                        <?php if ((current_user_can('coursepress_delete_classes_cap')) || (current_user_can('coursepress_delete_my_classes_cap') && $course->details->post_author == get_current_user_id())) { ?>
                            <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&delete_class=<?php echo urlencode((isset($class) ? $class : '')); ?>" onClick="return deleteClass();" title="<?php _e('Delete Class and move students to Default class', 'cp'); ?>"><?php _e('Delete Class', 'cp'); ?></a>
                        <?php } ?>
                        <?php if (((current_user_can('coursepress_delete_classes_cap')) || (current_user_can('coursepress_delete_my_classes_cap') && $course->details->post_author == get_current_user_id())) && (((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())))) { ?>
                            | 
                        <?php } ?>
                        <?php if ((current_user_can('coursepress_unenroll_students_cap')) || (current_user_can('coursepress_unenroll_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                            <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&unenroll_all=<?php echo urlencode((isset($class) ? $class : '')); ?>" onClick="return unenrollAllFromClass();" title="<?php _e('Un-enroll all students from the course', 'cp'); ?>"><?php _e('Un-enroll all students', 'cp'); ?></a>
                        <?php } ?>
                    </div>


                    <div class="additional_class_actions_add_student">
                        <?php if ((current_user_can('coursepress_add_move_students_cap')) || (current_user_can('coursepress_add_move_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                            <form name="add_new_student_to_class_<?php echo (isset($class) ? $class : ''); ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&ms=as" method="post">
                                <input type="hidden" name="class_name" value="<?php echo (isset($class) ? $class : ''); ?>" />
                                <input type="hidden" name="active_student_tab" value="<?php echo $course_num; ?>" /> 
                                <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                                <?php wp_nonce_field('student_details'); ?>
                            <?php } ?>
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
                        <?php if ((current_user_can('coursepress_delete_classes_cap')) || (current_user_can('coursepress_delete_my_classes_cap') && $course->details->post_author == get_current_user_id())) { ?>
                            <a href="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&delete_class=<?php echo urlencode($class); ?>" onClick="return deleteClass();" title="<?php _e('Delete Class', 'cp'); ?>"><?php _e('Delete Class', 'cp'); ?></a>
                        <?php } ?>
                    </div>


                    <div class="additional_class_actions_add_student">
                        <?php if ((current_user_can('coursepress_add_move_students_cap')) || (current_user_can('coursepress_add_move_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
                            <form name="add_new_student_to_class_<?php echo $class; ?>" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&ms=as" method="post">
                                <input type="hidden" name="class_name" value="<?php echo $class; ?>" />
                                <input type="hidden" name="active_student_tab" value="<?php echo $course_num; ?>" /> 
                                <?php coursepress_students_drop_down(); ?> <?php submit_button(__('Add Student', 'cp'), 'secondary', 'add_new_student', ''); ?>
                                <?php wp_nonce_field('student_details'); ?>
                            </form>
                        <?php } ?>
                    </div>

                </div>
                <?php
            }
            $course_num++;
        }
    }
    ?>
</div>

<form name="" method="post" action="?page=course_details&tab=students&course_id=<?php echo $course_id; ?>&ms=ac">
    <?php
    if (!empty($course_classes)) {
        foreach ($course_classes as $class) {
            ?>
            <input type="hidden" name="course_classes[]" value="<?php echo $class; ?>" />
            <!--<input type="hidden" name="active_student_tab" value="<?php echo $course_num; ?>" /> -->
            <?php
        }
    }
    wp_nonce_field('add_student_class');
    ?>

    <?php if ((current_user_can('coursepress_add_new_classes_cap')) || (current_user_can('coursepress_add_new_my_classes_cap') && $course->details->post_author == get_current_user_id())) { ?>
        <div class="add-student-class-area">
            <h2><?php _e('New Class', 'cp'); ?></h2>
            <label><?php _e('New Class name', 'cp'); ?>
                <input type="text" name="course_classes[]" value="" />
            </label>
            <?php submit_button(__('Add New Class', 'cp'), 'primary', 'add_student_class', ''); ?>
        </div>
    <?php } ?>

</form>

<?php
if ($course->details->enroll_type != 'manually') {//There shouldn't be invitations functionality if enrollment type is only Manually
    ?>
    <?php if ((current_user_can('coursepress_invite_students_cap')) || (current_user_can('coursepress_invite_my_students_cap') && $course->details->post_author == get_current_user_id())) { ?>
        <div class="invite_student_area">
            <form name="student_invitation" method="post">
                <?php wp_nonce_field('student_invitation'); ?>
                <h2><?php _e('Invite a Student', 'cp'); ?></h2>
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
            </form>
        </div>
    <?php } ?>
<?php } ?>


<?php /*
  if ((current_user_can('coursepress_send_bulk_students_email_cap')) || (current_user_can('coursepress_send_bulk_my_students_email_cap') && $course->details->post_author == get_current_user_id())) { ?>
  <div class="students_bulk_email_area">
  <form name="students_bulk_email" method="post">
  <?php wp_nonce_field('students_bulk_email'); ?>
  <h2><?php _e('Send an e-mail notification to students', 'cp'); ?></h2>

  <label class="email_subject"><?php _e('E-Mail Subject', 'cp'); ?>
  <input type="text" name="email_subject" value="" />
  </label>
  <label class="email_body"><?php _e('E-Mail Body', 'cp'); ?>
  <?php
  $args = array("textarea_name" => "email_body", "textarea_rows" => 3);
  wp_editor('', "email_body", $args);
  ?>
  </label><br />
  <?php submit_button(__('Send', 'cp'), 'primary', 'send_bulk_email_to_students', ''); ?>
  </form>
  </div>
  <?php } */ ?>
