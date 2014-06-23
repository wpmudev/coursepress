<?php
global $action, $page;
global $page, $user_id, $coursepress_admin_notice;
global $coursepress;

$notification_id = '';

if ( isset( $_GET['notification_id'] ) ) {
    $notification = new Notification( $_GET['notification_id'] );
    $notification_details = $notification->get_notification();
    $notification_id = ( int )$_GET['notification_id'];
} else {
    $notification = new Notification();
    $notification_id = 0;
}

wp_reset_vars( array( 'action', 'page' ) );

if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'add' || $_POST['action'] == 'update' ) ) {

    check_admin_referer( 'notifications_details' );

    $new_post_id = $notification->update_notification();

    if ( $new_post_id !== 0 ) {
        ob_start();
        wp_redirect( admin_url( 'admin.php?page=' . $page . '&notification_id=' . $new_post_id . '&action=edit' ) );
        exit;
    } else {
        //an error occured
    }
}

if ( isset( $_GET['notification_id'] ) ) {
    $meta_course_id = $notification->details->course_id;
} else {
    $meta_course_id = '';
}
?>

<div class="wrap nosubsub">
    <div class="icon32" id="icon-themes"><br></div>

    <h2><?php _e( 'Notification', 'cp' ); ?><?php if ( current_user_can( 'coursepress_create_notification_cap' ) ) { ?><a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=notifications&action=add_new' );?>"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?></h2>

    <?php
    $message['ca'] = __( 'New Notification added successfully!', 'cp' );
    $message['cu'] = __( 'Notification updated successfully.', 'cp' );
    ?>

    <div class='wrap nocoursesub'>
        <form action='<?php echo esc_attr( admin_url( 'admin.php?page='.$page.( ( $notification_id !== 0 ) ? '&notification_id=' . $notification_id : '' ) . '&action=' . $action. ( ( $notification_id !== 0 ) ? '&ms=cu' : '&ms=ca' ) ) );?>' name='notification-add' method='post'>

            <div class='course-liquid-left'>

                <div id='course-full'>

                    <?php wp_nonce_field( 'notifications_details' ); ?>

                    <?php if ( isset( $notification_id ) ) { ?>
                        <input type="hidden" name="notification_id" value="<?php echo esc_attr( $notification_id ); ?>" />
                        <input type="hidden" name="action" value="update" />
                    <?php } else { ?>
                        <input type="hidden" name="action" value="add" />
                    <?php } ?>

                    <div id='edit-sub' class='course-holder-wrap'>
                        <div class='course-holder'>
                            <div class='course-details'>
                                <label for='notification_name'><?php _e( 'Notify Students in selected courses', 'cp' ); ?></label>
                                <p><?php _e( 'Notifications are shown to end users in their Notifications menu item', 'cp' ); ?></p>

                                <div class="full">
                                    <label><?php _e( 'Course', 'cp' ); ?></label>
                                    <select name="meta_course_id" class="chosen-select">
                                        <option value="" <?php selected( $meta_course_id, '' ); ?>><?php _e( 'All Courses', 'cp' ); ?></option>
                                        <?php
                                        $args = array(
                                            'post_type' => 'course',
                                            'post_status' => 'any',
                                            'posts_per_page' => -1
                                        );

                                        $courses = get_posts( $args );

                                        foreach ( $courses as $course ) {
                                            ?>
                                            <option value="<?php echo $course->ID; ?>" <?php selected( $meta_course_id, $course->ID ); ?>><?php echo $course->post_title; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>

                                </div>
                                <br clear="all" />

                                <label for='notification_name'><?php _e( 'Notification Title', 'cp' ); ?></label>
                                <input class='wide' type='text' name='notification_name' id='notification_name' value='<?php
                                if ( isset( $_GET['notification_id'] ) ) {
                                    echo esc_attr( stripslashes( $notification->details->post_title ) );
                                }
                                ?>' />

                                <br/><br/>
                                <label for='course_name'><?php _e( 'Notification Content', 'cp' ); ?></label>
                                <?php
                                $args = array( "textarea_name" => "notification_description", "textarea_rows" => 10 );
                                wp_editor( htmlspecialchars_decode( isset( $notification->details->post_content ) ? $notification->details->post_content : '' ), "notification_description", $args );
                                ?>
                                <br/>

                                <br clear="all" />


                                <div class="buttons">
                                    <?php
                                    if ( ( $notification_id == 0 && current_user_can( 'coursepress_create_notification_cap' ) ) || ( $notification_id != 0 && current_user_can( 'coursepress_update_notification_cap' ) ) || ( $notification_id != 0 && current_user_can( 'coursepress_update_my_notification_cap' ) && $notification_details->post_author == get_current_user_id() ) ) {//do not show anything
                                        ?>
                                        <input type="submit" value = "<?php ( $notification_id == 0 ? _e( 'Create', 'cp' ) : _e( 'Update', 'cp' ) ); ?>" class = "button-primary" />
                                        <?php
                                    } else {
                                        _e( 'You do not have required permissions for this action' );
                                    }
                                    ?>
                                </div>

                                <br clear="all" />

                            </div>



                        </div>
                    </div>

                </div>
            </div> <!-- course-liquid-left -->
        </form>

    </div> <!-- wrap -->
</div>
