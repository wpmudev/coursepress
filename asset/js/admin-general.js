/*! CoursePress - v2.2.1-beta.2
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2019; * Licensed GPLv2+ */
(jQuery(function() {

	// Make the left menu sticky.
	jQuery( '.sticky-tabs' ).sticky( { topSpacing: 45 } );

	/*
	***** General Admin Patterns *****
	 */

	(function() {
		function on_content_handle_click() {
			var el = jQuery( this ),
				box = el.closest( '.cp-content-box' );

			if ( box.hasClass('collapsed') ) {
				box.removeClass('collapsed');
			} else {
				box.addClass('collapsed');
			}
			on_content_handle_click_calculate();
		}

		/**
		 * calculate (un)fold buttons
		 */
		function on_content_handle_click_calculate() {
			var all = jQuery('.cp-content-box').length;
			var collapsed = jQuery('.cp-content-box.collapsed').length;
			if ( 0 == collapsed ) {
				jQuery( 'input.button.hndle-items-unfold' ).addClass('disabled');
			} else {
				jQuery( 'input.button.hndle-items-unfold' ).removeClass('disabled');
			}
			if ( all == collapsed ) {
				jQuery( 'input.button.hndle-items-fold' ).addClass('disabled');
			} else {
				jQuery( 'input.button.hndle-items-fold' ).removeClass('disabled');
			}
		}

		jQuery( '.cp-content-box h3.hndle' ).on( 'click', on_content_handle_click );
		jQuery( 'input.button.hndle-items-unfold' ).on( 'click', function() {
			jQuery('.cp-content-box').removeClass('collapsed');
			on_content_handle_click_calculate();
		} );
		jQuery( 'input.button.hndle-items-fold' ).on( 'click', function() {
			jQuery('.cp-content-box').addClass('collapsed');
			on_content_handle_click_calculate();
		} );
	}());

	jQuery(document).ready( function($) {
		/**
		 * Send certificate manually
		 */
		$('.student-profile').on( 'click', '.button.button-certificate-send', function() {
			var $thiz = $(this);
			if ( $thiz.hasClass('disabled') ) {
				return false;
			}
			data = {
				name: $('.course-title a', $thiz.closest('tr')).html(),
				id: $thiz.data('certificate-id')
			};
			$thiz.addClass('disabled').html( '<span><i class="fa fa-spinner fa-pulse"></i></span> ' + $thiz.data('label-sending') );
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: "certificate_send",
					id: $thiz.data("certificate-id"),
					_wpnonce: $thiz.data("nonce")
				},
				dataType: "json"
			}).done( function(data) {
				/**
				 * add message
				 */
				$('.notice.certificate-send').detach();
				$('.student-profile h1').after(data.message);
				/**
				 * remove display changes
				 */
				$('span', $thiz).detach();
				$thiz.removeClass('disabled').html($thiz.data('label-default'));
				window.setTimeout( function() { $('.notice.certificate-send').slideUp(); }, 3000 );
			});
			return false;
		});
		/**
		 * bind dismissible notices
		 */
		$('div.notice.is-dismissible[data-dismissible] button.notice-dismiss').click(function (event) {
			event.preventDefault();
			option_name = $(this).parent().attr('data-option-name');
			data = {
				'action': 'coursepress_dismiss_admin_notice',
				'option_name': option_name,
				'_wpnonce': $(this).parent().data('nonce'),
				'user_id': $(this).parent().data('user_id')
			};
			$.post(ajaxurl, data);
		});
	});

}));
