jQuery(document).ready(function() {
    /* Signup */
    jQuery('button.apply-button.signup, .cp_signup_step').live('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        open_popup('signup', jQuery(this).attr('data-course-id'));
    });
    
    /* Login */
    
    jQuery('.cp_login_step').live('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        open_popup('login', jQuery(this).attr('data-course-id'));
    });
   
    jQuery('.cp_popup_close_button').click(function(e) {//.cp_popup_overall, 
        close_popup();
    });

    function open_popup(step, course_id) {
        cp_popup_load_content(step, course_id);
        jQuery("body > div").not(jQuery(".cp_popup_window")).addClass('cp_blur');
        jQuery('.cp_popup_overall').show();
        jQuery('.cp_popup_window').show();
    }

    function close_popup() {
        jQuery("body > div").not(jQuery(".cp_popup_window")).removeClass('cp_blur');
        jQuery('.cp_popup_overall').hide();
        jQuery('.cp_popup_window').hide();
    }

    function cp_popup_load_content(step, course_id) {
        jQuery('.cp_popup_loading').show();
        jQuery('.cp_popup_content').html('');
        jQuery.post(
                cp_vars.admin_ajax_url, {
                    action: 'cp_popup_step',
                    course_id: course_id,
                    step: step
                }
        ).done(function(data, status) {
            if (status == 'success') {
                jQuery('.cp_popup_content').html(data);
                jQuery('.cp_popup_loading').hide();
            } else {
            }
        }).fail(function(data) {
        });
    }
});

jQuery(document).ready(function() {

    jQuery('.cp_popup_window').css({
        position: 'absolute',
        left: (jQuery('#wpcontent').width() - jQuery('.cp_popup_window').outerWidth()) / 2,
        top: (jQuery(window).height() - jQuery('.cp_popup_window').outerHeight()) / 2
    });

    jQuery(window).resize(function() {
        jQuery('.cp_popup_window').css({
            position: 'absolute',
            left: (jQuery(window).width() - jQuery('.cp_popup_window').outerWidth()) / 2,
            top: (jQuery(window).height() - jQuery('.cp_popup_window').outerHeight()) / 2
        });
        jQuery(".cp_popup_overall").height(jQuery(document).height());
    });

    jQuery(".cp_popup_overall").height(jQuery(document).height());
    jQuery(window).resize();
});
