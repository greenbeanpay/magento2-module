<?php

namespace Gbp\GreenBeanPay\Model\Api;

class Client
{
    const URL = 'https://prod.greenbeanpay.com/api/';

    const ENDPOINT_URL = 'https://6uxgaw8d30.execute-api.us-west-2.amazonaws.com/prod/';

    /**
     * @var \Gbp\GreenBeanPay\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    private $curl;

    /**
     * @var \Gbp\GreenBeanPay\Model\Logger\Logger
     */
    private $logger;

    /**
     * @var bool
     */
    protected $useEndpoint = false;

    /**
     * @var array
     */
    protected $commandHeaders = [];

    /**
     * @param \Gbp\GreenBeanPay\Model\Api\Curl $curl
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Gbp\GreenBeanPay\Helper\Api $apiHelper
     * @param \Gbp\GreenBeanPay\Model\Logger\Logger $logger
     */
    public function __construct(
        \Gbp\GreenBeanPay\Model\Api\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Gbp\GreenBeanPay\Helper\Api $apiHelper,
        \Gbp\GreenBeanPay\Model\Logger\Logger $logger
    ) {
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->apiHelper = $apiHelper;
        $this->logger = $logger;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param bool $decode
     * @return void
     */
    protected function post($uri, array $data, $decode = true)
    {
        return $this->execute('post', $uri, $data, $decode);
    }

    /**
     * @param string $uri
     * @param bool $decode
     * @return void
     */
    protected function put($uri, $decode = true)
    {
        return $this->execute('put', $uri, [], $decode);
    }

    private function execute($method, $uri, array $data, $decode)
    {
        $jsonData = $this->jsonHelper->jsonEncode($data);
        $this->curl->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                // 'Content-Length' => strlen($jsonData),
                'Expect' => '',
            ] + $this->commandHeaders);

        if ($this->apiHelper->isDebugEnabled()) {
             $this->logger->debug($method . ' ' . (!$this->useEndpoint ? self::URL : self::ENDPOINT_URL) . $uri);
             $this->logger->debug('Request: ' . $jsonData);
        }

        try {
            $this->curl->{$method}((!$this->useEndpoint ? self::URL : self::ENDPOINT_URL) . $uri, $jsonData);
            $response = $this->curl->getBody();
            if ($this->apiHelper->isDebugEnabled()) {
                $this->logger->debug('Response: ' . $response);
            }

            if ($response === '' || $this->curl->getStatus() !== 200) {
                $response = false;
            } elseif ($decode) {
                $response = $this->jsonHelper->jsonDecode($response);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
            $response = false;
        }

        return $response;
    }
}
