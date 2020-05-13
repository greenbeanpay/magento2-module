<?php

namespace Gbp\GreenBeanPay\Model;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CODE = 'greenbeanpay';

    const STATUS_EXISTING_ACCOUNT = 0;

    const STATUS_NEW_ACCOUNT = 1;

    /**
     * @var \Gbp\GreenBeanPay\Helper\Api
     */
    private $apiHelper;

    /**
     * @param \Gbp\GreenBeanPay\Helper\Api $apiHelper
     */
    public function __construct(
        \Gbp\GreenBeanPay\Helper\Api $apiHelper
    ) {
        $this->apiHelper = $apiHelper;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => true,
                    'description' => $this->apiHelper->getDescription()
                ]
            ]
        ];
    }
}
