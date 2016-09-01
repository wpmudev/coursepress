<?php
/**
 * Debugger Class
 *
 * Use for development only and should not be included to any release!
 **/
class CoursePress_Debugger {
	public static function log( $error_message = '' ) {
		$time = date_i18n( 'M d, Y @H:i a', time() );

		$message = "\n" . '[' . $time . '] ' . $error_message;

		ob_start();
		debug_print_backtrace();

		$message .= "\n";
		$message .= ob_get_clean();

		error_log( $message );
	}
}