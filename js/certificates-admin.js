jQuery( document ).ready( function( $ ) {

    $( ".rows" ).sortable( {
        items: 'ul',
        receive: function( template, ui ) {
            update_rows();
        },
        stop: function( template, ui ) {
            update_rows();
        }
    } );

    var template_classes = new Array();
    var parent_id = 0;

    $( ".draggable li" ).draggable( {
        helper: "clone",
        connectToSortable: ".certificate-layout ul.sortables"
    } );

    /*$( "#side-sortables ul.sortables" ).sortable( {
     connectWith: 'ul',
     forcePlaceholderSize: true,
     helper: "clone",
     //placeholder: "ui-state-highlight",
     receive: function( template, ui ) {
     },
     } );*/

    $( ".certificate-layout ul.sortables" ).sortable( {
        connectWith: 'ul',
        forcePlaceholderSize: true,
        //placeholder: "ui-state-highlight",
        receive: function( template, ui ) {
            $( ".rows ul li" ).last().addClass( "last_child" );

            var $this = $( this );

            if ( $this.children( 'li' ).length > 4 ) {
                alert( certificate.max_elements_message );
                $( this ).data().uiSortable.currentItem.remove();
            }

            update_li();
        },
        stop: function( template, ui ) {
            update_li();
            $( ".rows ul li" ).last().addClass( "last_child" );
        }
    } ).disableSelection();

    $( ".sortables" ).disableSelection();


    function update_rows() {
        $( ".rows ul" ).each( function( index ) {
            $( this ).attr( 'id', 'row_' + ( index + 1 ) );
            $( this ).find( '.rows_classes' ).attr( 'name', 'rows_' + ( index + 1 ) + '_post_meta' );
        } );
    }

    function update_li( ) {

        var children_num = 0;
        var current_child_num = 0;

        $( ".rows ul" ).each( function() {

            template_classes.length = 0; //empty the array

            children_num = $( this ).children( 'li' ).length;

            $( this ).children( 'li' ).removeClass();
            $( this ).children( 'li' ).addClass( "ui-state-default" );
            $( this ).children( 'li' ).addClass( "cols cols_" + children_num );
            $( this ).children( 'li' ).last().addClass( "last_child" );
            $( this ).find( 'li' ).each( function( index, element ) {
                if ( $.inArray( $( this ).attr( 'data-class' ), template_classes ) == -1 ) {
                    template_classes.push( $( this ).attr( 'data-class' ) );
                }
            } );
            $( this ).find( '.rows_classes' ).val( template_classes.join() );
        } );
        cp_fix_template_elements_sizes()
    }

    function cp_fix_template_elements_sizes() {
        $( ".rows ul" ).each( function() {
            var maxHeight = -1;

            $( this ).find( 'li' ).each( function() {
                $( this ).removeAttr( "style" );
                maxHeight = maxHeight > $( this ).height() ? maxHeight : $( this ).height();
            } );

            $( this ).find( 'li' ).each( function() {
                $( this ).height( maxHeight );
            } );
        } );

        $( "#side-sortables .sortables li" ).each( function() {
            $( this ).height( 'auto' );
        } );
    }

    update_li();

    cp_fix_template_elements_sizes();

    $( window ).resize( function() {
        cp_fix_template_elements_sizes();
    } );


    /* Native WP media browser for file module (for instructors) */
    $( document.body ).on('click', '.file_url_button', function()
    {
        var target_url_field = jQuery( this ).prevAll( ".file_url:first" );
        wp.media.editor.send.attachment = function( props, attachment )
        {
            $( target_url_field ).val( attachment.url );
        };
        wp.media.editor.open( this );
        return false;
    } );

} );