<?php
if (isset($_GET['quick_setup'])) {
    include('quick-setup.php');
} else {
    if(isset($_GET['s'])){
        $s = $_GET['s'];
    }else{
        $s = '';
    }
    
    $page = $_GET['page'];

    if (isset($_POST['action']) && isset($_POST['courses'])) {
        check_admin_referer('bulk-courses');

        $action = $_POST['action'];

        foreach ($_POST['courses'] as $course_value) {
            if (is_numeric($course_value)) {
                $course_id = (int) $course_value;
                $course = new Course($course_id);
                $course_object = $course->get_course();

                switch (addslashes($action)) {
                    case 'publish':
                        if (current_user_can('coursepress_change_course_status_cap') || (current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id())) {
                            $course->change_status('publish');
                            $message = __('Selected courses have been published successfully.', 'cp');
                        } else {
                            $message = __("You don't have right persmissions to change course status.", 'cp');
                        }
                        break;

                    case 'unpublish':
                        if (current_user_can('coursepress_change_course_status_cap') || (current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id())) {
                            $course->change_status('private');
                            $message = __('Selected courses have been unpublished successfully.', 'cp');
                        } else {
                            $message = __("You don't have right persmissions to change course status.", 'cp');
                        }
                        break;

                    case 'delete':
                        if (current_user_can('coursepress_delete_course_cap') || (current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id())) {
                            $course->delete_course();
                            $message = __('Selected courses have been deleted successfully.', 'cp');
                        } else {
                            $message = __("You don't have right persmissions to delete the course.", 'cp');
                        }
                        break;
                }
            }
        }
    }

// Query the courses
    if (isset($_GET['page_num'])) {
        $page_num = $_GET['page_num'];
    } else {
        $page_num = 1;
    }

    if (isset($_GET['s'])) {
        $coursesearch = $_GET['s'];
    } else {
        $coursesearch = '';
    }

    $wp_course_search = new Course_Search($coursesearch, $page_num);

    if (isset($_GET['course_id'])) {
        $course = new Course($_GET['course_id']);
    }

    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
        $course_object = $course->get_course();
        if (current_user_can('coursepress_delete_course_cap') || (current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id())) {
            $course->delete_course($force_delete = true);
            $message = __('Selected course has been deleted successfully.', 'cp');
        } else {
            $message = __("You don't have right persmissions to delete the course.", 'cp');
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == 'change_status' && isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
        $course->change_status($_GET['new_status']);
        $message = __('Status for the selected course has been changed successfully.', 'cp');
    }
    ?>
    <div class="wrap nosubsub">
        <div class="icon32" id="icon-themes"><br></div>
        <h2><?php _e('Courses', 'cp'); ?><?php if (current_user_can('coursepress_create_course_cap')) { ?><a class="add-new-h2" href="admin.php?page=course_details"><?php _e('Add New', 'cp'); ?></a><?php } ?></h2>

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
                        <label class="screen-reader-text"><?php _e('Search Courses', 'cp'); ?>:</label>
                        <input type="text" value="<?php echo esc_attr($s); ?>" name="s">
                        <input type="submit" class="button" value="<?php _e('Search Courses', 'cp'); ?>">
                    </p>
                </form>
            </div><!--/alignright-->

            <form method="post" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">

                <?php if (current_user_can('coursepress_change_course_status_cap') || current_user_can('coursepress_delete_course_cap')) { ?>
                    <div class="alignleft actions">
                        <select name="action">
                            <option selected="selected" value=""><?php _e('Bulk Actions', 'cp'); ?></option>
                            <?php if (current_user_can('coursepress_change_course_status_cap')) { ?>
                                <option value="publish"><?php _e('Publish', 'cp'); ?></option>
                                <option value="unpublish"><?php _e('Unpublish', 'cp'); ?></option>
                            <?php } ?>
                            <?php if (current_user_can('coursepress_delete_course_cap')) { ?>
                                <option value="delete"><?php _e('Delete', 'cp'); ?></option>
                            <?php } ?>
                        </select>
                        <input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e('Apply', 'cp'); ?>" />
                    </div>
                <?php } ?>


                <br class="clear">

                </div><!--/tablenav-->


                <?php
                wp_nonce_field('bulk-courses');

                $columns = array(
                    "course" => __('Course', 'cp'),
                    "students" => __('Students', 'cp'),
                    "status" => __('Status', 'cp'),
                    "actions" => __('Actions', 'cp'),
                );


                $col_sizes = array(
                    '3', '65', '5', '10', '10'
                );

                if (current_user_can('coursepress_delete_course_cap') || (current_user_can('coursepress_delete_my_course_cap'))) {
                    $columns["remove"] = __('Remove', 'cp');
                    $col_sizes[] = '7';
                }
                ?>

                <table cellspacing="0" class="widefat shadow-table unit-control-buttons">
                    <thead>
                        <tr>
                            <th style="" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php echo $col_sizes[0] . '%'; ?>"><input type="checkbox"></th>
                            <?php
                            $n = 1;
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

                        foreach ($wp_course_search->get_results() as $course) {

                            $course_obj = new Course($course->ID);
                            $course_object = $course_obj->get_course();

                            $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                            
                            unit-control-buttons
                            ?>
                            <tr id='user-<?php echo $course_object->ID; ?>' <?php echo $style; ?>>
                                <th scope='row' class='check-column'>
                                    <input type='checkbox' name='courses[]' id='user_<?php echo $course_object->ID; ?>' class='' value='<?php echo $course_object->ID; ?>' />
                                </th>
                                <td <?php echo $style; ?>><a href="?page=course_details&course_id=<?php echo $course_object->ID; ?>"><strong><?php echo $course_object->post_title; ?></strong></a><br />
                                    <div class="course_excerpt"><?php echo get_the_course_excerpt($course_object->ID, 55); ?></div>
                                    <div class="row-actions">
                                        <span class="edit_course"><a href="?page=course_details&course_id=<?php echo $course_object->ID; ?>"><?php _e('Edit', 'cp'); ?></a> | </span>
                                        <?php if (current_user_can('coursepress_delete_course_cap') || (current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id())) { ?>
                                            <span class="course_units"><a href="?page=course_details&tab=units&course_id=<?php echo $course_object->ID; ?>"><?php _e('Units', 'cp'); ?></a> | </span>
                                        <?php } ?>
                                        <span class="course_students"><a href="?page=course_details&tab=students&course_id=<?php echo $course_object->ID; ?>"><?php _e('Students', 'cp'); ?></a> | </span>
                                        <?php if (current_user_can('coursepress_change_course_status_cap') || (current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id())) { ?>
                                            <span class="course_publish_unpublish"><a href="?page=courses&course_id=<?php echo $course_object->ID; ?>&action=change_status&new_status=<?php echo ($course_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>"><?php ($course_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a> | </span>
                                        <?php } ?>
                                        <?php if (current_user_can('coursepress_delete_course_cap') || (current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id())) { ?>
                                            <span class="course_remove"><a href="?page=courses&action=delete&course_id=<?php echo $course_object->ID; ?>" onClick="return removeCourse();"><?php _e('Delete', 'cp'); ?></a> | </span>
                                        <?php } ?>
                                        <span class="view_course"><a href="<?php echo get_permalink($course->ID); ?>" rel="permalink"><?php _e('View Course', 'cp') ?></a><?php if (current_user_can('coursepress_view_all_units_cap') || $course_object->post_author == get_current_user_id()) { ?> | <?php } ?></span>
                                        <?php if (current_user_can('coursepress_view_all_units_cap') || $course_object->post_author == get_current_user_id()) { ?>
                                            <span class="units"><a href="<?php echo get_permalink($course->ID); ?>units/" rel="permalink"><?php _e('View Units', 'cp') ?></a></span>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td class="center" <?php echo $style; ?>><a href="?page=course_details&tab=students&course_id=<?php echo $course_object->ID; ?>"><?php echo $course_obj->get_number_of_students(); ?></a></td>
                                <td <?php echo $style; ?>><?php echo ($course_object->post_status == 'publish') ? ucfirst($course_object->post_status) . 'ed' : ucfirst($course_object->post_status); ?></td>
                                <td <?php echo $style; ?>>
                                    <a href="?page=course_details&course_id=<?php echo $course_object->ID; ?>" class="button button-settings"><?php _e('Settings', 'cp'); ?></a>

                                    <?php if (current_user_can('coursepress_view_all_units_cap') || $course_object->post_author == get_current_user_id()) { ?>
                                        <a href="?page=course_details&tab=units&course_id=<?php echo $course_object->ID; ?>" class="button button-units"><?php _e('Units', 'cp'); ?></a>
                                    <?php } ?>
                                    <?php if (current_user_can('coursepress_change_course_status_cap') || (current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id())) { ?>
                                        <a href="?page=courses&course_id=<?php echo $course_object->ID; ?>&action=change_status&new_status=<?php echo ($course_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>" class="button button-<?php echo ($course_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>"><?php ($course_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a></td>
                                <?php } ?>
                                <?php if (current_user_can('coursepress_delete_course_cap') || (current_user_can('coursepress_delete_my_course_cap'))) { ?>
                                    <td <?php echo $style; ?>>
                                        <?php if (current_user_can('coursepress_delete_course_cap') || (current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id())) { ?>
                                            <a href="?page=courses&action=delete&course_id=<?php echo $course_object->ID; ?>" onClick="return removeCourse();" class="remove-button"></a>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                        ?>

                        <?php
                        if (count($wp_course_search->get_results()) == 0) {
                            ?>
                            <tr>
                                <td colspan="6"><div class="zero-courses"><?php _e('No courses found.', 'cp') ?></div></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table><!--/widefat shadow-table-->

                <div class="tablenav">
                    <div class="tablenav-pages"><?php $wp_course_search->page_links(); ?></div>
                </div><!--/tablenav-->

            </form>

        </div><!--/wrap-->
    <?php } ?>