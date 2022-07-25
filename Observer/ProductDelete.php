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

class ProductDelete implements ObserverInterface
{
    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    public $config;

    public $apiEndPoint;

    public $resourceProduct;

    public $resourceConfigurable;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable
    ) {
        $this->messageManager = $messageManager;
        $this->collectionFactory =  $collectionFactory;
        $this->reques = $request;
        $this->registry = $registry;
        $this->apiEndPoint = $apiEndPoint;
        $this->config = $config;
        $this->resourceProduct = $resourceProduct;
        $this->resourceConfigurable = $resourceConfigurable;
    }

    /**
     * Catalog product save after event handler, Retires Product on MagentoConnector on Delete
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->isConnected()) {
            return $observer;
        }
        $product = $observer->getEvent()->getData('product');
        $sku = $product->getSku();
        $userId = $this->config->getUserId();
        $data = [];
        if ($sku) {
            $data['user_id'] = $userId;
            $data['data_type_status'] = 'delete';
            $data['sku'] = $sku;
            $data['product_type'] = $product->getTypeId();
        }
        if ($data) {
            $token = $this->config->getAccessToken();
            $response = $this->apiEndPoint->productChangeSend($token, $data);
        }
        return $observer;
    }
}
