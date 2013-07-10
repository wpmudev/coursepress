<div id="poststuff" class="metabox-holder m-settings">
    <form action='' method='post'>


        <div class="postbox">
            <h3 class='hndle'><span><?php _e('Shortcodes', 'cp') ?></span></h3>
            <div class="inside">
                <p><?php _e('Shortcodes allow you to include dynamic content in posts and pages on your site. Simply type or paste them into your post or page content where you would like them to appear. Optional attributes can be added in a format like <em>[shortcode attr1="value" attr2="value"]</em>. ', 'cp') ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Instructors List', 'cp') ?></th>
                        <td>
                            <strong>[course_instructors]</strong> -
                            <span class="description"><?php _e('Display a list or count of Instructors (gravatar, name and link to profile page)', 'cp') ?></span>
                            <p>
                                <strong><?php _e('Optional Attributes:', 'cp') ?></strong>
                            <ul class="cp-shortcode-options">
                                <li><?php _e('"course_id" - ID of the course instructors are assign to (required if use it outside of a loop)', 'cp') ?></li>
                                <li><?php _e('"count" - If this attribute is used, only number of instructors will be returned without list', 'cp') ?></li>
                                
                                <li><?php _e('Examples:', 'cp') ?> <em>[course_instructors], [course_instructors course_id="5"], [course_instructors count="true"]</em></li>
                            </ul></p>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Course Details', 'cp') ?></th>
                        <td>
                            <strong>[course_details]</strong> -
                            <span class="description"><?php _e('Display additional course information like start date, end date, price etc.', 'cp') ?></span>
                            <p>
                                <strong><?php _e('Optional Attributes:', 'cp') ?></strong>
                            <ul class="cp-shortcode-options">
                                <li><?php _e('"course_id" - ID of the course instructors are assign to (required if use it outside of a loop)', 'cp') ?></li>
                                <li><?php _e('"field" - What fields to display. Possible values: course_start_date, course_end_date, enrollment_start_date, enrollment_end_date, price, button, passcode, class_size and standard post type fields (ID, post_author, post_date, post_content, post_title, post_status, post_name, post_modified etc. )', 'cp') ?></li>
                                
                                <li><?php _e('Examples:', 'cp') ?> <em>[course_instructors field="course_start_date"], [course_instructors field="button" course_id="5"]</em></li>
                            </ul></p>
                            <span class="description"></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Student Dashboard', 'cp') ?></th>
                        <td>
                            <strong>[courses_student_dashboard]</strong> -
                            <span class="description"><?php _e('Display content of the student dashboard including enrolled courses', 'cp') ?></span>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Student Settings', 'cp') ?></th>
                        <td>
                            <strong>[courses_student_settings]</strong> -
                            <span class="description"><?php _e('Display content of the student settings page where they can change username, password etc.', 'cp') ?></span>
                        </td>
                    </tr>
               
                </table>
            </div>
        </div>

    </form>
</div>