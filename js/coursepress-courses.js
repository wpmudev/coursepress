jQuery(document).ready(function() {

    jQuery('.modules_accordion .module-holder-title, #unit-pages .ui-tabs-nav li, .save-unit-button').live('click', function() {
        var current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();
        var active_element = jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').accordion("option", "active");
        jQuery('#active_element').val(active_element);
    });

    jQuery('#add_student_class').live('input', function() {
        return false;
    });

    jQuery('.element_title').live('input', function() {
        jQuery(this).parent().parent().find('.h3-label-left').html(jQuery(this).val());
    });

    jQuery('#unit_name').live('input', function() {
        jQuery('.mp-wrap .mp-tab.active a').html(jQuery(this).val());
    });

    function submit_elements() {

        jQuery("input[name*='radio_input_module_radio_check']:checked").each(function() {
            var vl = jQuery(this).parent().find('.radio_answer').val();
            jQuery(this).closest(".module-content").find('.checked_index').val(vl);
        });

        jQuery("input[name*='radio_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });

        jQuery("input[name*='radio_check']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });

        jQuery("#unit-add").submit();
    }

    jQuery(".unit-control-buttons .save-unit-button").click(function() {
        var unit_page_num = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();
        jQuery("#unit_page_num").val(unit_page_num);

        var unit_pages = jQuery("#unit-pages .ui-tabs-nav li").size() - 2;

        var page_break_to_delete_id = jQuery("#unit-page-1 .module-holder-page_break_module .element_id").val();
        //alert(page_break_to_delete_id);
        if (!isNaN(parseFloat(page_break_to_delete_id)) && isFinite(page_break_to_delete_id)) {
            prepare_module_for_execution(page_break_to_delete_id)
        } else {
            jQuery("#unit-page-1 .module-holder-page_break_module").remove();
        }
        //jQuery('#unit-add').attr('action', jQuery('#unit-add').attr('action') + "&unit_page_num=" + unit_page_num);

        submit_elements();
    });

    jQuery(".unit-control-buttons .button-publish").click(function() {
        //submit_elements();
    });
});

function delete_class_confirmed() {
    return confirm(coursepress_units.delete_class);
}

function deleteClass() {
    if (delete_class_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function withdraw_all_from_class_confirmed() {
    return confirm(coursepress_units.withdraw_class_alert);
}

function withdrawAllFromClass() {
    if (withdraw_all_from_class_confirmed()) {
        return true;
    } else {
        return false;
    }
}

jQuery(function() {

    jQuery("#sortable-units").sortable({
        placeholder: "ui-state-highlight",
        items: "li:not(.static)",
        stop: function(event, ui) {
            update_sortable_indexes();
        }
    });

    jQuery("#sortable-units").disableSelection();


    var current_unit_page = 0;//current selected unit page

    current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();

    jQuery("#unit-page-" + current_unit_page + " .unit-module-list").change(function() {
        jQuery("#unit-page-" + current_unit_page + " .module_description").html(jQuery(this).find(':selected').data('module-description'));
    });

    jQuery("#unit-page-" + current_unit_page + " .module_description").html(jQuery(this).find(':selected').data('module-description'));

});

function update_sortable_indexes() {
    jQuery('.numberCircle').each(function(i, obj) {
        jQuery(this).html(i + 1);
    });

    jQuery('.unit_order').each(function(i, obj) {
        jQuery(this).val(i + 1);
    });

    var positions = new Array();

    jQuery('.unit_id').each(function(i, obj) {
        positions[i] = jQuery(this).val();
    });

    var data = {
        action: 'update_units_positions',
        positions: positions.toString()
    };

    jQuery.post(ajaxurl, data, function(response) {
        //alert(response);
    });

}

/* Native WP media browser for audio module (unit module) */

jQuery(document).ready(function()
{

    jQuery('.remove_module_link').live('click', function() {
        var current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();
        var accordion_elements_count = (jQuery('#unit-page-' + current_unit_page + ' .modules_accordion div.module-holder-title').length);//.modules_accordion').find('.modules_accordion div.module-holder-title').length);

        //alert('Current page: '+current_unit_page+', elements count: '+accordion_elements_count);

        if ((current_unit_page == 1 && accordion_elements_count == 0) || (current_unit_page >= 2 && accordion_elements_count == 1)) {
            jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').show();
        } else {
            jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').hide();
        }
    });

    jQuery('.audio_url_button').live('click', function()
    {
        var target_url_field = jQuery(this).prevAll(".audio_url:first");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });
});

/* Native WP media browser for video module (unit module) */

jQuery(document).ready(function()
{
    jQuery('.video_url_button').live('click', function()
    {
        var target_url_field = jQuery(this).prevAll(".video_url:first");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };

        wp.media.editor.open(this);
        return false;
    });

    jQuery('.course_video_url_button').live('click', function()
    {
        var target_url_field = jQuery(this).prevAll(".course_video_url:first");

        wp.media.string.props = function(props, attachment)
        {
            jQuery(target_url_field).val(props.url);
        }

        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };

        wp.media.editor.open(this);
        return false;
    });

});

/* Native WP media browser for file module (for instructors) */

jQuery(document).ready(function()
{
    jQuery('.file_url_button').live('click', function()
    {

        var target_url_field = jQuery(this).prevAll(".file_url:first");
        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });
});


jQuery(document).ready(function()
{
    jQuery('.insert-media-cp').live('click', function()
    {

        var rand_id = jQuery(this).attr("data-editor");

        wp.media.editor.send.attachment = function(props, attachment)
        {
            tinyMCE.execCommand('mceFocus', false, rand_id);
            var ed = tinyMCE.get(rand_id);
            var range = ed.selection.getRng();
            var image = ed.getDoc().createElement("img");

            var image_width = eval('attachment.sizes' + '.' + props.size + '.' + 'width');
            var image_height = eval('attachment.sizes' + '.' + props.size + '.' + 'height');

            image.setAttribute('class', 'align' + props.align + ' size-' + props.size + ' wp-image-' + rand_id);
            image.src = attachment.url;
            image.alt = attachment.alt;
            image.width = image_width;
            image.height = image_height;
            range.insertNode(image);
        };

        wp.media.editor.open(this);

        return false;
    });

    //tinyMCE.activeEditor.selection.moveToBookmark(bm);
});


jQuery.urlParam = function(name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null) {
        return null;
    }
    else {
        return results[1] || 0;
    }
}

// Detect key changes in the wp_editor
var active_editor;
function cp_editor_key_down(ed, page, tab) {
    $ = jQuery;

    if (page == 'coursepress_page_course_details') {
        if (tab == '' || tab == 'overview') {

            // Mark as dirty when wp_editor content changes on 'Course Setup' page.
            $('#' + ed.id).parents('.course-section').addClass('dirty');
            if ($($('#' + ed.id).parents('.course-section.step').children('.status.saved')[0]).hasClass('saved')) {
                $('#' + ed.id).parents('.course-section.step').find('input.button.update').css('display', 'inline-block');
            }

            active_editor = ed.id;

        }
    }

}

// Detect mouse movement in the wp_editor
function cp_editor_mouse_move(ed, event) {
}


function set_update_progress(step, value) {

    $ = jQuery;
    $('input[name="meta_course_setup_progress[' + step + ']"]').val(value);

}

function get_meta_course_setup_progress() {
    var meta_course_setup_progress = {
        'step-1': $('input[name="meta_course_setup_progress[step-1]"]').val(),
        'step-2': $('input[name="meta_course_setup_progress[step-2]"]').val(),
        'step-3': $('input[name="meta_course_setup_progress[step-3]"]').val(),
        'step-4': $('input[name="meta_course_setup_progress[step-4]"]').val(),
        'step-5': $('input[name="meta_course_setup_progress[step-5]"]').val(),
        'step-6': $('input[name="meta_course_setup_progress[step-6]"]').val(),
    }

    return meta_course_setup_progress;
}

function autosave_course_setup_done(data, status, step, statusElement, nextAction) {
    if (typeof (nextAction) === 'undefined')
        nextAction = false;
    if (status == 'success') {

        var response = $.parseJSON($(data).find('response_data').text());
        // console.log(response);
        // Apply a new nonce when returning
        if (response && response.success) {
            $('#course-ajax-check').data('nonce', response.nonce);
            $('#course-ajax-check').data('cap', response.cap);
            $('#course-ajax-check').data('id', response.course_id);
            $('[name=course_id]').val(response.course_id);
			if( response.mp_product_id ) {
				$('[name=meta_mp_product_id]').val(response.mp_product_id);
			}
			
            // Add user as instructor
            if (step == 'step-1' && response.instructor) {
                $.post(
                        'admin-ajax.php', {
                            action: 'add_course_instructor',
                            user_id: response.instructor,
                            course_id: response.course_id,
                        }
                ).done(function(data, status) {

                    var instructor_id = response.instructor;
                    var response2 = $.parseJSON($(data).find('response_data').text());
                    var response_type = $($.parseHTML(response2.content));

                    if ($("#instructor_holder_" + instructor_id).length == 0 && response2.instructor_added) {
                        $('.instructor-avatar-holder.empty').hide();
                        $('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor( ' + instructor_id + ' );"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' + response2.instructor_gravatar + '<span class="instructor-name">' + response2.instructor_name + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
                    }


                    //window.location = $('form#course-add').attr('action')  + '&course_id=' + response.course_id;

                });
                // return;
            }

            // Else, toggle back.	
        } else {
            $(statusElement).removeClass('progress');
            $(statusElement).addClass('invalid');
            set_update_progress(step, 'invalid');
            return;
        }

        $($('.' + step + '.dirty')[0]).removeClass('dirty')
        $(statusElement).removeClass('progress');

        var is_paid = $('[name=meta_paid_course]').is(':checked') ? true : false;
        var has_gateway = $($('.step-6 .course-enable-gateways')[0]).hasClass('gateway-active');

        // Different logic required here for last step
        if (step == 'step-6') {
            // Paid product
            if (is_paid)
            {
                // Gateway is setup and next action is set
                if (has_gateway && 'unit_setup' == nextAction) {
                    $course_id = $('[name=course_id]').val();
                    $admin_url = $('[name=admin_url]').val();
                    window.location = $admin_url + '&tab=units&course_id=' + $course_id;
                    // Gateway is set, but we forgot to tell the 'done' button what to do
                } else if (has_gateway) {
                    $(statusElement).addClass('saved');
                    set_update_progress(step, 'saved');
                    // Gateway is not set	
                } else {
                    // alert(coursepress_units.setup_gateway);
                    $(statusElement).addClass('attention');
                    set_update_progress(step, 'attention');
                }
            } else {
                $(statusElement).addClass('saved');
                set_update_progress(step, 'saved');
                $course_id = $('[name=course_id]').val();
                $admin_url = $('[name=admin_url]').val();
				
				if( 'unit_setup' == nextAction ) {
	                window.location = $admin_url + '&tab=units&course_id=' + $course_id;					
				}

            }
            // Steps 1 - 5	
        } else {
            $(statusElement).addClass('saved');
        }

        $('.course-section.step input.button.update').css('display', 'none');
    } else {
        $(statusElement).removeClass('progress');
        $(statusElement).addClass('invalid');
        set_update_progress(step, 'invalid');
    }
}

/** Prepare AJAX post vars */
function step_1_update(attr) {
    var theStatus = attr['status'];
    var initialVars = attr['initialVars'];

    var content = '';
    if (tinyMCE.get('course_excerpt')) {
        content = tinyMCE.get('course_excerpt').getContent();
    } else {
        content = $('[name=course_excerpt]').val();
    }

    var _thumbnail_id = '';
    if ($('[name=_thumbnail_id]')) {
        _thumbnail_id = $('[name=_thumbnail_id]').val()
    }

    return {
        // Don't remove
        action: initialVars['action'],
        course_id: initialVars['course_id'],
        course_name: initialVars['course_name'],
        course_nonce: initialVars['course_nonce'],
        required_cap: initialVars['required_cap'],
        uid: initialVars['uid'],
        // Alter as required
        course_excerpt: content,
        meta_featured_url: $('[name=meta_featured_url]').val(),
        _thumbnail_id: _thumbnail_id,
        meta_course_category: $('[name=meta_course_category]').val(),
        meta_course_language: $('[name=meta_course_language]').val(),
        // Don't remove
        meta_course_setup_progress: initialVars['meta_course_setup_progress'],
        meta_course_setup_marker: 'step-2',
    }
}

function step_2_update(attr) {
    var theStatus = attr['status'];
    var initialVars = attr['initialVars'];

    var content = '';
    if (tinyMCE.get('course_description')) {
        content = tinyMCE.get('course_description').getContent();
    } else {
        content = $('[name=course_description]').val();
    }

    //var show_boxes = {};
    //var preview_boxes = {};

    var show_unit_boxes = {};
    var preview_unit_boxes = {};

    var show_page_boxes = {};
    var preview_page_boxes = {};

    $("input[name^=meta_show_unit]").each(function() {
        var unit_id = $(this).attr('data-id');

        show_unit_boxes[ unit_id ] = $(sanitize_checkbox($("input[name=meta_show_unit\\[" + unit_id + "\\]]"))).val();
        preview_unit_boxes[ unit_id ] = $(sanitize_checkbox($("input[name=meta_preview_unit\\[" + unit_id + "\\]]"))).val();

    });

    $("input[name^=meta_show_page]").each(function() {
        var page_id = $(this).attr('data-id');

        show_page_boxes[ page_id ] = $(sanitize_checkbox($("input[name=meta_show_page\\[" + page_id + "\\]]"))).val();
        preview_page_boxes[ page_id ] = $(sanitize_checkbox($("input[name=meta_preview_page\\[" + page_id + "\\]]"))).val();

    });

    /*
     
     $("input[name^=module_element]").each(function() {
     var mod_id = $(this).val();
     
     show_boxes[ mod_id ] = $(sanitize_checkbox($("input[name=meta_show_module\\[" + mod_id + "\\]]"))).val();
     preview_boxes[ mod_id ] = $(sanitize_checkbox($("input[name=meta_preview_module\\[" + mod_id + "\\]]"))).val();
     
     });
     */

    return {
        // Don't remove
        action: initialVars['action'],
        course_id: initialVars['course_id'],
        course_name: initialVars['course_name'],
        course_nonce: initialVars['course_nonce'],
        required_cap: initialVars['required_cap'],
        // Alter as required
        meta_course_video_url: $('[name=meta_course_video_url]').val(),
        course_description: content,
        meta_course_structure_options: $('[name=meta_course_structure_options]').is(':checked') ? 'on' : 'off',
        meta_course_structure_time_display: $('[name=meta_course_structure_time_display]').is(':checked') ? 'on' : 'off',
        meta_show_unit_boxes: show_unit_boxes,
        meta_preview_unit_boxes: preview_unit_boxes,
        meta_show_page_boxes: show_page_boxes,
        meta_preview_page_boxes: preview_page_boxes,
        //meta_show_module: show_boxes,
        //meta_preview_module: preview_boxes,
        // Don't remove
        meta_course_setup_progress: initialVars['meta_course_setup_progress'],
        meta_course_setup_marker: 'step-3',
    }
}

function step_3_update(attr) {
    var theStatus = attr['status'];
    var initialVars = attr['initialVars'];

    var instructors = $("input[name^=instructor]").map(function() {
        return $(this).val();
    }).get();
    if ($(instructors).length == 0) {
        instructors = 0;
    }

    return {
        // Don't remove
        action: initialVars['action'],
        course_id: initialVars['course_id'],
        course_name: initialVars['course_name'],
        course_nonce: initialVars['course_nonce'],
        required_cap: initialVars['required_cap'],
        // Alter as required
        instructor: instructors,
        // Don't remove
        meta_course_setup_progress: initialVars['meta_course_setup_progress'],
        meta_course_setup_marker: 'step-4',
    }
}

function step_4_update(attr) {
    var theStatus = attr['status'];
    var initialVars = attr['initialVars'];

    return {
        // Don't remove
        action: initialVars['action'],
        course_id: initialVars['course_id'],
        course_name: initialVars['course_name'],
        course_nonce: initialVars['course_nonce'],
        required_cap: initialVars['required_cap'],
        // Alter as required
        meta_open_ended_course: $('[name=meta_open_ended_course]').is(':checked') ? 'on' : 'off',
        meta_course_start_date: $('[name=meta_course_start_date]').val(),
        meta_course_end_date: $('[name=meta_course_end_date]').val(),
        meta_open_ended_enrollment: $('[name=meta_open_ended_enrollment]').is(':checked') ? 'on' : 'off',
        meta_enrollment_start_date: $('[name=meta_enrollment_start_date]').val(),
        meta_enrollment_end_date: $('[name=meta_enrollment_end_date]').val(),
        // Don't remove
        meta_course_setup_progress: initialVars['meta_course_setup_progress'],
        meta_course_setup_marker: 'step-5',
    }
}

function step_5_update(attr) {
    var theStatus = attr['status'];
    var initialVars = attr['initialVars'];

    return {
        // Don't remove
        action: initialVars['action'],
        course_id: initialVars['course_id'],
        course_name: initialVars['course_name'],
        course_nonce: initialVars['course_nonce'],
        required_cap: initialVars['required_cap'],
        // Alter as required
        meta_limit_class_size: $('[name=meta_limit_class_size]').is(':checked') ? 'on' : 'off',
        meta_class_size: $('[name=meta_class_size]').val(),
        meta_allow_course_discussion: $('[name=meta_allow_course_discussion]').is(':checked') ? 'on' : 'off',
        meta_allow_workbook_page: $('[name=meta_allow_workbook_page]').is(':checked') ? 'on' : 'off',
        // Don't remove
        meta_course_setup_progress: initialVars['meta_course_setup_progress'],
        meta_course_setup_marker: 'step-6',
    }
}

function step_6_update(attr) {
    var theStatus = attr['status'];
    var initialVars = attr['initialVars'];

    var passcode_val = false;
    var prerequisite_val = false;

    switch ($('[name=meta_enroll_type]').val()) {
        case 'passcode':
            passcode_val = $('[name=meta_passcode]').val();
            break;
        case 'prerequisite':
            prerequisite_val = $('[name=meta_prerequisite]').val();
            break;
    }

    return {
        // Don't remove
        action: initialVars['action'],
        course_id: initialVars['course_id'],
        course_name: initialVars['course_name'],
        course_nonce: initialVars['course_nonce'],
        required_cap: initialVars['required_cap'],
        // Alter as required
        meta_enroll_type: $('[name=meta_enroll_type]').val(),
        meta_prerequisite: prerequisite_val,
        meta_passcode: passcode_val,
        meta_paid_course: $('[name=meta_paid_course]').is(':checked') ? 'on' : 'off',
        meta_auto_sku: $('[name=meta_auto_sku]').is(':checked') ? 'on' : 'off',
        mp_sku: $('[name=mp_sku]').val(),
        mp_is_sale: $('[name=mp_is_sale]').is(':checked') ? '1' : '0',
        mp_price: $('[name=mp_price]').val(),
        mp_sale_price: $('[name=mp_sale_price]').val(),
        meta_mp_product_id: $('[name=meta_mp_product_id]').val(),
        //meta_allow_workbook_page: $('[name=meta_allow_workbook_page]').is(':checked') ? 'on' : 'off',
        // Don't remove
        meta_course_setup_progress: initialVars['meta_course_setup_progress'],
        meta_course_setup_marker: initialVars['meta_course_setup_marker'],
    }
}

function clearCourseErrorMessages() {
    $('span.error').remove();
}

function validateCourseFields(step) {
    var valid = true;

    $ = jQuery;

    clearCourseErrorMessages();

    switch (step) {

        case 1:
        case '1':
            if ($('[name=course_name]').val() == "") {
                $('[for=course_name]').parent().append('<span class="error">' + coursepress_units.required_course_name + '</span>');
                valid = false;
            }

            var content = '';
            if (tinyMCE.get('course_excerpt')) {
                content = tinyMCE.get('course_excerpt').getContent();
            } else {
                content = $('[name=course_excerpt]').val();
            }

            break;

        case 2:
        case '2':
            var content = '';
            if (tinyMCE.get('course_description')) {
                content = tinyMCE.get('course_description').getContent();
            } else {
                content = $('[name=course_description]').val();
            }
            if (content == '') {
                $('[for=course_description]').parent().append('<span class="error">' + coursepress_units.required_course_description + '</span>');
                valid = false;
            }
            break;

        case 3:
        case '3':
            break;

        case 4:
        case '4':

            if ($('[name=meta_course_start_date]').val() == "") {
                $('[name=meta_course_start_date]').parents('.date-range').parent().append('<span class="error">' + coursepress_units.required_course_start + '<br /></span>');
                valid = false;
            }

            if (!$('[name=meta_open_ended_course]').is(':checked')) {
                if ($('[name=meta_course_end_date]').val() == "") {
                    $('[name=meta_course_end_date]').parents('.date-range').parent().append('<span class="error">' + coursepress_units.required_course_end + '</span>');
                    valid = false;
                }
            }

            if (!$('[name=meta_open_ended_enrollment]').is(':checked')) {
                if ($('[name=meta_enrollment_start_date]').val() == "") {
                    $('[name=meta_enrollment_start_date]').parents('.date-range').parent().append('<span class="error">' + coursepress_units.required_enrollment_start + '<br /></span>');
                    valid = false;
                }
                if ($('[name=meta_enrollment_end_date]').val() == "") {
                    $('[name=meta_enrollment_end_date]').parents('.date-range').parent().append('<span class="error">' + coursepress_units.required_enrollment_end + '<br /></span>');
                    valid = false;
                }
            }

            break;

        case 5:
        case '5':
            if ($('[name=meta_limit_class_size]').is(':checked')) {
                if ($('[name=meta_class_size]').val() == "" || $('[name=meta_class_size]').val() == "0" || $('[name=meta_class_size]').val() == 0) {
                    $('[for=meta_class-size]').parent().append('<span class="error">' + coursepress_units.required_course_class_size + '</span>');
                    valid = false;
                }
            }

            break;

        case 6:
        case '6':

            var has_gateway = $($('.step-6 .course-enable-gateways')[0]).hasClass('gateway-active');
            if ($('[name=meta_enroll_type]').val() == 'passcode') {
                if ($('[name=meta_passcode]').val() == "") {
                    $('[for=meta_enroll_type]').parent().append('<span class="error">' + coursepress_units.required_course_passcode + '</span>');
                    valid = false;
                }
            }

            if ($('[name=meta_paid_course]').is(':checked')) {

                if ($('[name=mp_price]').val() == "") {
                    $('[name=mp_price]').parents('.course-price').append('<span class="error">' + coursepress_units.required_price + '</span>');
                    valid = false;
                }

                if (!has_gateway) {
                    $('.course-enable-gateways').append('<div><span class="error">' + coursepress_units.required_gateway + '</span></div>');
                    valid = false;
                }

                if ($('[name=mp_is_sale]').is(':checked')) {
                    if ($('[name=mp_sale_price]').val() == "") {
                        $('.course-sale-price').append('<span class="error">' + coursepress_units.required_sale_price + '</span>');
                        valid = false;
                    }
                }
            }

            break;

    }


    if (!valid) {
        alert(coursepress_units.section_error);
    }

    return valid;
}

function courseAutoUpdate(step, nextAction) {
    if (typeof (nextAction) === 'undefined')
        nextAction = false
    $ = jQuery;

    clearCourseErrorMessages();

    var theStatus = $($('.course-section.step-' + step + ' .course-section-title h3')[0]).siblings('.status')[0];

    var statusNice = '';
    if ($(theStatus).hasClass('saved')) {
        statusNice = 'saved';
    }
    if ($(theStatus).hasClass('invalid')) {
        statusNice = 'invalid';
    }
    if ($(theStatus).hasClass('attention')) {
        statusNice = 'attention';
    }
    $(theStatus).removeClass('saved');
    $(theStatus).removeClass('invalid');
    $(theStatus).removeClass('attention');

    var dirty = $('.step-' + step + '.dirty')[0];
    // Step 5 doesn't have anything that MUST be set, so override.
    if (!dirty && step == 5) {
        dirty = true;
    }

    if (dirty || nextAction == 'unit_setup') {
        $(theStatus).addClass('progress');

        // Course ID
        var course_id = $('[name=course_id]').val();
        if (!course_id) {
            course_id = $.urlParam('course_id');
            $('[name=course_id]').val(course_id);
        }

        // Setup course progress markers and statuses		
        set_update_progress('step-' + step, 'saved');
        var meta_course_setup_progress = get_meta_course_setup_progress();

        var course_nonce = $('#course-ajax-check').data('nonce');
        var required_cap = $('#course-ajax-check').data('cap');
        var uid = $('#course-ajax-check').data('uid');

        var initial_vars = {
            action: 'autoupdate_course_settings',
            course_id: course_id,
            course_name: $('[name=course_name]').val(),
            course_nonce: course_nonce,
            required_cap: required_cap,
            uid: uid,
            meta_course_setup_progress: meta_course_setup_progress,
            meta_course_setup_marker: 'step-' + step,
        }
        // console.log( initial_vars );
        var func = 'step_' + step + '_update';
        // Get the AJAX post vars from step_[x]_update();
        var post_vars = window[func]({status: theStatus, initialVars: initial_vars});

        // AJAX CALL
        $.post(
                'admin-ajax.php', post_vars
                ).done(function(data, status) {
            // Handle return
            autosave_course_setup_done(data, status, 'step-' + step, theStatus, nextAction);
        }).fail(function(data) {
        });

    } else {
        $(theStatus).addClass(statusNice);
    }
}

function sanitize_checkbox(checkbox) {
    $ = jQuery;

    if ($(checkbox).attr('type') == 'checkbox') {
        if ($(checkbox).attr('checked')) {
            $(checkbox).val('on');
        } else {
            $(checkbox).val('off');
        }
    }

    return checkbox;
}

function mark_dirty(element) {
    $ = jQuery;

    // Mark as dirty
    var parent_section = $(element).parents('.course-section.step')[0];
    if (parent_section) {
        if (!$(parent_section).hasClass('dirty')) {
            $(parent_section).addClass('dirty');
        }
    }

    if ($($(element).parents('.course-section.step').find('.course-section-title .status')[0]).hasClass('saved')) {
        $(element).parents('.course-section.step').find('input.button.update').css('display', 'inline-block');
    }


}

/** Handle Course Setup Wizard */
jQuery(document).ready(function($) {


    $(window).bind('tb_unload', function(e) {
        if ($(e).parents('.step-6')) {
            $($(e).parents('.course-section.step')[0]).addClass('dirty');

            var statusElement = $($('.course-section.step-6 .course-section-title h3')[0]).siblings('.status')[0];

            //Does course have an active gateway now?
            $(statusElement).addClass('progress');
            $.post(
                    'admin-ajax.php', {
                        action: 'course_has_gateway',
                    }
            ).done(function(data, status) {
                if (status == 'success') {
                    var step = 6;
                    var response = $.parseJSON($(data).find('response_data').text());
                    if (response.has_gateway) {
                        $($('.step-6 .course-enable-gateways')[0]).addClass('gateway-active');
                        $('.step-6 .button-edit-gateways').css('display', 'inline-block');
                        $('.step-6 .button-incomplete-gateways').css('display', 'none');
                        $(statusElement).removeClass('progress');
                        $(statusElement).removeClass('attention');
                        $(statusElement).removeClass('invalid');
                        $(statusElement).addClass('saved');
                        set_update_progress(step, 'saved');
                    } else {
                        $($('.step-6 .course-enable-gateways')[0]).removeClass('gateway-active');
                        $('.step-6 .button-edit-gateways').css('display', 'none');
                        $('.step-6 .button-incomplete-gateways').css('display', 'inline-block');
                        $(statusElement).removeClass('progress');
                        $(statusElement).removeClass('saved');
                        $(statusElement).removeClass('invalid');
                        $(statusElement).addClass('attention');
                        set_update_progress(step, 'attention');
                    }
                    // Update step-6
                    courseAutoUpdate(6);
                }
            });
        }
    });


    /** If a section is not market as saved, automatically mark it as dirty. */
    $.each($('.course-section.step'), function(index, value) {
        if (!$($($('.course-section.step')[index]).find('.status')[0]).hasClass('saved')) {
            $($('.course-section.step')[index]).addClass('dirty')
        }
    });

    /** Done course setup. */
    $('.course-section.step input.done').click(function(e) {
        var step = 6;
        if (validateCourseFields(step)) {
            courseAutoUpdate(step, 'unit_setup');
        }
    });

    /** Inline step update. */
    $('.course-section.step input.update').click(function(e) {
        var course_section = $(this).parents('.course-section.step')[0];
        var step = $(course_section).attr('class').match(/step-\d+/)[0].replace(/^\D+/g, '');
        if (validateCourseFields(step)) {
            courseAutoUpdate(step);
        }
    });

    /** Proceed to next step. */
    $('.course-section.step input.next').click(function(e) {

        /**
         * Get the current step we're on. 
         *
         * Looks for <div class="course-section step step-[x]"> and extracts the number.
         **/
        var course_section = $(this).parents('.course-section.step')[0];
        var step = $(course_section).attr('class').match(/step-\d+/)[0].replace(/^\D+/g, '');

        if (validateCourseFields(step)) {

            // Next section
            var nextStep = parseInt(step) + 1;

            // Attempt to get the next section.
            var nextSection = $(this).parents('.course-details .course-section').siblings('.step-' + nextStep)[0];

            // If next section exists
            if (nextSection) {
                // There is a 'next section'. What do you want to do with it?
                var newTop = $('.step-' + step).position().top + 130;

                // Jump first, then animate		
                $(document).scrollTop(newTop);

                $(nextSection).children('.course-form').slideDown(500);
                $(nextSection).children('.course-section-title').animate({backgroundColor: '#0091cd'}, 500);
                $(nextSection).children('.course-section-title').animate({color: '#FFFFFF'}, 500);
                $(this).parents('.course-form').slideUp(500);
                $(this).parents('.course-section').children('.course-section-title').animate({backgroundColor: '#F1F1F1'}, 500);
                $(this).parents('.course-section').children('.course-section-title').animate({color: '#222'}, 500);

                $(nextSection).addClass('active');
                $(this).parents('.course-section').removeClass('active');

                /* Time to call some Ajax */
                courseAutoUpdate(step);

            } else {
                // There is no 'next sections'. Now what?
            }
        }
    });

    /** Return to previous step. */
    $('.course-section.step input.prev').click(function(e) {

        /**
         * Get the current step we're on. 
         *
         * Looks for <div class="course-section step step-[x]"> and extracts the number.
         **/
        var step = $($(this).parents('.course-section.step')[0]).attr('class').match(/step-\d+/)[0].replace(/^\D+/g, '');
        if (validateCourseFields(step)) {
            // Previous section
            var prevStep = parseInt(step) - 1;

            // Attempt to get the previous section.
            var prevSection = $(this).parents('.course-details .course-section').siblings('.step-' + prevStep)[0];

            // If previous section exists
            if (prevSection) {
                // There is a 'previous section'. What do you want to do with it?
                var newTop = $('.step-' + prevStep).offset().top - 50;
                $(prevSection).children('.course-form').slideDown(500);
                $(prevSection).children('.course-section-title').animate({backgroundColor: '#0091cd'}, 500);
                $(prevSection).children('.course-section-title').animate({color: '#FFFFFF'}, 500);
                $(this).parents('.course-form').slideUp(500);
                $(this).parents('.course-section').children('.course-section-title').animate({backgroundColor: '#F1F1F1'}, 500);
                $(this).parents('.course-section').children('.course-section-title').animate({color: '#222'}, 500);

                // Animate first then jump
                $(document).scrollTop(newTop);
                $(prevSection).addClass('active');
                $(this).parents('.course-section').removeClass('active');

                /* Time to call some Ajax */
                courseAutoUpdate(step);

            } else {
                // There is no 'previous sections'. Now what?
            }
        }
    });

    $('.course-section.step .course-section-title h3').click(function(e) {

        // Get current "active" step
        var activeElement = $('.course-section.step.active')[0];
        var activeStep = $(activeElement).attr('class').match(/step-\d+/)[0].replace(/^\D+/g, '');

        var thisElement = $(this).parents('.course-section.step')[0];
        var thisElementFormVisible = $(thisElement).children('.course-form').is(':visible');
        var thisStep = $(thisElement).attr('class').match(/step-\d+/)[0].replace(/^\D+/g, '');

        var thisStatus = $(this).siblings('.status')[0];

        // Only move to a saved step or a previous step (asuming that it has to be saved)
        if ($(thisStatus).hasClass('saved') || $(thisStatus).hasClass('attention') || thisStep < activeStep) {

            // There is a 'previous section'. What do you want to do with it?
            if (thisStep < activeStep) {
                var newTop = $(thisElement).position().top + 130;
            } else if (thisStep != 1) {
                var step = thisStep + 1;
                var newTop = $(thisElement).prev('.step').offset().top + 20;
            }

            if (!thisElementFormVisible) {
                $(thisElement).children('.course-form').slideDown(500);
                $(thisElement).children('.course-section-title').animate({backgroundColor: '#0091cd'}, 500);
                $(thisElement).children('.course-section-title').animate({color: '#FFFFFF'}, 500);
                if (thisStep != activeStep) {
                    $(activeElement).children('.course-form').slideUp(500);
                    $(activeElement).children('.course-section-title').animate({backgroundColor: '#F1F1F1'}, 500);
                    $(activeElement).children('.course-section-title').animate({color: '#222'}, 500);
                    // Animate first then jump
                    $(document).scrollTop(newTop);
                }
            } else {
                $(activeElement).children('.course-form').slideUp(500);
                $(activeElement).children('.course-section-title').animate({backgroundColor: '#0091cd'}, 500);
                $(activeElement).children('.course-section-title').animate({color: '#222'}, 500);
            }

            $(activeElement).removeClass('active');
            $(thisElement).addClass('active');

        } else {
            $($(this).parent()).effect('shake', {distance: 10}, 100);
        }
    });

    $('#invite-instructor-trigger').click(function() {

        // Course ID
        var course_id = $('[name=course_id]').val();
        if (!course_id) {
            course_id = $.urlParam('course_id');
            $('[name=course_id]').val(course_id);
        }


        $.post(
                'admin-ajax.php', {
                    action: 'send_instructor_invite',
                    first_name: $('[name=invite_instructor_first_name]').val(),
                    last_name: $('[name=invite_instructor_last_name]').val(),
                    email: $('[name=invite_instructor_email]').val(),
                    course_id: course_id,
                }
        ).done(function(data, status) {
            // Handle return
            if (status == 'success') {

                var response = $.parseJSON($(data).find('response_data').text());
                var response_type = $($.parseHTML(response.content));

                if ($(response_type).hasClass('status-success')) {

                    var remove_button = '';
                    if (response.capability) {
                        remove_button = '<div class="instructor-remove"><a href="javascript:removePendingInstructor(\'' + response.data.code + '\', ' + course_id + ' );"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>';
                    }

                    var content = '<div class="instructor-avatar-holder pending" id="' + response.data.code + '">' +
                            '<div class="instructor-status">PENDING</div>' +
                            remove_button +
                            '<img class="avatar avatar-80 photo" width="80" height="80" src="http://www.gravatar.com/avatar/' + CryptoJS.MD5(response.data.email) + '" alt="admin">' +
                            '<span class="instructor-name">' + response.data.first_name + ' ' + response.data.last_name + '</span>' +
                            '</div>';

                    $('#instructors-info').append(content);

                    $('[name=invite_instructor_first_name]').val('');
                    $('[name=invite_instructor_last_name]').val('');
                    $('[name=invite_instructor_email]').val('');
                }

                if ($('#invite-message')) {
                    $('#invite-message').remove()
                }
                ;
                $('div.instructor-invite .submit-message').append('<div id="invite-message" style="display:none;">' + response.content + '</div>')
                // Popup Message
                $('#invite-message').show(function() {
                    $(this).fadeOut(3000);
                });
                $('[name=invite_instructor_first_name]').trigger('focus');

            } else {
            }
        }).fail(function(data) {
        });

    });


    // Submit Invite on 'Return/Enter' 
    $('.instructor-invite input').keypress(function(event) {
        if (event.which == 13) {
            switch ($(this).attr('name')) {

                case "invite_instructor_first_name":
                    $('[name=invite_instructor_last_name]').trigger('focus');
                    break;
                case "invite_instructor_last_name":
                    $('[name=invite_instructor_email]').trigger('focus');
                    break;
                case "invite_instructor_email":
                case "invite_instructor_trigger":
                    $('#invite-instructor-trigger').trigger('click');
                    break;
            }
            event.preventDefault();
        }
    });

    $('.date').click(function(event) {
        if (!$(this).parents('div').hasClass('disabled')) {
            $(this).find('.dateinput').datepicker("show");
        }
    });

    $('.course-section .featured_url_button').click(function() {
        // Mark as dirty
        mark_dirty(this);
    });
    $('.course-section .course_video_url_button').click(function() {
        // Mark as dirty
        mark_dirty(this);
    });
    $('.course-form textarea').change(function() {
        // Mark as dirty		
        mark_dirty(this);
    });
    $('.course-form select').change(function() {
        // Mark as dirty		
        mark_dirty(this);
    });


    $('#add-instructor-trigger').click(function() {

        // Course ID
        var course_id = $('[name=course_id]').val();
        if (!course_id) {
            course_id = $.urlParam('course_id');
            $('[name=course_id]').val(course_id);
        }

        var instructor_id = $('#instructors option:selected').val();

        // Mark as dirty
        mark_dirty(this);

        $.post(
                'admin-ajax.php', {
                    action: 'add_course_instructor',
                    user_id: instructor_id,
                    course_id: course_id,
                }
        ).done(function(data, status) {
            // Handle return
            if (status == 'success') {

                var response = $.parseJSON($(data).find('response_data').text());
                var response_type = $($.parseHTML(response.content));

                if ($("#instructor_holder_" + instructor_id).length == 0 && response.instructor_added) {
                    $('.instructor-avatar-holder.empty').hide();
                    $('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor( ' + instructor_id + ' );"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery('#instructors option:selected').text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
                } else {
                    alert(response.reason);
                }

            } else {
            }
        }).fail(function(data) {
        });


    });


    $('.course-form input').keypress(function(event) {
        $(this).change();
    });
    $('.course-form textarea').keypress(function(event) {
        $(this).change();
    });

    /** Mark "dirty" content */
    $('.course-form input').change(function() {
        mark_dirty(this);

        if ($(this).attr('type') == 'checkbox') {
            if ($(this).attr('checked')) {
                $(this).val('on');
            } else {
                $(this).val('off');
            }
        }


    });



});

// Popup that shows upon 'Course Setup' completed or when 0 units are found.
jQuery(document).ready(function($) {
    var unit_count = $('[name="unit_count"]').val();

    if (unit_count == 0) {
        var content = '<div class="update orange top-right">' + coursepress_units.unit_setup_prompt + '<i class="fa fa-times-circle" /></div>';

        $('#wpbody-content').append(content);
        $('.update.orange.top-right').css({
            position: 'absolute',
            top: '25px',
            right: '35px',
            'background-color': '#fff',
            'border-left': '4px solid #ec8c35',
            'box-shadow': '0 1px 1px 0 rgba(0, 0, 0, 0.1)',
            padding: '15px 25px 15px 15px',
        });
        $('.update.orange.top-right .fa-times-circle').css({
            'font-size': '20px',
            position: 'absolute',
            top: '10px',
            right: '10px',
            cursor: 'pointer',
        }).click(function(event) {
            $(this).parent().remove();
        });
    }
});

jQuery(document).ready(function($) {

    $('#marketpressprompt').click(function(event) {
        $('#marketpressprompt-box').toggle();
    });
	
	$('[name="meta_course_structure_options"]').change( function(event){

		if ( $( this ).prop('checked') ) {
			$('.course-structure [name^="meta_show_unit"]').attr('checked', 'checked');
			$('.course-structure [name^="meta_show_unit"]').val( 'on' );

			$('.course-structure [name^="meta_show_page"]').attr('checked', 'checked');
			$('.course-structure [name^="meta_show_page"]').val( 'on' );
		} else {
			$('.course-structure [name^="meta_show_unit"]').removeAttr('checked');
			$('.course-structure [name^="meta_show_unit"]').val( 'off' );

			$('.course-structure [name^="meta_show_page"]').removeAttr('checked');
			$('.course-structure [name^="meta_show_page"]').val( 'off' );
		}

	});
	
	// If inheriting course show options then force save
	if ( $('[name="section_dirty"]') ) {
		mark_dirty( $('[name="section_dirty"]') );
	}

});


jQuery(document).ready(function($) {
    var unit_state_toggle = {
        init: function() {
            this.attachHandlers('.unit-state .control');
        },
        controls: {
            $radio_slide_init: function(selector)
            {
                //console.log('requested');
                $(selector).click(function()
                {
                    if ($(this).hasClass('disabled')) {
                        return;
                    }

                    if ($(selector).hasClass('on')) {
                        $(selector).removeClass('on');
                        $(selector).parent().find('.live').removeClass('on');
                        $(selector).parent().find('.draft').addClass('on');
                        $('#unit_state').val('draft');
                        $('.mp-tab.active .unit-state-circle').removeClass('active');
                        var unit_state = 'draft';
                    } else {
                        $(selector).addClass('on');
                        $(selector).parent().find('.draft').removeClass('on');
                        $(selector).parent().find('.live').addClass('on');
                        $('#unit_state').val('publish');
                        $('.mp-tab.active .unit-state-circle').addClass('active');
                        var unit_state = 'publish';
                    }

                    var unit_id = $(this).parent().find('.unit_state_id').attr('data-id');
                    var unit_nonce = $(this).parent().find('.unit_state_id').attr('data-nonce');
                    var required_cap = $(this).parent().find('.unit_state_id').attr('data-cap');

                    if (unit_id !== '') {//if it's empty it means that's not saved yet so we won't save it via ajax
                        $.post(
                                'admin-ajax.php', {
                                    action: 'change_unit_state',
                                    unit_state: unit_state,
                                    unit_id: unit_id,
                                    unit_nonce: unit_nonce,
                                    required_cap: required_cap,
                                }
                        ).done(function(data, status) {
                            if (status == 'success') {

                                var response = $.parseJSON($(data).find('response_data').text());
                                console.log(response);
                                // Apply a new nonce when returning
                                if (response && response.toggle) {
                                    $($(selector).parents('form')[0]).find('.unit_state_id').attr('data-nonce', response.nonce);
                                    $($(selector).parents('form')[0]).find('.unit_state_id').attr('data-cap', response.cap);
                                    // Else, toggle back.	
                                } else {
                                    if ($(selector).hasClass('on')) {
                                        $(selector).removeClass('on');
                                        $(selector).parent().find('.live').removeClass('on');
                                        $(selector).parent().find('.draft').addClass('on');
                                        $('#unit_state').val('draft');
                                        $('.mp-tab.active .unit-state-circle').removeClass('active');
                                    } else {
                                        $(selector).addClass('on');
                                        $(selector).parent().find('.draft').removeClass('on');
                                        $(selector).parent().find('.live').addClass('on');
                                        $('#unit_state').val('publish');
                                        $('.mp-tab.active .unit-state-circle').addClass('active');
                                    }
                                }
                            }
                        });

                    }
                });
            }
        },
        attachHandlers: function(selector) {
            //console.log('handlers attached');
            this.controls.$radio_slide_init(selector);
        }
    };

    var course_state_toggle = {
        init: function() {
            this.attachHandlers('.course-state .control');
        },
        controls: {
            $radio_slide_init: function(selector)
            {
                //console.log('requested');
                $(selector).click(function()
                {
                    if ($(this).hasClass('disabled')) {
                        return;
                    }

                    if ($(selector).hasClass('on')) {
                        $(selector).removeClass('on');
                        $(selector).parent().find('.live').removeClass('on');
                        $(selector).parent().find('.draft').addClass('on');
                        $('#course_state').val('draft');
                        var course_state = 'draft';
                    } else {
                        $(selector).addClass('on');
                        $(selector).parent().find('.draft').removeClass('on');
                        $(selector).parent().find('.live').addClass('on');
                        var course_state = 'publish';
                    }

                    var course_id = $('#course_state_id').attr('data-id');
                    var course_nonce = $('#course_state_id').attr('data-nonce');
                    var required_cap = $('#course_state_id').attr('data-cap');

                    $.post(
                            'admin-ajax.php', {
                                action: 'change_course_state',
                                course_state: course_state,
                                course_id: course_id,
                                course_nonce: course_nonce,
                                required_cap: required_cap,
                            }
                    ).done(function(data, status) {
                        if (status == 'success') {

                            var response = $.parseJSON($(data).find('response_data').text());
                            console.log(response);
                            // Apply a new nonce when returning
                            if (response && response.toggle) {
                                $('#course_state_id').attr('data-nonce', response.nonce);
                                $('#course_state_id').attr('data-cap', response.cap);
                                // Else, toggle back.	
                            } else {
                                if ($(selector).hasClass('on')) {
                                    $(selector).removeClass('on');
                                    $(selector).parent().find('.live').removeClass('on');
                                    $(selector).parent().find('.draft').addClass('on');
                                    $('#course_state').val('draft');
                                } else {
                                    $(selector).addClass('on');
                                    $(selector).parent().find('.draft').removeClass('on');
                                    $(selector).parent().find('.live').addClass('on');
                                }
                            }
                        }
                    });

                });
            }
        },
        attachHandlers: function(selector) {
            //console.log('handlers attached');
            this.controls.$radio_slide_init(selector);
        }
    };

    course_state_toggle.init();//single course in admin
    unit_state_toggle.init();
});
