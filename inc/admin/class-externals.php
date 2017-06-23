<?php
/**
 * Load externals
 *
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Externals{

	public function __construct() {
		include_once dirname( dirname( __FILE__ ) ).'/external/wpmu-lib/core.php';
		lib3()->ui->add( 'core', 'admin.php?page=coursepress_settings' );
		lib3()->ui->add( 'html', 'admin.php?page=coursepress_settings' );
	}
}
