<?php
/**
 * @var array $students
 */
?>
<script type="text/template" id="coursepress-students-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Course Students', 'cp' ); ?></h2>
	</div>
    <ul class="subsubsub">
        <?php echo implode( '<li>|</li>', $statuses ); ?>
    </ul>
    <div class="tablenav top">
        <div class="alignleft actions bulkactions cp-flex">
        <label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'cp' ); ?></label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1"><?php esc_html_e( 'Bulk Actions', 'cp' ); ?></option>
                <option value="delete"><?php esc_html_e( 'Delete', 'cp' ); ?></option>
            </select>
            <input type="submit" id="doaction" class="button action cp-btn" value="<?php esc_attr_e( 'Apply', 'cp' ); ?>" />
        </div>
        <br class="clear">
    </div>
    <table class="coursepress-table">
        <thead>
        <tr>
                <th class="column-cb"><input type="checkbox" /></th>
                <th class="column-student"><?php _e( 'Student', 'cp' ); ?></th>
                <th class="column-certified"><?php _e( 'Certified', 'cp' ); ?></th>
                <th class="column-withdraw"><?php _e( 'Withdraw', 'cp' ); ?></th>
            </tr>
        </thead>
        <tbody id="coursepress-table-students">
            <?php if ( count( $students ) > 0 ) { ?>
                <?php foreach ( $students as $student ) { ?>
                <tr id="student-<?php echo esc_attr( $student->ID ); ?>">
                    <td class="check-column"><input type="checkbox" name="bulk-actions[]" value="<?php esc_attr_e( $student->ID ); ?>" /></td>
                    <td>
                        <div class="cp-flex cp-user">
                            <span class="gravatar"> <?php echo $student->get_avatar( 30 ); ?></span>
                            <span class="user_login"><?php echo $student->user_login; ?></span>
                            <span class="display_name">(<?php echo $student->get_name(); ?>)</span>
                        </div>
                    </td>
                    <td class="cp-student-certified">
		                <?php
			                /**
			                 * @var array $certified_students
			                 */
			                $student_certified = in_array( $student->ID, $certified_students );
			                printf(
				                '<span class="dashicons dashicons-%s"></span>',
				                $student_certified ? 'yes' : 'no'
			                );
		                ?>
                    </td>
                    <td>
                        <a href="#" data-id="<?php echo esc_attr( $student->ID ); ?>" class="cp-btn cp-btn-xs cp-btn-active cp-btn-withdraw-student"><?php _e( 'Withdraw', 'cp' ); ?></a>
                    </td>
                </tr>
                <?php } ?>
            <?php } ?>
                <tr class="noitems <?php echo count( $students ) > 0? 'hidden':''; ?>">
                    <td colspan="4">
<?php if ( 1 > $all_student_count ) { ?>
                        <p><?php _e( 'There are currently no students enrolled to this course.', 'cp' ); ?></p>
                        <p><?php _e( 'You can invite students below or wait for them to enroll once the course is active.', 'cp' ); ?></p>
<?php } else {
	switch ( $show ) {
		case 'yes':
		?>
                        <p><?php _e( 'No student has completed this course yet.', 'cp' ); ?></p>
	<?php
	break;
		case 'no':
		?>
                        <p><?php _e( 'All enrolled students have completed this course.', 'cp' ); ?></p>
	<?php
	break;
		default:
		?>
                        <p><?php _e( 'Something went wrong.', 'cp' ); ?></p>
	<?php
	break;
	}
} ?>
                    </td>
                </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="add-student">
                    <div class="cp-flex">
                        <select id="add-student-select"></select>
                        <button id="add-student-button" class="cp-btn cp-btn-xs"><?php esc_html_e( 'Add Student', 'cp' ); ?></button>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
    <div class="tablenav cp-admin-pagination">
	    <?php $pagination->pagination( 'bottom' ); ?>
    </div>

    <div class="cp-flex cp-invitee-container">
        <div class="cp-content-box" id="student-invites">
            <table class="coursepress-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Invite Student ', 'cp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="cp-box">
                                <label class="label"><?php esc_html_e( 'First Name', 'cp' ); ?></label>
                                <input type="text" name="first_name" class="widefat" placeholder="John" />
                            </div>
                            <div class="cp-box">
                                <label class="label"><?php esc_html_e( 'Last Name', 'cp' ); ?></label>
                                <input type="text" name="last_name" class="widefat" placeholder="Smith" />
                            </div>
                            <div class="cp-box">
                                <label class="label"><?php esc_html_e( 'Email', 'cp' ); ?></label>
                                <input type="text" name="email" class="widefat" placeholder="johnsmith@example.net" />
                            </div>
                            <button type="button" class="cp-btn cp-btn-active send-invite"><i class="fa fa-circle-o-notch fa-spin"></i><?php esc_html_e( 'Send Invite', 'cp' ); ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="cp-content-box" id="invited-students">
            <table class="coursepress-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Student Name', 'cp' ); ?></th>
                        <th><?php esc_html_e( 'Email', 'cp' ); ?></th>
                        <th><?php esc_html_e( 'Date', 'cp' ); ?></th>
                    </tr>
                </thead>
                <tbody id="invited-list">
                    <?php if ( ! $invited_students ) : ?>
                    <tr class="no-invites">
                        <td colspan="3"><?php esc_html_e( 'No invited students found...', 'cp' ); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</script>

<script type="text/template" id="coursepress-invited-student">
    <td>{{first_name}} {{last_name}}</td>
    <td>{{email}}</td>
    <td>{{date}}</td>
</script>

<script type="text/template" id="coursepress-course-add-student">
    <td class="check-column"><input type="checkbox" name="bulk-actions[]" value="{{ID}}" /></td>
    <td>
        <div class="cp-flex cp-user">
            <span class="gravatar"><img alt="" src="{{gravatar_url}}" class="avatar avatar-30 photo" height="30" width="30"></span>
            <span class="user_login">{{user_login}}</span>
            <span class="display_name">({{display_name}})</span>
        </div>
    </td>
    <td>
        <span class="dashicons dashicons-no"></span>
    </td>
    <td><a href="#" data-id="{{ID}}" class="cp-btn cp-btn-xs cp-btn-active cp-btn-withdraw-student"><?php esc_html_e( 'Withdraw', 'cp' ); ?></a></td>
</script>
