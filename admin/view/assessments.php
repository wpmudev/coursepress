<?php
$user_id = get_current_user_id();
$courses = $this->get_assessable_courses( $user_id );
$courses = (array) $courses;
$courses = array_filter( $courses );
$selected_course = isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : 0;
$active_unit = ! empty( $_REQUEST['unit'] ) ? $_REQUEST['unit'] : 'all';
$grade_type = ! empty( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'all';
$orderby = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'login';
$order = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
$search = isset( $_REQUEST['search'] ) ? $_REQUEST['search'] : '';

if ( 0 == $selected_course && ! empty( $courses ) ) {
	$selected_course = $courses[0]->ID;
}
$units = CoursePress_Data_Course::get_units( $selected_course );
$nonce = wp_create_nonce( 'cp_get_units' );
$base_location = remove_query_arg( array( 'unit', 'type', 'paged' ) );
?>
<input type="hidden" id="base_location" value="<?php echo esc_url( $base_location ); ?>" />
<input type="hidden" id="assessment-orderby" value="<?php echo esc_attr( $orderby ); ?>" />
<input type="hidden" id="assessment-order" value="<?php echo esc_attr( $order ); ?>" />
<div class="wrap coursepress_wrapper coursepress-assessment">
	<h2><?php esc_html_e( 'Assessments', 'coursepress' ); ?></h2>

	<?php if ( empty( $courses ) ) :  ?>
		<p class="description"><?php esc_html_e( 'No assessable courses found.', 'coursepress' ); ?></p>
	<?php else : ?>
		<div class="cp-assessment-page" data-nonce="<?php echo $nonce; ?>">
			<div class="cp-course-selector">
				<div class="cp-box">
					<label><?php esc_html_e( 'Select course', 'coursepress' ); ?></label>
					<select id="course-list" class="medium dropdown">
						<?php foreach ( $courses as $course_id => $course ) :  ?>
							<option value="<?php echo $course->ID; ?>" <?php selected( $course->ID, $selected_course ); ?>><?php echo apply_filters( 'the_title', $course->post_title, $course->ID ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="cp-box">
					<select id="unit-list" class="medium dropdown">
						<option value="all" <?php selected( $active_unit, 'all' ); ?>><?php esc_html_e( 'Show all', 'coursepress' ); ?></option>
						<option value="all_submitted" <?php selected( $active_unit, 'all_submitted' ); ?>><?php esc_html_e( 'Show all assessable students', 'coursepress' ); ?></option>

						<?php foreach ( $units as $unit_id => $unit ) :  ?>
							<option value="<?php echo $unit->ID; ?>" <?php selected( $active_unit, $unit_id ); ?>><?php echo $unit->post_title; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="cp-box">
					<select id="ungraded-list" class="medium dropdown">
						<?php foreach ( array(
							'all' => __( 'Show graded and ungraded students', 'coursepress' ),
							'ungraded' => __( 'Show ungraded students', 'coursepress' ),
							'graded' => __( 'Show graded students', 'coursepress' ),
						) as $ungraded => $ungraded_label ) :  ?>
							<option value="<?php echo $ungraded; ?>" <?php selected( $grade_type, $ungraded ); ?>><?php echo $ungraded_label; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="cp-box">
					<form method="get" class="cp-right assessment-search-student-box">
						<p class="description"><?php esc_html_e( 'Search students by name, username, or email.', 'coursepress' ); ?></p>
                        <input type="text" id="search_student_box" placeholder="<?php esc_attr_e( 'Enter here...', 'coursepress' ); ?>" value="<?php echo esc_attr( $search ); ?>"/>
						<input type="submit" id="search_student_submit" class="button-primary" value="<?php esc_attr_e( 'Search', 'coursepress' ); ?>" />
						<input type="button" id="search_reset" class="button disabled" value="<?php esc_attr_e( 'Reset' ); ?>" />
					</form>
				</div>
			</div>
			<div id="assessment-table-container"></div>
			<div class="cp-loader-info" style="display: none;"><span class="fa fa-spinner fa-spin"></span> <?php esc_html_e( 'Fetching students...', 'coursepress' ); ?></div>
		</div>
	<?php endif; ?>
</div>
