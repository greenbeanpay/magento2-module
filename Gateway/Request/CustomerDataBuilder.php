<?php

namespace Gbp\GreenBeanPay\Gateway\Request;

class CustomerDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentData */
        $paymentData = $buildSubject['payment'];
        $account = $paymentData->getPayment()->getAdditionalInformation('account');
        $status = (int)$paymentData->getPayment()->getAdditionalInformation('status');
        if ($status === \Gbp\GreenBeanPay\Model\ConfigProvider::STATUS_EXISTING_ACCOUNT) {
            return ['account' => $account];
        }

        $billingAddress = $paymentData->getOrder()->getBillingAddress();
        $data = [
            'customer_name' => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
            'first_name' => $billingAddress->getFirstname(),
            'last_name' => $billingAddress->getLastname(),
        ];

        if (\Zend_Validate::is($account, \Magento\Framework\Validator\EmailAddress::class)) {
            $data['customer_email'] = $account;
            $data['customer_phone'] = '+1' . $billingAddress->getTelephone();
        } else {
            $data['customer_phone'] = '+1' . $account;
            $data['customer_email'] = $billingAddress->getEmail();
        }
        $data['account'] = $account;

        return $data;
    }
}
