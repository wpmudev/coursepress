// AjaxQ jQuery Plugin
// Copyright (c) 2012 Foliotek Inc.
// MIT License
// https://github.com/Foliotek/ajaxq
(function($){var queues={};$.ajaxq=function(qname,opts){if(typeof opts==="undefined"){throw ("AjaxQ: queue name is not provided")}var deferred=$.Deferred(),promise=deferred.promise();promise.success=promise.done;promise.error=promise.fail;promise.complete=promise.always;var clonedOptions=$.extend(true,{},opts);enqueue(function(){var jqXHR=$.ajax.apply(window,[clonedOptions]).always(dequeue);jqXHR.done(function(){deferred.resolve.apply(this,arguments)});jqXHR.fail(function(){deferred.reject.apply(this,arguments)})});return promise;function enqueue(cb){if(!queues[qname]){queues[qname]=[];cb()}else{queues[qname].push(cb)}}function dequeue(){if(!queues[qname]){return}var nextCallback=queues[qname].shift();if(nextCallback){nextCallback()}else{delete queues[qname]}}};$.each(["getq","postq"],function(i,method){$[method]=function(qname,url,data,callback,type){if($.isFunction(data)){type=type||callback;callback=data;data=undefined}return $.ajaxq(qname,{type:method==="postq"?"post":"get",url:url,data:data,success:callback,dataType:type})}});var isQueueRunning=function(qname){return queues.hasOwnProperty(qname)};var isAnyQueueRunning=function(){for(var i in queues){if(isQueueRunning(i)){return true}}return false};$.ajaxq.isRunning=function(qname){if(qname){return isQueueRunning(qname)}else{return isAnyQueueRunning()}};$.ajaxq.clear=function(qname){if(!qname){for(var i in queues){if(queues.hasOwnProperty(i)){delete queues[i]}}}else{if(queues[qname]){delete queues[qname]}}}})(jQuery);

/**** MarketPress Ajax JS *********/
jQuery(document).ready(function($) {
	//empty cart
	function mp_empty_cart() {
		$(document).on('click', 'a.mp_empty_cart', function(e) {
			e.preventDefault();
			
			var answer = confirm(MP_Ajax.emptyCartMsg);
			if (answer) {
				$(this).html('<img src="'+MP_Ajax.imgUrl+'" />');
				$.post(MP_Ajax.ajaxUrl, {action: 'mp-update-cart', empty_cart: 1}, function(data) {
					$("div.mp_cart_widget_content").html(data);
				});
			}
			return false;
		});
	}
	
	//add item to cart
	function mp_cart_listeners() {
		$(document).on('submit', '.mp_buy_form:has(input[name="action"])', function(e) {
			e.preventDefault();
			
			var $formElm = $(this),
					tempHtml = $formElm.html(),
					serializedForm = $formElm.serialize();
			
			$formElm.html('<img src="' + MP_Ajax.imgUrl + '" alt="' + MP_Ajax.addingMsg + '" />');
			
			// we use the AjaxQ plugin here because we need to queue multiple add-to-cart requests http://wp.mu/96f
			$.ajaxq('addtocart', {
				"data" : serializedForm,
				"dataType" : "html",
				"type" : "POST",
				"url" : MP_Ajax.ajaxUrl,
			})
			
			//callback when item is successfully added to cart
			.success(function(data){
				var result = data.split('||', 2);
				if (result[0] == 'error') {
					alert(result[1]);
					$formElm.html(tempHtml);
				} else {
					$formElm.html('<span class="mp_adding_to_cart">' + MP_Ajax.successMsg + '</span>');
					$("div.mp_cart_widget_content").html(result[1]);
					if (result[0] > 0) {
						$formElm.fadeOut(2000, function(){
							$formElm.html(tempHtml).fadeIn('fast');
						});
					} else {
						$formElm.fadeOut(2000, function(){
							$formElm.html('<span class="mp_no_stock">' + MP_Ajax.outMsg + '</span>').fadeIn('fast');
						});
					}
				}
			})
			
			//callback when an error occurs while adding an item to the cart
			.error(function(){
				alert(MP_Ajax.addToCartErrorMsg);
			});
		});
	}
	
	//general store listeners (e.g. pagination, etc)
	function mp_store_listeners(){
		// on next/prev link click, get page number and update products
		$(document).on('click', '#mp_product_nav a', function(e){
			e.preventDefault();
			
			var hrefParts = $(this).attr('href').split('#'),
					qs = parse_query(hrefParts[1]);
			
			console.log($('.mp_product_list_refine').serialize());
			get_and_insert_products($('.mp_product_list_refine').serialize() + '&page=' + qs['page']);
		});
	}

	// get products via ajax, insert into DOM with new pagination links
	function get_and_insert_products(query_string){
		ajax_loading(true);
		$.post(MP_Ajax.ajaxUrl, 'action=get_products_list&' + query_string, function(data) {
			var qs = parse_query(query_string),
					hash = 'product_category=' + qs['product_category'] + '&order=' + $('select[name="order"]').val() + '&page=' + qs['page'];
			
			ajax_loading(false);
			$('#mp_product_nav').remove();
			$('#mp_product_list').first().replaceWith(data.products);
			location.hash = hash;

			// scroll to top of list
			var pos = $('a[name="mp-product-list-top"]').offset();
			$('body,html').animate({ scrollTop: pos.top - 20 });
	  });
	}
	
	// parse querystring into variables
	function parse_query(query_string){
		var vars = [],
				pairs = query_string.split('&');
		
		for ( i = 0; i < pairs.length; i++ ) {
			var tmp = pairs[i].split('=');
			vars[tmp[0]] = tmp[1];
		}
		
		return vars;
	}

	// if the page has been loaded from a bookmark set the current state for select elements
	function update_dropdown_state(query_string){
		var query = parse_query(query_string);
		for(name in query){
			$('select[name="'+name+'"]').val(query[name]);
		}
	}

	// show loading ui when waiting for ajax response
	function ajax_loading(loading){
		$('#mp_product_list .mp_ajax_loading').remove();
		if(loading){
			$('#mp_product_list').prepend('<div class="mp_ajax_loading"></div>');
		}
	}

	// Ajax for products view.
	function mp_ajax_products_list(){
		$('.mp_product_list_refine').show(); // hide for non JS users

		// if hash tag contains a state, update view
		// hash tags are used to store the current state in these situations:
		//	a) when the user views a product and then clicks back
		//	b) viewing the URL from a bookmark
		if( /product_category|order|page/.test(location.hash) ){
				var query_string = location.hash.replace('#', '');
				
				if ( MP_Ajax.productCategory != '' )
					query_string += '&product_category=' + MP_Ajax.productCategory;
				
				var $perPage = $('.mp_list_filter').find('[name="per_page"]');
				if ( $perPage.length )
					query_string += '&per_page=' + $perPage.val();
					
				get_and_insert_products(query_string);
				update_dropdown_state(query_string);
		}

		$(".mp_list_filter select").not('#product-category').change(function(){
				get_and_insert_products( $('.mp_product_list_refine').serialize() );
		});
		
		$('.mp_list_filter').on('change', '#product-category', function(){
			var thehash = location.hash,
			
			thehash = thehash.replace(/page=(\d)*/, 'page=1'); //when changing categories set page=1
			
			window.location.href = MP_Ajax.links[$(this).val()] + thehash;
		});
	}

	//add listeners
	mp_empty_cart();
	mp_cart_listeners();
	mp_store_listeners();

	if( MP_Ajax.showFilters == 1 ){
		mp_ajax_products_list();
	}

});

