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
                        <div class="cp-flex">
                            <span class="gravatar">
                                <?php echo get_avatar( $student->ID, 30 ); ?>
                            </span>
                            <span class="user_login">
                                <?php echo $student->user_login; ?>
                            </span>
                            <span class="display_name">
                                <?php echo $student->get_name(); ?>
                            </span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="cp-content-box" id="invited-students">
    </div>

    <div class="cp-content-box" id="student-invites">
        <div class="cp-box">
            <label class="label"><?php _e( 'First Name', 'cp' ); ?></label>
            <input type="text" name="first_name" class="widefat" placeholder="Jhon" />
        </div>
        <div class="cp-box">
            <label class="label"><?php _e( 'Last Name', 'cp' ); ?></label>
            <input type="text" name="last_name" class="widefat" placeholder="Smith" />
        </div>
        <div class="cp-box">
            <label class="label"><?php _e( 'Email', 'cp' ); ?></label>
            <input type="text" name="email" class="widefat" placeholder="jhonsmith@example.net" />
        </div>
        <button type="button" class="cp-btn cp-btn-active send-invite"><?php _e( 'Send Invite', 'cp' ); ?></button>
    </div>
</script>