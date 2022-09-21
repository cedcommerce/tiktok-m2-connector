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

class OrderSave implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public $messageManager;

    public $config;

    public $apiEndPoint;

    public $order;

    public $logger;

    public $connecterOrderFactory;

    /**
     * ProductSaveBefore constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order $order,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Model\OrderFactory $connecterOrderFactory
    ) {
        $this->request = $request;
        $this->registry  = $registry;
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->apiEndPoint = $apiEndPoint;
        $this->order = $order;
        $this->logger = $logger;
        $this->connecterOrderFactory = $connecterOrderFactory;
    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        if (!$this->config->isConnected()) {
            return $observer;
        }
        try {
            $event = $observer->getEvent()->getOrder();
            $orderId = $event->getOrderId();
            if ($orderId) {
                $order = $this->order->load($orderId);
                if ($order) {
                   $userId = $this->config->getUserId();
                   $statuscode = $event->getOrder()->getStatus();
                   $incrementId = $order->getIncrementId();
                   $model = $this->connecterOrderFactory->create()->load($incrementId);
                   if($model && $model->getId()) {
                       $data = [
                           'status' => $statuscode,
                           'order_id' => $incrementId,
                           'user_id' => $userId
                       ];
                       $token = $this->config->getAccessToken();
                       $response = $this->apiEndPoint->orderStatusChange($token, $data);
                       $this->logger->logger(
                           'Order On Save',
                           'Observer Request and Response',
                           json_encode($data). ' Response '. json_encode($response),
                           'Order Save'
                       );
                   }
               }
            }
        } catch (\Exception $e) {
            $this->logger->logger(
                'Order On Save',
                'Observer Error',
                $e->getMessage(),
                'Order Save'
            );
            return $observer;
        }
        return $observer;
    }
}
