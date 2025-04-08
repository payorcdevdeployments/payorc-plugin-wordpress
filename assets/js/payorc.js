jQuery(function($) {
    'use strict';

    var PayOrc = {
        init: function() {
            this.bindEvents();
            this.attachEvent();
        },

        bindEvents: function() {
            $(document).on('click', '.payorc-payment-button', this.handlePayment);
        },

        handlePayment: function(e) {
            e.preventDefault();
            var $button = $(this);

            var paymentData = {
                action: 'payorc_process_payment',
                nonce: payorc_params.nonce,
                amount: $button.data('amount'),
                currency: $button.data('currency'),
                success_url: $button.data('success-url'),
                cancel_url: $button.data('cancel-url'),
                failure_url: $button.data('failure-url'),
                customer_email: $button.data('customer-email'),
                customer_name: $button.data('customer-name'),
                customer_phone: $button.data('customer-phone'),
                customer_address: $button.data('customer-address'),
                country_code: $button.data('country-code'),
                payment_method: $button.data('payement-method'),
            };

            $.ajax({
                url: payorc_params.ajax_url,
                type: 'POST',
                data: paymentData,
                success: function(response) {
                    if (response.success) {
                        if ($button.data('payment-method') === 'hosted') {
                            window.location.href = response.data.payment_link;
                        } else {
                            PayOrc.openModal(response.data.iframe_link);
                        }
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        saveTransaction: function(transactionData) {
            $.ajax({
                url: payorc_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'payorc_save_transaction',
                    nonce: payorc_params.nonce,
                    transaction: transactionData
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('Failed to save transaction:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving transaction:', error);
                }
            });
        },

        openModal: function(iframeUrl) {
            // Create modal styles for responsiveness
            var modalStyles = `
                <style>
                    #payorc-modal {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 999999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    #payorc-modal-container {
                        background: #fff;
                        padding: 20px;
                        border-radius: 8px;
                        position: relative;
                        width: 90%;
                        max-width: 650px;
                        max-height: 90vh;
                        margin: 20px;
                    }
                    #payorc-loader {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        z-index: 2;
                        background: rgba(255,255,255,0.9);
                        padding: 20px;
                        border-radius: 8px;
                        text-align: center;
                    }
                    #payorc-loader img {
                        width: 50px;
                        height: 50px;
                    }
                    #payorc-iframe {
                        width: 100%;
                        height: 600px;
                        border: none;
                        display: block;
                    }
                    #payorc-close {
                        position: absolute;
                        right: 10px;
                        top: 10px;
                        border: none;
                        background: none;
                        font-size: 24px;
                        cursor: pointer;
                        z-index: 3;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 50%;
                        background: #f0f0f0;
                    }
                    #payorc-close:hover {
                        background: #e0e0e0;
                    }
                    @media (max-width: 768px) {
                        #payorc-modal-container {
                            width: 95%;
                            margin: 10px;
                            padding: 15px;
                        }
                        #payorc-iframe {
                            height: 80vh;
                        }
                    }
                    @media (max-width: 480px) {
                        #payorc-iframe {
                            height: 80vh;
                        }
                    }
                </style>
            `;

            // Create modal HTML
            var modal = $('<div/>', { id: 'payorc-modal' });
            var modalContainer = $('<div/>', { id: 'payorc-modal-container' });
            
            var loader = $('<div/>', { 
                id: 'payorc-loader',
                html: '<img src="https://checkout.payorc.com/checkout/public/images/spinner-loader.gif" alt="Loading..." />'
            });

            var closeButton = $('<button/>', {
                id: 'payorc-close',
                text: '×'
            });

            var iframe = $('<iframe/>', {
                id: 'payorc-iframe',
                src: iframeUrl
            });

            // Append styles and elements
            $('head').append(modalStyles);
            modalContainer.append(closeButton, loader, iframe);
            modal.append(modalContainer);
            $('body').append(modal).addClass('payorc-modal-open');

            // Show loader for minimum 2 seconds
            setTimeout(function() {
                $('#payorc-loader').fadeOut();
            }, 2000);

            // Handle close button
            closeButton.on('click', function() {
                modal.remove();
                $('body').removeClass('payorc-modal-open');
                window.location.href = payorc_params.return_url;
            });
        },

        attachEvent: function() {
            var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
            var eventer = window[eventMethod];
            var messageEvent = eventMethod === "attachEvent" ? "onmessage" : "message";

            eventer(messageEvent, function(e) {
                try {
                    var result = JSON.parse(e.data);
                    console.log('PayOrc payment response:', result);

                    // Save transaction data first
                    PayOrc.saveTransaction(result);

                    switch (result.status) {
                        case 'SUCCESS':
                            $('#payorc-loader').show();
                            window.location.href = result.return_url || payorc_params.return_url;
                            break;

                        case 'CANCELLED':
                            $('#payorc-loader').hide();
                            $('#payorc-iframe').attr('src', '');
                            setTimeout(function() {
                                $('#payorc-modal').remove();
                                $('body').removeClass('payorc-modal-open');
                                window.location.href = result.cancel_url || payorc_params.return_url;
                            }, 200);
                            break;

                        case 'FAILED':
                            $('#payorc-loader').hide();
                            $('#payorc-iframe').attr('src', '');
                            setTimeout(function() {
                                $('#payorc-modal').remove();
                                $('body').removeClass('payorc-modal-open');
                                window.location.href = result.failure_url || payorc_params.return_url;
                            }, 200);
                            break;
                    }
                } catch (error) {
                    console.error('Error processing payment response:', error);
                }
            }, false);
        }
    };

    PayOrc.init();
});