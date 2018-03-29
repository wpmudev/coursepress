<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course_edit_link
 * @var $course CoursePress_Course
 * @var $bulk_actions Courses bulk actions
 * @var $statuses Courses statuses
 */
?>
<div class="wrap coursepress-wrap coursepress-courses" id="coursepress-courselist">
    <h1 class="wp-heading-inline">
        <?php _e( 'Courses', 'cp' ); ?>
        <?php if ( CoursePress_Data_Capabilities::can_create_course() ) : ?>
            <a href="<?php echo $course_edit_link; ?>" class="cp-btn cp-bordered-btn"><?php echo $course_post_type_object->labels->add_new_item; ?></a>
        <?php endif; ?>
    </h1>

    <div class="coursepress-page">
        <form method="get" class="cp-search-form" id="cp-search-form">
            <div class="cp-input-clear">
                <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
                <input type="text" name="s" placeholder="<?php _e( 'Type here...', 'cp' ); ?>" value="<?php echo $search; ?>" />
                <button type="button" id="cp-search-clear" class="cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
            </div>
            <button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Search', 'cp' ); ?></button>
        </form>
<?php
$count = count( $statuses );
if ( $count > 0 ) {
	printf( '<h2 class="screen-reader-text">%s</h2>', $course_post_type_object->labels->filter_items_list );
	$first = true;
	cp_subsubsub( $statuses );
}
if ( 0 < count( $courses ) ) { ?>
        <form method="get" class="cp-bulk-actions-form" id="cp-bulk-actions-form">
            <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
            <div class="cp-flex">
                <div class="cp-div" id="bulk-actions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'cp' ); ?></label>
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
<input type="button" class="cp-btn cp-btn-active" value="<?php esc_attr_e( 'Apply', 'cp' ); ?>" />
                </div>
            </div>
        </form>
<?php } ?>

        <table class="coursepress-table">
            <thead>
                <tr>
                <th id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'cp' ); ?></label><input id="cb-select-all-1" type="checkbox"></td>
                    <th class="column-title"><?php _e( 'Title', 'cp' ); ?></th>
                    <?php foreach ( $columns as $column_id => $column_label ) : ?>
                        <th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
                            <?php echo $column_label; ?>
                        </th>
                    <?php endforeach; ?>
<?php if ( 'trash' != $current_status ) { ?>
                    <th class="column-status"><?php _e( 'Active?', 'cp' ); ?></th>
<?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
				$odd = true;
				if ( ! empty( $courses ) ) :
					foreach ( $courses as $course ) :
						$edit_link = add_query_arg( 'cid', $course->ID, $course_edit_link );
						?>
                        <tr class="<?php echo $odd ? 'odd' : 'even'; ?>">
<td scope="row" class="check-column">
<label class="screen-reader-text" for="cb-select-<?php esc_attr_e( $course->ID ); ?>"><?php printf( __( 'Select %s', 'cp' ), esc_html( $course->post_title ) ); ?></label>
            <input id="cb-select-<?php esc_attr_e( $course->ID ); ?>" type="checkbox" name="post[]" value="<?php esc_attr_e( $course->ID ); ?>">
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
                <span class="screen-reader-text"><?php esc_html_e( $course->post_title ); ?></span>
			</div>
		</td>
                            <td class="column-title">
<?php
				printf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html( $course->post_title ) );
				echo $course->get_numeric_identifier_to_course_name( $course->ID , ' <small>(', ')</small>' );
?>
                            </td>

                            <?php foreach ( array_keys( $columns ) as $column_id ) : ?>
                                <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
                                    <?php
									switch ( $column_id ) :
										case 'units' :
											$count_published = $course->count_units( true );
											$count = $course->count_units( false );

											printf( _n( __( '%d Unit', 'cp' ), __( '%d Units', 'cp' ), $count ), $count );
                      echo ", ";
											printf( _n( __( '%d Published', 'cp' ), __( '%d Published', 'cp' ), $count_published ), $count_published );
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
											echo $course->course_end_date && ! $course->meta_course_open_ended ? $course->course_end_date : '-';
											break;
										case 'enrollment_start' :
											echo $course->enrollment_start_date && ! $course->meta_enrollment_open_ended ? $course->enrollment_start_date : '-';
											break;
										case 'enrollment_end' :
											echo $course->enrollment_end_date && ! $course->meta_enrollment_open_ended ? $course->enrollment_end_date : '-';
											break;
										case 'category' :
											$categories = $course->get_category();
											if ( ! empty( $categories ) ) :
												$cat_i = 1; $cat_count = count( $categories );
												foreach ( $categories as $cat_id => $cat_name ) : ?>
													<a href="<?php echo add_query_arg( 'course_category', $cat_id ); ?>" class="cp-cats-link"><?php echo $cat_name; ?></a><?php echo $cat_i < $cat_count ? ',' : ''; ?>
													<?php $cat_i++; ?>
												<?php endforeach;
											endif;
											break;
										default :
											/**
												 * Trigger to allow custom column value
												 *
												 * @since 3.0
												 * @param string $column_id
												 * @param CoursePress_Course object $course
												 */
											do_action( 'coursepress_courselist_column', $column_id, $course );
											break;
										endswitch;
									?>
                                </td>
                            <?php endforeach; ?>
