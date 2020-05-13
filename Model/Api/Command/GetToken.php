<?php

namespace Gbp\GreenBeanPay\Model\Api\Command;

class GetToken extends \Gbp\GreenBeanPay\Model\Api\Client
{
    const API_URI = 'cognito/get_merchant_token_by_credentials';

    /**
     * @return void
     */
    public function execute()
    {
        return $this->post(
            self::API_URI,
            [
                'username' => $this->apiHelper->getLogin(),
                'password' => $this->apiHelper->getPassword(),
            ],
            false
        );
    }
}
