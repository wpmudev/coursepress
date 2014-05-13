<?php if (!is_user_logged_in()) { ?>

    <?php
    $form_message_class = '';
    $form_message = '';

    $student = new Student(0);

    if (isset($_POST['student-settings-submit'])) {

        check_admin_referer('student_signup');
        $min_password_length = apply_filters('cp_min_password_length', 6);

        $student_data = array();
        $form_errors = 0;

        do_action('cp_before_signup_validation');

        if ($_POST['username'] != '' && $_POST['first_name'] != '' && $_POST['last_name'] != '' && $_POST['email'] != '' && $_POST['password'] != '' && $_POST['password_confirmation'] != '') {

            if (!username_exists($_POST['username'])) {

                if (!email_exists($_POST['email'])) {

                    if ($_POST['password'] == $_POST['password_confirmation']) {

                        if (!preg_match("#[0-9]+#", $_POST['password']) || !preg_match("#[a-zA-Z]+#", $_POST['password']) || strlen($_POST['password']) < $min_password_length) {
                            $form_message = sprintf(__('Your password must be at least %d characters long and have at least one letter and one number in it.', 'cp'), $min_password_length);
                            $form_message_class = 'red';
                            $form_errors++;
                        } else {

                            if ($_POST['password_confirmation']) {
                                $student_data['user_pass'] = $_POST['password'];
                            } else {
                                $form_message = __("Passwords don't match", 'cp');
                                $form_message_class = 'red';
                                $form_errors++;
                            }
                        }
                    } else {
                        $form_message = __('Passwords don\'t match', 'cp');
                        $form_message_class = 'red';
                        $form_errors++;
                    }

                    $student_data['role'] = 'student';
                    $student_data['user_login'] = $_POST['username'];
                    $student_data['user_email'] = $_POST['email'];
                    $student_data['first_name'] = $_POST['first_name'];
                    $student_data['last_name'] = $_POST['last_name'];

                    if (!is_email($_POST['email'])) {
                        $form_message = __('E-mail address is not valid.', 'cp');
                        $form_message_class = 'red';
                        $form_errors++;
                    }

                    if ($form_errors == 0) {
                        if ($student_id = $student->add_student($student_data) !== 0) {
                            //$form_message = __('Account created successfully! You may now <a href="' . (get_option('use_custom_login_form', 1) ? trailingslashit(site_url() . '/' . $this->get_login_slug()) : wp_login_url()) . '">log into your account</a>.', 'cp');
                            //$form_message_class = 'regular';
                            $email_args['email_type'] = 'student_registration';
                            $email_args['student_id'] = $student_id;
                            $email_args['student_email'] = $student_data['user_email'];
                            $email_args['student_first_name'] = $student_data['first_name'];
                            $email_args['student_last_name'] = $student_data['last_name'];
                            coursepress_send_email($email_args);

                            $creds = array();
                            $creds['user_login'] = $student_data['user_login'];
                            $creds['user_password'] = $student_data['user_pass'];
                            $creds['remember'] = true;
                            $user = wp_signon($creds, false);

                            if (is_wp_error($user)) {
                                $form_message = $user->get_error_message();
                                $form_message_class = 'red';
                            }

                            if (isset($_POST['course_id']) && is_numeric($_POST['course_id'])) {
                                $course = new Course($_POST['course_id']);
                                wp_redirect($course->get_permalink());
                            } else {
                                wp_redirect($this->get_student_dashboard_slug(true));
                            }
                            exit;
                        } else {
                            $form_message = __('An error occured while creating the account. Please check the form and try again.', 'cp');
                            $form_message_class = 'red';
                        }
                    }
                } else {
                    $form_message = __('User with the same e-mail already exists.', 'cp');
                    $form_message_class = 'error';
                }
            } else {
                $form_message = __('Username already exists. Please choose another one.', 'cp');
                $form_message_class = 'red';
            }
        } else {
            $form_message = __('All fields are required.', 'cp');
            $form_message_class = 'red';
        }
    } else {
        $form_message = __('All fields are required.', 'cp');
    }
    ?>
    <p class="form-info-<?php echo apply_filters('signup_form_message_class', $form_message_class); ?>"><?php echo apply_filters('signup_form_message', $form_message); ?></p>

    <?php do_action('cp_before_signup_form'); ?>

    <form id="student-settings" name="student-settings" method="post" class="student-settings">

    <?php do_action('cp_before_all_signup_fields'); ?>

        <input type="hidden" name="course_id" value="<?php esc_attr_e(isset($_GET['course_id']) ? $_GET['course_id'] : ''); ?>" />

        <label>
    <?php _e('First Name', 'cp'); ?>:
            <input type="text" name="first_name" value="<?php echo (isset($_POST['first_name']) ? $_POST['first_name'] : ''); ?>" />
        </label>

    <?php do_action('cp_after_signup_first_name'); ?>

        <label>
    <?php _e('Last Name', 'cp'); ?>:
            <input type="text" name="last_name" value="<?php echo (isset($_POST['last_name']) ? $_POST['last_name'] : ''); ?>" />
        </label>

    <?php do_action('cp_after_signup_last_name'); ?>

        <label>
    <?php _e('Username', 'cp'); ?>:
            <input type="text" name="username" value="<?php echo (isset($_POST['username']) ? $_POST['username'] : ''); ?>" />
        </label>

    <?php do_action('cp_after_signup_username'); ?>

        <label>
    <?php _e('E-mail', 'cp'); ?>:
            <input type="text" name="email" value="<?php echo (isset($_POST['email']) ? $_POST['email'] : ''); ?>" />
        </label>

    <?php do_action('cp_after_signup_email'); ?>

        <label>
    <?php _e('Password', 'cp'); ?>:
            <input type="password" name="password" value="" />
        </label>

    <?php do_action('cp_after_signup_password'); ?>

        <label class="right">
    <?php _e('Confirm Password', 'cp'); ?>:
            <input type="password" name="password_confirmation" value="" />
        </label>

    <?php do_action('after_all_signup_fields'); ?>

        <label class="full">
            <a href="<?php echo (get_option('use_custom_login_form', 1) ? trailingslashit(site_url() . '/' . $this->get_login_slug()) : wp_login_url()); ?>"><?php _e('Already have an Account?', 'cp'); ?></a>
        </label>

        <label class="full-right">
            <input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="<?php _e('Create an Account', 'cp'); ?>" />
        </label>

        <?php do_action('cp_after_submit'); ?>

    <?php wp_nonce_field('student_signup'); ?>
    </form>

    <?php do_action('cp_after_signup_form'); ?>
    <?php
} else {
    if (isset($this)) {
        //ob_start();
        wp_redirect($this->get_student_dashboard_slug(true));
        exit;
    }
}
?>