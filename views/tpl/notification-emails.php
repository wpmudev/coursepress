<script type="text/template" id="coursepress-notification-emails-tpl">

	<div class="cp-flex cp-box">

		<div class="cp-div">
			<h3 class="label label-small"><?php esc_html_e( 'Students from', 'cp' ); ?></h3>
			<select name="course" id="cp-course">
				<option value="0"><?php esc_html_e( 'All Courses', 'cp' ); ?></option>
				<?php if ( ! empty( $courses ) ) : ?>
					<?php foreach ( $courses as $course ) : ?>
						<option value="<?php echo esc_attr( $course->ID ); ?>"><?php echo esc_html( $course->post_title ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</div>

		<div class="cp-div">
			<h3 class="label label-small"><?php esc_html_e( 'Completed unit', 'cp' ); ?></h3>
			<select name="unit" data-placeholder="<?php esc_html_e( 'Select course to see unit', 'cp' ); ?>" id="cp-unit"></select>
		</div>
	</div>

	<div class="cp-div cp-sep">
		<h3 class="label label-small"><?php esc_html_e( 'Manually add students', 'cp' ); ?></h3>
		<div class="cp-input-clear">
			<select name="student" data-placeholder="<?php esc_html_e( 'Begin typing student name', 'cp' ); ?>" id="cp-student">
				<?php if ( ! empty( $students ) ) : ?>
					<option></option>
					<?php foreach ( $students as $student ) : ?>
						<option value="<?php echo esc_attr( $student->ID ); ?>"><?php echo esc_html( $student->get_name() ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</div>
		<button type="button" class="cp-btn cp-btn-active" id="cp-add-student-btn"><?php esc_html_e( 'Add Student', 'cp' ); ?></button>
	</div>

	<div class="clear-bottom"></div>

	<div class="cp-div cp-sep">
		<h3 class="label"><?php esc_html_e( 'Student selection', 'cp' ); ?></h3>
		<ul id="cp-notifications-students" class="cp-tagged-list">
			<li data-user-id="0"><?php esc_html_e( 'Students from All Courses', 'cp' ); ?></li>
		</ul>
	</div>

	<div class="cp-box-content">
		<div class="box-label-area">
			<div class="cp-div">
				<h3 class="label"><?php esc_html_e( 'Email subject', 'cp' ); ?></h3>
			</div>
		</div>
		<input type="text" class="widefat" name="notification_title" id="notification-title" required="required">
	</div>

	<?php echo esc_html( coursepress_alert_message( $tokens ) ); ?>
	<div class="cp-box-content">
		<div class="box-label-area">
			<div class="cp-div">
				<h3 class="label"><?php esc_html_e( 'Email body', 'cp' ); ?></h3>
			</div>
		</div>
		<div class="box-inner-content">
			<div id="notification_content"></div>
		</div>
	</div>

	<div class="course-footer">
		<button type="button" class="cp-btn cp-btn-cancel cp-email-cancel" data-page="emails" data-tab="emails"><?php esc_html_e( 'Cancel', 'cp' ); ?></button>
		<button type="button" class="cp-btn cp-btn-active cp-send-email"><i class="fa fa-circle-o-notch fa-spin"></i><?php esc_html_e( 'Send Email', 'cp' ); ?></button>
	</div>

</script>
