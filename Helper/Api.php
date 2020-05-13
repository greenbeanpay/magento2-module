<?php

namespace Gbp\GreenBeanPay\Helper;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LOGIN_XML_PATH = 'payment/greenbeanpay/merchant_login';

    const PASSWORD_XML_PATH = 'payment/greenbeanpay/merchant_password';

    const DESCRIPTION_XML_PATH = 'payment/greenbeanpay/description';

    const DEBUG_XML_PATH = 'payment/greenbeanpay/debug';

    /**
     * Get merchant login.
     *
     * @return string
     */
    public function getLogin()
    {
        return trim($this->scopeConfig->getValue(
            self::LOGIN_XML_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Get merchant password.
     *
     * @return string
     */
    public function getPassword()
    {
        return trim($this->scopeConfig->getValue(
            self::PASSWORD_XML_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Get method description.
     *
     * @return string
     */
    public function getDescription()
    {
        return trim($this->scopeConfig->getValue(
            self::DESCRIPTION_XML_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Check if debug is enabled.
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::DEBUG_XML_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
