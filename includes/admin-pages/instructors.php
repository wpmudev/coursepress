<?php
$page = $_GET['page'];

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

if (isset($_GET['instructor_id']) && is_numeric($_GET['instructor_id'])) {
    $instructor = new Instructor($_GET['instructor_id']);
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['instructor_id']) && is_numeric($_GET['instructor_id'])) {
    $instructor->delete_instructor();
}

if (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'view') && isset($_GET['instructor_id']) && is_numeric($_GET['instructor_id'])) {
    include('instructors-profile.php');
} else {

    // Query the users
    $wp_user_search = new Instructor_Search($usersearch, $page_num);
    ?>

    <div class="wrap nosubsub">


        <div class="icon32 " id="icon-users"><br></div>
        <h2><?php _e('Instructors', 'cp'); ?><a class="add-new-h2" href="user-new.php"><?php _e('Add New', 'cp'); ?></a></h2>


        <form method="get" action="?page=<?php echo esc_attr($page); ?>" class="search-form">
            <p class="search-box">
                <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />
                <label class="screen-reader-text"><?php _e('Search Instructors', 'cp'); ?>:</label>
                <input type="text" value="<?php echo esc_attr($s); ?>" name="s">
                <input type="submit" class="button" value="<?php _e('Search Instructors', 'cp'); ?>">
            </p>
        </form>


        <br class="clear" /><br class="clear" />


        <form method="get" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">
            <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />

            <?php
            wp_nonce_field('bulk-instructors');

            $columns = array(
                "ID" => __('Instructor ID', 'cp'),
                "user_firstname" => __('First Name', 'cp'),
                "user_lastname" => __('Surname', 'cp'),
                "registration_date" => __('Registered', 'cp'),
                "courses" => __('Courses', 'cp'),
                "edit" => __('Edit', 'cp'),
                "delete" => __('Delete', 'cp'),
            );

            $col_sizes = array(
                '8', '15', '15', '20', '10', '7', '5'
            );
            ?>

            <table cellspacing="0" class="widefat fixed shadow-table">
                <thead>
                    <tr>
                        <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
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

                                <!--<tfoot>
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
                                </tfoot>-->

                <tbody>
                    <?php
                    $style = '';

                    foreach ($wp_user_search->get_results() as $user) {

                        $user_object = new Instructor($user->ID);
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
                            <td <?php echo $style; ?> style="padding-top:9px; padding-right:15px;"><a href="?page=instructors&action=view&instructor_id=<?php echo $user_object->ID; ?>" class="button button-settings"><?php _e('View', 'cp'); ?></a></td>
                            <td <?php echo $style; ?> style="padding-top:13px;"><a href="?page=instructors&action=delete&instructor_id=<?php echo $user_object->ID; ?>" onclick="return removeInstructor();" class="remove-button">&nbsp;</a></td>

                        </tr>
                        <?php
                    }
                    ?>

                    <?php
                    if (count($wp_user_search->get_results()) == 0) {
                        ?>
                        <tr><td colspan="8"><div class="zero"><?php _e('0 instructors found', 'cp'); ?></div></td></tr>
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