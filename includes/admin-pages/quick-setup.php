<div class="wrap about-wrap cp-wrap">

	<h1><?php _e( 'Welcome to', '<%= wpmudev.plugin.textdomain %>' ); ?> <?php echo $this->name; ?></h1>

	<div class="about-text">
		<?php
		printf( __( '%s has done a few things to get you on your way.', '<%= wpmudev.plugin.textdomain %>' ), $this->name );
		?>
		<br/>
		<?php
		_e( 'It’s created a couple of dynamic pages labeled ‘Courses’ & ‘Dashboard’ and added them to your navigation.', '<%= wpmudev.plugin.textdomain %>' );
		?>
		<br/>
		<?php
		printf( __( 'If these are not visible on your site and theme, you may need to check your %s.', '<%= wpmudev.plugin.textdomain %>' ), '<a href="' . admin_url( 'nav-menus.php' ) . '">' . __( 'Menu Settings', '<%= wpmudev.plugin.textdomain %>' ) . '</a>' );
		?>
		<br/>
		<?php
		printf( __( '%s has also installed - but not activated - MarketPress Lite.', '<%= wpmudev.plugin.textdomain %>' ), $this->name );
		?>
		<br/>
		<?php _e( 'For those of you who wish to sell your amazing courses you will need to activate and set up a payment gateway.  But more on that later.', '<%= wpmudev.plugin.textdomain %>' ); ?>
	</div>

	<h1><?php _e( 'Let’s Get Started', '<%= wpmudev.plugin.textdomain %>' ); ?></h1>

	<div class="changelog">
		<h3><?php _e( 'Step 1. Create a course', '<%= wpmudev.plugin.textdomain %>' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Add course title and description', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Assign course instructor', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Configure attendance and access settings', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Set up payment gateways for paid courses', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
			</ul>

		</div>
		<br/>
		<img alt="" src="<?php echo trailingslashit( $this->plugin_url ) . 'images/quick-setup-step-1.jpg'; ?>" class="image-66">
	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 2. Add course Content', '<%= wpmudev.plugin.textdomain %>' ); ?></h3>

		<div class="about-text">
			<?php
			_e( 'Courses are structured by Units. Units are made up of elements these can be presented on a single page or over several pages . Elements include', '<%= wpmudev.plugin.textdomain %>' );
			?>
			<ul>
				<li><?php _e( 'Text, Video & Audio', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'File Upload and Download ', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Multiple and Single Choice Questions', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Test Response fields', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
			</ul>

		</div>
		<img alt="" src="<?php echo trailingslashit( $this->plugin_url ) . 'images/quick-setup-step-2.jpg'; ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 3. Enroll students', '<%= wpmudev.plugin.textdomain %>' ); ?></h3>

		<div class="about-text">
			<?php
			_e( 'Configure student enrollment, select to either:', '<%= wpmudev.plugin.textdomain %>' );
			?>
			<ul>
				<li><?php _e( 'Manually add students with or without passcode restriction', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Enroll students  automatically after registration and/or payment', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
			</ul>

		</div>

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 4. Publish your course!', '<%= wpmudev.plugin.textdomain %>' ); ?></h3>

		<div class="about-text">
			<?php
			_e( 'There are many other features in CoursePress, but those are the basics to get you up and running. Now it’s time to publish the course and watch your students learn', '<%= wpmudev.plugin.textdomain %>' );
			?>
			<br/><br/>

		</div>
		<img alt="" src="<?php echo trailingslashit( $this->plugin_url ) . 'images/quick-setup-step-3.jpg'; ?>" class="image-66">

	</div>

	<div class="changelog">
		<h3><?php _e( 'Step 5. Course Management', '<%= wpmudev.plugin.textdomain %>' ); ?></h3>

		<div class="about-text">
			<ul>
				<li><?php _e( 'Administer instructors and students', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Manage Grading of the students submitted work', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
				<li><?php _e( 'Generate Unit/Course/Site-wide Reporting', '<%= wpmudev.plugin.textdomain %>' ); ?></li>
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
				// echo '<div class="error"><p>' . __('<strong>' . $this->name . ' is almost ready</strong>. You must <a href="options-permalink.php">update your permalink structure</a> to something other than the default for it to work.', '<%= wpmudev.plugin.textdomain %>') . '</p></div>';
				?>
				<div class="permalinks-error">
					<h4><?php _e( 'Pretty permalinks are required to use CoursePress.', '<%= wpmudev.plugin.textdomain %>' ); ?></h4>

					<p><?php _e( 'Click the button below to setup your permalinks.', '<%= wpmudev.plugin.textdomain %>' ); ?></p>
					<a href="<?php echo admin_url( 'options-permalink.php' ); ?>" class="button button-units save-unit-button setup-permalinks-button"><?php _e( 'Setup Permalinks', '<%= wpmudev.plugin.textdomain %>' ); ?></a>
				</div>
			<?php
			}
		} else {
			?>
			<a href="<?php echo admin_url( 'admin.php?page=course_details' ); ?>" class="button button-units save-unit-button start-course-button"><?php _e( 'Start building your own course now &rarr;', '<%= wpmudev.plugin.textdomain %>' ); ?></a>
		<?php
		}
		?>


	</div>


</div>