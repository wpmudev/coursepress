<?php
$redirect_url = '';
if ( ! empty( $_REQUEST['redirect_url'] ) ) {
	$redirect_url = $_REQUEST['redirect_url'];
}
echo do_shortcode( '[course_signup page="login" login_title="" redirect_url="' . $redirect_url . '" signup_url="' . CoursePress::instance()->get_signup_slug( true ) . '" logout_url="' . CoursePress::instance()->get_signup_slug( true ) . '"]' );
