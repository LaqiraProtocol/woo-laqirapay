jQuery(function($) {
    var messages = typeof laqiraTxAdmin !== 'undefined' ? laqiraTxAdmin : {
        genericError: 'Unable to confirm the order. Please try again.',
        successMessage: 'Order confirmation completed successfully.'
    };

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
        var $feedback = $markup.find('#laqira-payments-after-confirmation-action');
        var ajaxUrl = config.ajax_url ||
            (typeof laqiraTxView !== 'undefined' && laqiraTxView.ajax_url ? laqiraTxView.ajax_url : '') ||
            (typeof ajaxurl !== 'undefined' ? ajaxurl : '');

        if (!$button.length) {
            return;
        }

        $loading.hide();

        $button.off('click.laqira-payments').on('click.laqira-payments', function() {
            var requestData = buildPayload(config, $markup);

            if (!requestData.action) {
                $feedback.html('<p class="error">' + escapeHtml(messages.genericError) + '</p>');
                return;
            }

            if (!ajaxUrl) {
                $loading.hide();
                $feedback.html('<p class="error">' + escapeHtml(messages.genericError) + '</p>');
                return;
            }

            $loading.show();
            $feedback.empty();

            $.ajax({
                url: ajaxUrl,
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

                        $feedback.html('<p class="updated">' + escapeHtml(messages.successMessage) + '</p>');
                        return;
                    }

                    var errorMessage = (response && response.data && (response.data.message || response.data.error)) || messages.genericError;
                    $feedback.html('<p class="error">' + escapeHtml(errorMessage) + '</p>');
                },
                error: function() {
                    $loading.hide();
                    $feedback.html('<p class="error">' + escapeHtml(messages.genericError) + '</p>');
                }
            });
        });
    }

    $('.laqira-payments-confirmation-actions').each(function() {
        var $markup = $(this);
        var action = $markup.data('laqira-payments-action');

        if (!action) {
            action = $markup.find('#tx_hash_input').length
                ? 'laqira_payments_do_confim_tx_hash_for_faild_transaction'
                : 'laqira_payments_do_confim_tx_hash';
        }

        handleConfirmation({ action: action }, $markup);
    });

    $(document).on('laqira-payments:confirmation-ready', function(event, config, $markup) {
        if (!config || !config.markup) {
            return;
        }

        var $renderedMarkup = $markup;

        if (!$renderedMarkup || !$renderedMarkup.length) {
            $renderedMarkup = $(config.markup);
            $('#laqira-payments-confirmation-table').append($renderedMarkup);
        }

        handleConfirmation(config, $renderedMarkup);
    });
});
