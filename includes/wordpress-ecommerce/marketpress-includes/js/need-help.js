jQuery(document).ready(function($) {
	$('div.wrap').prepend('<a id="mp-need-help" href="#"></a>');
	var offset = $('#contextual-help-link').offset();
	var new_top = offset.top;
	$('#mp-need-help').css('top', new_top+'px');
	$('#mp-need-help').click(function(){
		$('#contextual-help-link').click();
		$(this).remove();
		$.post(ajaxurl, {action: 'mp-hide-help'});
		return false;
	});
	$('#contextual-help-link').click(function(){
		$('#mp-need-help').remove();
		$.post(ajaxurl, {action: 'mp-hide-help'});
	});
});