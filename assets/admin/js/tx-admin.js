jQuery(function($) {
    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function buildPayload(config, $markup) {
        var payload = $.extend({}, config.request || {});

        if (config.action && !payload.action) {
            payload.action = config.action;
        }

        if (!payload.orderID) {
            var orderId = $markup.find('#order_id_input').val();
            if (orderId) {
                payload.orderID = orderId;
            }
        }

        if (!payload.txHash) {
            var txHash = $markup.find('#tx_hash_input').val();
            if (txHash) {
                payload.txHash = txHash;
            }
        }

        if (!payload.nonce) {
            var nonce = $markup.find('#nonce_input').val();
            if (nonce) {
                payload.nonce = nonce;
            }
        }

        return payload;
    }

    function handleConfirmation(config, $markup) {
        var $button = $markup.find('#do-confirm-button');
        var $loading = $markup.find('#loading-indicator-bottom');
        var $feedback = $markup.find('#laqirapay-after-confirmation-action');

        $loading.hide();

        $button.off('click.laqirapay').on('click.laqirapay', function() {
            var requestData = buildPayload(config, $markup);

            if (!requestData.action) {
                $feedback.html('<p class="error">' + escapeHtml(laqiraTxAdmin.genericError) + '</p>');
                return;
            }

            $loading.show();
            $feedback.empty();

            $.ajax({
                url: laqiraTxView.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: requestData,
                success: function(response) {
                    $loading.hide();

                    if (response && response.success && response.data) {
                        var data = response.data;

                        if ($('#adminmenumain').length < 1 && data.redirect) {
                            window.location.replace(data.redirect);
                            return;
                        }

                        if (data.admin_result) {
                            $feedback.html(data.admin_result);
                            $('#lqr-recover-order-result').hide();
                            $button.hide();
                            return;
                        }

                        if (data.message) {
                            $feedback.html(data.message);
                            return;
                        }

                        $feedback.html('<p class="updated">' + escapeHtml(laqiraTxAdmin.successMessage) + '</p>');
                        return;
                    }

                    var errorMessage = (response && response.data && (response.data.message || response.data.error)) || laqiraTxAdmin.genericError;
                    $feedback.html('<p class="error">' + escapeHtml(errorMessage) + '</p>');
                },
                error: function() {
                    $loading.hide();
                    $feedback.html('<p class="error">' + escapeHtml(laqiraTxAdmin.genericError) + '</p>');
                }
            });
        });
    }

    $(document).on('laqirapay:confirmation-ready', function(event, config, $markup) {
        if (!config || !config.markup) {
            return;
        }

        var $renderedMarkup = $markup;

        if (!$renderedMarkup || !$renderedMarkup.length) {
            $renderedMarkup = $(config.markup);
            $('#laqirapay-confirmation-table').append($renderedMarkup);
        }

        handleConfirmation(config, $renderedMarkup);
    });
});
