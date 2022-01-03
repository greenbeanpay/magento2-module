define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        '//player.vimeo.com/api/player.js'
    ],
    function (
        $,
        Component,
        additionalValidators,
        globalMessageList,
        checkoutData,
        quote,
        customer,
        Player
    ) {
        'use strict';

        additionalValidators.registerValidator(
            {
                validate: function () {
                    if (checkoutData.getSelectedPaymentMethod() === 'greenbeanpay') {
                        if (!$('#gbp-account').val()) {
                            globalMessageList.addErrorMessage({
                                message: $.mage.__('Enter your phone number or email and click submit in the payment method area')
                            });

                            return false;
                        }

                        if ($('#gbp-popup-status').val() !== '1') {
                            if (!$('#gbp-payment-id').val()) {
                                globalMessageList.addErrorMessage({
                                    message: $.mage.__('Please click GreenBeanPay submit button below to continue')
                                });

                                return false;
                            }

                            if (!$('#gbp-code').val()) {
                                globalMessageList.addErrorMessage({
                                    message: $.mage.__('Wrong verification code')
                                });

                                return false;
                            }
                        }
                    }

                    return true;
                }
            }
        );

        var component = Component.extend({
            windowPopup: null,
            gbpPlayer: null,
            popupUrl: null,

            defaults: {
                template: 'Gbp_GreenBeanPay/payment/greenbeanpay'
            },

            getDescription: function () {
                return window.checkoutConfig.payment.greenbeanpay.description;
            },

            isValueValid: function (field) {
                var mailRegex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                var phoneRegex = /^\d{10}$/;
                if (!mailRegex.test(field) && !phoneRegex.test(field)) {
                    return false;
                }
                return true;
            },

            accountFilter: function (data, e) {
                var allowedKeys = [8, 13, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 65, 66 , 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 189, 190];

                return !(($.inArray(e.keyCode, allowedKeys) === -1) || (e.shiftKey === true && (e.keyCode !== 50 && e.keyCode !== 189)));
            },

            initPaymentProcess: function() {
                if ($('#gbp-payment-request').is('.loading')) {
                    return;
                }

                $('#gbp-account').removeClass('error');
                var value = $('#gbp-account').val(),
                    sameAddress = $('#billing-address-same-as-shipping-greenbeanpay').is(':checked'),
                    shippingAddress = !customer.isLoggedIn() && checkoutData.getShippingAddressFromData() ? checkoutData.getShippingAddressFromData() : quote.shippingAddress(),
                    billingAddress = !customer.isLoggedIn() && checkoutData.getBillingAddressFromData() ? checkoutData.getBillingAddressFromData() : quote.billingAddress(),
                    address = sameAddress ? shippingAddress : billingAddress;

                if (this.isValueValid(value)) {
                    $('#gbp-payment-request').addClass('loading');
                    $('#gbp-account').attr('disabled', 'disabled');
                    var self = this;

                    $.ajax({
                        type: 'POST',
                        url: '/gbp/auth',
                        data: {
                            'account': value,
                            'name': address.firstname + ' ' + address.lastname,
                            'billing_email': !customer.isLoggedIn() ? checkoutData.getValidatedEmailValue() : customer.customerData.email,
                            'billing_phone': address.telephone,
                            'billing_address_1': address.street[0],
                            'billing_address_2': address.street[1],
                            'billing_city': address.city,
                            'billing_state': address.region_id ? address.region_id : address.regionId,
                            'billing_postcode': address.postcode,
                            'billing_first_name': address.firstname,
                            'billing_last_name': address.lastname,
                            'naf': $('#gbp-naf').val(),
                        },
                        async: false,
                        complete: function () {
                            $('#gbp-account').removeAttr('disabled');
                            $('#gbp-payment-request').removeClass('loading');
                        },
                        success: function (response) {
                            if (response.success) {
                                if (response.exists === false) {
                                    $('#gbp-overlay').show();
                                    $('html body').addClass('remove-scroll');
                                    var username = response.username;
                                    if (username === null) {
                                        username = 'NF';
                                    }
                                    self.popupUrl = 'https://prod.greenbeanpay.com/plugin/registration/account/' + value + '/username/' + username;
                                    self.openPopup();

                                    var timer = setInterval(function() {
                                        if (self.windowPopup && self.windowPopup.closed) {
                                            clearInterval(timer);
                                            $('#gbp-overlay').hide();
                                            $('html body').removeClass('remove-scroll');
                                            self.windowPopup = null;
                                            self.popupUrl = null;
                                        }
                                    }, 100);

                                    window.addEventListener('message', function(e) {
                                        if (e.origin === 'https://prod.greenbeanpay.com') {
                                            var status = e.data.status;
                                            $('#gbp-popup-status').val(status);
                                            if (status === 1) {
                                                $('.gbp-payment-method-content').hide();
                                                $('.gbp-another-account').hide();
                                                $('.gbp-popup-success').show();
                                            }
                                        }
                                    } , false);
                                } else {
                                    $('#gbp-payment-request-input, #gbp-register').hide();
                                    $('.gbp-another-account').hide();
                                    $('#gbp-code-block').show();
                                    $('#gbp-payment-id').val(response.id);
                                    $('#gbp-code').focus();
                                }
                            } else {
                                globalMessageList.addErrorMessage({ message: response.message });
                            }
                        }
                    });
                } else {
                    if (value.indexOf('@') < 0) {
                        globalMessageList.addErrorMessage({
                            message: $.mage.__('Cell phone format entry error. No spaces or characters needed, i.e. 1234567890. Please contact 888-616-2535 for additional support')
                        });
                    } else {
                        globalMessageList.addErrorMessage({
                            message: $.mage.__('Wrong phone/email')
                        });
                    }

                    $('#gbp-account').addClass('error');
                }
            },

            openPopup: function () {
                if ($(window).width() < 851) {
                    var width = $(window).width(),
                        height = $(window).height();
                    this.windowPopup = window.open(
                        this.popupUrl,
                        '',
                        'toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=' + width + ',height=' + height
                    );
                } else {
                    var left = $(window).width() / 2 - 425,
                        top = $(window).height() / 2 - 275;
                    this.windowPopup = window.open(
                        this.popupUrl,
                        '',
                        'toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=850,height=650,left=' + left + ',top=' + top
                    );
                }
            },

            focusOnPopup: function () {
              if (this.windowPopup !== null && !this.windowPopup.closed) {
                  this.windowPopup.focus();
              } else if (this.popupUrl) {
                  this.openPopup();
              }

              return false;
            },

            linkAnother: function () {
                $('#gbp-account').val('');
                $('#gbp-popup-status').val(0);
                $('.gbp-popup-success').hide();
                $('.gbp-another-account').hide();
                $('#gbp-code-block').hide();
                $('#gbp-payment-request-input').show();
                $('.gbp-payment-method-content').show();
            },

            addNewBA: function () {
                $('#gbp-popup-status').val(0);
                $('#gbp-naf').val(1);
                $('#gbp-code').val('');
                $('.popup-success').hide();
                $('.gbp-another-account').hide();
                $('#gbp-code-block').hide();
                $('#gbp-payment-request-input').show();
                $('.gbp-payment-method-content').show();
                this.initPaymentProcess();
            },

            showDescription: function () {
                $('#gbp-how-it-works-content').toggle();
            },

            getPlayer: function () {
                if (!this.gbpPlayer) {
                    var options = {
                        id: 59777392,
                        width: 640,
                        loop: true
                    };
                    // <iframe allow="autoplay; fullscreen" allowfullscreen="" id="gbp-vimeo-player" frameborder="0" height="100%" src="https://player.vimeo.com/video/465429204" width="100%" data-ready="true"></iframe>
                    console.log('init player');
                    this.gbpPlayer = new Player($('#gbp-vimeo-player'));
                }
                return this.gbpPlayer;
            },

            playVideo: function() {
                console.log('start video');
                $("#gbp-videoModal").css("display", "flex");
                this.getPlayer().setCurrentTime(0);
                this.getPlayer().play();
            },

            stopVideo: function() {
                console.log('stop video');
                this.gbpPlayer.pause();
                $("#gbp-videoModal").hide();
            },

            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'id': $('#gbp-payment-id').val(),
                        'code': $('#gbp-code').val(),
                        'account': $('#gbp-account').val(),
                        'status': $('#gbp-popup-status').val(),
                        'naf': $('#gbp-naf').val(),
                    }
                };
            },
        });

        $(document).on('gbpAddBA', function (event) {
            $('.popup-success').hide();
            $('.gbp-payment-method-content').hide();
            $('.gbp-another-account').show();
        });

        return component;
    }
);
