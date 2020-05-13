<?php

namespace Gbp\GreenBeanPay\Gateway\ErrorMapper;

class ErrorMessageMapper implements \Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface
{
    /**
     * @inheritdoc
     */
    public function getMessage(string $code)
    {
        return $code;
    }
}
