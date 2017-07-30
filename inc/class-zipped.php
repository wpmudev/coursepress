<?php
/**
 * Class CoursePress_Zipped
 */
class CoursePress_Zipped extends CoursePress_Utility {
	var $module_id = 0;
	var $zip_url = '';
	var $file = '';

	public function __construct( $module_id ) {
		if ( ! empty( $module_id ) ) {
			$this->module = new CoursePress_Step_Zip( $module_id );

			$this->zip_url = $this->module->__get( 'zip_url' );
			$this->file = $this->module->__get( 'primary_file' );
			$ext = substr( $this->zip_url, strrpos( $this->zip_url, '.' )+1 );

			if ( ! empty( $this->zip_url ) && 'zip' == $ext ) {
				$this->view_document();
			}
		}
	}

	function view_document() {
		ob_start();

		$requested_file = $this->zip_url;

		$module_id = $this->module->__get( 'ID' );
		$append_url = ! empty( $module_id ) ? '#module-' . $module_id : '';

		// Unzipping the magic
		$upload_dir = wp_upload_dir();

		$path = explode( '.', $requested_file );
		$extension = array_pop( $path );
		$path = implode( '.', $path );

		if ( 'zip' !== strtolower( $extension ) ) {
			exit();
		}

		// Get access to zip functions
		require_once ABSPATH .'/wp-admin/includes/file.php'; //the cheat
		WP_Filesystem();

		$subdir = str_replace( $upload_dir['baseurl'], '', $path );
		$subdir = explode( '/', $subdir );
		$filename = array_pop( $subdir );
		$subdir = implode( '/', $subdir );

		$src_path = untrailingslashit( $upload_dir['basedir'] ) . trailingslashit( $subdir ) . $filename . '.' . $extension;
		$object_dir = trailingslashit( untrailingslashit( $upload_dir['basedir'] ) . trailingslashit( $subdir ) . 'objects/' . $filename );
		$file = $_GET['file'];
		$file_path = $object_dir . $file;
		$file_url_base = trailingslashit( str_replace( $filename, '', $path ) ) . trailingslashit( 'objects' ) . trailingslashit( $filename );
		$file_url = $file_url_base . $file;


		// Presume that its not unzipped yet.
		if ( ! file_exists( $object_dir ) || ! file_exists( $file_path ) ) {
			// Unzip it
			$unzipfile = unzip_file( $src_path, $object_dir );
		}

		echo '<a href="' . esc_url_raw( wp_get_referer() ) . $append_url . '" style="padding: 5px; font-size: 12px; text-decoration: none; opacity: 0.3; background: #3C3C3C; color: #fff; font-family: helvetica, sans-serif; position: absolute; top: 2; left: 2;"> &laquo; ' . esc_html__( 'Back to Course', 'CP_TD' ) . '</a>';

		if ( file_exists( $file_path ) ) {
			echo '<iframe style="margin:0; padding:0; border:none; width: 100%; height: 100vh;" src="' .$file_url . '"></iframe>';
		} else {
			// file not there? try redirect and should go to 404
			//wp_safe_redirect( $file_url );
		}
		exit();
	}
}