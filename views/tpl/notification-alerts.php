<script type="text/template" id="coursepress-notification-alerts-tpl">

    <?php if ( CoursePress_Data_Capabilities::can_add_notifications() ) : ?>
        <div class="clear">
            <a href="javascript:void(0);" class="cp-btn cp-btn-active cp-notification-menu-item notifications-alerts_form" data-page="alerts_form" data-tab="alerts"><?php _e( 'New Course Alert', 'cp' ); ?></a>
        </div>
    <?php endif; ?>
    <?php cp_subsubsub( $statuses ); ?>
	<?php if ( 0 < count( $notifications ) ) { ?>
		<form method="get" class="cp-bulk-actions-form" id="cp-bulk-actions-form">
			<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
			<div class="cp-flex">
				<div id="bulk-actions">
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

		<table class="coursepress-table" cellspacing="0">
			<thead>
			<tr>
				<th id="cb" class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'cp' ); ?></label>
					<input id="cb-select-all-1" type="checkbox">
				</td>
				<?php foreach ( $columns as $column_id => $column_label ) : ?>
					<th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
						<?php echo $column_label; ?>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php $odd = true; ?>
            <?php foreach ( $notifications as $notification ) : ?>
                <tr class="<?php echo $odd ? 'odd' : 'even'; ?>">
					<th scope="row" class="check-column check-column-value">
						<input type="checkbox" name="notifications[]" value="<?php esc_attr_e( $notification->ID ); ?>">
					</th>

                    <?php foreach ( array_keys( $columns ) as $column_id ) : ?>
                        <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" data-id="<?php echo esc_attr( $notification->ID ); ?>">
                            <?php
                            $can_delete = CoursePress_Data_Capabilities::can_delete_notification( $notification->ID );
                            switch( $column_id ) :
                                case 'title' :
                                    echo $notification->post_title;
                                    echo '<div class="row-actions">';
                                    if ( 'trash' != $current_status ) {
                                        if ( CoursePress_Data_Capabilities::can_update_notification( $notification->ID ) ) :
                                            printf(
                                                '<span class="edit"><a href="#" aria-label="%s “%s”" class="cp_edit_alert" data-page="alerts_form" data-tab="alerts" data-id="' . $notification->ID . '">Edit</a></span>',
                                                esc_attr__( 'Edit', 'cp' ),
                                                esc_attr( $notification->post_title ),
                                                esc_html__( 'Edit', 'cp' )
                                            );
                                        endif;
                                        if ( $can_delete ) :
                                            echo ' | <span class="inline hide-if-no-js cp-trash"><a href="#">' . __( 'Trash', 'cp' ) . '</a></span>';
                                        endif;
                                    } elseif ( $can_delete ) { ?>
                                        <span class="inline hide-if-no-js cp-restore"><a href="#"><?php _e( 'Restore', 'cp' ); ?></a> |</span>
                                        <span class="inline hide-if-no-js cp-delete"><a href="#"><?php _e( 'Delete Permanently', 'cp' ); ?></a></span>
                                    <?php }
                                    echo '</div>';

                                    break;
                                case 'course' :
                                    // Get course name.
                                    $course_id = get_post_meta( $notification->ID, 'alert_course', true );
                                    echo empty( $course_id ) ? __( 'All Courses', 'cp' ) : get_the_title( $course_id );
                                    break;
                                case 'status':
                                    echo '<label>';
                                    $active = isset( $notification->post_status ) && 'publish' === $notification->post_status;
                                    $disabled = CoursePress_Data_Capabilities::can_change_notification_status( $notification->ID ) ? '' : 'disabled="disabled"';
                                    printf(
                                        '<input type="checkbox" class="cp-toggle-input cp-toggle-alert-status" value="%d" %s %s /> <span class="cp-toggle-btn"></span>',
                                        esc_attr( $notification->ID ),
                                        checked( $active, true, false ),
                                        $disabled
                                    );
                                    echo '</label>';
                                    break;
                                default :
                                    /**
                                     * Trigger to allow custom column value
                                     *
                                     * @since 3.0
                                     * @param string $column_id
                                     * @param object $notification
                                     */
                                    do_action( 'coursepress_notifications_list_column', $column_id, $notification );
                                    break;
                            endswitch;
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php $odd = $odd ? false : true; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
	<?php } else { ?>
		<div class="clear cp-alert cp-alert-info">
			<p><?php esc_html_e( 'No notifications found.', 'cp' ); ?></p>
		</div>
	<?php } ?>
    <?php if ( ! empty( $list_table ) ) : ?>
        <div class="tablenav cp-admin-pagination">
            <?php $list_table->pagination( 'bottom' ); ?>
        </div>
    <?php endif; ?>

</script>
