<?php

namespace Gbp\GreenBeanPay\Model\Api\Command;

class CheckConsumer extends \Gbp\GreenBeanPay\Model\Api\Client
{
    const API_URI = 'cognito/check_consumer';

    /**
     * @return void
     */
    public function execute($account, $token)
    {
        return $this->post(
            self::API_URI,
            [
                'username' => $account,
                'token' => $token,
            ]
        );
    }
}
