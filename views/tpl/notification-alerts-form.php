<script type="text/template" id="coursepress-notification-alerts-form-tpl">

	<div class="cp-flex cp-box">

		<div class="cp-div">
			<label class="label"><?php _e( 'Pick course', 'cp' ); ?></label>
			<select name="course" id="cp-alert-course">
				<option value="0"><?php _e( 'All Courses', 'cp' ); ?></option>
				<?php if ( ! empty( $courses ) ) : ?>
					<?php foreach ( $courses as $course ) : ?>
						<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</div>

		<div class="cp-div">
			<label class="label"><?php _e( 'Select condition', 'cp' ); ?></label>
			<select name="unit" data-placeholder="<?php _e( 'Select alert condition', 'cp' ); ?>" id="cp-alert-condition">
				<option value="all"><?php _e( 'All students', 'cp' ); ?></option>
				<option value="pass"><?php _e( 'All students who pass this course', 'cp' ); ?></option>
				<option value="fail"><?php _e( 'All students who failed this course', 'cp' ); ?></option>
			</select>
		</div>
	</div>

	<div class="cp-box-content">
		<div class="box-label-area">
			<div class="cp-div">
				<label class="label"><?php _e( 'Alert title', 'cp' ); ?></label>
			</div>
		</div>
		<div class="box-inner-content">
			<input type="text" class="widefat" name="alert_title" id="alert-title" required="required">
		</div>
	</div>

	<div class="cp-box-content">
		<div class="box-label-area">
			<div class="cp-div">
				<label class="label"><?php _e( 'Alert body', 'cp' ); ?></label>
			</div>
		</div>
		<div class="box-inner-content">
			<?php coursepress_teeny_editor( '', 'alert_content', array( 'textarea_name' => 'alert_content', 'textarea_rows' => 5 ) ); ?>
		</div>
	</div>

	<div class="course-footer">
		<button type="button" class="cp-btn cp-btn-active cp-alert-submit"><i class="fa fa-circle-o-notch fa-spin"></i><?php _e( 'Publish', 'cp' ); ?></button>
	</div>
	
</script>