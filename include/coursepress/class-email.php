<?php
/**
 * CoursePress Email
 *
 * This class is use as base class for coursepress email setup.
 *
 * @since 2.0
 **/
class CoursePress_Email {
	/**
	 * @var (string)			The email type.
	 **/
	protected $email_type;

	public function __construct() {
		// Set admin related hooks
		add_action( 'admin_init', array( $this, 'admin_hooks' ) );
		// Set default settings
		add_filter( 'coursepress_default_email_settings', array( $this, 'default_email_settings' ) );
	}

	/**
	 * Sets admin hooks
	 **/
	public function admin_hooks() {
		// Set email settings block
		add_filter( 'coursepress_email_settings_sections', array( $this, 'email_settings_view' ) );
	}

	public function default_email_settings( $all_settings ) {
		$email_fields = wp_parse_args(
			$this->default_email_fields(),
			array(
				'enabled' => 1,
				'from' => get_option( 'blogname' ),
				'email' => get_option( 'admin_email' ),
				'subject' => __( 'Subject line here...', 'coursepress' ),
				'content' => 'The content goes here...',
		) );

		$all_settings[ $this->email_type ] = $email_fields;

		return $all_settings;
	}

	/**
	 * Set the default email fields.
	 * 
	 * Must be overriden in a sub-class
	 **/
	public function default_email_fields() { return array(); }

	/**
	 * Default mail tokens.
	 *
	 * Must be overriden in a sub-class.
	 **/
	public function mail_tokens() {
		return array(
			'BLOG_NAME', 'WEBSITE_ADDRESS',
		);
	}

	/**
	 * Use to set email settings and sending actual email.
	 **/
	public function email_fields() {
		$email_fields = wp_parse_args(
			$this->default_email_fields(),
			array(
				'enabled' => 1,
				'from' => get_option( 'blogname' ),
				'email' => get_option( 'admin_email' ),
				'subject' => __( 'Subject line here...', 'coursepress' ),
				'content' => 'The content goes here...',
		) );

		// Get the latest from DB
		$email_fields = CoursePress_Core::get_setting( 'email/' . $this->email_type, $email_fields );

		/**
		 * Allow third party to filter the email fields
		 *
		 * @param (array) $email_fields			An array of email fields
		 **/
		$email_fields = apply_filters( 'coursepress_get_email_fields-' . $this->email_type, $email_fields );

		return $email_fields;
	}

	/**
	 * Template view for email settings
	 *
	 * @since 2.0
	 **/
	public function email_settings_view() {
		$email_settings = wp_parse_args( $this->email_settings(), $this->email_fields() );

		$content = sprintf( '<h3 class="hndle">%s</h3>', $email_settings['title'] );
		$content .= '<div class="inside">';
		$content .= sprintf( '<p class="description">%s</p>', $email_settings['description'] );

		$block = '<tr><th>%s</th><td>%s<input type="text" class="widefat" name="coursepress_settings[email][%s][%s]" value="%s" /></td></tr>';

		// Enabled
		$fields = '
			<tr>
				<th>' . esc_html__( 'Enabled', 'coursepress' ) . '</th>
				<td>
					<input type="hidden" name="coursepress_settings[email][' . $this->email_type . '][enabled]" value="0" />
					<input type="checkbox" class="widefat" name="coursepress_settings[email][' . $this->email_type . '][enabled]" value="1" '
				. checked( $email_settings['enabled'], true, false) . ' />
				</td>
			</tr>
		';

		// From Name
		$fields .= sprintf(
			$block,
			__( 'From Name', 'coursepress' ),
			isset( $email_settings['from_sub'] ) ? $email_settings['from_sub'] : '', // Allow description
			$this->email_type,
			'from',
			esc_attr( $email_settings['from'] )
		);

		// From Email
		$fields .= sprintf(
			$block,
			__( 'From Email', 'coursepress' ),
			isset( $email_settings['email_sub'] ) ? $email_settings['email_sub'] : '', // Allow description
			$this->email_type,
			'email',
			esc_attr( $email_settings['email'] )
		);

		// Subject
		$fields .= sprintf(
			$block,
			__( 'Subject', 'coursepress' ),
			isset( $email_settings['subject_sub'] ) ? $email_settings['email_sub'] : '',
			$this->email_type,
			'subject',
			esc_attr( $email_settings['subject'] )
		);

		// Content
		$content_help_text = '';
		$mail_tokens = $this->mail_tokens();

		if ( ! empty( $mail_tokens ) ) {
			$content_help_text .= sprintf( '<p class="description"><strong>%s</strong>: <br />%s', __( 'Mail Tokens', 'coursepress' ), implode( ', ', $mail_tokens ) );
			$content_help_text .= '<p class="description">* ' . __( 'These tokens will be replaced with actual data.', 'coursepress' ) . '</p>'; 
		}

		$fields .= '<tr><th>' . __( 'Email Body', 'coursepress' ) . '</th><td>' . $content_help_text;

		ob_start();
		$editor_settings = array(
			'textarea_name' => 'coursepress_settings[email]['. $this->email_type . '][content]',
			'media_buttons' => false,
			'teeny' => true,
			'tinymce' => array( 'height' => 400 ),
		);
		$editor_id = 'cp-wp-email-editor-' . $this->email_type;
		$editor_content = stripcslashes( $email_settings['content'] );
		wp_editor( $editor_content, $editor_id, $editor_settings );

		$editor = ob_get_clean();

		$fields .= $editor . '<br /></td></tr>';

		$content .= sprintf( '<table id="email-setting-fields" class="form-table compressed email-fields"><tbody>%s</tbody></table>', $fields );

		$content .= '</div>';

		return '<div class="email-template cp-content-box collapsed cp-email-setting-fields email-setting-'. $this->email_type . '">' . $content . '</div>';
	}

