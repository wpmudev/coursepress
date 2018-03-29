<script type="text/template" id="coursepress-import-export-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Import & Export', 'cp' ); ?></h2>
	</div>

    <?php
	$toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );
	$config = array();
	$disabled = ! CoursePress_Data_Capabilities::can_create_course();

	$config['import'] = array(
		'title' => __( 'Import', 'CoursePress' ),
		'description' => __( 'Upload your exported courses to import here.', 'CoursePress' ),
		'fields' => array(
			'replace' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . __( 'Replace course if exists', 'CoursePress' ),
				'desc' => __( 'Courses with the same title will be automatically replaced by the new one.', 'CoursePress' ),
				'class' => 'cp-ignore-update-model',
				'disabled' => $disabled,
			),
			'with_students' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . __( 'Include course students', 'CoursePress' ),
				'desc' => __( 'Students listing must also included in your export for this to work.', 'CoursePress' ),
				'class' => 'cp-ignore-update-model',
				'disabled' => $disabled,
			),
			'with_comments' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . __( 'Include course thread/comments', 'CoursePress' ),
				'desc' => __( 'Comments listing must also included in your export for this to work.', 'CoursePress' ),
				'class' => 'cp-ignore-update-model',
				'disabled' => $disabled,
			),
			'' => array(
				'type' => 'submit',
				'value' => __( 'Upload file and import', 'CoursePress' ),
				'class' => 'cp-btn cp-btn-active',
				'disabled' => $disabled,
			),
		),
	);
	/**
	 * export
	 */
	$config['export'] = array(
		'title' => __( 'Export', 'CoursePress' ),
		'description' => __( 'Select courses to export to another site.', 'CoursePress' ),
		'fields' => array(
			'_wpnonce' => array(
				'type' => 'hidden',
				'value' => wp_create_nonce( 'export_courses' ),
			),
			'cp_action' => array(
				'type' => 'hidden',
				'value' => 'export_courses',
			),
			'coursepress[all]' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . __( 'All Courses', 'CoursePress' ),
				'data' => array(
					'course-id' => 'all',
				),
			),
		),
	);
	/**
	 * Courses list
	 */
	$list = coursepress_get_courses( array( 'posts_per_page' => -1 ) );

	foreach ( $list as $course ) {
		$course_id = $course->__get( 'ID' );
		// If course is not editable by current user, do not export.
		if ( ! CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
			continue;
		}
		$course_title = $course->__get( 'post_title' );
		$config['export']['fields'][ 'coursepress[courses]['.$course_id.']' ] = array(
			'type' => 'checkbox',
			'title' => $toggle_input . ( empty( $course_title )? __( '-[This course has no title]-', 'CoursePress' ):$course_title ),
			'data' => array(
				'course-id' => $course_id,
			),
			'class' => 'course',
		);
	}
	$config['export']['fields'] += array(
		'coursepress[export][subtitle]' => array(
			'type' => 'html_text',
			'value' => sprintf( '<h4>%s</h4>', esc_html__( 'Export Options', 'CoursePress' ) ),
		),
		'coursepress[export][students]' => array(
			'type' => 'checkbox',
			'title' => $toggle_input . __( 'Include course students', 'CoursePress' ),
			'desc' => __( 'Will include course students and their course submission progress.', 'CoursePress' ),
		),
		'coursepress[export][comments]' => array(
			'type' => 'checkbox',
			'title' => $toggle_input . __( 'Include course thread/comments', 'CoursePress' ),
			'desc' => __( 'Will include comments from Course forum and discussion modules.', 'CoursePress' ),
		),
		'coursepress[export][button]' => array(
			'id' => 'coursepress-export-button',
			'type' => 'submit',
			'value' => __( 'Export Courses', 'CoursePress' ),
			'class' => 'cp-btn cp-btn-active',
		),
	);

	/**
	 * Fire to get all options.
	 *
	 * @since 3.0
	 * @param array $extensions
	 */
	$option_name = sprintf( 'coursepress_%s', basename( __FILE__, '.php' ) );
	$options = apply_filters( $option_name, $config );

	foreach ( $options as $option_key => $option ) {
	?>
    <div class="cp-box-content">
        <div class="box-label-area">
            <h2 class="label"><?php echo $option['title']; ?></h2>
    <?php
	if ( isset( $option['description'] ) ) {
		printf( '<p class="description">%s</p>', $option['description'] );
	}
	?>
        </div>
        <div class="box-inner-content">
            <form method="post" id="form-<?php echo $option_key; ?>" class="coursepress-form" enctype="multipart/form-data">
                <?php if ( 'import' == $option_key ) : ?>
                    <input type="file" name="import"<?php echo $disabled ? ' disabled="disabled"' : ''; ?> />
                    <div class="cp-alert cp-alert-error"></div>
                <?php elseif ( 'export' == $option_key ) : ?>

                <?php endif; ?>
<?php
foreach ( $option['fields'] as $key => $data ) {
	?>
	<div class="option option-<?php esc_attr_e( $key ); ?>">
    <?php
	if ( isset( $data['label'] ) ) {
		printf( '<h3>%s</h3>', $data['label'] );
	}
	$data['name'] = $key;
	lib3()->html->element( $data );
	?>
	</div>
    <?php
}
	?>
            </form>
    </div>
    <?php
	}
	?>
</script>
