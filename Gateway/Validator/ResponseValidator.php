<?php

namespace Gbp\GreenBeanPay\Gateway\Validator;

class ResponseValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @var \Gbp\GreenBeanPay\Model\Logger\Logger
     */
    private $logger;

    /**
     * @param \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Gbp\GreenBeanPay\Model\Logger\Logger $logger
    ) {
        parent::__construct($resultFactory);

        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = $validationSubject['response'];
        $isValid = true;
        $fails = [];
        if (!empty($response['error'])) {
            $isValid = false;
            $fails[] = $this->prepareErrorMessage($response['account'], $response['error']);
        }

        return $this->createResult($isValid, $fails);
    }

    /**
     * Prepare error message based on API error.
     *
     * @param string $account
     * @param string $error
     *
     * @return string
     */
    private function prepareErrorMessage($account, $error)
    {
        if (strpos($error, 'Bank Account balance does not have sufficient funds') !== false) {
            if (strpos($account, '@') !== false) {
                return __('You don\'t have enough funds in your bank account. '
                    . 'Please add more funds and try placing order again. '
                    . 'Click `Add New Bank Account` button to link another bank account '
                    . 'to your email %1#system_add_bank_account', $account);
            } else {
                return __('You don\'t have enough funds in your bank account. '
                    . 'Please add more funds and try placing order again. '
                    . 'Click `Add New Bank Account` button to link another bank account '
                    . 'to phone number %1#system_add_bank_account', $account);
            }
        }

        if (strpos($error, 'Invalid banking credentials') !== false) {
            return __('Invalid banking credentials. '
                . 'Click `Add New Bank Account` button to update your bank login details.#system_add_bank_account');
        }

        if (strpos($error, 'No checking account found') !== false || $error === 'Valid Bank Account not found.') {
            return __('Invalid banking information found. '
                . 'Click `Add New Bank Account` button to enter banking details '
                . 'for valid checking account.#system_add_bank_account');
        }

        return __($error);
    }
}
