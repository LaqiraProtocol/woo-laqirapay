jQuery(function($){
    $('#verify-button').on('click', function(){
        var inputValue = $('#tx-hash-input').val();
        var button = $(this);
        var message = $('#confirmation_result_area');
        button.prop('disabled', true).text('...');
        message.text(' verifing ...');
        $.ajax({
            url: laqiraTxRepair.ajax_url,
            type: 'POST',
            data: {
                action: laqiraTxRepair.action,
                input_value: inputValue,
                order_id: laqiraTxRepair.order_id,
                nonce: laqiraTxRepair.nonce
            },
            success: function(response){
                if(response.data && response.data.result === 'success'){
                    window.location.replace(response.data.redirect);
                    return;
                }
                button.prop('disabled', false).text(laqiraTxRepair.retry_text);
                if(response.data && response.data.message){
                    message.html(response.data.message);
                } else {
                    message.text('');
                }
            },
            error: function(){
                button.prop('disabled', false).text(laqiraTxRepair.retry_text);
            }
        });
    });
});
