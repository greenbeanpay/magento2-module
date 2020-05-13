<?php

namespace Gbp\GreenBeanPay\Model\Api\Command;

class CreatePaymentWithData extends \Gbp\GreenBeanPay\Model\Api\Client
{
    const API_URI = 'payments/create_payment_request_from_plugin';

    const API_URI_WITH_DATA = 'payments/create_payment_request_from_plugin_with_data';

    /**
     * @return void
     */
    public function execute($data, $withData = true)
    {
        return $this->post($withData ? self::API_URI_WITH_DATA : self::API_URI, [ 'data' => json_encode($data) ]);
    }
}
