/* UNIT MODULES */
jQuery(document).ready(function() {


    /*jQuery("input[name*='radio_check']").change(function() {
     jQuery("input[name*='radio_check']:checked").each(function() {
     jQuery(this).closest(".module-content").find('.checked_index').val(jQuery(this).parent().find('.radio_answer').val());
     });
     });*/

    jQuery('.button-primary').click(function() {
        jQuery("input[name*='radio_check']:checked").each(function() {
            var vl = jQuery(this).parent().find('.radio_answer').val();
            jQuery(this).closest(".module-content").find('.checked_index').val(vl);
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
//jQuery('input#beingdragged').val('');

            }
        });
        jQuery('.module-droppable').droppable({
            hoverClass: 'hoveringover',
            drop: function(event, ui) {
                var stamp = new Date().getTime();
                var cloned = jQuery('.draggable-module-holder-' + jQuery('input#beingdragged').val()).html();
                cloned = '<div class="module-holder-' + jQuery('input#beingdragged').val() + ' module-holder-title">' + cloned + '</div>';
                jQuery('.modules_accordion').html(cloned + jQuery('.modules_accordion').html());
                var data = '';
                jQuery.post('admin-ajax.php?action=dynamic_wp_editor', data, function(response) {
                    jQuery('#modules_accordion .editor_to_place').html(response);
                });
                /*            jQuery('textarea, div, a, input').each(function() {
                 var attr = jQuery(this).attr('id');
                 var str_to_replace = '';
                 
                 if (typeof attr !== 'undefined' && attr !== false) {
                 var current_object_id = jQuery(this).attr('id');
                 var matched_results = new Array();
                 matched_results = current_object_id.match(/id_placeholder/g);
                 if (typeof matched_results !== 'undefined' && matched_results !== null) {
                 str_to_replace = jQuery(this).attr('id');
                 
                 jQuery(this).attr('id', str_to_replace.replace('id_placeholder','editor_' + stamp));
                 }
                 }
                 });
                 
                 jQuery('a').each(function() {
                 var attr = jQuery(this).attr('data-editor');
                 var str_to_replace = '';
                 
                 if (typeof attr !== 'undefined' && attr !== false) {
                 var current_object_id = jQuery(this).attr('data-editor');
                 var matched_results = new Array();
                 matched_results = current_object_id.match(/id_placeholder/g);
                 if (typeof matched_results !== 'undefined' && matched_results !== null) {
                 str_to_replace = jQuery(this).attr('data-editor');
                 
                 jQuery(this).attr('data-editor', str_to_replace.replace('id_placeholder','editor_' + stamp));
                 }
                 }
                 });*/



                jQuery('.modules_accordion').accordion("refresh");
                moving = jQuery('input#beingdragged').val();
                if (moving != '') {

                    /*
                     jQuery('#main-' + moving).prependTo('#' + ruleplace + '-holder');
                     jQuery('#' + moving).hide();
                     
                     // redisplay our one
                     jQuery('#main-' + moving).removeClass('closed').addClass('open');
                     */
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




            }
        }, function() {
            jQuery('a').click(function(e) {
                e.stopPropagation();
            });
        }).on('click', 'a', function(e) {
            e.stopPropagation();
        });
        jQuery('.action .action-top .action-button').click(coursepress_module_click_action_toggle);
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
    function unenroll_student_confirmed() {
        return confirm(coursepress.unenroll_student_alert);
    }

    function unenrollStudent() {
        if (unenroll_student_confirmed()) {
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
        });
        jQuery('#add-instructor-trigger').click(function() {
            var instructor_id = jQuery('#instructors option:selected').val();
            if (jQuery("#instructor_holder_" + instructor_id).length == 0) {
                jQuery('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-remove"><a href="javascript:removeInstructor(' + instructor_id + ');"></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery('#instructors option:selected').text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
            }
        });
        /*jQuery(function() {
         jQuery("#modules_accordion").sortable({
         handle: "h3",
         stop: function(event, ui) {
         //update_sortable_indexes();
         }
         });
         
         jQuery("#modules_accordion").disableSelection();
         });*/
        var ct = 2;

        /*jQuery(function() {*/

        jQuery('a.radio_new_link').click(function() {
            var unique_group_id = jQuery(this).closest(".module-content").find('.module_order').val();
            //alert(unique_group_id);
            /*ct++;
             var r = document.createElement('tr');
             r.setAttribute('id', 'r' + ct);
             var ca = document.createElement('td');
             ca.setAttribute('id', 'r' + ct + 'td1');
             var cd = document.createElement('td');
             cd.setAttribute('id', 'r' + ct + 'td4');
             var t = document.getElementById('items');
             r.appendChild(ca);
             r.appendChild(cd);
             jQuery(this).parent().parent().parent().append(r);
             
             jQuery('#r' + ct + 'td1').html('<input class="radio_answer" type="text" name="radio_input_module_radio_answers_' + unique_group_id + '[]" /><input class="radio_answer_check" type="radio" name="radio_input_module_radio_answers_check_' + unique_group_id + '[]" />');
             if (ct >= 3) {
             jQuery('#r' + ct + 'td4').html('<a class="radio_remove" onClick="jQuery(this).parent().parent().remove();" >' + coursepress.remove_row + '</a>'); //href="javascript:radio_removeElement(\'items\',\'r' + ct + '\');"
             } else {
             jQuery('#r' + ct + 'td4').html('');
             }*/

            var r = '<tr><td><input class="radio_answer" type="text" name="radio_input_module_radio_answers_' + unique_group_id + '[]"><input class="radio_answer_check" type="radio" name="radio_input_module_radio_answers_check_' + unique_group_id + '[]"></td><td><a class="radio_remove" onclick="jQuery(this).parent().parent().remove();">Remove</a></td></tr>';
            jQuery(this).parent().parent().parent().append(r);

            jQuery("input[name*='radio_answers']").each(function(i, obj) {
                jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
            });

            jQuery("input[name*='radio_check']").each(function(i, obj) {
                jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
            });
        });
        jQuery("#students_accordion").accordion({
            heightStyle: "content",
            active: parseInt(coursepress.active_student_tab)
        });
        jQuery("#modules_accordion").accordion({
            heightStyle: "content",
            header: "> div > h3",
            //active: ".remove_module_link"
        }).sortable({
            axis: "y",
            stop: function(event, ui) {
// IE doesn't register the blur when sorting
// so trigger focusout handlers to remove .ui-state-focus
//ui.item.children("h3").triggerHandler("focusout");
                update_sortable_module_indexes();
            }
        }, function() {
            jQuery('a').click(function(e) {
                e.stopPropagation();
            })
        }).on('click', 'a', function(e) {
            e.stopPropagation();
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

            /*jQuery("input[name*='radio_answers[999]'], input[name*='radio_answers_check[999]']").each(function(i, obj) {
             jQuery(this).attr("name", "radio_input_module_radio_answers[" + (i + 1) + '][]');
             });*/
        }



        jQuery('#open_ended_course').change(function() {
            if (this.checked) {
                jQuery('#all_course_dates').hide(500);
            } else {
                jQuery('#all_course_dates').show(500);
            }
        });
        //capture the click on the a tag
        /*   jQuery("#modules_accordion div h3 a").click(function() {
         alert(jQuery(this).attr('href'));
         
         if (jQuery(this).attr('href') == 'remove') {
         jQuery(this).parent('.module-holder-title').remove();
         } else {
         window.location = jQuery(this).attr('href');
         }
         
         return false;
         });*/



    });
    jQuery(document).ready(function()
    {


        jQuery('.featured_url_button').click(function()
        {

            var target_url_field = jQuery(this).prevAll(".featured_url:first");
            wp.media.editor.send.attachment = function(props, attachment)
            {
                jQuery(target_url_field).val(attachment.url);
                jQuery('#thumbnail_id').val(attachment.id);
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
//alert("The parent div cannot be removed.");
        }
        else if (document.getElementById(childDiv)) {
            var child = document.getElementById(childDiv);
            var parent = document.getElementById(parentDiv);
            parent.removeChild(child);
        }
        else {
//alert("Child div has already been removed or does not exist.");
//return false;
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

    jQuery('a').click(function(e) {
        e.stopPropagation();
    });

});