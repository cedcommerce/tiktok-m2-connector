<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_MagentoConnector
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\MagentoConnector\Observer;

use Magento\Framework\Event\ObserverInterface;

class CsvProductSave implements ObserverInterface
{

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Request
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public $json;

    public $config;

    public $apiEndPoint;

    public $productRepository;

    public $resourceConfigurable;

    public $resourceProduct;

    public $logger;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param \Ced\MagentoConnector\Helper\Logger $logger
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable
     * @param \Magento\Framework\Json\Helper\Data $json
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable,
        \Magento\Framework\Json\Helper\Data $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->config = $config;
        $this->apiEndPoint = $apiEndPoint;
        $this->productRepository = $productRepository;
        $this->json = $json;
        $this->resourceProduct = $resourceProduct;
        $this->resourceConfigurable = $resourceConfigurable;
        $this->logger = $logger;
    }

    /**
     * Catalog product save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->logger->logger(
                'Product CSV Import',
                'Observer',
                "product CSV Import observer",
                'Product save start'
            );
            if (!$this->config->isConnected()) {
                $this->logger->logger(
                    'Product CSV Import',
                    'Observer',
                    "product CSV Import observer",
                    'You are not connected'
                );
                return $observer;
            }

            $userId = $this->config->getUserId();
            $token = $this->config->getAccessToken();
            $bunch = $observer->getBunch();
            $skus = [];
            foreach($bunch as $product) {
                if(isset($product['sku'])) {
                    $skus[] = $product['sku'];
                    $data = [
                        'user_id' => $userId,
                        'sku' => $product['sku'],
                        'data_type_status' => 'update',
                        'product_type' => $product['product_type'],
                        'csv_bulk' => true
                    ];
                    $response = $this->apiEndPoint->productChangeSend($token, $data);
                    $this->logger->logger(
                        'Product CSV IMPORT Sku '.$product['sku'],
                        'Response ',
                        json_encode($response),
                        'Product data'
                    );
                    $this->logger->logger(
                        'Product CSV IMPORT Sku '.$product['sku'],
                        'Request data ',
                        json_encode($data),
                        'Product data'
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->logger(
                'Product CSV Bulk',
                'Exception ',
                $e->getMessage(),
                'Error'
            );
        }
        return $observer;
    }
}
