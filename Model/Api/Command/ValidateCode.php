<?php

namespace Gbp\GreenBeanPay\Model\Api\Command;

class ValidateCode extends \Gbp\GreenBeanPay\Model\Api\Client
{
    const API_URI = 'payment_requests/authorize_with_code/';

    protected $useEndpoint = true;

    /**
     * @return void
     */
    public function execute($token, $paymentId, $code)
    {
        $this->commandHeaders['Authorization'] = $token;

        $response = $this->put(self::API_URI . $paymentId . '/' . $code);

        if (!$response) {
            return ['error' => __('Wrong validation code')];
        }

        if (!is_array($response)) {
            return ['error' => $response];
        }

        return $response;
    }
}
