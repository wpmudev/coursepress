(function( $ ) {
	$( document ).ready( function() {
        $(document).on( 'change', '#select_course_id', function() {
            $(this).closest('form').submit();
        });
	});
})( jQuery );
