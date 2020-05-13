<?php

namespace Gbp\GreenBeanPay\Observer;

class DataAssignObserver extends \Magento\Payment\Observer\AbstractDataAssignObserver
{
    const KEY_ID = 'id';
    const KEY_CODE = 'code';
    const KEY_ACCOUNT = 'account';
    const KEY_STATUS = 'status';
    const KEY_NAF = 'naf';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::KEY_ID,
        self::KEY_CODE,
        self::KEY_ACCOUNT,
        self::KEY_STATUS,
        self::KEY_NAF,
    ];

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
