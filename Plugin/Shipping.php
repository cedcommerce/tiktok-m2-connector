<?php

namespace Ced\MagentoConnector\Plugin;

class Shipping
{

    public function afterSave(
        \Magento\Shipping\Model\Order\Track $shipData
    ) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->create(\Ced\MagentoConnector\Helper\Logger::class);
        $data = $shipData->getData();
        $logger->logger(
            'Shipping ',
            'Before',
            json_encode($data),
            'Shipping Before Plugin'
        );
        echo "<PRE>";
        print_r($data);
        die;
        $dataHelper = $objectManager->create(\Ced\Otto\Helper\Data::class);
        $helperConfig = $objectManager->create(\Ced\Otto\Helper\Config::class);
    }
}
