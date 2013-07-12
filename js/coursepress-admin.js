/* UNIT MODULES */
function m_removeaction() {
	var section = jQuery(this).attr('id');
	var sectionname = section.replace('remove-','');

	jQuery('#main-' + sectionname).appendTo('#hiden-actions');
	jQuery('#' + sectionname).show();

	// Move from the fields
	jQuery('#in-positive-rules').val( jQuery('#in-positive-rules').val().replace(',' + sectionname, ''));
	jQuery('#in-negative-rules').val( jQuery('#in-negative-rules').val().replace(',' + sectionname, ''));

	return false;
}

function m_addnewlevel() {
	window.location = "?page=membershiplevels&action=edit&level_id=";

	return false;
}

function m_deactivatelevel() {
	if(confirm(coursepress.deactivatelevel)) {
		return true;
	} else {
		return false;
	}
}

function m_deletelevel() {
	if(confirm(coursepress.deletelevel)) {
		return true;
	} else {
		return false;
	}
}

function m_clickactiontoggle() {
	if(jQuery(this).parent().hasClass('open')) {
		jQuery(this).parent().removeClass('open').addClass('closed');
		jQuery(this).parents('.action').find('.action-body').removeClass('open').addClass('closed');
	} else {
		jQuery(this).parent().removeClass('closed').addClass('open');
		jQuery(this).parents('.action').find('.action-body').removeClass('closed').addClass('open');
	}
}

function m_addtopositive() {

	// hide all the other rules first
	m_hide_all_rules();

	moving = jQuery(this).parents('.draggable-level').attr('id');
	ruleplace = 'positive-rules';
	if(moving != '') {
		jQuery('#main-' + moving).prependTo('#' + ruleplace + '-holder');
		//jQuery('#' + moving).hide();

		// put the name in the relevant holding input field
		jQuery('#in-' + ruleplace).val( jQuery('#in-' + ruleplace).val() + ',' + moving );

		// redisplay our one
		jQuery('#main-' + moving).removeClass('closed').addClass('open');
	}

	return false;
}

function m_addtonegative() {

	// hide all the other rules first
	m_hide_all_rules();

	moving = jQuery(this).parents('.draggable-level').attr('id');
	ruleplace = 'negative-rules';
	if(moving != '') {
		jQuery('#main-' + moving).prependTo('#' + ruleplace + '-holder');
		jQuery('#' + moving).hide();

		// put the name in the relevant holding input field
		jQuery('#in-' + ruleplace).val( jQuery('#in-' + ruleplace).val() + ',' + moving );

		// redisplay our one
		jQuery('#main-' + moving).removeClass('closed').addClass('open');
	}

	return false;
}

function m_clickpositivetab() {

	if(jQuery(this).parents('.positivetab').hasClass('activetab')) {
		return false;
	} else {

		negatives = jQuery('#in-negative-rules').val();
		negatives = negatives.replace(',','');

		if(negatives.length >= 1) {
			if(!confirm(coursepress.movetopositive)) {
				return false;
			} else {
				// remove the content
				jQuery('#negative-rules-holder .level-operation').each( function(index) {
																		sectionname = jQuery(this).attr('id');
																		sectionname = sectionname.replace('main-', '');
																		jQuery('#main-' + sectionname).appendTo('#hiden-actions');
																		jQuery('#' + sectionname).show();
																	});
				jQuery('#in-negative-rules').val(',');
			}
		}

		jQuery('#ontab').val('positive');

		jQuery('.activetab').removeClass('activetab');
		jQuery('.positivetab').addClass('activetab');

		jQuery('.positivecontent').removeClass('inactivecontent').addClass('activecontent');
		jQuery('.negativecontent').removeClass('activecontent').addClass('inactivecontent');
		jQuery('.advancedcontent').removeClass('activecontent').addClass('inactivecontent');

		jQuery('.level-holder h3').css('display', 'none');
		jQuery('div.advancedtabwarning').css('display', 'none');

		jQuery('a.action-to-negative').css('display', 'none');
		jQuery('a.action-to-positive').css('display', 'block');
	}

	return false;
}

function m_clicknegativetab() {

	if(jQuery(this).parents('.negativetab').hasClass('activetab')) {
		return false;
	} else {

		positives = jQuery('#in-positive-rules').val();
		positives = positives.replace(',','');

		if(positives.length >= 1) {
			if(!confirm(coursepress.movetonegative)) {
				return false;
			} else {
				// remove the content
				jQuery('#positive-rules-holder .level-operation').each( function(index) {
																		sectionname = jQuery(this).attr('id');
																		sectionname = sectionname.replace('main-', '');
																		jQuery('#main-' + sectionname).appendTo('#hiden-actions');
																		jQuery('#' + sectionname).show();
																	});
				jQuery('#in-positive-rules').val(',');
			}
		}

		jQuery('#ontab').val('negative');

		jQuery('.activetab').removeClass('activetab');
		jQuery('.negativetab').addClass('activetab');

		jQuery('.negativecontent').removeClass('inactivecontent').addClass('activecontent');
		jQuery('.positivecontent').removeClass('activecontent').addClass('inactivecontent');
		jQuery('.advancedcontent').removeClass('activecontent').addClass('inactivecontent');

		jQuery('.level-holder h3').css('display', 'none');
		jQuery('div.advancedtabwarning').css('display', 'none');

		jQuery('a.action-to-negative').css('display', 'block');
		jQuery('a.action-to-positive').css('display', 'none');
	}

	return false;
}

