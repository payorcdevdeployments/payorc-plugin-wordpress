jQuery(function($) {
    'use strict';

    $('#lookup-btn').on('click', function() {
        var orderId = $('#p_order_id').val().trim();
        
        if (!orderId) {
            alert('Please enter an Order ID');
            return;
        }

        var $button = $(this);
        var originalText = $button.text();
        
        $button.prop('disabled', true).text('Looking up...');
        $('#payorc-result').hide();

        $.ajax({
            url: payorcAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'payorc_lookup_transaction',
                nonce: payorcAdmin.nonce,
                p_order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    $('.payorc-result-content').html(response.data.html);
                    $('#payorc-result').fadeIn();
                } else {
                    alert(response.data.message || 'Transaction not found');
                }
            },
            error: function() {
                alert('An error occurred while looking up the transaction');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Allow Enter key to trigger lookup
    $('#p_order_id').on('keypress', function(e) {
        if (e.which === 13) {
            $('#lookup-btn').click();
        }
    });
});