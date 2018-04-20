<div class="step-title step-7">
	<?php _e( 'Step 7 &ndash; Course Completion', 'coursepress' ); ?>
	<div class="status <?php echo $setup_class; ?>"></div>
</div>

<div class="cp-box-content step-content step-7">
	<input type="hidden" name="meta_setup_step_7" value="saved" />

	<div class="wide minimum-grade">
		<label class="required" for="meta_minimum_grade_required"><?php _e( 'Minimum Grade Required', 'coursepress' ); ?></label>
		<input type="number" id="meta_minimum_grade_required" name="meta_minimum_grade_required" value="<?php echo (int) $minimum_grade_required; ?>" min="0" max="100" class="text-small" />
		<p class="description"><?php _e( 'The minimum grade required to marked course completion and send course certficates.', 'coursepress' ); ?></p>
	</div>

	<!-- Course Pre Completion Page -->
	<div class="wide page-pre-completion">
		<label><?php _e( 'Pre-Completion Page', 'coursepress' ); ?></label>
		<p class="description"><?php _e( 'Use the fields below to show custom pre-completion page after the student completed the course but require final assessment from instructors.', 'coursepress' ); ?></p>

		<label for="meta_pre_completion_title" class="required"><?php _e( 'Page Title', 'coursepress' ); ?></label>
		<input type="text" class="wide" name="meta_pre_completion_title" value="<?php echo esc_attr( $precompletion['title'] ); ?>" />
		<label for="meta_pre_completion_content" class="required"><?php _e( 'Page Content', 'coursepress' ); ?></label>
		<?php
		echo $token_message;
		echo CoursePress_Helper_Editor::get_wp_editor( 'pre-completion-content', 'meta_pre_completion_content', $precompletion['content'] );
		?>
	</div>

	<!-- Course Completion -->
	<div class="wide page-completion">
		<label><?php _e( 'Course Completion Page', 'coursepress' ); ?></label>
		<p class="description"><?php _e( 'Use the fields below to show a custom page after successfull course completion.', 'coursepress' ); ?></p>
		<label for="meta_course_completion_title" class="required"><?php _e( 'Page Title', 'coursepress' ); ?></label>
		<input type="text" class="widefat" name="meta_course_completion_title" value="<?php echo esc_attr( $completion['title'] ); ?>" />

		<label for="meta_course_completion_content" class="required"><?php _e( 'Page Content', 'coursepress' ); ?></label>
		<?php
			echo $token_message;
			echo CoursePress_Helper_Editor::get_wp_editor( 'course-completion-editor-content', 'meta_course_completion_content', $completion['content'] );
		?>
	</div>

	<!-- Course Faield Page -->
	<div class="wide page-failed">
		<label><?php _e( 'Failed Page', 'coursepress' ); ?></label>
		<p class="description"><?php _e( 'Use the fields below to display failure page when an student completed a course but fail to reach the minimum required grade.', 'coursepress' ); ?></p>
		<label for="meta_course_failed_title" class="required"><?php _e( 'Page Title', 'coursepress' ); ?></label>
		<input type="text" class="widefat" name="meta_course_failed_title" value="<?php echo $failed['title']; ?>" />

		<label for="meta_course_field_content" class="required"><?php _e( 'Page Content', 'coursepress' ); ?></label>
		<?php
			echo $token_message;
			echo CoursePress_Helper_Editor::get_wp_editor( 'course-failed-content', 'meta_course_failed_content', $failed['content'] );
		?>
	</div>

	<!-- Course Certificate -->
	<div class="wide course-certificate">
		<br />
		<h3><?php echo _e( 'Custom Certificate', 'coursepress' ); ?></h3>
		<a href="<?php echo esc_url( $certificate['preview_link'] ); ?>" target="_blank" class="button button-default btn-cert <?php echo false === $certificate['enabled'] ? 'hidden' : ''; ?>" style="float:right;margin-top:-35px;">
			<?php echo _e( 'Preview', 'coursepress' ); ?>
		</a>

		<?php
		$one = array(
			'meta_key' => 'basic_certificate',
			'label' => __( 'Use custom certificate for this course.', 'coursepress' ),
			'default' => false,
		);
		echo CoursePress_Helper_UI::course_edit_checkbox( $one, $course_id );
		?>

		<div class="options <?php echo $certificate['enabled'] ? '' : 'hidden'; ?>">
			<label for="meta_basic_certificate_layout"><?php _e( 'Certificate Content', 'coursepress' ); ?></label>
			<p class="description" style="float:left;"><?php echo $certificate['token_message']; ?></p>
			<?php echo CoursePress_Helper_Editor::get_wp_editor( 'basic-certificate-layout', 'meta_basic_certificate_layout', $certificate['content'] ); ?>

			<table class="wide">
				<tr>
					<td style="width:20%;"><label><?php _e( 'Background Image', 'coursepress' ); ?></label></td>
					<td><?php
						echo CoursePress_Helper_UI::browse_media_field(
							'meta_certificate_background',
							'meta_certificate_background',
							array(
								'placeholder' => __( 'Choose background image', 'coursepress' ),
								'type' => 'image',
								'value' => $certificate['background'],
							)
						);
					?></td>
				</tr>
				<tr>
					<td style="width:20%;"><label><?php _e( 'Logo', 'coursepress' ); ?></label></td>
					<td><?php
						echo CoursePress_Helper_UI::browse_media_field(
							'meta_certificate_logo',
							'meta_certificate_logo',
							array(
								'placeholder' => __( 'Choose logo image', 'coursepress' ),
								'type' => 'image',
								'value' => $certificate['logo'],
							)
						);
						?></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Logo Position', 'coursepress' ); ?></label></td>
					<td>
						<?php _e( 'X', 'coursepress' ); ?>:
						<input type="number" class="small-text" name="meta_logo_position[x]" value="<?php echo esc_attr( $certificate['logo_position']['x'] ); ?>" />
						<?php _e( 'Y', 'coursepress' ); ?>:
						<input type="number" class="small-text" name="meta_logo_position[y]" value="<?php echo esc_attr( $certificate['logo_position']['y'] ); ?>" />
						<?php _e( 'Width', 'coursepress' ); ?>:
						<input type="number" class="small-text" name="meta_logo_position[width]" value="<?php echo esc_attr( $certificate['logo_position']['width'] ); ?>" />
					</td>
				</tr>
				<tr>
					<td><label><?php _e( 'Content Margin', 'coursepress' ); ?></label></td>
					<td>
						<?php _e( 'Top', 'coursepress' ); ?>:
						<input type="number" class="small-text" name="meta_cert_margin[top]" value="<?php echo esc_attr( $certificate['margin']['top'] ); ?>" />
						<?php _e( 'Left', 'coursepress' ); ?>:
						<input type="number" class="small-text" name="meta_cert_margin[left]" value="<?php echo esc_attr( $certificate['margin']['left'] ); ?>" />
						<?php _e( 'Right', 'coursepress' ); ?>:
						<input type="number" class="small-text" name="meta_cert_margin[right]" value="<?php echo esc_attr( $certificate['margin']['right'] ); ?>" />
					</td>
				</tr>
				<tr>
					<td><label><?php _e( 'Page Orientation', 'coursepress' ); ?></label></td>
					<td>
						<label style="float:left;margin-right:25px;">
							<input type="radio" name="meta_page_orientation" value="L" <?php checked( 'L', $certificate['orientation'] ); ?> /> <?php _e( 'Landscape', 'coursepress' ); ?>
						</label>
						<label style="float:left;">
							<input type="radio" name="meta_page_orientation" value="P" <?php checked( 'P', $certificate['orientation'] ); ?>/> <?php _e( 'Portrait', 'coursepress' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td><label><?php _e( 'Text Color', 'coursepress' ); ?></label></td>
					<td>
						<label for="meta_cert_text_color">
						</label>
						<input
							type="text"
							name="meta_cert_text_color"
							id="meta_cert_text_color"
							class="certificate-color-picker"
							value="<?php echo esc_attr($certificate['text_color']); ?>"/>
					</td>
					<?php
						wp_enqueue_script( 'wp-color-picker' );
						wp_enqueue_style( 'wp-color-picker' );
					?>
				</tr>
			</table>
		</div>
	</div>

	<?php
	/**
	 * Trigger to add additional fields in step 6.
	 **/
	echo apply_filters( 'coursepress_course_setup_step_7', '', $course_id );

	// Show button
	echo CoursePress_View_Admin_Course_Edit::get_buttons( $course_id, 7, array( 'next' => false ) );
	?>
</div>
