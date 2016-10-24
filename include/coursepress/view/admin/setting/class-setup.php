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
			'title' => __( 'Setup Guide', 'cp' ),
			'description' => __( 'This is the description of what you can do on this page.', 'cp' ),
			'order' => 70,
			'class' => 'setup_tab',
		);

		return $tabs;
	}

	public static function return_content( $content ) {
		ob_start();
?>
<div class="wrap about-wrap cp-wrap">
	<h1><?php _e( 'Welcome to', 'cp' ); ?> <?php echo CoursePress::$name; ?></h1>

	<div class="about-text">
<?php
		printf( __( '%s has done a few things to get you on your way.', 'cp' ), CoursePress::$name );
?>
		<br/>
<?php
		_e( 'It’s created a couple of dynamic pages labeled ‘Courses’ & ‘Dashboard’ and added them to your navigation.', 'cp' );
?>
		<br/>
<?php
		printf( __( 'If these are not visible on your site and theme, you may need to check your %s.', 'cp' ), '<a href="' . admin_url( 'nav-menus.php' ) . '">' . __( 'Menu Settings', 'cp' ) . '</a>' );
?>
		<br/>
<?php
		printf( __( '%s has also installed - but not activated - MarketPress Lite.', 'cp' ), CoursePress::$name );
?>
		<br/>
		<?php _e( 'For those of you who wish to sell your amazing courses you will need to activate and set up a payment gateway.  But more on that later.', 'cp' ); ?>
	</div>

	<h1><?php _e( 'Let’s Get Started', 'cp' ); ?></h1>

	<div class="changelog">
		<h3><?php _e( 'Step 1. Create a course', 'cp' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Add course title and description', 'cp' ); ?></li>
				<li><?php _e( 'Assign course instructor', 'cp' ); ?></li>
				<li><?php _e( 'Configure attendance and access settings', 'cp' ); ?></li>
				<li><?php _e( 'Set up payment gateways for paid courses', 'cp' ); ?></li>
			</ul>

		</div>
		<br/>
		<img alt="" src="<?php echo esc_attr_e( CoursePress::$url . 'asset/img/quick-setup/step-1.jpg' ); ?>" class="image-66">
	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 2. Add course Content', 'cp' ); ?></h3>

		<div class="about-text">
<?php
		_e( 'Courses are structured by Units. Units are made up of elements these can be presented on a single page or over several pages . Elements include', 'cp' );
?>
			<ul>
				<li><?php _e( 'Text, Video & Audio', 'cp' ); ?></li>
				<li><?php _e( 'File Upload and Download ', 'cp' ); ?></li>
				<li><?php _e( 'Multiple and Single Choice Questions', 'cp' ); ?></li>
				<li><?php _e( 'Test Response fields', 'cp' ); ?></li>
			</ul>

		</div>
		<img alt="" src="<?php echo esc_attr_e( CoursePress::$url . 'asset/img/quick-setup/step-2.jpg' ); ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 3. Enroll students', 'cp' ); ?></h3>

		<div class="about-text">
<?php
		_e( 'Configure student enrollment, select to either:', 'cp' );
?>
			<ul>
				<li><?php _e( 'Manually add students with or without passcode restriction', 'cp' ); ?></li>
				<li><?php _e( 'Enroll students  automatically after registration and/or payment', 'cp' ); ?></li>
			</ul>

		</div>

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 4. Publish your course!', 'cp' ); ?></h3>

		<div class="about-text">
<?php
		_e( 'There are many other features in CoursePress, but those are the basics to get you up and running. Now it’s time to publish the course and watch your students learn', 'cp' );
?>
			<br/><br/>

		</div>
		<img alt="" src="<?php esc_attr_e( CoursePress::$url . 'asset/img/quick-setup/step-3.jpg' ); ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 5. Course Management', 'cp' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Administer instructors and students', 'cp' ); ?></li>
				<li><?php _e( 'Manage Grading of the students submitted work', 'cp' ); ?></li>
				<li><?php _e( 'Generate Unit/Course/Site-wide Reporting', 'cp' ); ?></li>
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
			<h4><?php _e( 'Pretty permalinks are required to use CoursePress.', 'cp' ); ?></h4>

			<p><?php _e( 'Click the button below to setup your permalinks.', 'cp' ); ?></p>
			<a href="<?php echo admin_url( 'options-permalink.php' ); ?>" class="button button-units save-unit-button setup-permalinks-button"><?php _e( 'Setup Permalinks', 'cp' ); ?></a>
		</div>
<?php
	}
} else {
	$url = admin_url('post-new.php?post_type=' . CoursePress_Data_Course::get_post_type_name());
?>
	<a href="<?php echo esc_url( $url ); ?>" class="button button-units save-unit-button start-course-button"><?php _e( 'Start building your own course now &rarr;', 'cp' ); ?></a>
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
