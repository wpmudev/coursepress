<script type="text/template" id="coursepress-import-export-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php esc_html_e( 'Import & Export', 'cp' ); ?></h2>
	</div>

    <?php
	$toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );
	$config = array();
	$disabled = ! CoursePress_Data_Capabilities::can_create_course();

	$config['import'] = array(
		'title' => esc_html__( 'Import', 'cp' ),
		'description' => esc_html__( 'Upload your exported courses to import here.', 'cp' ),
		'fields' => array(
			'replace' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Replace course if exists', 'cp' ),
				'desc' => esc_html__( 'Courses with the same title will be automatically replaced by the new one.', 'cp' ),
				'class' => 'cp-ignore-update-model',
				'disabled' => $disabled,
			),
			'with_students' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Include course students', 'cp' ),
				'desc' => esc_html__( 'Students listing must also included in your export for this to work.', 'cp' ),
				'class' => 'cp-ignore-update-model',
				'disabled' => $disabled,
			),
			'with_comments' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Include course thread/comments', 'cp' ),
				'desc' => esc_html__( 'Comments listing must also included in your export for this to work.', 'cp' ),
				'class' => 'cp-ignore-update-model',
				'disabled' => $disabled,
			),
			'' => array(
				'type' => 'submit',
				'value' => esc_html__( 'Upload file and import', 'cp' ),
				'class' => 'cp-btn cp-btn-active',
				'disabled' => $disabled,
			),
		),
	);
	/**
	 * export
	 */
	$config['export'] = array(
		'title' => esc_html__( 'Export', 'cp' ),
		'description' => esc_html__( 'Select courses to export to another site.', 'cp' ),
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
				'title' => $toggle_input . esc_html__( 'All Courses', 'cp' ),
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
			'title' => $toggle_input . ( empty( $course_title )? esc_html__( '-[This course has no title]-', 'cp' ):$course_title ),
			'data' => array(
				'course-id' => $course_id,
			),
			'class' => 'course',
		);
	}
	$config['export']['fields'] += array(
		'coursepress[export][subtitle]' => array(
			'type' => 'html_text',
			'value' => sprintf( '<h4>%s</h4>', esc_html__( 'Export Options', 'cp' ) ),
		),
		'coursepress[export][students]' => array(
			'type' => 'checkbox',
			'title' => $toggle_input . esc_html__( 'Include course students', 'cp' ),
			'desc' => esc_html__( 'Will include course students and their course submission progress.', 'cp' ),
		),
		'coursepress[export][comments]' => array(
			'type' => 'checkbox',
			'title' => $toggle_input . esc_html__( 'Include course thread/comments', 'cp' ),
			'desc' => esc_html__( 'Will include comments from Course forum and discussion modules.', 'cp' ),
			'disabled' => true,
		),
		'coursepress[export][button]' => array(
			'id' => 'coursepress-export-button',
			'type' => 'submit',
			'value' => esc_html__( 'Export Courses', 'cp' ),
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
            <h2 class="label"><?php echo esc_html( $option['title'] ); ?></h2>
    <?php
	if ( isset( $option['description'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $option['description'] ) );
	}
	?>
        </div>
        <div class="box-inner-content">
            <form method="post" id="form-<?php echo esc_attr( $option_key ); ?>" class="coursepress-form" enctype="multipart/form-data">
                <?php if ( 'import' === $option_key ) : ?>
                    <input type="file" name="import"<?php echo $disabled ? ' disabled="disabled"' : ''; ?> />
                    <div class="cp-alert cp-alert-error"></div>
                <?php elseif ( 'export' === $option_key ) : ?>

                <?php endif; ?>
<?php
foreach ( $option['fields'] as $key => $data ) {
	?>
	<div class="option option-<?php echo esc_html( $key ); ?>">
    <?php
	if ( isset( $data['label'] ) ) {
		printf( '<h3>%s</h3>', esc_html( $data['label'] ) );
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
