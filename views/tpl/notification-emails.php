<script type="text/template" id="coursepress-notification-emails-tpl">

	<div class="cp-flex cp-box">

		<div class="cp-div">
			<label class="label"><?php _e( 'Students from', 'cp' ); ?></label>
			<select name="course" id="cp-course">
				<option value="0"><?php _e( 'All Courses', 'cp' ); ?></option>
				<?php if ( ! empty( $courses ) ) : ?>
					<?php foreach ( $courses as $course ) : ?>
						<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</div>

		<div class="cp-div">
			<label class="label"><?php _e( 'Completed unit', 'cp' ); ?></label>
			<select name="unit" data-placeholder="<?php _e( 'Select course to see unit', 'cp' ); ?>" id="cp-unit"></select>
		</div>
	</div>

	<div class="cp-div cp-sep">
		<label class="label"><?php _e( 'Manually add students', 'cp' ); ?></label>
		<div class="cp-input-clear">
			<select name="student" data-placeholder="<?php _e( 'Begin typing student name', 'cp' ); ?>" id="cp-student"></select>
		</div>
		<button type="button" class="cp-btn cp-btn-active" id="cp-add-student-btn"><?php _e( 'Add Student', 'cp' ); ?></button>
	</div>

	<div class="clear-bottom"></div>

	<div class="cp-div cp-sep">
		<label class="label"><?php _e( 'Student selection', 'cp' ); ?></label>
		<ul id="cp-notifications-students" class="cp-tagged-list">
			<li data-user-id="0"><?php _e( 'Students from All Courses', 'cp' ); ?></li>
		</ul>
	</div>

	<div class="cp-box-content">
		<div class="box-label-area">
			<div class="cp-div">
				<h3 class="label"><?php _e( 'Email subject', 'cp' ); ?></h3>
			</div>
		</div>
		<div class="box-inner-content">
			<input type="text" class="widefat" name="notification_title" id="notification-title" required="required">
		</div>
	</div>

	<div class="cp-box-content">
		<div class="box-label-area">
			<div class="cp-div">
				<h3 class="label"><?php _e( 'Email body', 'cp' ); ?></h3>
			</div>
		</div>
		<div class="box-inner-content">
			<?php echo coursepress_alert_message( $tokens ); ?>
			<?php coursepress_teeny_editor( '', 'notification_content', array( 'textarea_name' => 'notification_content', 'textarea_rows' => 5 ) ); ?>
		</div>
	</div>

	<div class="course-footer">
		<button type="button" class="cp-btn cp-btn-active cp-send-email"><i class="fa fa-circle-o-notch fa-spin"></i><?php _e( 'Send Email', 'cp' ); ?></button>
	</div>

</script>