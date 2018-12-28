<?php
/**
 * GDPR
 *
 * @since 2.2.0
 */
class CoursePress_Admin_GDPR {

	private $certificate;
	private $is_certificate_enabled = false;

	public function __construct() {
		global $wp_version;
		$is_less_496 = version_compare( $wp_version, '4.9.6', '<' );
		if ( $is_less_496 ) {
			return;
		}
		/**
		 * CoursePress_Certificate object
		 */
		$this->certificate = new CoursePress_Certificate;
		$this->is_certificate_enabled = $this->certificate->is_enabled();
		/**
		 * Add information to privacy policy page (only during creation).
		 */
		add_filter( 'wp_get_default_privacy_policy_content', array( $this, 'add_policy' ) );
		/**
		 * Adding the Personal Data Exporter
		 */
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_plugin_exporter' ), 10 );
		/**
		 * Adding the Personal Data Eraser
		 */
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_plugin_eraser' ), 10 );
	}

	/**
	 * Get plugin friendly name
	 */
	private function get_plugin_friendly_name() {
		$name = _x( 'CoursePress Plugin', 'Plugin name in personal data exporter.', 'cp' );
		return $name;
	}

	/**
	 * Register plugin exporter.
	 *
	 * @since 2.2.0
	 */
	public function register_plugin_exporter( $exporters ) {
		$exporters['cp'] = array(
			'exporter_friendly_name' => $this->get_plugin_friendly_name(),
			'callback' => array( $this, 'plugin_exporter' ),
		);
		return $exporters;
	}

	/**
	 * get CoursePress User by email
	 *
	 * @since 3.0.0
	 */
	private function get_cp_user_by_email( $email ) {
		$user = get_user_by( 'email', $email );
		if ( ! is_a( $user, 'WP_User' ) ) {
			return new WP_Error( 'error', __( 'User does not exists.', 'cp' ) );
		}
		$cp_user = new CoursePress_User( $user );
		if ( is_wp_error( $cp_user ) ) {
			return new WP_Error( 'error', __( 'User is not a CoursePress user.', 'cp' ) );
		}
		/**
		 * check is student
		 */
		$is_student = $cp_user->is_student();
		if ( ! $is_student ) {
			return new WP_Error( 'error', __( 'User is not a student.', 'cp' ) );
		}
		return $cp_user;
	}

	/**
	 * Export personal data.
	 *
	 * @since 2.2.0
	 */
	public function plugin_exporter( $email, $page = 1 ) {
		/**
		 * get CoursePress User
		 */
		$cp_user = $this->get_cp_user_by_email( $email );
		if ( is_wp_error( $cp_user ) ) {
			return;
		}
		/**
		 * items
		 */
		$export_items = array();
		/**
		 * courses
		 */
		$courses = $cp_user->get_enrolled_courses_ids();
		if ( count( $courses ) ) {
			foreach ( $courses as $course_id ) {
				$item = array(
					'group_id' => 'cp',
					'group_label' => $this->get_plugin_friendly_name(),
					'item_id' => 'coursepress-'.$course_id,
					'data' => array(
						array(
							'name' => __( 'Name', 'cp' ),
							'value' => get_the_title( $course_id ),
						),
						array(
							'name' => __( 'Date Enrollment', 'cp' ),
							'value' => $cp_user->get_date_enrolled( $course_id ),
						),
					),
				);
				if ( $this->is_certificate_enabled ) {
					$is_completed = $cp_user->is_course_completed( $course_id );
					if ( $is_completed ) {
						$item['data'][] = array(
							'name' => __( 'Certificate', 'cp' ),
							'value' => $this->certificate->get_pdf_file_url( $course_id, $cp_user->ID ),
						);
					}
				}
				/**
				 * Export single course row.
				 *
				 * @since 2.2.0
				 *
				 * @param array $item Export data for course.
				 * @param string $email course email.
				 * @param object $course_id Single course ID.
				 */
				$export_items[] = apply_filters( 'coursepress_gdpr_export', $item, $email, $course_id );
			}
		}
		$export = array(
			'data' => $export_items,
			'done' => true,
		);
		return $export;
	}

	/**
	 * Register plugin eraser.
	 *
	 * @since 2.2.0
	 */
	public function register_plugin_eraser( $erasers ) {
		$erasers['cp'] = array(
			'eraser_friendly_name' => $this->get_plugin_friendly_name(),
			'callback'             => array( $this, 'plugin_eraser' ),
		);
		return $erasers;
	}

	/**
	 * Erase personal data.
	 *
	 * @since 2.2.0
	 */
	public function plugin_eraser( $email, $page = 1 ) {
		/**
		 * get CoursePress User
		 */
		$cp_user = $this->get_cp_user_by_email( $email );
		if ( is_wp_error( $cp_user ) ) {
			return array(
				'items_removed' => 0,
				'items_retained' => 0,
				'messages' => array(
					__( 'This email has no courses.', 'cp' ),
				),
				'done' => true,
			);
		}
		/**
		 * courses
		 */
		$courses = $cp_user->get_enrolled_courses_ids();
		foreach ( $courses as $course_id ) {
			$cp_user->remove_course_student( $course_id );
		}
		/**
		 * return
		 */
		return array(
			'items_removed' => count( $courses ),
			'items_retained' => 0,
			'messages' => array(),
			'done' => true,
		);
	}

	/**
	 * Add coursepress Policy to "Privace Policy" page during creation.
	 *
	 * @since 2.2.0
	 */
	public function add_policy( $content ) {
		$content .= '<h2>' . __( 'Plugin: CoursePress', 'cp' ) . '</h2>';
		$content .= $this->get_privacy_message();
		return $content;
	}

	/**
	 * Add privacy policy content for the privacy policy page.
	 *
	 * @since 3.4.0
	 */
	public function get_privacy_message() {
		$content = wp_kses_post( apply_filters( 'coursepress_privacy_policy_content', wpautop( __( '
We collect information about you during the enrollment to our courses or checkout process on our site.

<h3>What we collect and store</h3>

While you visit our site, we’ll track:

<ul>
    <li>Courses you’ve enrolled.</li>
    <li>Courses your answers, comments, files.</li>
</ul>

When you purchase from us, we’ll ask you to provide information including your name, billing address, shipping address, email address, phone number, credit card/payment details and optional account information like username and password. We’ll use this information to:
<ul>
	<li>Send you information about your account and order.</li>
	<li>Respond to your requests, including refunds and complaints.</li>
	<li>Process payments and prevent fraud.</li>
</ul>

If you create an account, we will store your name, address, email and phone number, which will be used to populate the checkout for future orders.

We will store order information for XXX years for tax and accounting purposes. This includes your name, email address and billing and shipping addresses.

We will also store comments or reviews, if you chose to leave them.

<h3>Who on our team has access</h3>

Members of our team have access to the information you provide us. Administrators, Instructors and Facilitators can access:
<ul>
	<li>Customer information like your name, email address and billing.</li>
</ul>

Our team members have access to this information to help fulfill orders, process refunds, and support you.

<h3>What we share with others</h3>

<h4>Payments</h4>
We accept payments through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.
Please see the <a href="https://www.paypal.com/us/webapps/mpp/ua/privacy-full">PayPal Privacy Policy</a> for more details.
', 'cp' ) ) ) );
		return $content;
	}
}

