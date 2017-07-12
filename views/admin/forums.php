<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course_edit_link
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap coursepress-forums" id="coursepress-forums">
	<h1 class="wp-heading-inline"><?php _e( 'Forums', 'cp' ); ?></h1>
    <div class="coursepress-page">
        <form method="get" class="cp-search-form" id="cp-search-form">
            <div class="cp-flex">
                <div class="cp-div">
                    <label class="label"><?php _e( 'Filter by course', 'cp' ); ?></label>
                    <select name="course_id">
                        <option value=""><?php _e( 'Any course', 'cp' ); ?></option>
<?php
$current = isset( $_REQUEST['course_id'] )? $_REQUEST['course_id']:0;
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
            </div>
        </form>

        <table class="coursepress-table" cellspacing="0">
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
$i = 0;
if ( ! empty( $forums ) ) {
	$date_format = get_option( 'date_format' );
	foreach ( $forums as $forum ) {


		l( $forum );

			$i++;
			$edit_link = add_query_arg( 'cid', $forum->ID, $forum_edit_link );
?>
                        <tr class="<?php echo $i % 2? 'odd' : 'even'; ?>">

                            <?php foreach ( array_keys( $columns ) as $column_id ) { ?>
                                <td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
<?php
switch ( $column_id ) :
	case 'topic' :
		echo $forum->post_title;
	break;
	case 'id' :
		echo $forum->ID;
	break;

	default :
		echo $column_id;
		/**
				 * Trigger to allow custom column value
				 *
				 * @since 3.0
				 * @param string $column_id
				 * @param CoursePress_Course object $forum
				 */
		do_action( 'coursepress_courselist_column', $column_id, $forum );
	break;
endswitch;
?>
                                </td>
                            <?php } ?>
                        </tr>
<?php
	}
} else {
?>
                    <tr class="odd">
                        <td>
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

