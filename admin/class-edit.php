<?php
/**
 * The class responsible for creating or editing CoursePress course.
 *
 * @package CoursePress
 **/
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'CoursePress_Admin_Edit' ) ) :

	class CoursePress_Admin_Edit extends CoursePress_Utility {
		/**
		 * @var (int) $course_id	Current course ID being edited.
		 **/
		static $course_id = 0;

		/**
		 * @var (array) $settings	Current course settings being edited.
		 **/
		static $settings = array();

		/**
		 * @var (int) $setup_marker	The last step current user open.
		 **/
		static $setup_marker = 0;

		public static $slug = 'coursepress_course';
		private static $action = 'new';
		private static $allowed_actions = array(
			'new',
			'edit',
		);
		private static $tabs = array();

		/**
		 * @var (object) $current_course	WP_Post instance.
		 **/
		private static $current_course = false;

		/**
		 * Hold CoursePress_Data_Course instance.
		 **/
		static $data_course;

		/**
		 * CP course post_type.
		 **/
		static $post_type = 'course';

		public static function init_hooks( $post ) {
			self::$data_course = new CoursePress_Data_Course();

			self::$post_type = $post_type = self::$data_course->get_post_type_name();

			if ( $post->post_type != $post_type ) {
				return;
			}

			/**
			 * Trigger before rendering CP page.
			 **/
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

				// Print steps
				add_action( 'edit_form_after_editor', array( __CLASS__, 'course_edit_steps' ) );

			} else {
				$_GET['id'] = $_REQUEST['id'] = self::$current_course->ID;
				add_action( 'admin_footer', array( __CLASS__, 'disable_style' ), 100 );
			}
		}

		public static function allowed_meta_boxes() {
			global $wp_meta_boxes;

			$post_type = self::$post_type;

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
		static function disable_style() {
?>
            <style>
            #postbox-container-1,
            #postbox-container-2 {
            display: none;
            }
            </style>
<?php
		}

		static function preview_post_link( $preview_link, $post ) {
			$preview_link = self::$data_course->get_course_url( $post->ID );
			$preview_link = add_query_arg( 'preview', 'true', $preview_link );

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
				'title' => __( 'Course Setup', 'coursepress' ),
				'description' => __( 'Edit your course specific settings below.', 'coursepress' ),
				'order' => 10,
				'buttons' => 'none',
				'is_form' => false,
			);
			$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

			if ( 'edit' == self::_current_action() ) {
				if ( CoursePress_Data_Capabilities::can_view_course_units( $course_id ) ) {
					$units = self::$data_course->get_unit_ids( $course_id, array( 'publish', 'draft' ) );
					self::$tabs['units'] = array(
						'title' => sprintf( __( 'Units (%s)', 'coursepress' ), count( $units ) ),
						'description' => __( 'Edit your course specific settings below.', 'coursepress' ),
						'order' => 20,
						'buttons' => 'none',
						'is_form' => false,
					);
				}

				if ( CoursePress_Data_Capabilities::can_view_course_students( $course_id ) ) {
					self::$tabs['students'] = array(
						'title' => sprintf(
							__( 'Students (%s)', 'coursepress' ),
							self::$data_course->count_students( $course_id )
						),
						'description' => __( 'Edit your course specific settings below.', 'coursepress' ),
						'order' => 30,
						'buttons' => 'none',
						'is_form' => false,
					);
				}
			}

			// Make sure that we have all the fields we need
			foreach ( self::$tabs as $key => $tab ) {
				$args_to_remove = array( 'message', 'certified' );
				self::$tabs[ $key ]['url'] = add_query_arg( 'tab', $key, remove_query_arg( $args_to_remove ) );
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

			$post_type = self::$post_type;

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
				'label' => __( 'Publish Course', 'coursepress' ),
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
					esc_attr__( 'Previous', 'coursepress' )
				);
			}

			if ( $args['next'] ) {
				$content .= sprintf(
					'<input type="button" class="button step next step-%d" value="%s" />',
					esc_attr( $step ),
					esc_attr__( 'Next', 'coursepress' )
				);
			}

			// Finish button
			if ( 7 == $step ) {
				$content .= sprintf(
					'<input type="button" class="button step finish step-7" value="%s" />',
					esc_attr__( 'Finish', 'coursepress' )
				);
			}
			/**
			 * update button
			 */
			if ( $args['update'] && CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
				$content .= sprintf(
					'<input type="button" class="button step update hidden step-%d" value="%s" />',
					esc_attr( $step ),
					esc_attr__( 'Update', 'coursepress' )
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

			$post_type = self::$post_type;
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
		 * Render the different course steps.
		 **/
		static function course_edit_steps( $course ) {
			// Setup Nonce
			$setup_nonce = wp_create_nonce( 'setup-course' );

			CoursePress_View_Admin_Course_Edit::$current_course = self::$current_course;

			$edit_course_link = get_edit_post_link( self::$current_course->ID );
			printf( '<input type="hidden" id="edit_course_link_url" value="%1$s" /><div class="coursepress-course-step-container"><div id="course-setup-steps" data-nonce="%2$s">', esc_url( $edit_course_link ), $setup_nonce );

			self::step_1();
			self::step_2();
			self::step_3();
			self::step_4();
			self::step_5();
			self::step_6();
			self::step_7();

			/**
			 * Hook to course edit
			 *
			 * @since 2.0
			 *
			 * @param (object) $course			WP_Post Object.
			 **/
			do_action( 'coursepress_course_edit_steps', $course );

			echo '</div></div>';
		}

		/**
		 * Step 1 - Course Overview
		 **/
		static function step_1() {
			self::$course_id = $course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
			self::$settings = $settings = self::$data_course->get_setting( $course_id, true );
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

			$units = self::$data_course->get_units_with_modules( self::$course_id, array( 'publish', 'draft' ) );
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
				'structure_show_empty_units' => self::$settings['structure_show_empty_units'],
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
				$label = esc_html__( 'Invite New Instructor or Facilitator', 'coursepress' );
				$description = esc_html__( 'If the instructor or the facilitator can not be found in the list above, you will need to invite them via email.', 'coursepress' );
				$placeholder = __( 'instructor-or-facilitator@email.com', 'coursepress' );

			} else if ( $can_assign_instructor ) {
				$label = esc_html__( 'Invite New Instructor', 'coursepress' );
				$description = esc_html__( 'If the instructor can not be found in the list above, you will need to invite them via email.', 'coursepress' );
				$placeholder = __( 'facilitator@email.com', 'coursepress' );

			} else if ( $can_assign_facilitator ) {
				$label = esc_html__( 'Invite New Facilitator', 'coursepress' );
				$description = esc_html__( 'If the facilitator can not be found in the list above, you will need to invite them via email.', 'coursepress' );
				$placeholder = __( 'instructor@email.com', 'coursepress' );
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

		/**
		 * Step 4 - Course Dates
		 **/
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

		/**
		 * Step 5 - Classes, Discussion and Workbook
		 **/
		public static function step_5() {
			$setup_class = self::$settings['setup_step_5'];
			$setup_class = 4 == self::$setup_marker ? $setup_class . ' setup_marker' : $setup_class;

			self::render( 'admin/view/steps/step-5', array(
				'course_id' => self::$course_id,
				'setup_class' => $setup_class,
				'class_limited' => self::$settings['class_limited'],
				'class_size' => self::$settings['class_size'],
			) );
		}

		/**
		 * Step 6 - Enrollment and Course Cost
		 **/
		public static function step_6() {
			$setup_class = self::$settings['setup_step_6'];
			$setup_class = 5 == self::$setup_marker ? $setup_class . ' setup_marker' : $setup_class;
			$disable_payment = defined( 'COURSEPRESS_DISABLE_PAYMENT' ) && true == COURSEPRESS_DISABLE_PAYMENT;
			$disable_payment = apply_filters( 'coursepress_disable_course_payments', $disable_payment, self::$course_id );
			$is_paid_course = ! empty( self::$settings['payment_paid_course'] );

			//$data_course = new CoursePress_Data_Course();
			$data_instructor = new CoursePress_Data_Instructor();
			$mp_class = new Coursepress_Helper_Extension_MarketPress();
			$utility_class = new CoursePress_Helper_Utility();

			$install_url = add_query_arg(
				array(
					'post_type' => self::$post_type,
					'page' => 'coursepress_settings',
					'tab' => 'extensions',
				),
				admin_url( 'edit.php' )
			);
			$mp_url = $url = add_query_arg(
				array(
					'post_type' => self::$post_type,
					'page' => 'coursepress_settings',
					'tab' => 'marketpress',
				),
				admin_url( 'edit.php' )
			);

			$install_message = __( 'Please contact your administrator to enable MarketPress for your site.', 'coursepress' );
			$install_message2 = '';
			$installed = $mp_class->installed();

			if ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins ' ) ) {
				$install_message = __( 'To start selling your course, please <a href="%s">install and activate MarketPress</a>.', 'coursepress' );

				if ( $installed && $mp_class->activated() ) {
					$install_message = __( 'To start selling your course, please <a href="%s">complete setup</a> of MarketPress.', 'coursepress' );
					$install_url = $mp_url;
				}

				if ( false === $installed ) {
					$install_message2 = __( 'The full version of MarketPress has been bundled with CoursePress.', 'coursepress' );
				}
			}
			$install_message = sprintf( $install_message, esc_url_raw( $install_url ) );

			/**
			 * Hook this filter to get rid of the payment message
			 */
			$payment_message = sprintf(
				'<div class="payment-message %1$s"><h4>%2$s</h4>%3$s%4$s<p>%5$s: WooCommerce</p></div>',
				esc_attr( $is_paid_course ? '' : 'hidden' ),
				__( 'Sell your courses online with MarketPress.', 'coursepress' ),
				! empty( $install_message2 ) ? sprintf( '<p>%s</p>', $install_message2 ) : '',
				! empty( $install_message ) ? sprintf( '<p>%s</p>', $install_message ) : '',
				__( 'Other supported plugins', 'coursepress' )
			);
			$payment_message = apply_filters( 'coursepress_course_payment_message', $payment_message, self::$course_id );

			// It's already been filtered, but because we're dealing with HTML, lets be sure
			$install_message = $utility_class->filter_content( $payment_message );

			self::render( 'admin/view/steps/step-6', array(
				'course_id' => self::$course_id,
				'setup_class' => $setup_class,
				'disable_payment' => $disable_payment,
				'title2' => false === $disable_payment ? __( '& Course Cost', 'coursepress' ) : '',
				'enrollment_types' => self::$data_course->get_enrollment_types_array( self::$course_id ),
				'enrollment_type' => self::$settings['enrollment_type'],
				'prerequisite_class' => 'prerequisite' === self::$settings['enrollment_type'] ? '' : ' hidden',
				'class_extra' => is_rtl() ? 'chosen-rtl' : '',
				'courses' => $data_instructor->get_accessable_courses( wp_get_current_user(), true ),
				'saved_settings' => self::$data_course->get_prerequisites( self::$course_id ),
				'passcode_class' => 'passcode' === self::$settings['enrollment_type'] ? '' : 'hidden',
				'payment_paid_course' => $is_paid_course,
				'enrollment_passcode' => self::$settings['enrollment_passcode'],
				'payment_supported' => $utility_class->is_payment_supported(),
				'payment_message' => $install_message,
			) );
		}

		/**
		 * Step 7 - Course Completion
		 **/
		public static function step_7() {
			$setup_class = self::$settings['setup_step_7'];
			$setup_class = 6 == self::$setup_marker ? $setup_class . ' setup_marker' : $setup_class;
			$tokens = array(
				'COURSE_NAME',
				'COURSE_SUB_TITLE',
				'COURSE_OVERVIEW',
				'COURSE_UNIT_LIST',
				'DOWNLOAD_CERTIFICATE_LINK',
				'DOWNLOAD_CERTIFICATE_BUTTON',
				'STUDENT_WORKBOOK',
			);

			$pre_completion_content = self::$settings['pre_completion_content'];
			if ( empty( $pre_completion_content ) ) {
				$pre_completion_content = sprintf( '<h3>%s</h3>', __( 'Congratulations! You have completed COURSE_NAME!', 'coursepress' ) );
				$pre_completion_content .= sprintf( '<p>%s</p>', __( 'Your course instructor will now review your work and get back to you with your final grade before issuing you a certificate of completion.', 'coursepress' ) );
			}

			$completion_content = self::$settings['course_completion_content'];
			if ( empty( $completion_content ) ) {
				$completion_content = sprintf( '<h3>%s</h3><p>%s</p><p>DOWNLOAD_CERTIFICATE_BUTTON</p>',
					__( 'Congratulations! You have successfully completed and passed COURSE_NAME!', 'coursepress' ),
					__( 'You can download your certificate here.', 'coursepress' )
				);
			}

			$failed_content = self::$settings['course_failed_content'];
			if ( empty( $failed_content ) ) {
				$failed_content = sprintf( '<p>%s</p><p>%s</p>',
					__( 'Unfortunately, you didn\'t pass COURSE_NAME.', 'coursepress' ),
					__( 'Better luck next time!', 'coursepress' )
				);
			}

			$certificate_tokens = apply_filters( 'coursepress_basic_certificate_vars',
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
			$certificate_tokens = array_keys( $certificate_tokens );
			$certificate_content = self::$settings['basic_certificate_layout'];

			self::render( 'admin/view/steps/step-7', array(
				'setup_class' => $setup_class,
				'course_id' => self::$course_id,
				'minimum_grade_required' => self::$settings['minimum_grade_required'],
				'token_message' => sprintf( __( 'Use these tokens to display actual course details: %s', 'coursepress' ), implode( ', ', $tokens ) ),
				'precompletion' => array(
					'title' => self::$settings['pre_completion_title'],
					'content' => htmlspecialchars_decode( $pre_completion_content ),
				),
				'completion' => array(
					'title' => self::$settings['course_completion_title'],
					'content' => htmlspecialchars_decode( $completion_content ),
				),
				'failed' => array(
					'title' => self::$settings['course_failed_title'],
					'content' => htmlspecialchars_decode( $failed_content ),
				),
				'certificate' => array(
					'content' => htmlspecialchars_decode( $certificate_content ),
					'preview_link' => add_query_arg( array(
						'nonce' => wp_create_nonce( 'cp_certificate_preview' ),
						'course_id' => self::$course_id,
					) ),
					'enabled' => ! empty( self::$settings['basic_certificate'] ),
					'token_message' => sprintf( __( 'Use these tokens to display actual course details: %s', 'coursepress' ), implode( ', ', $certificate_tokens ) ),
					'background' => self::$settings['certificate_background'],
					'logo' => self::$settings['certificate_logo'],
					'logo_position' => self::$settings['logo_position'],
					'margin' => (array) self::$settings['cert_margin'],
					'orientation' => self::$settings['page_orientation'],
					'text_color' => self::$settings['cert_text_color'],
				),
			) );
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
					'FIRST_NAME' => __( 'Jon', 'coursepress' ),
					'LAST_NAME' => __( 'Snow', 'coursepress' ),
					'COURSE_NAME' => __( 'Example Course Title', 'coursepress' ),
					'COMPLETION_DATE' => date_i18n( $date_format, CoursePress_Data_Course::time_now() ),
					'CERTIFICATE_NUMBER' => uniqid( rand(), true ),
				);

				/**
				 * Use CP defaults?
				 */
				$use_cp_default = self::get_setting( 'basic_certificate/use_cp_default', false );
				$use_cp_default = cp_is_true( $use_cp_default );

				if ( $course_id > 0 ) {
					$use_course_settings = self::get_course_setting( $course_id, 'basic_certificate', false );
					$use_course_settings = cp_is_true( $use_course_settings );
					if ( $use_course_settings ) {
						$background = self::get_course_setting( $course_id, 'certificate_background', '' );
						$certificate_logo = self::get_course_setting( $course_id, 'certificate_logo' );
						if ( ! empty( $certificate_logo ) ) {
							$logo_positions = self::get_course_setting( $course_id, 'logo_position', array() );
							$logo  = array(
								'file' => $certificate_logo,
								'x'    => $logo_positions['x'],
								'y'    => $logo_positions['y'],
								'w'    => $logo_positions['width'],
							);
						}
						$margins = self::get_course_setting( $course_id, 'cert_margin', array() );
						$orientation = self::get_course_setting( $course_id, 'page_orientation', 'L' );
						$text_color = CoursePress_Helper_Utility::convert_hex_color_to_rgb( self::get_course_setting( $course_id, 'cert_text_color' ), $text_color );
						$html = self::get_course_setting( $course_id, 'basic_certificate_layout' );
						$html = apply_filters( 'coursepress_basic_certificate_html', $html, $course_id, get_current_user_id() );
						$use_cp_default = false;
					} else {
						$background = self::get_setting( 'basic_certificate/background_image' );
						$certificate_logo = self::get_setting( 'basic_certificate/logo_image' );
						if ( ! empty( $certificate_logo ) ) {
							$x     = self::get_setting( 'basic_certificate/logo/x', 95 );
							$y     = self::get_setting( 'basic_certificate/logo/y', 15 );
							$width = self::get_setting( 'basic_certificate/logo/width', 100 );
							$logo  = array(
								'file' => $certificate_logo,
								'x'    => $x,
								'y'    => $y,
								'w'    => $width,
							);
						}
						$orientation = self::get_setting( 'basic_certificate/orientation', 'L' );
						$margins  = self::get_setting( 'basic_certificate/margin' );
						$text_color = CoursePress_Helper_Utility::convert_hex_color_to_rgb( self::get_setting( 'basic_certificate/text_color' ), $text_color );
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
					$background = self::get_setting( 'basic_certificate/background_image' );
					$certificate_logo = self::get_setting( 'basic_certificate/logo_image' );
					if ( ! empty( $certificate_logo ) ) {
						$x     = self::get_setting( 'basic_certificate/logo/x', 95 );
						$y     = self::get_setting( 'basic_certificate/logo/y', 15 );
						$width = self::get_setting( 'basic_certificate/logo/width', 100 );
						$logo  = array(
							'file' => $certificate_logo,
							'x'    => $x,
							'y'    => $y,
							'w'    => $width,
						);
					}

					$orientation = self::get_setting( 'basic_certificate/orientation', 'L' );
					$margins  = self::get_setting( 'basic_certificate/margin' );
					$text_color = CoursePress_Helper_Utility::convert_hex_color_to_rgb( self::get_setting( 'basic_certificate/text_color' ), $text_color );
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
					$html = self::get_setting(
						'basic_certificate/content',
						CoursePress_View_Admin_Setting_BasicCertificate::default_certificate_content()
					);
				}
				$html = stripslashes( $html );
				$html = CoursePress_Helper_Utility::replace_vars( $html, $vars );
				// Set PDF args
				$args = array(
					'title' => __( 'Course Completion Certificate', 'coursepress' ),
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
			_e( 'The free version of CoursePress is limited to one course. To add more courses, upgrade to CoursePress Pro for unlimited courses and more payment gateways.', 'coursepress' );
			echo '</p>';
			printf(
				'<p><a href="%s" class="button-primary">%s</a></p>',
				esc_url( __( 'https://premium.wpmudev.org/project/coursepress-pro/', 'coursepress' ) ),
				esc_html__( 'Try CoursePress Pro for Free', 'coursepress' )
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

		private static function get_course_setting( $course_id, $key, $default = '' ) {

			$query_param = 'meta_' . $key;
			$query_param_value = isset( $_GET[ $query_param ] ) ? $_GET[ $query_param ] : null;

			if ( $query_param_value !== null ) {
				return CoursePress_Helper_Utility::filter_content( $query_param_value );
			}

			return CoursePress_Data_Course::get_setting( $course_id, $key, $default );
		}

		private static function get_setting( $key, $default = '' ) {

			$query_param_value = CoursePress_Helper_Utility::get_array_val( $_GET, 'coursepress_settings/' . $key );
			if ( $query_param_value !== null ) {
				return CoursePress_Helper_Utility::filter_content( $query_param_value );
			}

			return CoursePress_Core::get_setting( $key, $default );
		}


		/**
		 * Enables TinyMCE for course pages.
		 */
		static function enable_tinymce() {
			global $wp_rich_edit;
			if ( ! $wp_rich_edit ) {
				$screen = get_current_screen();
				if ( in_array( $screen->id, array( 'course' ), true ) ) {
					return true;
				}
			}
			return $wp_rich_edit;
		}
	}
endif;
