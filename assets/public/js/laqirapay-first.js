'use strict';

(function ($) {
    $(function () {
        // Run only on checkout or order-pay pages
        var pageState = (function detectPageState() {
            var body = document.body;
            var hasBodyClass = function (className) {
                return !!(body && body.classList && body.classList.contains(className));
            };

            var isOrderPay = hasBodyClass('woocommerce-order-pay');
            var isCheckout = hasBodyClass('woocommerce-checkout') || isOrderPay;

            if (!isOrderPay) {
                var query = window.location.search ? window.location.search.toLowerCase() : '';
                if (query.indexOf('order-pay=') !== -1) {
                    isOrderPay = true;
                    isCheckout = true;
                }
            }

            if (!isCheckout && document.getElementById('LaqirapayApp')) {
                isCheckout = true;
            }

            return {
                isCheckout: isCheckout,
                isOrderPay: isOrderPay,
            };
        })();

        if (!pageState.isCheckout) {
            return;
        }

        // --- State ---
        var isInitialized = false;
        var placeOrderClassicButton = $('#place_order');
        var isCartRefreshInFlight = false;
        var CART_REFRESH_ACTION = 'laqirapay_update_cart_data';
        var isOrderPayPage = pageState.isOrderPay;

        // --- Utils ---

        function ensureAppPositioned() {
            var app = $('#LaqirapayApp');
            if (!app.length) {
                return;
            }

            var placeholder = $('#startLaqiraPayApp');
            if (!placeholder.length) {
                return;
            }

            if (!app.prev('#startLaqiraPayApp').length) {
                app.insertAfter(placeholder);
            }
        }

        // Inject CSS to hide place order button when LaqiraPay is active
        (function injectStyleOnce() {
            if (!document.getElementById('laqira-hide-po-style')) {
                var css = `
          .laqira-active .wc-block-components-checkout-place-order-button,
          .laqira-active #place_order {
            display: none !important;
          }
        `;
                var style = document.createElement('style');
                style.id = 'laqira-hide-po-style';
                style.type = 'text/css';
                style.appendChild(document.createTextNode(css));
                document.head.appendChild(style);
            }
        })();

        // Detect if checkout blocks mode is active
        function detectBlocksMode() {
            if (isOrderPayPage) {
                return false;
            }

            return !!(window.wc && window.wc.wcBlocksData && window.wp && window.wp.data);
        }

        // Normalize slug and check if it's LaqiraPay
        function isLaqiraMethod(slug) {
            if (!slug) return false;
            var s = String(slug).trim().toLowerCase().replace(/[^a-z0-9_]/g, '_');
            return s === 'laqirapay' || s === 'wc_laqirapay';
        }

        // Read active payment method from checkout blocks data store
        function getActiveMethodFromBlocks() {
            try {
                var select = window.wp.data.select;
                var paymentStore = window.wc.wcBlocksData.paymentStore;
                return select(paymentStore).getActivePaymentMethod
                    ? select(paymentStore).getActivePaymentMethod()
                    : null;
            } catch (e) {
                return null;
            }
        }

        // Apply UI state depending on selected payment method
        function applyPaymentState(method, source) {
            if (!method) {
                method = $('input[name="payment_method"]:checked').val() || null;
            }

            var laqira = isLaqiraMethod(method);
            document.body.classList.toggle('laqira-active', laqira);

            if (laqira) {
                $('#laqira_loder').remove();
                ensureAppPositioned();
                $('#LaqirapayApp').show();

                // Refresh cart when LaqiraPay is active
                if (
                    source === 'init-classic' || source === 'change' || source === 'updated_checkout' ||
                    source === 'updated_wc_div' || source === 'init-blocks' || source === 'init-timeout' ||
                    source === 'blocks-initial' || source === 'subscribe-blocks' || source === 'init-mutation-blocks' ||
                    source === 'ajax-complete' || source === 'poll' || source === 'init-ready-blocks' ||
                    source === 'init-ready-classic'
                ) {
                    if ($('#payment_method_WC_laqirapay').is(':checked') && $('input[name="payment_method"]:checked').closest('#order_review').length) {
                        get_refreshed_cart();
                    }
                }
            } else {
                $('#LaqirapayApp').hide();
            }

            isInitialized = true;
        }

        // --- Handle order-pay page ---
        if (isOrderPayPage) {
            $('#laqira_loder').remove();
            ensureAppPositioned();
            $('#LaqirapayApp').show();
        } else {
            $('#LaqirapayApp').hide();
        }

        // --- Identify Laqira cart refresh AJAX calls ---
        function isCartRefreshRequest(settings) {
            if (!settings) {
                return false;
            }

            if (settings.data) {
                if (typeof settings.data === 'string') {
                    if (settings.data.indexOf('action=' + CART_REFRESH_ACTION) !== -1) {
                        return true;
                    }
                } else if (typeof settings.data === 'object') {
                    if (settings.data.action === CART_REFRESH_ACTION) {
                        return true;
                    }

                    if (typeof settings.data.get === 'function') {
                        var actionValue = settings.data.get('action');
                        if (actionValue === CART_REFRESH_ACTION) {
                            return true;
                        }
                    }
                }
            }

            if (settings.url && settings.url.indexOf('action=' + CART_REFRESH_ACTION) !== -1) {
                return true;
            }

            return false;
        }

        // --- Handle AJAX complete events (classic checkout) ---
        $(document).ajaxComplete(function (event, xhr, settings) {
            var isRefreshRequest = isCartRefreshRequest(settings);
            var requestUrl = settings && settings.url ? settings.url : '';

            if (!isRefreshRequest && requestUrl.includes('update_order_review') && $('#payment_method_WC_laqirapay').is(':checked')) {
                get_refreshed_cart();
            }
            $('#laqira_loder').remove();
            ensureAppPositioned();
            placeOrderClassicButton = $('#place_order');
            if (!isRefreshRequest) {
                var ajaxMethod = $('input[name="payment_method"]:checked').val() || null;
                applyPaymentState(ajaxMethod, 'ajax-complete');
            }
        });

        // --- Fetch updated cart data ---
        function get_refreshed_cart() {
            if (isCartRefreshInFlight) {
                return;
            }

            isCartRefreshInFlight = true;
            $.ajax({
                url: LaqiraData.orderData.laqiraAajaxUrl,
                type: 'POST',
                data: {
                    action: CART_REFRESH_ACTION,
                    security: LaqiraData.orderData.laqiraAjaxnonce
                },
                success: function (response) {
                    if (response && response.success && response.data) {
                        var data = response.data;
                        LaqiraData.orderData.originalOrderAmount = data.originalOrderAmount;
                        LaqiraData.orderData.cartTotal = data.cartTotal;
                    }
                },
                error: function (error) {
                    console.log('Error fetching cart info:', error);
                }
            }).always(function () {
                isCartRefreshInFlight = false;
            });
        }

        // --- Classic checkout mode ---
        function initClassicMode() {
            var initial = $('input[name="payment_method"]:checked').val() || null;
            applyPaymentState(initial, 'init-classic');

            // Listen for payment method change
            $(document.body).on('change', 'input[name="payment_method"]', function () {
                var method = $(this).val();
                applyPaymentState(method, 'change');
                if ($('#payment_method_WC_laqirapay').is(':checked') && $(this).closest('#order_review').length) {
                    get_refreshed_cart();
                }
            });

            // Listen for WooCommerce updated events
            $(document.body).on('updated_checkout updated_wc_div', function () {
                var method = $('input[name="payment_method"]:checked').val() || null;
                applyPaymentState(method, 'updated_checkout');
            });

            if (placeOrderClassicButton && placeOrderClassicButton.length) {
                var initialClassic = $('input[name="payment_method"]:checked').val() || null;
                applyPaymentState(initialClassic, 'init-ready-classic');
            }
        }

        // --- Checkout blocks mode ---
        function initBlocksMode() {
            // Initial read from data store
            var initial = getActiveMethodFromBlocks();
            applyPaymentState(initial, 'init-blocks');

            // Additional delayed check
            setTimeout(function () {
                if (!isInitialized) {
                    var delayed = getActiveMethodFromBlocks();
                    applyPaymentState(delayed, 'blocks-initial');
                }
            }, 100);

            // Subscribe to store changes
            try {
                var subscribe = window.wp.data.subscribe;
                subscribe(function () {
                    var current = getActiveMethodFromBlocks();
                    applyPaymentState(current, 'subscribe-blocks');
                });
            } catch (e) {
                console.warn('Blocks subscribe failed; falling back to classic.', e);
                initClassicMode();
                return;
            }

            // Observe DOM changes to reapply state when Place Order button appears
            var observer = new MutationObserver(function (mutations) {
                for (var i = 0; i < mutations.length; i++) {
                    var m = mutations[i];
                    if (m.type === 'childList' && m.addedNodes && m.addedNodes.length) {
                        var current = getActiveMethodFromBlocks();
                        applyPaymentState(current, 'init-mutation-blocks');
                        break;
                    }
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });
        }

        // --- Initialize based on checkout mode ---
        if (detectBlocksMode()) {
            initBlocksMode();
        } else {
            initClassicMode();
        }

        // --- Fallback polling (max 10 seconds) ---
        var tries = 0;
        var pollInterval = setInterval(function () {
            if (isInitialized || tries++ > 20) {
                clearInterval(pollInterval);
                return;
            }
            var method = detectBlocksMode() ? getActiveMethodFromBlocks() : ($('input[name="payment_method"]:checked').val() || null);
            applyPaymentState(method, 'poll');
        }, 500);

        // --- Final fallback after 500ms ---
        setTimeout(function () {
            if (!isInitialized) {
                var timeoutMethod = detectBlocksMode() ? getActiveMethodFromBlocks() : ($('input[name="payment_method"]:checked').val() || null);
                applyPaymentState(timeoutMethod, 'init-timeout');
            }
        }, 500);
    });
})(jQuery);
