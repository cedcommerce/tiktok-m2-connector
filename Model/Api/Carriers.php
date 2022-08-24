<?php

namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\CarrierListInterface;

class Carriers implements CarrierListInterface
{
    public $logger;

    public $config;

    public $carrierslist;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Carrierslist $carrierslist
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->carrierslist = $carrierslist;
    }

    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getCarriers($userid)
    {
        $returnData = [];
        if ($this->config->isConnected()) {
            $userID = $this->config->getUserId();
            if ($userID == $userid) {
                $returnData = $this->carrierslist->getShippingMethods($this->config->getStoreId());
            } else {
                $returnData = ['error' => ['message' => "Invalid User Id."]];
            }
        } else {
            $returnData = ['error' => ['message' => "You are not connected"]];
        }

        $this->logger->logger(
            'Carriers List',
            'Carriers api',
            json_encode($returnData),
            'Carriers api response'
        );

        return [$returnData];
    }
}
