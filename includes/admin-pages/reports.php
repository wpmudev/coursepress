<?php
global $coursepress, $wpdb;

$page = $_GET['page'];
$s    = ( isset( $_GET['s'] ) ? $_GET['s'] : '' );

/* * **************************GENERATING REPORT******************************** */
if ( isset( $_POST['units'] ) && isset( $_POST['users'] ) ) {

	ob_end_clean();
	ob_start();
	$course_id          = ( int ) $_POST['course_id'];
	$course             = new Course( $course_id );
	$course_units       = $course->get_units();
	$course_details     = $course->get_course();
	$units_filter       = $_POST['units'];
	$assessable_answers = 0;

	if ( is_numeric( $units_filter ) ) {
		$course_units    = array();
		$unit            = new Unit( $units_filter );
		$course_units[0] = $unit->get_unit();
	}

	$report_title = $course_details->post_title;

	if ( isset( $_POST['classes'] ) ) {
		$report_classes = $_POST['classes'];
		if ( $report_classes == '' ) {
			$report_classes = __( 'Default Class', 'cp' );
		} else {
			$report_classes .= __( ' Class', 'cp' );
		}
	} else {
		$report_classes = __( 'All Classes', 'cp' );
	}

	$report_title = $report_title .= ' | ' . $report_classes;
	?>
	<h1 style="text-align:center;"><?php echo $course_details->post_title; ?></h1>
	<hr/><br/>

	<?php
	$users_num = 0;
	foreach ( $_POST['users'] as $user_id ) {
		$current_row   = 0;
		$overall_grade = 0;
		$responses     = 0;

		$user_object = new Student( $user_id );
		?>
		<h2 style="text-align:center; color:#2396A0;"><?php echo $user_object->first_name . ' ' . $user_object->last_name; ?></h2>
		<?php
		foreach ( $course_units as $course_unit ) {
			?>
			<table cellspacing="0" cellpadding="5">
				<tr>
					<td colspan="4" style="background-color:#f5f5f5;"><?php echo $course_unit->post_title; ?></td>
				</tr>
			</table>
			<?php

			$modules = Unit_Module::get_modules( $course_unit->ID );

			$input_modules_count = 0;

			foreach ( $modules as $mod ) {
				if ( isset( $mod->module_type ) && $mod->module_type !== '' ) {
					$class_name = $mod->module_type;

					if ( class_exists( $class_name ) ) {
						if ( constant( $class_name . '::FRONT_SAVE' ) ) {
							$input_modules_count ++;
						}
					}
				}
			}

			if ( $input_modules_count == 0 ) {
				?>
				<table cellspacing="0" cellpadding="5">
					<tr>
						<td colspan="4" style="color:#ccc;"><?php _e( 'Read-only', 'cp' ); ?></td>
					</tr>
				</table>
			<?php
			}

			foreach ( $modules as $mod ) {

				if ( isset( $mod->module_type ) && $mod->module_type !== '' ) {
					$class_name = $mod->module_type;

					$class_name = $mod->module_type;

					if ( class_exists( $class_name ) ) {

						$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );

						if ( constant( $class_name . '::FRONT_SAVE' ) ) {
							$response = call_user_func( $class_name . '::get_response', $user_object->ID, $mod->ID );

							$visibility_class = ( count( $response ) >= 1 ? '' : 'less_visible_row' );

							$id = isset( $response->ID ) ? $response->ID : 0;

							$grade_data = Unit_Module::get_response_grade( $id );
							?>
							<table cellspacing="0" cellpadding="5">
								<tr>
									<td style="border-bottom: 1px solid #cccccc;">
										<?php echo $mod->label;
										?>
									</td>

									<td style="border-bottom: 1px solid #cccccc;">
										<?php echo $mod->post_title; ?>
									</td>

									<td style="border-bottom: 1px solid #cccccc;">
										<?php echo( count( $response ) >= 1 ? $response->post_date : __( 'Not submitted yet', 'cp' ) ); ?>
									</td>

									<td style="border-bottom: 1px solid #cccccc;">
										<?php
										$grade           = $grade_data['grade'];
										$instructor_id   = $grade_data['instructor'];
										$instructor_name = get_userdata( $instructor_id );
										$grade_time      = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $grade_data['time'] );

										if ( $assessable == 'yes' ) {
											if ( count( $response ) >= 1 ) {
												if ( $grade_data ) {
													echo $grade . '%';
													$responses ++;
													$overall_grade = $overall_grade + $grade;
												} else {
													_e( 'Pending grade', 'cp' );
												}
											} else {
												echo '0%';
											}

											$assessable_answers ++;
										} else {
											_e( 'Non-assessable', 'cp' );
										}
										?>
									</td>
								</tr>
								<?php
								$comment = Unit_Module::get_response_comment( $id );
								if ( ! empty( $comment ) ) {
									?>
									<tr>
										<td colspan="4" style="background-color:#FF6600; color:#fff; margin-left:30px;">
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $comment; ?></td>
									</tr>
								<?php
								}
								?>

							</table>
							<?php
							$current_row ++;
						}//end front save
					}
				}
			}//end modules
		}//course units

		if ( $current_row > 0 ) {
			?>
			<table cellspacing="0" cellpadding="10">
				<tr>
					<td colspan="2" style="background-color: #2396A0; color:#fff;">
						<?php _e( 'Average response grade: ', 'cp' ); ?>
						<?php
						if ( $overall_grade > 0 ) {
							echo round( ( $overall_grade / $responses ), 2 ) . '%';
						} else {
							echo '0%';
						}
						?>
					</td>
					<td colspan="2" style="text-align: right; background-color: #2396A0; color:#fff; font-weight: bold;">
						<?php _e( 'TOTAL:', 'cp' ); ?>
						<?php
						if ( $overall_grade > 0 ) {
							echo round( ( $overall_grade / $assessable_answers ), 2 ) . '%';
						} else {
							echo '0%';
						}
						?>
					</td>
				</tr>
			</table>

		<?php
		}
		?>
		<!--<br pagebreak="true"/>-->
		<?php
		$users_num ++;
	}//post users

	if ( $users_num == 1 ) {
		$report_title = $report_title .= ' | ' . $user_object->first_name . ' ' . $user_object->last_name;
	} else {
		$report_title = $report_title .= ' | ' . __( 'All Students', 'cp' );
	}


	$report_content = ob_get_clean();
	//$report_title = __( 'Report', 'cp' );
	$report_name = __( $report_title . '.pdf', 'cp' );
	$coursepress->pdf_report( $report_content, $report_name, $report_title );
	exit;
}//generate report initiated
/* * ****************************END OF REPORT********************************** */

