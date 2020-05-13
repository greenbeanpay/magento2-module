<?php

namespace Gbp\GreenBeanPay\Gateway\Request;

class AddressDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentData */
        $paymentData = $buildSubject['payment'];
        $status = (int)$paymentData->getPayment()->getAdditionalInformation('status');
        if ($status === \Gbp\GreenBeanPay\Model\ConfigProvider::STATUS_EXISTING_ACCOUNT) {
            return [];
        }

        $billingAddress = $paymentData->getOrder()->getBillingAddress();

        return [
            'address' => [
                'address_line1' => $billingAddress->getStreetLine1(),
                'address_line2' => $billingAddress->getStreetLine2(),
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegionCode(),
                'zip_code' => $billingAddress->getPostcode(),
            ],
        ];
    }
}
