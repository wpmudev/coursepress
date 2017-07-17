(function( $ ) {
	$( document ).ready( function() {
		$(document).on( 'change', '#select_course_id', function() {
			$(this).closest('form').submit();
		})
		.on ( 'click', '#cp-search-clear', function() {
			var s = $('[name=s]', $(this).parent());
			if ( '' != s.val() ) {
				s.val('');
				$(this).closest('form').submit();
			}
		});
	});
})( jQuery );