	/**
	 * Replaced course related tokens
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id			The course.
	 * @return (array) 					An array of `key=value` where value is the actual course data.
	 **/
	public function prepare_course_tokens( $course_id = 0 ) {
		$vars = array( 'COURSE_NAME' => '', 'COURSE_ADDRESS' => '' );

		if ( ! empty( $course_id ) ) {
			$course = get_post( $course_id );
			$course_address = CoursePress_Core::get_slug( 'courses/', true ) . $course->post_name;

			$vars['COURSE_NAME'] = $course->post_title;
			$vars['COURSE_ADDRESS'] = $course_address;
		}

		return $vars;
	}

	/**
	 * Replaced user tokens
	 *
	 * @since 2.0
	 *
	 * @param (int) $user_id				The user.
	 * @return (array) 						An array of `key=value` where value is the actual user data.
	 **/
	public function prepare_user_tokens( $user_id ) {
		$vars = array(
			'FIRST_NAME' => '',
			'LAST_NAME' => '',
			'EMAIL' => '',
			'STUDENT_FIRST_NAME' => '',
			'STUDENT_LAST_NAME' => '',
			'INSTRUCTOR_FIRST_NAME' => '',
			'INSTRUCTOR_LAST_NAME' => '',
		);

		if ( ! empty( $user_id ) ) {
			$user = get_userdata( $user_id );

			$vars['FIRST_NAME'] = $vars['STUDENT_FIRST_NAME'] = empty( $user->first_name ) && empty( $user->last_name ) ? $user->display_name : $user->first_name;
			$vars['LAST_NAME'] = $vars['STUDENT_LAST_NAME'] = $user->last_name;
			$vars['EMAIL'] = $user->user_email;
		}

		return $vars;
	}

	/**
	 * Default mail tokens.
	 *
	 * @since 2.0
	 *
	 * @return (array) 				An array of `key=value` where value is the actual data.
	 **/
	public function prepare_tokens() {
		$vars = array_fill_keys( array_values( $this->mail_tokens() ), '' );
		$vars = CoursePress_Helper_Utility::add_site_vars( $vars );
		return $vars;
	}

	/**
	 * Use to send email.
	 *
	 * Note: This method is using CoursePress_Helper_Email class
	 *
	 * @param (array) $email_args				The email arguments.
	 * @param (array) $tokens					An array of set mail tokens with their actual value.
	 **/
	public function send_email( $email_args = array(), $tokens = array() ){
		$email_fields = $this->email_fields();
		$message = $email_fields['content'];

		/**
		 * Filter variables before applying the changes to the content.
		 **/
		$vars = apply_filters( 'coursepress_fields_' . $this->email_type, $tokens );

		$message = CoursePress_Helper_Utility::replace_vars( $message, $vars );

		// Use CoursePress_Helper_Email class
		$email_args = wp_parse_args( $email_args, $email_fields );
		$email_args['message'] = $message;

		CoursePress_Helper_Email::send_email( $this->email_type, $email_args );
	}
}
