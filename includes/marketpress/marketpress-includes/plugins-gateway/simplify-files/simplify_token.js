/*
 * Copyright (c) 2013, MasterCard International Incorporated
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, are 
 * permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of 
 * conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its 
 * contributors may be used to endorse or promote products derived from this software 
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES 
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT 
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER 
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING 
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF 
 * SUCH DAMAGE.
 */
(function($){

	function simplifyResponseHandler(data) {
		$(".error").remove();
		if(data.error) {
			if(data.error.code == "validation") {
				var fieldErrors = data.error.fieldErrors,
						fieldErrorsLength = fieldErrors.length;
				$('#cc-cvc, #cc-number, #cc-exp-month, #cc-exp-year').css('box-shadow', 'none');
				for (var i = 0; i < fieldErrorsLength; i++) {
					if(fieldErrors[i].field == 'card.cvc') {
						$('#cc-cvc').css('box-shadow', '0px 0px 5px red');
					} else if(fieldErrors[i].field == 'card.number') {
						$('#cc-number').css('box-shadow', '0px 0px 5px red');
					} else if(fieldErrors[i].field == 'card.expMonth') {
						$('#cc-exp-month').css('box-shadow', '0px 0px 5px red');
					} else if(fieldErrors[i].field == 'card.expYear') {
						$('#cc-exp-year').css('box-shadow', '0px 0px 5px red');
					}
				}
			}
			$("#mp_payment_confirm").removeAttr("disabled");
		} else {
			var token = data["id"];
			$("#mp_payment_form").append("<input type='hidden' name='simplifyToken' value='" + token + "' />");
			$("#mp_payment_form").get(0).submit();
		}
	}
	
	$(document).ready(function() {
		$("#mp_payment_form").on("submit", function(event) {
			var $this = $(this),
					$gateway = $this.find('.mp_choose_gateway');
			
			if ( $gateway.length && $gateway.filter(':checked').val() != 'simplify' )
				//simplify gateway is not checked so let's bail to prevent conflicts with other gateways
				return;
			
			event.preventDefault();
			
			$("#mp_payment_confirm").attr("disabled", "disabled");
			
			SimplifyCommerce.generateToken({
				"key" : simplify.publicKey,
				"card" : {
					"number" : $("#cc-number").val(),
					"cvc" : $("#cc-cvc").val(),
					"expMonth" : $("#cc-exp-month").val(),
					"expYear" : $("#cc-exp-year").val()
				}
			}, simplifyResponseHandler);
		});
	});
	
}(jQuery));