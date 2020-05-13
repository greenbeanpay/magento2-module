define([
    'jquery',
    'underscore',
    'mage/utils/wrapper'
], function ($, _, wrapper) {
    'use strict';

    return function (errorProcessor) {

        errorProcessor.process = wrapper.wrap(
            errorProcessor.process,
            function (_super, response, messageContainer) {
                if (response.status !== 401) {
                    try {
                        var error = JSON.parse(response.responseText);
                        if (error.message && error.message.indexOf('#system_add_bank_account') > -1) {
                            $(document).trigger('gbpAddBA');
                            error.message = error.message.replace('#system_add_bank_account', '');
                            response.responseText = JSON.stringify(error);
                        }
                    } catch (e) {

                    }
                }

                _super(response, messageContainer);
            }
        );

        return errorProcessor;
    };
});