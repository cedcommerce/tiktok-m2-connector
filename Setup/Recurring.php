<?php

namespace Ced\MagentoConnector\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Recurring implements InstallSchemaInterface
{

    protected $logger;
    protected $apiEndPoint;
    protected $config;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Ced\MagentoConnector\Helper\Config $config
    ) {
        $this->logger = $logger;
        $this->apiEndPoint = $apiEndPoint;
        $this->config = $config;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $data = [];
        if ($this->config->isConnected()) {
            $data['token_type'] = $this->config->getTokenType();
            $data['storeurl'] = $this->config->getStoreurl();
            $data['setup_upgrade'] = true;

        }
        $response = $this->apiEndPoint->sendTokenByCurl($data);
        $this->logger->logger(
            'Setup Upgrade',
            'Installer',
            "Response . ".json_encode($response),
            'Installer is running  '
        );
        $setup->endSetup();
    }
}
