<?php

namespace Gbp\GreenBeanPay\Controller\Auth;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    private $onepage;

    /**
     * @var \Gbp\GreenBeanPay\Model\Api\Command\GetToken
     */
    private $getToken;

    /**
     * @var \Gbp\GreenBeanPay\Model\Api\Command\CheckConsumer
     */
    private $checkConsumer;

    /**
     * @var \Gbp\GreenBeanPay\Model\Api\Command\CreatePayment
     */
    private $createPayment;

    /**
     * @var \Gbp\GreenBeanPay\Model\Api\Command\CreatePaymentWithData
     */
    private $createPaymentWithData;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private $regionFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Type\Onepage $onepage
     * @param \Gbp\GreenBeanPay\Model\Api\Command\GetToken $getToken
     * @param \Gbp\GreenBeanPay\Model\Api\Command\CheckConsumer $checkConsumer
     * @param \Gbp\GreenBeanPay\Model\Api\Command\CreatePayment $createPayment
     * @param \Gbp\GreenBeanPay\Model\Api\Command\CreatePaymentWithData $createPaymentWithData
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Type\Onepage $onepage,
        \Gbp\GreenBeanPay\Model\Api\Command\GetToken $getToken,
        \Gbp\GreenBeanPay\Model\Api\Command\CheckConsumer $checkConsumer,
        \Gbp\GreenBeanPay\Model\Api\Command\CreatePayment $createPayment,
        \Gbp\GreenBeanPay\Model\Api\Command\CreatePaymentWithData $createPaymentWithData,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        $this->jsonFactory = $resultJsonFactory;
        $this->onepage = $onepage;
        $this->getToken = $getToken;
        $this->checkConsumer = $checkConsumer;
        $this->createPayment = $createPayment;
        $this->createPaymentWithData = $createPaymentWithData;
        $this->regionFactory = $regionFactory;

        return parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');

            return;
        }

        $token = $this->getToken->execute();
        if (!$token || $token === 'Incorrect username or password.' || $token === 'User does not exist.') {
            return $this->jsonFactory->create()->setData([
                'success' => false,
                'message' => __('Invalid merchant credentials'),
            ]);
        }
        $account = $this->getAccount();
        $consumerResponse = $this->checkConsumer->execute($account, $token);
        $notEnoughFunds = $this->getRequest()->getParam('naf', false);

        if ($consumerResponse['status'] === true && $consumerResponse['accounts'] > 0 && !$notEnoughFunds) {
            return $this->createPayment($consumerResponse['address'], $account, $token);
        } else {
            return $this->jsonFactory->create()->setData([
                'success' => true,
                'exists' => false,
                'username' => isset($consumerResponse['username']) ? $consumerResponse['username'] : null
            ]);
        }
    }

    /**
     * Get customer email or phone.
     *
     * @return string
     */
    private function getAccount()
    {
        $account = $this->getRequest()->getParam('account');
        if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
            $account = '+1' . $account;
        }

        return $account;
    }

    /**
     * Create payment through GBP API.
     *
     * @param boolean $hasAddress
     * @param string $account
     * @param string $token
     *
     * @return $this
     */
    private function createPayment($hasAddress, $account, $token)
    {
        $name = $this->getRequest()->getParam('name');
        $amount = $this->onepage->getQuote()->getGrandTotal();

        if ($hasAddress === true) {
            $result = $this->createPayment->execute($token, $account, $name, $amount);
        } else {
            $region = $this->regionFactory->create()->load($this->getRequest()->getParam('billing_state'));
            $params = [
                'customer_name' => $name,
                'address' => [
                    'address_line1' => $this->getRequest()->getParam('billing_address_1'),
                    'address_line2' => $this->getRequest()->getParam('billing_address_2'),
                    'city' => $this->getRequest()->getParam('billing_city'),
                    'state' => $region && $region->getCode() ? $region->getCode() : '',
                    'zip_code' => $this->getRequest()->getParam('billing_postcode'),
                ],
                'first_name' => $this->getRequest()->getParam('billing_first_name'),
                'last_name' => $this->getRequest()->getParam('billing_last_name'),
                'token' => $token,
                'amount' => $amount,
            ];
            if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
                $params['customer_email'] = $params['account'] = $account;
                $params['customer_phone'] = '+1' . $this->getRequest()->getParam('billing_phone');
                $params['account'] = $account;
            } else {
                $params['customer_phone'] = $params['account'] = '+1' . $account;
                $params['customer_email'] = $this->getRequest()->getParam('billing_email');
            }
            $result = $this->createPaymentWithData->execute($params);
        }

        if (!$result || empty($result['id'])) {
            return $this->jsonFactory->create()->setData([
                'success' => false,
                'message' => $result['error'] ?? __('Cannot create payment request'),
            ]);
        }

        return $this->jsonFactory->create()->setData([
            'success' => true,
            'id' => $result['id'],
            'exists' => true
        ]);
    }
}
