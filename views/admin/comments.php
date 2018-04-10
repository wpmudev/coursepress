<div class="wrap coursepress-wrap" id="coursepress-comments-list">
	<h1 class="wp-heading-inline"><?php _e( 'Comments', 'cp' ); ?></h1>
    <div class="coursepress-page">
        <form method="get" class="cp-search-form" id="cp-search-form">
            <div class="cp-flex">
                <div class="cp-div">
                    <label class="label"><?php _e( 'Filter by course', 'cp' ); ?></label>
                    <div class="cp-input-clear">
                        <select name="course_id" id="select_course_id">
                            <option value=""><?php _e( 'Any course', 'cp' ); ?></option>
<?php
$current = isset( $_REQUEST['course_id'] ) ? $_REQUEST['course_id'] : 0;
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

        <table class="coursepress-table wp-list-table widefat comments">
            <thead>
                <tr>
                    <?php foreach ( $columns as $column_id => $column_label ) { ?>
                        <th class="manage-column column-<?php echo $column_id; ?> <?php echo in_array( $column_id, $hidden_columns ) ? 'hidden': ''; ?>" id="<?php echo $column_id; ?>">
                            <?php echo $column_label; ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$clasess = array(
							1 == $item->comment_approved? 'approved':'unapproved',
							'comment-'.$item->comment_ID,
						);
						?>
                        <tr class="<?php echo esc_attr( implode( ' ', $clasess ) ); ?>">
                            <?php foreach ( array_keys( $columns ) as $column_id ) { ?>
                                <td class="column-<?php echo $column_id; ?> <?php echo in_array( $column_id, $hidden_columns ) ? 'hidden': ''; ?>">
<?php
switch ( $column_id ) {
	case 'author':
		echo '<div class="cp-flex cp-user">';
		echo '<span class="gravatar">';
		echo $item->user['avatar'];
		echo '</span>';
		echo ' ';
		echo '<span class="display_name">';
		echo $item->user['display_name'];
		echo '</span>';
		echo '</div>';
		echo '<div class="actions hidden">';
		printf(
			'<a href="#" data-id="%d" data-nonce="%s" class="status">%s</a>',
			esc_attr( $item->comment_ID ),
			esc_attr( $item->status_nonce ),
			1 == $item->comment_approved ? esc_attr__( 'Unapprove', 'cp' ) : esc_attr__( 'Approve', 'cp' )
		);
		echo ' ';
		printf(
			'<a href="%s" class="edit">%s</a>',
			esc_url( $item->edit_comment_link ),
			esc_attr__( 'Edit', 'cp' )
		);
		echo '</div>';
		break;

	case 'comment':
		comment_text( $item->comment_ID );
		break;

	case 'in_response_to':
		printf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( $item->in_response_to_link ),
			esc_html( $item->parent['title'] )
		);
		break;

	case 'added':
		printf(
			'<strong>%s</strong>%s',
			esc_html( $item->time ),
			esc_html( $item->date )
		);
		break;

	default:
		echo $column_id;
		/**
												 * Trigger to allow custom column value
												 *
												 * @since 3.0
												 * @param string $column_id
												 * @param CoursePress_Course object $item
												 */
		do_action( 'coursespress_commentslist_column', $column_id, $item );
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
                    <tr>
                        <td colspan="<?php echo count( $columns ); ?>">
                            <?php _e( 'No comments found.', 'cp' ); ?>
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
