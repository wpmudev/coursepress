<?php
global $coursepress;
//$coursepress->register_external_plugins();
if ( ! CoursePress_Capabilities::is_campus() ) {
	$activation = new CP_Plugin_Activation();
	$activation->install_plugins_page();
}
