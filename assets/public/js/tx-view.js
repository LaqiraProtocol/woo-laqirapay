jQuery(function($) {
    var $hashInput = $('#tx-hash-input');
    var $loadingIndicator = $('#loading-indicator');
    var $resultsContainer = $('#laqirapay-confirmation-table');

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function showError(message) {
        $resultsContainer.html('<p class="error">' + escapeHtml(message) + '</p>');
    }

    function renderResponse(data) {
        var payload = data || {};

        $resultsContainer.empty();

        if (payload.html) {
            $resultsContainer.html(payload.html);
        }

        if (payload.confirmation && payload.confirmation.markup) {
            var $markup = $(payload.confirmation.markup);
            $resultsContainer.append($markup);
            $(document).trigger('laqirapay:confirmation-ready', [ payload.confirmation, $markup ]);
        }
    }

    $('#verify-button').on('click', function() {
        var inputValue = $hashInput.val().trim();
        if (!inputValue) {
            showError(laqiraTxView.error_required || 'Please enter a transaction hash.');
            return;
        }

        var requestData = {
            action: laqiraTxView.action,
            input_value: inputValue,
            nonce: laqiraTxView.nonce
        };

        if (typeof laqiraTxView.order_id !== 'undefined' && laqiraTxView.order_id !== null) {
            requestData.order_id = laqiraTxView.order_id;
        }

        $loadingIndicator.show();

        $.ajax({
            url: laqiraTxView.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: requestData,
            success: function(response) {
                $loadingIndicator.hide();

                if (response && response.success) {
                    renderResponse(response.data);
                    return;
                }

                var message = (response && response.data && (response.data.message || response.data.error)) || laqiraTxView.error_generic || 'Unable to retrieve transaction details.';
                showError(message);
            },
            error: function() {
                $loadingIndicator.hide();
                showError(laqiraTxView.error_generic || 'Unable to retrieve transaction details.');
            }
        });
    });
});
