
function update_calendar( date, course_calendar ) {

    $ = jQuery;

    $.post(
        wpajaxurl, // declared by class.coursecalendar
        {
            action: 'refresh_course_calendar',
            course_id: $( course_calendar ).data( 'courseid' ),
            date: date,
        }
    ).done( function( data, status ) {

        // Set a course_id if its still empty
        var response = $.parseJSON( $( data ).find( 'response_data' ).text() );
        html = $.parseHTML( response.calendar );
        // console.log( course_calendar );
        $( course_calendar ).find( '.course-calendar-body' ).replaceWith( $( html ).find( '.course-calendar-body' ) );

        if ( $( html ).find( '.pre-month' ).data( 'date' ) == 'empty' ) {
            $( course_calendar ).find( '.pre-month' ).hide();
        } else {
            $( course_calendar ).find( '.pre-month' ).show();
        }

        if ( $( html ).find( '.next-month' ).data( 'date' ) == 'empty' ) {
            $( course_calendar ).find( '.next-month' ).hide();
        } else {
            $( course_calendar ).find( '.next-month' ).show();
        }

        $( course_calendar ).find( '.pre-month' ).data( 'date', $( html ).find( '.pre-month' ).data( 'date' ) );
        $( course_calendar ).find( '.next-month' ).data( 'date', $( html ).find( '.next-month' ).data( 'date' ) );

    } ).fail( function( data ) {
    } );



}



jQuery( document ).ready( function( $ ) {

    if ( $( '.pre-month' ).data( 'date' ) == 'empty' ) {
        $( '.pre-month' ).hide();
    }

    if ( $( '.next-month' ).data( 'date' ) == 'empty' ) {
        $( '.next-month' ).hide();
    }

    $( '.course-calendar .pre-month' ).live( 'click', function( event ) {
        event.stopPropagation();
        update_calendar( $( this ).data( 'date' ), $( this ).parents( '.course-calendar' )[0] );
    } );

    $( '.course-calendar .next-month' ).live( 'click', function( event ) {
        event.stopPropagation();
        update_calendar( $( this ).data( 'date' ), $( this ).parents( '.course-calendar' )[0] );
    } );

} );