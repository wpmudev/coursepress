<?php
/**
 * Class to show HTML
 *
 * @since 2.0.5
 *
 */
class CoursePress_Helper_HTML {
	/**
	 * Show HTML
	 *
	 * @since 2.0.5
	 *
	 * @param string $html HTML to show.
	 * @param array $args Configuration args.
	 */
	public static function make( $html, $args ) {
		_wp_admin_html_begin();
		printf( '<title>%s - %s</title>', esc_html( $args['footer'] ), esc_attr( $args['header']['title'] ) );
		echo '</head>';
		echo '<body>';
		printf( '<h1>%s</h1>', $args['header']['title'] );
		echo '<hr />';
		echo $html;
		echo '<hr />';
		printf( '<small style="float: right;">%s</small>', date_i18n( get_option( 'date_format' ), time() ) );
		printf( '<small>%s</small>', $args['footer'] );
		echo '</body>';
		echo '</html>';
	}
}
