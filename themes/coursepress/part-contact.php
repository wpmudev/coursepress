<?php
$form_message_class = '';
$form_message = '';

if ( isset( $_POST['contact_submit_button'] ) ) {

    check_admin_referer( 'contact_submit' );
    
    do_action( 'before_contact_validation' );
    
    if ( $_POST['sender_name'] != '' && $_POST['sender_email'] != '' && $_POST['subject'] != '' && $_POST['message'] ) {

        if ( is_email( $_POST['sender_email'] ) ) {

            add_filter( 'wp_mail_from', 'coursepress_set_sender_from_email' );

            function coursepress_set_sender_from_email( $email ) {
                return $_POST['sender_email'];
            }

            add_filter( 'wp_mail_from_name', 'coursepress_set_sender_from_name' );

            function coursepress_set_sender_from_name( $name ) {
                return $_POST['sender_name'];
            }

            if ( wp_mail( get_option( 'admin_email' ), $_POST['subject'], $_POST['message'] ) ) {
                $form_message = __( 'E-mail sent successfully! We will respond as soon as possible.', 'cp' );
                $form_message_class = 'regular';
            } else {
                $form_message = __( 'An error occured while trying to send the e-mail. Please try again later.', 'cp' );
                $form_message_class = 'error';
            }
        } else {
            $form_message = __( 'E-mail address is not valid.', 'cp' );
            $form_message_class = 'error';
        }
    } else {
        $form_message = __( 'All fields are required.', 'cp' );
        $form_message_class = 'error';
    }
}
?>

<?php if ( isset( $form_message_class ) ) {
    ?>
    <p class="form-info-<?php echo $form_message_class; ?>"><?php echo $form_message; ?></p>
<?php } ?>
<?php do_action( 'before_contact_form' ); ?>
<form id="contact_form" name="contact-form" method="post" class="contact-form">

    <label class="full">
        <?php _e( 'Your Name', 'cp' ); ?>:
        <input type="text" name="sender_name" value="" />
    </label>
    <?php do_action( 'after_contact_name' ); ?>
    <label class="full">
        <?php _e( 'Your E-mail', 'cp' ); ?>:
        <input type="text" name="sender_email" value="" />
    </label>
    <?php do_action( 'after_contact_email' ); ?>
    <label class="full">
        <?php _e( 'Subject', 'cp' ); ?>:
        <input type="text" name="subject" value="" />
    </label>
    <?php do_action( 'after_contact_subject' ); ?>
    <label class="right">
        <?php _e( 'Message', 'cp' ); ?>:
        <textarea name="message"></textarea>
    </label>
    <?php do_action( 'after_contact_message' ); ?>
    <input type="submit" name="contact_submit_button" class="apply-button-enrolled" value="<?php _e( 'Send', 'cp' ); ?>" />

    <?php wp_nonce_field( 'contact_submit' ); ?>
</form>
<?php do_action( 'after_contact_form' ); ?>