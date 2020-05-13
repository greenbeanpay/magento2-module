<?php

namespace Gbp\GreenBeanPay\Gateway\Request;

class PaymentDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentData */
        $paymentData = $buildSubject['payment'];
        $status = (int)$paymentData->getPayment()->getAdditionalInformation('status');
        $data = ['status' => $status];
        if ($status === \Gbp\GreenBeanPay\Model\ConfigProvider::STATUS_EXISTING_ACCOUNT) {
            return $data + [
                'code' => $paymentData->getPayment()->getAdditionalInformation('code'),
                'paymentId' => $paymentData->getPayment()->getAdditionalInformation('id'),
            ];
        }

        if ($status === \Gbp\GreenBeanPay\Model\ConfigProvider::STATUS_NEW_ACCOUNT) {
            return $data + ['amount' => $buildSubject['amount'] ?? 0];
        }

        throw new LocalizedException(__('Payment validation error.'));
    }
}
