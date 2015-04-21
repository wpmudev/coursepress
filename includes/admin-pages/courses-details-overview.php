<?php
global $page, $user_id, $cp_admin_notice, $coursepress, $mp;

add_editor_style( CoursePress::instance()->plugin_url . 'css/editor_style_fix.css' );

add_thickbox();

if ( isset( $_GET[ 'course_id' ] ) ) {
	$course			 = new Course( (int) $_GET[ 'course_id' ] );
	$course_details	 = $course->get_course();
	$course_id		 = (int) $_GET[ 'course_id' ];
} else {
	$course		 = new Course();
	$course_id	 = 0;
}

if ( isset( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] == 'add' || $_POST[ 'action' ] == 'update' ) ) {

	check_admin_referer( 'course_details_overview' );

	/* if ( $_POST['meta_course_category'] != -1 ) {
	  $term = get_term_by( 'id', $_POST['meta_course_category'], 'course_category' );
	  wp_set_object_terms( $course_id, $term->slug, 'course_category', false );
	  } */

	// Course has a start date, but no end date
	if ( !isset( $_POST[ 'meta_open_ended_course' ] ) ) {
		$_POST[ 'meta_open_ended_course' ] = 'off';
	}

	// Users can enroll anytime
	if ( !isset( $_POST[ 'meta_open_ended_enrollment' ] ) ) {
		$_POST[ 'meta_open_ended_enrollment' ] = 'off';
	}

	// Limit class size?
	if ( !isset( $_POST[ 'meta_limit_class_size' ] ) ) {
		$_POST[ 'meta_limit_class_size' ] = 'off';
	}

	// Enable/disable course structure preview options
	if ( !isset( $_POST[ 'meta_course_structure_options' ] ) ) {
		$_POST[ 'meta_course_structure_options' ] = 'off';
	}

	// Enable/disable course time preview
	if ( !isset( $_POST[ 'meta_course_structure_time_display' ] ) ) {
		$_POST[ 'meta_course_structure_time_display' ] = 'off';
	}

	if ( !isset( $_POST[ 'meta_allow_course_discussion' ] ) ) {
		$_POST[ 'meta_allow_course_discussion' ] = 'off';
	}

	if ( !isset( $_POST[ 'meta_allow_course_grades_page' ] ) ) {
		$_POST[ 'meta_allow_course_grades_page' ] = 'off';
	}

	if ( !isset( $_POST[ 'meta_allow_workbook_page' ] ) ) {
		$_POST[ 'meta_allow_workbook_page' ] = 'off';
	}

	if ( !isset( $_POST[ 'meta_paid_course' ] ) ) {
		$_POST[ 'meta_paid_course' ] = 'off';
	}

	if ( !isset( $_POST[ 'meta_auto_sku' ] ) ) {
		$_POST[ 'meta_auto_sku' ] = 'off';
	}

	if ( isset( $_POST[ 'submit-unit' ] ) ) {
		/* Save / Save Draft */
		$new_post_id = $course->update_course();
	}

	if ( isset( $_POST[ 'submit-unit-publish' ] ) ) {
		/* Save & Publish */
		$new_post_id = $course->update_course();
		$course		 = new Course( $new_post_id );
		$course->change_status( 'publish' );
	}

	if ( isset( $_POST[ 'submit-unit-unpublish' ] ) ) {
		/* Save & Unpublish */
		$new_post_id = $course->update_course();
		$course		 = new Course( $new_post_id );
		$course->change_status( 'private' );
	}


	if ( $new_post_id != 0 ) {
		// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
		ob_start();
		if ( isset( $_GET[ 'ms' ] ) ) {
			wp_redirect( admin_url( 'admin.php?page=' . $page . '&course_id=' . (int) $new_post_id . '&ms=' . $_GET[ 'ms' ] ) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=' . $page . '&course_id=' . (int) $new_post_id ) );
			exit;
		}
	} else {
//an error occured
	}
}

if ( isset( $_GET[ 'course_id' ] ) ) {
	$class_size						 = $course->details->class_size;
	$enroll_type					 = $course->details->enroll_type;
	$passcode						 = $course->details->passcode;
	$prerequisite					 = $course->details->prerequisite;
	$course_start_date				 = $course->details->course_start_date;
	$course_end_date				 = $course->details->course_end_date;
	$enrollment_start_date			 = $course->details->enrollment_start_date;
	$enrollment_end_date			 = $course->details->enrollment_end_date;
	$open_ended_course				 = $course->details->open_ended_course;
	$open_ended_enrollment			 = $course->details->open_ended_enrollment;
	$limit_class_size				 = $course->details->limit_class_size;
	$marketpress_product			 = $course->details->marketpress_product;
	$woo_product					 = $course->details->woo_product;
	$allow_course_discussion		 = $course->details->allow_course_discussion;
	$allow_course_grades_page		 = $course->details->allow_course_grades_page;
	$allow_workbook_page			 = $course->details->allow_workbook_page;
	$paid_course					 = ($coursepress->marketpress_active || cp_use_woo()) ? $course->details->paid_course : false;
	$auto_sku						 = $course->details->auto_sku;
	$course_terms					 = wp_get_post_terms( (int) $_GET[ 'course_id' ], 'course_category' );
	$course_category				 = is_array( $course_terms ) ? ( isset( $course_terms[ 0 ] ) ? $course_terms[ 0 ]->term_id : 0 ) : 0; //$course->details->course_category;
	$language						 = $course->details->course_language;
	$course_video_url				 = $course->details->course_video_url;
	$course_setup_progress			 = empty( $course->details->course_setup_progress ) ? array(
		'step-1' => 'incomplete',
		'step-2' => 'incomplete',
		'step-3' => 'incomplete',
		'step-4' => 'incomplete',
		'step-5' => 'incomplete',
		'step-6' => 'incomplete',
	) : maybe_unserialize( $course->details->course_setup_progress );
	$course_setup_marker			 = empty( $course->details->course_setup_marker ) ? 'step-1' : $course->details->course_setup_marker;
	$course_structure_options		 = $course->details->course_structure_options;
	$course_structure_time_display	 = $course->details->course_structure_time_display;

	$course_setup_complete = get_post_meta( (int) $_GET[ 'course_id' ], 'course_setup_complete', true );

	if ( !empty( $course_setup_complete ) && 'yes' == $course_setup_complete ) {
		$course_setup_marker = '';
	}

	//$show_module = $course->details->show_module;
	//$preview_module = $course->details->preview_module;

	$show_unit		 = $course->details->show_unit_boxes;
	$preview_unit	 = $course->details->preview_unit_boxes;

	$show_page		 = $course->details->show_page_boxes;
	$preview_page	 = $course->details->preview_page_boxes;
} else {
	$class_size						 = 0;
	$enroll_type					 = '';
	$passcode						 = '';
	$prerequisite					 = '';
	$course_start_date				 = date( 'Y-m-d', current_time( 'timestamp', 0 ) );
	$course_end_date				 = '';
	$enrollment_start_date			 = '';
	$enrollment_end_date			 = '';
	$open_ended_course				 = 'off';
	$open_ended_enrollment			 = 'off';
	$limit_class_size				 = 'off';
	$marketpress_product			 = '';
	$woo_product					 = '';
	$allow_course_discussion		 = 'off';
	$allow_course_grades_page		 = 'off';
	$allow_workbook_page			 = 'off';
	$course_category				 = 0;
	$language						 = __( 'English', 'cp' );
	$course_video_url				 = '';
	$course_setup_progress			 = array(
		'step-1' => 'incomplete',
		'step-2' => 'incomplete',
		'step-3' => 'incomplete',
		'step-4' => 'incomplete',
		'step-5' => 'incomplete',
		'step-6' => 'incomplete',
	);
	$course_setup_marker			 = 'step-1';
	$course_structure_options		 = 'off';
	$course_structure_time_display	 = 'off';
}

// Fix issue where previous versions caused nested serial objects when duplicating courses.
$course_setup_progress = cp_deep_unserialize( $course_setup_progress );


// Detect gateways for MarketPress
// MarketPress 2.x and MarketPress Lite
$mp_settings = get_option( 'mp_settings' );
$gateways	 = !empty( $mp_settings[ 'gateways' ][ 'allowed' ] ) ? true : false;

/**
 * Filter to enable or disable payable courses.
 *
 * @since 1.2.1
 */
$offer_paid	 = apply_filters( 'coursepress_offer_paid_courses', true );
?>
<div class='wrap nocoursesub cp-wrap'>
	<form action='<?php esc_attr_e( admin_url( 'admin.php?page=' . $page . ( ( $course_id !== 0 ) ? '&course_id=' . $course_id : '' ) . ( ( $course_id !== 0 ) ? '&ms=cu' : '&ms=ca' ) ) ); ?>' name='course-add' id='course-add' method='post'>

		<?php
		$can_update	 = 0 == $course_id || CoursePress_Capabilities::can_update_course( $course_id );
		$data_nonce	 = wp_create_nonce( 'auto-update-' . $course_id );
		?>

		<input type='hidden' name='course-ajax-check' id="course-ajax-check" data-id="<?php echo $course_id; ?>" data-uid="<?php echo $can_update ? get_current_user_id() : ''; ?>" data-nonce="<?php echo $data_nonce; ?>" value=""/>

		<div class='course-liquid-left'>

			<div id='course'>
				<?php if ( 0 == $course_id && CoursePress_Capabilities::can_create_course() || CoursePress_Capabilities::can_update_course( $course_id ) ) { ?>
					<?php wp_nonce_field( 'course_details_overview' ); ?>

					<?php if ( isset( $course_id ) ) { ?>
						<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>"/>
						<?php
						if ( CoursePress_Capabilities::can_update_course( $course_id ) || 0 == $course_id ) {
							?>
							<input type="hidden" name="admin_url" value="<?php echo esc_attr( admin_url( 'admin.php?page=course_details' ) ); ?>"/>
						<?php } ?>
						<input type="hidden" name="action" value="update"/>
					<?php } else { ?>
						<input type="hidden" name="action" value="add"/>
					<?php } ?>

					<div id='edit-sub' class='course-holder-wrap mp-wrap'>

						<div class='sidebar-name no-movecursor'>
							<h3><?php _e( 'Course Setup', 'cp' ); ?></h3>
						</div>

						<div class='course-holder'>

							<!-- COURSE BUTTONS -->
							<div class="unit-control-buttons course-control-buttons">

								<?php /* if (( $course_id == 0 && current_user_can('coursepress_create_course_cap'))) {//do not show anything
								  ?>
								  <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php _e('Save Draft', 'cp'); ?>">
								  <input type="submit" name="submit-unit-publish" class="button button-units button-publish" value="<?php _e('Publish', 'cp'); ?>">

								  <?php } */ ?>

								<?php /* if (( $course_id != 0 && current_user_can('coursepress_update_course_cap') ) || ( $course_id != 0 && current_user_can('coursepress_update_my_course_cap') && $course_details->post_author == get_current_user_id() )) {//do not show anything
								  ?>
								  <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php echo ( $course_details->post_status == 'unpublished' ) ? __('Save Draft', 'cp') : __('Publish', 'cp'); ?>">
								  <?php } */ ?>

								<?php
								if ( $course_id != 0 && CoursePress_Capabilities::can_update_course( $course_id ) ) {//do not show anything
									?>
									<a class="button button-preview-overview" href="<?php echo get_permalink( $course_id ); ?>" target="_new"><?php _e( 'Preview', 'cp' ); ?></a>

									<?php
									/* if (current_user_can('coursepress_change_course_status_cap') || ( current_user_can('coursepress_change_my_course_status_cap') && $course_details->post_author == get_current_user_id() )) { ?>
									  <input type="submit" name="submit-unit-<?php echo ( $course_details->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>" class="button button-units button-<?php echo ( $course_details->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>" value="<?php echo ( $course_details->post_status == 'unpublished' ) ? __('Publish', 'cp') : __('Unpublish', 'cp'); ?>">
									  <?php
									  } */
								}
								?>
							</div>
							<!-- /COURSE BUTTONS -->

							<!-- COURSE DETAILS -->
							<div class='course-details'>
								<?php
								$wp_course_search = new Course_Search( '', 1 );
								if ( CoursePress_Capabilities::can_create_course() ) {
									if ( $wp_course_search->is_light ) {
										if ( $wp_course_search->get_count_of_all_courses() < $wp_course_search->courses_per_page ) {
											$not_limited = true;
										} else {
											$not_limited = false;
										}
									} else {
										$not_limited = true;
									}
								}

								if ( ( isset( $_GET[ 'course_id' ] ) ) || !isset( $_GET[ 'course_id' ] ) && $not_limited ) {
									?>
									<!-- Course Overview -->
									<div class="course-section step step-1 <?php echo 'step-1' == $course_setup_marker ? 'save-marker active' : ''; ?>">
										<div class='course-section-title'>
											<div class="status <?php echo empty( $course_setup_progress[ 'step-1' ] ) ? '' : $course_setup_progress[ 'step-1' ]; ?> "></div>
											<h3><?php _e( 'Step 1 - Course Overview', 'cp' ) ?></h3>
										</div>
										<div class='course-form'>
											<?php
											$set_status = $course_setup_progress[ 'step-1' ];
											?>
											<input type='hidden' name='meta_course_setup_progress[step-1]' class='course_setup_progress' value="<?php echo esc_attr( $set_status ); ?>"/>

											<div class="wide">
												<label for='course_name' class="required">
													<?php _e( 'Course Name', 'cp' ); ?>
												</label>
												<input class='wide' type='text' name='course_name' id='course_name' value='<?php
												if ( isset( $_GET[ 'course_id' ] ) ) {
													echo esc_attr( stripslashes( $course->details->post_title ) );
												}
												?>'/>
											</div>

											<div class="wide">
												<label for='course_excerpt' class="required">
													<?php _e( 'Course Excerpt / Short Overview', 'cp' ); ?>
													<?php //CP_Helper_Tooltip::tooltip( __( 'Provide a few short sentences to describe the course', 'cp' ) );    ?>
												</label>
												<?php
												$editor_name	 = "course_excerpt";
												$editor_id		 = "course_excerpt";
												$editor_content	 = htmlspecialchars_decode( ( isset( $_GET[ 'course_id' ] ) ? $course_details->post_excerpt : '' ) );

												$args = array(
													"textarea_name"	 => $editor_name,
													"editor_class"	 => 'cp-editor cp-course-overview',
													"textarea_rows"	 => 3,
													"media_buttons"	 => false,
													"quicktags"		 => false,
												);

												if ( !isset( $course_excerpt->post_excerpt ) ) {
													$course_excerpt					 = new StdClass;
													$course_excerpt->post_excerpt	 = '';
												}

												$desc = '';

												// Filter $args
												$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

												wp_editor( $editor_content, $editor_id, $args );
												$supported_image_extensions = implode( ", ", cp_wp_get_image_extensions() );
												?>
											</div>

											<div class="wide narrow">
												<label for='featured_url'>
													<?php _e( 'Listing Image', 'cp' ); ?><br/>
													<span><?php _e( 'The image is used on the "Courses" listing ( archive ) page along with the course excerpt.', 'cp' ) ?></span>
												</label>

												<div class="featured_url_holder">
													<input class="featured_url" type="text" size="36" name="meta_featured_url" value="<?php
													if ( $course_id !== 0 ) {
														echo esc_attr( $course->details->featured_url );
													}
													?>" placeholder="<?php _e( 'Add Image URL or Browse for Image', 'cp' ); ?>"/>
													<input class="featured_url_button button-secondary" type="button" value="<?php _e( 'Browse', 'cp' ); ?>"/>
													<input type="hidden" name="_thumbnail_id" id="thumbnail_id" value="<?php
													if ( $course_id !== 0 ) {
														echo esc_attr( get_post_meta( $course_id, '_thumbnail_id', true ) );
													}
													?>"/>
														   <?php
														   //get_the_post_thumbnail( $course_id, 'course_thumb', array( 100, 100 ) );
														   //echo wp_get_attachment_image( get_post_meta( $course_id, '_thumbnail_id', true ), array( 100, 100 ) );
														   //echo 'asdads'.get_post_meta( $course_id, '_thumbnail_id', true );
														   ?>
													<div class="invalid_extension_message"><?php echo sprintf( __( 'Extension of the file is not valid. Please use one of the following: %s', 'cp' ), $supported_image_extensions ); ?></div>
												</div>
											</div>

											<!-- v2 -->
											<div class="narrow">
												<label>
													<?php _e( 'Course Category', 'cp' ); ?>
													<a class="context-link" href="edit-tags.php?taxonomy=course_category&post_type=course"><?php _e( 'Manage Categories', 'cp' ); ?></a>
												</label>
												<?php
												$taxonomies = array(
													'course_category',
												);

												$args = array(
													'orderby'		 => 'name',
													'order'			 => 'ASC',
													'hide_empty'	 => false,
													'fields'		 => 'all',
													'hierarchical'	 => true,
												);

												$terms = get_terms( $taxonomies, $args );

												$course_terms = wp_get_post_terms( $course_id, 'course_category', array() );

												$course_terms_array = array();
												foreach ( $course_terms as $course_term ) {
													$course_terms_array[] = $course_term->term_id;
												}

												$class_extra = is_rtl() ? 'chosen-rtl' : '';
												?>

												<select name="course_category" id="course_category" class="postform chosen-select-course <?php echo $class_extra; ?>" multiple="true">
													<?php
													foreach ( $terms as $terms ) {
														?>
														<option value="<?php echo $terms->term_id; ?>" <?php
														if ( in_array( $terms->term_id, $course_terms_array ) ) {
															echo 'selected';
														}
														?>><?php echo $terms->name; ?></option>
																<?php
															}
															?>
												</select>

											</div>

											<div class="narrow">
												<label for='meta_course_language'><?php _e( 'Course Language', 'cp' ); ?></label>
												<input type="text" name="meta_course_language" value="<?php echo esc_attr( stripslashes( $language ) ); ?>"/>
											</div>

											<?php do_action( 'course_step_1_fields', $course_id ); ?>

											<div class="course-step-buttons">
												<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>"/>
												<input type="button" class="button button-units update" value="<?php _e( 'Update', 'cp' ); ?>"/>
											</div>
										</div>
									</div>
									<!-- /Course Overview -->

									<!-- Course Description -->
									<div class="course-section step step-2 <?php echo 'step-2' == $course_setup_marker ? 'save-marker active' : ''; ?>">
										<div class='course-section-title'>
											<div class="status <?php echo empty( $course_setup_progress[ 'step-2' ] ) ? '' : $course_setup_progress[ 'step-2' ]; ?> "></div>
											<h3><?php _e( 'Step 2 - Course Description', 'cp' ) ?></h3>
										</div>
										<div class='course-form'>
											<?php
											$set_status = $course_setup_progress[ 'step-2' ];
											?>
											<input type='hidden' name='meta_course_setup_progress[step-2]' class='course_setup_progress' value="<?php echo $set_status; ?>"/>

											<div class="wide narrow">
												<?php
												global $content_width;

												wp_enqueue_style( 'thickbox' );
												wp_enqueue_script( 'thickbox' );
												wp_enqueue_media();
												wp_enqueue_script( 'media-upload' );

												$supported_video_extensions = implode( ", ", wp_get_video_extensions() );

												if ( !empty( $data ) ) {
													if ( !isset( $data->player_width ) or empty( $data->player_width ) ) {
														$data->player_width = empty( $content_width ) ? 640 : $content_width;
													}
												}
												?>

												<div class="video_url_holder mp-wrap">
													<label for='meta_course_video_url'>
														<?php _e( 'Featured Video', 'cp' ); ?><br/>
														<span><?php _e( 'This is used on the Course Overview page and will be displayed with the course description.', 'cp' ); ?></span>
													</label>
													<input class="course_video_url" type="text" size="36" name="meta_course_video_url" value="<?php echo esc_attr( $course_video_url ); ?>" placeholder="<?php
													_e( 'Add URL or Browse', 'cp' );
													echo ' ( ' . $supported_video_extensions . ' )';
													?>"/>

													<input type="button" class="course_video_url_button button-secondary" value="<?php _e( 'Browse', 'cp' ); ?>"/>

													<div class="invalid_extension_message"><?php echo sprintf( __( 'Extension of the file is not valid. Please use one of the following: %s', 'cp' ), $supported_video_extensions ); ?></div>
												</div>
											</div>

											<div class="wide">
												<label for='course_description' class="required">
													<?php _e( 'Course Description', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'Provide a detailed description of the course', 'cp' ) );       ?>
												</label>

												<p><?php _e( 'This is an in-depth description of the course. It should include such things like an overview, outcomes, possible requirements, etc.', 'cp' ); ?></p>
												<?php
												$editor_name	 = "course_description";
												$editor_id		 = "course_description";
												$editor_content	 = htmlspecialchars_decode( isset( $course_details->post_content ) ? $course_details->post_content : ''  );


												$args = array(
													"textarea_name"	 => $editor_name,
													"editor_class"	 => 'cp-editor cp-course-overview',
													"textarea_rows"	 => 10,
												);

												if ( !isset( $course_details->post_content ) ) {
													$course_details					 = new StdClass;
													$course_details->post_content	 = '';
												}

												$desc = '';

												// Filter $args before showing editor
												$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

												wp_editor( $editor_content, $editor_id, $args );
												?>
											</div>

											<!-- PLACEHOLDER -->
											<div class="wide">
												<label>
													<?php _e( 'Course Structure', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'Provide a detailed description of the course', 'cp' ) );       ?>
													<br/>
													<span><?php _e( 'This gives you the option to show/hide Course Units, Lessons, Estimated Time and Free Preview options on the Course Overview page', 'cp' ); ?></span>
												</label>

												<div class="course-structure">
													<input type='checkbox' id='meta_course_structure_options' name='meta_course_structure_options' <?php echo ( $course_structure_options == 'on' ) ? 'checked' : ''; ?> />
													<label for="meta_course_structure_options"><?php _e( 'Show the Course Overview structure and Preview Options', 'cp' ); ?></label><br/>
													<input type='checkbox' id='meta_course_structure_time_display' name='meta_course_structure_time_display' <?php echo ( $course_structure_time_display == 'on' ) ? 'checked' : ''; ?> />
													<label for="meta_course_structure_time_display"><?php _e( 'Display Time Estimates for Units and Lessons', 'cp' ); ?></label>
													<table>
														<thead>
															<tr>
																<th class="column-course-structure"><?php _e( 'Course Structure', 'cp' ); ?></th>
																<th class="column-show"><?php _e( 'Show', 'cp' ); ?></th>
																<th class="column-free-preview"><?php _e( 'Free Preview', 'cp' ); ?></th>
																<th class="column-time"><?php _e( 'Time', 'cp' ); ?></th>
															</tr>
															<tr class="break">
																<td colspan="4"></td>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td colspan="4">
																	<ol class="tree">
																		<li>
																			<label for="course_<?php echo (!isset( $course ) || !empty( $course->details ) ) ? $course->details->ID : '0'; ?>"><?php echo(!isset( $course ) || !empty( $course->details ) && $course->details->post_title && $course->details->post_title !== '' ? $course->details->post_title : __( 'Course', 'cp' ) ); ?></label>
																			<input type="checkbox" checked disabled id="course_<?php echo isset( $course->details ) ? $course->details->ID : ''; ?>" class="hidden_checkbox"/>
																			<?php
																			$course_id	 = isset( $course ) && isset( $course->details ) && !empty( $course->details->ID ) ? $course->details->ID : 0;
																			$units		 = Unit::get_units_from_course( $course_id, 'any', false );
																			$units		 = !empty( $units ) ? $units : array();
																			if ( 0 == count( $units ) ) {
																				?>
																				<ol>
																					<li>
																						<label><?php _e( 'There are currently no units to display', 'cp' ); ?></label>
																					</li>
																				</ol>
																			<?php } else {
																				?>
																				<ol>
																					<?php
																					// Cheking for inhertited "show" status and forces a save.
																					$section_dirty = false;

																					foreach ( $units as $unit ) {
																						$unit_class		 = new Unit( $unit->ID );
																						$unit_pages		 = $unit_class->get_number_of_unit_pages();
																						$unit_pagination = cp_unit_uses_new_pagination( $unit->ID );

																						if ( $unit_pagination ) {
																							$unit_pages = coursepress_unit_pages( $unit->ID, $unit_pagination );
																						} else {
																							$unit_pages = coursepress_unit_pages( $unit->ID );
																						}

																						$modules = Unit_Module::get_modules( $unit->ID );
																						?>

																						<li class="<?php echo( $unit->post_status == 'publish' ? 'enabled_unit' : 'disabled_unit' ); ?>">

																							<label for="unit_<?php echo $unit->ID; ?>">
																								<div class="tree-unit-left"><?php echo( $unit->post_status != 'publish' ? __( '[draft] ', 'cp' ) : '' ); ?><?php echo $unit->post_title; ?></div>
																								<div class="tree-unit-right">
																									<input type='checkbox' class="module_show" id='show-<?php echo $unit->ID; ?>' data-id="<?php echo esc_attr( $unit->ID ); ?>" name='meta_show_unit[<?php echo $unit->ID; ?>]' <?php
																									if ( isset( $show_unit[ $unit->ID ] ) ) {
																										echo ( $show_unit[ $unit->ID ] == 'on' ) ? 'checked' : '';
																									} else {
																										echo ( 'on' == $course_structure_options ) ? 'checked' : '';
																										$section_dirty = true;
																									}
																									?> <?php echo( $unit->post_status == 'publish' ? 'enabled' : 'disabled' ); ?> />

																									<input type='checkbox' class="module_preview" id='preview-<?php echo $unit->ID; ?>' data-id="<?php echo esc_attr( $unit->ID ); ?>" name='meta_preview_unit[<?php echo $unit->ID; ?>]' <?php
																									if ( isset( $preview_unit[ $unit->ID ] ) ) {
																										echo ( $preview_unit[ $unit->ID ] == 'on' ) ? 'checked' : '';
																									}
																									?> <?php echo( $unit->post_status == 'publish' ? 'enabled' : 'disabled' ); ?> />

																									<span><?php echo $unit_class->get_unit_time_estimation( $unit->ID ); ?></span>
																								</div>
																							</label>
																							<input type="checkbox" id="unit_<?php echo $unit->ID; ?>" class="hidden_checkbox"/>


																							<ol>
																								<?php
																								if ( $unit_pages == 0 ) {
																									?>
																									<li>
																										<label><?php _e( 'There are currently no pages to display', 'cp' ); ?></label>
																									</li>
																									<?php
																								} else {
																									?>
																									<li class="course_structure_page_li">
																										<?php
																										for ( $i = 1; $i <= $unit_pages; $i ++ ) {
																											$pages_num	 = 1;
																											$page_title	 = $unit_class->get_unit_page_name( $i );
																											?>

																											<label for="page_<?php echo $unit->ID . '_' . $i; ?>">
																												<div class="tree-page-left">
																													<?php echo( isset( $page_title ) && $page_title !== '' ? $page_title : __( 'Untitled Page', 'cp' ) ); ?>
																												</div>
																												<div class="tree-page-right">
																													<input type='checkbox' class="module_show" id='show-<?php echo $unit->ID . '_' . $i; ?>' data-id="<?php echo esc_attr( $unit->ID . '_' . $i ); ?>" name='meta_show_page[<?php echo $unit->ID . '_' . $i; ?>]' <?php
																													if ( isset( $show_page[ $unit->ID . '_' . $i ] ) ) {
																														echo ( $show_page[ $unit->ID . '_' . $i ] == 'on' ) ? 'checked' : '';
																													} else {
																														echo ( 'on' == $course_structure_options ) ? 'checked' : '';
																														$section_dirty = true;
																													}
																													?> <?php echo( $unit->post_status == 'publish' ? 'enabled' : 'disabled' ); ?> />
																														   <?php
																														   $disabled = '';
																														   if ( isset( $preview_unit[ $unit->ID ] ) ) {
																															   if ( $preview_unit[ $unit->ID ] == 'on' ) {
																																   $disabled = 'disabled';
																															   } else {
																																   $disabled = '';
																															   }
																														   }
																														   ?>
																													<input type='checkbox' <?php echo $disabled; ?> class="module_preview" id='preview-<?php echo $unit->ID . '_' . $i; ?>' data-id="<?php echo esc_attr( $unit->ID . '_' . $i ); ?>" name='meta_preview_page[<?php echo $unit->ID . '_' . $i; ?>]' <?php
																													if ( isset( $preview_page[ $unit->ID . '_' . $i ] ) || isset( $preview_unit[ $unit->ID ] ) ) {
																														echo ( $preview_page[ $unit->ID . '_' . $i ] == 'on' || $preview_unit[ $unit->ID ] == 'on' ) ? 'checked' : '';
																													}
																													?> <?php echo( $unit->post_status == 'publish' ? 'enabled' : 'disabled' ); ?> />

																													<span><?php echo $unit_class->get_unit_page_time_estimation( $unit->ID, $i ); ?></span>
																												</div>
																											</label>

																											<input type="checkbox" id="page_<?php echo $unit->ID . '_' . $i; ?>" class="hidden_checkbox"/>

																											<ol class="course_structure_elements_ol">
																												<?php
																												/*
																												  foreach ($modules as $mod) {
																												  $class_name = $mod->module_type;

																												  if (class_exists($class_name)) {
																												  $module = new $class_name();

																												  if ($module->name == 'page_break_module') {
																												  $pages_num++;
																												  } else {
																												  ?>
																												  <?php
																												  if ($pages_num == $i) {
																												  if ($module->name !== 'section_break_module') {
																												  ?>
																												  <li class="element">
																												  <div class="tree-element-left">
																												  <?php echo ($mod->post_title && $mod->post_title !== '' ? $mod->post_title : __('Untitled Element', 'cp')); ?>
																												  </div>

																												  <div class="tree-element-right">
																												  <input type='checkbox' class="module_show" id='show-<?php echo $mod->ID; ?>' name='meta_show_module[<?php echo $mod->ID; ?>]' <?php
																												  if (isset($show_module[$mod->ID])) {
																												  echo ( $show_module[$mod->ID] == 'on' ) ? 'checked' : '';
																												  }
																												  ?> />

																												  <input type='checkbox' class="module_preview" id='preview-<?php echo $mod->ID; ?>' name='meta_preview_module[<?php echo $mod->ID; ?>]' <?php
																												  if (isset($preview_module[$mod->ID])) {
																												  echo ( $preview_module[$mod->ID] == 'on' ) ? 'checked' : '';
																												  }
																												  ?> />

																												  <span><?php echo (isset($mod->time_estimation) && $mod->time_estimation !== '') ? $mod->time_estimation.' '.__('min', 'cp') : __('N/A', 'cp');?></span>
																												  </div>
																												  </li>
																												  <?php
																												  }
																												  }
																												  }
																												  }
																												  } */
																												?>

																											</ol>
																											<?php
																										}
																										?>
																									</li>
																								<?php } ?>

																							</ol>
																						</li>


																						<?php
																					}

																					if ( $section_dirty ) {
																						?>
																						<input type="hidden" name="section_dirty" value="true"/>
																						<?php
																					}
																					?>
																				</ol>
																				<?php
																			}
																			?>
																		</li>

																	</ol>
																</td>
															</tr>
															<?php
															/* $units = $course->get_units();

															  if (0 == count($units)) {
															  ?>
															  <tr>
															  <th colspan="4"><?php _e('There are currently no Units to Display', 'cp'); ?></th>
															  </tr>
															  <?php
															  } else { */
															/* foreach ($units as $unit) {
															  ?>
															  <tr>
															  <th class="title" colspan="4"><?php echo $unit->post_title; ?></th>
															  </tr>
															  <?php
															  $module = new Unit_Module();
															  $modules = $module->order_modules(Unit_Module::get_modules($unit->ID));

															  foreach ($modules as $module) {
															  if (!empty($module->post_title)) {
															  ?>
															  <tr>
															  <td>
															  <?php echo $module->post_title; ?>
															  <input type="hidden" name="module_element[<?php echo $module->ID; ?>]" value="<?php echo $module->ID; ?>" />
															  </td>
															  <td><input type='checkbox' id='show-<?php echo $module->ID; ?>' name='meta_show_module[<?php echo $module->ID; ?>]' <?php
															  if (isset($show_module[$module->ID])) {
															  echo ( $show_module[$module->ID] == 'on' ) ? 'checked' : '';
															  }
															  ?> /></td>
															  <td><input type='checkbox' id='preview-<?php echo $module->ID; ?>' name='meta_preview_module[<?php echo $module->ID; ?>]' <?php
															  if (isset($preview_module[$module->ID])) {
															  echo ( $preview_module[$module->ID] == 'on' ) ? 'checked' : '';
															  }
															  ?> /></td>

															  <td>10 min</td>
															  </tr>
															  <?php
															  } // if not empty post title
															  } // foreach ( $modules as $modul )
															  ?>
															  <?php
															  } */ // foreach ( $units as $unit )
															//}
															?>

														</tbody>
													</table>
												</div>
											</div>

											<?php do_action( 'course_step_2_fields', $course_id ); ?>

											<div class="course-step-buttons">
												<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>"/>
												<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>"/>
												<input type="button" class="button button-units update" value="<?php _e( 'Update', 'cp' ); ?>"/>
											</div>
										</div>
									</div>
									<!-- /Course Description -->

									<!-- Instructors -->
									<div class="course-section step step-3 <?php echo 'step-3' == $course_setup_marker ? 'save-marker active' : ''; ?>">
										<div class='course-section-title'>
											<div class="status <?php echo empty( $course_setup_progress[ 'step-3' ] ) ? '' : $course_setup_progress[ 'step-3' ]; ?> "></div>
											<h3><?php _e( 'Step 3 - Instructors', 'cp' ) ?></h3>
										</div>
										<div class='course-form'>
											<?php
											$set_status = $course_setup_progress[ 'step-3' ];
											?>
											<input type='hidden' name='meta_course_setup_progress[step-3]' class='course_setup_progress' value="<?php echo $set_status; ?>"/>

											<div class="wide narrow">
												<label>
													<?php _e( 'Course Instructor(s)', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'Select one or more instructor to facilitate this course.', 'cp' ) );                 ?>
													<br/>
													<span><?php _e( 'Select one or more instructor to facilitate this course', 'cp' ); ?></span>
												</label>

												<?php if ( CoursePress_Capabilities::can_assign_course_instructor( $course_id ) ) { ?>
													<?php cp_instructors_avatars_array(); ?>

													<div class="clearfix"></div>
													<?php cp_instructors_drop_down( 'postform chosen-select-course course-instructors ' . $class_extra ); ?>
													<input class="button-primary" id="add-instructor-trigger" type="button" value="<?php _e( 'Assign', 'cp' ); ?>">
													<!-- <p><?php _e( 'NOTE: If you need to add an instructor that is not on the list, please finish creating your course and save it. To create a new instructor, you must go to Users to create a new user account which you can select in this list. Then come back to this course and you can then select the instructor.', 'cp' ); ?></p> -->

													<?php
													$data_nonce = wp_create_nonce( 'manage-instructors-' . get_current_user_id() );
													?>

													<input type='hidden' name='instructor-ajax-check' id="instructor-ajax-check" data-id="<?php echo $course_id; ?>" data-uid="<?php echo get_current_user_id(); ?>" data-nonce="<?php echo $data_nonce; ?>" value=""/>
													<?php
												} else {
													if ( cp_get_number_of_instructors() == 0 || cp_instructors_avatars( $course_id, false, true ) == 0 ) {//just to fill in emtpy space if none of the instructors has been assigned to the course and in the same time instructor can't assign instructors to a course
														_e( 'You do not have required permissions to assign instructors to a course.', 'cp' );
													}
												}
												?>

												<p><?php _e( 'Assigned Instructors:', 'cp' ); ?></p>

												<div class="instructors-info" id="instructors-info">
													<?php if ( 0 >= cp_instructors_avatars( $course_id, true, true ) ) : ?>
														<div class="instructor-avatar-holder empty">
															<span class="instructor-name"><?php _e( 'Please Assign Instructor', 'cp' ); ?></span>
														</div>
													<?php endif ?>

													<?php
													$can_manage_instructors = CoursePress_Capabilities::can_assign_course_instructor( $course_id );
													?>

													<?php cp_instructors_avatars( $course_id, $can_manage_instructors ); ?>
													<?php cp_instructors_pending( $course_id, $can_manage_instructors ); ?>
												</div>

												<div class="clearfix"></div>
												<?php if ( $can_manage_instructors || 0 == $course_id ) : ?>
													<hr/>
													<!-- INVITE INSTRUCTOR -->

													<label>
														<?php _e( 'Invite New Instructor', 'cp' ); ?>
														<?php // CP_Helper_Tooltip::tooltip( __( 'If the instructor can not be found in the list above, you will need to invite them via email.', 'cp' ) );                 ?>
														<br/>
														<span><?php _e( 'If the instructor can not be found in the list above, you will need to invite them via email.', 'cp' ); ?></span>
													</label>
													<div class="instructor-invite">
														<label for="invite_instructor_first_name"><?php _e( 'First Name', 'cp' ); ?></label>
														<input type="text" name="invite_instructor_first_name" placeholder="<?php _e( 'First Name', 'cp' ); ?>"/>
														<label for="invite_instructor_last_name"><?php _e( 'Last Name', 'cp' ); ?></label>
														<input type="text" name="invite_instructor_last_name" placeholder="<?php _e( 'Last Name', 'cp' ); ?>"/>
														<label for="invite_instructor_email"><?php _e( 'E-Mail', 'cp' ); ?></label>
														<input type="text" name="invite_instructor_email" placeholder="<?php _e( 'instructor@email.com', 'cp' ); ?>"/>

														<div class="submit-message">
															<input class="button-primary" name="invite_instructor_trigger" id="invite-instructor-trigger" type="button" value="<?php _e( 'Send Invite', 'cp' ); ?>">
														</div>
													</div>
												<?php endif; ?>


											</div>

											<?php do_action( 'course_step_3_fields', $course_id ); ?>

											<div class="course-step-buttons">
												<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>"/>
												<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>"/>
												<input type="button" class="button button-units update" value="<?php _e( 'Update', 'cp' ); ?>"/>
											</div>
										</div>
									</div>
									<!-- /Instructors -->

									<!-- Course Dates -->
									<div class="course-section step step-4 <?php echo 'step-4' == $course_setup_marker ? 'save-marker active' : ''; ?>">
										<div class='course-section-title'>
											<div class="status <?php echo empty( $course_setup_progress[ 'step-4' ] ) ? '' : $course_setup_progress[ 'step-4' ]; ?> "></div>
											<h3><?php _e( 'Step 4 - Course Dates', 'cp' ) ?></h3>
										</div>
										<div class='course-form'>
											<?php
											$set_status		 = $course_setup_progress[ 'step-4' ];
											?>
											<input type='hidden' name='meta_course_setup_progress[step-4]' class='course_setup_progress' value="<?php echo esc_attr( $set_status ); ?>"/>

											<div class="wide course-dates">
												<label>
													<?php _e( 'Course Dates', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'This is the duration the course will be open to the students.', 'cp' ) );                 ?>
												</label>

												<div class="course-date-override">
													<label><input type="checkbox" name="meta_open_ended_course" id="open_ended_course" <?php echo ( $open_ended_course == 'on' ) ? 'checked' : ''; ?> /><?php _e( 'This course has no end date', 'cp' ); ?>
													</label>
												</div>

												<p><?php _e( 'This is the duration the course will be open to the students', 'cp' ); ?></p>

												<div class="date-range">
													<div class="start-date">
														<label for="meta_course_start_date" class="start-date-label required"><?php _e( 'Start Date', 'cp' ); ?></label>

														<div class="date">
															<input type="text" class="dateinput" name="meta_course_start_date" value="<?php echo esc_attr( $course_start_date ); ?>"/><i class="calendar"></i>
														</div>
													</div>
													<div class="end-date <?php echo ( $open_ended_course == 'on' ) ? 'disabled' : ''; ?>">
														<label for="meta_course_end_date" class="end-date-label <?php echo ( $open_ended_course == 'on' ) ? '' : 'required'; ?>"><?php _e( 'End Date', 'cp' ); ?></label>

														<div class="date">
															<input type="text" class="dateinput" name="meta_course_end_date" value="<?php echo esc_attr( $course_end_date ); ?>" <?php echo ( $open_ended_course == 'on' ) ? 'disabled="disabled"' : ''; ?> />
														</div>
													</div>
												</div>
												<div class="clearfix"></div>
											</div>

											<div class="wide enrollment-dates">
												<label>
													<?php _e( 'Enrollment Dates', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'These are the dates that students can enroll.', 'cp' ) );                 ?>
												</label>

												<div class="enrollment-date-override">
													<label><input type="checkbox" name="meta_open_ended_enrollment" id="open_ended_enrollment" <?php echo ( $open_ended_enrollment == 'on' ) ? 'checked' : ''; ?> /><?php _e( 'Users can enroll at any time', 'cp' ); ?>
													</label>
												</div>

												<p><?php _e( 'These are the dates that students can enroll', 'cp' ); ?></p>

												<div class="date-range">
													<div class="start-date <?php echo ( $open_ended_enrollment == 'on' ) ? 'disabled' : ''; ?>">
														<label for="meta_enrollment_start_date" class="start-date-label <?php echo ( $open_ended_enrollment == 'on' ) ? '' : 'required'; ?>"><?php _e( 'Start Date', 'cp' ); ?></label>

														<div class="date">
															<input type="text" class="dateinput" name="meta_enrollment_start_date" value="<?php echo esc_attr( $enrollment_start_date ); ?>" <?php echo ( $open_ended_enrollment == 'on' ) ? 'disabled="disabled"' : ''; ?> />
														</div>
													</div>
													<div class="end-date <?php echo ( $open_ended_enrollment == 'on' ) ? 'disabled' : ''; ?>">
														<label for="meta_enrollment_end_date" class="end-date-label <?php echo ( $open_ended_enrollment == 'on' ) ? '' : 'required'; ?>"><?php _e( 'End Date', 'cp' ); ?></label>

														<div class="date">
															<input type="text" class="dateinput" name="meta_enrollment_end_date" value="<?php echo esc_attr( $enrollment_end_date ); ?>" <?php echo ( $open_ended_enrollment == 'on' ) ? 'disabled="disabled"' : ''; ?> />
														</div>
													</div>
												</div>

												<div class="clearfix"></div>
											</div>
											<!--/all-course-dates-->

											<?php do_action( 'course_step_4_fields', $course_id ); ?>

											<div class="course-step-buttons">
												<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>"/>
												<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>"/>
												<input type="button" class="button button-units update" value="<?php _e( 'Update', 'cp' ); ?>"/>
											</div>
										</div>
									</div>
									<!-- /Course Dates -->

									<!-- Classes, Discussions & Workbook -->
									<div class="course-section step step-5 <?php echo 'step-5' == $course_setup_marker ? 'save-marker active' : ''; ?>">
										<div class='course-section-title'>
											<div class="status <?php echo empty( $course_setup_progress[ 'step-5' ] ) ? '' : $course_setup_progress[ 'step-5' ]; ?> "></div>
											<h3><?php _e( 'Step 5 - Classes, Discussion & Workbook', 'cp' ) ?></h3>
										</div>
										<div class='course-form'>
											<?php
											$set_status		 = $course_setup_progress[ 'step-5' ];
											?>
											<input type='hidden' name='meta_course_setup_progress[step-5]' class='course_setup_progress' value="<?php echo $set_status; ?>"/>

											<div class="wide narrow">
												<div>
													<label for='meta_class-size'>
														<input type="checkbox" name="meta_limit_class_size" id="limit_class_size" <?php echo ( $limit_class_size == 'on' ) ? 'checked' : ''; ?> />
														<span><?php _e( 'Limit class size', 'cp' ); ?></span>
														<?php // CP_Helper_Tooltip::tooltip( __( 'Use this setting to set a limit for all classes. Uncheck for unlimited class size( s ).', 'cp' ) );                    ?>
														<br/>
														<span><?php _e( 'Use this setting to set a limit for all classes. Uncheck for unlimited class size( s ).', 'cp' ); ?></span>
													</label>
													<input class='spinners <?php echo ( $limit_class_size == 'on' ) ? '' : 'disabled'; ?> class_size' name='meta_class_size' id='class_size' value='<?php echo esc_attr( stripslashes( ( is_numeric( $class_size ) ? $class_size : 0 ) ) ); ?>' <?php echo ( $limit_class_size == 'on' ) ? '' : 'disabled="disabled"'; ?> />
													<span class="limit-class-size-required <?php echo ( $limit_class_size == 'on' ) ? 'required' : ''; ?>"></span>
												</div>
												<hr/>

												<label for='meta_allow_course_discussion'>
													<input type="checkbox" name="meta_allow_course_discussion" id="allow_course_discussion" <?php echo ( $allow_course_discussion == 'on' ) ? 'checked' : ''; ?> />
													<span><?php _e( 'Allow Course Discussion', 'cp' ); ?></span>
													<?php // CP_Helper_Tooltip::tooltip( __( 'If checked, students can post questions and receive answers at a course level. A \'Discusssion\' menu item is added for the student to see ALL discussions occuring from all class members and instructors.', 'cp' ) );                    ?>
													<br/>
													<span><?php _e( 'If checked, students can post questions and receive answers at a course level. A \'Discusssion\' menu item is added for the student to see ALL discussions occuring from all class members and instructors.', 'cp' ); ?></span>
												</label>

												<label for='meta_allow_workbook_page'>
													<input type="checkbox" name="meta_allow_workbook_page" id="allow_workbook_page" <?php echo ( $allow_workbook_page == 'on' ) ? 'checked' : ''; ?> />
													<span><?php _e( 'Show student Workbook', 'cp' ); ?></span>
													<?php // CP_Helper_Tooltip::tooltip( __( 'If checked, students can see their progress and grades.', 'cp' ) );                    ?>
													<br/>
													<span><?php _e( 'If checked, students can see their progress and grades.', 'cp' ); ?></span>
												</label>

											</div>

											<?php do_action( 'course_step_5_fields', $course_id ); ?>

											<div class="course-step-buttons">
												<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>"/>
												<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>"/>
												<input type="button" class="button button-units update" value="<?php _e( 'Update', 'cp' ); ?>"/>
											</div>
										</div>
									</div>
									<!-- /Classes, Discussions & Workbook -->

									<!-- Enrollment & Course Cost -->
									<div class="course-section step step-6 <?php echo 'step-6' == $course_setup_marker ? 'save-marker active' : ''; ?>">
										<div class='course-section-title'>
											<?php
											$step_6_status	 = empty( $course_setup_progress[ 'step-6' ] ) ? '' : $course_setup_progress[ 'step-6' ];
											$step_6_status	 = !$gateways && ( isset( $paid_course ) && $paid_course == 'on' ) ? 'attention' : $step_6_status;
											?>
											<div class="status <?php echo $step_6_status; ?> "></div>
											<?php
											$section_title	 = __( 'Step 6 - Enrollment & Course Cost', 'cp' );
											if ( !$offer_paid ) {
												$section_title = __( 'Step 6 - Enrollment', 'cp' );
											}
											?>
											<h3><?php echo esc_html( $section_title ); ?></h3>
										</div>
										<div class='course-form'>
											<?php
											$set_status			 = $course_setup_progress[ 'step-6' ];
											?>
											<input type='hidden' name='meta_course_setup_progress[step-6]' class='course_setup_progress' value="<?php echo $set_status; ?>"/>

											<div class="narrow">
												<label for='meta_enroll_type'>
													<?php _e( 'Who can Enroll in this course', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'Select the limitations on accessing and enrolling in this course.', 'cp' ) );                  ?>
													<br/>
													<span><?php _e( 'Select the limitations on accessing and enrolling in this course.', 'cp' ); ?></span>
												</label>

												<select class="wide" name="meta_enroll_type" id="enroll_type">
													<?php
													$enrollment_types	 = apply_filters( 'coursepress_course_enrollment_types', array(
														'manually' => __( 'Manually added only', 'cp' ),
													) );
													?>
													<?php foreach ( $enrollment_types as $key => $type_text ) { ?>
														<option value="<?php echo esc_attr( $key ); ?>" <?php echo( $enroll_type == $key ? 'selected=""' : '' ) ?>><?php echo esc_html( $type_text ) ?></option>
													<?php } ?>
												</select>

												<?php //if ( !cp_user_can_register() && current_user_can( 'manage_options' ) ) {   ?>
																																										<!--	<span class="course_settings_enrollment_message">-->
												<?php //_e( 'In order to allow course enrollment (other than Manually) you have to activate "Anyone can register" from the WordPress settings.', 'cp' );     ?><!--</span>-->
												<?php //} ?>
											</div>

											<div class='wide' id='manually_added_holder'>
												<p><?php _e( 'NOTE: If you need to manually add a student, students must be registered on your site first. To do this for a student, you can do this yourself by going to Users in WordPress where you can add the students manually. You can then select them from this list.', 'cp' ); ?></p>
											</div>

											<div class="wide" id="enroll_type_prerequisite_holder" <?php echo( $enroll_type <> 'prerequisite' ? 'style="display:none"' : '' ) ?>>
												<label for='meta_enroll_type'>
													<?php _e( 'Prerequisite Course', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'Students will need to fulfil prerequisite in order to enroll.', 'cp' ) );                  ?>
												</label>

												<p><?php _e( 'Students will need to complete the following prerequisite course in order to enroll.', 'cp' ); ?></p>
												<select name="meta_prerequisite" class="chosen-select">
													<?php
													$args = array(
														'post_type'		 => 'course',
														'post_status'	 => 'any',
														'posts_per_page' => - 1,
														'exclude'		 => $course_id
													);

													$pre_courses = get_posts( $args );

													foreach ( $pre_courses as $pre_course ) {

														$pre_course_obj		 = new Course( $pre_course->ID );
														$pre_course_object	 = $pre_course_obj->get_course();
														?>
														<option value="<?php echo $pre_course->ID; ?>" <?php selected( $prerequisite, $pre_course->ID, true ); ?>><?php echo $pre_course->post_title; ?></option>
														<?php
													}
													?>
												</select>

											</div>

											<div class="narrow" id="enroll_type_holder" <?php echo( $enroll_type <> 'passcode' ? 'style="display:none"' : '' ) ?>>
												<label for='meta_enroll_type'>
													<?php _e( 'Pass Code', 'cp' ); ?>
													<?php // CP_Helper_Tooltip::tooltip( __( 'Students will need to enter this pass code in order to enroll.', 'cp' ) );                  ?>
												</label>

												<p><?php _e( 'Students will need to enter this pass code in order to enroll.', 'cp' ); ?></p>

												<input type="text" name="meta_passcode" value="<?php echo esc_attr( stripslashes( $passcode ) ); ?>"/>

											</div>

											<?php
											// Check to see if we're offering paid courses.
											if ( $offer_paid ) {
												if ( cp_use_woo() ) {
													//START WOO
													?>
													<div class="narrow product">

														<label>
															<?php _e( 'Cost to participate in this course', 'cp' ); ?>
														</label>

														<div class="course-paid" id="marketpressprompt">
															<input type="checkbox" name="meta_paid_course" <?php echo ( isset( $paid_course ) && $paid_course == 'on' ) ? 'checked' : ''; ?> id="paid_course"></input>
															<span><?php _e( 'This is a Paid Course', 'cp' ); ?></span>
														</div>

														<div>
															<?php
															$woo_product_id = CP_WooCommerce_Integration::woo_product_id( $course_id );

															$product_exists = 0 != $woo_product_id ? true : false;

															$paid_course = !isset( $paid_course ) || $paid_course == 'off' ? 'off' : 'on';
															$paid_course = !$product_exists ? 'off' : $paid_course;

															if ( isset( $course_id ) && $course_id !== 0 ) {
																$woo_product_details = get_post_custom( $woo_product_id ); //$course_id
															}

															if ( isset( $woo_product ) && $woo_product !== '' ) {
																$woo_product_sku = get_post_meta( $woo_product, '_sku', true );
															} else {
																$woo_product_sku = '';
															}

															$input_state = 'off' == $paid_course ? 'disabled="disabled"' : '';
															?>

															<input type="hidden" name="meta_mp_product_id" id="mp_product_id" value="<?php echo esc_attr( isset( $woo_product_id ) ? $woo_product_id : ''  ); ?>"/>

															<div class="course-paid-course-details <?php echo ( $paid_course != 'on' ) ? 'hidden' : ''; ?>">
																<div class="course-sku">
																	<p>
																		<input type="checkbox" name="meta_auto_sku" <?php echo ( isset( $auto_sku ) && $auto_sku == 'on' ) ? 'checked' : ''; ?> <?php echo $input_state; ?>  />
																		<?php _e( 'Automatically generate Stock Keeping Unit (SKU)', 'cp' ); ?>
																	</p>
																	<input type="text" name="mp_sku" id="mp_sku" placeholder="CP-000001" value="<?php
																	echo esc_attr( isset( $woo_product_sku ) ? $woo_product_sku : ''  );
																	?>" <?php echo $input_state; ?> />
																</div>

																<div class="course-price">
																	<span class="price-label <?php echo $paid_course == 'on' ? 'required' : ''; ?>"><?php _e( 'Price', 'cp' ); ?></span>
																	<input type="text" name="mp_price" id="mp_price" value="<?php echo isset( $woo_product_details[ '_regular_price' ][ 0 ] ) ? esc_attr( $woo_product_details[ '_regular_price' ][ 0 ] ) : ''; ?>" <?php echo $input_state; ?>  />
																</div>

																<div class="clearfix"></div>

																<div class="course-sale-price">
																	<?php
																	$woo_is_sale = isset( $woo_product_details[ "_sale_price" ][ 0 ] ) ? (is_numeric( $woo_product_details[ "_sale_price" ][ 0 ] ) ? true : false) : false;
																	?>
																	<p>
																		<input type="checkbox" id="mp_is_sale" name="mp_is_sale" value="<?php echo esc_attr( $woo_is_sale ); ?>" <?php checked( $woo_is_sale, '1', true ); ?><?php echo $input_state; ?>  />
																		<?php _e( 'Enabled Sale Price', 'cp' ); ?></p>
																	<span class="price-label <?php isset( $woo_product_details ) && !empty( $woo_product_details[ "_sale_price" ] ) && checked( $woo_product_details[ "_sale_price" ][ 0 ], '1' ) ? 'required' : ''; ?>"><?php _e( 'Sale Price', 'cp' ); ?></span>
																	<input type="text" name="mp_sale_price" id="mp_sale_price" value="<?php echo(!empty( $woo_product_details[ '_sale_price' ] ) ? esc_attr( $woo_product_details[ "_sale_price" ][ 0 ] ) : 0 ); ?>" <?php echo $input_state; ?>  />
																</div>

																<div class="clearfix"></div>

																<div class="course-enable-gateways gateway-active"></div>

															</div>
														</div>

													</div>
													<?php
													//END WOO
												} else {
													?>
													<hr/>
													<?php // START ////////////////////////////////////////////////////////////////////////////////////////////////////////////
													?>
													<div class="narrow product">
														<!-- MarketPress not Active -->
														<?php
														if ( $coursepress->marketpress_active || ( current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) ) ) {
															?>
															<label>
																<?php _e( 'Cost to participate in this course', 'cp' ); ?>
															</label>

															<div class="course-paid" id="marketpressprompt">
																<input type="checkbox" name="meta_paid_course" <?php echo ( isset( $paid_course ) && $paid_course == 'on' ) ? 'checked' : ''; ?> id="paid_course"></input>
																<span><?php _e( 'This is a Paid Course', 'cp' ); ?></span>
															</div>

															<?php
														}
														if ( current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) ) {
															?>
															<div class="cp-markertpress-not-active <?php echo $coursepress->marketpress_active ? 'hidden' : ''; ?>">
																<div id="marketpressprompt-box">
																	<label>
																		<?php _e( 'Sell your courses online with MarketPress.', 'cp' ); ?>
																	</label>

																	<?php
																	if ( !CoursePress_Capabilities::is_pro() ) {
																		echo sprintf( __(
																		'To start selling your course, you will need to activate the MarketPress Lite plugin: <br /> %s<br /><br />' .
																		'If you require other payment gateways, you will need to upgrade to %s.', 'cp' ), '<a target="_blank" href="' . admin_url( 'admin.php?page=' . $this->screen_base . '_settings' . '&tab=cp-marketpress' ) . '">' . __( 'Begin Activating MarketPress Lite', 'cp' ) . '</a>', '<a target="_blank" href="https://premium.wpmudev.org/project/e-commerce/">' . __( 'MarketPress', 'cp' ) . '</a>' );
																	} else {
																		echo sprintf( __( 'The full version of MarketPress has been bundled with %s.<br />' .
																		'To start selling your course, you will need to activate MarketPress: <br /> %s<br /><br />', 'cp' ), 'CoursePress Pro', '<a target="_blank" href="' . admin_url( 'admin.php?page=' . $this->screen_base . '_settings' . '&tab=cp-marketpress' ) . '">' . __( 'Begin Activating MarketPress', 'cp' ) . '</a>' );
																	}
																	?>
																</div>
															</div>  <!-- cp-marketpress-not-active -->
														<?php } ?>
														<?php if ( current_user_can( 'manage_options' ) || (!current_user_can( 'manage_options' ) && $gateways ) ) { ?>
															<div class="cp-markertpress-is-active <?php echo!$coursepress->marketpress_active ? 'hidden' : ''; ?>">
																<?php
																$mp_product_id = $course->mp_product_id();

																$product_exists = 0 != $mp_product_id ? true : false;

																$paid_course = !isset( $paid_course ) || $paid_course == 'off' ? 'off' : 'on';
																$paid_course = !$product_exists ? 'off' : $paid_course;

																//var_dump(get_post_custom($course_id));
																if ( isset( $course_id ) && $course_id !== 0 ) {
																	$mp_product_details = get_post_custom( $course_id );
																}

																if ( isset( $marketpress_product ) && $marketpress_product !== '' ) {
																	$marketpress_product_sku = get_post_meta( $marketpress_product, 'mp_sku', true );
																} else {
																	$marketpress_product_sku = '';
																}

																$input_state = 'off' == $paid_course ? 'disabled="disabled"' : '';
																?>

																<input type="hidden" name="meta_mp_product_id" id="mp_product_id" value="<?php echo esc_attr( isset( $course->details->mp_product_id ) ? $course->details->mp_product_id : ''  ); ?>"/>

																<div class="course-paid-course-details <?php echo ( $paid_course != 'on' ) ? 'hidden' : ''; ?>">
																	<div class="course-sku">
																		<p>
																			<input type="checkbox" name="meta_auto_sku" <?php echo ( isset( $auto_sku ) && $auto_sku == 'on' ) ? 'checked' : ''; ?> <?php echo $input_state; ?>  />
																			<?php _e( 'Automatically generate Stock Keeping Unit (SKU)', 'cp' ); ?>
																		</p>
																		<input type="text" name="mp_sku" id="mp_sku" placeholder="CP-000001" value="<?php
																		/* if ( isset( $auto_sku ) && $auto_sku == 'on' ) {
																		  echo esc_attr( $mp_product_details[ "mp_sku" ][ 0 ] );
																		  } */
																		echo esc_attr( isset( $marketpress_product_sku[ 0 ] ) ? $marketpress_product_sku[ 0 ] : ''  );
																		?>" <?php echo $input_state; ?> />
																	</div>

																	<div class="course-price">
																		<span class="price-label <?php echo $paid_course == 'on' ? 'required' : ''; ?>"><?php _e( 'Price', 'cp' ); ?></span>
																		<input type="text" name="mp_price" id="mp_price" value="<?php echo isset( $mp_product_details[ 'mp_price' ][ 0 ] ) ? esc_attr( $mp_product_details[ 'mp_price' ][ 0 ] ) : ''; ?>" <?php echo $input_state; ?>  />
																	</div>

																	<div class="clearfix"></div>

																	<div class="course-sale-price">
																		<?php
																		$mp_is_sale	 = isset( $mp_product_details[ "mp_is_sale" ][ 0 ] ) ? $mp_product_details[ "mp_is_sale" ][ 0 ] : 0;
																		?>
																		<p>
																			<input type="checkbox" id="mp_is_sale" name="mp_is_sale" value="<?php echo esc_attr( $mp_is_sale ); ?>" <?php checked( $mp_is_sale, '1', true ); ?><?php echo $input_state; ?>  />
																			<?php _e( 'Enabled Sale Price', 'cp' ); ?></p>
																		<span class="price-label <?php isset( $mp_product_details ) && !empty( $mp_product_details[ "mp_is_sale" ] ) && checked( $mp_product_details[ "mp_is_sale" ][ 0 ], '1' ) ? 'required' : ''; ?>"><?php _e( 'Sale Price', 'cp' ); ?></span>
																		<input type="text" name="mp_sale_price" id="mp_sale_price" value="<?php echo(!empty( $mp_product_details[ 'mp_sale_price' ] ) ? esc_attr( $mp_product_details[ "mp_sale_price" ][ 0 ] ) : 0 ); ?>" <?php echo $input_state; ?>  />
																	</div>

																	<div class="clearfix"></div>

																	<?php if ( current_user_can( 'manage_options' ) ) { ?>
																		<div class="course-enable-gateways <?php echo $gateways ? 'gateway-active' : 'gateway-undefined'; ?>">
																			<?php
																			//Try to dequeue need-help script to avoid need-help popup
																			wp_dequeue_script( 'mp-need-help' );
																			?>
																			<!-- Add both links for JS/CSS toggle -->
																			<a href="<?php echo admin_url( 'edit.php?post_type=product&page=marketpress&tab=gateways&cp_admin_ref=cp_course_creation_page' ) ?>&TB_iframe=true&width=600&height=550" class="button button-incomplete-gateways thickbox <?php echo $gateways ? 'hide' : ''; ?>" style="<?php echo $gateways ? 'display:none' : ''; ?>"><?php _e( 'Setup Payment Gateways', 'cp' ); ?></a>
																			<span class="payment-gateway-required <?php echo!$gateways && $paid_course == 'on' ? 'required' : ''; ?>"></span>

																			<a href="<?php echo admin_url( 'edit.php?post_type=product&page=marketpress&tab=gateways&cp_admin_ref=cp_course_creation_page' ) ?>&TB_iframe=true&width=600&height=550" class="button button-edit-gateways thickbox <?php echo $gateways ? '' : 'hide'; ?>" style="<?php echo $gateways ? '' : 'display:none'; ?>"><?php _e( 'Edit Payment Gateways', 'cp' ); ?></a>
																		</div>
																	<?php } else {
																		?>
																		<div class="course-enable-gateways gateway-active"></div>
																	<?php }
																	?>
																</div>
															</div>
														<?php } ?><!-- cp-markertpress-is-active -->
														<!--_e('Please ask administrator to enable at least one payment gateway.', 'cp');-->
													</div>

													<?php
													// End check for Campus.
												}
											}
											?>
											<?php // END ///////////////////////////////                     ?>

											<?php do_action( 'course_step_6_fields', $course_id ); ?>

											<div class="course-step-buttons">
												<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>"/>
												<input type="button" class="button button-units update" value="<?php _e( 'Update', 'cp' ); ?>"/>
												<input type="button" class="button button-units done" value="<?php _e( 'Done', 'cp' ); ?>"/>
											</div>
										</div>
									</div>
									<!-- /Enrollment & Course Cost -->

									<!-- OLD GRADEBOOK INTEGRATION
																																									<div class="full border-divider">
																									<label><?php _e( 'Show Grades Page for Students', 'cp' ); ?>
																											<a class="help-icon" href="javascript:;"></a>
																											<div class="tooltip">
																													<div class="tooltip-before"></div>
																													<div class="tooltip-button">&times;</div>
																													<div class="tooltip-content">
									<?php _e( 'If checked, students can see their course performance and grades by units.', 'cp' ) ?>
																													</div>
																											</div>
									
																											<input type="checkbox" name="meta_allow_course_grades_page" id="allow_course_grades_page" <?php echo ( $allow_course_grades_page == 'on' ) ? 'checked' : ''; ?> />
																									</label>
																							</div>
									// OLD GRADEBOOK INTEGRATION -->


								<?php } else {
									?>
									<div class="limited_courses_message">
										<?php
										printf( __( 'While %s is suitable for offering a few courses, you may have bigger goals for your site. %s takes the features you love from %s and unlocks the ability to create an unlimited number of courses. And get 12 payment gateways making it even easier to accept payments for your premium content.' ), $this->name, '<a href="http://premium.wpmudev.org/project/coursepress-pro/">' . __( 'CoursePress PRO' ) . '</a>', $this->name );
										//printf(__('You can create only %s courses with Standard version of %s. Check out the %s.'), $wp_course_search->courses_per_page, $this->name, '<a href="http://premium.wpmudev.org/project/coursepress-pro/">' . __('PRO version') . '</a>');
										?>
									</div>
									<?php
								}
								?>
							</div>
						</div>

						<!-- /COURSE DETAILS -->

						<!--
						<div class="buttons course-add-units-button">
						<?php
						if ( $course_id !== 0 ) {
							?>
																																																				<a href="<?php echo admin_url( 'admin.php?page=' . (int) $_GET[ 'page' ] . '&tab=units&course_id=' . (int) $_GET[ 'course_id' ] ); ?>" class="button-secondary"><?php _e( 'Add Units &raquo;', 'cp' ); ?></a>
						<?php } ?>
						</div>
						-->

					</div>
				<?php } ?>
			</div>

		</div>
		<!-- course-liquid-left -->

	</form>

</div> <!-- wrap -->