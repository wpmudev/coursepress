<?php
// Query the courses
$wp_course_search = new Course_Search($coursesearch, $coursepage);

if(isset($_GET['course_id'])){
    $course =  new Course($_GET['course_id']);
}

if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['course_id']) && is_numeric($_GET['course_id'])){
    $course->delete_course($force_delete = true);
}

if(isset($_GET['action']) && $_GET['action'] == 'change_status' && isset($_GET['course_id']) && is_numeric($_GET['course_id'])){
    $course->change_status($_GET['new_status']);
}
?>
<div class="wrap nosubsub">
    <div class="icon32" id="icon-themes"><br></div>
    <h2><?php _e('Courses', 'cp'); ?><a class="add-new-h2" href="admin.php?page=course_details"><?php _e('Add New', 'cp'); ?></a></h2>

    <form method="get" action="?page=<?php echo esc_attr($page); ?>" class="search-form">
        <p class="search-box">
            <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />
            <label class="screen-reader-text"><?php _e('Search Courses', 'cp'); ?>:</label>
            <input type="text" value="<?php echo esc_attr($s); ?>" name="s">
            <input type="submit" class="button" value="<?php _e('Search Courses', 'cp'); ?>">
        </p>
    </form>

    <br class="clear" />

    <form method="get" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">
        <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />

        <div class="tablenav">
            <div class="tablenav-pages"><?php $wp_course_search->page_links(); ?></div>
        </div><!--/tablenav-->

        <?php
        wp_nonce_field('bulk-courses');

        $columns = array(
            "course" => __('Course', 'cp'),
            "status" => __('Status', 'cp'),
            "actions" => __('Actions', 'cp'),
            "remove" => __('Remove', 'cp')
        );
        
        $col_sizes = array(
            '3', '70', '10', '10', '7'
        );
        ?>

        <table cellspacing="0" class="widefat">
            <thead>
                <tr>
                    <th style="" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php echo $col_sizes[0].'%'; ?>"><input type="checkbox"></th>
                    <?php
                    $n = 1;
                    foreach ($columns as $key => $col) {
                        ?>
                        <th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo $col_sizes[$n].'%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                        <?php
                        $n++;
                    }
                    ?>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>
                    <?php
                    reset($columns);

                    foreach ($columns as $key => $col) {
                        ?>
                        <th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                        <?php
                    }
                    ?>
                </tr>
            </tfoot>

            <tbody>
                <?php
                $style = '';

                foreach ($wp_course_search->get_results() as $course) {

                    $course_object = new Course($course->ID);
                    $course_object = $course_object->get_course();

                    $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                    ?>
                    <tr id='user-<?php echo $course_object->ID; ?>' <?php echo $style; ?>>
                        <th scope='row' class='check-column'>
                            <input type='checkbox' name='courses[]' id='user_<?php echo $course_object->ID; ?>' class='' value='<?php echo $course_object->ID; ?>' />
                        </th>
                        <td <?php echo $style; ?>><a href="?page=course_details&course_id=<?php echo $course_object->ID; ?>"><strong><?php echo $course_object->post_title; ?></strong></a><br />
                            <div class="course_excerpt"><?php echo get_the_course_excerpt($course_object->ID); ?></div>
                        </td>
                        <td <?php echo $style; ?>><?php echo ($course_object->post_status == 'publish') ? ucfirst($course_object->post_status).'ed' : ucfirst($course_object->post_status); ?></td>
                        <td <?php echo $style; ?>><a href="?page=course_details&course_id=<?php echo $course_object->ID; ?>" class="button button-settings"><?php _e('Settings', 'cp'); ?></a><a href="?page=course_details&tab=units&course_id=<?php echo $course_object->ID; ?>" class="button button-units"><?php _e('Units', 'cp'); ?></a><a href="?page=courses&course_id=<?php echo $course_object->ID; ?>&action=change_status&new_status=<?php echo ($course_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>" class="button button-<?php echo ($course_object->post_status == 'unpublished') ? 'unpublish' : 'publish'; ?>"><?php ($course_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a></td>
                        <td <?php echo $style; ?>><a href="?page=courses&action=delete&course_id=<?php echo $course_object->ID; ?>" onClick="return removeCourse();" class="remove-button"></a></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

    </form>

</div>