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

class Shipment implements ObserverInterface
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
        $userId = $this->config->getUserId();

        try {
            try {
                $event = $observer->getEvent();
                if ($event->getName() == 'sales_order_shipment_track_save_after') {
                    $track = $event->getTrack();
                    $shipment = $track->getShipment();

                } else {
                    $shipment = $observer->getEvent()->getShipment();
                }
            } catch (\Exception $e) {
                $this->logger->logger(
                    'Order Shipment Observer',
                    'Shipment Error',
                    $e->getMessage(),
                    'Shipment'
                );
                return $observer;
            }
            $trackArray = [];
            foreach ($shipment->getAllTracks() as $track) {
                $trackArray = $track->getData();
            }

            $order = $shipment->getOrder();
            $method = $order->getShippingMethod();
            if ($method != 'shipbyconnector_shipbyconnector') {
                $this->logger->logger(
                    'Order Shipment Observer',
                    'Shipment',
                    'Order having Other Shipping method :'.$method.' Please use shipbyconnector_shipbyconnector',
                    'Shipment'
                );
                return $observer;
            }
            if (empty($trackArray)) {
                $this->logger->logger(
                    'Order Shipment Observer',
                    'Shipment',
                    "Please add Tracking Then try",
                    'Shipment'
                );
                return false;
            }
            $stopPartialShip = false;
            $order = $this->order->load($trackArray['order_id']);
            $incrementId = $order->getIncrementId();
            $model = $this->connecterOrderFactory->create()->load($incrementId, 'order_id');
            if($model && $model->getId()) {
                $stopPartialShip = $model->getStopPartialShip();
            }
            $shipmentArray = [];
            $postData = $this->request->getPost();
            $shipmentItems = isset($postData['shipment']['items']) ? $postData['shipment']['items'] : '';
            foreach ($order->getAllItems() as $item) {
              
                $qtyToShip = $item->getQtyToShip();
                $itemId = $item->getItemId();
                if(isset($shipmentItems[$itemId])) {
                    $qtyToShip = $shipmentItems[$itemId];
                }
                if($stopPartialShip) {
                    if((int)$qtyToShip < (int)$item->getQtyOrdered()) {
                        $this->logger->logger(
                            'Order Shipment Observer',
                            'Shipment',
                            "This order can't Partial Ship ",
                            'Shipment'
                        );
                        break;
                    }
                }

                $shipmentArray [] = [
                    'sku' => $item->getSku(),
                    'qty_shipped' => (int)$item->getQtyShipped(),
                    'qty_ordered' => (int)$item->getQtyOrdered(),
                    'qty_canceled' => (int)$item->getQtyCanceled(),
                    'qty_refunded' => (int)$item->getQtyRefunded(),
                ];
            }

            if ($shipmentArray) {
                if (isset($trackArray['track_number'])) {
                    $tracking = (string)$trackArray['track_number'];
                }
                $trackingUrl = '';
                $tracking = $tracking;

                $carrierArray = [
                        'ups'=>'UPS',
                        'dhl'=>'DHL',
                        'usps'=>'USPS',
                        'fedex'=>'FedEx',
                        'custom'=>'other',
                    ];

                $shipStationcarrier = $trackArray['carrier_code'];
                $carrier = isset($carrierArray[$shipStationcarrier]) ? $carrierArray[$shipStationcarrier] : $shipStationcarrier;
                $dataShip = [
                    'user_id' => $userId,
                    'ordersn' => $incrementId,
                    'data_type_status' => 'shipment',
                    'shipment_tracking_number' => $tracking,
                    'carrier' => $carrier,
                    'shipment_tracking_url' => $trackingUrl,
                    'shipment_items' => $shipmentArray
                ];

                if ($dataShip) {
                    $token = $this->config->getAccessToken();
                    $response = $this->apiEndPoint->orderShip($token, $dataShip);
                }
                return  $observer;
            }
        } catch (\Exception $e) {
            $this->logger->logger(
                'Order Shipment Observer',
                'Shipment',
                "Exception ".$e->getMessage(),
                'Shipment'
            );
            return $observer;
        }

    }
}
