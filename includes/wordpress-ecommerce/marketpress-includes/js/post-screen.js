jQuery(document).ready(function($) {
  var formfield;

  //open thickbox when button is clicked
  $('#mp_upload_button').click(function() {
    formfield = $('#mp_file');
		tb_show('Upload A Product File', 'media-upload.php?TB_iframe=true');
		return false;
	});

	// user inserts file into post. only run custom if user started process using the above process
	// window.send_to_editor(html) is how wp would normally handle the received data
	window.original_send_to_editor = window.send_to_editor;
	window.send_to_editor = function(html){
		if (formfield) {
			fileurl = $(html).attr('href');
			$(formfield).val(fileurl);
      formfield = false;
			tb_remove();
		} else {
			window.original_send_to_editor(html);
		}
	};

  //remove formfield whenever thickbox is closed
  $('a.thickbox, #TB_overlay, #TB_imageOff, #TB_closeWindowButton, #TB_TopCloseWindowButton').click(function(){
    formfield = false;
  });

  //checkbox toggle sale field
  if($('#mp_is_sale').is(':checked')) {
    $('td.mp_sale_col input').removeAttr('disabled');
	} else {
    $('td.mp_sale_col input').attr('disabled', true);
  }
  $('#mp_is_sale').change(function() {
    if(this.checked) {
      $('td.mp_sale_col input').removeAttr('disabled');
		} else {
      $('td.mp_sale_col input').attr('disabled', true);
    }
	});

  //checkbox toggle inventory field
  if($('#mp_track_inventory').is(':checked')) {
    $('td.mp_inv_col input').removeAttr('disabled');
	} else {
    $('td.mp_inv_col input').attr('disabled', true);
  }
  $('#mp_track_inventory').change(function() {
    if(this.checked) {
      $('td.mp_inv_col input').removeAttr('disabled');
		} else {
      $('td.mp_inv_col input').attr('disabled', true);
    }
	});

	//variation name hiding
	function variation_check() {
    if($('.variation').size() > 1) {
      $('.mp_var_col').show();
		} else {
      $('.mp_var_col').hide();
    }

	// FPM: Custom Field Processing logic
	variation_custom_check();
	}
  variation_check();

	/* 
		When the user clicks the Add/Delete variation the table columns can switch from 5 to 6 column. This effects the 
		layout of the custom field row elements. So this function sets the colspan of the <td> as needed 
	*/
	
	function variation_custom_check() {
		if ( $('table#mp_product_variations_table th.mp_var_col').is(':visible') ) {
			$('table#mp_product_variations_table tr.variation-custom-field td.mp_custom_label_col').attr('colSpan', 6);
		} else {
			$('table#mp_product_variations_table tr.variation-custom-field td.mp_custom_label_col').attr('colSpan', 5);
		}
		
	}

	//setup del link html on load
	var var_del_html = $('.variation:last .mp_var_remove').html();
	if ($('.variation').size() == 1)
  	$('.variation:last .mp_var_remove a').remove();
  

	/* 
		On the Product Variation row we have added a checkbox to allow the admin to set a custom field. When
		the checkbox is set we reveal a hidden row beneath to current row. 
	*/
	function reg_variation_custom_field() {
		var parent_tr = $(this).parent().parent();
		var parent_custom_tr = $(parent_tr).next('tr.variation-custom-field');
		
		if ($(this).is(":checked")) {
			$('td', parent_tr).css('border-bottom-color', '#F9F9F9');
			$(parent_tr).next('tr.variation-custom-field').removeClass('variation-custom-field-hidden');			

		} else {
			$('td', parent_tr).css('border-bottom-color', '#DFDFDF');
			$(parent_tr).next('tr.variation-custom-field').addClass('variation-custom-field-hidden');			
		}		
		variation_custom_check();			
	}
	$('input.mp_has_custom_field').click(reg_variation_custom_field);

	//add new variation
  $('#mp_add_vars').click(function() {

    //var var_html = '<tr class="variation">' + $('.variation:last').html() + '</tr>';
    var var_html = '<tr class="variation">' + $('.variation:last').html() + '</tr><tr class="variation-custom-field">'+ $('.variation-custom-field:last').html() + '</tr>';
    $('.variation:last .mp_var_remove a').remove();
	
	//$('.variation:last').after(var_html);
	$('.variation-custom-field:last').after(var_html);
		
	//add back in remove link if missing
	if ($('.variation:last .mp_var_remove a'))
		$('.variation:last .mp_var_remove').html(var_del_html);

    variation_check();
    
    $('.variation:last .mp_var_col input').val('').focus();
    $('.variation:last .mp_sku_col input').val('');
    $('.variation:last .mp_var_col input').val('');
    
	$('tr.variation-custom-field:last').addClass('variation-custom-field-hidden');
    $('tr.variation-custom-field:last input.mp_custom_field_label').val('');
    $('tr.variation-custom-field:last select.mp_custom_field_type').val('');

	// We need to set the value for the checkboxes to the row number (0-based). This way when the form submits 
	// we know which row had the custom field. Otherwise we will have the wrong offset from PHP.
	var count_tr = $('table#mp_product_variations_table tr.variation').length;
	count_tr = count_tr-1;
	
	$('tr.variation:last input.mp_has_custom_field').attr('checked', false);
	$('tr.variation:last input.mp_has_custom_field').val(count_tr);

	// FPM: Handle show/hide of Custom Field row. 
	$('tr.variation:last input.mp_has_custom_field').click(reg_variation_custom_field);

    $('tr.variation-custom-field:last input.mp_custom_field_required').attr('checked', false);
    $('tr.variation-custom-field:last input.mp_custom_field_required').val(count_tr);

	variation_custom_check();
	
	//remove variation
   	reg_remove_variation();
		return false;
	});

	function reg_remove_variation() {
		//remove variation
	  $('.mp_var_remove a').click(function() {
	    $('.variation:last').remove();

		// 2.5.9.1: PaulM - Added for Custom Field row
	    $('.variation-custom-field:last').remove();

	    //add back in remove link if missing
			if ($('.variation').size() > 1 && $('.variation:last .mp_var_remove a'))
	      $('.variation:last .mp_var_remove').html(var_del_html);
	      
      variation_check();
	    reg_remove_variation();
			return false;
		});
	}
	reg_remove_variation();
	
	//toggle extra tax field
	$('#mp_is_special_tax').change(function() {
    if(this.checked) {
      $('#mp_special_tax').show();
		} else {
      $('#mp_special_tax').hide();
    }
	});
	
	//toggle the limit cart field
	$('#mp_track_limit').change(function(){
		if(this.checked) {
			$('#mp_limit').show();
		}else{
			$('#mp_limit').hide();
		}
	})
});