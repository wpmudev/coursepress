<script type="text/template" id="coursepress-course-settings-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Course Settings', 'cp' ); ?></h2>
	</div>

    <div class="cp-box-content step-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Course listing info', 'cp' ); ?></label>
            <p class="description"><?php _e( 'This is the information that will be displayed for this course on the Courses listing page.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="listing_image"><?php _e( 'Course feature image', 'cp' ); ?></label>
                <input type="text" class="cp-add-image-input" id="listing_image" name="meta_listing_image" value="{{listing_image}}" data-thumbnail="20" data-size="medium" data-title="<?php _e( 'Select Feature Image', 'cp' ); ?>" />
            </div>

            <div class="cp-box">
                <label class="label" for="course-excerpt"><?php _e( 'Course short description', 'cp' ); ?></label>
                <textarea name="post_excerpt" class="widefat" id="course-excerpt" rows="5">{{post_excerpt}}</textarea>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Course details', 'cp' ); ?></label>
            <p class="description"><?php _e( 'This is the information that will be displayed on the main page of the course.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="course-description"><?php _e( 'Course full description', 'cp' ); ?></label>
                <textarea name="post_content" class="widefat" id="course-description" rows="10">{{post_content}}</textarea>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Course overview video', 'cp' ); ?></label>
                <div class="cp-flex">
                    <div class="cp-div-flex">
                        <input type="text" class="widefat cp-add-video" name="featured_video" placeholder="<?php _e( 'Paste URL or browse uploaded files', 'cp' ); ?>" data-id="0" data-title="<?php _e( 'Select Feature Image', 'cp' ); ?>" />
                    </div>
                    <div class="cp-div-auto">
                        <button type="button" class="cp-btn cp-btn-default"><?php _e( 'Browse', 'cp' ); ?></button>
                    </div>
                </div>
            </div>

            <div class="cp-box">
                <label class="label" for="course-categories"><?php _e( 'Course categories', 'cp' ); ?></label>
                <div class="cp-flex">
                    <div class="cp-div-flex-2">
                        <select id="course-categories" multiple="multiple" data-placeholder="<?php _e( 'Pick existing categories or add new one', 'cp' ); ?>" name="course_category">
                            <?php foreach ( coursepress_get_categories() as $category ) : ?>
                                <option value="<?php echo $category; ?>" {{_.selected('<?php echo $category; ?>', course_category)}}><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cp-div-flex">
                        <button type="button" class="cp-btn"><?php _e( 'Create Category', 'cp' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Course settings', 'cp' ); ?></label>
            <p class="description"><?php _e( 'Set-up Enrollment and Pass Grade settings for this course.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="enrollment-type"><?php _e( 'Who can enroll', 'cp' ); ?></label>
                <select id="enrollment-type" name="meta_enrollment_type">
                    <?php foreach ( coursepress_get_enrollment_types() as $id => $label ) : ?>
                        <option value="<?php echo $id; ?>" {{_.selected('<?php echo $id; ?>', enrollment_type)}}><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Minimum passing grade', 'cp' ); ?></label>
                <input type="text" name="meta_minimum_grade_required" class="cp-input-auto" value="{{minimum_grade_required}}" /> %
                <p class="description"><?php _e( 'Minimum passing grade required for student to pass this course. Use 0 if course has no minimum passing grade.', 'cp' ); ?></p>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Course modules', 'cp' ); ?></label>
            <p class="description"><?php _e( 'Modules allow to you to group steps, they may be helpful for more complex course structures.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_with_modules" value="1" class="cp-toggle-input" autocomplejte="off" /> <span class="cp-toggle-btn"></span>
		            <span class="label"><?php _e( 'Enable modules for this course', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'The setting can be changed after the course has been set-up.', 'cp' ); ?></p>
                <p class="description"><a href=""><?php _e( 'Learn more', 'cp' ); ?></a></p>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Instructors & Facilitators', 'cp' ); ?></label>
            <p class="description"><?php _e( 'Instructors put a course together, whereas facilitators help answer student questions after the course has been launed. Instructor information is displayed on the front-end of the site, whereas a facilitator\'s is not.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label"><i class="fa fa-users"></i> <?php _e( 'Instructors', 'cp' ); ?></label>
                <div id="cp-instructors-box">
                    <p class="description"><?php _e( 'This course currently have no instructors', 'cp' ); ?></p>
                </div>
                <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs cp-right"><?php _e( 'Add Instructor', 'cp' ); ?></button>
            </div>

            <div class="cp-box">
                <label class="label"><i class="fa fa-users"></i> <?php _e( 'Facilitators', 'cp' ); ?></label>
                <div id="cp-facilitators-box">
                    <p class="description"><?php _e( 'This course currently have no facilitators', 'cp' ); ?></p>
                </div>
                <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs cp-right"><?php _e( 'Add Facilitators', 'cp' ); ?></button>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Course Completion', 'cp' ); ?></label>
            <p class="description"></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <ul class="cp-flex cp-input-group">
                    <li class="cp-div-flex"><?php _e( 'Pre', 'cp' ); ?></li>
                    <li class="cp-div-flex"><?php _e( 'Success', 'cp' ); ?></li>
                    <li class="cp-div-flex"><?php _e( 'Failed', 'cp' ); ?></li>
                </ul>
            </div>

            <div class="cp-box">
                <label class="label" for="pre-completion-title"><?php _e( 'Page Title', 'cp' ); ?></label>
                <input type="text" id="pre-completion-title" class="widefat" name="meta_pre_completion_title" value="{{pre_completion_title}}" />
            </div>
            <div class="cp-box">
                <label class="label" for="pre-completion-content"><?php _e( 'Content', 'cp' ); ?></label>
                <textarea id="pre-completion-content" class="widefat" name="meta_pre_completion_content" rows="5">{{pre_completion_content}}</textarea>
            </div>
        </div>
    </div>

    <div class="cp-box-content">
        <div class="box-label-area">
            <label class="label"><?php _e( 'Certificate', 'cp' ); ?></label>
        </div>

        <div class="box-inner-content">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Enable custom certificate setup', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'Creates custom certificate for this course that overrides default certificate settings.', 'cp' ); ?></p>
            </div>
        </div>
    </div>
</script>