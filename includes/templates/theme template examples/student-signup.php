<?php
/**
 * The EXAMPLE Template for the student signup/registration page.
 *
 */
get_header();
?>
<div id="primary" class="site-content">
    <div id="content" role="main">
        <?php
        $form_message_class = '';
        $form_message = '';
        if (!current_user_can('student')) {
            ?>

            <?php
            $student = new Student(0);

            if (isset($_POST['student-settings-submit'])) {

                check_admin_referer('student_signup');

                $student_data = array();
                $form_errors = 0;

                if ($_POST['username'] != '' && $_POST['first_name'] != '' && $_POST['last_name'] != '' && $_POST['email'] != '' && $_POST['password'] != '' && $_POST['password_confirmation'] != '') {

                    if (!username_exists($_POST['username'])) {

                        if (!email_exists($_POST['email'])) {

                            if ($_POST['password'] == $_POST['password_confirmation']) {
                                $student_data['user_pass'] = $_POST['password'];
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
                                    $form_message = __('Account created successfully! You may now <a href="' . wp_login_url() . '">log into your account</a>.', 'cp');
                                    $form_message_class = 'regular';
                                    $email_args['email_type'] = 'student_registration';
                                    $email_args['student_id'] = $student_id;
                                    $email_args['student_email'] = $student_data['user_email'];
                                    $email_args['student_first_name'] = $student_data['first_name'];
                                    $email_args['student_last_name'] = $student_data['last_name'];
                                    coursepress_send_email($email_args);
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
            }
            ?>
            <p class="form-info-<?php echo $form_message_class; ?>"><?php echo $form_message; ?></p>

            <form id="student-settings" name="student-settings" method="post" class="student-settings">

                <label>
                    <?php _e('First Name', 'cp'); ?>:
                    <input type="text" name="first_name" value="" />
                </label>

                <label>
                    <?php _e('Last Name', 'cp'); ?>:
                    <input type="text" name="last_name" value="" />
                </label>

                <label>
                    <?php _e('Username', 'cp'); ?>:
                    <input type="text" name="username" value="" />
                </label>

                <label>
                    <?php _e('E-mail', 'cp'); ?>:
                    <input type="text" name="email" value="" />
                </label>

                <label>
                    <?php _e('Password', 'cp'); ?>:
                    <input type="password" name="password" value="" />
                </label>

                <label class="right">
                    <?php _e('Confirm Password', 'cp'); ?>:
                    <input type="password" name="password_confirmation" value="" />
                </label>

                <label class="full">
                    <a href="<?php echo wp_login_url(); ?>"><?php _e('Already have an Account?', 'cp'); ?></a>
                    <input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="<?php _e('Create an Account', 'cp'); ?>" />
                </label>
                <?php wp_nonce_field('student_signup'); ?>
            </form>
            <?php
        } else {
            //ob_start();
            wp_redirect($this->get_student_dashboard_slug(true));
            exit;
        }
        ?>
    </div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>