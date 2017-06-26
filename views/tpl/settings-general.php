<script type="text/template" id="coursepress-general-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'General', 'cp' ); ?></h2>
	</div>
<?php
$option_name = sprintf( 'coursepress_%s', basename( __FILE__, '.php' ) );
$GLOBALS['CoursePress_Admin_Configuration']->print_options( $option_name );
?>
</script>