function m_clickadvancedtab() {

	if(jQuery(this).parents('.advancedtab').hasClass('activetab')) {
		return false;
	} else {
		jQuery('#ontab').val('advanced');

		jQuery('.activetab').removeClass('activetab');
		jQuery('.advancedtab').addClass('activetab');

		jQuery('.positivecontent').removeClass('inactivecontent').addClass('activecontent');
		jQuery('.negativecontent').removeClass('inactivecontent').addClass('activecontent');
		jQuery('.advancedcontent').removeClass('inactivecontent').addClass('activecontent');

		jQuery('.level-holder h3').css('display', 'block');
		jQuery('div.advancedtabwarning').css('display', 'block');

		jQuery('a.action-to-negative').css('display', 'block');
		jQuery('a.action-to-positive').css('display', 'block');
	}

	return false;
}

function m_toggle_visibility() {
	if(jQuery(this).parents('.level-operation').hasClass('closed')) {
		m_hide_all_rules();
		jQuery(this).parents('.level-operation').removeClass('closed').addClass('open');
	} else {
		jQuery(this).parents('.level-operation').removeClass('open').addClass('closed');
	}
	return false;
}

function m_hide_all_rules() {
	jQuery('div.level-operation').removeClass('open').addClass('closed');
}

function m_levelsReady() {


	jQuery('.draggable-level').draggable({
			opacity: 0.7,
			helper: 'clone',
			start: function(event, ui) {
					jQuery('input#beingdragged').val( jQuery(this).attr('id') );
				 },
			stop: function(event, ui) {
					jQuery('input#beingdragged').val( '' );
				}
				});

	jQuery('.level-droppable-rules').droppable({
			hoverClass: 'hoveringover',
			drop: function(event, ui) {
					moving = jQuery('input#beingdragged').val();
					ruleplace = jQuery(this).attr('id');
					if(moving != '') {
						// hide all the other rules first
						m_hide_all_rules();

						jQuery('#main-' + moving).prependTo('#' + ruleplace + '-holder');
						jQuery('#' + moving).hide();

						// put the name inmembership the relevant holding input field
						jQuery('#in-' + ruleplace).val( jQuery('#in-' + ruleplace).val() + ',' + moving );

						// redisplay our one
						jQuery('#main-' + moving).removeClass('closed').addClass('open');
					}
				}
	});

	jQuery('#positive-rules-holder').sortable({
		opacity: 0.7,
		helper: 'clone',
		placeholder: 'placeholder-rules',
		update: function(event, ui) {
				jQuery('#in-positive-rules').val(',' + jQuery('#positive-rules-holder').sortable('toArray').join(',').replace(/main-/gi, ''));
			}
	});

	jQuery('#negative-rules-holder').sortable({
		opacity: 0.7,
		helper: 'clone',
		placeholder: 'placeholder-rules',
		update: function(event, ui) {
				jQuery('#in-negative-rules').val(',' + jQuery('#negative-rules-holder').sortable('toArray').join(',').replace(/main-/gi, ''));
			}
	});

	jQuery('a.removelink').click(m_removeaction);
	jQuery('.addnewlevelbutton').click(m_addnewlevel);

	jQuery('.deactivate a').click(m_deactivatelevel);
	jQuery('.delete a').click(m_deletelevel);

	jQuery('.action .action-top .action-button').click(m_clickactiontoggle);

	jQuery('a.action-to-positive').click(m_addtopositive);
	jQuery('a.action-to-negative').click(m_addtonegative);

	jQuery('.positivetab a').click(m_clickpositivetab);
	jQuery('.negativetab a').click(m_clicknegativetab);
	jQuery('.advancedtab a').click(m_clickadvancedtab);

	jQuery('div.level-operation h2.sidebar-name').click(m_toggle_visibility);

	m_hide_all_rules();

	if(jQuery('ul.leveltabs li.positivetab').hasClass('activetab')) {
		jQuery('a.action-to-negative').css('display', 'none');
		jQuery('a.action-to-positive').css('display', 'block');
	}

	if(jQuery('ul.leveltabs li.negativetab').hasClass('activetab')) {
		jQuery('a.action-to-negative').css('display', 'block');
		jQuery('a.action-to-positive').css('display', 'none');
	}

	if(jQuery('ul.leveltabs li.advancedtab').hasClass('activetab')) {
		jQuery('a.action-to-negative').css('display', 'block');
		jQuery('a.action-to-positive').css('display', 'block');
	}

}

jQuery(document).ready(m_levelsReady);
/* END-UNIT MODULES*/

jQuery(function() {
    jQuery(".spinners").spinner({
        min: 0
    });
    jQuery('.dateinput').datepicker({
        dateFormat: 'yy-mm-dd'
    });
    /*jQuery("#course-start-date").datepicker();
     jQuery("#course-end-date").datepicker();
     jQuery("#enrollment-start-date").datepicker();
     jQuery("#enrollment-end-date").datepicker();
     */
//getFullYear
//getDate
//getMonth

//var currentDate = $( ".selector" ).datepicker( "getDate" );

});
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
    })

    jQuery('#add-instructor-trigger').click(function() {
        var instructor_id = jQuery('#instructors option:selected').val();

        if (jQuery("#instructor_holder_" + instructor_id).length == 0) {
            jQuery('#instructors-info').append('<div class="instructor-avatar-holder" id="instructor_holder_' + instructor_id + '"><div class="instructor-remove"><a href="javascript:removeInstructor(' + instructor_id + ');"></a></div>' + instructor_avatars[instructor_id] + '<span class="instructor-name">' + jQuery('#instructors option:selected').text() + '</span></div><input type="hidden" id="instructor_' + instructor_id + '" name="instructor[]" value="' + instructor_id + '" />');
        }
    });

    jQuery(function() {
        jQuery("#students_accordion").accordion();
    });

});