<?php
/**
 * @var array $students
 */
?>
<script type="text/template" id="coursepress-students-tpl">
    <table class="coursepress-table">
        <thead>
            <tr>
                <th class="column-student"><?php _e( 'Student', 'cp' ); ?></th>
                <th class="column-certified"><?php _e( 'Certified', 'cp' ); ?></th>
                <th class="column-withdraw"><?php _e( 'Withdraw', 'cp' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( count( $students ) > 0 ) : ?>
                <?php foreach ( $students as $student ) : ?>
                <tr>
                    <td>
                        <div class="cp-flex cp-user">
                            <span class="gravatar"> <?php echo $student->get_avatar( 30 ); ?></span>
                            <span class="user_login"><?php echo $student->user_login; ?></span>
                            <span class="display_name">(<?php echo $student->get_name(); ?>)</span>
                        </div>
                    </td>
                    <td></td>
                    <td>
                        <a href="<?php echo esc_url_raw( add_query_arg( 'student_id', $student->ID ) ); ?>" class="cp-btn cp-btn-xs cp-btn-active"><?php _e( 'Withdraw', 'cp' ); ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3"><?php _e( 'There are no enrolled students to this course.', 'cp' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="tablenav cp-admin-pagination">
	    <?php $pagination->pagination( 'bottom' ); ?>
    </div>

    <div class="cp-flex cp-invitee-container">
        <div class="cp-content-box" id="student-invites">
            <div class="cp-box">
                <label class="label"><?php _e( 'First Name', 'cp' ); ?></label>
                <input type="text" name="first_name" class="widefat" placeholder="John" />
            </div>
            <div class="cp-box">
                <label class="label"><?php _e( 'Last Name', 'cp' ); ?></label>
                <input type="text" name="last_name" class="widefat" placeholder="Smith" />
            </div>
            <div class="cp-box">
                <label class="label"><?php _e( 'Email', 'cp' ); ?></label>
                <input type="text" name="email" class="widefat" placeholder="johnsmith@example.net" />
            </div>
            <button type="button" class="cp-btn cp-btn-active send-invite">
                <i class="fa fa-circle-o-notch fa-spin"></i>
                <?php _e( 'Send Invite', 'cp' ); ?>
            </button>
        </div>

        <div class="cp-content-box" id="invited-students">
            <table class="coursepress-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Student Name', 'cp' ); ?></th>
                        <th><?php _e( 'Date', 'cp' ); ?></th>
                    </tr>
                </thead>
                <tbody id="invited-list">
                    <?php if ( ! $invited_students ) : ?>
                    <tr class="no-invites">
                        <td colspan="2"><?php _e( 'No invited students found...', 'cp' ); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</script>

<script type="text/template" id="coursepress-invited-student">
    <td>{{first_name}} {{last_name}}</td>
    <td>{{date}}</td>
</script>
