<?php

namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\AttributeOptionsInterface;

class AttributeOptions implements AttributeOptionsInterface
{

    public $logger;

    public $config;

    public $eavConfig;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->eavConfig = $eavConfig;
    }
    /**
     * @param string $attrCode
     * @return mixed|string
     */
    public function getAttributOptions($attrCode)
    {
        $returnData = [];
        if ($this->config->isConnected()) {
            if ($attrCode) {
                $attribute = $this->eavConfig->getAttribute('catalog_product', $attrCode);
                if (!$attribute || !$attribute->getId()) {
                    $returnData = ['error' => ['message' => 'Invalid Attribute Code:'.$attrCode]];
                } elseif ($attribute && ($attribute->usesSource() || $attribute->getData('frontend_input')=='select' ||
                        $attribute->getData('frontend_input')=='multiselect')) {
                    $return = $attribute->getSource()->getAllOptions();
                    $returnData = ['success' => ['data' => $return]];
                } else {
                    $returnData = ['error' => ['message' => 'Attribute not having options.']];
                }
            } else {
                $returnData = ['error' => ['message' => "Attribute code is not valid."]];
            }
        } else {
            $returnData = ['error' => ['message' => "You are not connected"]];
        }

        $this->logger->logger(
            'Attribute Options',
            'Attribute Options api',
            json_encode($returnData),
            'Attribute Options api response'
        );

        return [$returnData];
    }
}
