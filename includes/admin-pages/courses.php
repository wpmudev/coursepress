<?php
if ( isset($_GET['quick_setup']) ) {
    include( 'quick-setup.php' );
} else {
    if ( isset($_GET['s']) ) {
        $s = $_GET['s'];
    } else {
        $s = '';
    }

    $page = $_GET['page'];

    if ( isset($_POST['action']) && isset($_POST['courses']) ) {
        check_admin_referer('bulk-courses');

        $action = $_POST['action'];

        foreach ( $_POST['courses'] as $course_value ) {
            if ( is_numeric($course_value) ) {
                $course_id = ( int ) $course_value;
                $course = new Course($course_id);
                $course_object = $course->get_course();

                switch ( addslashes($action) ) {
                    case 'publish':
                        if ( current_user_can('coursepress_change_course_status_cap') || ( current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id() ) ) {
                            $course->change_status('publish');
                            $message = __('Selected courses have been published successfully.', 'cp');
                        } else {
                            $message = __("You don't have right persmissions to change course status.", 'cp');
                        }
                        break;

                    case 'unpublish':
                        if ( current_user_can('coursepress_change_course_status_cap') || ( current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id() ) ) {
                            $course->change_status('private');
                            $message = __('Selected courses have been unpublished successfully.', 'cp');
                        } else {
                            $message = __("You don't have right persmissions to change course status.", 'cp');
                        }
                        break;

                    case 'delete':
                        if ( current_user_can('coursepress_delete_course_cap') || ( current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id() ) ) {
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
    if ( isset($_GET['page_num']) ) {
        $page_num = ( int ) $_GET['page_num'];
    } else {
        $page_num = 1;
    }

    if ( isset($_GET['s']) ) {
        $coursesearch = $_GET['s'];
    } else {
        $coursesearch = '';
    }

    $wp_course_search = new Course_Search($coursesearch, $page_num);

    if ( isset($_GET['course_id']) ) {
        $course = new Course($_GET['course_id']);
    }

    if ( isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['course_id']) && is_numeric($_GET['course_id']) ) {
        if ( !isset($_GET['cp_nonce']) || !wp_verify_nonce($_GET['cp_nonce'], 'delete_course_' . $_GET['course_id']) ) {
            die(__('Cheating huh?', 'cp'));
        }
        $course_object = $course->get_course();
        if ( current_user_can('coursepress_delete_course_cap') || ( current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id() ) ) {
            $course->delete_course($force_delete = true);
            $message = __('Selected course has been deleted successfully.', 'cp');
        } else {
            $message = __("You don't have right persmissions to delete the course.", 'cp');
        }
    }

    if ( isset($_GET['action']) && $_GET['action'] == 'change_status' && isset($_GET['course_id']) && is_numeric($_GET['course_id']) ) {
        if ( !isset($_GET['cp_nonce']) || !wp_verify_nonce($_GET['cp_nonce'], 'change_course_status_' . $_GET['course_id']) ) {
            die(__('Cheating huh?', 'cp'));
        }
        $course->change_status($_GET['new_status']);
        $message = __('Status for the selected course has been changed successfully.', 'cp');
    }
    ?>
    <div class="wrap nosubsub">
        <div class="icon32" id="icon-themes"><br></div>
        <h2><?php _e('Courses', 'cp'); ?>
            <?php
            if ( current_user_can('coursepress_create_course_cap') ) {
                if ( $wp_course_search->is_light ) {
                    if ( $wp_course_search->get_count_of_all_courses() <= 9 ) {
                        ?><a class="add-new-h2" href="<?php echo admin_url('admin.php?page=course_details'); ?>"><?php _e('Add New', 'cp'); ?></a>
                        <?php
                    }
                } else {
                    ?>
                    <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=course_details'); ?>"><?php _e('Add New', 'cp'); ?></a>
                    <?php
                }
            }
            ?>
        </h2>

        <?php
        if ( isset($message) ) {
            ?>
            <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
            <?php
        }
        ?>
        <div class="tablenav tablenav-top">

            <div class="alignright actions new-actions">
                <form method="get" action="<?php echo admin_url('admin.php?page=' . $page); ?>" class="search-form">
                    <p class="search-box">
                        <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />
                        <label class="screen-reader-text"><?php _e('Search Courses', 'cp'); ?>:</label>
                        <input type="text" value="<?php echo esc_attr($s); ?>" name="s">
                        <input type="submit" class="button" value="<?php _e('Search Courses', 'cp'); ?>">
                    </p>
                </form>
            </div><!--/alignright-->

            <form method="post" action="<?php echo esc_attr(admin_url('admin.php?page=' . $page)); ?>" id="posts-filter">

                <?php if ( current_user_can('coursepress_change_course_status_cap') || current_user_can('coursepress_delete_course_cap') ) { ?>
                    <div class="alignleft actions">
                        <select name="action">
                            <option selected="selected" value=""><?php _e('Bulk Actions', 'cp'); ?></option>
                            <?php if ( current_user_can('coursepress_change_course_status_cap') ) { ?>
                                <option value="publish"><?php _e('Publish', 'cp'); ?></option>
                                <option value="unpublish"><?php _e('Unpublish', 'cp'); ?></option>
                            <?php } ?>
                            <?php if ( current_user_can('coursepress_delete_course_cap') ) { ?>
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
                    "units" => __('Units', 'cp'),
                    "students" => __('Students', 'cp'),
                    "status" => __('Published', 'cp'),
                        //"actions" => __('Actions', 'cp'),
                );


                $col_sizes = array(
                    '3', '55', '10', '4', '10'
                );

                if ( current_user_can('coursepress_delete_course_cap') || ( current_user_can('coursepress_delete_my_course_cap') ) ) {
                    $columns["remove"] = __('Delete', 'cp');
                    $col_sizes[] = '7';
                }
                ?>

                <table cellspacing="0" class="widefat shadow-table unit-control-buttons">
                    <thead>
                        <tr>
                            <th style="width: 3%;" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php echo $col_sizes[0] . '%'; ?>"><input type="checkbox"></th>
                            <?php
                            $n = 1;
                            foreach ( $columns as $key => $col ) {
                                ?>
                                <th class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" style="width: <?php echo $col_sizes[$n] . '%'; ?>;" scope="col"><?php echo $col; ?></th>
                                <?php
                                $n++;
                            }
                            ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $style = '';

                        foreach ( $wp_course_search->get_results() as $course ) {

                            $course_obj = new Course($course->ID);
                            $course_object = $course_obj->get_course();

                            $style = ( 'alternate' == $style ) ? '' : 'alternate';
                            ?>
                            <tr id='user-<?php echo $course_object->ID; ?>' class="<?php echo $style; ?>">
                                <th scope='row' class='check-column'>
                                    <input type='checkbox' name='courses[]' id='user_<?php echo $course_object->ID; ?>' class='' value='<?php echo $course_object->ID; ?>' />
                                </th>
                                <td class="column-course <?php echo $style; ?>"><a href="<?php echo admin_url('admin.php?page=course_details&course_id=' . $course_object->ID); ?>"><strong><?php echo $course_object->post_title; ?></strong></a><br />
                                    <!-- <div class="course-thumbnail"><img src="<?php echo Course::get_course_thumbnail($course->ID); ?>" alt="<?php echo esc_attr($course_object->post_title); ?>" /></div> -->
                                    <div class="course_excerpt"><?php echo get_the_course_excerpt($course_object->ID, 55); ?></div>
                                    <div class="column-course-units visible-small visible-extra-small">
                                        <strong><?php _e('Units', 'cp'); ?>:</strong>
                                        <?php echo $course_obj->get_units('', 'any', true); ?> <?php _e('Units', 'cp'); ?>,
                                        <?php echo $course_obj->get_units('', 'publish', true); ?> Published
                                    </div>
                                    <div class="column-course-students visible-small visible-extra-small">
                                        <strong><?php _e('Students', 'cp'); ?>:</strong>
                                        <a href="<?php echo admin_url('admin.php?page=course_details&tab=students&course_id=' . $course_object->ID); ?>"><?php echo $course_obj->get_number_of_students(); ?></a>
                                    </div>									
                                    <div class="row-actions hide-small hide-extra-small">
                                        <span class="edit_course"><a href="<?php echo admin_url('admin.php?page=course_details&course_id=' . $course_object->ID); ?>"><?php _e('Edit', 'cp'); ?></a> | </span>
                                        <?php if ( current_user_can('coursepress_view_all_units_cap') || $course_object->post_author == get_current_user_id() ) { ?>
                                            <span class="course_units"><a href="<?php echo admin_url('admin.php?page=course_details&tab=units&course_id=' . $course_object->ID); ?>"><?php _e('Units', 'cp'); ?></a> | </span>
                                        <?php } ?>
                                        <span class="course_students"><a href="<?php echo admin_url('admin.php?page=course_details&tab=students&course_id=' . $course_object->ID); ?>"><?php _e('Students', 'cp'); ?></a> | </span>
                                        <?php /* if (current_user_can('coursepress_change_course_status_cap') || ( current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id() )) { ?>
                                          <span class="course_publish_unpublish"><a href="<?php echo wp_nonce_url(admin_url('admin.php?page=courses&course_id=' . $course_object->ID . '&action=change_status&new_status=' . ( $course_object->post_status == 'unpublished' ? 'publish' : 'private' )), 'change_course_status_' . $course_object->ID, 'cp_nonce'); ?>"><?php ( $course_object->post_status == 'unpublished' ) ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a> | </span>
                                          <?php } */ ?>
                                        <?php /* if (current_user_can('coursepress_delete_course_cap') || ( current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id() )) { ?>
                                          <span class="course_remove"><a href="<?php echo wp_nonce_url(admin_url('admin.php?page=courses&action=delete&course_id=' . $course_object->ID), 'delete_course_' . $course_object->ID, 'cp_nonce'); ?>" onClick="return removeCourse();"><?php _e('Delete', 'cp'); ?></a> | </span>
                                          <?php } */ ?>
                                        <span class="view_course"><a href="<?php echo get_permalink($course->ID); ?>" rel="permalink"><?php _e('View Course', 'cp') ?></a><?php if ( current_user_can('coursepress_view_all_units_cap') || $course_object->post_author == get_current_user_id() ) { ?> | <?php } ?></span>
                                        <?php if ( current_user_can('coursepress_view_all_units_cap') || $course_object->post_author == get_current_user_id() ) { ?>
                                            <span class="units"><a href="<?php echo get_permalink($course->ID); ?>units/" rel="permalink"><?php _e('View Units', 'cp') ?></a></span>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td class="column-units <?php echo $style; ?>">
                                    <?php echo $course_obj->get_units('', 'any', true); ?> <?php _e('Units', 'cp'); ?><br />
                                    <?php echo $course_obj->get_units('', 'publish', true); ?> <?php _e('Published', 'cp'); ?>
                                </td>
                                <td class="center column-students <?php echo $style; ?>"><a href="<?php echo admin_url('admin.php?page=course_details&tab=students&course_id=' . $course_object->ID); ?>"><?php echo $course_obj->get_number_of_students(); ?></a></td>
                                <td class="column-status <?php echo $style; ?>">
                                    <div class="courses-state">
                                        <div class="course_state_id" data-id="<?php echo $course->ID; ?>"></div>
                                        <span class="draft <?php echo ( $course_object->post_status == 'unpublished' ) ? 'on' : '' ?>"><i class="fa fa-ban"></i></span>
                                        <div class="control <?php echo ( $course_object->post_status == 'unpublished' ) ? '' : 'on' ?>">
                                            <div class="toggle"></div>
                                        </div>
                                        <span class="live <?php echo ( $course_object->post_status == 'unpublished' ) ? '' : 'on' ?>"><i class="fa fa-check"></i></span>
                                    </div>
                                    <!--<?php echo ( $course_object->post_status == 'publish' ) ? ucfirst($course_object->post_status) . 'ed' : ucfirst($course_object->post_status); ?>-->
                                </td>
                                <!--<td class="column-actions <?php echo $style; ?>">
                                    <a href="<?php echo admin_url('admin.php?page=course_details&course_id=' . $course_object->ID); ?>" class="button button-settings"><?php _e('Settings', 'cp'); ?></a>

                                <?php if ( current_user_can('coursepress_view_all_units_cap') || $course_object->post_author == get_current_user_id() ) { ?>
                                                            <a href="<?php echo admin_url('admin.php?page=course_details&tab=units&course_id=' . $course_object->ID); ?>" class="button button-units"><?php _e('Units', 'cp'); ?></a>
                                <?php } ?>
                                <?php if ( current_user_can('coursepress_change_course_status_cap') || ( current_user_can('coursepress_change_my_course_status_cap') && $course_object->post_author == get_current_user_id() ) ) { ?>

                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=courses&course_id=' . $course_object->ID . '&action=change_status&new_status=' . ( $course_object->post_status == 'unpublished' ? 'publish' : 'private' )), 'change_course_status_' . $course_object->ID, 'cp_nonce'); ?>" class="button button-<?php echo ( $course_object->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>"><?php ( $course_object->post_status == 'unpublished' ) ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a></td>-->
                                <?php } ?>
                                <?php if ( current_user_can('coursepress_delete_course_cap') || ( current_user_can('coursepress_delete_my_course_cap') ) ) { ?>
                                    <td class="column-remove <?php echo $style; ?>">
                                        <?php if ( current_user_can('coursepress_delete_course_cap') || ( current_user_can('coursepress_delete_my_course_cap') && $course_object->post_author == get_current_user_id() ) ) { ?>
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=courses&action=delete&course_id=' . $course_object->ID), 'delete_course_' . $course_object->ID, 'cp_nonce'); ?>" onClick="return removeCourse();">
                                                <i class="fa fa-times-circle cp-move-icon remove-btn"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                        ?>

                        <?php
                        if ( count($wp_course_search->get_results()) == 0 ) {
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
                    <?php if ( !$wp_course_search->is_light ) { ?>
                        <div class="tablenav-pages"><?php $wp_course_search->page_links(); ?></div>
                    <?php } ?>
                </div><!--/tablenav-->

            </form>

        </div><!--/wrap-->
    <?php } ?>