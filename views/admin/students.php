<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course_edit_link
 * @var $course CoursePress_Student
 */
?>
<div class="wrap coursepress-wrap coursepress-students" id="coursepress-studentlist">
    <h1 class="wp-heading-inline"><?php _e( 'Students', 'cp' ); ?></h1>

    <div class="coursepress-page">
        <div class="cp-flex">
            <div class="cp-div">
                <label class="label"><?php _e( 'Filter by course', 'cp' ); ?></label>
                <select>
                    <option value=""><?php _e( 'Any course', 'cp' ); ?></option>
                </select>
            </div>

            <div class="cp-div">
                <label class="label"></label>
            </div>
        </div>
        <form method="get" class="cp-search-form" id="cp-search-form">
            <div class="cp-input-clear">
                <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
                <input type="text" name="s" placeholder="<?php _e( 'Enter search query here...', 'cp' ); ?>" value="<?php echo $search; ?>" />
                <button type="button" id="cp-search-clear" class="cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
            </div>
            <button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Search', 'cp' ); ?></button>
        </form>

        <table class="coursepress-table" cellspacing="0">
            <thead>
            <tr>
                <?php foreach ( $columns as $column_id => $column_label ) : ?>
                    <th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
                        <?php echo $column_label; ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php $odd = true; ?>
            <?php if ( ! empty( $students ) ) : ?>
                <?php foreach ( $students as $student ) : ?>
                    <?php $view_link = add_query_arg( 'cid', $student->ID ); ?>
                    <tr class="<?php echo $odd ? 'odd' : 'even'; ?>">

                        <?php foreach ( array_keys( $columns ) as $column_id ) : ?>
                            <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
                                <?php
                                switch( $column_id ) :
                                    case 'id' :
                                        echo $student->ID;
                                        break;
                                    case 'student' :
                                        echo $student->get_name();
                                        break;
                                    case 'last_active' :
                                        echo 'Today';
                                        break;
                                    case 'number_of_courses' :
                                        echo count( $student->get_enrolled_courses_ids() );
                                        break;
                                    default :
                                        /**
                                         * Trigger to allow custom column value
                                         *
                                         * @since 3.0
                                         * @param string $column_id
                                         * @param CoursePress_Student object $student
                                         */
                                        do_action( 'coursepress_studentlist_column', $column_id, $student );
                                        break;
                                endswitch;
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php $odd = $odd ? false : true; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="odd">
                    <td><?php _e( 'No students found.', 'cp' ); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php if ( ! empty( $pagination ) ) : ?>
            <div class="tablenav cp-admin-pagination">
                <?php $pagination->pagination( 'bottom' ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>