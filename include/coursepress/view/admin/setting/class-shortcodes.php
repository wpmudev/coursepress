<?php

class CoursePress_View_Admin_Setting_Shortcodes {

	public static function init() {
		add_filter(
			'coursepress_settings_tabs',
			array( __CLASS__, 'add_tabs' )
		);
		add_action(
			'coursepress_settings_process_shortcodes',
			array( __CLASS__, 'process_form' ),
			10, 2
		);
		add_filter(
			'coursepress_settings_render_tab_shortcodes',
			array( __CLASS__, 'return_content' ),
			10, 3
		);
	}

	public static function add_tabs( $tabs ) {
		$tabs['shortcodes'] = array(
			'title' => __( 'Shortcodes', 'CP_TD' ),
			'description' => __( 'Shortcodes allow you to include dynamic content in posts and pages on your site. Simply type or paste them into your post or page content where you would like them to appear. Optional attributes can be added in a format like <em>[shortcode attr1="value" attr2="value"]</em>.', 'CP_TD' ),
			'order' => 50,
			'buttons' => 'none',
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		$content = 'shortcodes!';
		$boxes = self::_boxes();

		ob_start();
		?>
		<div class="shortcodes-list">
			<?php foreach ( $boxes as $group => $data ) : ?>
            <div class="cp-content-box <?php echo esc_attr( $group ); ?>" id="shortcode-<?php echo esc_attr( $group ); ?>">
				<h3 class="hndle">
					<span><?php echo esc_html( $data['title'] ); ?></span>
				</h3>
				<div class="inside"><?php echo $data['content']; ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php

		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public static function process_form() {
	}

	private static function _boxes() {
		$boxes = array(
			'course_instructors' => array(
				'title' => __( 'Instructors List', 'CP_TD' ),
				'content' => self::_box_course_instructors(),
			),
			'course_instructor_avatar' => array(
				'title' => __( 'Instructors Avatar', 'CP_TD' ),
				'content' => self::_box_course_instructor_avatar(),
			),
			'instructor_profile_url' => array(
				'title' => __( 'Instructor Profile URL', 'CP_TD' ),
				'content' => self::_box_instructor_profile_url(),
			),
			'course' => array(
				'title' => __( 'Course', 'CP_TD' ),
				'content' => self::_box_course(),
			),
			'course_details' => array(
				'title' => __( 'Course Details', 'CP_TD' ),
				'content' => self::_box_course_details(),
			),
			'course_title' => array(
				'title' => __( 'Course Title', 'CP_TD' ),
				'content' => self::_box_course_title(),
			),
			'course_summary' => array(
				'title' => __( 'Course Summary', 'CP_TD' ),
				'content' => self::_box_course_summary(),
			),
			'course_description' => array(
				'title' => __( 'Course Description', 'CP_TD' ),
				'content' => self::_box_course_description(),
			),
			'course_start' => array(
				'title' => __( 'Course Start Date', 'CP_TD' ),
				'content' => self::_box_course_start_date(),
			),
			'course_end' => array(
				'title' => __( 'Course End Date', 'CP_TD' ),
				'content' => self::_box_course_end_date(),
			),
			'course_dates' => array(
				'title' => __( 'Course Dates', 'CP_TD' ),
				'content' => self::_box_course_dates(),
			),
			'course_enrollment_start' => array(
				'title' => __( 'Course Enrollment Start', 'CP_TD' ),
				'content' => self::_box_course_enrollment_start(),
			),
			'course_enrollment_end' => array(
				'title' => __( 'Course Enrollment End', 'CP_TD' ),
				'content' => self::_box_course_enrollment_end(),
			),
			'course_enrollment_dates' => array(
				'title' => __( 'Course Enrollment Dates', 'CP_TD' ),
				'content' => self::_box_course_enrollment_dates(),
			),
			'course_enrollment_type' => array(
				'title' => __( 'Coure Enrollment Type', 'CP_TD' ),
				'content' => self::_box_course_enrollment_type(),
			),
			'course_class_size' => array(
				'title' => __( 'Course Class Size', 'CP_TD' ),
				'content' => self::_box_course_class_size(),
			),
			'course_cost' => array(
				'title' => __( 'Course Cost', 'CP_TD' ),
				'content' => self::_box_course_cost(),
			),
			'course_time_estimation' => array(
				'title' => __( 'Course Time Estimation', 'CP_TD' ),
				'content' => self::_box_course_time_estimation(),
			),
			'course_language' => array(
				'title' => __( 'Course Language', 'CP_TD' ),
				'content' => self::_box_course_language(),
			),
			'course_list_image' => array(
				'title' => __( 'Course List Image', 'CP_TD' ),
				'content' => self::_box_course_list_image(),
			),
			'course_featured_video' => array(
				'title' => __( 'Course Featured Video', 'CP_TD' ),
				'content' => self::_box_course_featured_video(),
			),
			'course_media' => array(
				'title' => __( 'Course Media', 'CP_TD' ),
				'content' => self::_box_course_media(),
			),
			'course_join_button' => array(
				'title' => __( 'Course Join Button', 'CP_TD' ),
				'content' => self::_box_course_join_button(),
			),
			'course_action_links' => array(
				'title' => __( 'Course Action Links', 'CP_TD' ),
				'content' => self::_box_course_action_links(),
			),
			'course_calendar' => array(
				'title' => __( 'Course Calendar', 'CP_TD' ),
				'content' => self::_box_course_calendar(),
			),
			'course_list' => array(
				'title' => __( 'Course List', 'CP_TD' ),
				'content' => self::_box_course_list(),
			),
			'course_featured' => array(
				'title' => __( 'Featured Course', 'CP_TD' ),
				'content' => self::_box_course_featured(),
			),
			'course_structure' => array(
				'title' => __( 'Course Structure', 'CP_TD' ),
				'content' => self::_box_course_structure(),
			),
			'course_signup' => array(
				'title' => __( 'Course Signup/Login Page', 'CP_TD' ),
				'content' => self::_box_course_signup(),
			),
			'courses_student_dashboard' => array(
				'title' => __( 'Student Dashboard Template', 'CP_TD' ),
				'content' => self::_box_courses_student_dashboard(),
			),
			'courses_student_settings' => array(
				'title' => __( 'Student Settings Template', 'CP_TD' ),
				'content' => self::_box_courses_student_settings(),
			),
		);
		ksort( $boxes );
		return $boxes;
	}

	/**
	 * Produce help box for course_instructors.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_instructors() {
		$data = array(
			'shortcode' => '[course_instructors]',
			'content' => __( 'Display a list or count of Instructors ( gravatar, name and link to profile page )', 'CP_TD' ),
			'parameters' => array(
				'optional' => array(
					'course_id' => array(
						'content' => __( 'ID of the course instructors are assign to ( required if use it outside of a loop )', 'CP_TD' ),
					),
					'style' => array(
						'content' => __( 'How to display the instructors.', 'CP_TD' ),
						'options' => array( 'block', 'default', 'list', 'list-flat', 'count' ),
						'options_description' => __( 'count - counts instructors for the course.', 'CP_TD' ),
					),
					'label' => array(
						'content' => __( 'Label to display for the output.', 'CP_TD' ),
					),
					'label_plural' => array(
						'content' => __( 'Plural if more than one instructor.', 'CP_TD' ),
						'default' => __( 'Instructors', 'CP_TD' ),
					),
					'label_delimeter' => array(
						'content' => __( 'Symbol to put after label.', 'CP_TD' ),
						'default' => ':',
					),
					'label_tag' => array(
						'content' => __( 'HTML tag to wrap the label (without brackets, e.g. <em>h3</em>).', 'CP_TD' ),
						'default' => __( 'empty', 'CP_TD' ),
					),
					'link_text' => array(
						'content' => __( 'Text to click to link to full profiles.', 'CP_TD' ),
						'default' => __( 'View Full Profile', 'CP_TD' ),
					),
					'show_label' => array(
						'content' => __( 'Show the label.', 'CP_TD' ),
						'options' => array( 'yes', 'no' ),
					),
					'summary_length' => array(
						'content' => __( 'Length of instructor bio to show when style is "block".', 'CP_TD' ),
						'default' => __( 50, 'CP_TD' ),
					),
					'list_separator' => array(
						'content' => __( 'Symbol to use to separate instructors when styl is "list" or "list-flat".', 'CP_TD' ),
						'default' => ',',
					),
					'avatar_size' => array(
						'content' => __( 'Pixel size of the avatars when viewing in block mode.', 'CP_TD' ),
						'default' => __( 80, 'CP_TD' ),
					),
					'default_avatar' => array(
						'content' => __( 'URL to a default image if the user avatar cannot be found.', 'CP_TD' ),
					),
					'show_divider' => array(
						'content' => __( 'Put a divider between instructor profiles when style is "block".', 'CP_TD' ),
					),
					'link_all' => array(
						'content' => __( 'Make the entire instructor profile a link to the full profile.', 'CP_TD' ),
					),
				),
			),
			'add_class_to_optional' => true,
			'examples' => array(
				'[course_instructors]',
				'[course_instructors course_id="5"]',
				'[course_instructors style="list"]',
			),
		);
		$content = self::_prepare_box( $data );
		return $content;
	}

	/**
	 * Produce help box for course_instructor_avatar.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_instructor_avatar() {
		$data = array(
			'shortcode' => '[course_instructor_avatar]',
			'content' => __( 'Display an instructor’s avatar.', 'CP_TD' ),
			'parameters' => array(
				'required' => array(
					'instructor_id' => array(
						'content' => __( 'The user id of the instructor.', 'CP_TD' ),
					),
				),
				'optional' => array(
					'force_display' => array(
						'content' => __( 'Whether to always show the default image, never the Gravatar.', 'CP_TD' ),
					),
					'thumb_size' => array(
						'content' => __( 'Size of avatar thumbnail.', 'CP_TD' ),
						'default' => 80,
					),
					'class' => array(
						'content' => __( 'CSS class to use for the avatar.', 'CP_TD' ),
						'default' => 'small-circle-profile-image',
					),
				),
			),
			'examples' => array(
				'[course_instructor_avatar instructor_id="1"]',
			),
		);
		$content = self::_prepare_box( $data );
		return $content;
	}

	/**
	 * Produce help box for instructor_profile_url.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_instructor_profile_url() {
		ob_start();
?>
<span class="cp-shortcode-code">[instructor_profile_url]</span><br />
<span class=""><?php _e( 'Returns the URL to the instructor profile.', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>instructor_id</span> – <?php _e( 'The user id of the instructor.', 'CP_TD' ); ?>
	</li>
</ul>

<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[instructor_profile_url instructor_id="1"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course() {
		ob_start();
?>
<span class="cp-shortcode-code">[course]</span><br />
<span class=""><?php _e( 'This shortcode allows you to display details about your course. <br /><strong>Note:</strong> All the same information can be retrieved by using the specific course shortcodes following.', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?>
	</li>
	<li>
		<span>show</span>
		– <?php _e( 'All the fields you would like to show.', 'CP_TD' ); ?> <?php _e( 'Default: summary', 'CP_TD' ); ?>
		<p class="description"><strong><?php _e( 'Available fields:', 'CP_TD' ) ?></strong>
			title, summary, description, start, end, dates, enrollment_start,
			enrollment_end, enrollment_dates, enrollment_type, class_size, cost, language,
			instructors, image, video, media, button, action_links, calendar, thumbnail</p>
	</li>
</ul>


<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>show_title</span>
    – <?php _e( 'yes | no - Required when showing the "title" field.', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
	<li><span>date_format</span>
		– <?php _e( 'PHP style date format.', 'CP_TD' ); ?> <?php _e( 'Default: WordPress setting.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span>
        – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' );?> <?php _e( 'Default: strong', 'CP_TD' ); ?>
	</li>
	<li><span>label_delimeter</span>
		– <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
</ul>

<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course show="title,summary,cost,button" course_id="5"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course details.
	 *
	 * @since 2.0.2
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_details() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_details]</span><br />
<span class=""><?php _e( 'This shortcode is an alias to [course] shortcode. see section [course] shortcode for details.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course show="title,summary,cost,button" course_id="5"]</code>
<?php
		$content = ob_get_contents();
			ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_title.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_title() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_title]</span><br />
<span class=""><?php _e( 'Displays the course title.', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>

<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>title_tag</span> – <?php _e( 'The HTML tag (without brackets) to use for the title.', 'CP_TD' ); ?> <?php _e( 'Default: h3', 'CP_TD' ); ?></li>
	<li><span>link</span> – <?php _e( 'Should the title link to the course?  Accepts "yes" or "no".', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>


<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_title course_id="4"]<br />[course_title]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_summary.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_summary() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_summary]</span><br />
<span class=""><?php _e( 'Displays the course summary/excerpt.', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>

<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>length</span> – <?php _e( 'Text length of the summary.', 'CP_TD' ); ?> <?php _e( 'Default: empty (uses WordPress excerpt length)', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>


<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_summary course_id="4"]<br />[course_summary]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_description.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_description() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_description]</span><br />
<span class=""><?php _e( 'Displays the longer course description (post content).', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?>
	</li>
</ul>


<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Additional label will be displayed before description.', 'CP_TD' ); ?></li>
</ul>


<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_description course_id="4"]<br />[course_description]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_description.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_start_date() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_start]</span><br />
<span class=""><?php _e( 'Shows the course start date.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?>
	</li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>date_format</span> – <?php _e( 'PHP style date format.', 'CP_TD' ); ?> <?php _e( 'Default: WordPress setting.', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_start]<br />[course_start label="Awesomeness begins on" label_tag="h3"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_end.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_end_date() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_end]</span><br />
<span class=""><?php _e( 'Shows the course end date.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>date_format</span> – <?php _e( 'PHP style date format.', 'CP_TD' ); ?> <?php _e( 'Default: WordPress setting.', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>no_date_text</span> – <?php _e( 'Text to display if the course has no end date.', 'CP_TD' ); ?> <?php _e( 'Default: No End Date', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_end]<br />[course_end label="The End." label_tag="h3" course_id="5"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_dates.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_dates() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_dates]</span><br />
<span class=""><?php _e( 'Displays the course start and end date range. Typically as [course_start] - [course_end].', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>date_format</span> – <?php _e( 'PHP style date format.', 'CP_TD' ); ?> <?php _e( 'Default: WordPress setting.', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>no_date_text</span> – <?php _e( 'Text to display if the course has no end date.', 'CP_TD' ); ?> <?php _e( 'Default: No End Date', 'CP_TD' ); ?></li>
	<li><span>alt_display_text</span> – <?php _e( 'Alternate display when there is no end date.', 'CP_TD' ); ?> <?php _e( 'Default: Open-ended', 'CP_TD' ); ?></li>
	<li><span>show_alt_display</span> – <?php _e( 'If set to "yes" use the alt_display_text. If set to "no" use the "no_date_text".', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_dates course_id="42"]<br />[course_dates course_id="42" show_alt_display="yes"
	alt_display_text="Learn Anytime!"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_enrollment_start.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_enrollment_start() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_enrollment_start]</span><br />
<span class=""><?php _e( 'Displays the course enrollment start date.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>date_format</span> – <?php _e( 'PHP style date format.', 'CP_TD' ); ?> <?php _e( 'Default: WordPress setting.', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>no_date_text</span> – <?php _e( 'Text to display if the course has no defined enrollment start date.', 'CP_TD' ); ?> <?php _e( 'Default: Enroll Anytime', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_enrollment_start]<br />[course_enrollment_start label="Signup from" label_tag="em"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_enrollment_end.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_enrollment_end() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_enrollment_end]</span><br />
<span class=""><?php _e( 'Shows the course enrollment end date.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>date_format</span> – <?php _e( 'PHP style date format.', 'CP_TD' ); ?> <?php _e( 'Default: WordPress setting.', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>no_date_text</span> – <?php _e( 'Text to display if there is no enrollment end date.', 'CP_TD' ); ?> <?php _e( 'Default: Enroll Anytime', 'CP_TD' ); ?></li>
	<li><span>show_all_dates</span> – <?php _e( 'If "yes" it will display the no_date_text even if there is no date. If "no" then nothing will be displayed.', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_end]<br />[course_end label="End" label_delimeter="-"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_enrollment_dates.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_enrollment_dates() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_enrollment_dates]</span><br />
<span class=""><?php _e( 'Displays the course enrollment start and end date range. Typically as [course_enrollment_start] - [course_enrollment_end].', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>alt_display_text</span> – <?php _e( 'Alternate display when there is no enrollment start or end dates.', 'CP_TD' ); ?> <?php _e( 'Default: Open-ended', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>date_format</span> – <?php _e( 'PHP style date format.', 'CP_TD' ); ?> <?php _e( 'Default: WordPress setting.', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>label_enrolled</span> – <?php _e( 'Label to display for enroled date.', 'CP_TD' ); ?> <?php _e( 'Default: You Enrolled on: ', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>no_date_text</span> – <?php _e( 'Text to display if there is no enrollment start or end dates.', 'CP_TD' ); ?> <?php _e( 'Default: Enroll Anytime', 'CP_TD' ); ?></li>
	<li><span>show_alt_display</span> – <?php _e( 'If set to "yes" use the alt_display_text. If set to "no" use the "no_date_text".', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
	<li><span>show_enrolled_display</span> – <?php _e( 'Display enrollment start label.', 'CP_TD' ); ?> <?php _e( 'Default: yes', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_enrollment_dates]<br />[course_enrollment_dates no_date_text="No better time than now!"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_enrollment_type.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_enrollment_type() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_enrollment_type]</span><br />
<span class=""><?php _e( 'Shows the type of enrollment (manual, prerequisite, passcode or anyone).', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>anyone_text</span> – <?php _e( 'Text to display when anyone can enroll.', 'CP_TD' ); ?> <?php _e( 'Default: Anyone', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>manual_text</span> – <?php _e( 'Text to display for manual enrollments.', 'CP_TD' ); ?> <?php _e( 'Default: Students are added by instructors.', 'CP_TD' ); ?></li>
	<li><span>passcode_text</span> – <?php _e( 'Text to display when a passcode is required.', 'CP_TD' ); ?> <?php _e( 'Default: A passcode is required to enroll.', 'CP_TD' ); ?></li>
	<li><span>prerequisite_text</span> – <?php _e( 'Text to display when there is a prerequisite. Use %s as placeholder for prerequisite course title.', 'CP_TD' ); ?> <?php _e( 'Default: Students need to complete "%s" first.', 'CP_TD' ); ?></li>
	<li><span>registered_text</span> – <?php _e( 'Text to display for registered users.', 'CP_TD' ); ?> <?php _e( 'Default: Registered users.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_enrollment_type]<br />[course_enrollment_type course_id="42"]<br />[course_enrollment_type passcode_text="Whats the magic word?"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_class_size.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_class_size() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_class_size]</span><br />
<span class=""><?php _e( 'Shows the course class size, limits and remaining seats.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>no_limit_text</span> – <?php _e( 'Text to display for unlimited class sizes.', 'CP_TD' ); ?> <?php _e( 'Default: Unlimited', 'CP_TD' ); ?></li>
	<li><span>remaining_text</span> – <?php _e( 'Text to display for remaining places. Use %d for the remaining number.', 'CP_TD' ); ?> <?php _e( 'Default: (%d places left)', 'CP_TD' ); ?></li>
	<li><span>show_no_limit</span> – <?php _e( 'If "yes" it will show the no_limit_text. If "no" then nothing will display for unlimited courses.', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
	<li><span>show_remaining</span> – <?php _e( 'If "yes" show remaining_text. If "no" don’t show remaining places.', 'CP_TD' ); ?> <?php _e( 'Default: "Yes"', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_class_size]<br />[course_class_size course_id="42" no_limit_text="The more the merrier"]<br />[course_class_size remaining_text="Only %d places remaining!"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_cost.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_cost() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_cost]</span><br />
<span class=""><?php _e( 'Shows the pricing for the course or free for unpaid courses.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>no_cost_text</span> – <?php _e( 'Text to display for unpaid courses.', 'CP_TD' ); ?> <?php _e( 'Default: FREE', 'CP_TD' ); ?></li>
	<li><span>show_icon</span> – <?php _e( 'Add extra span with class "product_price" around no_cost_text.', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?>
	</li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_cost]<br />[course_cost no_cost_text="<?php _e( 'Free as in beer.', 'CP_TD' ); ?>"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_time_estimation.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_time_estimation() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_time_estimation]</span><br />
<span class=""><?php _e( 'Shows the total time estimation based on calculation of unit elements.', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>

<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>wrapper</span> – <?php _e( 'Wrap inside a div tag (yes|no).', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_time_estimation course_id="42" wrapper="yes"]<br />[course_time_estimation course_id="42"]<br />[course_time_estimation wrapper="yes"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_language.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_language() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_language]</span><br />
<span class=""><?php _e( 'Displays the language of the course (if set).', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>label</span> – <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code> [course_language]<br />[course_language label="Delivered in"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_list_image.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_list_image() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_list_image]</span><br />
<span class=""><?php _e( 'Displays the course list image. (See [course_media])', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>width</span> – <?php _e( 'Width of image.', 'CP_TD' ); ?> <?php _e( 'Default: Original width', 'CP_TD' ); ?></li>
	<li><span>height</span> – <?php _e( 'Height of image.', 'CP_TD' ); ?> <?php _e( 'Default: Original height', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_list_image]<br />[course_list_image width="100" height="100"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_featured_video.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_featured_video() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_featured_video]</span><br />
<span class=""><?php _e( 'Embeds a video player with the course’s featured video. (See [course_media])', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>width</span> – <?php _e( 'Width of video player.', 'CP_TD' ); ?> <?php _e( 'Default: Default player width', 'CP_TD' ); ?></li>
	<li><span>height</span> – <?php _e( 'Height of video player.', 'CP_TD' ); ?> <?php _e( 'Default: Default player height', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_featured_video]<br />[course_featured_video width="320" height="240"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_media.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_media() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_media]</span><br />
<span class=""><?php _e( 'Displays either the list image or the featured video (with the other option as possible fallback).', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
    <li><span>width</span> – <?php _e( 'Width of media.', 'CP_TD' ); ?> <?php _e( 'Default: CoursePress settings.', 'CP_TD' ); ?></li>
	<li><span>height</span> – <?php _e( 'Height of media.', 'CP_TD' ); ?> <?php _e( 'Default: CoursePress settings.', 'CP_TD' ); ?></li>
	<li><span>list_page</span> – <?php _e( 'Use "yes" to use the CoursePress Settings for "Course Listings". Use "no" to use the CoursePress Settings for "Course Details Page".', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
	<li><span>priority</span> – <?php _e( 'Use "image" to try to show the list image first. If not available, then try to use the featured video.  Use "video" to try to show the featured video first. If not available, try to use the list image.', 'CP_TD' ); ?> <?php _e( 'Default: CoursePress Settings', 'CP_TD' ); ?></li>
	<li><span>type</span> – <?php _e( 'Use "image" to only display list image if available. Use "video" to only show the video if available. Use "thumbnail" to show the course thumbnail (shortcut for type="image" and priority="image"). Use "default" to enable priority mode (see priority attribute).', 'CP_TD' ); ?> <?php _e( 'Default: CoursePress Settings', 'CP_TD' ); ?></li>
	<li><span>wrapper</span> – <?php _e( 'Wrap inside a tag.', 'CP_TD' ); ?> <?php _e( 'Default: empty string, but if height or width is defined, then wrapper is a "div" tag.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_media]<br />[course_media list_page="yes"]<br />[course_media type="video"]<br />[course_media priority="image"]<br />[course_media type="thumbnail"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_join_button.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_join_button() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_join_button]</span><br />
<span class=""><?php _e( 'Shows the Join/Signup/Enroll button for the course. What it displays is dependent on the course settings and the user’s status/enrollment.<br />See the attributes for possible button labels.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>access_text</span> – <?php _e( 'Text to display when the user is enrolled and ready to learn.', 'CP_TD' ); ?> <?php _e( 'Default: Start Learning', 'CP_TD' ); ?></li>
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>continue_learning_text</span> – <?php _e( 'Text to display when the course can be continued.', 'CP_TD' ); ?> <?php _e( 'Default: Continue Learning', 'CP_TD' ); ?></li>
	<li><span>course_expired_text</span> – <?php _e( 'Text to display when the course has expired.', 'CP_TD' ); ?> <?php _e( 'Default: Not available', 'CP_TD' ); ?></li>
	<li><span>course_full_text</span> – <?php _e( 'Text to display if the course is full.', 'CP_TD' ); ?> <?php _e( 'Default: Course Full', 'CP_TD' ); ?></li>
	<li><span>details_text</span> – <?php _e( 'Text for the button that takes you to the full course page.', 'CP_TD' ); ?> <?php _e( 'Default: Course Details', 'CP_TD' ); ?></li>
	<li><span>enrollment_closed_text</span> – <?php _e( 'Text to display when enrollments haven’t started yet.', 'CP_TD' ); ?> <?php _e( 'Default: Enrollments Closed', 'CP_TD' ); ?></li>
	<li><span>enrollment_finished_text</span> – <?php _e( 'Text to display when enrollments are finished (expired).', 'CP_TD' ); ?> <?php _e( 'Default: Enrollments Finished', 'CP_TD' ); ?></li>
	<li><span>enroll_text</span> – <?php _e( 'Text to display when course is ready for enrollments.', 'CP_TD' ); ?> <?php _e( 'Default: Enroll now', 'CP_TD' ); ?></li>
	<li><span>instructor_text</span> – <?php _e( 'Text to display when current user is an instructor of this course.', 'CP_TD' ); ?> <?php _e( 'Default: Access Course', 'CP_TD' ); ?></li>
	<li><span>list_page</span> – <?php _e( 'Show button to course details..', 'CP_TD' ); ?> <?php _e( 'Default: false', 'CP_TD' ); ?></li>
	<li><span>not_started_text</span> – <?php _e( 'Text to display when a student is enrolled, but the course hasn’t started yet.', 'CP_TD' ); ?> <?php _e( 'Default: Not available', 'CP_TD' ); ?></li>
	<li><span>passcode_text</span> – <?php _e( 'Text to display if the course requires a password.', 'CP_TD' ); ?> <?php _e( 'Default: Passcode Required', 'CP_TD' ); ?></li>
	<li><span>prerequisite_text</span> – <?php _e( 'Text to display if the course has a prerequisite.', 'CP_TD' ); ?> <?php _e( 'Default: Pre-requisite Required', 'CP_TD' ); ?></li>
	<li><span>signup_text</span> – <?php _e( 'Text to display when course is ready for enrollments, but the user is not logged in (visitor).', 'CP_TD' ); ?> <?php _e( 'Default: Signup!', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_join_button]<br />[course_join_button course_id="11" course_expired_text="<?php _e( 'You missed out big time!', 'CP_TD' ); ?>"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_action_links.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_action_links() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_action_links]</span><br />
<span class=""><?php _e( 'Shows  "Course Details" and "Withdraw" links to students.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_action_links]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_calendar.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_calendar() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_calendar]</span><br />
<span class=""><?php _e( 'Shows the course calendar (bounds are restricted by course start and end dates). Will always attempt to show today’s date on a calendar first.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>date_indicator</span> – <?php _e( 'Classes string added calendar table tag holder.', 'CP_TD' ); ?> <?php _e( 'Default: indicator_light_block', 'CP_TD' ); ?></li>
	<li><span>month</span> – <?php _e( 'Month to display as number (e.g. 03 for March).', 'CP_TD' ); ?> <?php _e( 'Default: Today’s date', 'CP_TD' ); ?></li>
	<li><span>next</span> – <?php _e( 'Text to display for next month link.', 'CP_TD' ); ?> <?php _e( 'Default: Next »', 'CP_TD' ); ?></li>
	<li><span>pre</span> – <?php _e( 'Text to display for previous month link.', 'CP_TD' ); ?> <?php _e( 'Default: « Previous', 'CP_TD' ); ?></li>
	<li><span>year</span> – <?php _e( 'Year to display as 4-digit number (e.g. 2014).', 'CP_TD' ); ?> <?php _e( 'Default: Today’s date', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_calendar]<br />[course_calendar pre="< Previous" next="Next >"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_list.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_list() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_list]</span><br />
<span class=""><?php _e( 'Displays a listing of courses. Can be for all courses or restricted by instructors or students (only one or the other, if both specified only students will be used).', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>completed_label</span> – <?php _e( 'Label for completed courses list..', 'CP_TD' ); ?> <?php _e( 'Default: Completed courses', 'CP_TD' ); ?></li>
	<li><span>context</span> – <?php _e( 'Context for the courses list. Possible values: enrolled, future, incomplete, completed, past, manage, facilitator, all.', 'CP_TD' ); ?> <?php _e( 'Default: all', 'CP_TD' ); ?></li>
	<li><span>current_label</span> – <?php _e( 'Label for current courses.', 'CP_TD' ); ?> <?php _e( 'Default: Current Courses', 'CP_TD' ); ?></li>
	<li><span>dashboard</span> – <?php _e( 'If is true or "yes" then switch context to "dashboard".', 'CP_TD' ); ?> <?php _e( 'Default: empty string', 'CP_TD' ); ?></li>
	<li><span>facilitator_label</span> – <?php _e( 'Label before courses list for "facilitator" context.', 'CP_TD' ); ?> <?php _e( 'Default: Facilitated Courses', 'CP_TD' ); ?></li>
	<li><span>facilitator</span> – <?php _e( 'If jest true or "yes" switch content to "facilitator".', 'CP_TD' ); ?> <?php _e( 'Default: empty string', 'CP_TD' ); ?></li>
	<li><span>future_label</span> – <?php _e( 'Label for future courses.', 'CP_TD' ); ?> <?php _e( 'Default: Starting soon', 'CP_TD' ); ?></li>
	<li><span>incomplete_label</span> – <?php _e( 'Label for incomplete courses.', 'CP_TD' ); ?> <?php _e( 'Default: Incomplete courses', 'CP_TD' ); ?></li>
	<li><span>instructor_msg</span> – <?php _e( 'Message displayed on intructor page, when instructor do not have any assigned courses.', 'CP_TD' ); ?> <?php _e( 'Default: The Instructor does not have any courses assigned yet.', 'CP_TD' ); ?></li>
	<li><span>instructor</span> – <?php _e( 'The instructor id to list courses for a specific instructor. Can also specify multiple instructors using commas. (e.g. instructor="1,2,3").', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?><br /><span class="description"><?php _e( '<strong>Note:</strong> If both student and instructor are specified, only the student will be used.', 'CP_TD' ); ?></span></li>
	<li><span>limit</span> – <?php _e( 'Limit the number of courses. Use -1 to show all.', 'CP_TD' ); ?> <?php _e( 'Default: -1', 'CP_TD' ); ?></li>
	<li><span>manage_label</span> – <?php _e( 'Label before manageable courses.', 'CP_TD' ); ?> <?php _e( 'Default: Manage Courses', 'CP_TD' ); ?></li>
	<li><span>order</span> – <?php _e( 'Order the courses by title. "ASC" for ascending order. "DESC" for descending order.', 'CP_TD' ); ?> <?php _e( 'Default: "ASC"', 'CP_TD' ); ?></li>
	<li><span>past_label</span> – <?php _e( 'Label before past courses.', 'CP_TD' ); ?> <?php _e( 'Default: Past courses', 'CP_TD' ); ?></li>
	<li><span>show_labels</span> – <?php _e( 'Show labels.', 'CP_TD' ); ?> <?php _e( 'Default: false', 'CP_TD' ); ?></li>
	<li><span>status</span> – <?php _e( 'The status of courses to show (uses WordPress status).', 'CP_TD' ); ?> <?php _e( 'Default: published', 'CP_TD' ); ?></li>
    <li><span>student_msg</span> – <?php _e( 'Messge displayed when student is not enroled to any course.', 'CP_TD' ); ?> <?php echo htmlentities( __( 'Default: You are not enrolled in any courses. <a href="%s">See available courses.</a>', 'CP_TD' ) ); ?></li>
	<li><span>student</span> – <?php _e( 'The student id to list courses for a specific student. Can also specify multiple students using commas. (e.g. student="1,2,3").', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?><br /><span class="description"><?php _e( '<strong>Note:</strong> If both student and instructor are specified, only the student will be used.', 'CP_TD' ); ?></span></li>
	<li><span>suggested_label</span> – <?php _e( 'Label before suggested courses.', 'CP_TD' ); ?> <?php _e( 'Default: Suggested courses', 'CP_TD' ); ?></li>
	<li><span>suggested_msg</span> – <?php _e( 'Message will be show when student is not enrolled to any course, but we have some suggested courses.', 'CP_TD' ); ?> <?php echo htmlentities( __( 'Default: You are not enrolled in any courses.<br />Here are a few you might like, or <a href="%s">see all available courses.</a>', 'CP_TD' ) ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_list]<br />[course_list instructor="2"]<br />[course_list student="3"]<br />[course_list instructor="2,4,5"]<br />[course_list show="dates,cost" limit="5"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_featured.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_featured() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_featured]</span><br />
<span class=""><?php _e( 'Shows a featured course.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>

<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If no id is pecified then it will return empty text.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>featured_title</span> – <?php _e( 'The title to display for the featured course.', 'CP_TD' ); ?> <?php _e( 'Default: Featured Course', 'CP_TD' ); ?></li>
	<li><span>button_title</span> – <?php _e( 'Text to display on the call to action button.', 'CP_TD' ); ?> <?php _e( 'Default: Find out more.', 'CP_TD' ); ?></li>
	<li><span>media_type</span> – <?php _e( 'Media type to use for featured course. See [course_media].', 'CP_TD' ); ?> <?php _e( 'Default: default', 'CP_TD' ); ?></li>
	<li><span>media_priority</span> – <?php _e( 'Media priority to use for featured course. See [course_media].', 'CP_TD' ); ?> <?php _e( 'Default: video', 'CP_TD' ); ?></li>
    <li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_featured course_id="42"]<br />[course_featured course_id="11" featured_title="The best we got!"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_structure.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_structure() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_structure]</span><br />
<span class=""><?php _e( 'Displays a tree view of the course structure.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>class</span> – <?php _e( 'Additional CSS classes for styling.', 'CP_TD' ); ?> <?php _e( 'Default: empty', 'CP_TD' ); ?></li>
	<li><span>deep</span> – <?php _e( 'Show all course modules.', 'CP_TD' ); ?> <?php _e( 'Default: false', 'CP_TD' ); ?></li>
	<li><span>free_class</span> – <?php _e( 'Additional CSS classes for styling free preview items.', 'CP_TD' ); ?> <?php _e( 'Default: free', 'CP_TD' ); ?></li>
	<li><span>free_show</span> – <?php _e( 'Show for FREE preview items.', 'CP_TD' ); ?> <?php _e( 'Default: true', 'CP_TD' ); ?></li>
	<li><span>free_text</span> – <?php _e( 'Text to show for FREE preview items.', 'CP_TD' ); ?> <?php _e( 'Default: Preview', 'CP_TD' ); ?></li>
	<li><span>label_delimeter</span> – <?php _e( 'Symbol to use after the label.', 'CP_TD' ); ?> <?php _e( 'Default is colon (:)', 'CP_TD' ); ?></li>
    <li><span>label</span> – <?php _e( 'Label to display for the output.', 'CP_TD' ); ?> <?php _e( 'Default: Course Structure', 'CP_TD' ); ?></li>
	<li><span>label_tag</span> – <?php _e( 'HTML tag (without brackets) to use for the individual labels.', 'CP_TD' ); ?> <?php _e( 'Default: strong', 'CP_TD' ); ?></li>
	<li><span>show_divider</span> – <?php _e( 'Show divider between major items in the tree, "yes" or "no".', 'CP_TD' ); ?> <?php _e( 'Default: yes', 'CP_TD' ); ?></li>
	<li><span>show_label</span> – <?php _e( 'Show label text as tree heading, "yes" or "no".', 'CP_TD' ); ?> <?php _e( 'Default: no', 'CP_TD' ); ?></li>
    <li><span>show_title</span> – <?php _e( 'Show course title in structure, "yes" or "no".', 'CP_TD' ); ?> <?php _e( 'Default: "no"', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_structure]<br />[course_structure course_id="42" free_text="Gratis!"
	show_title="no"]<br />[course_structure show_title="no" label="Curriculum"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for course_signup.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_course_signup() {
		ob_start();
?>
<span class="cp-shortcode-code">[course_signup]</span><br />
<span class=""><?php _e( 'Shows a custom login or signup page for front-end user registration and login. <strong>Note:</strong> This is already part of CoursePress and can be set in CoursePress Settings. Links to default pages can be found in Appearance > Menus > CoursePress.', 'CP_TD' ); ?></span>
<p class="cp-shortcode-subheading optional"><?php _e( 'Optional Attributes:', 'CP_TD' ); ?></p>
<ul class="cp-shortcode-options">
	<li><span>failed_login_class</span> – <?php _e( 'CSS class to use for invalid login.', 'CP_TD' ); ?> <?php _e( 'Default: red', 'CP_TD' ); ?></li>
	<li><span>failed_login_text</span> – <?php _e( 'Text to display when user doesn’t authenticate.', 'CP_TD' ); ?> <?php _e( 'Default: Invalid login.', 'CP_TD' ); ?></li>
	<li><span>login_tag</span> – <?php _e( 'Title tag wrapper.', 'CP_TD' ); ?> <?php _e( 'Default: h3', 'CP_TD' ); ?></li>
	<li><span>login_title</span> – <?php _e( 'Title to use for Login section.', 'CP_TD' ); ?> <?php _e( 'Default: Login', 'CP_TD' ); ?></li>
	<li><span>login_url</span> – <?php _e( 'URL to redirect to when clicking on "Already have an Account?".', 'CP_TD' ); ?> <?php _e( 'Default: Plugin defaults.', 'CP_TD' ); ?></li>
	<li><span>logout_url</span> – <?php _e( 'URL to redirect to when user logs out.', 'CP_TD' ); ?> <?php _e( 'Default: Plugin defaults.', 'CP_TD' ); ?></li>
	<li><span>page</span> – <?php _e( 'Page parameter if not set CoursePress try to use "page" variable from $_REQUEST.', 'CP_TD' ); ?> <?php _e( 'Default: empty string', 'CP_TD' ); ?></li>
	<li><span>signup_tag</span> – <?php _e( 'Title tag wrapper.', 'CP_TD' ); ?> <?php _e( 'Default: h3', 'CP_TD' ); ?></li>
	<li><span>signup_title</span> – <?php _e( 'Title to use for Signup section.', 'CP_TD' ); ?> <?php _e( 'Default: Signup', 'CP_TD' ); ?></li>
	<li><span>signup_url</span> – <?php _e( 'URL to redirect to when clicking on "Don\'t have an account? Go to Signup!"', 'CP_TD' ); ?> <?php _e( 'Default: empty string', 'CP_TD' ); ?></li>
</ul>
<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[course_signup]<br />[course_signup signup_title="&lt;h1&gt;Signup Now&lt;/h1&gt;"]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for courses_student_dashboard.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_courses_student_dashboard() {
		ob_start();
?>
<span class="cp-shortcode-code">[courses_student_dashboard]</span><br />
<span class=""><?php _e( 'Loads the student dashboard template.', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[courses_student_dashboard]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce help box for courses_student_settings.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function _box_courses_student_settings() {
		ob_start();
?>
<span class="cp-shortcode-code">[courses_student_settings]</span><br />
<span class=""><?php _e( 'Loads the student settings template.', 'CP_TD' ); ?></span>

<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'CP_TD' ); ?></p>
<code>[courses_student_settings]</code>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Produce Box.
	 *
	 * @since 2.0.2
	 * @access private
	 *
	 * @return string
	 */
	private static function _prepare_box( $data ) {
		$content = sprintf( '<span class="cp-shortcode-code">%s</span><br />', $data['shortcode'] );
		$content = sprintf( '<p class="description">%s</p>', $data['content'] );
		if ( isset( $data['parameters'] ) ) {
			$kinds = array(
				'required' => __( 'Required Attributes:', 'CP_TD' ),
				'optional' => __( 'Optional Attributes:', 'CP_TD' ),
			);
			if ( isset( $data['add_class_to_optional'] ) && $data['add_class_to_optional'] ) {
				if ( ! isset( $data['parameters'] ) ) {
					$data['parameters'] = array();
				}
				if ( ! isset( $data['parameters']['optional'] ) ) {
					$data['parameters']['optional'] = array();
				}
				$data['parameters']['optional']['class'] = array( 'content' => __( 'Additional CSS classes to use for further styling.', 'CP_TD' ) );
			}
			foreach ( $kinds as $kind => $kind_label ) {
				if ( isset( $data['parameters'][ $kind ] ) && is_array( $data['parameters'][ $kind ] ) && ! empty( $data['parameters'][ $kind ] ) ) {
					$content .= sprintf( '<div class="cp-shortcode-attributes cp-shortcode-attributes-%s">', esc_attr( $kind ) );
					$content .= sprintf( '<p class="cp-shortcode-subheading">%s</p>', esc_html( $kind_label ) );
					$content .= '<ul class="cp-shortcode-options">';
					$attributes = $data['parameters'][ $kind ];
					ksort( $attributes );
					foreach ( $attributes as $attr_name => $attr_data ) {
						$content .= '<li>';
						$content .= sprintf( '<span>%s</span> –', esc_html( $attr_name ) );
						if ( isset( $attr_data['content'] ) ) {
							$content .= ' ';
							$content .= $attr_data['content'];
						}
						if ( isset( $attr_data['options'] ) ) {
							$content .= ' ';
							$options = '<em>'.implode( '</em>, <em>', $attr_data['options'] ).'</em>';
							$content .= sprintf( __( 'Options: %s.', 'CP_TD' ), $options );
							if ( isset( $attr_data['options_description'] ) && ! empty( $attr_data['options_description'] ) ) {
								$content .= sprintf( '<p class="description">%s</p>', esc_html( $attr_data['options_description'] ) );
							}
						}
						if ( isset( $attr_data['default'] ) && ! empty( $attr_data['default'] ) ) {
							$content .= ' ';
							switch ( $attr_data['default'] ) {
								case ':':
									$content .= __( 'Default is colon (<em>:</em>)', 'CP_TD' );
								break;
								case ',':
									$content .= __( 'Default is coma (<em>,</em>)', 'CP_TD' );
								break;
								default:
									if ( is_numeric( $attr_data['default'] ) ) {
										$content .= sprintf( __( 'Default: <em>%s</em>.', 'CP_TD' ), htmlentities( $attr_data['default'] ) );
									} else {
										$content .= sprintf( __( 'Default: "<em>%s</em>"', 'CP_TD' ), htmlentities( $attr_data['default'] ) );
									}
							}
							$content .= '</li>';
						}
					}
						$content .= '</ul>';
						$content .= '</div>';

				}
			}
			if ( isset( $data['examples'] ) && is_array( $data['examples'] ) && ! empty( $data['examples'] ) ) {
				$content .= '<div cp-shortcode-examples">';
				$content .= sprintf( '<p class="cp-shortcode-subheading">%s</p>', esc_attr__( 'Examples:', 'CP_TD' ) );
				$content .= '<code>';
				$content .= join( $data['examples'], '<br />' );
				$content .= '</code>';
						$content .= '</div>';
			}
			return $content;
		}
	}
}
/*
        $data = array(
            'shortcode' => '',
            'content' => __( '', 'CP_TD' ),
            'parameters' => array(
                'optional' => array(
                    '' => array (
                        'content' => ,
                        'options' => array( ),
                        'options_description' => ,
                    ),
                    '' => array (
                        'content' => __( '.', 'CP_TD' ),
                        'options' => array( 'yes', 'no' ),
                    ),
                ),
            ),
            'add_class_to_optional' => true,
            'examples' => array(
            ),
        );
 */
