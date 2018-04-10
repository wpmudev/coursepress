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
			$ext = substr( $this->zip_url, strrpos( $this->zip_url, '.' ) + 1 );

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
		add_filter( 'filesystem_method', array( $this, 'filesystem_method' ) );
		WP_Filesystem();
		remove_filter( 'filesystem_method', array( $this, 'filesystem_method' ) );
		$subdir = str_replace( $upload_dir['baseurl'], '', $path );
		$subdir = explode( '/', $subdir );
		$filename = array_pop( $subdir );
		$subdir = implode( '/', $subdir );
		$src_path = untrailingslashit( $upload_dir['basedir'] ) . trailingslashit( $subdir ) . $filename . '.' . $extension;
		$object_dir = untrailingslashit( $upload_dir['basedir'] ) . trailingslashit( $subdir ) . 'objects/' . $filename;
		$file = $this->module->__get( 'meta_primary_file' );
		$file_path = trailingslashit( $upload_dir['path'] ).trailingslashit( 'objects' ).trailingslashit( $filename ).$file;
		$file = trailingslashit( $upload_dir['url'] ).trailingslashit( 'objects' ).trailingslashit( $filename ).$file;
		$unzipfile = true;
		// Presume that its not unzipped yet.
		if ( ! file_exists( $file_path ) ) {
			if ( ! defined( 'FS_CHMOD_DIR' ) ) {
				define( 'FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
			}
			if ( ! file_exists( $object_dir ) ) {
				wp_mkdir_p( $object_dir );
			}
			// Unzip it
			$unzipfile = unzip_file( $src_path, $object_dir );
		}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title><?php echo esc_html( $this->module->__get( 'post_title' ) );?></title>
        <style type="text/css">
        /* <![CDATA[ */
body {
    margin:0;
    padding: 0;
}
.cp-zipped-container {
    position: fixed;
}
.cp-zipped-container a {
    padding: 0 5px;
    font-size: 12px;
    text-decoration: none;
    opacity: 0.3;
    background: #3c3c3c;
    color: #fff;
    font-family: helvetica, sans-serif;
    display: block;
    line-height: 20px;
    float: left;
}
        /* ]]> */
        </style>
    </head>
    <body>
    <div class="cp-zipped-container"><a href="<?php echo esc_url( wp_get_referer() . $append_url ); ?>"><?php esc_html_e( '&laquo; Back to Course', 'cp' );?></a></div><?php

	if ( is_wp_error( $unzipfile ) ) {
		echo '<p>&nbsp;</p>';
		if ( current_user_can( 'manage_options' ) ) {
			foreach ( $unzipfile->errors as $id => $messages ) {
				foreach ( $messages as $message ) {
					printf( '<p id="%s">%s</p>', esc_attr( $id ), esc_html( $message ) );
				}
			}
		} else {
				printf( '<p id="%s">%s</p>', 'error', esc_html__( 'Something went wrong!', 'cp' ) );
		}
	} elseif ( file_exists( $file_path ) ) {
		echo '<iframe style="margin:0; padding:0; border:none; width: 100%; height: 100vh;" src="' .esc_url( $file ) . '"></iframe>';
	} else {
		printf( '<p id="%s">%s</p>', 'error', esc_html__( 'Somthing went wrong!', 'cp' ) );
	}
?>
</body>
</html>
<?php
		exit();
	}

	public function filesystem_method( $method ) {
		return 'direct';
	}
}
