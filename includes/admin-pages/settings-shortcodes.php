<div id="poststuff" class="metabox-holder m-settings">
    <form action='' method='post'>


        <div class="postbox">
            <h3 class='hndle'><span><?php _e( 'Shortcodes', 'cp' ) ?></span></h3>
            <div class="inside">
                <p><?php _e( 'Shortcodes allow you to include dynamic content in posts and pages on your site. Simply type or paste them into your post or page content where you would like them to appear. Optional attributes can be added in a format like <em>[shortcode attr1="value" attr2="value"]</em>. ', 'cp' ) ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Instructors List', 'cp' ) ?></th>
                        <td>
                            <strong>[course_instructors]</strong> -
                            <span class="description"><?php _e( 'Display a list or count of Instructors ( gravatar, name and link to profile page )', 'cp' ) ?></span>

                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course instructors are assign to ( required if use it outside of a loop )', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"count" - If this attribute is used, only number of instructors will be returned without list', 'cp' ) ?></li>

                                <li><?php _e( 'Examples:', 'cp' ) ?> <em>[course_instructors], [course_instructors course_id="5"], [course_instructors count="true"]</em></li>
                            </ul>

                            <span class="description"></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Instructor Avatar', 'cp' ) ?></th>
                        <td>
                            <strong>[course_instructor_avatar]</strong> -
                            <span class="description"><?php _e( "Display instructor's gravatar", 'cp' ) ?></span>

                            <p><strong><?php _e( 'Required Attributes:', 'cp' ) ?></strong></p>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"instructor_id" - ID of the instructor', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Examples:', 'cp' ) ?> <em>[course_instructor_avatar instructor_id="1"]</em></li>
                            </ul>

                            <span class="description"></span>
                        </td>
                    </tr>
				
                    <tr>
                        <th scope="row"><?php _e( 'Course Details', 'cp' ) ?></th>
                        <td>
                            <strong>[course_details]</strong> -
                            <span class="description"><?php _e( 'Display additional course information like start date, end date, price etc.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course instructors are assign to ( required if use it outside of a loop )', 'cp' ) ?></li>
                                <li><?php _e( '"field" - What fields to display. Possible values: course_start_date, course_end_date, enrollment_start_date, enrollment_end_date, price, button, passcode, class_size and standard post type fields ( ID, post_author, post_date, post_content, post_title, post_status, post_name, post_modified etc. )', 'cp' ) ?></li>

                                <li><?php _e( 'Examples:', 'cp' ) ?> <em>[course_instructors field="course_start_date"], [course_instructors field="button" course_id="5"]</em></li>
                            </ul>

                            <span class="description"></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Student Dashboard', 'cp' ) ?></th>
                        <td>
                            <strong>[courses_student_dashboard]</strong> -
                            <span class="description"><?php _e( 'Display content of the student dashboard including enrolled courses', 'cp' ) ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Student Settings', 'cp' ) ?></th>
                        <td>
                            <strong>[courses_student_settings]</strong> -
                            <span class="description"><?php _e( 'Display content of the student settings page where they can change username, password etc.', 'cp' ) ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Student Registration Form', 'cp' ) ?></th>
                        <td>
                            <strong>[student_registration_form]</strong> -
                            <span class="description"><?php _e( 'Display custom registration form for students', 'cp' ) ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Course Units', 'cp' ) ?></th>
                        <td>
                            <strong>[course_units]</strong> -
                            <span class="description"><?php _e( 'Display list of the Units for the course ( Units Archive )', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Required Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_units course_id="5"]</em></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Units Details', 'cp' ) ?></th>
                        <td>
                            <strong>[course_unit_details]</strong> -
                            <span class="description"><?php _e( 'Display list of the Units for the course ( Units Archive )', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Required Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"unit_id" - ID of the Unit', 'cp' ) ?></li>
                            </ul>
                            
                            <p><strong><?php _e( 'Optional Attribute:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"field" - post type field we want to show ( ID, post_author, post_date, post_content, post_title, post_status, post_name, post_modified etc. )', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_unit_details unit_id="5" field="post_title"]</em></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2" scope="row"><?php _e( 'Course Information Shortcodes', 'cp' ) ?><hr /></th>
					</tr>
					<tr>
						<td><?php _e( 'Course Title', 'cp' ) ?></td>
                        <td>
                            <strong>[course_title]</strong> -
                            <span class="description"><?php _e( 'Displays the course title.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"title_tag" - The HTML element to wrap the title in. (default: h3)', 'cp' ) ?></li>								
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_title course_id="4"], [course_title]</em></li>				
                            </ul>
                        </td>
                    </tr>
					<tr>
						<td><?php _e( 'Course Summary', 'cp' ) ?></td>
                        <td>
                            <strong>[course_summary]</strong> -
                            <span class="description"><?php _e( 'Displays the course summary/excerpt.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_summary course_id="4"], [course_summary]</em></li>				
                            </ul>
                        </td>
                    </tr>
					<tr>
						<td><?php _e( 'Course Description', 'cp' ) ?></td>
                        <td>
                            <strong>[course_description]</strong> -
                            <span class="description"><?php _e( 'Displays the longer course description.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_description course_id="4"], [course_description]</em></li>				
                            </ul>
                        </td>
                    </tr>
					<tr>
						<td><?php _e( 'Course Start', 'cp' ) ?></td>
                        <td>
                            <strong>[course_start]</strong> -
                            <span class="description"><?php _e( 'Displays the course start date.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"date_format" - PHP style date format. (default: as set in WordPress settings)', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to date (defaul: Course Start Date). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>																																
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_start], [course_start label="Awesomeness begins on" label_tag="h3"]</em></li>				
                            </ul>
                        </td>
                    </tr>										
					<tr>
						<td><?php _e( 'Course End', 'cp' ) ?></td>
                        <td>
                            <strong>[course_end]</strong> -
                            <span class="description"><?php _e( 'Displays the course end date.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"date_format" - PHP style date format. (default: as set in WordPress settings)', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to date (defaul: Course End Date). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>
								<li><?php _e( '"no_date_text" - Text to display if the course has no end date. (default: No End Date)', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_end], [course_end label="End" label_delimeter="-"]</em></li>				
                            </ul>
                        </td>
                    </tr>										
					<tr>
						<td><?php _e( 'Course Dates', 'cp' ) ?></td>
                        <td>
                            <strong>[course_dates]</strong> -
                            <span class="description"><?php _e( 'Displays the course start and end date range. Typically as [course_start] - [course_end].', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"date_format" - PHP style date format. (default: as set in WordPress settings)', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to date (defaul: Course End Date). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>																																
                                <li><?php _e( '"no_date_text" - Text to display when there is no end-date. (default: No End Date)', 'cp' ) ?></li>	
                                <li><?php _e( '"alt_display_text" - Alternate display when there is no end date. (default: Open-ended)', 'cp' ) ?></li>
                                <li><?php _e( '"show_alt_display" - Display alternate text if there is no end date. (default: no)', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_dates], [course_dates show_alt_display="yes" alt_display_text="Learn Anytime!"]</em></li>				
                            </ul>
                        </td>
                    </tr>										
					<tr>
						<td><?php _e( 'Course Enrollment Start', 'cp' ) ?></td>
                        <td>
                            <strong>[course_enrollment_start]</strong> -
                            <span class="description"><?php _e( 'Displays the enrollment start date.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"date_format" - PHP style date format. (default: as set in WordPress settings)', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to date (defaul: Enrollment Start Date). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>
								<li><?php _e( '"no_date_text" - Text to display if the course has no defined enrollment period. (default: Enroll Anytime)', 'cp' ) ?></li>
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_enrollment_start], [course_enrollment_start label="Signup from" label_tag="em"]</em></li>				
                            </ul>
                        </td>
                    </tr>										
					<tr>
						<td><?php _e( 'Course Enrollment End', 'cp' ) ?></td>
                        <td>
                            <strong>[course_enrollment_end]</strong> -
                            <span class="description"><?php _e( 'Displays the enrollment end date.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"date_format" - PHP style date format. (default: as set in WordPress settings)', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to date (defaul: Course End Date). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>																																
								<li><?php _e( '"no_date_text" - Text to display if the course has no defined enrollment period. (default: Enroll Anytime)', 'cp' ) ?></li>
								<li><?php _e( '"show_all_dates" - Show/hide the end date if the course has no defined enrollment period. (default: no)', 'cp' ) ?></li>	
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_end], [course_end label="End" label_delimeter="-"]</em></li>				
                            </ul>
                        </td>
                    </tr>										
					<tr>
						<td><?php _e( 'Enrollment Dates', 'cp' ) ?></td>
                        <td>
                            <strong>[course_enrollment_dates]</strong> -
                            <span class="description"><?php _e( 'Displays the course enrollment start and end date range. Typically as [course_enrollment_start] - [course_enrollment_end].', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"date_format" - PHP style date format. (default: as set in WordPress settings)', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to date (defaul: Course End Date). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>																																
                                <li><?php _e( '"no_date_text" - Text to display when there is no defined enrollment period. (default: Enroll Anytime)', 'cp' ) ?></li>	
								<li><?php _e( '"show_all_dates" - Show/hide the end date if the course has no defined enrollment period. (default: no)', 'cp' ) ?></li>									
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_enrollment_dates], [course_enrollment_dates no_date_text="No better time than now!"]</em></li>				
                            </ul>
                        </td>
                    </tr>													
					<tr>
						<td><?php _e( 'Course Class Size', 'cp' ) ?></td>
                        <td>
                            <strong>[course_class_size]</strong> -
                            <span class="description"><?php _e( 'Displays the available spaces in a course.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"show_no_limit" - Show class size even if there is no limit. no_limit_text will be displayed. (default: no)', 'cp' ) ?></li>
								<li><?php _e( '"show_remaining" - Show available spaces in the course. remaining_text will be displayed. (default: yes)', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to class limit (defaul: Class Size). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>																																
                                <li><?php _e( '"no_limit_text" - Text to display when show_no_limit="yes". (default: Unlimited)', 'cp' ) ?></li>	
								<li><?php _e( '"remaining_text" - Text to show remaining places in the course (default: %d places left). When overriding this attribute use %d where the number will appear. (see example)', 'cp' ) ?></li>									
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_class_size], [course_class_size remaining_text="Only %d places left."]</em></li>				
                            </ul>
                        </td>
                    </tr>	
					<tr>
						<td><?php _e( 'Course Language', 'cp' ) ?></td>
                        <td>
                            <strong>[course_language]</strong> -
                            <span class="description"><?php _e( 'Displays the course language if set.', 'cp' ) ?></span>
                            <p><strong><?php _e( 'Optional Attributes:', 'cp' ) ?></strong></p>
                            <ul class="cp-shortcode-options">
                                <li><?php _e( '"course_id" - ID of the course', 'cp' ) ?></li>
                                <li><?php _e( '"label" - Label to display next to the langague (defaul: Course Language). Set label to "" to hide the label completely.', 'cp' ) ?></li>
                                <li><?php _e( '"label_tag" - HTML element to wrap around label. (defaul: strong)', 'cp' ) ?></li>
                                <li><?php _e( '"label_delimeter" - Delimeter to add after the label. (default: colon :)', 'cp' ) ?></li>																																
                            </ul>

                            <ul class="cp-shortcode-options">
                                <li><?php _e( 'Example:', 'cp' ) ?> <em>[course_language], [course_language label="Delivered in"]</em></li>				
                            </ul>
                        </td>
                    </tr>	
					


                </table>
            </div>
        </div>

    </form>
</div>