<?php if ( 'trash' != $current_status ) { ?>
                            <td class="column-status">
                                <label>
                                    <?php $active = ( isset( $course->post_status ) && $course->post_status === 'publish' ); ?>
                                    <?php $can_change_status = CoursePress_Data_Capabilities::can_change_course_status( $course->ID ); ?>
                                    <input type="checkbox" class="cp-toggle-input cp-toggle-course-status" value="<?php echo $course->ID; ?>" <?php checked( $active, true ); ?>  <?php echo $can_change_status ? '' : 'disabled="disabled"'; ?> /> <span class="cp-toggle-btn"></span>
                                </label>
                            </td>
<?php } ?>
                        </tr>
                        <tr class="<?php echo $odd ? 'odd' : 'even'; ?> column-actions">
                            <td scope="row" class="check-column"></td>
                            <td colspan="<?php echo count( $columns ) + 2; ?>" data-id="<?php echo esc_attr( $course->ID ); ?>">
                                <div class="cp-row-actions">
                                    <?php if ( 'trash' != $current_status ) { ?>
                                        <?php $can_update = CoursePress_Data_Capabilities::can_update_course( $course->ID ); ?>
                                        <?php if ( $can_update ) : ?>
                                            <a href="<?php echo $edit_link; ?>" data-step="course-type" class="cp-reset-step cp-edit-overview"><?php _e( 'Overview', 'cp' ); ?></a> |
                                            <a href="<?php echo $edit_link; ?>" data-step="course-units" class="cp-reset-step cp-edit-units"><?php _e( 'Units', 'cp' ); ?></a> |
                                            <a href="<?php echo $edit_link; ?>" data-step="course-settings" class="cp-reset-step cp-edit-settings"><?php _e( 'Display Settings', 'cp' ); ?></a>
                                        <?php endif; ?>

                                    <div class="cp-dropdown hide-if-no-js">
                                        <button type="button" class="cp-btn-xs cp-dropdown-btn">
                                            <?php _e( 'More', 'cp' ); ?>
                                        </button>
                                        <ul class="cp-dropdown-menu">
                                            <?php if ( $can_update ) : ?>
                                                <li class="menu-item-students">
                                                    <a href="<?php echo $edit_link; ?>" data-step="course-students" class="cp-reset-step"><?php _e( 'Students', 'cp' ); ?></a>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ( CoursePress_Data_Capabilities::can_create_course( $course->ID ) ) : ?>
                                                <li class="menu-item-duplicate-course cp-duplicate">
                                                    <a href="#"><?php _e( 'Duplicate', 'cp' ); ?></a>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ( CoursePress_Data_Capabilities::can_update_course( $course->ID ) ) : ?>
                                            <li class="menu-item-export">
                                                <a href="<?php echo add_query_arg( array( 'course_id' => $course->ID, '_wpnonce' => wp_create_nonce( 'export_course' ), 'cp_action' => 'export_course' ) ); ?>"><?php _e( 'Export', 'cp' ); ?></a>
                                            </li>
                                            <?php endif; ?>
                                            <li class="menu-item-view-course">
                                                <a href="<?php echo esc_url( $course->get_permalink() ); ?>" target="_blank"><?php _e( 'View', 'cp' ); ?></a>
                                            </li>
                                        <?php if ( CoursePress_Data_Capabilities::can_delete_course( $course->ID ) ) : ?>
                                            <li class="menu-item-trash cp-trash" data-course="<?php echo $course->ID; ?>">
                                                <a href="#"><?php _e( 'Trash', 'cp' ); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        </ul>
                                    </div>
                                    <?php } else { ?>
                                    <span class="inline hide-if-no-js cp-restore"><a href="#"><?php _e( 'Restore', 'cp' ); ?></a> |</span>
                                    <span class="inline hide-if-no-js cp-delete"><a href="#"><?php _e( 'Delete Permanently', 'cp' ); ?></a></span>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php
					if ( $odd ) {
						$odd = false; } else { 							$odd = true; }
					endforeach;
				else : ?>
                    <tr class="odd">
                        <?php $colspan = count( $columns ) + 3; ?>
                        <td colspan="<?php echo $colspan; ?>">
<?php
if ( 'trash' == $current_status ) {
	echo $course_post_type_object->labels->not_found_in_trash;
} else {
	echo $course_post_type_object->labels->not_found;
}
?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot><td>&nbsp;</td></tfoot>
        </table>
        <?php if ( ! empty( $pagination ) ) : ?>
            <div class="tablenav cp-admin-pagination">
                <?php $pagination->pagination( 'bottom' ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
