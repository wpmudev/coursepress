/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CommentReply', function( $, doc ) {
        var editLink;

        // Recreate comment-reply js
        editLink = function() {
            var link = $(this),
               // datacom = link.parents( '[data-comid]' ).first(),
                com_id = link.data( 'comid' ),
                module_content = link.parents( '.course-module-step-question' ).first(),
                form = $( '#respond', module_content ),
                comment_div = $( '#comment-' + com_id ),
                comment_parent = $( '[name="comment_parent"]', form ),
                tempDiv = $( '.cp-temp-div' ),
                cancel_link = form.find( '#cancel-comment-reply-link' )
            ;

            // Add marker to the original form position
            if ( 0 === tempDiv.length ) {
                tempDiv = $( '<div class="cp-temp-div"></div>' ).insertAfter( form );
            }

            comment_parent.val( com_id );
            form.hide();
            comment_div.append( form.slideDown() );

            cancel_link.off( 'click' );
            cancel_link.show().on( 'click', function() {
                form.insertBefore( tempDiv );
                cancel_link.hide();
                tempDiv.remove();

                return false;
            });

            // Focus to the form
            _.focus( form );
            // Focus to textarea
            form.find( 'textarea[name="comment"]' ).focus();

            return false;
        };

        $(doc).on( 'click', '.comment-reply-link', editLink );
    });
})();