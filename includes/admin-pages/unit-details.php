<?php
global $page, $user_id, $cp_admin_notice;
global $coursepress_modules, $coursepress_modules_labels, $coursepress_modules_descriptions, $coursepress_modules_ordered, $save_elements;

$course_id	 = '';
$unit_id	 = '';

if ( isset( $_GET[ 'course_id' ] ) && is_numeric( $_GET[ 'course_id' ] ) ) {
	$course_id	 = (int) $_GET[ 'course_id' ];
	$course		 = new Course( $course_id );
}

if ( !empty( $course_id ) && !CoursePress_Capabilities::can_view_course_units( $_GET[ 'course_id' ] ) ) {
	die( __( 'You do not have required permissions to access this page.', 'cp' ) );
}

if ( !isset( $_POST[ 'force_current_unit_completion' ] ) ) {
	$_POST[ 'force_current_unit_completion' ] = 'off';
}
if ( !isset( $_POST[ 'force_current_unit_successful_completion' ] ) ) {
	$_POST[ 'force_current_unit_successful_completion' ] = 'off';
}

Unit_Module::check_for_modules_to_delete();

if ( isset( $_GET[ 'unit_id' ] ) ) {
	$unit_id									 = (int) $_GET[ 'unit_id' ];
	$unit										 = new Unit( $unit_id );
	$unit_details								 = $unit->get_unit();
	$force_current_unit_completion				 = $unit->details->force_current_unit_completion;
	$force_current_unit_successful_completion	 = $unit->details->force_current_unit_successful_completion;
	$unit_pagination							 = cp_unit_uses_new_pagination( (int) $_GET[ 'unit_id' ] );
} else {
	$unit										 = new Unit();
	$unit_id									 = 0;
	$force_current_unit_completion				 = 'off';
	$force_current_unit_successful_completion	 = 'off';
	$unit_pagination							 = false;
}

if ( $unit_id == 0 ) {
	$unit_id = $unit->create_auto_draft( $course_id ); //create auto draft and get unit id
	//wp_redirect(admin_url('admin.php?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $unit_id));
}

