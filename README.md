# Compatibility

Magento 2.3

PHP 7.1 | 7.2

# Installation

## Installation via Composer
`composer require greenbeanpay/magento2-module`

## Manual Installation
Deploy files into Magento2 folder `app/code/Gbp/GreenBeanPay/`

# Enable Extension
- enable the module: `php bin/magento module:enable Gbp_GreenBeanPay`
- to make sure that the module is properly registered, run
  - `php bin/magento setup:upgrade`
  - `php bin/magento  setup:di:compile`
  - `php bin/magento setup:static-content:deploy`
