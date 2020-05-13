<?php

namespace Gbp\GreenBeanPay\Model\Api\Command;

class CreatePayment extends \Gbp\GreenBeanPay\Model\Api\Client
{
    const API_URI = 'payment_requests';

    protected $useEndpoint = true;

    /**
     * @return void
     */
    public function execute($token, $account, $name, $amount)
    {
        $this->commandHeaders['Authorization'] = $token;

        return $this->post(self::API_URI, [
            filter_var($account, FILTER_VALIDATE_EMAIL) ? 'customer_email' : 'customer_phone' => $account,
            'amount' => $amount,
            'customer_name' => $name,
        ]);
    }
}
