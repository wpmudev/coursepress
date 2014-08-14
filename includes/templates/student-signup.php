<?php
$redirect_url = '';
if ( ! empty( $_REQUEST['redirect_url'] ) ) {
	$redirect_url = $_REQUEST['redirect_url'];
}
do_shortcode('[course_signup page="signup" signup_title="" redirect_url="' . $redirect_url . '" login_url="' . CoursePress::instance()->get_login_slug(true) . '"] logout_url="' . CoursePress::instance()->get_signup_slug(true) . '"]');