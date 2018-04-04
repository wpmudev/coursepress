<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course_edit_link
 * @var $course CoursePress_Student
 */
?>
<div class="wrap coursepress-wrap coursepress-students" id="coursepress-students">
    <h1 class="wp-heading-inline"><?php _e( 'Students', 'cp' ); ?></h1>

    <div class="coursepress-page">
        <form method="get" class="cp-action-form" id="cp-search-form">
            <div class="cp-flex">
                <div class="cp-div" id="bulk-actions">
                    <label class="label"><?php _e( 'Bulk actions', 'cp' ); ?></label>
                    <div class="cp-input-clear">
                        <select id="bulk-action-selector-top">
                            <option value="-1"><?php esc_attr_e( 'Bulk Actions', 'cp' ); ?></option>
                            <?php
							foreach ( $bulk_actions as $value => $label ) {
								printf(
									'<option value="%s">%s</option>',
									esc_attr( $value ),
									esc_html( $label )
								);
							}
							?>
                        </select>
                    </div>
                    <button type="button" class="cp-btn cp-btn-active"><?php _e( 'Apply', 'cp' ); ?></button>
                </div>
                <div class="cp-div">
                    <label class="label"><?php _e( 'Filter by course', 'cp' ); ?></label>
                    <div class="cp-input-clear">
                        <select name="course_id">
                            <option value=""><?php _e( 'Any course', 'cp' ); ?></option>
                            <?php if ( ! empty( $courses ) ) : ?>
                                <?php foreach ( $courses as $course ) : ?>
                                    <?php $selected_course = empty( $_GET['course_id'] ) ? 0 : $_GET['course_id']; ?>
                                    <option value="<?php echo $course->ID; ?>" <?php selected( $course->ID, $selected_course ); ?>><?php
                                    echo $course->post_title;
                                    echo $course->get_numeric_identifier_to_course_name( $course->ID );
    ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="cp-div">
                    <label class="label"><?php _e( 'Search by course', 'cp' ); ?></label>
                    <div class="cp-input-clear">
                        <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
                        <input type="text" name="s" placeholder="<?php _e( 'Enter search query here...', 'cp' ); ?>" value="<?php echo $search; ?>" />
                        <button type="button" id="cp-search-clear" class="cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
                    </div>
                    <button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Search', 'cp' ); ?></button>
                </div>
            </div>
        </form>

        <table class="coursepress-table cp-user" cellspacing="0">
            <thead>
            <tr>
                <th id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'cp' ); ?></label><input id="cb-select-all-1" type="checkbox"></td>
                <?php foreach ( $columns as $column_id => $column_label ) : ?>
                    <th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
                        <?php echo $column_label; ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php
								$odd = true;
			if ( ! empty( $students ) ) {
				$last_active_format = get_option( 'date_format' ).' '.get_option( 'time_format' );
				foreach ( $students as $student ) {
?>
<tr class="<?php echo esc_attr( $odd ? 'odd' : 'even' ); ?>" data-studetnt-id="<?php echo esc_attr( $student->ID ); ?>">
    <th scope="row" class="check-column check-column-value"><input type="checkbox" name="students[]" value="<?php esc_attr_e( $student->ID ); ?>"></th>
    <?php foreach ( array_keys( $columns ) as $column_id ) : ?>
                            <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
<?php
					$a = sprintf( '<a href="%s">%%s</a>', esc_url( add_query_arg( array( 'view' => 'profile', 'student_id' => $student->ID ) ) ) );
switch ( $column_id ) :
	// @todo Add profile link if required.
	case 'student' :
		echo '<div class="cp-flex cp-user">';
		echo '<span class="gravatar">';
		echo get_avatar( $student->ID, 30 );
		echo '</span>';
		echo ' ';
		echo '<span class="user_login">';
		printf( $a, $student->user_login );
		echo '</span>';
		echo ' ';
		echo '<span class="display_name">(';
		printf( $a, $student->get_name() );
		echo ')</span>';
		echo '</div>';
	break;
	case 'last_active' :
		// Last activity time.
		$last_active = $student->get_last_activity_time();
		if ( $last_active ) {
			echo $last_active;
			$kind = $student->get_last_activity_kind();
			if ( ! empty( $kind ) ) {
				printf( '<span class="activity-kind">%s</span>', $kind );
			}
		} else {
			echo '--';
		}
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
                <?php } ?>
            <?php } else { ?>
                <tr class="odd">
                    <td colspan="<?php echo count( $columns ); ?>">
                        <?php _e( 'No students found.', 'cp' ); ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php if ( ! empty( $list_table ) ) : ?>
            <div class="tablenav cp-admin-pagination">
                <?php $list_table->pagination( 'bottom' ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
