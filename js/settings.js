function mp_chosen( $elm ) {
	$elm.chosen();
}

function mp_iCheck( $elm ) {
	$elm.iCheck({
		"checkboxClass" : "icheckbox_square-grey",
		"radioClass" : "iradio_square-grey"
	});	
}

function mp_tooltips( $elm ) {
	$elm.each(function(){
		var $this = jQuery(this);
		
		$this.qtip({
			"show" : {
				"event" : "click",
				"solo" : true
			},
			"hide" : {
				"event" : "click",
				"fixed" : true
			},
			"position" : {
				"my" : "left center",
				"at" : "right center",
				"adjust" : {
					"x" : -10
				}
			},
			"style" : "qtip-shadow",
			"content" : {
				"button" : true,
				"text" : $this.next('.mp-help-text')
			},
		});
	});
}

jQuery(document).ready(function($){
	/*var updateUserPreference = function(key, value) {
		var data = [
			{
				"name" : "key",
				"value" : key
			},{
				"name" : "value",
				"value" : value
			},{
				"name" : "action",
				"value" : "mp-update-user-preference"
			},{
				"name" : "mp_update_user_preference_nonce",
				"value" : MarketPress.updateUserPreferenceNonce
			}
		];
		
		$.post(ajaxurl, $.param(data));
	};*/
	/*
	var getUserPreference = function(key) {
		return MarketPress.userPrefs[key] === undefined ? false : MarketPress.userPrefs[key];
	};*/
	
	//Sticky navigation
	$('.mp-tabs').sticky({
		"topSpacing" : 45
	});
	
	//iCheck
	//mp_iCheck($('.mp-settings').find(':checkbox,:radio'));
	
	//Chosen
	mp_chosen($('.mp-settings').find('.mp-chosen-select').filter(':visible'));

	//Show accordion tooltip on first page view
	$firstHandle = $('.mp-postbox:first')
	/*if ( !getUserPreference('accordion_tooltip_dismissed') ) {
		$firstHandle.find('.hndle');
		$firstHandle.qtip({
			"content" : '',//MarketPress.accordionText,
			"overwrite" : false,
			"style" : "qtip-shadow",
			"position" : {
				"my" : "bottom right",
				"at" : "right top"
			},
			"show" : {
				"event" : false,
				"ready" : true,
			}
		});
	}*/
	
	//Don't show accordion tooltip again
	//updateUserPreference('accordion_tooltip_dismissed', 1);
	
	//Settings accordions
	$('.mp-settings').find('form').on('click', '.hndle', function(){
		var $this = $(this),
				$inside = $this.next('.inside'),
				$postbox = $this.closest('.mp-postbox'),
				id = $postbox.attr('id');
		
		if ( $inside.is(':hidden') ) {
			$inside.slideDown(250, function(){
				$postbox.removeClass('closed');
				$inside.find('.mp-chosen-select').filter(':visible').chosen();
			});
			updateUserPreference('accordion_state_' + id.substr(3), 'open');
		} else {
			$inside.slideUp(250, function(){
				$postbox.addClass('closed');
			});
			updateUserPreference('accordion_state_' + id.substr(3), 'closed');
			$firstHandle.qtip('toggle', false);
		}
	});
		
	//Inline-help tooltips
	mp_tooltips($('.mp-help-icon'));
		
	//Spinners
	$('.mp-spinner').spinner({
		"min" : 0,
		"numberFormat" : "n"
	});
	
	//Validate Forms
	$('#mp-main-form').validate({
		"errorPlacement" : function(error, element) {
			error.appendTo(element.closest('td'));
		}
	});	//settings form
});