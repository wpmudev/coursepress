(jQuery(function() {

	jQuery(document).ready( function($) {
		var $form = $('form#coursepress-update-courses-form');
		var $holder = $('#coursepress-updater-holder');
		/**
		 * Send certificate manually
		 */
		$('.button').on( 'click', $form, function() {
			var $thiz = $(this);
			var $ids = [];
			var done = 0;
			$("input.course", $form).each( function() {
				$ids.push( $(this).val() );
			});
			if ( 0 === $ids.length ) {
			$holder.html( '<p>' + $form.data('label-empty-list') + '</p>' );
				return false;
			}
			data = {
				action: "coursepress_upgrade_update",
				user_id: $("input[name=user_id]").val(),
				_wpnonce: $("input[name=_wpnonce]").val(),
				_wp_http_referer: $("input[name=_wp_http_referer]").val(),
				course_id: 0
			};
			$holder.html( '<p class="working"><span><i class="fa fa-spinner fa-pulse"></i></span> ' + $form.data('label-working') + '</p>' );
			$.each( $ids, function( index, value ) {
				data.course_id = value;
				$.ajax({
					type: "POST",
					url: ajaxurl,
					data: data,
					dataType: "json"
				}).done( function(data) {
					done++;
					if ( data.success ) {
						$holder.html( $holder.html() + '<p>' + data.message + '</p>' );
					}
					if ( done === $ids.length ) {
						$( ".working", $holder ).detach();
					}
				});
			});
			return false;
		});
	});

}));
