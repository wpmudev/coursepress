<?php
global $action, $page;
wp_reset_vars( array( 'action', 'page' ) );

$wp_course_search = new Course_Search();

if ( $wp_course_search->is_light ) {
	if ( $wp_course_search->get_count_of_all_courses() >= 10 && ! isset( $_GET['course_id'] ) ) {
		wp_redirect( admin_url( 'admin.php?page=courses' ) );
		exit;
	}
}

$unit_id   = '';
$course_id = '';

if ( isset( $_GET['course_id'] ) && is_numeric( $_GET['course_id'] ) ) {
	$course_id = ( int ) $_GET['course_id'];
}

if ( isset( $_GET['unit_id'] ) && is_numeric( $_GET['unit_id'] ) ) {
	$unit_id = ( int ) $_GET['unit_id'];
}

$course = new Course( $course_id );

if ( empty( $course ) ) {
	$course = new StdClass;
} else {
	$course_object = $course->get_course();
}

$units          = $course->get_units();
$students_count = $course->get_number_of_students();
?>

<div class="wrap nosubsub course-details cp-wrap">
	<div class="icon32" id="icon-themes"><br></div>
	<?php
	$tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : '';
	if ( empty( $tab ) ) {
		$tab = 'overview';
	}
	?>

	<h2><?php
		if ( $course_id == '' ) {
			_e( 'New Course', 'coursepress_base_td' );
		}
		if ( $course_id != '' ) {
			_e( 'Course', 'coursepress_base_td' );
		}

		if ( ! isset( $_GET['course_id'] ) ) {
			$course          = new StdClass;
			$course->details = null;
		}

		if ( $course_id != '' ) {
			if ( $tab != 'overview' ) {
				echo ' &raquo; ' . $course->details->post_title . ' &raquo; ' . esc_html( ucfirst( $tab ) );
			} else {
				echo ' &raquo; ' . $course->details->post_title;
			}
		}
		?>
	</h2>

	<?php
	$message['ca']  = __( 'New Course added successfully!', 'coursepress_base_td' );
	$message['cu']  = __( 'Course updated successfully.', 'coursepress_base_td' );
	$message['usc'] = __( 'Unit status changed successfully', 'coursepress_base_td' );
	$message['ud']  = __( 'Unit deleted successfully', 'coursepress_base_td' );
	$message['ua']  = __( 'New Unit added successfully!', 'coursepress_base_td' );
	$message['uu']  = __( 'Unit updated successfully.', 'coursepress_base_td' );
	$message['as']  = __( 'Student added to the class successfully.', 'coursepress_base_td' );
	$message['ac']  = __( 'New class has been added successfully.', 'coursepress_base_td' );
	$message['dc']  = __( 'Selected class has been deleted successfully.', 'coursepress_base_td' );
	$message['us']  = __( 'Selected student has been withdrawed successfully from the course.', 'coursepress_base_td' );
	$message['usl'] = __( 'Selected students has been withdrawed successfully from the course.', 'coursepress_base_td' );
	$message['is']  = __( 'Invitation sent sucessfully.', 'coursepress_base_td' );
	$message['ia']  = __( 'Successfully added as instructor.', 'coursepress_base_td' );

	$error_message['wrong_email'] = __( 'Please enter valid e-mail address', 'coursepress_base_td' );

	if ( isset( $_GET['unit_id'] ) && isset( $_GET['new_status'] ) ) {
		$_GET['ms'] = 'usc';
	}

	if ( isset( $_GET['unit_id'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'delete_unit' ) {
		$_GET['ms'] = 'ud';
	}


	$ms = null;
	if ( isset( $_GET['ms'] ) ) {
		$ms = $_GET['ms'];
	}

	$ems = null;
	if ( isset( $_GET['ems'] ) ) {
		$ems = $_GET['ems'];
	}

	if ( isset( $ms ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo $message[ $ms ]; ?></p></div>
	<?php
	}

	if ( isset( $ems ) ) {
		?>
		<div id="message" class="error fade"><p><?php echo $error_message[ $ems ]; ?></p></div>
	<?php
	}
	?>

	<?php
	$menus             = array();
	$menus['overview'] = __( 'Course Overview', 'coursepress_base_td' );
	$menus['units']    = __( 'Units', 'coursepress_base_td' ) . ( count( $units ) >= 1 ? ' ( ' . count( $units ) . ' )' : '' );
	$menus['students'] = __( 'Students', 'coursepress_base_td' ) . ( $students_count >= 1 ? ' ( ' . $students_count . ' )' : '' );
	$menus             = apply_filters( 'coursepress_course_new_menus', $menus );
	?>

	<h3 class="nav-tab-wrapper">
		<?php
		foreach ( $menus as $key => $menu ) {
			if ( $key == 'overview' || ( $key != 'overview' && $course_id != '' ) ) {
				?>
				<a class="nav-tab<?php
				if ( $tab == $key ) {
					echo ' nav-tab-active';
				}
				?>" href="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page . '&amp;tab=' . $key . '&amp;course_id=' . $course_id ) ); ?>"><?php echo $menu; ?></a>
			<?php
			}
		}


		/* if ( $course_id != '' ) {
			 $course = new Course( $course_id );
			 if ( $course->can_show_permalink() ) {
			 ?>
			 <a class="nav-tab view-course-link" href="<?php echo get_permalink( $course_id ); ?>" target="_new"><?php _e( 'View Course', 'coursepress_base_td' ); ?></a>
			 <?php
			 }
			 } */
		?>

		<?php
		/* if ( $unit_id != '' ) {
		  $unit = new Course( $unit_id );
		  if ( $unit->can_show_permalink() ) {
		  ?>
		  <a class="nav-tab view-course-link" href="<?php echo get_permalink( $unit_id ); ?>" target="_new"><?php _e( 'View Unit', 'coursepress_base_td' );?></a>
		  <?php
		  }
		  } */
		?>
		<?php if ( isset( $course_id ) && $course_id !== '' ) { ?>

			<div class="course-state">
				<?php
				$can_publish = CoursePress_Capabilities::can_change_course_status( $course_id );
				$data_nonce  = wp_create_nonce( 'toggle-' . $course_id );
				?>
				<div id="course_state_id" data-id="<?php echo $course_id ?>" data-nonce="<?php echo $data_nonce; ?>"></div>
				<span class="publish-course-message"><?php _e( 'Publish Course', 'coursepress_base_td' ); ?></span>
				<span class="draft <?php echo ( $course_object->post_status == 'unpublished' ) ? 'on' : '' ?>"><i class="fa fa-ban"></i></span>

				<div class="control <?php echo $can_publish ? '' : 'disabled'; ?> <?php echo ( $course_object->post_status == 'unpublished' ) ? '' : 'on' ?>">
					<div class="toggle"></div>
				</div>
				<span class="live <?php echo ( $course_object->post_status == 'unpublished' ) ? '' : 'on' ?>"><i class="fa fa-check"></i></span>
			</div>
		<?php } ?>
	</h3>

	<?php
	switch ( $tab ) {

		case 'overview':
			$this->show_courses_details_overview();
			break;

		case 'units':
			$this->show_courses_details_units();
			break;

		case 'students':
			$this->show_courses_details_students();
			break;

		default:
			do_action( 'coursepress_courses_details_menu_' . $tab );
			break;
	}
	?>

</div>