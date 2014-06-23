<?php
//Set a cookie now to see if they are supported by the browser.
setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
if ( SITECOOKIEPATH != COOKIEPATH )
    setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
?>
    <?php do_action( 'cp_before_login_form' ); ?>
<form name="loginform" id="student-settings" class="student-settings" action="<?php echo get_option( 'siteurl' ); ?>/wp-login.php" method="post">
<?php do_action( 'cp_after_start_form_fields' ); ?>

    <label>
<?php _e( 'Username', 'cp' ); ?>:
        <input type="text" name="log" value="<?php echo ( isset( $_POST['log'] ) ? $_POST['log'] : '' ); ?>" />
    </label>

    <label>
<?php _e( 'Password', 'cp' ); ?>:
        <input type="password" name="pwd" value="<?php echo ( isset( $_POST['pwd'] ) ? $_POST['pwd'] : '' ); ?>" />
    </label>

<?php do_action( 'cp_form_fields' ); ?>

    <label class="full-right"><br>
        <input type="submit" name="wp-submit" id="wp-submit" class="apply-button-enrolled" value="<?php _e( 'Log In', 'cp' ); ?>"><br>
    </label>
    <br clear="all" />

    <input name="rememberme" id="rememberme" value="forever" tabindex="90" type="checkbox"> <span><?php _e( 'Remember Me?', 'cp' ); ?> </span>
    <input name="redirect_to" value="<?php echo $this->get_student_dashboard_slug( true ); ?>" type="hidden">
    <input name="testcookie" value="1" type="hidden">
<?php do_action( 'cp_before_end_form_fields' ); ?>
</form>

<?php do_action( 'cp_after_login_form' ); ?>

<?php
/* } else {
  if ( isset( $this ) ) {
  //ob_start( );
  wp_redirect( $this->get_student_dashboard_slug( true ) );
  exit;
  }
  } */
?>