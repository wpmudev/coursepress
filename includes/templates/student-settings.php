<?php if (current_user_can('student')) { ?>
    <?php
    $form_message_class = '';
    $form_message = '';

    if (isset($_POST['student-settings-submit'])) {
        $student_data = array();
        $student_data['ID'] = get_current_user_id();
        $form_errors = 0;
        if ($_POST['password'] != '') {
            if ($_POST['password'] == $_POST['password_confirmation']) {
                $student_data['user_pass'] = $_POST['password'];
            } else {
                $form_message = __('Passwords don\'t match', 'cp');
                $form_message_class = 'red';
                $form_errors++;
            }
        }

        $student_data['user_email'] = $_POST['email'];
        $student_data['first_name'] = $_POST['first_name'];
        $student_data['last_name'] = $_POST['last_name'];

        if (!is_email($_POST['email'])) {
            $form_message = __('E-mail address is not valid.', 'cp');
            $form_message_class = 'red';
            $form_errors++;
        }

        if ($form_errors == 0) {
            $student = new Student(get_current_user_id());
            if ($student->update_student_data($student_data)) {
                $form_message = __('Settings have been updated successfully.', 'cp');
                $form_message_class = 'regular';
            } else {
                $form_message = __('An error occured while updating. Please check the form and try again.', 'cp');
                $form_message_class = 'red';
            }
        }
    }
    $student = new Student(get_current_user_id());
    ?>
    <p class="form-info-<?php echo $form_message_class; ?>"><?php echo $form_message; ?></p>

    <form id="student-settings" name="student-settings" method="post" class="student-settings">
        <label>
            <?php _e('First Name', 'cp'); ?>:
            <input type="text" name="first_name" value="<?php esc_attr_e($student->user_firstname); ?>" />
        </label><label>
            <?php _e('Last Name', 'cp'); ?>:
            <input type="text" name="last_name" value="<?php esc_attr_e($student->user_lastname); ?>" />
        </label><label>
            <?php _e('E-mail', 'cp'); ?>:
            <input type="text" name="email" value="<?php esc_attr_e($student->user_email); ?>" />
        </label><label>
            <?php _e('Password (empty = don\'t change)', 'cp'); ?>:
            <input type="password" name="password" value="" />
        </label><label class="right">
            <?php _e('Confirm Password', 'cp'); ?>:
            <input type="password" name="password_confirmation" value="" />
        </label><label class="full">
            <input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="<?php _e('Save Changes', 'cp'); ?>" />
        </label>
    </form>
    <?php
} else {
    wp_redirect(wp_login_url());
    exit;
}
?>