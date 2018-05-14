/*! CoursePress - v2.1.6-beta.3
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2018; * Licensed GPLv2+ */
(jQuery(function() {
	jQuery(document).ready( function($) {
		var coursepress_upgrade_form = $('form#coursepress-update-courses-form');
		var coursepress_upgrade_holder = $('#coursepress-updater-holder');
		var coursepress_upgrade_original = coursepress_upgrade_holder.html();
		var coursepress_upgrade_spinner = '<p class="working"><span><i class="fa fa-spinner fa-pulse"></i></span> ' + coursepress_upgrade_form.data('label-working') + '</p>';
		/**
		 * Do not process if ther is no upgrade form.
		 */
		if ( 0 === coursepress_upgrade_form.length ) {
			return;
		}
		/**
		 * handle button click
		 */
		$('.button').on( 'click', coursepress_upgrade_form, function() {
			var $thiz = $(this);
			var course_id = $('input[name=course]', coursepress_upgrade_form ).val();
			input_data = {
				action: "coursepress_upgrade_update",
				user_id: $("input[name=user_id]").val(),
				_wpnonce: $("input[name=_wpnonce]").val(),
				_wp_http_referer: $("input[name=_wp_http_referer]").val(),
				course_id: "",
				section: ""
			};
			coursepress_upgrade_holder.html( coursepress_upgrade_spinner );
			coursepress_upgrade_course( course_id , false, input_data );
			$( ".working", coursepress_upgrade_holder ).detach();
			return false;
		});
		function coursepress_upgrade_course( course_id, section, input_data) {
			coursepress_upgrade_holder.append( coursepress_upgrade_spinner );
			input_data.course_id = course_id;
			input_data.section = section;
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: input_data,
				dataType: "json"
			}).done( function(data) {
				if ( data.success ) {
					$( ".working", coursepress_upgrade_holder ).detach();
					coursepress_upgrade_holder.append( data.message );
					if ( "undefined" == typeof( data.course_id ) || "stop" == data.course_id ) {
						coursepress_upgrade_holder.append( coursepress_upgrade_form.data('label-done') );
						return false;
					} else {
						coursepress_upgrade_course( data.course_id, data.section, input_data );
					}
				}
			}).fail( function( data ) {
				coursepress_upgrade_holder.append( coursepress_upgrade_form.data('label-fail') );
			})
		}
	});
}));