if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'update_unit' ) {

	if ( wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'unit_details_overview_' . $user_id ) ) {

		if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_course_unit_cap' ) || current_user_can( 'coursepress_update_course_unit_cap' ) || current_user_can( 'coursepress_update_my_course_unit_cap' ) || current_user_can( 'coursepress_update_all_courses_unit_cap' ) ) {
			$new_post_id = $unit->update_unit( isset( $_POST[ 'unit_id' ] ) ? $_POST[ 'unit_id' ] : 0  );
		}

		if ( isset( $_POST[ 'unit_state' ] ) ) {
			if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_change_course_unit_status_cap' ) || current_user_can( 'coursepress_change_my_course_unit_status_cap' ) || current_user_can( 'coursepress_change_all_courses_unit_status_cap' ) ) {
				$unit = new Unit( $new_post_id );
				$unit->change_status( $_POST[ 'unit_state' ] );
			}
		}

		if ( $new_post_id !== 0 ) {
			//ob_start();
			// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }

			/**
			 * @todo: Work out what needs to happen before the redirect so that we can properly exit the script.
			 */
			if ( isset( $_GET[ 'ms' ] ) ) {
				wp_redirect( admin_url( 'admin.php?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id . '&ms=' . $_GET[ 'ms' ] . '&active_element=' . $active_element . (isset( $preview_redirect_url ) && $preview_redirect_url !== '' ? '&preview_redirect_url=' . $preview_redirect_url : '' ) . '&unit_page_num=' . (isset( $unit_page_num ) ? $unit_page_num : 1) . '#unit-page-' . (isset( $unit_page_num ) ? $unit_page_num : 1) ) );
				//exit;  // exiting the script here breaks page elements
			} else {
				wp_redirect( admin_url( 'admin.php?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id ) );
				//exit; // exiting the script here breaks page elements
			}
		} else {
			//an error occured
		}

		/* }else{
		  die( __( 'You don\'t have right permissions for the requested action', 'cp' ) );
		  } */
	}
}

if ( isset( $_GET[ 'preview_redirect_url' ] ) && $_GET[ 'preview_redirect_url' ] !== '' ) {
	wp_redirect( trailingslashit( get_permalink( $unit_id ) ) . 'page/' . (isset( $unit_page_num ) ? $unit_page_num : 1) );
	exit;
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' && isset( $_GET[ 'new_status' ] ) && isset( $_GET[ 'unit_id' ] ) && is_numeric( $_GET[ 'unit_id' ] ) ) {
	$unit		 = new Unit( $_GET[ 'unit_id' ] );
	$unit_object = $unit->get_unit();
	if ( CoursePress_Capabilities::can_change_course_unit_status( $course_id, $unit_id ) ) {
		$unit->change_status( $_GET[ 'new_status' ] );
	}
}

// cp_write_log(' preview redir: ' . $_POST['preview_redirect'] );

$preview_redirect	 = isset( $_REQUEST[ 'preview_redirect' ] ) ? $_REQUEST[ 'preview_redirect' ] : 'no';
?>
<div class='wrap mp-wrap nocoursesub unit-details cp-wrap'>

    <div id="undefined-sticky-wrapper" class="sticky-wrapper">
        <div class="sticky-slider visible-small visible-extra-small"><i class="fa fa-chevron-circle-right"></i></div>
        <ul id="sortable-units" class="mp-tabs" style="">
			<?php
			// $units = $course->get_units();
			// $course_id = isset( $course ) && isset( $course->details ) && ! empty( $course->details->ID ) ? $course->details->ID : 0;
			$units				 = Unit::get_units_from_course( $course_id, 'any', false );
			$units				 = !empty( $units ) ? $units : array();
			?>
            <input type="hidden" name="unit_count" value="<?php echo $units ? count( $units ) : 0; ?>" />
			<?php
			$list_order			 = 1;

			foreach ( $units as $unit ) {

				$unit_object = new Unit( $unit->ID );
				$unit_object = $unit_object->get_unit();
				?>
				<li class="mp-tab <?php echo ( isset( $_GET[ 'unit_id' ] ) && $unit->ID == $_GET[ 'unit_id' ] ? 'active' : '' ); ?>">
					<a class="mp-tab-link" href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&unit_id=' . $unit_object->ID . '&action=edit' ); ?>"><?php echo $unit_object->post_title; ?></a>
					<i class="fa fa-arrows-v cp-move-icon"></i>
					<span class="unit-state-circle <?php echo (isset( $unit_object->post_status ) && $unit_object->post_status == 'publish' ? 'active' : ''); ?>"></span>

					<input type="hidden" class="unit_order" value="<?php echo $list_order; ?>" name="unit_order_<?php echo $unit_object->ID; ?>" />
					<input type="hidden" name="unit_id" class="unit_id" value="<?php echo $unit_object->ID; ?>" /> 

				</li>
				<?php
				$list_order++;
			}
			?>
			<?php if ( CoursePress_Capabilities::can_create_course_unit( $course_id ) ) { ?>
				<li class="mp-tab <?php echo (!isset( $_GET[ 'unit_id' ] ) ? 'active' : '' ); ?> static">
					<a href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&action=add_new_unit' ); ?>" class="<?php echo (!isset( $_GET[ 'unit_id' ] ) ? 'mp-tab-link' : 'button-secondary' ); ?>"><?php _e( 'Add new Unit', 'cp' ); ?></a>
				</li>
			<?php } ?>
        </ul>

		<?php if ( CoursePress_Capabilities::can_create_course_unit( $course_id ) ) { ?>
			<!--<div class="mp-tabs">
				<div class="mp-tab <?php echo (!isset( $_GET[ 'unit_id' ] ) ? 'active' : '' ); ?>">
					<a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit" class="<?php echo (!isset( $_GET[ 'unit_id' ] ) ? 'mp-tab-link' : 'button-secondary' ); ?>"><?php _e( 'Add new Unit', 'cp' ); ?></a>
				</div>
			</div>-->
		<?php } ?>

    </div>
    <div class='mp-settings'><!--course-liquid-left-->
        <form action="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=add_new_unit' . ( ( $unit_id !== 0 ) ? '&ms=uu' : '&ms=ua' ) . (isset( $preview_redirect_url ) && $preview_redirect_url !== '' ? '&preview_redirect_url=' . $preview_redirect_url : '' ) ) ); ?>" name="unit-add" id="unit-add" class="unit-add" method="post">

			<?php wp_nonce_field( 'unit_details_overview_' . $user_id ); ?>
            <input type="hidden" name="unit_state" id="unit_state" value="<?php echo esc_attr( (isset( $unit_id ) && ($unit_id > 0) ? isset( $unit_object->post_status ) ? $unit_object->post_status : 'live'  : 'live' ) ); ?>" />

            <input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>" />
            <input type="hidden" name="unit_id" id="unit_id" value="<?php echo esc_attr( $unit_id ); ?>" />
            <input type="hidden" name="unit_page_num" id="unit_page_num" value="1" />
			<input type="hidden" name="unit_pagination" class="unit_pagination" value="<?php echo $unit_pagination; ?>" />
            <input type="hidden" name="action" value="update_unit" />
            <input type="hidden" name="active_element" id="active_element" value="<?php echo (isset( $_GET[ 'active_element' ] ) ? (int) $_GET[ 'active_element' ] : 1); ?>" />

			<?php
			$unit		 = new Unit( $unit_id );
			$unit_object = $unit->get_unit();
			$unit_id	 = (isset( $unit_object->ID ) && $unit_object->ID !== '') ? $unit_object->ID : '';

			$can_publish		 = CoursePress_Capabilities::can_change_course_unit_status( $course_id, $unit_id );
			$data_nonce			 = wp_create_nonce( 'toggle-' . $unit_id );
			?>

            <div class='section static'>
                <div class='unit-detail-settings'>
                    <h3><i class="fa fa-cog"></i> <?php _e( 'Unit Settings', 'cp' ); ?>
                        <div class="unit-state">
							<?php
							$control_position	 = 'off';
							if ( $unit_id > 0 && $unit_object && 'publish' == $unit_object->post_status ) {
								$control_position = 'on';
							}
							?>
                            <div class="unit_state_id" data-id="<?php echo $unit_id; ?>" data-nonce="<?php echo $data_nonce; ?>"></div>
                            <span class="draft <?php echo 'off' == $control_position ? 'on' : 'off'; ?>"><?php _e( 'Draft', 'cp' ); ?></span>
                            <div class="control <?php echo $can_publish ? '' : 'disabled'; ?> <?php echo $control_position; ?>">
                                <div class="toggle"></div>
                            </div>
                            <span class="live <?php echo 'on' == $control_position ? 'on' : 'off'; ?>"><?php _e( 'Live', 'cp' ); ?></span>
                        </div>
                    </h3>

                    <div class='mp-settings-label'><label for='unit_name'><?php _e( 'Unit Title', 'cp' ); ?></label></div>
                    <div class='mp-settings-field'>
                        <input class='wide' type='text' name='unit_name' id='unit_name' value='<?php echo esc_attr( stripslashes( isset( $unit_details->post_title ) ? $unit_details->post_title : ''  ) ); ?>' />					
                    </div>
                    <div class='mp-settings-label'><label for='unit_availability'><?php _e( 'Unit Availability', 'cp' ); ?></label></div>
                    <div class='mp-settings-field'>
                        <input type="text" class="dateinput" name="unit_availability" value="<?php echo esc_attr( stripslashes( isset( $unit_details->unit_availability ) ? $unit_details->unit_availability : ( date( 'Y-m-d', current_time( 'timestamp', 0 ) ) )  ) ); ?>" />
                        <div class="force_unit_completion">
                            <input type="checkbox" name="force_current_unit_completion" id="force_current_unit_completion" value="on" <?php echo ( $force_current_unit_completion == 'on' ) ? 'checked' : ''; ?> /> <?php _e( 'User needs to <strong><em>answer</em></strong> all mandatory assessments and view all pages in order to access the next unit', 'cp' ); ?>
                        </div>						
                        <div class="force_unit_successful_completion">
							<input type="checkbox" name="force_current_unit_successful_completion" id="force_current_unit_successful_completion" value="on" <?php echo ( $force_current_unit_successful_completion == 'on' ) ? 'checked' : ''; ?> /> <?php _e( 'User also needs to <strong><em>pass</em></strong> all mandatory assessments', 'cp' ); ?>							
						</div>
                    </div>					
                </div>
                <div class="unit-control-buttons">

					<?php
					if ( $unit_id == 0 && CoursePress_Capabilities::can_create_course_unit( $course_id ) ) {//do not show anything
						?>
						<input type="hidden" name="preview_redirect" value="<?php echo $preview_redirect; ?>" />
						<input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php _e( 'Save', 'cp' ); ?>">
						<!--<input type="submit" name="submit-unit-publish" class="button button-units button-publish" value="<?php _e( 'Publish', 'cp' ); ?>">-->

					<?php } ?>

					<?php
					if ( $unit_id != 0 && CoursePress_Capabilities::can_update_course_unit( $course_id, $unit_id ) ) {//do not show anything
						?>
						<input type="hidden" name="preview_redirect" value="<?php echo $preview_redirect; ?>" />
						<input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php echo ( $unit_object->post_status == 'unpublished' ) ? __( 'Save', 'cp' ) : __( 'Save', 'cp' ); ?>">
					<?php } ?>

					<?php
					if ( CoursePress_Capabilities::can_update_course_unit( $course_id, $unit_id ) ) {//do not show anything if user can't update course unit
						?>
						<a class="button button-preview" href="<?php echo get_permalink( $unit_id ); ?>" data-href="<?php echo get_permalink( $unit_id ); ?>" target="_new"><?php _e( 'Preview', 'cp' ); ?></a>

						<?php
						/* if (current_user_can('coursepress_change_course_unit_status_cap') || ( current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id() )) { ?>
						  <input type="submit" name="submit-unit-<?php echo ( $unit_object->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>" class="button button-units button-<?php echo ( $unit_object->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>" value="<?php echo ( $unit_object->post_status == 'unpublished' ) ? __('Publish', 'cp') : __('Unpublish', 'cp'); ?>">
						  <?php
						  } */
					}
					?>

					<?php if ( $unit_id != 0 ) { ?>
						<span class="delete_unit">							
							<a class="button button-units button-delete-unit" href="<?php echo admin_url( 'admin.php?page=course_details&tab=units&course_id=' . $course_id . '&unit_id=' . $unit_id . '&action=delete_unit' ); ?>" onclick="return removeUnit();">
								<i class="fa fa-trash-o"></i>&nbsp;&nbsp;&nbsp;<?php _e( 'Delete Unit', 'cp' ); ?>
							</a>
						</span>
					<?php } ?>

                </div>
            </div>
            <div class='section elements-section'>
                <input type="hidden" name="beingdragged" id="beingdragged" value="" />
                <div id='course'>


                    <div id='edit-sub' class='course-holder-wrap elements-wrap'>

                        <div class='course-holder'>
                            <!--<div class='course-details'>

                                <label for='unit_description'><?php //_e('Introduction to this Unit', 'cp');                   ?></label>
							<?php
							// $editor_name = "unit_description";
							// $editor_id = "unit_description";
							// $editor_content = htmlspecialchars_decode($unit_details->post_content);
							//
							//                             $args = array( "textarea_name" => $editor_name, "textarea_rows" => 10 );
							//
							//                             if ( !isset($unit_details->post_content) ) {
							//                                 $unit_details = new StdClass;
							//                                 $unit_details->post_content = '';
							//                             }
							//
							//                             $desc = '';
							//
							// // Filter $args before showing editor
							// $args = apply_filters('coursepress_element_editor_args', $args, $editor_name, $editor_id);
							//
							//                             wp_editor($editor_content, $editor_id, $args);
							?>
                                <br/>

                            </div>-->


                            <div class="module-droppable levels-sortable ui-droppable" style='display: none;'>
								<?php _e( 'Drag & Drop unit elements here', 'cp' ); ?>
                            </div>

                            <div id="unit-pages">
                                <ul class="sidebar-name unit-pages-navigation">
                                    <li class="unit-pages-title"><span><?php _e( 'Unit Page(s)', 'cp' ); ?></span></li>
									<?php
									if ( $unit_pagination ) {
										$unit_pages = coursepress_unit_pages( $unit_id, $unit_pagination );
									} else {
										$unit_pages = coursepress_unit_pages( $unit_id );
									}
									if ( $unit_id == 0 ) {
										$unit_pages = 1;
									}
									for ( $i = 1; $i <= $unit_pages; $i++ ) {
										?>
										<li><a href="#unit-page-<?php echo $i; ?>"><?php echo $i; ?></a><span class="arrow-down"></span></li>
									<?php } ?>
                                    <li class="ui-state-default ui-corner-top add_new_unit_page"><a id="add_new_unit_page" class="ui-tabs-anchor">+</a></li>
                                </ul>

								<?php
								//$pages_num = 1;

								$save_elements = true;

								$show_title = get_post_meta( $unit_id, 'show_page_title', true );

								for ( $i = 1; $i <= $unit_pages; $i++ ) {
									?>
									<div id="unit-page-<?php echo $i; ?>" class='unit-page-holder'>
										<div class='course-details elements-holder'>
											<div class="unit_page_title">
												<label><?php _e( 'Page Label', 'cp' ); ?>
													<span class="delete_unit_page">							
														<a class="button button-units button-delete-unit"><i class="fa fa-trash-o"></i> <?php _e( 'Delete Page', 'cp' ); ?></a>
													</span>
												</label>
												<div class="description"><?php _e( 'The label will be displayed on the Course Overview and Unit page', 'cp' ); ?></div>
												<input type="text" value="<?php echo esc_attr( $unit->get_unit_page_name( $i ) ); ?>" name="page_title[page_<?php echo $i; ?>]" id="page_title_<?php echo $i; ?>" class="page_title" />
												<label class="show_page_title">
													<input type="checkbox" name="show_page_title[]" value="yes" <?php echo ( isset( $show_title[ $i - 1 ] ) && $show_title[ $i - 1 ] == 'yes' ? 'checked' : (!isset( $show_title[ $i - 1 ] ) ) ? 'checked' : '' ) ?> />
													<input type="hidden" name="show_page_title_field[]" value="<?php echo ( (isset( $show_title[ $i - 1 ] ) && $show_title[ $i - 1 ] == 'yes') || !isset( $show_title[ $i - 1 ] ) ? 'yes' : 'no' ) ?>" />
													<?php _e( 'Show page label on unit.', 'cp' ); ?><br />
												</label>

												<label><?php _e( 'Build Page', 'cp' ); ?></label>
												<div class="description"><?php _e( 'Click to add elements to the page', 'cp' ); ?></div>
											</div>
											<?php
											foreach ( $coursepress_modules_ordered[ 'output' ] as $element ) {
												?>
												<div class="output-element <?php echo $element; ?>">
													<span class="element-label">
														<?php
														$module = new $element;
														echo $module->label;
														?>
													</span>
													<a class="add-element" id="<?php echo $element; ?>"></a>
												</div>
												<?php
											}
											?>
											<div class="elements-separator"></div>
											<?php
											foreach ( $coursepress_modules_ordered[ 'input' ] as $element ) {
												?>
												<div class="input-element <?php echo $element; ?>">
													<span class="element-label">
														<?php
														$module = new $element;
														echo $module->label;
														?>
													</span>
													<a class="add-element" id="<?php echo $element; ?>"></a>
												</div>
												<?php
											}
											foreach ( $coursepress_modules_ordered[ 'invisible' ] as $element ) {
												?>
												<div class="input-element <?php echo $element; ?>">
													<span class="element-label">
														<?php
														$module = new $element;
														echo $module->label;
														?>
													</span>
													<a class="add-element" id="<?php echo $element; ?>"></a>
												</div>
												<?php
											}
											$save_elements	 = false;
											?>

											<hr />

											<span class="no-elements"><?php _e( 'No elements have been added to this page yet', 'cp' ); ?></span>

										</div>


										<?php /* if ( is_array( $modules ) && count( $modules ) >= 1 ) {
										  ?>
										  <div class="loading_elements"><?php _e( 'Loading Unit elements, please wait...', 'cp' ); ?></div>
										  <?php } */ ?>

										<div class="modules_accordion">
											<!--modules will appear here-->
											<?php
											$unit_id		 = ($unit_id == 0 ? -1 : $unit_id);

											if ( $unit_pagination ) {
												$modules	 = Unit_Module::get_modules( $unit_id, $i );
												$pages_num	 = 1;
												foreach ( $modules as $mod ) {
													$class_name = $mod->module_type;
													if ( class_exists( $class_name ) ) {
														$module = new $class_name();
														$module->admin_main( $mod );
													}
												}
											} else {
												$modules	 = Unit_Module::get_modules( $unit_id, 0 );
												$pages_num	 = 1;
												foreach ( $modules as $mod ) {
													$class_name = $mod->module_type;

													if ( class_exists( $class_name ) ) {
														$module = new $class_name();

														if ( $module->name == 'page_break_module' ) {
															$pages_num++;
															if ( $pages_num == $i ) {
																$module->admin_main( $mod );
															}
														} else {
															if ( $pages_num == $i ) {
																$module->admin_main( $mod );
															}
														}
													}
												}
											}
											//$module->get_modules_admin_forms( isset( $_GET['unit_id'] ) ? $_GET['unit_id'] : '-1' );
											?>
										</div>

									</div>
									<?php
								}
								?>
                            </div>

							<div class="unit_pages_preloader">
								<div class="preloader_image"><?php _e( 'Loading unit elements...', 'cp' ); ?></div>
							</div>

							<div class="unit_pages_delete">
								<div class="unit_pages_delete_message"><?php _e( 'Deleting the unit page...', 'cp' ); ?></div>
							</div>

                            <div class="course-details-unit-controls">
                                <div class="unit-control-buttons">

									<?php
									if ( $unit_id == 0 && CoursePress_Capabilities::can_create_course_unit( $course_id ) ) {//do not show anything
										?>
										<input type="hidden" name="preview_redirect" value="<?php echo $preview_redirect; ?>" />
										<input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php _e( 'Save', 'cp' ); ?>">
										<!--<input type="submit" name="submit-unit-publish" class="button button-units button-publish" value="<?php _e( 'Publish', 'cp' ); ?>">-->

									<?php } ?>

									<?php
									if ( $unit_id != 0 && CoursePress_Capabilities::can_update_course_unit( $course_id, $unit_id ) ) {//do not show anything
										?>
										<input type="hidden" name="preview_redirect" value="<?php echo $preview_redirect; ?>" />
										<input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php echo ( $unit_object->post_status == 'unpublished' ) ? __( 'Save', 'cp' ) : __( 'Save', 'cp' ); ?>">
									<?php } ?>

									<?php
									if ( CoursePress_Capabilities::can_update_course_unit( $course_id, $unit_id ) ) {//do not show anything
										?>
										<a class="button button-preview" href="<?php echo get_permalink( $unit_id ); ?>" data-href="<?php echo get_permalink( $unit_id ); ?>" target="_new"><?php _e( 'Preview', 'cp' ); ?></a>

										<?php
										/* if (current_user_can('coursepress_change_course_unit_status_cap') || ( current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id() )) { ?>
										  <input type="submit" name="submit-unit-<?php echo ( $unit_object->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>" class="button button-units button-<?php echo ( $unit_object->post_status == 'unpublished' ) ? 'publish' : 'unpublish'; ?>" value="<?php echo ( $unit_object->post_status == 'unpublished' ) ? __('Publish', 'cp') : __('Unpublish', 'cp'); ?>">
										  <?php
										  } */
									}
									?>

                                    <div class="unit-state">
										<?php
										$control_position = 'off';
										if ( $unit_id > 0 && $unit_object && 'publish' == $unit_object->post_status ) {
											$control_position = 'on';
										}
										?>
                                        <div class="unit_state_id" data-id="<?php echo $unit_id; ?>" data-nonce="<?php echo $data_nonce; ?>"></div>
                                        <span class="draft <?php echo 'off' == $control_position ? 'on' : 'off'; ?>"><?php _e( 'Draft', 'cp' ); ?></span>
                                        <div class="control <?php echo $can_publish ? '' : 'disabled'; ?> <?php echo $control_position; ?>">
                                            <div class="toggle"></div>
                                        </div>
                                        <span class="live <?php echo 'on' == $control_position ? 'on' : 'off'; ?>"><?php _e( 'Live', 'cp' ); ?></span>
                                    </div>

                                </div>
                            </div>

                        </div><!--/course-holder-->
                    </div><!--/course-holder-wrap-->
                </div><!--/course-->
            </div> <!-- /section -->
        </form>			
    </div> <!-- course-liquid-left -->

    <div class='level-liquid-right' style="display:none;">
        <div class="level-holder-wrap">
			<?php
			$sections = array( "input" => __( 'Input Elements', 'cp' ), "output" => __( 'Output Elements', 'cp' ), "invisible" => __( 'Invisible Elements', 'cp' ) );

			foreach ( $sections as $key => $section ) {
				?>

				<div class="sidebar-name no-movecursor">
					<h3><?php echo $section; ?></h3>
				</div>

				<div class="section-holder" id="sidebar-<?php echo $key; ?>" style="min-height: 98px;">
					<ul class='modules'>
						<?php
						if ( isset( $coursepress_modules[ $key ] ) ) {
							foreach ( $coursepress_modules[ $key ] as $mmodule => $mclass ) {
								$module = new $mclass();
								if ( !array_key_exists( $mmodule, $module ) ) {
									$module->admin_sidebar( false );
								} else {
									$module->admin_sidebar( true );
								}

								$module->admin_main( array() );
							}
						}
						?>
					</ul>
				</div>
				<?php
			}
			?>
        </div> <!-- level-holder-wrap -->

    </div> <!-- level-liquid-right -->


    <script type="text/javascript">
		jQuery( document ).ready( function($) {

			/*jQuery( '.modules_accordion .switch-tmce' ).each( function() {
				jQuery( this ).trigger( 'click' );
			} );*/
			
			jQuery('.switch-html').click();
			//jQuery('.switch-tmce').click();
			
			//$('.wp-switch-editor.switch-tmce').click();

			var current_page = jQuery( '#unit-pages .ui-tabs-nav .ui-state-active a' ).html();
			var elements_count = jQuery( '#unit-page-1 .modules_accordion .module-holder-title' ).length;
			var current_page_elements_count = jQuery( '#unit-page-' + current_page + ' .modules_accordion .module-holder-title' ).length;
			//jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').show();

			if ( coursepress_units.unit_pagination == 0 ) {
				if ( ( current_page == 1 && elements_count == 0 ) || ( current_page >= 2 && current_page_elements_count == 1 ) ) {
					jQuery( '#unit-page-' + current_page + ' .elements-holder .no-elements' ).show();
				} else {
					jQuery( '#unit-page-' + current_page + ' .elements-holder .no-elements' ).hide();
				}
			} else {
				if ( elements_count == 0 ) {
					jQuery( '#unit-page-' + current_page + ' .elements-holder .no-elements' ).show();
				} else {
					jQuery( '#unit-page-' + current_page + ' .elements-holder .no-elements' ).hide();
				}
			}

			var current_unit_page = jQuery( '#unit-pages .ui-tabs-nav .ui-state-active a' ).html();

			jQuery( '#unit-page-' + current_unit_page + ' .modules_accordion' ).accordion( "option", "active", <?php echo ($unit_page_num == 1) ? ($active_element) : $active_element; ?> );

			var unit_pages = jQuery( "#unit-pages .ui-tabs-nav li" ).size() - 2;

			if ( unit_pages == 1 ) {
				jQuery( ".delete_unit_page" ).hide();
			} else {
				jQuery( ".delete_unit_page" ).show();
			}

			jQuery( '#unit-pages' ).css( 'display', 'block' );
			jQuery( '.unit_pages_preloader' ).css( 'display', 'none' );
			jQuery( '.unit-pages-navigation' ).css( 'opacity', '1' );
			//jQuery( '#unit-pages' ).css( 'cursor', 'default' );
			//jQuery('#unit-pages').tabs({active: <?php echo $unit_page_num; ?>});
		} );
    </script>
</div> <!-- wrap -->