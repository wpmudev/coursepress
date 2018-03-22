<script type="text/template" id="coursepress-mini-visual-editor">
	<?php
	// Create a single wp-editor instance.
	$wp_editor_settings = array(
		'textarea_name' => 'dummy_editor_name',
		'wpautop'       => true,
		'media_buttons' => false
	);
	wp_editor( 'dummy_editor_content', 'dummy_editor_id', $wp_editor_settings );
	?>
</script>