if ( isset( $_POST['action'] ) && isset( $_POST['users'] ) ) {
	check_admin_referer( 'bulk-students' );

	$action = $_POST['action'];
	foreach ( $_POST['users'] as $user_value ) {

		if ( is_numeric( $user_value ) ) {

			$student_id = ( int ) $user_value;
			$student    = new Student( $student_id );

			switch ( addslashes( $action ) ) {
				case 'delete':
					if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_students_cap' ) ) {
						$student->delete_student();
						// $message = __( 'Selected students has been removed successfully.', 'cp' );
						$message = __( 'Selected students has been withdrawed from all courses successfully.', 'cp' );
					}
					break;

				case 'withdraw':
					if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_withdraw_students_cap' ) ) {
						$student->withdraw_from_all_courses();
						$message = __( 'Selected students has been withdrawed from all courses successfully.', 'cp' );
					}
					break;
			}
		}
	}
}

if ( isset( $_GET['page_num'] ) ) {
	$page_num = ( int ) $_GET['page_num'];
} else {
	$page_num = 1;
}

if ( isset( $_GET['s'] ) ) {
	$usersearch = $_GET['s'];
} else {
	$usersearch = '';
}


// Query the users
$wp_user_search = new Student_Search( $usersearch, $page_num );
?>
<div class="wrap nosubsub reports cp-wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit-pages"><br></div>
	<h2><?php _e( 'Reports', 'cp' ); ?></h2>

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
	<?php
	}
	?>

	<div class="tablenav tablenav-top">
		<form method="get" id="course-filter">
			<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>"/>
			<input type="hidden" name="page_num" value="<?php echo esc_attr( $page_num ); ?>"/>

			<div class="alignleft actions">
				<select name="course_id" id="dynamic_courses" class="chosen-select">

					<?php
					$args = array(
						'post_type'      => 'course',
						'post_status'    => 'any',
						'posts_per_page' => - 1
					);

					$courses               = get_posts( $args );
					$courses_with_students = 0;
					$course_num            = 0;
					$first_course_id       = 0;

					foreach ( $courses as $course ) {
						if ( $course_num == 0 ) {
							$first_course_id = $course->ID;
						}

						$course_obj    = new Course( $course->ID );
						$course_object = $course_obj->get_course();

						if ( $course_obj->get_number_of_students() >= 1 ) {
							$courses_with_students ++;
							?>
							<option value="<?php echo $course->ID; ?>" <?php echo( ( isset( $_GET['course_id'] ) && $_GET['course_id'] == $course->ID ) ? 'selected="selected"' : '' ); ?>><?php echo $course->post_title; ?></option>
						<?php
						}

						$course_num ++;
					}

					if ( $courses_with_students == 0 ) {
						?>
						<option value=""><?php _e( '0 courses with enrolled students.', 'cp' ); ?></option>
					<?php
					}
					?>

				</select>

				<?php
				$current_course_id = 0;
				if ( isset( $_GET['course_id'] ) ) {
					$current_course_id = ( int ) $_GET['course_id'];
				} else {
					$current_course_id = $first_course_id;
				}
				?>

				<?php
				if ( $current_course_id !== 0 ) {//courses exists, at least one
					$course       = new Course( $current_course_id );
					$course_units = $course->get_units();

					if ( count( $course_units ) >= 1 ) {

						//search for students
						if ( isset( $_GET['classes'] ) ) {
							$classes = $_GET['classes'];
						} else {
							$classes = 'all';
						}
						?>
						<!--<select name="classes" id="dynamic_classes" name="dynamic_classes">
                                <option value="all" <?php //selected( $classes, 'all', true );  ?>><?php //_e( 'All Classes', 'cp' );  ?></option>
                                <option value="" <?php //selected( $classes, '', true );  ?>><?php //_e( 'Default', 'cp' );  ?></option>
                        <?php
						//$course_classes = get_post_meta( $current_course_id, 'course_classes', true );
						// foreach ( $course_classes as $course_class ) {
						?>
                                    <option value="<?php //echo $course_class;  ?>" <?php //selected( $classes, $course_class, true );  ?>><?php //echo $course_class;  ?></option>
                        <?php
						// }
						?>
                                </select>-->

					<?php
					}
				}
				?>

			</div>
		</form>
	</div>
	<!--tablenav-->

	<?php
	$columns = array(
		"ID"             => __( 'ID', 'cp' ),
		"user_fullname"  => __( 'Full Name', 'cp' ),
		"user_firstname" => __( 'First Name', 'cp' ),
		"user_lastname"  => __( 'Surname', 'cp' ),
		"responses"      => __( 'Responses', 'cp' ),
		"average_grade"  => __( 'Average Grade', 'cp' ),
		"report"         => __( 'Report', 'cp' ),
	);

	$col_sizes = array(
		'4',
		'10',
		'10',
		'10',
		'10',
		'5'
	);

	$class_meta_query_key = '';
	if ( is_multisite() ) {
		$class_meta_query_key = $wpdb->prefix . 'enrolled_course_class_' . $current_course_id;
	} else {
		$class_meta_query_key = 'enrolled_course_class_' . $current_course_id;
	}

	?>
	<form method="post" id="generate-report">
		<input type="hidden" name="course_id" value="<?php echo $current_course_id; ?>"/>
		<table cellspacing="0" class="widefat fixed shadow-table">
			<thead>
			<tr>
				<th class="manage-column column-cb check-column" style="width:3%;" id="cb" scope="col">
					<input type="checkbox"></th>
				<?php
				$n = 0;
				foreach ( $columns as $key => $col ) {
					?>
					<th style="" class="manage-column column-<?php echo str_replace( '_', '-', $key ); ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
					<?php
					$n ++;
				}
				?>
			</tr>
			</thead>

			<tbody>
			<?php
			$style = '';

			//search for students
			if ( isset( $_GET['classes'] ) ) {
				$classes = $_GET['classes'];
			} else {
				$classes = 'all';
			}

			if ( $classes !== 'all' ) {
				$args = array(
					'meta_query' => array(
						array(
							'key'   => $class_meta_query_key,
							'value' => $classes,
						)
					)
				);
			} else {
				$args = array(
					'meta_query' => array(
						array(
							'key' => $class_meta_query_key
						)
					)
				);
			}

			$additional_url_args              = array();
			$additional_url_args['course_id'] = $current_course_id;
			$additional_url_args['classes']   = urlencode( $classes );

			$student_search = new Student_Search( '', $page_num, array(), $args, $additional_url_args );

			foreach ( $student_search->get_results() as $user ) {

				$user_object = new Student( $user->ID );
				$roles       = $user_object->roles;
				$role        = array_shift( $roles );

				$style = ( ' alternate' == $style ) ? '' : ' alternate';
				?>
				<tr id='user-<?php echo $user_object->ID; ?>' class="<?php echo $style; ?>">
					<th scope='row' class='check-column'>
						<input type='checkbox' name='users[]' id='user_<?php echo $user_object->ID; ?>' value='<?php echo $user_object->ID; ?>'/>
					</th>
					<td class="column-ID <?php echo $style; ?>"><?php echo $user_object->ID; ?></td>
					<td class="column-user-fullname visible-small visible-extra-small <?php echo $style; ?>">
                            <span class="user-fullname"><?php echo $user_object->first_name; ?>
	                            <?php echo $user_object->last_name; ?></span>

						<div class="visible-extra-small">
							<?php _e( 'Responses:', 'cp' ); ?> <?php echo $user_object->get_number_of_responses( $current_course_id ); ?>
						</div>
					</td>
					<td class="column-user-firstname <?php echo $style; ?>"><?php echo $user_object->first_name; ?></td>
					<td class="column-user-lastname <?php echo $style; ?>"><?php echo $user_object->last_name; ?></td>

					<td class="column-responses <?php echo $style; ?>"><?php echo $user_object->get_number_of_responses( $current_course_id ); ?></td>
					<td class="column-average-grade <?php echo $style; ?>"><?php echo $user_object->get_avarage_response_grade( $current_course_id ) . '%'; ?></td>
					<td class="column-report <?php echo $style; ?>"><a class="pdf">&nbsp;</a></td>
				</tr>

			<?php
			}
			?>
			<?php
			if ( count( $student_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="8">
						<div class="zero"><?php _e( 'No students found.', 'cp' ); ?></div>
					</td>
				</tr>
			<?php
			}
			?>
			</tbody>
		</table>

		<div class="tablenav">
			<div class="alignleft actions">
				<select name="units" class="chosen-select">
					<option value=""><?php _e( 'All Units', 'cp' ) ?></option>
					<?php
					$course       = new Course( $current_course_id );
					$course_units = $course->get_units();
					foreach ( $course_units as $course_unit ) {
						?>
						<option value="<?php echo $course_unit->ID; ?>"><?php echo $course_unit->post_title; ?></option>
					<?php
					}
					?>

				</select>
				<?php submit_button( __( 'Generate Report', 'cp' ), 'primary', 'generate_report_button', false ); ?>
			</div>

			<div class="tablenav-pages"><?php $student_search->page_links(); ?></div>

		</div>
		<!--/tablenav-->
	</form>


</div>