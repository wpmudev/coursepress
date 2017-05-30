<script type="text/template" id="coursepress-course-settings-tpl">
	<div class="step-heading">
		<h2 class="step-heading-title"><?php _e( 'Course Settings', 'cp' ); ?></h2>
	</div>

    <div class="step-content step-sep">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Course listing info', 'cp' ); ?></label>
            <p class="description"><?php _e( 'This is the information that will be displayed for this course on the Courses listing page.', 'cp' ); ?></p>
        </div>

        <div class="step-inner-content">
            <div class="cp-box">
                <label class="label"><?php _e( 'Course feature image', 'cp' ); ?></label>
                <input type="text" class="cp-add-image-input" name="feature_image" data-thumbnail="20" data-size="medium" data-title="<?php _e( 'Select Feature Image', 'cp' ); ?>" />
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Course short description', 'cp' ); ?></label>
                <textarea name="post_excerpt" rows="5">{{post_excerpt}}</textarea>
            </div>
        </div>
    </div>

    <div class="step-content cp-sep">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Course details', 'cp' ); ?></label>
            <p class="description"><?php _e( 'This is the information that will be displayed on the main page of the course.', 'cp' ); ?></p>
        </div>

        <div class="step-inner-content">
            <div class="cp-box">
                <label class="label"><?php _e( 'Course full description', 'cp' ); ?></label>
                <textarea name="post_content" rows="10">{{post_content}}</textarea>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Course overview video', 'cp' ); ?></label>
                <div class="cp-flex cp-flex-2">
                    <div class="cp-div-left">
                        <input type="text" class="cp-add-video" name="feature_video" placeholder="<?php _e( 'Paste URL or browse uploaded files', 'cp' ); ?>" data-id="0" data-title="<?php _e( 'Select Feature Image', 'cp' ); ?>" />
                    </div>
                    <div class="cp-div-right">
                        <button type="button" class="cp-btn cp-btn-default"><?php _e( 'Browse', 'cp' ); ?></button>
                    </div>
                </div>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Course categories', 'cp' ); ?></label>
                <div class="cp-flex cp-flex-2">
                    <div class="cp-div-left">
                        <input type="text" placeholder="<?php _e( 'Pick or create categories', 'cp' ); ?>" />
                    </div>
                    <div class="cp-div-right">
                        <button type="button" class="cp-btn"><?php _e( 'Create Category', 'cp' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="step-content cp-sep">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Course settings', 'cp' ); ?></label>
            <p class="description"><?php _e( 'Set-up Enrollment and Pass Grade settings for this course.', 'cp' ); ?></p>
        </div>

        <div class="step-inner-content">
            <div class="cp-box">
                <label class="label"><?php _e( 'Who can enroll', 'cp' ); ?></label>
                <select></select>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Minimum passing grade', 'cp' ); ?></label>
                <input type="text" class="cp-input-auto"/> %
                <p class="description"><?php _e( 'Minimum passing grade required for student to pass this course. Use 0 if course has no minimum passing grade.', 'cp' ); ?></p>
            </div>
        </div>
    </div>

    <div class="step-content cp-sep">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Instructors & Facilitators', 'cp' ); ?></label>
            <p class="description"><?php _e( 'Instructors put a course together, whereas facilitators help answer student questions after the course has been launed. Instructor information is displayed on the front-end of the site, whereas a facilitator\'s is not.', 'cp' ); ?></p>
        </div>

        <div class="step-inner-content">
            <div class="cp-box">
                <label class="label"><i class="fa fa-users"></i> <?php _e( 'Instructors', 'cp' ); ?></label>
                <div id="cp-instructors-box">
                    <p class="description"><?php _e( 'This course currently have no instructors', 'cp' ); ?></p>
                </div>
                <button type="button" class="cp-btn cp-bordered-btn cp-right"><?php _e( 'Add Instructor', 'cp' ); ?></button>
            </div>

            <div class="cp-box">
                <label class="label"><i class="fa fa-users"></i> <?php _e( 'Facilitators', 'cp' ); ?></label>
                <div id="cp-facilitators-box">
                    <p class="description"><?php _e( 'This course currently have no facilitators', 'cp' ); ?></p>
                </div>
                <button type="button" class="cp-btn cp-bordered-btn cp-right"><?php _e( 'Add Facilitators', 'cp' ); ?></button>
            </div>
        </div>
    </div>

    <div class="step-content cp-sep">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Course Completion', 'cp' ); ?></label>
            <p class="description"></p>
        </div>

        <div class="step-inner-content">
            <div class="cp-box">
                <ul class="cp-input-group">
                    <li><?php _e( 'Pre', 'cp' ); ?></li>
                    <li><?php _e( 'Success', 'cp' ); ?></li>
                    <li><?php _e( 'Failed', 'cp' ); ?></li>
                </ul>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Page Title', 'cp' ); ?></label>
                <input type="text" />
            </div>
            <div class="cp-box">
                <label class="label"><?php _e( 'Content', 'cp' ); ?></label>
                <textarea rows="5"></textarea>
            </div>
        </div>
    </div>

    <div class="step-content">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Certificate', 'cp' ); ?></label>
        </div>

        <div class="step-inner-content">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <?php _e( 'Enable custom certificate setup', 'cp' ); ?>
                </label>
                <p class="description"><?php _e( 'Creates custom certificate for this course that overrides default certificate settings.', 'cp' ); ?></p>
            </div>
        </div>
    </div>
</script>