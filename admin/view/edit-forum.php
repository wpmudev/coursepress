<?php

CoursePress_Admin_Forums::init_edit();

$post_type = CoursePress_Data_Discussion::get_post_type_name();
$the_id = ! empty( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : 0;

$post = CoursePress_Helper_Utility::get_post_by_post_type( $post_type, $the_id );

$title = CoursePress_Admin_Forums::get_label_by_name( 'add_new_item' );
if ( 0 < $the_id ) {
	$title = CoursePress_Admin_Forums::get_label_by_name( 'edit_item' );
}

do_action( 'add_meta_boxes', $post_type, $post );
do_action( 'do_meta_boxes', $post_type, 'side', $post );

?>
<div class="wrap coursepress_wrapper course-edit-forums">
<h2><?php
echo $title;
CoursePress_Admin_Forums::add_button_add_new();
?></h2>
	<hr />
	<form method="post" class="edit">
<?php
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false, false );
wp_nonce_field( 'edit_discussion' );
?>
		<input type="hidden" name="post_status" value="<?Php echo esc_attr( $post->post_status ); ?>" />
		<input type="hidden" id="post_ID" name="post_ID" value="<?php echo esc_attr( $the_id ); ?>" />
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
			<div id="post-body-content">
				<?php echo CoursePress_Helper_UI::get_admin_edit_title_field( $post->post_title, __( 'Enter topic title', 'coursepress' ) ); ?>
				<br />
				<div id="postdivrich" class="postarea wp-editor-expand">
					<?php
					$editor_name = 'post_content';
					$editor_id = 'postContent';
					$args = array(
						'textarea_name' => $editor_name,
						'editor_class' => 'cp-editor',
						'textarea_rows' => 10,
						'tinymce' => array(
							'height' => 350,
						),
					);
					// Filter $args
					$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
					wp_editor( $post->post_content, $editor_id, $args );
					?>
				</div>
			</div>
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( $post_type, 'side', $post ); ?>
			</div>
		</div>
	</form>
</div>
