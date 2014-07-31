<div class="cp_popup_title"><?php _e('Sign Up', 'cp'); ?></div>
<input type="hidden" name="signup-next-step" value="" />
<p class="cp_popup_required"><?php _e('All fields are required'); ?></p>

<label class="cp_popup_col_2">
    <input type="text" name="student_first_name" id="cp_popup_student_first_name" placeholder="<?php _e('First Name', 'cp'); ?>" />
</label>

<label class="cp_popup_col_2 second-child">
    <input type="text" name="student_last_name" id="cp_popup_student_first_name" placeholder="<?php _e('Last Name', 'cp'); ?>" />
</label>

<label class="cp_popup_col_1">
    <input type="text" name="username" id="cp_popup_username" value="" placeholder="<?php _e('Username', 'cp'); ?>">
</label>

<label class="cp_popup_col_2">
    <input type="text" name="email" id="cp_popup_email" placeholder="<?php _e('E-mail', 'cp'); ?>" />
</label>

<label class="cp_popup_col_2 second-child">
    <input type="text" name="email_confirmation" id="cp_popup_email_confirmation" placeholder="<?php _e('E-mail Confirmation', 'cp'); ?>" />
</label>

<label class="cp_popup_col_2">
    <input type="password" name="password" id="cp_popup_password" placeholder="<?php _e('Password', 'cp'); ?>" />
</label>

<label class="cp_popup_col_2 second-child">
    <input type="password" name="password_confirmation" id="cp_popup_password_confirmation" placeholder="<?php _e('Password Confirmation', 'cp'); ?>" />
</label>

<div class="cp_popup_buttons">
    <label class="cp_popup_col_2">
        <a href="" class="cp_login_step" data-course-id="<?php esc_attr_e(isset($_POST['course_id']) ? $_POST['course_id'] : '' ); ?>"><?php _e('Already have an Account?', 'cp'); ?></a>
    </label>

    <label class="cp_popup_col_2 second-child">
        <button class="apply-button signup"><?php _e('Create Account', 'cp');?></button>
    </label>
</div>