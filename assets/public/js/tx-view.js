jQuery(function($){
    $('#verify-button').on('click', function(){
        var inputValue = $('#tx-hash-input').val().trim();
        if (!inputValue) {
            $('#laqirapay-confirmation-table').html('<p class="error">Please enter a transaction hash.</p>');
            return;
        }
        $('#loading-indicator').show();
        $.ajax({
            url: laqiraTxView.ajax_url,
            type: 'POST',
            data: {
                action: laqiraTxView.action,
                input_value: inputValue,
                order_id: laqiraTxView.order_id,
                nonce: laqiraTxView.nonce
            },
            success: function(response){
                $('#laqirapay-confirmation-table').html(response);
                $('#loading-indicator').hide();
            },
            error: function(){
                $('#loading-indicator').hide();
                $('#laqirapay-confirmation-table').html('<p class="error">Unable to retrieve transaction details.</p>');
            }
        });
    });
});
