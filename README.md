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

# Module Configuration
1. Login into Magento 2 Admin Panel
2. Go to Stores > Configuration > Sales > Payment Methods
3. Configure the module in `Green Bean Pay` section: 
   - set Enabled field to Yes
   - enter merchant login and password
   - specify method description (optional)
   - set sort order value (optional)
![Module Configuration](https://edebitdirect.com/wp-content/uploads/2020/05/3.png)
4. Click `Save Config` button at the top
