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
use Magento\Framework\FlagManager;

class ProductSaveBefore implements ObserverInterface
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

    public $productRepository;

    public $apiEndPoint;

    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\Catalog\Model\ProductRepository $productRepository,
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
            if (!$this->config->isConnected()) {
                return $observer;
            }
            $product = $observer->getEvent()->getProduct();
            if (empty($product)) {
                return false;
            }
            $id = $product->getId();
            if (!$id) {
                return $observer;
            }

        } catch (\Exception $e) {
            return $observer;
        }
    }
}
