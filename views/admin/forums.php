<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $statuses
 * @var $edit_link
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap coursepress-forums" id="coursepress-forums">
    <h1 class="wp-heading-inline">
        <?php _e( 'Forums', 'cp' ); ?>
        <?php if ( CoursePress_Data_Capabilities::can_add_discussions() ) : ?>
            <a href="<?php echo $edit_link; ?>" class="cp-btn cp-bordered-btn"><?php _e( 'Create New', 'cp' ); ?></a>
        <?php endif; ?>
    </h1>
    <div class="coursepress-page">
        <?php cp_subsubsub( $statuses ); ?>
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
                        <select name="course_id" id="select_course_id">
                            <option value=""><?php _e( 'Any course', 'cp' ); ?></option>
<?php
$current = isset( $_REQUEST['course_id'] )? $_REQUEST['course_id']:0;
foreach ( $courses as $course_id => $course ) {
	printf(
		'<option value="%d" %s>%s</option>',
		esc_attr( $course_id ),
		selected( $current, $course_id ),
		esc_html( $course->post_title . $course->get_numeric_identifier_to_course_name( $course->ID ) )
	);
}
	?>
                        </select>
                    </div>
                    <button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Filter', 'cp' ); ?></button>
                </div>
            </div>
            <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
        </form>

        <table class="coursepress-table" id="cp-forums-table" cellspacing="0">
            <thead>
                <tr>
                    <th id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'cp' ); ?></label><input id="cb-select-all-1" type="checkbox"></td>
                    <?php foreach ( $columns as $column_id => $column_label ) { ?>
                        <th class="manage-column column-<?php echo $column_id; ?> <?php echo in_array( $column_id, $hidden_columns ) ? 'hidden': ''; ?>" id="<?php echo $column_id; ?>">
<?php
if ( 'comments' === $column_id ) {
	echo '<i class="fa fa-comments" aria-hidden="true"></i>';
} else {
	echo $column_label;
}
?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
<?php
$i = 0;
if ( ! empty( $forums ) ) {
	$date_format = get_option( 'date_format' );
	foreach ( $forums as $forum ) {
			$i++;
?>
                        <tr class="<?php echo $i % 2? 'odd' : 'even'; ?>">

                            <th scope="row" class="check-column check-column-value"><input type="checkbox" name="forums[]" value="<?php echo esc_attr( $forum->ID ); ?>"></th>
                            <?php foreach ( array_keys( $columns ) as $column_id ) { ?>
                                <td class="column-<?php echo $column_id; ?> <?php echo in_array( $column_id, $hidden_columns ) ? 'hidden': ''; ?>" data-id="<?php echo esc_attr( $forum->ID ); ?>">
<?php
$can_delete = CoursePress_Data_Capabilities::can_delete_discussion( $forum->ID );
switch ( $column_id ) {
	case 'topic' :
		echo $forum->post_title;
		echo '<div class="row-actions">';
		if ( 'trash' !==$current_status ) {
            if ( CoursePress_Data_Capabilities::can_update_discussion( $forum->ID ) ) :
                printf(
                    '<span class="edit"><a href="%s" aria-label="%s “%s”">Edit</a></span>',
                    $forum->edit_link,
                    esc_attr__( 'Edit', 'cp' ),
                    esc_attr( $forum->post_title ),
                    esc_html__( 'Edit', 'cp' )
                );
            endif;
            if ( $can_delete ) :
			    echo ' | <span class="inline hide-if-no-js cp-trash"><a href="#">' . __( 'Trash', 'cp' ) . '</a></span>';
            endif;
		} elseif ( $can_delete ) {
			?>
			<span class="inline hide-if-no-js cp-restore"><a href="#"><?php _e( 'Restore', 'cp' ); ?></a> |</span>
			<span class="inline hide-if-no-js cp-delete"><a href="#"><?php _e( 'Delete Permanently', 'cp' ); ?></a></span>
			<?php
		}

		echo '</div>';
		break;
	case 'course' :
		if ( isset( $forum_courses[ $forum->course_id ] ) ) {
			echo $forum_courses[ $forum->course_id ]->post_title;
		}
		break;
	case 'comments':
		echo $forum->comments_number;
		break;
	case 'status':
		echo '<label>';
		$active = isset( $forum->post_status ) && 'publish' === $forum->post_status;
		$disabled = CoursePress_Data_Capabilities::can_change_discussion_status( $forum->ID ) ? '' : 'disabled="disabled"';
		printf(
			'<input type="checkbox" class="cp-toggle-input cp-toggle-forum-status" value="%d" %s %s /> <span class="cp-toggle-btn"></span>',
			esc_attr( $forum->ID ),
			checked( $active, true, false ),
			$disabled
		);
		echo '</label>';
		break;

	default:
		echo $column_id;
		/**
				 * Trigger to allow custom column value
				 *
				 * @since 3.0
				 * @param string $column_id
				 * @param CoursePress_Course object $forum
				 */
		do_action( 'coursepress_forums_column', $column_id, $forum );
		break;
}
?>
                                </td>
                            <?php } ?>
                        </tr>
<?php
	}
} else {
?>
                    <tr class="odd">
                        <td colspan="5">
                            <?php _e( 'No forums found.', 'cp' ); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php if ( ! empty( $pagination ) ) : ?>
            <div class="tablenav cp-admin-pagination">
                <?php $pagination->pagination( 'bottom' ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
