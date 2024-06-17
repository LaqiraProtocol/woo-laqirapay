'use strict';

(($) => {
  $(document).ready(() => {    

  //   $('form.checkout').on('change', 'input, select, textarea', function() {
  //     // Form data collection
  //     var formData = $('form.checkout').serialize();

  //     // Send an AJAX request to update the client session
  //     $.ajax({
  //         url: LaqiraData.orderData.laqiraAajaxUrl,
  //         type: 'POST',
  //         data: {
  //             action: 'update_checkout_session1',
  //             data: formData,
  //         },
  //         success: function(response) {
  //           //console.log(response);
  //             // if (response.success) {
  //             //     //console.log('Customer session updated successfully.');
  //             // } else {
  //             //     //console.log('Error updating customer session: ' + response.data);
  //             // }
  //         }
  //     });
  // });
    

    $("#wcLaqirapayApp").hide();
    if (window.location.pathname.includes('/checkout/order-pay/')) {
      $("#laqira_loder").remove();
      $("#wcLaqirapayApp").insertAfter("#startwooLaqiraPayApp");
      $("#wcLaqirapayApp").show();
    }
    
    $(document).ajaxComplete(function () {
      $("#laqira_loder").remove();
      $("#wcLaqirapayApp").insertAfter("#startwooLaqiraPayApp");
      $("#wcLaqirapayApp").show();

    });
  }); 
})(jQuery);
