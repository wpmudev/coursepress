jQuery(document).ready(function($) {
    /* Signup Step */
    $('button.apply-button.signup, .cp_signup_step').live('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        open_popup('signup', $(this).attr('data-course-id'));
    });

    /* Signup Submit Data */
    $('button.apply-button.signup-data').live('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        validate_signup_data();
    });

    /* Login Step */

    $('.cp_login_step').live('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        open_popup('login', $(this).attr('data-course-id'));
    });

    $('.cp_popup_close_button').click(function(e) {//.cp_popup_overall, 
        close_popup();
    });

    function validate_signup_data() {
        var errors = 0;
        var required_errors = 0;
        $(".required").each(function(index) {
            if($(this).val() == ''){
                required_errors++;
                errors++;
                validate_mark_error_field($(this).attr('id'));
            }else{
                validate_mark_no_error_field($(this).attr('id'));
            }
        });
        
        if(required_errors > 0){
            $('.validation_errors').html(cp_vars.message_all_fields_are_required);
        }else{//continue with checking
            var username = $('#cp_popup_username').val();
            if(username.length < 4){
                errors++;
                $('.validation_errors').html(cp_vars.message_username_minimum_length);
            }
        }
    }
    
    function validate_mark_error_field(field){
        $('#'+field).addClass('cp_error_field');
    }
    
    function validate_mark_no_error_field(field){
        $('#'+field).removeClass('cp_error_field');
    }

    function open_popup(step, course_id) {
        cp_popup_load_content(step, course_id);
        $("body > div").not($(".cp_popup_window")).addClass('cp_blur');
        $('.cp_popup_overall').show();
        $('.cp_popup_window').center();
        $('.cp_popup_window').show();

    }

    function close_popup() {
        $("body > div").not($(".cp_popup_window")).removeClass('cp_blur');
        $('.cp_popup_overall').hide();
        $('.cp_popup_window').hide();
    }

    function cp_popup_load_content(step, course_id) {
        $('.cp_popup_loading').show();
        $('.cp_popup_content').html('');
        $.post(
                cp_vars.admin_ajax_url, {
                    action: 'cp_popup_signup',
                    course_id: course_id,
                    step: step
                }
        ).done(function(data, status) {
            if (status == 'success') {

                var response = $.parseJSON($(data).find('response_data').text());
                if (response) {
                    console.log(response);
                    $('.cp_popup_content').html(response.html);
                    $('.cp_popup_window').autoHeight();
                    $('.cp_popup_window').center();
                    $('.cp_popup_loading').hide();
                }

            } else {
            }
        }).fail(function(data) {
        });
    }

    // Extend jQuery with $.center() function to center elements in the middle of the screen
    jQuery.fn.center = function() {
        this.css('position', 'fixed');
        this.css('top', ($(window).height() / 2) - (this.outerHeight() / 2));
        this.css('left', ($(window).width() / 2) - (this.outerWidth() / 2));
        return this;
    }

    // Extend jQuery with $.autoHeight() function to adjust the height of an element to its contents.
    jQuery.fn.autoHeight = function() {
        var new_height = $($(this).find('*').last()).position().top + $($(this).find('*').last()).outerHeight();
        this.css('height', new_height);
        return this;
    }

    // When the window scrolls, make sure we keep the popup in the center.
    $(window).resize(function() {
        $('.cp_popup_window').center();
        $(".cp_popup_overall").height($(document).height());
    });

    $(".cp_popup_overall").height($(document).height());

});
