<?php
$redirect_url = '';
if ( ! empty( $_REQUEST['redirect_url'] ) ) {
	$redirect_url = esc_url_raw( urldecode( $_REQUEST['redirect_url'] ) );
}
echo do_shortcode( '[course_signup page="signup" signup_title="" redirect_url="' . $redirect_url . '" login_url="' . CoursePress::instance()->get_login_slug( true ) . '"]' );