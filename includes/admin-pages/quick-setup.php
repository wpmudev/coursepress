<div class="wrap about-wrap">

    <h1><?php _e('Welcome to', 'cp'); ?> <?php echo $this->name; ?></h1>

    <div class="about-text">
        <?php echo $this->name; ?> <?php _e('turns WordPress into a powerful learning management system. Set up online courses, create learning units, invite/enroll students to a course.', 'cp'); ?>
        <br /><br />
        <?php _e('Check these simple steps for the quick setup:', 'cp'); ?>
    </div>

    <div class="changelog">
        <h3>Quick Setup</h3>

        <div class="feature-section images-stagger-right">
            <img alt="" src="<?php echo trailingslashit($this->plugin_url).'images/quick-setup-step-1.png';?>" class="image-66">
            <h4>Add new Instructors</h4>
            <p>You can add new instructor easily by creating <a href='user-new.php'>new user</a> with the Instructor role</p>
            <p>It is recommended that you populate Biographical Info (you can see the field on Edit user screen) of a instructor because that will be visible on the instructor profile page on the front.</p>
            <p>You may publish new courses without assigned instructors so this step is <i>NOT REQUIRED</i></p>
        </div>
    </div>

    <div class="changelog">
        <h3>&nbsp;</h3>

        <div class="feature-section images-stagger-right">
            <img alt="" src="<?php echo trailingslashit($this->plugin_url).'images/quick-setup-step-2.png';?>" class="image-66">
            <h4>Setup New Course</h4>
            <p></p>
            <p>Creating a <a href='admin.php?page=course_details'>new course</a> is an easy process. Just populate title, excerpt and course description for start.</p>
            <p>It is important that you decide when the course will start and when it ends. Also, set enrollment dates because students may enroll only during selected date range.</p>
            <p>After saving new course, you'll get the option to create new Units. At least one unit should be added before allowing students to enroll to the course but it's not required. </p>
        </div>

    </div>

    <div class="changelog">
        <h3>&nbsp;</h3>

        <div class="feature-section images-stagger-right">
            <img alt="" src="<?php echo trailingslashit($this->plugin_url).'images/quick-setup-step-3.png';?>" class="image-66">
            <h4>Add New Students</h4>
            <p></p>
            <p>After saving the course, you'll see a Students tab. From there, you may add new students, invite them via e-mail of simply wait for new students to enroll to the course from the front side of the website. You can also manage students from within the tab, move them through course classes, add or remove new classes and more!</p>
        </div>

    </div>




</div>