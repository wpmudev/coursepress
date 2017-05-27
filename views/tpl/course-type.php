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
                <label class="label"><?php _e( 'Language', 'cp' ); ?></label>
                <select></select>
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

                <div class="cp-info-box">
                    <div class="cp-info-content active" id="type-auto-moderated">
                        <p><?php _e( 'All grading is done manually, any number of students can enroll in this course at any time. Similar to Envato & Treehouse courses. Instructors can participate in discussion.', 'cp' ); ?></p>
                        <p><?php _e( '(These settings can be changed at any time).', 'cp' ); ?></p>
                    </div>
                    <div class="cp-info-content" id="type-manual">
                        MANUAL
                    </div>
                    <div class="cp-info-content" id="type-sample_course">
                        SAMPLE COURSE
                    </div>
                </div>
            </div>

            <div class="cp-box cp-toggle-box cp-sep">
                <label>
                    <input type="checkbox" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <?php _e( 'Enable course discussion', 'cp' ); ?>
                </label>
                <p class="description"><?php _e( 'Creates discussion area where users can post questions and get help from instructors, facilitators and other students', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-toggle-box cp-sep">
                <label>
                    <input type="checkbox" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <?php _e( 'Enable workbook', 'cp' ); ?>
                </label>
                <p class="description"><?php _e( 'Users can access their workbook which will show their progress/scores for the course.', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" class="cp-toggle-input" autocomplejte="off" /> <span class="cp-toggle-btn"></span>
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