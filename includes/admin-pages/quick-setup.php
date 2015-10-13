<div class="wrap about-wrap cp-wrap">

	<h1><?php _e( 'Welcome to', 'coursepress' ); ?> <?php echo $this->name; ?></h1>

	<div class="about-text">
		<?php
		printf( __( '%s has done a few things to get you on your way.', 'coursepress' ), $this->name );
		?>
		<br/>
		<?php
		_e( 'It’s created a couple of dynamic pages labeled ‘Courses’ & ‘Dashboard’ and added them to your navigation.', 'coursepress' );
		?>
		<br/>
		<?php
		printf( __( 'If these are not visible on your site and theme, you may need to check your %s.', 'coursepress' ), '<a href="' . admin_url( 'nav-menus.php' ) . '">' . __( 'Menu Settings', 'coursepress' ) . '</a>' );
		?>
		<br/>
		<?php
		printf( __( '%s has also installed - but not activated - MarketPress Lite.', 'coursepress' ), $this->name );
		?>
		<br/>
		<?php _e( 'For those of you who wish to sell your amazing courses you will need to activate and set up a payment gateway.  But more on that later.', 'coursepress' ); ?>
	</div>

	<h1><?php _e( 'Let’s Get Started', 'coursepress' ); ?></h1>

	<div class="changelog">
		<h3><?php _e( 'Step 1. Create a course', 'coursepress' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Add course title and description', 'coursepress' ); ?></li>
				<li><?php _e( 'Assign course instructor', 'coursepress' ); ?></li>
				<li><?php _e( 'Configure attendance and access settings', 'coursepress' ); ?></li>
				<li><?php _e( 'Set up payment gateways for paid courses', 'coursepress' ); ?></li>
			</ul>

		</div>
		<br/>
		<img alt="" src="<?php echo trailingslashit( $this->plugin_url ) . 'images/quick-setup-step-1.jpg'; ?>" class="image-66">
	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 2. Add course Content', 'coursepress' ); ?></h3>

		<div class="about-text">
			<?php
			_e( 'Courses are structured by Units. Units are made up of elements these can be presented on a single page or over several pages . Elements include', 'coursepress' );
			?>
			<ul>
				<li><?php _e( 'Text, Video & Audio', 'coursepress' ); ?></li>
				<li><?php _e( 'File Upload and Download ', 'coursepress' ); ?></li>
				<li><?php _e( 'Multiple and Single Choice Questions', 'coursepress' ); ?></li>
				<li><?php _e( 'Test Response fields', 'coursepress' ); ?></li>
			</ul>

		</div>
		<img alt="" src="<?php echo trailingslashit( $this->plugin_url ) . 'images/quick-setup-step-2.jpg'; ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 3. Enroll students', 'coursepress' ); ?></h3>

		<div class="about-text">
			<?php
			_e( 'Configure student enrollment, select to either:', 'coursepress' );
			?>
			<ul>
				<li><?php _e( 'Manually add students with or without passcode restriction', 'coursepress' ); ?></li>
				<li><?php _e( 'Enroll students  automatically after registration and/or payment', 'coursepress' ); ?></li>
			</ul>

		</div>

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 4. Publish your course!', 'coursepress' ); ?></h3>

		<div class="about-text">
			<?php
			_e( 'There are many other features in CoursePress, but those are the basics to get you up and running. Now it’s time to publish the course and watch your students learn', 'coursepress' );
			?>
			<br/><br/>

		</div>
		<img alt="" src="<?php echo trailingslashit( $this->plugin_url ) . 'images/quick-setup-step-3.jpg'; ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 5. Course Management', 'coursepress' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Administer instructors and students', 'coursepress' ); ?></li>
				<li><?php _e( 'Manage Grading of the students submitted work', 'coursepress' ); ?></li>
				<li><?php _e( 'Generate Unit/Course/Site-wide Reporting', 'coursepress' ); ?></li>
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
				// echo '<div class="error"><p>' . __('<strong>' . $this->name . ' is almost ready</strong>. You must <a href="options-permalink.php">update your permalink structure</a> to something other than the default for it to work.', 'coursepress') . '</p></div>';
				?>
				<div class="permalinks-error">
					<h4><?php _e( 'Pretty permalinks are required to use CoursePress.', 'coursepress' ); ?></h4>

					<p><?php _e( 'Click the button below to setup your permalinks.', 'coursepress' ); ?></p>
					<a href="<?php echo admin_url( 'options-permalink.php' ); ?>" class="button button-units save-unit-button setup-permalinks-button"><?php _e( 'Setup Permalinks', 'coursepress' ); ?></a>
				</div>
			<?php
			}
		} else {
			?>
			<a href="<?php echo admin_url( 'admin.php?page=course_details' ); ?>" class="button button-units save-unit-button start-course-button"><?php _e( 'Start building your own course now &rarr;', 'coursepress' ); ?></a>
		<?php
		}
		?>


	</div>


</div>