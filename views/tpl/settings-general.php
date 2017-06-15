<script type="text/template" id="coursepress-general-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'General', 'cp' ); ?></h2>
	</div>
<?php
/**
 * Fire to get all options.
 *
 * @since 3.0
 * @param array $extensions
 */
$option_name = sprintf( 'coursepress_%s', basename( __FILE__, '.php' ) );
$options = apply_filters( $option_name, array() );
foreach ( $options as $option ) {
?>
<div class="cp-box-content">
    <div class="box-label-area">
        <h2 class="label"><?php echo $option['title']; ?></h2>
    </div>
    <div class="box-inner-content">
<?php
foreach ( $option['fields'] as $key => $data ) {
?>
	<div class="option option-<?php esc_attr_e( $key ); ?>">
		<h3><?php echo $data['label']; ?></h3>
<?php
	$data['name'] = $key;
	lib3()->html->element( $data );
?>
	</div>
<?php
}
?>
    </div>
</div>
<?php
}
?>
</script>
