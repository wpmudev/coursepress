<?php
// Query the users
$wp_user_search = new Instructor_Search($usersearch, $userspage);

?>
<div class="wrap nosubsub">
    <div class="icon32" id="icon-users"><br></div>
    <h2><?php _e('Instructors', 'cp'); ?><a class="add-new-h2" href="admin.php?page=instructor&action=add"><?php _e('Add New', 'cp'); ?></a></h2>

    <form method="get" action="?page=<?php echo esc_attr($page); ?>" class="search-form">
        <p class="search-box">
            <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />
            <label class="screen-reader-text"><?php _e('Search Instructors', 'cp'); ?>:</label>
            <input type="text" value="<?php echo esc_attr($s); ?>" name="s">
            <input type="submit" class="button" value="<?php _e('Search Instructors', 'cp'); ?>">
        </p>
    </form>

    <br class="clear" />

    <form method="get" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">
        <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />

        <div class="tablenav">
            <div class="tablenav-pages"><?php $wp_user_search->page_links(); ?></div>
        </div><!--/tablenav-->

        <?php
        wp_nonce_field('bulk-instructors');

        $columns = array(
            "ID" => __('Instructor ID', 'cp'),
            "user_firstname" => __('First Name', 'cp'),
            "user_lastname" => __('Surname', 'cp'),
            "registration_date" => __('Registered', 'cp'),
            "edit" => __('Edit', 'cp'),
            "delete" => __('Delete', 'cp'),
            
        );
        ?>

        <table cellspacing="0" class="widefat fixed">
            <thead>
                <tr>
                    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
                    <?php
                    foreach ($columns as $key => $col) {
                        ?>
                        <th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                        <?php
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

                foreach ($wp_user_search->get_results() as $user) {

                    $user_object = new Instructor($user->ID);
                    $roles = $user_object->roles;
                    $role = array_shift($roles);

                    $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                    ?>
                    <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>
                        <th scope='row' class='check-column'>
                            <input type='checkbox' name='users[]' id='user_<?php echo $user_object->ID; ?>' class='$role' value='<?php echo $user_object->ID; ?>' />
                        </th>
                        <td <?php echo $style; ?>><?php echo $user_object->ID; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->first_name; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->last_name; ?></td>
                        <td <?php echo $style; ?>><?php echo $user_object->user_registered; ?></td>
                        <td <?php echo $style; ?>><a href="">Edit</a></td>
                        <td <?php echo $style; ?>><a href="">Remove</a></td>
                                                
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

    </form>

</div>