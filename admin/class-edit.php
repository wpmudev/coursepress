<?php
/**
 * Course Edit
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Edit extends CoursePress_Utility {
	static $course_id = 0;
	static $settings = array();
	static $setup_marker = 0;

	public static $slug = 'coursepress_course';
	private static $action = 'new';
	private static $allowed_actions = array(
		'new',
		'edit',
	);
	private static $tabs = array();
	private static $current_course = false;

	public static function init_hooks( $post ) {
		$post_type = CoursePress_Data_Course::get_post_type_name();

		if ( $post->post_type != $post_type ) {
			return;
		}

		do_action( 'coursepress_admin_render_page' );

		self::$current_course = $post;

		if ( 'auto-draft' !== $post->post_status || ! empty( $_GET['post'] ) ) {
			self::$action = 'edit';
		}

		$tab = empty( $_GET['tab'] ) ? 'setup' : $_GET['tab'];
		add_action( 'edit_form_top', array( __CLASS__, 'edit_tabs' ) );

		// Filter metabox to render
		add_action( 'add_meta_boxes', array( __CLASS__, 'allowed_meta_boxes' ), 999 );

		if ( 'setup' == $tab ) {

			// Change preview link
			add_filter( 'preview_post_link', array( __CLASS__, 'preview_post_link' ), 10, 2 );

			// Disable permalink
			add_filter( 'get_sample_permalink_html', array( __CLASS__, 'disable_permalink' ), 100, 5 );

			// Start wrapper
			add_action( 'edit_form_after_editor', array( __CLASS__, 'start_wrapper' ) );

			// Step 1
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_1' ) );
			// Step 2
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_2' ) );
			// Step 3
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_3' ) );
			// Step 4
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_4' ) );
			// Step 5
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_5' ) );
			// Step 6
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_6' ) );
			// Step 7
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_7' ) );
			// Allow hooks for additional steps
			add_action( 'edit_form_after_editor', array( __CLASS__, 'other_steps' ) );

			// End wrapper
			add_action( 'edit_form_after_editor', array( __CLASS__, 'end_wrapper' ) );
		} else {
			$_GET['id'] = $_REQUEST['id'] = self::$current_course->ID;
			add_action( 'admin_footer', array( __CLASS__, 'disable_style' ), 100 );
		}
	}

	public static function allowed_meta_boxes() {
		global $wp_meta_boxes;

		$post_type = CoursePress_Data_Course::get_post_type_name();

		if ( ! empty( $wp_meta_boxes[ $post_type ] ) ) {
			$cp_metaboxes = $wp_meta_boxes[ $post_type ];

			/**
			 * Note: Add third party meta_box ID here to be included in CP edit UI!
			 **/
			$allowed = array(
				'submitdiv',
				'course_categorydiv',
				'slugdiv',
				'wpseo_meta',
			);

			/**
			 * Filter the allowed meta boxes to be rendered
			 **/
			$allowed = apply_filters( 'coursepress_allowed_meta_boxes', $allowed );

			foreach ( $cp_metaboxes as $group => $groups ) {
				foreach ( $groups as $location => $metaboxes ) {
					foreach ( $allowed as $key ) {
						if ( ! isset( $metaboxes[ $key ] ) ) {
							unset( $cp_metaboxes[ $group ][ $location ][ $key ] );
						}
					}
				}
			}
			// Restore metaboxes
			$wp_meta_boxes[ $post_type ] = $cp_metaboxes;
		}

		// Remove media buttons hooks
		remove_all_actions( 'media_buttons' );
		// Enable 'Add Media' button
		add_action( 'media_buttons', 'media_buttons' );
	}

	/**
	 * Disable metabox containers. It looks ugly on units and students tabs.
	 **/
	public static function disable_style() {
		?>
		<style>
		#postbox-container-1,
		#postbox-container-2 {
			display: none;
		}
		</style>
		<?php
	}

	public static function preview_post_link( $preview_link, $post ) {
		$preview_link = CoursePress_Data_Course::get_course_url( $post->ID );

		return $preview_link;
	}

	public static function disable_permalink( $return, $post_id, $new_title, $new_slug, $post ) {
		return '';
	}

	private static function _current_action() {
		return self::$action;
	}

	public static function get_tabs() {
		// Make it a filter so we can add more tabs easily
		self::$tabs = apply_filters( self::$slug . '_tabs', self::$tabs );

		self::$tabs['setup'] = array(
			'title' => __( 'Course Setup', 'CP_TD' ),
			'description' => __( 'Edit your course specific settings below.', 'CP_TD' ),
			'order' => 10,
			'buttons' => 'none',
			'is_form' => false,
		);
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		if ( 'edit' == self::_current_action() ) {
			if ( CoursePress_Data_Capabilities::can_view_course_units( $course_id ) ) {
				$units = CoursePress_Data_Course::get_unit_ids( $course_id, array( 'publish', 'draft' ) );
				self::$tabs['units'] = array(
					'title' => sprintf( __( 'Units (%s)', 'CP_TD' ), count( $units ) ),
					'description' => __( 'Edit your course specific settings below.', 'CP_TD' ),
					'order' => 20,
					'buttons' => 'none',
					'is_form' => false,
				);
			}

			if ( CoursePress_Data_Capabilities::can_view_course_students( $course_id ) ) {
				self::$tabs['students'] = array(
					'title' => sprintf(
						__( 'Students (%s)', 'CP_TD' ),
						CoursePress_Data_Course::count_students( $course_id )
					),
					'description' => __( 'Edit your course specific settings below.', 'CP_TD' ),
					'order' => 30,
					'buttons' => 'none',
					'is_form' => false,
				);
			}
		}

		// Make sure that we have all the fields we need
		foreach ( self::$tabs as $key => $tab ) {
			self::$tabs[ $key ]['url'] = add_query_arg( 'tab', $key, remove_query_arg( 'message' ) );
			self::$tabs[ $key ]['buttons'] = isset( $tab['buttons'] ) ? $tab['buttons'] : 'both';
			self::$tabs[ $key ]['class'] = isset( $tab['class'] ) ? $tab['class'] : '';
			self::$tabs[ $key ]['is_form'] = isset( $tab['is_form'] ) ? $tab['is_form'] : true;
			self::$tabs[ $key ]['order'] = isset( $tab['order'] ) ? $tab['order'] : 999; // Set default order to 999... bottom of the list
		}

		// Order the tabs
		self::$tabs = CoursePress_Helper_Utility::sort_on_key( self::$tabs, 'order' );

		return self::$tabs;
	}

	public static function updated_messages( $messages ) {
		global $typenow;

		$post_type = CoursePress_Data_Course::get_post_type_name();

		if ( $typenow == $post_type ) {
			$post_messages = $messages['post'];

			foreach ( $post_messages as $pos => $msg ) {
				$msg = str_replace( 'Post', ucfirst( $post_type ), $msg );
				$post_messages[ $pos ] = $msg;
			}
			$messages['post'] = $post_messages;
		}

		return $messages;
	}

	public static function edit_tabs() {
		$course = self::$current_course;
		$tabs = self::get_tabs();
		$tab_keys = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';
		$tab = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );

		$method = preg_replace( '/\_$/', '', 'render_tab_' . $tab );
		$content = '';

		if ( method_exists( __CLASS__, $method ) ) {
			$content = call_user_func( __CLASS__ . '::' . $method );
		}

		$hidden_args = $_GET;
		unset( $hidden_args['_wpnonce'] );

		// Publish Course Toggle
		$course_id = $course->ID;
		$status = get_post_status( $course_id );
		$user_id = get_current_user_id();
		$publish_toggle = '';

		$ui = array(
			'label' => __( 'Publish Course', 'CP_TD' ),
			'left' => '<i class="fa fa-ban"></i>',
			'left_class' => 'red',
			'right' => '<i class="fa fa-check"></i>',
			'right_class' => 'green',
			'state' => 'publish' === $status ? 'on' : 'off',
			'data' => array(
				'nonce' => wp_create_nonce( 'publish-course' ),
			),
		);
		$ui['class'] = 'course-' . $course_id;
		$publish_toggle = '';

		if ( 'edit' == self::$action && CoursePress_Data_Capabilities::can_change_course_status( $course_id ) && $status !== 'auto-draft' ) {
			$publish_toggle = ! empty( $course_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-course-toggle', 'publish-course-toggle', $ui ) : '';
		}
		echo CoursePress_Helper_Tabs::render_tabs( $tabs, $content, $hidden_args, self::$slug, $tab, false, 'horizontal', $publish_toggle );
	}

	public static function start_wrapper() {
		// Setup Nonce
		$setup_nonce = wp_create_nonce( 'setup-course' );

		CoursePress_View_Admin_Course_Edit::$current_course = self::$current_course;
		printf( '<input type="hidden" id="edit_course_link_url" value="%s" />',
		esc_url( get_edit_post_link( self::$current_course->ID ) ) );
		echo '<div class="coursepress-course-step-container">
			<div id="course-setup-steps" data-nonce="' . $setup_nonce . '">';
	}

	public static function end_wrapper() {
		echo '</div></div>';
	}

	private static function render_tab_units() {
		return CoursePress_Admin_Controller_Unit::render();
	}

	private static function render_tab_students() {
		return CoursePress_View_Admin_Course_Student::render();
	}

	/**
	 * Build course step buttons
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param integer $step Step.
	 * @param array $args Array of buttons to show info, default is true, use
	 * to disable selected button e.g. 'next' => false
	 *
	 * @return string Buttons.
	 */
	static function get_buttons( $course_id, $step, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'previous' => true,
				'next' => true,
				'update' => true,
			)
		);
		$content = '';

		if ( $args['previous'] ) {
			$content .= sprintf(
				'<input type="button" class="button step prev step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Previous', 'CP_TD' )
			);
		}

		if ( $args['next'] ) {
			$content .= sprintf(
				'<input type="button" class="button step next step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Next', 'CP_TD' )
			);
		}

		// Finish button
		if ( 7 == $step ) {
			$content .= sprintf(
				'<input type="button" class="button step finish step-7" value="%s" />',
				esc_attr__( 'Finish', 'CP_TD' )
			);
		}
		/**
		 * update button
		 */
		if ( $args['update'] && CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
			$content .= sprintf(
				'<input type="button" class="button step update hidden step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Update', 'CP_TD' )
			);
		}
		/**
		 * if empty, do not use wrapper!
		 */
		if ( empty( $content ) ) {
			return $content;
		}
		return sprintf( '<div class="wide course-step-buttons">%s</div>', $content );
	}

	/**
	 * Get Wp Editor.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $editor_id WP Editor ID
	 * @param string $editor_name WP Editor name
	 * @param string $editor_content Edited content.
	 * @param array $args WP Editor args, see
	 * https://codex.wordpress.org/Function_Reference/wp_editor#Parameters
	 * @return string WP Editor.
	 */

	static function get_wp_editor( $editor_id, $editor_name, $editor_content = '', $args = array() ) {
		wp_enqueue_script( 'post' );
		$_wp_editor_expand = $_content_editor_dfw = false;

		$post_type = CoursePress_Data_Course::get_post_type_name();
		global $is_IE;

		if (
			! wp_is_mobile()
			&& ! ( $is_IE && preg_match( '/MSIE [5678]/', $_SERVER['HTTP_USER_AGENT'] ) )
			&& apply_filters( 'wp_editor_expand', true, $post_type )
		) {

			wp_enqueue_script( 'editor-expand' );
			$_content_editor_dfw = true;
			$_wp_editor_expand = ( get_user_setting( 'editor_expand', 'on' ) === 'on' );
		}

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}

		/** This filter is documented in wp-includes/class-wp-editor.php  */
		//add_filter( 'teeny_mce_plugins', array( __CLASS__, 'teeny_mce_plugins' ) );

		$defaults = array(
			'_content_editor_dfw' => $_content_editor_dfw,
			'drag_drop_upload' => true,
			'tabfocus_elements' => 'content-html,save-post',
			'textarea_name' => $editor_name,
			'editor_class' => 'cp-editor cp-course-overview',
			'media_buttons' => false,
			'editor_height' => 300,
			'tinymce' => array(
				'resize' => false,
				'wp_autoresize_on' => $_wp_editor_expand,
				'add_unload_trigger' => false,
			),
		);
		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		ob_start();
		wp_editor( $editor_content, $editor_id, $args );
		$editor_html = sprintf( '<div class="postarea%s">', $_wp_editor_expand? ' wp-editor-expand':'' );
		$editor_html .= ob_get_clean();
		$editor_html .= '</div>';
		return $editor_html;
	}

	/**
	 * Step 1 - Course Overview
	 **/
	static function step_1() {
		self::$course_id = $course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		self::$settings = $settings = CoursePress_Data_Course::get_setting( $course_id, true );
		self::$setup_marker = $setup_marker = (int) $settings['setup_marker'];
		$setup_class = $settings['setup_step_1'];
		$setup_class = 6 === $setup_marker || 0 === $setup_marker ? $setup_class . ' setup_marker' : $setup_class;
		$editor_content = ! empty( self::$current_course ) ? self::$current_course->post_excerpt : '';
		$editor_content = htmlspecialchars_decode( $editor_content, true );

		self::render( 'admin/view/steps/step-1', array(
			'course_id' => $course_id,
			'setup_class' => $setup_class,
			'course_name' => ! empty( self::$current_course ) ? self::$current_course->post_title : '',
			'editor_content' => $editor_content,
			'language' => $settings['course_language'],
		) );
	}

	/**
	 * Step 2 - Course Details
	 **/
	static function step_2() {
		$setup_class = self::$settings['setup_step_2'];
		$setup_class = self::$setup_marker === 1 ? $setup_class . ' setup_marker' : $setup_class;

		$units = CoursePress_Data_Course::get_units_with_modules( self::$course_id, array( 'publish', 'draft' ) );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );

		self::render( 'admin/view/steps/step-2', array(
			'course_id' => self::$course_id,
			'setup_class' => $setup_class,
			'supported_ext' => implode( ', ', wp_get_video_extensions() ),
			'editor_content' => ! empty( self::$current_course ) ? self::$current_course->post_content : '',
			'course_view' => self::$settings['course_view'],
			'focus_hide_section' => ! empty( self::$settings['focus_hide_section'] ),
			'structure_level' => ! empty( self::$settings['structure_level'] ) ? self::$settings['structure_level'] : 'unit',
			'structure_visible' => ! empty( self::$settings['structure_visible'] ),
			'structure_show_duration' => ! empty( self::$settings['structure_show_duration'] ),
			'units' => $units,
			'duration_class' => ! empty( self::$settings['structure_show_duration'] ) ? '' : 'hidden',
		) );
	}

	/**
	 * Step 3 - Instructors and Facilitators
	 **/
	static function step_3() {
		$setup_class = self::$settings['setup_step_3'];
		$setup_class = 2 == self::$setup_marker ? $setup_class . ' setup_marker' : $setup_class;
		$can_assign_instructor = CoursePress_Data_Capabilities::can_assign_course_instructor( self::$course_id );
		$can_assign_facilitator = CoursePress_Data_Capabilities::can_assign_facilitator( self::$course_id );

		$label = $description = $placeholder = '';

		if ( $can_assign_instructor && $can_assign_facilitator ) {
			$label = esc_html__( 'Invite New Instructor or Facilitator', 'CP_TD' );
			$description = esc_html__( 'If the instructor or the facilitator can not be found in the list above, you will need to invite them via email.', 'CP_TD' );
			$placeholder = __( 'instructor-or-facilitator@email.com', 'CP_TD' );

		} else if ( $can_assign_instructor ) {
			$label = esc_html__( 'Invite New Instructor', 'CP_TD' );
			$description = esc_html__( 'If the instructor can not be found in the list above, you will need to invite them via email.', 'CP_TD' );
			$placeholder = __( 'facilitator@email.com', 'CP_TD' );

		} else if ( $can_assign_facilitator ) {
			$label = esc_html__( 'Invite New Facilitator', 'CP_TD' );
			$description = esc_html__( 'If the facilitator can not be found in the list above, you will need to invite them via email.', 'CP_TD' );
			$placeholder = __( 'instructor@email.com', 'CP_TD' );
		}

		self::render( 'admin/view/steps/step-3', array(
			'course_id' => self::$course_id,
			'setup_class' => $setup_class,
			'can_assign_instructor' => $can_assign_instructor,
			'search_nonce' => wp_create_nonce( 'coursepress_instructor_search' ),
			'instructors' => CoursePress_Helper_UI::course_instructors_avatars( self::$course_id, array( 'remove_buttons' => true, 'count' => true ) ),
			'can_assign_facilitator' => $can_assign_facilitator,
			'facilitators' => CoursePress_Data_Facilitator::get_course_facilitators( self::$course_id ),
			'facilitator_search_nonce' => $search_nonce = wp_create_nonce( 'coursepress_search_users' ),
			'label' => $label,
			'description' => $description,
			'placeholder' => $placeholder,
		));
	}

	static function step_4() {
		$setup_class = self::$settings['setup_step_4'];
		$setup_class = 3 == self::$setup_marker ? $setup_class . ' setup_marker' : $setup_class;

		self::render( 'admin/view/steps/step-4', array(
			'course_id' => self::$course_id,
			'setup_class' => $setup_class,
			'open_ended_course' => ! empty( self::$settings['course_open_ended'] ),
			'course_start_date' => self::$settings['course_start_date'],
			'course_end_date' => self::$settings['course_end_date'],
			'enrollment_open_ended' => ! empty( self::$settings['enrollment_open_ended'] ),
			'enrollment_start_date' => self::$settings['enrollment_start_date'],
			'enrollment_end_date' => self::$settings['enrollment_end_date'],
		) );
	}

	public static function step_5() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_5', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 4 ? $setup_class . ' setup_marker' : $setup_class;
		$content = '
			<div class="step-title step-5">' . esc_html__( 'Step 5 &ndash; Classes, Discussion & Workbook', 'CP_TD' ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-5">
				<input type="hidden" name="meta_setup_step_5" value="saved" />
			';

		$limit_checked = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'class_limited', false ) );
		$limited = ! empty( $limit_checked );
		$content .= '
				<div class="wide class-size">
					<label>' .
					esc_html__( 'Class Size', 'CP_TD' ) . '
					</label>
					<p class="description">' . esc_html__( 'Use this setting to set a limit for all classes. Uncheck for unlimited class size(s).', 'CP_TD' ) . '</p>
					<label class="narrow col">
						<input type="checkbox" name="meta_class_limited" ' . $limit_checked . ' />
						<span>' . esc_html__( 'Limit class size', 'CP_TD' ) . '</span>
					</label>

					<label class="num-students narrow col ' . ( $limited ? '' : 'disabled' ) . '">
						' . esc_html__( 'Number of students', 'CP_TD' ) . '
						<input type="text" class="spinners" name="meta_class_size" value="' . CoursePress_Data_Course::get_setting( $course_id, 'class_size', '' ) . '" ' . ( $limited ? '' : 'disabled="disabled"' ) . '/>
					</label>
				</div>';

		$checkboxes = array(
			array(
				'meta_key' => 'allow_discussion',
				'title' => __( 'Course Discussion', 'CP_TD' ),
				'description' => __( 'If checked, students can post questions and receive answers at a course level. A \'Discusssion\' menu item is added for the student to see ALL discussions occuring from all class members and instructors.', 'CP_TD' ),
				'label' => __( 'Allow course discussion', 'CP_TD' ),
				'default' => false,
			),
			array(
				'meta_key' => 'allow_workbook',
				'title' => __( 'Student Workbook', 'CP_TD' ),
				'description' => __( 'If checked, students can see their progress and grades.', 'CP_TD' ),
				'label' => __( 'Show student workbook', 'CP_TD' ),
				'default' => false,
			),
			array(
				'meta_key' => 'allow_grades',
				'title' => __( 'Student grades', 'CP_TD' ),
				'description' => __( 'If checked, students can see their grades.', 'CP_TD' ),
				'label' => __( 'Show student grades', 'CP_TD' ),
				'default' => false,
			),
		);
		foreach ( $checkboxes as $one ) {
			$content .= CoursePress_Helper_UI::course_edit_checkbox( $one, $course_id );
		}

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_5', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 5 );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_6() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		// Payment can be disabled using the COURSEPRESS_DISABLE_PAYMENT constant or hooking the filter
		$disable_payment = defined( 'COURSEPRESS_DISABLE_PAYMENT' ) && true == COURSEPRESS_DISABLE_PAYMENT;
		$disable_payment = apply_filters( 'coursepress_disable_course_payments', $disable_payment, $course_id );

		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_6', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 5 ? $setup_class . ' setup_marker' : $setup_class;

		$payment_tagline = ! $disable_payment ? __( ' & Course Cost', 'CP_TD' ) : '';

		$content = '
			<div class="step-title step-6">' . esc_html( sprintf( __( 'Step 6 &ndash; Enrollment%s', 'CP_TD' ), $payment_tagline ) ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-6">
				<!-- depending on gateway setup, this could be save-attention -->
				<input type="hidden" name="meta_setup_step_6" value="saved" />
			';

		// Enrollment Options
		$enrollment_types = CoursePress_Data_Course::get_enrollment_types_array( $course_id );

		$content .= '<div class="wide">';
		$content .= sprintf( '<label>%s</label>', esc_html__( 'Enrollment Restrictions', 'CP_TD' ) );

		$content .= '<p class="description">' . esc_html__( 'Select the limitations on accessing and enrolling in this course.', 'CP_TD' ) . '</p>';
		/**
		 * select
		 */
		$enrollment_type_default = CoursePress_Data_Course::get_enrollment_type_default( $course_id );
		$selected = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_type', $enrollment_type_default );
		$content .= CoursePress_Helper_UI::select( 'meta_enrollment_type', $enrollment_types, $selected, 'chosen-select medium' );
		$content .= '</div>';

		$class = 'prerequisite' === $selected ? '' : 'hidden';
		$content .= '
				<div class="wide enrollment-type-options prerequisite ' . $class . '">';

		$class_extra = is_rtl() ? 'chosen-rtl' : '';
		$content .= '
					<label>' .
					esc_html__( 'Prerequisite Courses', 'CP_TD' ) .
					'</label>
					<p class="description">' . esc_html__( 'Select the courses a student needs to complete before enrolling in this course', 'CP_TD' ) . '</p>
					<select name="meta_enrollment_prerequisite" class="medium chosen-select chosen-select-course ' . $class_extra . '" multiple="true" data-placeholder=" ">
			';

		$courses = CoursePress_Data_Instructor::get_accessable_courses( wp_get_current_user(), true );

		$saved_settings = CoursePress_Data_Course::get_prerequisites( $course_id );

		foreach ( $courses as $course ) {
			/**
			 * exclude current course
			 */
			if ( $course_id == $course->ID ) {
				continue;
			}
			$content .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $course->ID ),
				selected( in_array( $course->ID, $saved_settings ), true, false ),
				esc_html( apply_filters( 'the_title', $course->post_title ) )
			);
		}

		$content .= '
					</select>
				</div>
			';

		$class = 'passcode' === $selected ? '' : 'hidden';
		$content .= '
				<div class="wide enrollment-type-options passcode ' . $class . '">';

		$content .= '
				<label>' .
					esc_html__( 'Course Passcode', 'CP_TD' ) .
					'</label>
				<p class="description">' . esc_html__( 'Enter the passcode required to access this course', 'CP_TD' ) . '</p>
				<input type="text" name="meta_enrollment_passcode" value="' . CoursePress_Data_Course::get_setting( $course_id, 'enrollment_passcode', '' ) . '" />
			';

		$content .= '
				</div>
			';

		$paid_checked = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'payment_paid_course', false ) );
		$is_paid = ! empty( $paid_checked );

		if ( ! $disable_payment ) {
			$one = array(
				'meta_key' => 'payment_paid_course',
				'title' => __( 'Course Payment', 'CP_TD' ),
				'description' => __( 'Payment options for your course. Additional plugins are required and settings vary depending on the plugin.', 'CP_TD' ),
				'label' => __( 'This is a paid course', 'CP_TD' ),
				'default' => false,
			);
			$content .= '<hr class="separator" />';
			$content .= CoursePress_Helper_UI::course_edit_checkbox( $one, $course_id );
		}

		/**
		 * Hook this filter to add payment plugin support
		 */
		$payment_supported = CoursePress_Helper_Utility::is_payment_supported();

		$installed = $activated = false;

		if ( ! $payment_supported && ! $disable_payment ) {
			$install_message = sprintf( '<p>%s</p>', __( 'Please contact your administrator to enable MarketPress for your site.', 'CP_TD' ) );
			if ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins ' ) ) {
				$url = add_query_arg(
					array(
						'post_type' => CoursePress_Data_Course::get_post_type_name(),
						'page' => 'coursepress_settings',
						'tab' => 'extensions',
					),
					admin_url( 'edit.php' )
				);
				$installed = CoursePress_Helper_Extension_MarketPress::installed();
				$text = __( 'To start selling your course, please <a href="%s">install and activate MarketPress</a>.', 'CP_TD' );
				if ( $installed ) {
					$activated = CoursePress_Helper_Extension_MarketPress::activated();
					$text = __( 'To start selling your course, please install and <a href="%s">activate MarketPress</a>.', 'CP_TD' );
					if ( $activated ) {
						$text = __( 'To start selling your course, please <a href="%s">complete setup</a> of of MarketPress.', 'CP_TD' );
						$url = add_query_arg(
							array(
								'post_type' => CoursePress_Data_Course::get_post_type_name(),
								'page' => 'coursepress_settings',
								'tab' => 'marketpress',
							),
							admin_url( 'edit.php' )
						);
					}
				}
				$install_message = sprintf( '<p>%s</p>', sprintf( $text, esc_url_raw( $url ) ) );
			}

			/**
			 * version message
			 */
			$version_message = '';
			if ( ! $installed ) {
				$version_message = sprintf( '<p>%s</p>', __( 'The full version of MarketPress has been bundled with CoursePress.', 'CP_TD' ) );
			}

			/**
			 * Hook this filter to get rid of the payment message
			 */
			$payment_message = sprintf(
				'<div class="payment-message %s"><h4>%s</h4>%s%s<p>%s: WooCommerce</p></div>',
				esc_attr( $is_paid ? '' : 'hidden' ),
				__( 'Sell your courses online with MarketPress.', 'CP_TD' ),
				$version_message,
				$install_message,
				__( 'Other supported plugins', 'CP_TD' )
			);
			$payment_message = apply_filters( 'coursepress_course_payment_message', $payment_message, $course_id );
			// It's already been filtered, but because we're dealing with HTML, lets be sure
			$content .= CoursePress_Helper_Utility::filter_content( $payment_message );
		}

		if ( $payment_supported ) {

			$class = $is_paid ? '' : 'hidden';
			$content .= '<div class="is_paid_toggle ' . $class . '">';
			/**
			 * Add additional fields if 'This is a paid course' is selected.
			 *
			 * Field names must begin with meta_ to allow it to be automatically added to the course settings
			 *
			 * * This is the ideal filter to use for integrating payment plugins
			 */
			$content .= apply_filters( 'coursepress_course_setup_step_6_paid', '', $course_id );

			$content .= '</div>';
		}

		/**
		 * Add additional fields.
		 *
		 * Field names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_6', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 6 );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_7() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_7', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 7 ? $setup_class . ' setup_marker' : $setup_class;

		/**
		 * Pre-Completion Page
		 */
		$pre_completion_title = CoursePress_Data_Course::get_setting( $course_id, 'pre_completion_title', __( 'Almost there!', 'CP_TD' ) );
		$pre_completion_content = sprintf( '<h3>%s</h3>', __( 'Congratulations! You have completed COURSE_NAME!', 'CP_TD' ) );
		$pre_completion_content .= sprintf( '<p>%s</p>', __( 'Your course instructor will now review your work and get back to you with your final grade before issuing you a certificate of completion.', 'CP_TD' ) );
		$pre_completion_content = CoursePress_Data_Course::get_setting( $course_id, 'pre_completion_content', $pre_completion_content );
		$pre_completion_content = htmlspecialchars_decode( $pre_completion_content );

		/**
		 * Course Completion Page
		 */
		$completion_title = CoursePress_Data_Course::get_setting( $course_id, 'course_completion_title', __( 'Congratulations, You Passed!', 'CP_TD' ) );
		$completion_content = sprintf( '<h3>%s</h3><p>%s</p><p>DOWNLOAD_CERTIFICATE_BUTTON</p>',
			__( 'Congratulations! You have successfully completed and passed COURSE_NAME!', 'CP_TD' ),
			__( 'You can download your certificate here.', 'CP_TD' )
		);
		$completion_content = CoursePress_Data_Course::get_setting( $course_id, 'course_completion_content', $completion_content );
		$completion_content = htmlspecialchars_decode( $completion_content );

		$content = '<div class="step-title step-7">'
			. esc_html__( 'Step 7 &ndash; Course Completion', 'CP_TD' )
			. '<div class="status '. $setup_class . '"></div>'
			. '</div>';

		$content .= '<div class="step-content step-7">
			<input type="hidden" name="meta_setup_step_7" value="saved" />';

		// Course completion
		$minimum_grade = CoursePress_Data_Course::get_setting( $course_id, 'minimum_grade_required', 100 );

		$content .= '<div class="wide minimum-grade">';
		$content .= sprintf( '<label class="required" for="meta_minimum_grade_required">%s</label> ', __( 'Minimum Grade Required', 'CP_TD' ) );
		$content .= sprintf( '<input type="number" id="meta_minimum_grade_required" name="meta_minimum_grade_required" value="%d" min="0" max="100" class="text-small" />', esc_attr__( $minimum_grade ) );
		$content .= sprintf(
			'<p class="description">%s</p>',
			__( 'The minimum grade required to marked course completion and send course certficates.', 'CP_TD' )
		);
		$content .= '</div>';

		$tokens = array(
			'COURSE_NAME',
			'COURSE_SUB_TITLE',
			'COURSE_OVERVIEW',
			'COURSE_UNIT_LIST',
			'DOWNLOAD_CERTIFICATE_LINK',
			'DOWNLOAD_CERTIFICATE_BUTTON',
			'STUDENT_WORKBOOK',
		);
		$token_info = '<p class="description" style="margin-bottom: -25px;">'. sprintf( __( 'Use these tokens to display actual course details: %s', 'CP_TD' ), implode( ', ', $tokens ) ) . '</p>';

		// Pre-completion page
		$content .= '<div class="wide page-pre-completion">'
			. '<label>' . __( 'Pre-Completion Page', 'CP_TD' ) . '</label>'
			. '<p class="description">' . __( 'Use the fields below to show custom pre-completion page after the student completed the course but require final assessment from instructors.', 'CP_TD' ) . '</p>'
			. '<label for="meta_pre_completion_title" class="required">' . __( 'Page Title', 'CP_TD' ) . '</label>'
			. '<input type="text" class="wide" name="meta_pre_completion_title" value="'. esc_attr( $pre_completion_title ) . '" />'
			. '<label for="meta_pre_completion_content" class="required">' . __( 'Page Content', 'CP_TD' ) . '</label>'
			. $token_info
		;
		$content .= CoursePress_Helper_Editor::get_wp_editor( 'pre-completion-content', 'meta_pre_completion_content', $pre_completion_content );
		$content .= '</div>';

		$content .= '<div class="wide page-completion">'
			. '<label>' . __( 'Course Completion Page', 'CP_TD' ) . '</label>'
			. '<p class="description">' . __( 'Use the fields below to show a custom page after successfull course completion.', 'CP_TD' ) . '</p>'
			. '<label for="meta_course_completion_title" class="required">' . __( 'Page Title', 'CP_TD' ) . '</label>'
			. '<input type="text" class="widefat" name="meta_course_completion_title" value="'. esc_attr( $completion_title ) . '" />'
		;

		$content .= '<label for="meta_course_completion_content" class="required">' . __( 'Page Content', 'CP_TD' ) . '</label>' . $token_info;
		$content .= CoursePress_Helper_Editor::get_wp_editor( 'course-completion-editor-content', 'meta_course_completion_content', $completion_content );
		$content .= '</div>';

		// Fail info
		$failed_title = CoursePress_Data_Course::get_setting( $course_id, 'course_failed_title', __( 'Sorry, you did not pass this course!', 'CP_TD' ) );
		$failed_content = sprintf( '<p>%s</p><p>%s</p>',
			__( 'Unfortunately, you didn\'t pass COURSE_NAME.', 'CP_TD' ),
			__( 'Better luck next time!', 'CP_TD' )
		);
		$failed_content = CoursePress_Data_Course::get_setting( $course_id, 'course_failed_content', $failed_content );
		$failed_content = htmlspecialchars_decode( $failed_content );

		$content .= '<div class="wide page-failed">
			<label>' . __( 'Failed Page', 'CP_TD' ) . '</label>
			<p class="description">'. __( 'Use the fields below to display failure page when an student completed a course but fail to reach the minimum required grade.', 'CP_TD' ) . '</p>
			<label for="meta_course_failed_title" class="required">'. __( 'Page Title', 'CP_TD' ) . '</label>
			<input type="text" class="widefat" name="meta_course_failed_title" value="'. esc_attr__( $failed_title ) . '" />
			<label for="meta_course_field_content" class="required">'. __( 'Page Content', 'CP_TD' ) . '</label>'
			. $token_info;
		$content .= CoursePress_Helper_Editor::get_wp_editor( 'course-failed-content', 'meta_course_failed_content', $failed_content );
		$content .= '</div>';

		// Basic certificate
		$fields = apply_filters( 'coursepress_basic_certificate_vars',
			array(
				'FIRST_NAME' => '',
				'LAST_NAME' => '',
				'COURSE_NAME' => '',
				'COMPLETION_DATE' => '',
				'CERTIFICATE_NUMBER' => '',
				'UNIT_LIST' => '',
				),
			null
		);
		$field_keys = array_keys( $fields );
		$default_layout = CoursePress_View_Admin_Setting_BasicCertificate::default_certificate_content();
		$certficate_content = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate_layout', $default_layout );
		$certficate_content = htmlspecialchars_decode( $certficate_content );
		$certificate_link = add_query_arg(
			array(
				'nonce' => wp_create_nonce( 'cp_certificate_preview' ),
				'course_id' => $course_id,
			)
		);
		$test_mail_link = add_query_arg(
			array(
				'nonce' => wp_create_nonce( 'cp_certificate_mail' ),
				'course_id' => $course_id,
			)
		);

		$value = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate' );
		$class = cp_is_true( $value )? '':'hidden';

		$content .= '<div class="wide course-certificate">';
		$content .= sprintf( '<br /><h3>%s</h3>', esc_html__( 'Course Certificate', 'CP_TD' ) );
		$content .= sprintf(
			'<a href="%s" target="_blank" class="button button-default btn-cert %s" style="float:right;margin-top:-35px;">%s</a>',
			esc_url( $certificate_link ),
			esc_attr( $class ),
			esc_html__( 'Preview', 'CP_TD' )
		);
		/**
		 * Override Course Certificate
		 */
		$one = array(
			'meta_key' => 'basic_certificate',
			'description' => __( 'Use this field to override general course certificate setting.', 'CP_TD' ),
			'label' => __( 'Override course certificate.', 'CP_TD' ),
			'default' => false,
		);
		$content .= CoursePress_Helper_UI::course_edit_checkbox( $one, $course_id );

		$content .= sprintf( '<div class="options %s">', cp_is_true( $value )? '':'hidden' );
		$content .= '<label for="meta_basic_certificate_layout">' . __( 'Certificate Content', 'CP_TD' ) . '</label>'
			. '<p class="description" style="float:left;">' . __( 'Useful tokens: ', 'CP_TD' ) . implode( ', ', $field_keys ) . '</p>'
		;
		$content .= CoursePress_Helper_Editor::get_wp_editor( 'basic-certificate-layout', 'meta_basic_certificate_layout', $certficate_content );
		$content .= '<table class="wide"><tr><td style="width:20%;">'
			. '<label>' . __( 'Background Image', 'CP_TD' ) . '</label>'
			. '</td><td>';
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_certificate_background',
			'meta_certificate_background',
			array(
				'placeholder' => __( 'Choose background image', 'CP_TD' ),
				'type' => 'image',
				'value' => CoursePress_Data_Course::get_setting( $course_id, 'certificate_background', '' ),
			)
		);
		$content .= '</td></tr>';
		$cert_margin = CoursePress_Data_Course::get_setting( $course_id, 'cert_margin', array() );
		$margin_top = CoursePress_Helper_Utility::get_array_val( $cert_margin, 'top', '' );
		$margin_bottom = CoursePress_Helper_Utility::get_array_val( $cert_margin, 'bottom', '' );
		$margin_left = CoursePress_Helper_Utility::get_array_val( $cert_margin, 'left', '' );
		$margin_right = CoursePress_Helper_Utility::get_array_val( $cert_margin, 'right', '' );
		$content .= '<tr><td><label>' . __( 'Content margin', 'CP_TD' ) . '</label></td><td>';
		$content .= __( 'Top', 'CP_TD' ) . ': <input type="number" class="small-text" name="meta_cert_margin[top]" value="'. esc_attr( $margin_top ) . '" />';
		$content .= __( 'Left', 'CP_TD' ) . ': <input type="number" class="small-text" name="meta_cert_margin[left]" value="'. esc_attr( $margin_left ) . '" />';
		$content .= __( 'Right', 'CP_TD' ) . ': <input type="number" class="small-text" name="meta_cert_margin[right]" value="'. esc_attr( $margin_right ) . '" />';
		$content .= '</td></tr>';
		$content .= '<tr><td><label>' . __( 'Page Orientation', 'CP_TD' ) . '</label></td><td>';
		$content .= '<label style="float:left;margin-right:25px;"><input type="radio" name="meta_page_orientation" value="L" '. checked( 'L', CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', 'L' ), false ) .' /> ' . __( 'Landscape', 'CP_TD' ) . '</label>';
		$content .= '<label style="float:left;"><input type="radio" name="meta_page_orientation" value="P" '. checked( 'P', CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', '' ), false ) .'/>' . __( 'Portrait', 'CP_TD' ) . '</label>';
		$content .= '</td></tr>';
		$content .= '</table></div>';
		$content .= '</div>';

		// Buttons
		$content .= self::get_buttons( $course_id, 7, array( 'next' => false ) );
		$content .= '</div>';

		echo $content;
	}

	public static function other_steps( $course ) {
		/**
		 * Hook to course edit
		 *
		 * @since 2.0
		 *
		 * @param (object) $course			WP_Post Object.
		 **/
		do_action( 'coursepress_course_edit_steps', $course );
	}

	public static function certificate_preview() {
		if ( ! isset( $_REQUEST['course_id'] ) ) {
			return;
		}
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'cp_certificate_preview' ) ) {
			$course_id = (int) $_REQUEST['course_id'];
			/**
			 * filename
			 */
			$filename = 'cert-preview-' . $course_id . '.pdf';
			/**
			 * options
			 */
			$background = '';
			$orientation = 'P';
			$html = '';
			$margins = array();
			$text_color = array();
			$logo = array();
			/**
			 * vars
			 */
			$date_format = apply_filters( 'coursepress_basic_certificate_date_format', get_option( 'date_format' ) );
			$vars = array(
				'FIRST_NAME' => __( 'Jon', 'CP_TD' ),
				'LAST_NAME' => __( 'Snow', 'CP_TD' ),
				'COURSE_NAME' => __( 'Example Course Title', 'CP_TD' ),
				'COMPLETION_DATE' => date_i18n( $date_format, CoursePress_Data_Course::time_now() ),
				'CERTIFICATE_NUMBER' => uniqid( rand(), true ),
			);

			/**
			 * Use CP defaults?
			 */
			$use_cp_default = CoursePress_Core::get_setting( 'basic_certificate/use_cp_default', false );
			$use_cp_default = cp_is_true( $use_cp_default );

			if ( $course_id > 0 ) {
				$use_course_settings = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate', false );
				$use_course_settings = cp_is_true( $use_course_settings );
				if ( $use_course_settings ) {
					$background = CoursePress_Data_Course::get_setting( $course_id, 'certificate_background', '' );
					$margins = CoursePress_Data_Course::get_setting( $course_id, 'cert_margin', array() );
					$orientation = CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', 'L' );
					$html = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate_layout' );
					$html = apply_filters( 'coursepress_basic_certificate_html', $html, $course_id, get_current_user_id() );
					$use_cp_default = false;
				} else {
					$background = CoursePress_Core::get_setting( 'basic_certificate/background_image' );
					$orientation = CoursePress_Core::get_setting( 'basic_certificate/orientation', 'L' );
					$margins  = CoursePress_Core::get_setting( 'basic_certificate/margin' );
					foreach ( $margins as $margin => $value ) {
						$margins[ $margin ] = $value;
					}
				}
				$userdata = get_userdata( get_current_user_id() );
				$course = get_post( $course_id );
				$vars = array_merge(
					$vars,
					array(
						'FIRST_NAME' => $userdata->first_name,
						'LAST_NAME' => $userdata->last_name,
						'COURSE_NAME' => $course->post_title,
					)
				);
				if ( empty( $vars['FIRST_NAME'] ) && empty( $vars['LAST_NAME'] ) ) {
					$vars['FIRST_NAME'] = $userdata->display_name;
				}
			} else if ( 0 == $course_id ) {
				$background = CoursePress_Core::get_setting( 'basic_certificate/background_image' );
				$orientation = CoursePress_Core::get_setting( 'basic_certificate/orientation', 'L' );
				$margins  = CoursePress_Core::get_setting( 'basic_certificate/margin' );
				foreach ( $margins as $margin => $value ) {
					$margins[ $margin ] = $value;
				}
			}

			if ( $use_cp_default ) {
				/**
				 * Default Background
				 */
				$background = CoursePress::$path.'/asset/img/certificate/certificate-background-p.png';
				/**
				 * default orientation
				 */
				$orientation = 'P';
				/**
				 * CP Logo
				 */
				$logo = array(
					'file' => CoursePress::$path.'/asset/img/certificate/certificate-logo-coursepress.png',
					'x' => 95,
					'y' => 15,
					'w' => 100,
				);
				/**
				 * Default margins
				 */
				$margins = array(
					'left' => 40,
					'right' => 40,
					'top' => 100,
				);
				/**
				 * default color
				 */
				$text_color = array( 90, 90, 90 );
				/**
				 * get default content
				 */
				$html = CoursePress_View_Admin_Setting_BasicCertificate::default_certificate_content();
			}

			/**
			 * get default content
			 */
			if ( empty( $html ) ) {
				$html = CoursePress_Core::get_setting(
					'basic_certificate/content',
					CoursePress_View_Admin_Setting_BasicCertificate::default_certificate_content()
				);
			}
			$html = stripslashes( $html );
			$html = CoursePress_Helper_Utility::replace_vars( $html, $vars );
			// Set PDF args
			$args = array(
				'title' => __( 'Course Completion Certificate', 'CP_TD' ),
				'orientation' => $orientation,
				'image' => $background,
				'filename' => $filename,
				'format' => 'F',
				'uid' => '12345',
				'margins' => apply_filters( 'coursepress_basic_certificate_margins', $margins ),
				'logo' => apply_filters( 'coursepress_basic_certificate_logo', $logo ),
				'text_color' => apply_filters( 'coursepress_basic_certificate_text_color', $text_color ),
			);
			CoursePress_Helper_PDF::make_pdf( $html, $args );
			// Print preview
			$args['format'] = 'I';
			CoursePress_Helper_PDF::make_pdf( $html, $args );
			exit;
		}
	}

	/**
	 * Message in FREE version, when we have more than 0 (zero) courses and we
	 * try to add next one. This is advertising to buy PRO version.
	 *
	 * @since 2.0.0
	 */
	public static function notice_about_pro_when_try_to_add_new_course() {
		echo '<p>';
		_e( 'The free version of CoursePress is limited to one course. To add more courses, upgrade to CoursePress Pro for unlimited courses and more payment gateways.', 'CP_TD' );
		echo '</p>';
		printf(
			'<p><a href="%s" class="button-primary">%s</a></p>',
			esc_url( __( 'https://premium.wpmudev.org/project/coursepress-pro/', 'CP_TD' ) ),
			esc_html__( 'Try CoursePress Pro for Free', 'CP_TD' )
		);
	}

	/**
	 * Remove course add meta boxes.
	 *
	 * @since 2.0.0
	 */
	public static function remove_meta_boxes() {
		$screen = get_current_screen();
		if ( ! is_a( $screen, 'WP_Screen' ) ) {
			return;
		}
		if ( 'add' != $screen->action ) {
			return;
		}
		$post_type = CoursePress_Data_Course::get_post_type_name();
		if ( $post_type != $screen->post_type ) {
			return;
		}
		$page = $screen->id;
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes[ $page ] ) ) {
			unset( $wp_meta_boxes[ $page ] );
		}
	}

	/**
	 * Helper to format time.
	 *
	 * @since 2.0.3
	 *
	 * @param string $duration current duration.
	 * @return Formated duration.
	 */
	static function sanitize_duration_display( $duration ) {
		if ( preg_match( '/^[0:]+$/', $duration ) ) {
			$duration = '';
		}
		if ( empty( $duration ) ) {
			return '&ndash;';
		}
		return $duration;
	}
}
