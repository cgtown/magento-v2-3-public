/**
 * @category    Katapult
 * @package     Katapult_Payment
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'mage/url',
], function ($, Component, quote, url) {
    'use strict';

    return Component.extend({
        isOrderPlaced: false,
        customer_id: false,
        uid: false,
        katapult_id: false,
        defaults: {
            template: 'Katapult_Payment/payment/katapult',
        },
        responseReceived: false,

        /**
         * Confirms that order can be paid for with Katapult
         * @returns {boolean}
         */
        isAvailable: function () {
            var quoteItems = quote.getItems(),
                totalLeasable = quote.totals()['subtotal_with_discount'] + quote.totals()['tax_amount'];

            quoteItems.forEach(function (product) {
                var isLeasable = '1';
                /** Check if quoteItem or Item has leasability value available **/
                if (
                    product.product &&
                    product.product.katapult_payment_leasable !== undefined
                ) {
                    isLeasable = product.product.katapult_payment_leasable;
                } else if (product.katapult_payment_leasable !== undefined) {
                    isLeasable = product.katapult_payment_leasable;
                }

                if (isLeasable == '0') {
                    totalLeasable -= product.row_total_incl_tax - product.base_discount_amount;
                }
            });

            return (totalLeasable).toFixed(2) >= window.checkoutConfig.payment.katapult.minOrderAmount;
        },

        /**
         * Initialize Katapult modal
         */
        triggerModal: function () {
            var self = this;

            $.ajax({
                url: url.build('katapult/katapult/checkoutInfoJson'),
                type: 'get',
                dataType: 'json',
            }).done(function (data) {
                katapult.checkout.set(data);
                katapult.checkout.load();
            });

            self.monitorModal();
        },

        /**
         * Add event listener for frame post messages
         * Pass the needed data to order creation trigger, if the data is valid
         */
        monitorModal: function () {
            window.addEventListener('message', (response) => {
                var responseContent = {};

                // Validate that the content can be converted
                try {
                    responseContent = JSON.parse(response.data);
                } catch (e) {
                    // Do nothing
                }

                if (!this.responseReceived) {
                    this.responseReceived = true;

                    // Confirm that the response is of the correct type
                    if (responseContent.type === 'checkout' && responseContent.status === true) {
                        this.handleCompletion(responseContent.response);
                    }
                }
            }, false);
        },

        /**
         * Handle completion event of Katapult modal
         *
         * @param {Object} data
         */
        handleCompletion: function (data) {
            if (data.uid) {
                this.customer_id = data.customer_id;
                this.uid = data.uid;
                this.katapult_id = data.katapult_id;

                this.placeOrder();
            }
        },

        /**
         * Get payment method data
         */
        getData: function () {
            var data = {
                method: this.getCode(),
                additional_data: {
                    katapult_payment_customer_id: this.customer_id,
                    katapult_payment_uid: this.uid,
                    katapult_payment_id: this.katapult_id,
                },
            };

            return data;
        },
    });
});
