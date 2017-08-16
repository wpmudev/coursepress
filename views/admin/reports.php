<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap" id="coursepress-reports-list">
	<h1 class="wp-heading-inline"><?php _e( 'Reports', 'cp' ); ?></h1>
    <div class="coursepress-page">
        <form method="get" class="cp-search-form" id="cp-search-form">
            <div class="cp-flex">
                <div class="cp-div">
                    <label class="label"><?php _e( 'Filter by course', 'cp' ); ?></label>
                    <select name="course_id" id="select_course_id">
<?php
$current = isset( $_REQUEST['course_id'] )? $_REQUEST['course_id']:0;
foreach ( $courses as $course_id => $course ) {
	if ( 0 == $current ) {
		$current = $course_id;
	}
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
            </div>
            <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
        </form>

        <table class="coursepress-table">
            <thead>
                <tr>
                    <?php foreach ( $columns as $column_id => $column_label ) { ?>
                        <th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
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
		'report-'.$item->ID,
		);
		?>
		<tr class="<?php echo esc_attr( implode( ' ', $clasess ) ); ?>">
<?php foreach ( array_keys( $columns ) as $column_id ) { ?>
                                <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
                                    <?php
									switch ( $column_id ) {
										case 'ID':
											echo $item->ID;
										break;

										case 'student':
											echo '<div class="cp-flex">';
											echo '<span class="gravatar">';
											echo get_avatar( $item->email, 30 );
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
} else {
?>
	<tr>
		<td>
			<?php _e( 'No reports found.', 'cp' ); ?>
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
