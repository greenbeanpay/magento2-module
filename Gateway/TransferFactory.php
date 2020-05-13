<?php

namespace Gbp\GreenBeanPay\Gateway;

class TransferFactory implements \Magento\Payment\Gateway\Http\TransferFactoryInterface
{
    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilder
     */
    private $transferBuilder;

    /**
     * Constructor.
     *
     * @param \Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder
     */
    public function __construct(
        \Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
