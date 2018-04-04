<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap" id="coursepress-reports-list" data-download_nonce="<?php esc_attr_e( $download_nonce ); ?>">
	<h1 class="wp-heading-inline"><?php _e( 'Reports', 'cp' ); ?></h1>
    <div class="coursepress-page">
        <form method="get" class="cp-action-form" id="cp-search-form">
            <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
            <div class="cp-flex">
<?php if ( 0 < count( $items ) ) { ?>
                <div class="cp-div" id="bulk-actions">
                    <label class="label"><?php _e( 'Bulk Actions', 'cp' ); ?></label>
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
<input type="button" class="cp-btn cp-btn-active" value="<?php esc_attr_e( 'Apply', 'cp' ); ?>" data-course="<?php esc_attr_e( $current ); ?>" />
                </div>
<?php } ?>
<?php if ( ! empty( $courses ) ) { ?>
                <div class="cp-div">
                    <label class="label"><?php _e( 'Filter by course', 'cp' ); ?></label>
                    <div class="cp-input-clear">
                        <select name="course_id" id="select_course_id">
<?php
foreach ( $courses as $course_id => $course ) {
	printf(
		'<option value="%d" %s>%s</option>',
		esc_attr( $course_id ),
		selected( $current, $course_id ),
		esc_html( $course->post_title )
	);
}
	?>
                        </select>
                    </div>
                    <button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Filter', 'cp' ); ?></button>
                </div>
<?php } ?>
            </div>

        </form>
<?php
if ( ! empty( $items ) ) {
?>
        <table class="coursepress-table">
            <thead>
                <tr>
                <th id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'cp' ); ?></label><input id="cb-select-all-1" type="checkbox"></td>
                    <?php foreach ( $columns as $column_id => $column_label ) { ?>
                        <th class="manage-column column-<?php echo esc_attr( strtolower( $column_id ) ); echo esc_attr( in_array( $column_id, $hidden_columns ) ? ' hidden': '' ); ?>" id="<?php echo esc_attr( $column_id ); ?>">
                            <?php echo $column_label; ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
<?php
foreach ( $items as $item ) {
	$clasess = array(
	'report-'.strtolower( $item->ID ),
	);
	?>
	<tr class="<?php echo esc_attr( implode( ' ', $clasess ) ); ?>">
		<th scope="row" class="check-column"><input type="checkbox" name="students[]" value="<?php esc_attr_e( $item->ID ); ?>"></th>
<?php foreach ( array_keys( $columns ) as $column_id ) { ?>
                                <td class="column-<?php
								echo esc_attr( strtolower( $column_id ) );
								echo esc_attr( in_array( $column_id, $hidden_columns ) ? ' hidden': '' );
?>">
                                    <?php
									switch ( $column_id ) {
										case 'ID':
											echo $item->ID;
										break;

										case 'student':
											echo '<div class="cp-flex">';
											echo '<span class="gravatar">';
											echo $item->get_avatar( 30 );
											echo '</span>';
											echo ' ';
											echo '<span class="user_login">';
											echo $item->user_login;
											echo '</span>';
											echo ' ';
											echo '<span class="display_name">(';
											echo $item->display_name;
											echo ')</span>';
											echo '</div>';
										break;

										case 'responses':
											echo $item->responses;
										break;

										case 'average':
											if ( isset( $item->progress['completion']['progress'] ) ) {
												echo $item->progress['completion']['progress'];
											} else {
												echo 0;
											}
											echo '%';
											break;

										case 'download':
											printf(
												'<a href="#" data-student="%d" data-course="%d"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;</a>',
												esc_attr( $item->ID ),
												esc_attr( $current )
											);
											break;

										case 'view':
											printf(
												'<a href="%s"><i class="fa fa-file-text-o" aria-hidden="true"></i>&nbsp;</a>',
												esc_url( $item->preview_url )
											);
											break;

										default :
											echo $column_id;
											/**
			 * Trigger to allow custom column value
			 *
			 * @since 3.0
			 * @param string $column_id
			 * @param CoursePress_Course object $item
			 */
											do_action( 'coursespress_reportslist_column', $column_id, $item );
										break;
									}
									?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php
}
?>
            </tbody>
        </table>
<?php
} else {
?>
<div class="cp-alert cp-alert-info">
	<p><?php esc_html_e( 'No reports found.', 'cp' ); ?></p>
</div>
<?php } ?>
        <?php if ( ! empty( $pagination ) ) : ?>
            <div class="tablenav cp-admin-pagination">
                <?php $pagination->pagination( 'bottom' ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
