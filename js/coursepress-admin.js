jQuery(document).ready(function() {

    jQuery('#add_student_class').click(function() {

        var class_input_errors = 0;
        if (jQuery('.course_classes_input').val() == '') {
            jQuery('.add_class_message').html(coursepress.empty_class_name);
            class_input_errors++;
        }

        jQuery(".ui-accordion-header h3").each(function(index) {
            if (jQuery(this).attr('data-title') == jQuery('.course_classes_input').val()) {
                jQuery('.add_class_message').html(coursepress.duplicated_class_name);
                class_input_errors++;
            }
        });
        if (class_input_errors == 0) {
            return true;
        } else {
            return false;
        }

    });
});
jQuery(document).ready(function() {
    jQuery('.checkbox_answer').live('input', function() {
        jQuery(this).closest('td').find(".checkbox_answer_check").val(jQuery(this).val());
    });
});
jQuery(document).ready(function() {
    if (coursepress.course_taxonomy_screen) {
//jQuery('#adminmenu .wp-submenu li.current').removeClass("current");
        jQuery('a[href="edit-tags.php?taxonomy=course_category&post_type=course"]').parent().addClass("current");
    }
});
/* UNIT MODULES */
jQuery(document).ready(function() {
    jQuery('.action .action-top .action-button').live('click', function() {
        if (jQuery(this).parent().hasClass('open')) {
            jQuery(this).parent().removeClass('open').addClass('closed');
            jQuery(this).parents('.action').find('.action-body').removeClass('open').addClass('closed');
        } else {
            jQuery(this).parent().removeClass('closed').addClass('open');
            jQuery(this).parents('.action').find('.action-body').removeClass('closed').addClass('open');
        }
    });
});
function coursepress_module_click_action_toggle() {
    if (jQuery(this).parent().hasClass('open')) {
        jQuery(this).parent().removeClass('open').addClass('closed');
        jQuery(this).parents('.action').find('.action-body').removeClass('open').addClass('closed');
    } else {
        jQuery(this).parent().removeClass('closed').addClass('open');
        jQuery(this).parents('.action').find('.action-body').removeClass('closed').addClass('open');
    }
}


