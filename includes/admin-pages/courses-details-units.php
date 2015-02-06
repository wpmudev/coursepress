<?php
global $coursepress;

$course_id = '';
$unit_id   = '';

if ( isset( $_GET['course_id'] ) && is_numeric( $_GET['course_id'] ) ) {
	$course_id = ( int ) $_GET['course_id'];
	$course    = new Course( $course_id );
	$units     = $course->get_units();
}

if ( ! empty( $course_id ) && ! CoursePress_Capabilities::can_view_course_units( $_GET['course_id'] ) ) {
	die( __( 'You do not have required permissions to access this page.', 'cp' ) );
}

if ( isset( $_GET['unit_id'] ) ) {
	$unit_id = ( int ) $_GET['unit_id'];
	$unit    = new Unit( $unit_id );
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete_unit' && isset( $_GET['unit_id'] ) && is_numeric( $_GET['unit_id'] ) ) {
	$unit        = new Unit( $unit_id );
	$unit_object = $unit->get_unit();
	if ( CoursePress_Capabilities::can_delete_course_unit( $course_id, $unit_id ) ) {
		$unit->delete_unit( $force_delete = true );
	}
	$units = $course->get_units();
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'change_status' && isset( $_GET['unit_id'] ) && is_numeric( $_GET['unit_id'] ) ) {
	$unit        = new Unit( $_GET['unit_id'] );
	$unit_object = $unit->get_unit();
	if ( CoursePress_Capabilities::can_change_course_unit_status( $course_id, $unit_id ) ) {
		$unit->change_status( $_GET['new_status'] );
	}
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'add_new_unit' || ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && isset( $_GET['unit_id'] ) ) ) {

	$coursepress->unit_page_num        = ! empty( $_REQUEST['unit_page_num'] ) ? ( int ) $_REQUEST['unit_page_num'] : 1;
	$coursepress->active_element       = isset( $_REQUEST['active_element'] ) ? $_REQUEST['active_element'] : ( $coursepress->unit_page_num == 1 ? 0 : 1 );
	$coursepress->preview_redirect_url = isset( $_REQUEST['preview_redirect_url'] ) ? $_REQUEST['preview_redirect_url'] : '';
	$this->show_unit_details( $coursepress->unit_page_num, $coursepress->active_element, $coursepress->preview_redirect_url );
} else {
	$first_unit_id = isset( $units[0]->ID ) ? $units[0]->ID : '';
	// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
	if ( isset( $first_unit_id ) && is_numeric( $first_unit_id ) ) {
		wp_redirect( admin_url( "admin.php?page=course_details&tab=units&course_id=" . $course_id . "&unit_id=" . $first_unit_id . "&action=edit" ) );
		exit;
	} else {
		wp_redirect( admin_url( "admin.php?page=course_details&tab=units&course_id=" . $course_id . "&action=add_new_unit" ) );
		exit;
	}
	?>

	<ul id="sortable-units">
		<?php
		$list_order = 1;
		foreach ( $units as $unit ) {

			$unit_object = new Unit( $unit->ID );
			$unit_object = $unit_object->get_unit();
			?>
			<li class="postbox ui-state-default clearfix">
				<div class="unit-order-number">
					<div class="numberCircle"><?php echo $list_order; ?></div>
				</div>
				<div class="unit-title">
					<a href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&unit_id=' . $unit_object->ID . '&action=edit' ) ?>"><?php echo $unit_object->post_title; ?></a>
				</div>
				<div class="unit-description"><?php echo cp_get_the_course_excerpt( $unit_object->ID, 28 ); ?></div>

				<?php if ( CoursePress_Capabilities::can_delete_course_unit( $course_id, $unit_object->ID ) ) { ?>
					<div class="unit-remove">
						<a href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&unit_id=' . $unit_object->ID . '&action=delete_unit' ); ?>" onClick="return removeUnit();">
							<i class="fa fa-times-circle cp-move-icon remove-btn"></i>
						</a></div>
				<?php } ?>

				<div class="unit-buttons unit-control-buttons">
					<a href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&unit_id=' . $unit_object->ID . '&action=edit' ); ?>" class="button button-units save-unit-button"><?php _e( 'Settings', 'cp' ); ?></a>
					<?php if ( CoursePress_Capabilities::can_change_course_unit_status( $course_id, $unit_object->ID ) ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&unit_id=' . $unit_object->ID . '&action=change_status&new_status=' . ( $unit_object->post_status == 'unpublished' ) ? 'publish' : 'private' ); ?>" class="button button-<?php echo ( $unit_object->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>"><?php echo ( $unit_object->post_status == 'unpublished' ) ? __( 'Publish', 'cp' ) : __( 'Unpublish', 'cp' ); ?></a>
					<?php } ?>
				</div>

				<input type="hidden" class="unit_order" value="<?php echo $list_order; ?>" name="unit_order_<?php echo $unit_object->ID; ?>"/>
				<input type="hidden" name="unit_id" class="unit_id" value="<?php echo $unit_object->ID; ?>"/>
			</li>
			<?php
			$list_order ++;
		}
		?>
	</ul>
	<?php if ( CoursePress_Capabilities::can_create_course_unit( $course_id ) ) { ?>
		<ul>
			<li class="postbox ui-state-fixed ui-state-highlight add-new-unit-box">
				<div class="add-new-unit-title">
					<span class="plusTitle"><a href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&action=add_new_unit' ); ?>"><?php _e( 'Add new Unit', 'cp' ); ?></a></span>
				</div>
			</li>
		</ul>
	<?php } ?>

<?php } ?>
