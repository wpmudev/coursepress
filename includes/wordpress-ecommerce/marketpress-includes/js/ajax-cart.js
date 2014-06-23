/**** MarketPress Ajax JS *********/
jQuery( document ).ready( function( $ ) {
	//empty cart
	function mp_empty_cart() {
		if ( $( "a.mp_empty_cart" ).attr( "onClick" ) != undefined ) {
			return;
		}

		$( "a.mp_empty_cart" ).click( function() {
			var answer = confirm( MP_Ajax.emptyCartMsg );
			if ( answer ) {
				$( this ).html( '<img src="'+MP_Ajax.imgUrl+'" />' );
				$.post( MP_Ajax.ajaxUrl, {action: 'mp-update-cart', empty_cart: 1}, function( data ) {
					$( "div.mp_cart_widget_content" ).html( data );
				} );
			}
			return false;
		} );
	}
	
	//add item to cart
	function mp_cart_listeners() {
		$( "input.mp_button_addcart" ).click( function( e ) {
			e.preventDefault();
			
			var input = $( this );
			var formElm = $( input ).parents( 'form.mp_buy_form' );
			var tempHtml = formElm.html();
			var serializedForm = formElm.serialize();
			formElm.html( '<img src="'+MP_Ajax.imgUrl+'" alt="'+MP_Ajax.addingMsg+'" />' );
			$.post( MP_Ajax.ajaxUrl, serializedForm, function( data ) {
				var result = data.split( '||', 2 );
				if ( result[0] == 'error' ) {
					alert( result[1] );
					formElm.html( tempHtml );
					mp_cart_listeners();
				} else {
					formElm.html( '<span class="mp_adding_to_cart">'+MP_Ajax.successMsg+'</span>' );
					$( "div.mp_cart_widget_content" ).html( result[1] );
					if ( result[0] > 0 ) {
						formElm.fadeOut( 2000, function() {
							formElm.html( tempHtml ).fadeIn( 'fast' );
							mp_cart_listeners();
						} );
					} else {
						formElm.fadeOut( 2000, function() {
							formElm.html( '<span class="mp_no_stock">'+MP_Ajax.outMsg+'</span>' ).fadeIn( 'fast' );
							mp_cart_listeners();
						} );
					}
					mp_empty_cart(); //re-init empty script as the widget was reloaded
				}
			} );
		} );
	}
	
	//general store listeners ( e.g. pagination, etc )
	function mp_store_listeners() {
		// on next/prev link click, get page number and update products
		$( document ).on( 'click', '#mp_product_nav a', function( e ) {
			e.preventDefault();
			
			var m = $( this ).attr( 'href' ).match( /( paged=|page\/ )( \d+ )/ );
			var nw_page = m != null ? m[2] : 1;
			get_and_insert_products( $( '.mp_product_list_refine' ).serialize() + '&page=' + nw_page );

			// scroll to top of list
			var pos = $( '.mp_list_filter' ).offset();
			$( 'body' ).animate( { scrollTop: pos.top-10 } );
			mp_cart_listeners();
		} );
	}

	// get products via ajax, insert into DOM with new pagination links
	function get_and_insert_products( query_string ) {
			ajax_loading( true );
			$.post(
				MP_Ajax.ajaxUrl, 
				'action=get_products_list&'+query_string,
				function( data ) {
					var hash = 'order=' + $( 'select[name="order"]' ).val();
					var m = query_string.match( /page=( \d )+/i );
					
					if ( m != null ) {
						hash += '&page=' + m[1];	
					}
					
					ajax_loading( false );
					$( '#mp_product_nav' ).remove();
					$( '#mp_product_list' ).first().replaceWith( data.products );
					location.hash = hash;
					mp_cart_listeners();
				}
			);
	}

	// if the page has been loaded from a bookmark set the current state for select elements
	function update_dropdown_state( query_string ) {
		var query = JSON.parse( '{"' + decodeURI( query_string.replace( /&/g, "\",\"" ).replace( /=/g,"\":\"" ) ) + '"}' );
		for( name in query ) {
			$( 'select[name="'+name+'"]' ).val( query[name] );
		}
	}

	// show loading ui when waiting for ajax response
	function ajax_loading( loading ) {
		$( '#mp_product_list .mp_ajax_loading' ).remove();
		if( loading ) {
			$( '#mp_product_list' ).prepend( '<div class="mp_ajax_loading"></div>' );
		}
	}

	// Ajax for products view.
	function mp_ajax_products_list() {
		$( '.mp_product_list_refine' ).show(); // hide for non JS users

		// if hash tag contains a state, update view
		// hash tags are used to store the current state in these situations:
		//	a ) when the user views a product and then clicks back
		//	b ) viewing the URL from a bookmark
		if( /filter-term|order|paged/.test( location.hash ) ) {
				var query_string = location.hash.replace( '#', '' );
				
				if ( MP_Ajax.productCategory != '' )
					query_string += '&product_category=' + MP_Ajax.productCategory;
				
				get_and_insert_products( query_string );
				update_dropdown_state( query_string );
		}

		$( ".mp_list_filter select" ).not( '#product-category' ).change( function() {
				get_and_insert_products( $( '.mp_product_list_refine' ).serialize() );
		} );
		
		$( '#product-category' ).change( function() {
			window.location.href = MP_Ajax.links[$( this ).val()] + location.hash;
		} );
	}

	//add listeners
	mp_empty_cart();
	mp_cart_listeners();
	mp_store_listeners();

	if( MP_Ajax.showFilters == 1 ) {
		mp_ajax_products_list();
	}

} );

