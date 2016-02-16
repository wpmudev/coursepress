/*jslint browser: true*/
/*global wp*/
/*global jQuery*/

var CoursePress = CoursePress || {};

(function ( $ ) {


    $( document ).ready( function ( $ ) {

        // Make the left menu sticky
        $( ".sticky-tabs" ).sticky( { topSpacing: 45 } );


        /*
         Certificate Background Image
         */
        $( '.certificate_background_button' ).on( 'click', function()
        {
            var target_url_field = $( this ).prevAll( ".certificate_background_url:first" );
            wp.media.string.props = function( props, attachment )
            {
                $( target_url_field ).val( props.url );

                if ( CoursePress.utility.valid_media_extension( attachment.url, target_url_field ) ) {//extension is allowed
                    $( target_url_field ).removeClass( 'invalid_extension_field' );
                    $( target_url_field ).parent().find( '.invalid_extension_message' ).hide();
                } else {//extension is not allowed
                    $( target_url_field ).addClass( 'invalid_extension_field' );
                    $( target_url_field ).parent().find( '.invalid_extension_message' ).show();
                }
            };

            wp.media.editor.send.attachment = function( props, attachment )
            {
                $( target_url_field ).val( attachment.url );
                if ( CoursePress.utility.valid_media_extension( attachment.url, target_url_field ) ) {//extension is allowed
                    $( target_url_field ).removeClass( 'invalid_extension_field' );
                    $( target_url_field ).parent().find( '.invalid_extension_message' ).hide();
                } else {//extension is not allowed
                    $( target_url_field ).addClass( 'invalid_extension_field' );
                    $( target_url_field ).parent().find( '.invalid_extension_message' ).show();
                }
            };

            wp.media.editor.open( this );
            return false;
        } );


    } );


})( jQuery );