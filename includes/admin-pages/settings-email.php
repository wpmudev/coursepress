<?php $page = $_GET['page']; ?>

<div id="poststuff" class="metabox-holder m-settings email-settings">
    <form action='' method='post'>

        <input type='hidden' name='page' value='<?php echo $page; ?>' />
        <input type='hidden' name='action' value='updateoptions' />

        <?php
        wp_nonce_field('update-coursepress-options');
        ?>
        <div class="postbox">
            <h3 class="hndle" style='cursor:auto;'><span><?php _e('User Registration E-mail', 'cp'); ?></span></h3>
            <div class="inside">
                <p class="description"><?php _e('Settings for an e-mail student get uppon account registration.', 'cp'); ?></p>
                <table class="form-table">
                    <tbody id="items">
                        <tr>
                            <th><?php _e('From Name', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_registration_from_name" value="<?php esc_attr_e(coursepress_get_registration_from_name()); ?>" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('From E-mail', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_registration_from_email" value="<?php esc_attr_e(coursepress_get_registration_from_email()); ?>" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th><?php _e('E-mail Subject', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_registration_email_subject" value="<?php esc_attr_e(coursepress_get_registration_email_subject()); ?>" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('E-mail Content', 'cp'); ?></th>
                            <td>
                                <p class="description"><?php _e('These codes will be replaced with actual data: STUDENT_FIRST_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS', 'cp');?></p>
                                <?php
                                $args = array("textarea_name" => "option_registration_content_email", "textarea_rows" => 10);
                                wp_editor(stripslashes(coursepress_get_registration_content_email()), "option_registration_content_email", $args);
                                ?>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div><!--/inside-->

        </div><!--/postbox-->


        <div class="postbox">
            <h3 class="hndle" style='cursor:auto;'><span><?php _e('Student Invitation to a Course E-mail', 'cp'); ?></span></h3>
            <div class="inside">
                <p class="description"><?php _e('Settings for an e-mail student get uppon receiving an invitation to a course.', 'cp'); ?></p>
                <table class="form-table">
                    <tbody id="items">
                        <tr>
                            <th><?php _e('From Name', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_invitation_from_name" value="<?php esc_attr_e(coursepress_get_invitation_from_name()); ?>" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('From E-mail', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_invitation_from_email" value="<?php esc_attr_e(coursepress_get_invitation_from_email()); ?>" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('E-mail Subject', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_invitation_email_subject" value="<?php esc_attr_e(coursepress_get_invitation_email_subject()); ?>" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th><?php _e('E-mail Content', 'cp'); ?></th>
                            <td>
                                <p class="description"><?php _e('These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS', 'cp');?></p>
                                <?php
                                $args = array("textarea_name" => "option_invitation_content_email", "textarea_rows" => 10);
                                wp_editor(stripslashes(coursepress_get_invitation_content_email()), "option_invitation_content_email", $args);
                                ?>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div><!--/inside-->

        </div><!--/postbox-->


        <div class="postbox">
            <h3 class="hndle" style='cursor:auto;'><span><?php _e('Student Invitation with Passcode to a Course E-mail', 'cp'); ?></span></h3>
            <div class="inside">
                <p class="description"><?php _e('Settings for an e-mail student get uppon receiving an invitation (with passcode) to a course.', 'cp'); ?></p>
                <table class="form-table">
                    <tbody id="items">
                        <tr>
                            <th><?php _e('From Name', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_invitation_passcode_from_name" value="<?php esc_attr_e(coursepress_get_invitation_passcode_from_name()); ?>" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('From E-mail', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_invitation_passcode_from_email" value="<?php esc_attr_e(coursepress_get_invitation_passcode_from_email()); ?>" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('E-mail Subject', 'cp'); ?></th>
                            <td>
                                <input type="text" name="option_invitation_passcode_email_subject" value="<?php esc_attr_e(coursepress_get_invitation_passcode_email_subject()); ?>" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th><?php _e('E-mail Content', 'cp'); ?></th>
                            <td>
                                <p class="description"><?php _e('These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, PASSCODE', 'cp');?></p>
                                <?php
                                $args = array("textarea_name" => "option_invitation_content_passcode_email", "textarea_rows" => 10);
                                wp_editor(stripslashes(coursepress_get_invitation_content_passcode_email()), "option_invitation_content_passcode_email", $args);
                                ?>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div><!--/inside-->

        </div><!--/postbox-->

        <p class="save-shanges">
            <?php submit_button(__('Save Changes', 'cp')); ?>
        </p>

    </form>
</div><!--/poststuff-->