/**
 * @category    Katapult
 * @package     Katapult_Payment
 */
/*browser:true*/
/*global define*/
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'katapult',
            component: 'Katapult_Payment/js/view/payment/method-renderer/katapult-method'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
