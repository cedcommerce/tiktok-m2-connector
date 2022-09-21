<?php

namespace Ced\MagentoConnector\Model\Api;

use \Ced\MagentoConnector\Api\VariantAttributeInterface;

class VariantAttribute implements VariantAttributeInterface
{
    public $logger;

    public $config;

    public $configurableAttr;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttr
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->configurableAttr = $configurableAttr;
    }

    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getAttributes($userid)
    {
        $returnData = [];
        if ($this->config->isConnected()) {
            $userID = $this->config->getUserId();
            if ($userID == $userid) {
                $return = $this->configurableAttr->getApplicableAttributes()->getData();
                $returnData = ['success' => ['data' => $return]];
            } else {
                $returnData = ['error' => ['message' => "Invalid User Id."]];
            }
        } else {
            $returnData = ['error' => ['message' => "You are not connected"]];
        }

        $this->logger->logger(
            'Variant Attribute List',
            'Variant Attribute api',
            json_encode($returnData),
            'Variant Attribute api response'
        );

        return [$returnData];
    }
}
