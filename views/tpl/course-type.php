<script type="text/template" id="coursepress-course-type-tpl">
	<div class="step-heading">
		<h2 class="step-heading-title"><?php _e( 'Pick name and type of course to create', 'cp' ); ?></h2>
	</div>
    <div class="step-content step-sep">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Course name and language', 'cp' ); ?></label>
        </div>
        <div class="step-inner-content">
            <div class="cp-box">
                <label class="label"><?php _e( 'Course Name', 'cp' ); ?></label>
                <input type="text" class="input-text" name="post_title" value="{{post_title}}" />
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Course Slug', 'cp' ); ?></label>
                <input type="text" name="post_name" value="{{post_name}}" />
                <p class="description"><?php echo coursepress_get_url(); ?><span class="cp-slug">{{post_name}}</span>/</p>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Language', 'cp' ); ?></label>
                <input type="text" name="meta_course_language" value="{{course_language}}" />
            </div>
        </div>
    </div>

    <div class="step-content">
        <div class="step-label-area">
            <label class="label"><?php _e( 'Course type', 'cp' ); ?></label>
            <p class="description"><?php _e( 'Pick a type of course you want to create', 'cp' ); ?></p>
        </div>
        <div class="step-inner-content">
            <div class="cp-box">
                <label class="label"><?php _e( 'Pick course type or load example course', 'cp' ); ?></label>
                <ul class="cp-input-group">
                    <li class="active">
                        <label>
                            <input type="radio" name="course_type" value="auto-moderated" />
                            <?php _e( 'Auto-moderated', 'cp' ); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="course_type" value="manual" />
                            <?php _e( 'Manual moderation', 'cp' ); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="course_type" value="sample_course" />
                            <?php _e( 'Example course', 'cp' ); ?>
                        </label>
                    </li>
                </ul>
            </div>

            <div class="cp-box cp-course-type active" id="type-auto-moderated">
                <div class="cp-alert cp-alert-info">
                    <p><?php _e( 'All grading is done manually, any number of students can enroll in this course at any time. Similar to Envato & Treehouse courses. Instructors can participate in discussion.', 'cp' ); ?></p>
                    <p><?php _e( '(These settings can be changed at any time).', 'cp' ); ?></p>
                </div>
            </div>

            <div class="cp-box cp-sep cp-course-type inactive" id="type-manual">
                <div class="cp-box">
                <label class="label"><?php _e( 'Class Size', 'cp' ); ?></label>
                <?php _e( 'Number of students', 'cp' ); ?> <input type="number" name="meta_class_size" class="input-inline" value="{{class_size}}" />
                </div>

                <label class="label"><?php _e( 'Course Availability', 'cp' ); ?></label>
                <div class="cp-flex">
                    <div class="cp-div-flex cp-pad-right">
                        <span class="course-title-tag"><?php _e( 'Start Date', 'cp' ); ?></span>
                        <input type="text" name="meta_course_start_date" value="{{course_start_date}}" />
                    </div>
                    <div class="cp-div-flex cp-pad-left">
                        <span class="course-title-tag"><?php _e( 'End Date', 'cp' ); ?></span>
                        <input type="text" name="meta_course_end_date" value="{{course_end_date}}" />
                    </div>
                </div>
                <br />
                <label class="label"><?php _e( 'Enrollment Date', 'cp' ); ?></label>
                <div class="cp-flex">
                    <div class="cp-div-flex cp-pad-right">
                        <span class="course-title-tag"><?php _e( 'Start Date', 'cp' ); ?></span>
                        <input type="text" name="meta_enrollment_start_date" value="{{enrollment_start_date}}" />
                    </div>
                    <div class="cp-div-flex cp-pad-left">
                        <span class="course-title-tag"><?php _e( 'End Date', 'cp' ); ?></span>
                        <input type="text" name="meta_enrollment_end_date" value="{{enrollment_end_date}}" />
                    </div>
                </div>
            </div>

            <div class="cp-box cp-course-type inactive" id="type-sample_course">
                <div class="cp-alert cp-alert-info">
                    YEAH YEAH
                </div>
            </div>

            <div class="cp-box cp-toggle-box cp-sep">
                <label>
                    <input type="checkbox" name="meta_allow_discussion" {{_.checked(true, allow_discussion)}} class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <?php _e( 'Enable course discussion', 'cp' ); ?>
                </label>
                <p class="description"><?php _e( 'Creates discussion area where users can post questions and get help from instructors, facilitators and other students', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-toggle-box cp-sep">
                <label>
                    <input type="checkbox" name="meta_allow_workbook" {{_.checked(true, allow_workbook)}} class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <?php _e( 'Enable workbook', 'cp' ); ?>
                </label>
                <p class="description"><?php _e( 'Users can access their workbook which will show their progress/scores for the course.', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_payment_paid_course" {{_.checked(true, payment_paid_course)}} class="cp-toggle-input" autocomplejte="off" /> <span class="cp-toggle-btn"></span>
                    <?php _e( 'This is a paid course', 'cp' ); ?>
                </label>
                <p class="description"><?php _e( 'Will allow you to set-up payment gateway/options.', 'cp' ); ?></p>
            </div>
        </div>
    </div>

    <?php
    /**
     * Trigger when all course type fields are printed.
     *
     * @since 3.0
     * @param int $course_id Current course ID created or edited.
     */
    do_action( 'coursepress_course_setup-course-type', $course_id );
    ?>
</script>