<?php

namespace Gbp\GreenBeanPay\Gateway\Client;

class Authorize implements \Magento\Payment\Gateway\Http\ClientInterface
{
    /**
     * @var \Gbp\GreenBeanPay\Model\Api\Command\GetToken
     */
    private $getToken;

    /**
     * @var \Gbp\GreenBeanPay\Model\Api\Command\ValidateCode
     */
    private $validateCode;

    /**
     * @var \Gbp\GreenBeanPay\Model\Api\Command\CreatePaymentWithData
     */
    private $createPaymentWithData;

    /**
     * Constructor.
     *
     * @param \Gbp\GreenBeanPay\Model\Api\Command\GetToken $getToken
     * @param \Gbp\GreenBeanPay\Model\Api\Command\ValidateCode $validateCode
     * @param \Gbp\GreenBeanPay\Model\Api\Command\CreatePaymentWithData $createPaymentWithData
     */
    public function __construct(
        \Gbp\GreenBeanPay\Model\Api\Command\GetToken $getToken,
        \Gbp\GreenBeanPay\Model\Api\Command\ValidateCode $validateCode,
        \Gbp\GreenBeanPay\Model\Api\Command\CreatePaymentWithData $createPaymentWithData
    ) {
        $this->getToken = $getToken;
        $this->validateCode = $validateCode;
        $this->createPaymentWithData = $createPaymentWithData;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $token = $this->getToken->execute();

        $params = $transferObject->getBody();
        if ($params['status'] === \Gbp\GreenBeanPay\Model\ConfigProvider::STATUS_EXISTING_ACCOUNT) {
            return ['account' => $params['account']]
                + $this->validateCode->execute($token, $params['paymentId'], $params['code']);
        } elseif ($params['status'] === \Gbp\GreenBeanPay\Model\ConfigProvider::STATUS_NEW_ACCOUNT) {
            unset($params['status']);
            $params['token'] = $token;

            return ['account' => $params['account']]
                + $this->createPaymentWithData->execute($params, false);
        }

        throw new LocalizedException(__('Payment validation error.'));
    }
}
