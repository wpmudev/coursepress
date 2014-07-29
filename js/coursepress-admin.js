jQuery(document).ready(function($) {

    var courses_state_toggle = {
        init: function() {
            this.attachHandlers('.courses-state .control');
        },
        controls: {
            $radio_slide_init: function(selector)
            {
                //console.log('requested');
                $(selector).click(function()
                {

					var the_toggle = this;
                    var course_id = $(this).parent().find('.course_state_id').attr('data-id');
					var course_nonce = $(this).parent().find('.course_state_id').attr('data-nonce');
					var required_cap = $(this).parent().find('.course_state_id').attr('data-cap');
					if ($(this).hasClass('disabled')) {
						return;
					}
                    if ($(this).hasClass('on')) {
                        $(the_toggle).removeClass('on');
                        $(the_toggle).parent().find('.live').removeClass('on');
                        $(the_toggle).parent().find('.draft').addClass('on');						
                        var course_state = 'draft';
                    } else {
                        $(the_toggle).addClass('on');
                        $(the_toggle).parent().find('.draft').removeClass('on');
                        $(the_toggle).parent().find('.live').addClass('on');
						var course_state = 'publish';
                    }

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
							// Apply a new nonce when returning
		                    if ( response && response.toggle) {								
								$(the_toggle).parent().find('.course_state_id').attr('data-nonce', response.nonce);
								$(the_toggle).parent().find('.course_state_id').attr('data-cap', response.cap);
							// Else, toggle back.	
							} else {
			                    if ($(the_toggle).hasClass('on')) {
			                        $(the_toggle).removeClass('on');
			                        $(the_toggle).parent().find('.live').removeClass('on');
			                        $(the_toggle).parent().find('.draft').addClass('on');
			                    } else {
			                        $(the_toggle).addClass('on');
			                        $(the_toggle).parent().find('.draft').removeClass('on');
			                        $(the_toggle).parent().find('.live').addClass('on');
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

    courses_state_toggle.init();//course admin archive page

    jQuery('#unit-pages').tabs();//{active:(coursepress.unit_page_num - 1)}

    jQuery('#add_new_unit_page').live("click", function(event) {
        event.preventDefault();
        add_new_unit_page();
    });

    jQuery('.ui-tabs-anchor').live("click", function(event) {

        var current_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();
        var elements_count = jQuery('#unit-page-' + current_page + ' .modules_accordion .module-holder-title').length;

        if ((current_page == 1 && elements_count == 0) || (current_page >= 2 && elements_count == 1)) {
            jQuery('#unit-page-' + current_page + ' .elements-holder .no-elements').show();
        } else {
            jQuery('#unit-page-' + current_page + ' .elements-holder .no-elements').hide();
        }
    });

    jQuery('.delete_unit_page .button-delete-unit').live("click", function() {
        var current_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();

        if (delete_unit_page_and_elements_confirmed()) {
            jQuery('#unit-page-' + current_page + ' .element_id').each(function(i, obj) {
                prepare_element_for_execution(jQuery(this).val());
                jQuery(this).closest('.module-holder-title').remove();
            });

//jQuery('#unit-page-' + current_page + ' .removable').each(function(i, obj) {
            jQuery('.removable').each(function(i, obj) {
                jQuery(this).closest('.module-holder-title').remove();
            });

            jQuery('#unit-pages .ui-tabs-nav .ui-state-active').remove();

            jQuery('#unit-page-' + current_page).remove();

            reenumarate_unit_pages();

            /*if (current_page == 1) {
             active_num = 1;
             } else {
             active_num = 0;
             }*/

            var unit_pages = jQuery("#unit-pages .ui-tabs-nav li").size() - 2;

            //var elements_count = jQuery('#unit-page-' + current_page + ' .modules_accordion .module-holder-title').length;

            if (unit_pages == 1) {
                jQuery(".delete_unit_page").hide();
            } else {
                jQuery(".delete_unit_page").show();
            }

            jQuery("#unit-pages").tabs({active: 0});

            current_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();

            if (typeof current_page === "undefined") {
                jQuery("#unit-pages").tabs({active: 1});
            }
        }

        function reenumarate_unit_pages() {
            var i = 1;
            jQuery(".unit-pages-navigation li.ui-state-default").each(function(index) {
                if (jQuery(this).find('a').html() !== '+') {
                    jQuery(this).find('a').html(i);
                    jQuery(this).attr('aria-controls', 'unit-page-' + i);
                    jQuery(this).attr('aria-labelledby', 'ui-id-' + i);
                    jQuery(this).find('a').attr('href', '#unit-page-' + i);
                    jQuery(this).find('a').attr('id', 'ui-id-' + i);
                    i++;
                }
            });

            i = 1;

            jQuery("#unit-pages .ui-tabs-panel").each(function(index) {
                jQuery(this).attr('id', 'unit-page-' + i);
                jQuery(this).attr('aria-controls', 'unit-page-' + i);
                jQuery(this).attr('aria-labelledby', 'ui-id-' + i);
                i++;
            });

        }

        function delete_unit_page_and_elements_confirmed() {
            return confirm(coursepress.delete_unit_page_and_elements_alert);
        }

        function prepare_element_for_execution(module_to_execute_id) {
            jQuery('<input>').attr({
                type: 'hidden',
                name: 'modules_to_execute[]',
                value: module_to_execute_id
            }).appendTo('#unit-add');
        }

    });


    jQuery('.ui-tabs-anchor').live("click", function(event) {
        var current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();

        var form_action = jQuery("#unit-add").attr("action");

        //var match = form_action.match( /unit-page-\[( \d+ )\]/ );
        //alert( match[1] );

        if (jQuery('#unit-page-' + current_unit_page + ' .modules_accordion div').first().attr('class') == 'module-holder-page_break_module module-holder-title') {
            jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').accordion("option", "active", 1);
        } else {
            jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').accordion("option", "active", 0);
        }

    });


    function add_new_unit_page() {
        var tabs = jQuery("#unit-pages").tabs();
        var unit_pages = jQuery("#unit-pages .ui-tabs-nav li").size() - 2;
        var next_page = (unit_pages + 1);
        var id = "unit-page-" + next_page;
        var li = '<li><a href="#' + id + '">' + next_page + '</a><span class="arrow-down"></span></li>';
        var tabs_html = jQuery('.ui-tabs-nav').html();
        var add_page_plus = '<li class="ui-state-default ui-corner-top"><a id="add_new_unit_page" class="ui-tabs-anchor">+</a></li>';

        tabs_html = tabs_html.replace(add_page_plus, '');

        jQuery('.ui-tabs-nav').html(tabs_html + li + add_page_plus);

        jQuery('#unit-pages').append('<div id="unit-page-' + next_page + '"><div class="course-details elements-holder">' + jQuery('.elements-holder').html() + '</div><div class="modules_accordion"></div></div>');
        //jQuery('#unit-page-'+next_page).append('<a class="delete_module_link" onclick="delete_unit_page_and_elements_confirmed()"><i class="fa fa-trash-o"></i> '+coursepress.delete_unit_page_label+'</a>');
        tabs.tabs("refresh");

        jQuery('#unit-page-' + next_page + ' .page_title').val('');

        /*jQuery( '#unit-page-' + next_page + ' .modules_accordion' ).accordion( {
         heightStyle: "content",
         header: "> div > h3",
         collapsible: true,
         } );*/


        jQuery('#unit-page-' + next_page + ' .modules_accordion').accordion({
            //
            heightStyle: "content",
            header: "> div > h3",
            collapsible: true,
            //active: ".remove_module_link"
        }).sortable({
            items: "div:not( .module-holder-page_break_module )",
            handle: "h3",
            axis: "y",
            stop: function(event, ui) {

                update_sortable_module_indexes();
                //ui.draggable.attr( 'id' ) or ui.draggable.get( 0 ).id or ui.draggable[0].id

                /* Dynamic WP Editor */
                var nth_child_num = ui.item.index() + 1;
                var editor_id = jQuery(".module-holder-title:nth-child( " + nth_child_num + " ) .wp-editor-wrap").attr('id');
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
        });

        var cloned = jQuery('.draggable-module-holder-page_break_module').html();
        cloned = '<div class="module-holder-page_break_module module-holder-title">' + cloned + '</div>';

        jQuery('#unit-page-' + next_page + ' .modules_accordion').append(cloned);

        jQuery('#unit-page-' + next_page + ' .modules_accordion').accordion("refresh");

        jQuery("#unit-pages li").each(function(index) {
            jQuery(this).removeClass('ui-tabs-active ui-state-active'); //fix for active unit page state
        });

        jQuery('#unit-pages').tabs({active: unit_pages}); //set last added page active

        var current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();
        var accordion_elements_count = jQuery('#unit-pages-' + current_unit_page + ' .modules_accordion').find('div.module-holder-title').length;

        jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').show();

        /*  if ((current_unit_page == 1 && accordion_elements_count == 0) || (current_unit_page >= 2 && accordion_elements_count == 1)) {
         jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').show();
         } else {
         jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').hide();
         }*/

        if (unit_pages == 0) {
            jQuery(".delete_unit_page").hide();
        } else {
            jQuery(".delete_unit_page").show();
        }

    }
});

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
//jQuery( '#adminmenu .wp-submenu li.current' ).removeClass( "current" );
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

function coursepress_no_elements(elements_number) {

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
    jQuery('.elements-holder div.output-element, .elements-holder div.input-element').live('click', function() {//.unit-module-add, 

        var current_unit_page = 0;//current selected unit page

        current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();

        var stamp = new Date().getTime();
        var module_count = 0;


        jQuery('input#beingdragged').val(jQuery(this).find('.add-element').attr('id'));//jQuery( "#unit-page-" + current_unit_page + " .unit-module-list option:selected" ).val()

        var cloned = jQuery('.draggable-module-holder-' + jQuery('input#beingdragged').val()).html();

        cloned = '<div class="module-holder-' + jQuery('input#beingdragged').val() + ' module-holder-title">' + cloned + '</div>';
        jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').append(cloned);

        var data = '';

        jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').accordion();
        jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').accordion("refresh");
        jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').accordion("option", "active", -1);

        moving = jQuery('input#beingdragged').val();

        if (moving != '') {

        }

        jQuery('.module_order').each(function(i, obj) {
            jQuery(this).val(i + 1);
            module_count = i;
        });

        module_count = module_count - jQuery(".unit-module-list option").size();

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

        jQuery('#unit-page-' + current_unit_page + ' .modules_accordion .editor_in_place').last().html(text_editor_whole);

        tinyMCE.init({
            mode: "exact",
            elements: rand_id,
            toolbar: "bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo",
            menubar: false
        });

        var accordion_elements_count = (jQuery(this).parents('.elements-holder').siblings('.modules_accordion').find('div.module-holder-title').length);//find('.modules_accordion').length

		jQuery(this).parent().parent().find('.modules_accordion div.module-holder-title').last().find('.module-title').attr( 'data-panel', accordion_elements_count );
		jQuery(this).parent().parent().find('.modules_accordion div.module-holder-title').last().find('.module-title').attr( 'data-id', -1 );
		$('[name="active_mod"]').val( -1 );

        //alert(jQuery(this).parent().parent().find('.elements-holder .no-elements').attr('class'));

        if ((current_unit_page == 1 && accordion_elements_count == 0) || (current_unit_page >= 2 && accordion_elements_count == 1)) {
            jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').show();
        } else {
            jQuery('#unit-page-' + current_unit_page + ' .elements-holder .no-elements').hide();
        }
		

        /*if (accordion_elements_count >= 1) {
         jQuery(this).parent().parent().find('.elements-holder .no-elements').hide();
         } else {
         jQuery(this).parent().parent().find('.elements-holder .no-elements').show();
         }*/

    });
    /* Drag & Drop */

    /*jQuery( '.module-droppable' ).droppable( {
     hoverClass: 'hoveringover',
     drop: function( event, ui ) {
     var stamp = new Date().getTime();
     
     var cloned = jQuery( '.draggable-module-holder-' + jQuery( 'input#beingdragged' ).val() ).html();
     cloned = '<div class="module-holder-' + jQuery( 'input#beingdragged' ).val() + ' module-holder-title">' + cloned + '</div>';
     
     jQuery( '.modules_accordion' ).prepend( cloned );
     
     var data = '';
     
     jQuery( '#modules_accordion' ).accordion();
     jQuery( '#modules_accordion' ).accordion( "refresh" );
     
     moving = jQuery( 'input#beingdragged' ).val();
     
     if ( moving != '' ) {
     
     }
     
     jQuery( '.module_order' ).each( function( i, obj ) {
     jQuery( this ).val( i + 1 );
     } );
     
     jQuery( "input[name*='radio_answers']" ).each( function( i, obj ) {
     jQuery( this ).attr( "name", "radio_input_module_radio_answers[" + jQuery( this ).closest( ".module-content" ).find( '.module_order' ).val() + '][]' );
     } );
     
     jQuery( "input[name*='radio_check']" ).each( function( i, obj ) {
     jQuery( this ).attr( "name", "radio_input_module_radio_check[" + jQuery( this ).closest( ".module-content" ).find( '.module_order' ).val() + '][]' );
     } );
     
     jQuery( "input[name*='checkbox_answers']" ).each( function( i, obj ) {
     jQuery( this ).attr( "name", "checkbox_input_module_checkbox_answers[" + jQuery( this ).closest( ".module-content" ).find( '.module_order' ).val() + '][]' );
     } );
     
     jQuery( "input[name*='checkbox_check']" ).each( function( i, obj ) {
     jQuery( this ).attr( "name", "checkbox_input_module_checkbox_check[" + jQuery( this ).closest( ".module-content" ).find( '.module_order' ).val() + '][]' );
     } );
     
     jQuery( '#modules_accordion' ).accordion( "option", "active", 0 );
     
     // Dynamic WP Editor 
     var rand_id = 'rand_id' + Math.floor( ( Math.random() * 99999 ) + 100 ) + '_' + Math.floor( ( Math.random() * 99999 ) + 100 ) + '_' + Math.floor( ( Math.random() * 99999 ) + 100 );
     
     jQuery.get( 'admin-ajax.php', {action: 'dynamic_wp_editor', rand_id: rand_id, module_name: moving} )
     .success( function( editor ) {
     jQuery( '#modules_accordion .editor_in_place' ).last().html( editor )
     tinymce.execCommand( 'mceAddEditor', false, rand_id );
     quicktags( {id: rand_id} );
     } );
     }
     } );*/

}

jQuery(document).ready(coursepress_modules_ready);
/* END-UNIT MODULES*/

jQuery(function() {
    jQuery(".spinners").spinner({
        min: 0,
        stop: function(event, ui) {
            // Trigger change event.
            jQuery(this).change();
        },
    });
    jQuery('.dateinput').datepicker({
        dateFormat: 'yy-mm-dd'
    });
});

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

    var current_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();
    var elements_count = jQuery('#unit-page-' + current_page + ' .modules_accordion .module-holder-title').length;

    if ((current_page == 1 && elements_count == 0) || (current_page >= 2 && elements_count == 1)) {
        jQuery('#unit-page-' + current_page + ' .elements-holder .no-elements').show();
    } else {
        jQuery('#unit-page-' + current_page + ' .elements-holder .no-elements').hide();
    }
}

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
    $ = jQuery;
    if (delete_instructor_confirmed()) {

        // Course ID
        var course_id = $('[name=course_id]').val();
        if (!course_id) {
            course_id = $.urlParam('course_id');
            $('[name=course_id]').val(course_id);
        }

        // Mark as dirty
        var parent_section = $('#instructor_holder_' + instructor_id).parents('.course-section.step')[0];
        if (parent_section) {
            if (!$(parent_section).hasClass('dirty')) {
                $(parent_section).addClass('dirty');
            }
        }

        $.post(
                'admin-ajax.php', {
                    action: 'remove_course_instructor',
                    user_id: instructor_id,
                    course_id: course_id,
                }
        ).done(function(data, status) {
            // Handle return
            if (status == 'success') {

                var response = $.parseJSON($(data).find('response_data').text());
                var response_type = $($.parseHTML(response.content));

                if (response.instructor_removed) {
                    $("#instructor_holder_" + instructor_id).remove();
                    $("#instructor_" + instructor_id).remove();
                    if (1 == $('.instructor-avatar-holder').length) {
                        $('.instructor-avatar-holder.empty').show();
                    }
                }

            } else {
            }
        }).fail(function(data) {
        });

    }
}

function removePendingInstructor(invite_code, course_id) {
    $ = jQuery;
    if (confirm(coursepress.delete_pending_instructor_alert)) {
        $.post(
                'admin-ajax.php', {
                    action: 'remove_instructor_invite',
                    invite_code: invite_code,
                    course_id: course_id,
                }
        ).done(function(data, status) {
            if (status == 'success') {
                $('#' + invite_code).remove();
            }
        }).fail(function(data) {
        });
    }
}

jQuery(document).ready(function() {

    // Enable spellcheck on textboxes/textareas
    jQuery.each(jQuery('[type="text"]'), function(index, val) {
        jQuery(jQuery('[type="text"]')[index]).attr('spellcheck', true);
    });
    jQuery.each(jQuery('textarea'), function(index, val) {
        jQuery(jQuery('textarea')[index]).attr('spellcheck', true);
    });

    // Enable tinyMCE browser spellcheck
    if (typeof tinyMCE != "undefined") {
        tinyMCE.init({
            browser_spellcheck: true,
        });
    }

    function get_tinymce_content(id) {

        tinyMCE.init({
// General options
            mode: "specific_textareas",
            editor_selector: "mceEditor",
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
        //tinyMCE.setActive( id, true );
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

    // MOVED TO: coursepress-courses.js
    // jQuery('#add-instructor-trigger').click(function() {
    //
    //     var instructor_id = jQuery('#instructors option:selected').val();
    //
    //     // Mark as dirty
    //     var parent_section = jQuery(this).parents('.course-section.step')[0];
    //     if (parent_section) {
    //         if (!jQuery(parent_section).hasClass('dirty')) {
    //             jQuery(parent_section).addClass('dirty');
    //         }
    //     }
    //
    //     if (jQuery("#instructor_holder_" + instructor_id).length == 0) {
    //         jQuery('.instructor-avatar-holder.empty').hide();
    //         jQuery('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor( ' + instructor_id + ' );"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery('#instructors option:selected').text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
    //     }
    //
    //     jQuery.get('admin-ajax.php', {action: 'assign_instructor_capabilities', user_id: instructor_id})
    //             .success(function(data) {
    //                 //alert( data );
    //             });
    // });
    var ct = 2;
    jQuery('a.radio_new_link').live('click', function() {
        var unique_group_id = jQuery(this).closest(".module-content").find('.module_order').val();
        var r = '<tr><td><input class="radio_answer_check" type="radio" name="radio_input_module_radio_check_' + unique_group_id + '[]"><input class="radio_answer" type="text" name="radio_input_module_radio_answers_' + unique_group_id + '[]"></td><td><a class="radio_remove" onclick="jQuery( this ).parent().parent().remove();"><i class="fa fa-trash-o"></i></a></td></tr>';
        jQuery(this).parent().find(".ri_items").append(r);
        //jQuery( this ).parent().parent().parent().append( r );

        jQuery("input[name*='radio_answers']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_answers[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
        jQuery("input[name*='radio_check']").each(function(i, obj) {
            jQuery(this).attr("name", "radio_input_module_radio_check[" + jQuery(this).closest(".module-content").find('.module_order').val() + '][]');
        });
    });
    jQuery('a.checkbox_new_link').live('click', function() {
        var unique_group_id = jQuery(this).closest(".module-content").find('.module_order').val();
        var r = '<tr><td><input class="checkbox_answer_check" type="checkbox" name="checkbox_input_module_checkbox_check_' + unique_group_id + '[]"><input class="checkbox_answer" type="text" name="checkbox_input_module_checkbox_answers_' + unique_group_id + '[]"></td><td><a class="checkbox_remove" onclick="jQuery( this ).parent().parent().remove();"><i class="fa fa-trash-o"></i></a></td></tr>';
        //jQuery( this ).parent().parent().parent().append( r );

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

    var current_unit_page = 0;
    current_unit_page = jQuery('#unit-pages .ui-tabs-nav .ui-state-active a').html();

    jQuery('#unit-page-' + current_unit_page + ' .modules_accordion').show();
    jQuery(".loading_elements").hide();
    jQuery(".unit-pages-navigation").show();

    var editor_content = '';

//#unit-page-' + current_unit_page + ' .modules_accordion'
    jQuery('.modules_accordion').accordion({
        heightStyle: "content",
        header: "> div > h3",
        collapsible: true,
        //active: ".remove_module_link"
    }).sortable({
        //items: "div:not(.notmovable)",
        handle: "h3",
        axis: "y",
        stop: function(event, ui) {

            update_sortable_module_indexes();
            //ui.draggable.attr( 'id' ) or ui.draggable.get( 0 ).id or ui.draggable[0].id

            /* Dynamic WP Editor */
            var nth_child_num = ui.item.index() + 1;
            var editor_id = jQuery(".module-holder-title:nth-child( " + nth_child_num + " ) .wp-editor-wrap").attr('id');
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
    /*} );*/


    jQuery('#open_ended_enrollment').change(function() {
        if (this.checked) {
            //jQuery( '#all_course_dates' ).hide( 500 );
            jQuery(this).parents('.enrollment-dates').find('.start-date label').removeClass('required');
            jQuery(this).parents('.enrollment-dates').find('.end-date label').removeClass('required');
            jQuery(this).parents('.enrollment-dates').find('.start-date').addClass('disabled');
            jQuery(this).parents('.enrollment-dates').find('.start-date input').attr('disabled', 'disabled');
            jQuery(this).parents('.enrollment-dates').find('.end-date').addClass('disabled');
            jQuery(this).parents('.enrollment-dates').find('.end-date input').attr('disabled', 'disabled');
        } else {
            //jQuery( '#all_course_dates' ).show( 500 );
            jQuery(this).parents('.enrollment-dates').find('.start-date label').addClass('required');
            jQuery(this).parents('.enrollment-dates').find('.end-date label').addClass('required');
            jQuery(this).parents('.enrollment-dates').find('.start-date').removeClass('disabled');
            jQuery(this).parents('.enrollment-dates').find('.start-date input').removeAttr('disabled');
            jQuery(this).parents('.enrollment-dates').find('.end-date').removeClass('disabled');
            jQuery(this).parents('.enrollment-dates').find('.end-date input').removeAttr('disabled');
        }
    });

    jQuery('#open_ended_course').change(function() {
        if (this.checked) {
            jQuery(this).parents('.course-dates').find('.end-date label').removeClass('required');
            jQuery(this).parents('.course-dates').find('.end-date').addClass('disabled');
            jQuery(this).parents('.course-dates').find('.end-date input').attr('disabled', 'disabled');
        } else {
            jQuery(this).parents('.course-dates').find('.end-date label').addClass('required');
            jQuery(this).parents('.course-dates').find('.end-date').removeClass('disabled');
            jQuery(this).parents('.course-dates').find('.end-date input').removeAttr('disabled');
        }
    });

    jQuery('#limit_class_size').change(function() {
        if (this.checked) {
            jQuery(this).parents('.wide').find('.limit-class-size-required').addClass('required');
            jQuery('input.class_size').removeClass('disabled');
            jQuery('input.class_size').removeAttr('disabled');
        } else {
            jQuery(this).parents('.wide').find('.limit-class-size-required').removeClass('required');
            jQuery('input.class_size').addClass('disabled');
            jQuery('input.class_size').attr('disabled', 'disabled');
        }
    });

    jQuery('#paid_course').change(function() {
        if (this.checked) {
            jQuery(this).parents('.product').find('.course-sku input').removeClass('disabled');
            jQuery(this).parents('.product').find('.course-price input').removeClass('disabled');
            jQuery(this).parents('.product').find('.course-sale-price input').removeClass('disabled');
            jQuery(this).parents('.product').find('.course-sku input').removeAttr('disabled');
            jQuery(this).parents('.product').find('.course-price input').removeAttr('disabled');
            jQuery(this).parents('.product').find('.course-sale-price input').removeAttr('disabled');
            jQuery(this).parents('.product').find('.course-price .price-label').addClass('required');
            jQuery(this).parents('.product').find('.payment-gateway-required').addClass('required');
			jQuery(this).parents('.product').find('.course-paid-course-details').removeClass('hidden');

            // jQuery('input.class_size').removeClass('disabled');
            // jQuery('input.class_size').removeAttr('disabled');
        } else {
            jQuery(this).parents('.product').find('.course-sku input').addClass('disabled');
            jQuery(this).parents('.product').find('.course-price input').addClass('disabled');
            jQuery(this).parents('.product').find('.course-sale-price input').addClass('disabled');
            jQuery(this).parents('.product').find('.course-sku input').attr('disabled', 'disabled');
            jQuery(this).parents('.product').find('.course-price input').attr('disabled', 'disabled');
            jQuery(this).parents('.product').find('.course-sale-price input').attr('disabled', 'disabled');
            jQuery(this).parents('.product').find('.course-price .price-label').removeClass('required');
            jQuery(this).parents('.product').find('.payment-gateway-required').removeClass('required');
			jQuery(this).parents('.product').find('.course-paid-course-details').addClass('hidden');			
            // jQuery( this ).parents('.wide').find('.limit-class-size-required').removeClass('required');
            //             jQuery('input.class_size').addClass('disabled');
            //             jQuery('input.class_size').attr('disabled', 'disabled');
        }
    });

    jQuery('.course-section #mp_is_sale').change(function() {
        if (this.checked) {
            jQuery(this).parents('.product').find('.course-sale-price .price-label').addClass('required');
        } else {
            jQuery(this).parents('.product').find('.course-sale-price .price-label').removeClass('required');
        }
    });


});

jQuery(document).ready(function()
{

    jQuery('.featured_url_button').on('click', function()
    {

        var target_url_field = jQuery(this).prevAll(".featured_url:first");

        wp.media.string.props = function(props, attachment)
        {
            //console.log(props);
            jQuery(target_url_field).val(props.url);
            jQuery('#thumbnail_id').val('');
            jQuery('#featured_url_size').val('');
        }

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
    //( identifier );
    jQuery('#r' + ct + 'td1').html('<input class="radio_answer" type="text" name="radio_input_module_radio_answers[' + identifier + '][]" /><input class="radio_answer_check" type="radio" name="radio_input_module_radio_answers_check[' + identifier + '][]" />');
    if (ct >= 3) {
        jQuery('#r' + ct + 'td4').html('<a class="radio_remove" >' + coursepress.remove_row + '</a>'); //href="javascript:radio_removeElement( \'items\',\'r' + ct + '\' );"
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
    //var t = document.getElementById( 'items' );

    r.appendChild(ca);
    r.appendChild(cd);
    //t.appendChild();
    //jQuery( "input[name='radio_input_module_radio_answers_"+identifier+"']" ).closest( ".ri-items" ).append( r );
    //alert( jQuery( "input[name='radio_input_module_radio_answers_" + identifier + "']" ).val() );
}

jQuery('a').on('click', function(e) {
    e.stopPropagation();
});

jQuery(function() {
    if (jQuery(window).width() < 783) {
        jQuery('.wp-editor-wrap .switch-tmce').click(function() {
            jQuery(this).parents('.wp-editor-wrap').find('.mce-toolbar-grp').toggle();
            jQuery(this).parents('.wp-editor-wrap').find('.quicktags-toolbar').hide();
        });
        jQuery('.wp-editor-wrap .switch-html').click(function() {
            jQuery(this).parents('.wp-editor-wrap').find('.quicktags-toolbar').toggle();
            jQuery(this).parents('.wp-editor-wrap').find('.mce-toolbar-grp').hide();
        });
    }

    if (jQuery(window).width() < 783) {
        jQuery('.sticky-slider').click(function() {
            if (jQuery(this).hasClass('slider-open')) {
                jQuery(this).parent().animate({left: "-235px"}, 500);
                jQuery(this).parent().siblings('.mp-settings').animate({left: "32px"}, 500);
                jQuery(this).removeClass('slider-open');
            } else {
                jQuery(this).parent().animate({left: "-11px"}, 500);
                jQuery(this).parent().siblings('.mp-settings').animate({left: "258px"}, 500);
                jQuery(this).addClass('slider-open');
            }
        });
    }

    if (jQuery(window).width() < 556) {
        jQuery('.coursepress_page_instructors div.course-liquid-right').after(jQuery('.coursepress_page_instructors div.course-liquid-left'));
    }

    if (jQuery(window).width() >= 556) {
        jQuery('.coursepress_page_instructors div.course-liquid-left').after(jQuery('.coursepress_page_instructors div.course-liquid-right'));
    }


});