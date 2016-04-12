<?php

class CoursePress_View_Admin_Setting_Setup {

	public static function init() {
		add_filter(
			'coursepress_settings_tabs',
			array( __CLASS__, 'add_tabs' )
		);
		add_action(
			'coursepress_settings_process_setup',
			array( __CLASS__, 'process_form' ), 10, 2
		);
		add_filter(
			'coursepress_settings_render_tab_setup',
			array( __CLASS__, 'return_content' ),
			10, 3
		);

		if ( isset( $_GET['tab'] ) && 'setup' == $_GET['tab'] ) {
			add_filter(
				'coursepress_settings_tabs_content',
				array( __CLASS__, 'remove_tabs' ),
				10, 2
			);
			add_filter(
				'coursepress_settings_page_main',
				array( __CLASS__, 'return_content' )
			);

			// TODO: This is premium only. move to premium folder!
			add_action(
				'coursepress_settings_page_pre_render',
				array( __CLASS__, 'remove_dashboard_notification' )
			);
		}
	}

	public static function add_tabs( $tabs ) {
		$tabs['setup'] = array(
			'title' => __( 'Setup Guide', 'CP_TD' ),
			'description' => __( 'This is the description of what you can do on this page.', 'CP_TD' ),
			'order' => 70,
			'class' => 'setup_tab',
		);

		return $tabs;
	}

	public static function return_content( $content ) {
		ob_start();
?>
<div class="wrap about-wrap cp-wrap">
	<h1><?php _e( 'Welcome to', 'CP_TD' ); ?> <?php echo CoursePress::$name; ?></h1>

	<div class="about-text">
<?php
		printf( __( '%s has done a few things to get you on your way.', 'CP_TD' ), CoursePress::$name );
?>
		<br/>
<?php
		_e( 'It’s created a couple of dynamic pages labeled ‘Courses’ & ‘Dashboard’ and added them to your navigation.', 'CP_TD' );
?>
		<br/>
<?php
		printf( __( 'If these are not visible on your site and theme, you may need to check your %s.', 'CP_TD' ), '<a href="' . admin_url( 'nav-menus.php' ) . '">' . __( 'Menu Settings', 'CP_TD' ) . '</a>' );
?>
		<br/>
<?php
		printf( __( '%s has also installed - but not activated - MarketPress Lite.', 'CP_TD' ), CoursePress::$name );
?>
		<br/>
		<?php _e( 'For those of you who wish to sell your amazing courses you will need to activate and set up a payment gateway. But more on that later.', 'CP_TD' ); ?>
	</div>

	<h1><?php _e( 'Let’s Get Started', 'CP_TD' ); ?></h1>

	<div class="changelog">
		<h3><?php _e( 'Step 1. Create a course', 'CP_TD' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Add course title and description', 'CP_TD' ); ?></li>
				<li><?php _e( 'Assign course instructor', 'CP_TD' ); ?></li>
				<li><?php _e( 'Configure attendance and access settings', 'CP_TD' ); ?></li>
				<li><?php _e( 'Set up payment gateways for paid courses', 'CP_TD' ); ?></li>
			</ul>

		</div>
		<br/>
		<img alt="" src="<?php echo esc_attr_e( CoursePress::$url . 'asset/img/quick-setup/step-1.jpg' ); ?>" class="image-66">
	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 2. Add course Content', 'CP_TD' ); ?></h3>

		<div class="about-text">
<?php
		_e( 'Courses are structured by Units. Units are made up of elements these can be presented on a single page or over several pages . Elements include', 'CP_TD' );
?>
			<ul>
				<li><?php _e( 'Text, Video & Audio', 'CP_TD' ); ?></li>
				<li><?php _e( 'File Upload and Download ', 'CP_TD' ); ?></li>
				<li><?php _e( 'Multiple and Single Choice Questions', 'CP_TD' ); ?></li>
				<li><?php _e( 'Test Response fields', 'CP_TD' ); ?></li>
			</ul>

		</div>
		<img alt="" src="<?php echo esc_attr_e( CoursePress::$url . 'asset/img/quick-setup/step-2.jpg' ); ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 3. Enrol students', 'CP_TD' ); ?></h3>

		<div class="about-text">
<?php
		_e( 'Configure student enrolment, select to either:', 'CP_TD' );
?>
			<ul>
				<li><?php _e( 'Manually add students with or without passcode restriction', 'CP_TD' ); ?></li>
				<li><?php _e( 'Enrol students automatically after registration and/or payment', 'CP_TD' ); ?></li>
			</ul>

		</div>

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 4. Publish your course!', 'CP_TD' ); ?></h3>

		<div class="about-text">
<?php
		_e( 'There are many other features in CoursePress, but those are the basics to get you up and running. Now it’s time to publish the course and watch your students learn', 'CP_TD' );
?>
			<br/><br/>

		</div>
		<img alt="" src="<?php esc_attr_e( CoursePress::$url . 'asset/img/quick-setup/step-3.jpg' ); ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 5. Course Management', 'CP_TD' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Administer instructors and students', 'CP_TD' ); ?></li>
				<li><?php _e( 'Manage Grading of the students submitted work', 'CP_TD' ); ?></li>
				<li><?php _e( 'Generate Unit/Course/Site-wide Reporting', 'CP_TD' ); ?></li>
			</ul>
		</div>

<?php
if ( current_user_can( 'manage_options' ) && ! get_option( 'permalink_structure' ) ) {
	// toplevel_page_courses
	$screen = get_current_screen();

	$show_warning = false;

	if ( 'toplevel_page_courses' == $screen->id && isset( $_GET['quick_setup'] ) ) {
		$show_warning = true;
	}

	if ( $show_warning ) {
?>
		<div class="permalinks-error">
			<h4><?php _e( 'Pretty permalinks are required to use CoursePress.', 'CP_TD' ); ?></h4>

			<p><?php _e( 'Click the button below to setup your permalinks.', 'CP_TD' ); ?></p>
			<a href="<?php echo admin_url( 'options-permalink.php' ); ?>" class="button button-units save-unit-button setup-permalinks-button"><?php _e( 'Setup Permalinks', 'CP_TD' ); ?></a>
		</div>
<?php
	}
} else {
	$url = add_query_arg( 'page', CoursePress_View_Admin_Course_Edit::$slug, admin_url( 'admin.php' ) );
?>
	<a href="<?php echo esc_url( $url ); ?>" class="button button-units save-unit-button start-course-button"><?php _e( 'Start building your own course now &rarr;', 'CP_TD' ); ?></a>
<?php
}
?>
	</div>
</div>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public static function remove_tabs( $wrapper, $content ) {
		$wrapper = $content;
		return $wrapper;
	}

	public static function remove_dashboard_notification() {
		if ( isset( $_GET['tab'] ) && 'setup' === $_GET['tab'] ) {
			global $wpmudev_notices;
			$wpmudev_notices = array();
		}
	}


	public static function process_form() {
	}
}
