<div class="wrap about-wrap">

    <h1><?php _e('Welcome to', 'cp'); ?> <?php echo $this->name; ?></h1>

    <div class="about-text">
        <?php
        printf(__('In four steps, %s turns WordPress into a powerful learning management system. Set up 
online courses, create learning units, invite/enrol students to a course, publish the course and you 
are away!', 'cp'), $this->name);
        ?>
        <br /><br />
    </div>

    <div class="changelog">
        <h3><?php _e('Step 1. Create a new course', 'cp'); ?></h3>

        <div class="about-text">
            <?php _e('Creating a new course is an easy process. Give it a title, and course description to begin with. Then 
configure the course details such as who the instructor of the course is, when the course is available, 
and control who can attend the course. ', 'cp'); ?>
        </div>
        <br /><br />
        <img alt="" src="<?php echo trailingslashit($this->plugin_url) . 'images/quick-setup-step-1.png'; ?>" class="image-66">
    </div>

    <div class="changelog">
        <h3><?php _e('Step 2. Create units for the course', 'cp'); ?></h3>

        <div class="about-text">
            <?php
            _e('After setting up the course, you move to the fun bit of creating the content for your course. Content 
for the course is broken down into units. A unit might contain videos, audio, multiple choice 
questions if you want to assess your students, file uploads if the students are asked to submit essays 
and much more. 
', 'cp');
            ?>
            <br /><br />

        </div>
        <img alt="" src="<?php echo trailingslashit($this->plugin_url) . 'images/quick-setup-step-2.png'; ?>" class="image-66">

    </div>

    <div class="changelog">
        <h3><?php _e('Step 3. Have students enrol in your course', 'cp'); ?></h3>

        <div class="about-text">
            <?php
            _e('Once you are happy your course is setup, you are now ready to accept students. There are 
numerous ways you can do this depending on how you configured the course. Students might be 
required to pay for the course, you might want to give out a private code for students to enter when 
they enrol, you might want to control who can do the course by entering in their email addresses 
yourself or you might just want to let anybody interested do the course.', 'cp');
            ?>
            <br /><br />

        </div>
      
    </div>
    
     <div class="changelog">
        <h3><?php _e('Step 4. Publish and watch your students learn!', 'cp'); ?></h3>

        <div class="about-text">
            <?php
            _e('There are many other features in Coursepress, but those are the basics to get you up and running. 
Now it’s time to publish the course and watch your students learn', 'cp');
            ?>
            <br /><br />

        </div>
        <img alt="" src="<?php echo trailingslashit($this->plugin_url) . 'images/quick-setup-step-3.png'; ?>" class="image-66">

    </div>




</div>