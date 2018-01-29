<script type="text/template" id="coursepress-notification-alerts-tpl">

    <div class="clear">
        <a href="javascript:void(0);" class="cp-btn cp-btn-active cp-notification-menu-item notifications-alerts_form" data-page="alerts_form" data-tab="alerts"><?php _e( 'New Course Alert', 'cp' ); ?></a>
    </div>
    <?php cp_subsubsub( $statuses ); ?>

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
        <?php if ( ! empty( $notifications ) ) : ?>
            <?php foreach ( $notifications as $notification ) : ?>
                <tr class="<?php echo $odd ? 'odd' : 'even'; ?>">

                    <?php foreach ( array_keys( $columns ) as $column_id ) : ?>
                        <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" data-id="<?php echo esc_attr( $notification->ID ); ?>">
                            <?php
                            switch( $column_id ) :
                                case 'title' :
                                    echo $notification->post_title;
                                    echo '<div class="row-actions">';
                                    if ( 'trash' != $current_status ) {
                                        printf(
                                            '<span class="edit"><a href="#" aria-label="%s “%s”" class="cp_edit_alert" data-page="alerts_form" data-tab="alerts" data-id="' . $notification->ID . '">Edit</a></span>',
                                            esc_attr__( 'Edit', 'cp' ),
                                            esc_attr( $notification->post_title ),
                                            esc_html__( 'Edit', 'cp' )
                                        );
                                        echo ' | <span class="inline hide-if-no-js cp-trash"><a href="#">' . __( 'Trash', 'cp' ) . '</a></span>';
                                    } else { ?>
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
                                    printf(
                                        '<input type="checkbox" class="cp-toggle-input cp-toggle-alert-status" value="%d" %s /> <span class="cp-toggle-btn"></span>',
                                        esc_attr( $notification->ID ),
                                        checked( $active, true, false )
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
        <?php else : ?>
            <tr class="odd">
                <td><?php _e( 'No notifications found.', 'cp' ); ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ( ! empty( $list_table ) ) : ?>
        <div class="tablenav cp-admin-pagination">
            <?php $list_table->pagination( 'bottom' ); ?>
        </div>
    <?php endif; ?>

</script>