function coursepress_modules_ready() {

    jQuery('.draggable-module').draggable({
        opacity: 0.7,
        helper: 'clone',
        start: function(event, ui) {
            jQuery('input#beingdragged').val(jQuery(this).attr('id'));
        },
        stop: function(event, ui) {

        }
    });
    jQuery('#unit-module-add').live('click', function() {
//jQuery('#unit-module-add').click(function() {
        var stamp = new Date().getTime();
        var module_count = 0;
        jQuery('input#beingdragged').val(jQuery("#unit-module-list option:selected").val());
        var cloned = jQuery('.draggable-module-holder-' + jQuery('input#beingdragged').val()).html();
        cloned = '<div class="module-holder-' + jQuery('input#beingdragged').val() + ' module-holder-title">' + cloned + '</div>';
        jQuery('.modules_accordion').append(cloned);
        var data = '';
        jQuery('#modules_accordion').accordion();
        jQuery('#modules_accordion').accordion("refresh");
        moving = jQuery('input#beingdragged').val();
        if (moving != '') {

        }

        jQuery('.module_order').each(function(i, obj) {
            jQuery(this).val(i + 1);
            module_count = i;
        });
        module_count = module_count - jQuery("#unit-module-list option").size();
        jQuery("input[name*='radio_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='radio_check']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='checkbox_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "checkbox_input_module_checkbox_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='checkbox_check']").each(function(i, obj) {
            jQuery(this).attr("name", "checkbox_input_module_checkbox_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        /* Dynamic WP Editor */
        /*var rand_id = 'rand_id' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100);
         jQuery.get('admin-ajax.php', {action: 'dynamic_wp_editor', rand_id: rand_id, module_name: moving})
         .success(function(editor) {
         jQuery('#modules_accordion .editor_in_place').last().html(editor);
         //tinymce.execCommand('mceAddControl', false, rand_id);
         tinymce.execCommand('mceAddEditor', false, rand_id);
         quicktags({id: rand_id});
         });*/

        /* Dynamic WP Editor */
        moving = jQuery('input#beingdragged').val();

        var rand_id = 'rand_id' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100);
        var text_editor = '<textarea name="' + moving + '_content[]" id="' + rand_id + '"></textarea>';

        var text_editor_whole =
                '<div id="wp-' + rand_id + '-wrap" class="wp-core-ui wp-editor-wrap tmce-active">' +
                '<div id="wp-' + rand_id + '-editor-tools" class="wp-editor-tools hide-if-no-js">' +
                '<div id="wp-' + rand_id + '-media-buttons" class="wp-media-buttons"><a href="#" class="button insert-media-cp add_media" data-editor="' + rand_id + '" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>' +
                '<div id="wp-' + rand_id + '-editor-container" class="wp-editor-container">' +
                text_editor +
                '</div></div></div>';

        jQuery('#modules_accordion .editor_in_place').last().html(text_editor_whole);

        tinyMCE.init({
            mode: "exact",
            elements: rand_id,
            toolbar: "bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo",
            menubar: false
        });

        jQuery('#modules_accordion').accordion("option", "active", module_count);
    });
    /* Drag & Drop */

    /*jQuery('.module-droppable').droppable({
     hoverClass: 'hoveringover',
     drop: function(event, ui) {
     var stamp = new Date().getTime();
     
     var cloned = jQuery('.draggable-module-holder-' + jQuery('input#beingdragged').val()).html();
     cloned = '<div class="module-holder-' + jQuery('input#beingdragged').val() + ' module-holder-title">' + cloned + '</div>';
     
     jQuery('.modules_accordion').prepend(cloned);
     
     var data = '';
     
     jQuery('#modules_accordion').accordion();
     jQuery('#modules_accordion').accordion("refresh");
     
     moving = jQuery('input#beingdragged').val();
     
     if (moving != '') {
     
     }
     
     jQuery('.module_order').each(function(i, obj) {
     jQuery(this).val(i + 1);
     });
     
     jQuery("input[name*='radio_answers']").each(function(i, obj) {
     jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
     });
     
     jQuery("input[name*='radio_check']").each(function(i, obj) {
     jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
     });
     
     jQuery("input[name*='checkbox_answers']").each(function(i, obj) {
     jQuery(this).attr("name", "checkbox_input_module_checkbox_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
     });
     
     jQuery("input[name*='checkbox_check']").each(function(i, obj) {
     jQuery(this).attr("name", "checkbox_input_module_checkbox_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
     });
     
     jQuery('#modules_accordion').accordion("option", "active", 0);
     
     // Dynamic WP Editor 
     var rand_id = 'rand_id' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100);
     
     jQuery.get('admin-ajax.php', {action: 'dynamic_wp_editor', rand_id: rand_id, module_name: moving})
     .success(function(editor) {
     jQuery('#modules_accordion .editor_in_place').last().html(editor)
     tinymce.execCommand('mceAddEditor', false, rand_id);
     quicktags({id: rand_id});
     });
     }
     });*/

}

jQuery(document).ready(coursepress_modules_ready);
/* END-UNIT MODULES*/

jQuery(function() {
    jQuery(".spinners").spinner({
        min: 0
    });
    jQuery('.dateinput').datepicker({
        dateFormat: 'yy-mm-dd'
    });
});
function withdraw_student_confirmed() {
    return confirm(coursepress.withdraw_student_alert);
}

