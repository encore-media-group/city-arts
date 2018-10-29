jQuery(document).ready(function($){
	// Add ColorPicker.
	if ($('.woocommerce-gift-coupon-color').length > 0) {
  	$('.woocommerce-gift-coupon-color').wpColorPicker();
	}
  // Selector visibility.
  var selectors = { 
  	woocommerce_gift_coupon_title_type: 'woocommerce_gift_coupon_title_h', 
  	woocommerce_gift_coupon_info_paragraph_type: 'woocommerce_gift_coupon_info_paragraph', 
  };
  $.each( selectors, function( key, value ) {
  	if ($('#'+key).length > 0) {
  		//Initial
  		if ($('#'+key)[0]) {
		  	if ($('#'+key)[0].value > 0) {
				  $('#wp-'+value+'-wrap').hide();
				}
			}
	  	//Onchage
			if ($('#wp-'+value+'-wrap').length > 0) {
		  	$('#'+key).change(function() {
			    if ($(this).val() > 0) {
			    	$('#wp-'+value+'-wrap').hide();
			    }
			    else {
			    	$('#wp-'+value+'-wrap').show();
			    }
				});
	  	}
  	}
	});
});