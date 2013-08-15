<?php
$page = $_GET['page'];
$s = (isset($_GET['s']) ? $_GET['s'] : '');

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

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    $student = new Student($_GET['student_id']);
    $student->delete_student();
    $message = __('Selected student has been removed successfully.', 'cp');
}

if (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'view') && isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    include('student-profile.php');
}else if(isset($_GET['action']) && ($_GET['action'] == 'add_new')){
    include('student-add.php');
}else {
// Query the users
    $wp_user_search = new Student_Search($usersearch, $page_num);
    ?>
    <div class="wrap nosubsub">
        <div class="icon32" id="icon-users"><br></div>
        <h2><?php _e('Students', 'cp'); ?><?php if (current_user_can('administrator')) { ?><a class="add-new-h2" href="user-new.php"><?php _e('Add New', 'cp'); ?></a><?php } ?><?php if (current_user_can('coursepress_add_new_students_cap') && !current_user_can('administrator')) { ?><a class="add-new-h2" href="?page=students&action=add_new"><?php _e('Add New', 'cp'); ?></a><?php } ?></h2>

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

            <form method="post" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">

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


                <br class="clear">

                </div><!--/tablenav-->

                <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />

                <?php
                $columns = array(
                    "ID" => __('Student ID', 'cp'),
                    "user_firstname" => __('First Name', 'cp'),
                    "user_lastname" => __('Surname', 'cp'),
                    "registration_date" => __('Registered', 'cp'),
                    "courses" => __('Courses', 'cp'),
                    "edit" => __('Profile', 'cp'),
                );

                $col_sizes = array(
                    '8', '15', '15', '20', '10', '7'
                );

                if (current_user_can('coursepress_delete_students_cap')) {
                    $columns["delete"] = __('Delete', 'cp');
                    $col_sizes[] = '5';
                }
                ?>

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

                        foreach ($wp_user_search->get_results() as $user) {

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
                                <td <?php echo $style; ?>><?php echo $user_object->user_registered; ?></td>
                                <td <?php echo $style; ?>><?php echo $user_object->courses_number; ?></td>
                                <td <?php echo $style; ?> style="padding-top:9px; padding-right:15px;"><a href="?page=students&action=view&student_id=<?php echo $user_object->ID; ?>" class="button button-settings"><?php _e('View', 'cp'); ?></a></td>
                                <?php if (current_user_can('coursepress_delete_students_cap')) { ?>
                                    <td <?php echo $style; ?> style="padding-top:13px;"><a href="?page=students&action=delete&student_id=<?php echo $user_object->ID; ?>" onclick="return removeStudent();" class="remove-button">&nbsp;</a></td>
                                <?php } ?>
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
                    <div class="tablenav-pages"><?php $wp_user_search->page_links(); ?></div>
                </div><!--/tablenav-->

            </form>

        </div>

    <?php } ?>