function withdrawStudent() {
    if (withdraw_student_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function remove_module_confirmed() {
    return confirm(coursepress.remove_module_alert);
}

function removeModule() {
    if (remove_module_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function delete_module_confirmed() {
    return confirm(coursepress.delete_module_alert);
}

function prepare_module_for_execution(module_to_execute_id) {
    jQuery('<input>').attr({
        type: 'hidden',
        name: 'modules_to_execute[]',
        value: module_to_execute_id
    }).appendTo('#unit-add');
}


function deleteModule(module_to_execute_id) {
    if (delete_module_confirmed()) {
        prepare_module_for_execution(module_to_execute_id);
        return true;
    } else {
        return false;
    }
}

function delete_course_confirmed() {
    return confirm(coursepress.delete_course_alert);
}

function removeCourse() {
    if (delete_course_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function delete_notification_confirmed() {
    return confirm(coursepress.delete_notification_alert);
}

function removeNotification() {
    if (delete_notification_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function delete_discussion_confirmed() {
    return confirm(coursepress.delete_discussion_alert);
}

function removeDiscussion() {
    if (delete_discussion_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function removeUnit() {
    if (delete_unit_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function delete_unit_confirmed() {
    return confirm(coursepress.delete_unit_alert);
}

function delete_instructor_confirmed() {
    return confirm(coursepress.delete_instructor_alert);
}

function removeInstructor(instructor_id) {
    if (delete_instructor_confirmed()) {
        jQuery("#instructor_holder_" + instructor_id).remove();
        jQuery("#instructor_" + instructor_id).remove();
    }
}

jQuery(document).ready(function() {

    function get_tinymce_content(id) {

        tinyMCE.init({
// General options
            mode: "specific_textareas",
            editor_selector: "mceEditor"
        });
        return tinyMCE.get(id).getContent();
    }

    function set_tinymce_content(id, content) {

        tinyMCE.init({
// General options
//mode: "specific_textareas",
//editor_selector: id
        });
        tinyMCE.EditorManager.execCommand('mceFocus', false, id);
        tinyMCE.activeEditor.selection.setContent(content);
    }

    function set_tinymce_active_editor(id) {
        tinyMCE.init({
// General options
            mode: "specific_textareas",
            editor_selector: "mceEditor"
        });
        //tinyMCE.setActive(id, true);
    }

    jQuery('#enroll_type').change(function() {
        var enroll_type = jQuery("#enroll_type").val();
        if (enroll_type == 'passcode') {
            jQuery("#enroll_type_holder").css({
                'display': 'block'
            });
        } else {
            jQuery("#enroll_type_holder").css({
                'display': 'none'
            });
        }
    });
    jQuery('#enroll_type').change(function() {
        var enroll_type = jQuery("#enroll_type").val();
        if (enroll_type == 'prerequisite') {
            jQuery("#enroll_type_prerequisite_holder").css({
                'display': 'block'
            });
        } else {
            jQuery("#enroll_type_prerequisite_holder").css({
                'display': 'none'
            });
        }

        if (enroll_type == 'manually') {
            jQuery("#manually_added_holder").css({
                'display': 'block'
            });
        } else {
            jQuery("#manually_added_holder").css({
                'display': 'none'
            });
        }
    });
    jQuery('#add-instructor-trigger').click(function() {
        var instructor_id = jQuery('#instructors option:selected').val();
        if (jQuery("#instructor_holder_" + instructor_id).length == 0) {
            jQuery('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-remove"><a href="javascript:removeInstructor(' + instructor_id + ');"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery('#instructors option:selected').text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
        }

        jQuery.get('admin-ajax.php', {action: 'assign_instructor_capabilities', user_id: instructor_id})
                .success(function(data) {
                    //alert(data);
                });
    });
    var ct = 2;
    jQuery('a.radio_new_link').live('click', function() {
        var unique_group_id = jQuery(this).closest(".module-content").find('.module_order').val();
        var r = '<tr><td><input class="radio_answer_check" type="radio" name="radio_input_module_radio_check_' + unique_group_id + '[]"><input class="radio_answer" type="text" name="radio_input_module_radio_answers_' + unique_group_id + '[]"></td><td><a class="radio_remove" onclick="jQuery(this).parent().parent().remove();">Remove</a></td></tr>';
        jQuery(this).parent().find(".ri_items").append(r);
        //jQuery(this).parent().parent().parent().append(r);

        jQuery("input[name*='radio_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='radio_check']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
    });
    jQuery('a.checkbox_new_link').live('click', function() {
        var unique_group_id = jQuery(this).closest(".module-content").find('.module_order').val();
        var r = '<tr><td><input class="checkbox_answer_check" type="checkbox" name="checkbox_input_module_checkbox_check_' + unique_group_id + '[]"><input class="checkbox_answer" type="text" name="checkbox_input_module_checkbox_answers_' + unique_group_id + '[]"></td><td><a class="checkbox_remove" onclick="jQuery(this).parent().parent().remove();">Remove</a></td></tr>';
        //jQuery(this).parent().parent().parent().append(r);

        jQuery(this).parent().find(".ci_items").append(r);
        jQuery("input[name*='checkbox_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "checkbox_input_module_checkbox_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='checkbox_check']").each(function(i, obj) {
            jQuery(this).attr("name", "checkbox_input_module_checkbox_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
    });
    jQuery("#students_accordion").accordion({
        heightStyle: "content",
        active: parseInt(coursepress.active_student_tab)
    });
    jQuery("#modules_accordion").show();
    jQuery(".loading_elements").hide();
    var editor_content = '';
    jQuery("#modules_accordion").accordion({
        heightStyle: "content",
        header: "> div > h3",
        collapsible: true,
        //active: ".remove_module_link"
    }).sortable({
        handle: "h3",
        axis: "y",
        stop: function(event, ui) {

            update_sortable_module_indexes();
            //ui.draggable.attr('id') or ui.draggable.get(0).id or ui.draggable[0].id

            /* Dynamic WP Editor */
            /*var rand_id = 'rand_id' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100);
             
             jQuery.get('admin-ajax.php', {action: 'dynamic_wp_editor', rand_id: rand_id, editor_content: editor_content})
             .success(function(editor) {
             jQuery('#modules_accordion .editor_in_place').last().html(editor);
             tinymce.execCommand('mceAddEditor', false, rand_id);
             tinyMCE.execCommand('mceFocus',false, rand_id);
             quicktags({id: rand_id});
             });*/




            /* Dynamic WP Editor */
            var nth_child_num = ui.item.index() + 1;
            var editor_id = jQuery(".module-holder-title:nth-child(" + nth_child_num + ") .wp-editor-wrap").attr('id');
            var initial_editor_id = editor_id;

            editor_id = editor_id.replace("-wrap", "");
            editor_id = editor_id.replace("wp-", "");
            editor_content = get_tinymce_content(editor_id);

            var textarea_name = (jQuery('#' + initial_editor_id + ' textarea').attr('name'));
            var rand_id = 'rand_id' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100) + '_' + Math.floor((Math.random() * 99999) + 100);
            var text_editor = '<textarea name="' + textarea_name + '" id="' + rand_id + '">' + editor_content + '</textarea>';

            var text_editor_whole =
                    '<div id="wp-' + rand_id + '-wrap" class="wp-core-ui wp-editor-wrap tmce-active">' +
                    '<div id="wp-' + rand_id + '-editor-tools" class="wp-editor-tools hide-if-no-js">' +
                    '<div id="wp-' + rand_id + '-media-buttons" class="wp-media-buttons"><a href="#" class="button insert-media-cp add_media" data-editor="' + rand_id + '" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a></div>' +
                    '<div id="wp-' + rand_id + '-editor-container" class="wp-editor-container">' +
                    text_editor +
                    '</div></div></div>';
            jQuery('#' + initial_editor_id).parent().html(text_editor_whole);

            tinyMCE.init({
                mode: "exact",
                elements: rand_id,
                toolbar: "bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo",
                menubar: false
            });


        }
    }, function() {
        jQuery('a').click(function(e) {
//e.stopPropagation();
        })
    }).on('click', 'a', function(e) {
//e.stopPropagation();
    })
    /*});*/
    function update_sortable_module_indexes() {

        jQuery('.module_order').each(function(i, obj) {
            jQuery(this).val(i + 1);
        });
        jQuery("input[name*='radio_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='radio_check']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='checkbox_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "checkbox_input_module_checkbox_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='checkbox_check']").each(function(i, obj) {
            jQuery(this).attr("name", "checkbox_input_module_checkbox_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
    }



    jQuery('#open_ended_course').change(function() {
        if (this.checked) {
            jQuery('#all_course_dates').hide(500);
        } else {
            jQuery('#all_course_dates').show(500);
        }
    });
});
jQuery(document).ready(function()
{

    jQuery('.featured_url_button').on('click', function()
    {

        var target_url_field = jQuery(this).prevAll(".featured_url:first");
        wp.media.editor.send.attachment = function(props, attachment)
        {
            jQuery(target_url_field).val(attachment.url);
            jQuery('#thumbnail_id').val(attachment.id);
            jQuery('#featured_url_size').val(props.size);
        };
        wp.media.editor.open(this);
        return false;
    });
});
function radio_new_link(identifier)
{
    //(identifier);
    jQuery('#r' + ct + 'td1').html('<input class="radio_answer" type="text" name="radio_input_module_radio_answers[' + identifier + '][]" /><input class="radio_answer_check" type="radio" name="radio_input_module_radio_answers_check[' + identifier + '][]" />');
    if (ct >= 3) {
        jQuery('#r' + ct + 'td4').html('<a class="radio_remove" >' + coursepress.remove_row + '</a>'); //href="javascript:radio_removeElement(\'items\',\'r' + ct + '\');"
    } else {
        jQuery('#r' + ct + 'td4').html('');
    }
}

function radio_removeElement(parentDiv, childDiv) {
    if (childDiv == parentDiv) {
    }
    else if (document.getElementById(childDiv)) {
        var child = document.getElementById(childDiv);
        var parent = document.getElementById(parentDiv);
        parent.removeChild(child);
    }
    else {
    }
}


function radio_addRow(identifier) {
    ct++;
    var r = document.createElement('tr');
    r.setAttribute('id', 'r' + ct);
    var ca = document.createElement('td');
    ca.setAttribute('id', 'r' + ct + 'td1');
    var cd = document.createElement('td');
    cd.setAttribute('id', 'r' + ct + 'td4');
    //var t = document.getElementById('items');

    r.appendChild(ca);
    r.appendChild(cd);
    //t.appendChild();
    //jQuery( "input[name='radio_input_module_radio_answers_"+identifier+"']" ).closest(".ri-items").append(r);
    //alert(jQuery("input[name='radio_input_module_radio_answers_" + identifier + "']").val());
}

jQuery('a').on('click', function(e) {
    e.stopPropagation();
});

jQuery(function() {
   if (jQuery(window).width() < 783) {
       jQuery('.wp-editor-wrap .switch-tmce').click(function( ) {
           jQuery(this).parents('.wp-editor-wrap').find('.mce-toolbar-grp').toggle();
           jQuery(this).parents('.wp-editor-wrap').find('.quicktags-toolbar').hide();
       });
       jQuery('.wp-editor-wrap .switch-html').click(function( ) {
           jQuery(this).parents('.wp-editor-wrap').find('.quicktags-toolbar').toggle();
           jQuery(this).parents('.wp-editor-wrap').find('.mce-toolbar-grp').hide();
       });
   }

   if (jQuery(window).width() < 783) {
		jQuery('.sticky-slider').click( function() {
			if ( jQuery(this).hasClass('slider-open') ) {
				jQuery(this).parent().animate({left: "-235px"}, 500);
				jQuery(this).parent().siblings('.mp-settings').animate({left: "32px"}, 500);
				jQuery(this).removeClass('slider-open');				
			} else {
				jQuery(this).parent().animate({left: "-11px"}, 500);
				jQuery(this).parent().siblings('.mp-settings').animate({left: "258px"}, 500);
				jQuery(this).addClass('slider-open');				
			}
		} );
		/* Remove sidebar-name class to fix formatting. */
//		jQuery('.sidebar-name').removeClass('sidebar-name').removeClass('no-movecursor').addClass('sidebar-restore').addClass('responsive-reset');
	}
	
   /* Restore changed classes */	
   if (jQuery(window).width() >= 783) {	
		/* Restore sidebar-name class to fix formatting. */
//		jQuery('.sidebar-restore').removeClass('sidebar-restore').removeClass('responsive-reset').addClass('sidebar-name').addClass('no-movecursor');
	
	}

});

