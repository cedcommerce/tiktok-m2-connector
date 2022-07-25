<?php

namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\CurrencyListInterface;

class Currency implements CurrencyListInterface
{
    public $logger;

    public $config;

    public $currency;

    public $priceCurrencyInterface;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyInterface,
        \Magento\Directory\Model\Currency $currency
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->currency = $currency;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
    }
    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getCurrency($userid)
    {
        $returnData = [];
        if ($this->config->isConnected()) {
            $userID = $this->config->getUserId();
            if ($userID == $userid) {
                $storeID = $this->config->getStoreId();
                $currentCurrency = $this->priceCurrencyInterface->getCurrency()
                    ->getCurrencyCode($storeID);

                $allowed  = $this->currency->getConfigAllowCurrencies();
                $returnData['success']['store_currency'] = $currentCurrency;
                $returnData['success']['allowed_currency'] = $allowed;

            } else {
                $returnData = ['error' => ['message' => "Invalid User Id."]];
            }
        } else {
            $returnData = ['error' => ['message' => "You are not connected"]];
        }
        $this->logger->logger(
            'Currency api',
            'Currency',
            json_encode($returnData),
            'api response'
        );
        return [$returnData];
    }
}
