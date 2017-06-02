<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course_edit_link
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap coursepress-courses" id="coursepress-courselist">
    <h1 class="wp-heading-inline">
        <?php _e( 'Courses', 'cp' ); ?>
        <a href="<?php echo $course_edit_link; ?>" class="cp-btn cp-bordered-btn"><?php _e( 'New Course', 'cp' ); ?></a>
    </h1>

    <div class="coursepress-page">
        <form method="post" class="cp-search-form cp-input-clear">
            <input type="text" name="s" placeholder="<?php _e( 'Search', 'cp' ); ?>" />
            <button type="button" class=""><?php _e( 'Clear', 'cp' ); ?></button>
        </form>
        <table class="coursepress-table">
            <thead>
                <tr>
                    <th class="column-title"><?php _e( 'Title', 'cp' ); ?></th>
                    <?php foreach ( $columns as $column_id => $column_label ) : ?>
                        <th class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
                            <?php echo $column_label; ?>
                        </th>
                    <?php endforeach; ?>
                    <th class="column-status"><?php _e( 'Active', 'cp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $odd = true;
                foreach ( $courses as $course ) :
                    $edit_link = add_query_arg( 'cid', $course->ID, $course_edit_link );
                    ?>
                    <tr class="<?php echo $odd ? 'odd' : 'even'; ?>">
                        <td class="column-title">
                            <?php echo $course->post_title; ?>
                        </td>

                        <?php foreach ( array_keys( $columns ) as $column_id ) : ?>
                            <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
                                <?php
                                    switch( $column_id ) :
                                        case 'units' :
                                            $count = $course->count_units( false );

                                            printf( _n( __( '%d Unit', 'cp' ), __( '%d Units', 'cp' ), $count ), $count );
                                            break;
                                        case 'students' :
                                            echo $course->count_students();
                                            break;
                                        case 'certified' :
                                            echo $course->count_certified_students();
                                            break;
                                        case 'start_date' :
                                            echo $course->course_start_date ? $course->course_start_date : '-';
                                            break;
                                        case 'end_date' :
                                            echo $course->course_end_date ? $course->course_end_date : '-';
                                            break;
                                        case 'enrollment_start' :
                                            echo $course->enrollment_start_date ? $course->enrollment_start_date : '-';
                                            break;
                                        case 'enrollment_end' :
                                            echo $course->enrollment_end_date ? $course->enrollment_end_date : '-';
                                            break;
                                        case 'category' :
                                            break;
                                        default :
	                                        /**
	                                         * Trigger to allow custom column value
                                             *
                                             * @since 3.0
                                             * @param string $column_id
                                             * @param CoursePress_Course object
	                                         */
	                                        do_action( 'coursepress_courselist_column', $column_id, $course );
                                            break;
                                    endswitch;
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="column-status">
                            <label>
                                <input type="checkbox" class="cp-toggle-input cp-toggle-course-status" value="<?php echo $course->ID; ?>" /> <span class="cp-toggle-btn"></span>
                            </label>
                        </td>
                    </tr>
                    <tr class="<?php echo $odd ? 'odd' : 'even'; ?> column-actions">
                        <td colspan="<?php echo count($columns)+2; ?>" data-id="<?php echo $course->ID; ?>">
                            <div class="cp-row-actions">
                                <a href="<?php echo $edit_link; ?>" data-step="course-type" class="cp-reset-step cp-edit-overview"><?php _e( 'Course Overview', 'cp' ); ?></a>
                                <a href="<?php echo $edit_link; ?>" data-step="course-units" class="cp-reset-step cp-edit-units"><?php _e( 'Units', 'cp' ); ?></a>
                                <a href="<?php echo $edit_link; ?>" data-step="course-settings" class="cp-reset-step cp-edit-settings"><?php _e( 'Display Settings', 'cp' ); ?></a>

                                <div class="cp-dropdown">
                                    <button type="button" class="cp-btn cp-btn-xs cp-dropdown-btn">
                                        <?php _e( 'More', 'cp' ); ?>
                                    </button>
                                    <ul class="cp-dropdown-menu">
                                        <li class="menu-item-students">
                                            <a href="<?php echo $edit_link; ?>" data-step="course-students" class="cp-reset-step"><?php _e( 'Students', 'cp' ); ?></a>
                                        </li>
                                        <li class="menu-item-duplicate-course">
                                            <a href=""><?php _e( 'Duplicate Course', 'cp' ); ?></a>
                                        </li>
                                        <li class="menu-item-export">
                                            <a href=""><?php _e( 'Export', 'cp' ); ?></a>
                                        </li>
                                        <li class="menu-item-view-course">
                                            <a href="" target="_blank"><?php _e( 'View Course', 'cp' ); ?></a>
                                        </li>
                                        <li class="menu-item-delete cp-delete">
                                            <a href=""><?php _e( 'Delete Course', 'cp' ); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php
                    if ( $odd )
                        $odd = false;
                    else
                        $odd = true;
                endforeach; ?>
            </tbody>
        </table>
    </div>
</div>