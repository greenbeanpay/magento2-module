<?php

namespace Gbp\GreenBeanPay\Model\Api;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    public function put($uri)
    {
        $this->makeRequest("PUT", $uri);
    }
}
