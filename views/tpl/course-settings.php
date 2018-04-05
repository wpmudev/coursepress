<script type="text/template" id="coursepress-course-settings-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Course Settings', 'cp' ); ?></h2>
	</div>

    <div class="cp-box-content step-sep">
        <div class="box-label-area">
            <h3 class="label"><?php _e( 'Course listing info', 'cp' ); ?></h3>
            <p class="description"><?php _e( 'This is the information that will be displayed for this course on the Courses listing page.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="listing_image"><?php _e( 'Course feature image', 'cp' ); ?></label>
                <input type="text" class="cp-add-image-input" id="listing_image" name="meta_listing_image" value="{{listing_image}}" data-thumbnail="20" data-size="medium" data-title="<?php _e( 'Select Feature Image', 'cp' ); ?>" />
            </div>

            <div class="cp-box">
                <label class="label" for="course-excerpt"><?php _e( 'Course short description', 'cp' ); ?></label>
                <div class="cp-course-overview"></div>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <h3 class="label"><?php _e( 'Course details', 'cp' ); ?></h3>
            <p class="description"><?php _e( 'This is the information that will be displayed on the main page of the course.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label" for="course-description"><?php _e( 'Course full description', 'cp' ); ?></label>
                <div class="cp-course-description"></div>
            </div>

            <div class="cp-box cp-course-video">
                <label class="label"><?php _e( 'Course overview video', 'cp' ); ?></label>
                <input type="text" class="widefat cp-add-video" id="listing_video" name="meta_featured_video" value="{{meta_featured_video}}"  data-title="<?php _e( 'Select Feature Video', 'cp' ); ?>" />
            </div>

            <div class="cp-box cp-course-categories">
                <h3 class="label" for="course-categories"><?php _e( 'Course categories', 'cp' ); ?></h3>
                <div>
                <div class="cp-categories-selector">
                    <select id="course-categories" multiple="multiple"
<?php $can_manage_categories = CoursePress_Data_Capabilities::can_manage_categories(); ?>
<?php $can_assign_instructor = CoursePress_Data_Capabilities::can_assign_course_instructor( $course_id ); ?>
<?php $can_assign_facilitator = CoursePress_Data_Capabilities::can_assign_facilitator( $course_id ); ?>
<?php if ( $can_manage_categories ) { ?>
data-placeholder="<?php _e( 'Pick existing categories or add new one', 'cp' ); ?>"
data-can-add="yes"
<?php } else { ?>
data-placeholder="<?php _e( 'Pick existing categories', 'cp' ); ?>"
data-can-add="no"
<?php } ?>
name="meta_course_category">
                            <?php foreach ( coursepress_get_categories() as $category ) : ?>
                                <option value="<?php echo $category; ?>" {{_.selected('<?php echo $category; ?>', course_category)}}><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="course-categories-search" value="">
                    </div>
<?php if ( $can_manage_categories ) { ?>
                    <p class="description"><?php _e( 'To add new category, name it and use enter key.', 'cp' ); ?></p>
<?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <h3 class="label"><?php _e( 'Course settings', 'cp' ); ?></h3>
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
            <div class="cp-box cp-boxes cp-passcode-box {{'passcode'!== meta_enrollment_type? 'inactive':''}}">
                <label class="label"><?php _e( 'Passcode', 'cp' ); ?></label>
                <input type="text" name="meta_enrollment_passcode" value="{{meta_enrollment_passcode}}" />
                <p class="description"><?php _e( 'Enter the passcode required to access the course.', 'cp' ); ?></p>
            </div>
            <div class="cp-box cp-boxes cp-requisite-box {{'prerequisite'!== meta_enrollment_type? 'inactive':''}}">
                <label class="label"><?php _e( 'Select required course', 'cp' ); ?></label>
                <div class="cp-courses-box">
                    <?php if ( ! empty( $courses ) ) : ?>
                    <select name="meta_enrollment_prerequisite" multiple="multiple">
                        <?php
												foreach ( $courses as $course ) :
													if( $course->ID !== $course_id ) :
													?>
                            <option value="<?php echo $course->ID; ?>" {{_.selected('<?php echo $course->ID; ?>', meta_enrollment_prerequisite)}}><?php echo $course->post_title; ?></option>
                        <?php
													endif;
												endforeach;
												?>
                    </select>
                    <?php else : ?>
                        <p class="description"><?php _e( 'No courses available!', 'cp' ); ?></p>
                    <?php endif; ?>
                </div>
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
            <h3 class="label"><?php _e( 'Course modules', 'cp' ); ?></h3>
            <p class="description"><?php _e( 'Modules allow to you to group steps, they may be helpful for more complex course structures.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content">
            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_with_modules" value="1" class="cp-toggle-input" autocomplete="off" {{_.checked(true, with_modules)}} /> <span class="cp-toggle-btn"></span>
		            <span class="label"><?php _e( 'Enable modules for this course', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'The setting can be changed after the course has been set-up.', 'cp' ); ?></p>
                <p class="description"><a class="cp-course-modules-help" href="#"><?php _e( 'Learn more', 'cp' ); ?></a></p>
            </div>
        </div>
    </div>

    <div class="cp-box-content">
        <div class="box-label-area">
            <h3 class="label"><?php _e( 'Instructors & Facilitators', 'cp' ); ?></h3>
            <p class="description"><?php _e( 'Instructors put a course together, whereas facilitators help answer student questions after the course has been launed. Instructor information is displayed on the front-end of the site, whereas a facilitator\'s is not.', 'cp' ); ?></p>
        </div>

        <div class="box-inner-content cp-users-box">
            <div class="cp-box">
                <label class="label"><i class="fa fa-users"></i> <?php _e( 'Instructors', 'cp' ); ?></label>
                <div id="cp-instructors-box">
                    <?php $instructors = coursepress_get_course_instructors( $course_id ); ?>
                    <ul id="cp-list-instructor" class="cp-tagged-list cp-tagged-list-removable" data-user-type="instructor">
                        <?php if ( ! empty( $instructors ) ) : ?>
                            <?php foreach ( $instructors as $instructor ) : ?>
                                <li data-user-id="<?php echo $instructor->ID; ?>"><?php echo $instructor->get_name(); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <?php if ( empty( $instructors ) ) : ?>
                        <p class="description" id="cp-no-instructor"><?php _e( 'This course currently have no instructors', 'cp' ); ?></p>
                    <?php endif; ?>
                </div>
                <?php if ( $can_assign_instructor ) : ?>
                    <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs cp-right" id="cp-instructor-selector"><?php _e( 'Add Instructor', 'cp' ); ?></button>
                <?php endif; ?>
            </div>

            <div class="cp-box">
                <label class="label"><i class="fa fa-users"></i> <?php _e( 'Facilitators', 'cp' ); ?></label>
                <div id="cp-facilitators-box">
                    <ul id="cp-list-facilitator" class="cp-tagged-list cp-tagged-list-removable" data-user-type="facilitator">
                        <?php $facilitators = coursepress_get_course_facilitators( $course_id ); ?>
                        <?php if ( ! empty( $facilitators ) ) : ?>
                            <?php foreach ( $facilitators as $facilitator ) : ?>
                                <li data-user-id="<?php echo $facilitator->ID; ?>"><?php echo empty( $facilitator->display_name ) ? $facilitator->user_login : $facilitator->display_name; ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <?php if ( empty( $facilitators ) ) : ?>
                        <p class="description" id="cp-no-facilitator"><?php _e( 'This course currently have no facilitators', 'cp' ); ?></p>
                    <?php endif; ?>
                </div>
                <?php if ( $can_assign_facilitator ) : ?>
                    <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs cp-right" id="cp-facilitator-selector"><?php _e( 'Add Facilitators', 'cp' ); ?></button>
                <?php endif; ?>
            </div>
        </div>

		<div class="cp-flex clear cp-invited-container">

			<div class="cp-content-box" id="invited-instructors">
				<table class="coursepress-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Instructor Name', 'cp' ); ?></th>
							<th><?php esc_html_e( 'Email', 'cp' ); ?></th>
							<?php if ( $can_assign_instructor ) : ?><th></th><?php endif; ?>
						</tr>
					</thead>
					<tbody id="invited-instructor-list">
						<tr class="no-invites <?php echo empty( false ) ? '' : 'inactive'; ?>">
							<td colspan="4"><?php esc_html_e( 'No invited instructors found...', 'cp' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="cp-content-box" id="invited-facilitators">
				<table class="coursepress-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Facilitator Name', 'cp' ); ?></th>
							<th><?php esc_html_e( 'Email', 'cp' ); ?></th>
							<?php if ( $can_assign_facilitator ) : ?><th></th><?php endif; ?>
						</tr>
					</thead>
					<tbody id="invited-facilitator-list">
						<tr class="no-invites <?php echo empty( false ) ? '' : 'inactive'; ?>">
							<td colspan="4"><?php esc_html_e( 'No invited facilitators found...', 'cp' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

    </div>

</script>

<script type="text/template" id="coursepress-invited-instructor">
    <td>{{first_name}} {{last_name}}</td>
    <td>{{email}}</td>
    <?php if ( $can_assign_instructor ) : ?>
        <td>
            <button data-code="{{code}}" class="cp-btn cp-btn-xs cp-btn-active remove-invite" title="<?php _e( 'Remove invitation', 'cp' ); ?>"><?php _e( 'Remove', 'cp' ); ?></button>
        </td>
    <?php endif; ?>
</script>

<script type="text/template" id="coursepress-invited-facilitator">
    <td>{{first_name}} {{last_name}}</td>
    <td>{{email}}</td>
    <?php if ( $can_assign_facilitator ) : ?>
        <td>
            <button data-code="{{code}}" class="cp-btn cp-btn-xs cp-btn-active remove-invite" title="<?php _e( 'Remove invitation', 'cp' ); ?>"><?php _e( 'Remove', 'cp' ); ?></button>
        </td>
    <?php endif; ?>
</script>

<script type="text/template" id="coursepress-course-instructor-selection-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3><?php _e( 'ADD INSTRUCTOR', 'cp' ); ?></h3>
            </div>
            <span class="cp-modal-close cp-close"></span>
        </div>
        <div class="coursepress-popup-content cp-content-nopad">
            <div class="cp-flex">
                <div class="cp-div-flex cp-pad-right cp-div-grey">
                    <label class="label"><?php _e( 'Invite by email', 'cp' ); ?></label>
                    <div class="cp-box"><input type="text" id="cp-invite-first-name-instructor" placeholder="<?php _e( 'First Name' ); ?>" /></div>
                    <div class="cp-box"><input type="text" id="cp-invite-last-name-instructor" placeholder="<?php _e( 'Last Name' ); ?>" /></div>
                    <div class="cp-box"><input type="email" id="cp-invite-email-instructor" placeholder="<?php _e( 'Email' ); ?>" /></div>
                    <button type="button" class="cp-btn cp-send-invite cp-invite-btn">
                        <i class="fa fa-circle-o-notch fa-spin"></i>
                        <?php _e( 'Send Invite', 'cp' ); ?>
                    </button>
                    <p class="cp-invitation-response-instructor inactive">ss</p>
                </div>
                <div class="cp-div-flex cp-pad-left">
                    <label class="label"><?php _e( 'Assign instructor from existing users', 'cp' ); ?></label>
                    <select id="cp-course-instructor">
                        <option value=""><?php _e( 'Search users', 'cp' ); ?></option>
                    </select>
                    <button type="button" class="cp-btn cp-assign-user cp-invite-btn">
                        <i class="fa fa-circle-o-notch fa-spin"></i>
                        <?php _e( 'Assign', 'cp' ); ?>
                    </button>
                    <p class="cp-assign-response-instructor inactive"></p>
                </div>
            </div>
        </div>
        <div class="coursepress-popup-footer">
            <button type="button" class="cp-btn cp-btn-active cp-close"><?php _e( 'Done', 'cp' ); ?></button>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-course-facilitator-selection-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <h3><?php _e( 'ADD FACILITATOR', 'cp' ); ?></h3>
        </div>
        <div class="coursepress-popup-content cp-content-nopad">
            <div class="cp-flex">
                <div class="cp-div-flex cp-pad-right cp-div-grey">
                    <label class="label"><?php _e( 'Invite by email', 'cp' ); ?></label>
                    <div class="cp-box"><input type="text" id="cp-invite-first-name-facilitator" placeholder="<?php _e( 'First Name' ); ?>" /></div>
                    <div class="cp-box"><input type="text" id="cp-invite-last-name-facilitator" placeholder="<?php _e( 'Last Name' ); ?>" /></div>
                    <div class="cp-box"><input type="email" id="cp-invite-email-facilitator" placeholder="<?php _e( 'Email' ); ?>" /></div>
                    <button type="button" class="cp-btn cp-send-invite cp-invite-btn">
                        <i class="fa fa-circle-o-notch fa-spin"></i>
                        <?php _e( 'Send Invite', 'cp' ); ?>
                    </button>
                </div>
                <div class="cp-div-flex cp-pad-left">
                    <label class="label"><?php _e( 'Assign facilitator from existing users', 'cp' ); ?></label>
                    <select id="cp-course-facilitator">
                        <option value=""><?php _e( 'Search users', 'cp' ); ?></option>
                    </select>
                    <button type="button" class="cp-btn cp-assign-user cp-invite-btn">
                        <i class="fa fa-circle-o-notch fa-spin"></i>
                        <?php _e( 'Assign', 'cp' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="coursepress-popup-footer">
            <button type="button" class="cp-btn cp-btn-active step-next cp-close"><?php _e( 'DONE', 'cp' ); ?></button>
        </div>
    </div>
</script>
