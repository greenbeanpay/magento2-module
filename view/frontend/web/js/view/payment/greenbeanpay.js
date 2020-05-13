define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'greenbeanpay',
                component: 'Gbp_GreenBeanPay/js/view/payment/method-renderer/greenbeanpay-method'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    });