<?php
/**
 * Course Edit - Step 2
 **/
?>
<div class="step-title step-2">
	<?php _e( 'Step 2 &ndash; Course Details', 'CP_TD' ); ?>
	<div class="status <?php echo $setup_class; ?>"></div>
</div>

<div class="cp-box-content step-content step-2">
	<input type="hidden" name="meta_setup_step_2" value="saved" />
	<?php
	echo CoursePress_Helper_UI::browse_media_field(
		'meta_featured_video',
		'meta_featured_video',
		array(
			'placeholder' => sprintf( __( 'Add URL or Browse ( %s )', 'CP_TD' ), $supported_ext ),
			'title' => __( 'Featured Video', 'CP_TD' ),
			'value' => CoursePress_Data_Course::get_setting( $course_id, 'featured_video' ),
			'type' => 'video',
			'description' => __( 'This is used on the Course Overview page and will be displayed with the course description.', 'CP_TD' ),
		)
	);
	?>

	<div class="wide">
		<label for="courseDescription" class="required"><?php _e( 'Full Description', 'CP_TD' ); ?></label><br />
		<?php echo CoursePress_Admin_Edit::get_wp_editor( 'courseDescription', 'course_description', $editor_content, array( 'media_buttons' => true ) ); ?>
	</div>

	<div class="wide">
		<label><?php _e( 'View Mode', 'CP_TD' ); ?></label>
		<label class="checkbox">
			<input type="radio" name="meta_course_view" value="normal" <?php checked( 'normal', $course_view ); ?>>
			<?php _e( 'Normal: Show full unit pages', 'CP_TD' ); ?>
			<p class="description"><?php _e( 'Choose if your course will show in "normal" mode or step by step "focus" mode.', 'CP_TD' ); ?></p>
		</label>
		<label class="checkbox">
			<input type="radio" name="meta_course_view" value="focus" <?php checked( 'focus', $course_view ); ?>>
			<?php _e( 'Focus: Focus on one item at a time', 'CP_TD' ); ?>
		</label>
		<label class="checkbox">
			<input type="checkbox" name="meta_focus_hide_section" value="unit" <?php checked( true, $focus_hide_section ); ?>>
			<?php _e( 'Don\'t render section titles in focus mode.', 'CP_TD' ); ?>
		</label>
		<label class="checkbox">
			<input type="radio" name="meta_structure_level" value="unit" <?php checked( 'unit', $structure_level ); ?>>
			<?php _e( 'Unit list only', 'CP_TD' ); ?><br />
		</label>
		<label class="checkbox">
			<input type="radio" name="meta_structure_level" value="section" <?php checked( 'section', $structure_level ); ?>>
			<?php _e( 'Expanded unit list', 'CP_TD' ); ?><br />
			<p class="description"><?php _e( 'Choose if course Unit page shows units only or in expanded view.', 'CP_TD' ); ?></p>
		</label>
	</div>

	<div class="wide">
		<label><?php _e( 'Course Structure', 'CP_TD' ); ?></label>
		<p><?php _e( 'This gives you the option to show/hide Course Units, Lessons, Estimated Time and Free Preview options on the Course Overview page', 'CP_TD' ); ?></p>

		<div class="course-structure">
			<label class="checkbox">
				<input type="checkbox" name="meta_structure_visible" value="1" <?php checked( true, $structure_visible ); ?> />
				<span><?php _e( 'Show the Course Overview structure and Preview Options', 'CP_TD' ); ?></span>
			</label>
			<label class="checkbox">
				<input type="checkbox" name="meta_structure_show_duration" value="1" <?php checked( true, $structure_show_duration ); ?> />
				<span><?php _e( 'Display Time Estimates for Units and Lessons', 'CP_TD' ); ?></span>
			</label>
			<label class="checkbox">
				<input type="checkbox" name="meta_structure_show_empty_units" <?php checked( true, ! empty( $structure_show_empty_units ) ); ?> />
				<span><?php _e( 'Show units without modules', 'cp' ); ?></span>
				<p class="description"><?php _e( 'By default unit without modules is not displayed, even if it is selected below.', 'CP_TD' ); ?></p>
			</label>

			<table class="course-structure-tree">
				<thead>
					<tr>
						<th class="column-course-structure">
							<?php _e( 'Course Structure', 'CP_TD' ); ?>
							<small><?php _e( 'Units and Sections with Modules selected will automatically be visible (only selected Modules accessible).', 'CP_TD' ); ?></small>
						</th>
						<th class="column-show"><?php _e( 'Show', 'CP_TD' ); ?></th>
						<th class="column-free-preview"><?php _e( 'Free Preview', 'CP_TD' ); ?></th>
						<th class="column-time <?php echo $duration_class; ?>"><?php _e( 'Time', 'CP_TD' ); ?></th>
					</tr>
					<tr class="break"><th colspan="4"></th></tr>
				</thead>
				<tbody>
					<?php
					$count = 0;
					$visible_units = CoursePress_Admin_Edit::$settings['structure_visible_units'];
					$preview_units = CoursePress_Admin_Edit::$settings['structure_preview_units'];
					$visible_pages = CoursePress_Admin_Edit::$settings['structure_visible_pages'];
					$preview_pages = CoursePress_Admin_Edit::$settings['structure_preview_pages'];
					$visible_modules = CoursePress_Admin_Edit::$settings['structure_visible_modules'];
					$preview_modules = CoursePress_Admin_Edit::$settings['structure_preview_modules'];

					foreach ( $units as $unit ) :
						$count++;
						$the_unit = $unit['unit'];
						$unit_id = $the_unit->ID;
						$status = 'publish' == $the_unit->post_status ? '' : __( '[DRAFT] ', 'CP_TD' );
						$draft_class = 'publish' == $the_unit->post_status ? '' : 'draft';
						$alt = $count % 2 ? 'even' : 'odd';
						$tr_class = 'unit unit-' . $unit_id . ' treegrid-' . $count . ' ' . $draft_class . ' ' . $alt;
						$estimations = CoursePress_Data_Unit::get_time_estimation( $unit_id, $units );
						$unit_parent = $count;
					?>
						<tr class="<?php echo $tr_class; ?>" data-unitid="<?php echo $unit_id; ?>">
							<td><?php echo $status . $the_unit->post_title; ?></td>
							<td><input type="checkbox" name="meta_structure_visible_units[<?php echo $unit_id; ?>]" value="1" <?php checked( true, isset( $visible_units[ $unit_id ] ) && $visible_units[ $unit_id ] ); ?>/></td>
							<td><input type="checkbox" name="meta_structure_preview_units[<?php echo $unit_id; ?>]" value="1" <?php checked( true, isset( $preview_units[ $unit_id ] ) && $preview_units[ $unit_id ] ); ?>/></td>
							<td class="column-time <?php echo $duration_class; ?>"><?php CoursePress_Admin_Edit::sanitize_duration_display( $estimations['unit']['estimation'] ); ?></td>
						</tr>

						<?php if ( ! empty( $unit['pages'] ) ) :
							$no_section_title = sprintf( '<small>[%s]</small>', esc_html__( 'this section has no title', 'CP_TD' ) );
							foreach ( $unit['pages'] as $page_number => $page ) :
								$count++;
								$page_title = ! empty( $page['title'] ) ? $page['title'] : sprintf( __( 'Section: %d %s', 'CP_TD' ), $page_number, $no_section_title );

								$page_key = $unit_id . '_' . (int) $page_number;
								$alt = $count % 2 ? 'even' : 'odd';
								$tr_class = 'page page-' . $page_number . ' treegrid-' . $count . ' treegrid-parent-' . $unit_parent . ' ' . $draft_class . ' ' . $alt;
								$duration = ! empty( $estimations['pages'][ $page_number ]['estimation'] ) ? $estimations['pages'][ $page_number ]['estimation'] : '';
								$duration = CoursePress_Admin_Edit::sanitize_duration_display( $duration );
								$page_parent = $count;
								$modules = CoursePress_Helper_Utility::sort_on_object_key( $page['modules'], 'module_order' );
							?>

								<tr class="<?php echo $tr_class; ?>" data-unitid="<?php echo $unit_id; ?>" data-pagenumber="<?php echo $page_number; ?>">
									<td><?php echo $page_title; ?></td>
									<td><input type="checkbox" name="meta_structure_visible_pages[<?php echo $page_key; ?>]" value="1" <?php checked( true, isset( $visible_pages[ $page_key ] ) && $visible_pages[ $page_key ] ); ?>/></td>
									<td><input type="checkbox" name="meta_structure_preview_pages[<?php echo $page_key; ?>]" value="1" <?php checked( true, isset( $preview_pages[ $page_key ] ) && $preview_pages[ $page_key ] ); ?>/></td>
									<td class="column-time <?php echo $duration_class; ?>"><?php echo $duration; ?></td>
								</tr>

								<?php if ( ! empty( $modules ) ) :
									foreach ( $modules as $module ) :
										$count++;
										$alt = $count % 2 ? 'even' : 'odd';
										$module_id = $module->ID;
										$mod_key = $page_key . '_' . $module_id;
										$module_title = ! empty( $module->post_title ) ? $module->post_title : __( 'Untitled Module', 'CP_TD' );
										$tr_class = 'module module-' . $module_id . ' treegrid-' . $count . ' treegrid-parent-' . $page_parent . ' ' . $draft_class . ' ' . $alt;
										$duration = CoursePress_Data_Module::get_time_estimation( $module->ID, '1:00', true );
									?>

										<tr class="<?php echo $tr_class; ?>" data-unitid="<?php echo $unit_id; ?>" data-pagenumber="<?php echo $page_number;?>">
											<td><?php echo $module_title; ?></td>
											<td><input type="checkbox" name="meta_structure_visible_modules[<?php echo $mod_key; ?>]" value="1" <?php checked( true, isset( $visible_modules[ $mod_key ] ) && $visible_modules[ $mod_key ] ); ?> /></td>
											<td><input type="checkbox" name="meta_structure_preview_modules[<?php echo $mod_key; ?>]" value="1" <?php checked( true, isset( $preview_modules[ $mod_key ] ) && $preview_modules[ $mod_key ] ); ?> /></td>
											<td class="column-time <?php echo $duration_class; ?>"><?php echo CoursePress_Admin_Edit::sanitize_duration_display( $duration ); ?></td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>

							<?php endforeach; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr class="break"><th colspan="4"></th></tr>
					<tr>
						<th class="column-course-structure"><?php _e( 'Course Structure', 'CP_TD' ); ?></th>
						<th class="column-show"><?php _e( 'Show', 'CP_TD' ); ?></th>
						<th class="column-free-preview"><?php _e( 'Free Preview', 'CP_TD' ); ?></th>
                        <th class="column-time <?php echo ! $structure_show_duration ? 'hidden': ''; ?>"><?php _e( 'Time', 'CP_TD' ); ?></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<?php
	/**
	 * Trigger after all step 2 fields are rendered.
	 **/
	echo apply_filters( 'coursepress_course_setup_step_2', '', $course_id );

	// Buttons
	echo CoursePress_Admin_Edit::get_buttons( $course_id, 2 );
	?>
</div>
