jQuery(document).ready(function($) {
    /* Signup */
    $('button.apply-button.signup, .cp_signup_step').live('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        open_popup('signup', $(this).attr('data-course-id'));
    });
    
    /* Login */
    
    $('.cp_login_step').live('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        open_popup('login', $(this).attr('data-course-id'));
    });
   
    $('.cp_popup_close_button').click(function(e) {//.cp_popup_overall, 
        close_popup();
    });

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
				if( response ) {
					console.log( response );
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
		this.css( 'position', 'fixed' );
		this.css( 'top', ( $( window ).height() / 2 ) - ( this.outerHeight() / 2 ) );
	    this.css( 'left', ( $( window ).width() / 2 ) - ( this.outerWidth() / 2 ) );
	    return this;
	}

	// Extend jQuery with $.autoHeight() function to adjust the height of an element to its contents.
	jQuery.fn.autoHeight = function() {
		var new_height = $($( this ).find('*').last()).position().top + $($( this ).find('*').last()).outerHeight();
		this.css( 'height', new_height );
		return this;
	}
	
	// When the window scrolls, make sure we keep the popup in the center.
	$( window ).resize( function() {
		$('.cp_popup_window').center();
		$(".cp_popup_overall").height($(document).height());
	});
	
	$(".cp_popup_overall").height($(document).height());
	
});
