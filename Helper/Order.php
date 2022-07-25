<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_MagentoConnector
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\MagentoConnector\Helper;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Store Manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * Customer Repository
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * Product Repository
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Catalog Product Model
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    /**
     * Customer Factory
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    public $helperConfig;

    public $orderFactory;

    public $shipmentFactory;

    public $modelIndox;

    public $stockRegistry;

    public $regionFactory;

    public $invoiceService;

    public $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ValueInterface $configValue,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\AdminNotification\Model\Inbox $modelIndox,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Magento\Catalog\Model\ProductFactory $product
    ) {
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->storeManager = $storeManager;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->product = $product;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->customerFactory = $customerFactory;
        $this->helperConfig = $config;
        $this->logger = $logger;
        $this->scopeConfigManager = $scopeConfig;
        $this->configValueManager = $configValue;
        $this->orderFactory = $orderFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->modelIndox = $modelIndox;
        $this->regionFactory = $regionFactory;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->stockRegistry = $stockRegistry;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Fetch Latest Orders in ready state from
     *
     * @return null
     */
    public function fetchLatestOrders($response)
    {
        $returnData = [];
        $message = [];
        $storeId = $this->helperConfig->getStoreId();
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();
        $this->storeManager->setCurrentStore($store);
        if (isset($response['orders'])) {
            foreach ($response['orders'] as $order) {
                if (!isset($order['user_id']) || $order['user_id'] != $this->helperConfig->getUserId()) {
                    $returnData['error'][] = [
                        'message' => "Invalid User Id, Make sure you are authorized user",
                        'requested_order_id' =>  $order ['ordersn'],
                    ];
                    continue;
                }
                $orderObject =  $order ;
                $email = isset($order['buyer_email']) ? $order ['buyer_email'] : 'customer@aliconnecter.com';
                $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
                if (count($order) <= 0) {
                    continue;
                }
                $purchaseOrderId = $order ['ordersn'];
                $saleOrder = $this->orderFactory->create()
                    ->loadByIncrementId($purchaseOrderId);
                $fetchOrder = false;
                if (!$saleOrder->getId()) {
                    $fetchOrder = true;
                }
                if ($fetchOrder) {
                    $ncustomer = $this->_assignCustomer($order, $customer, $store, $email);
                    if (is_bool($ncustomer) && ! $ncustomer->getId()) {
                        continue;
                    } else {
                        $return = $this->generateQuote($store, $ncustomer, $order, $orderObject);
                        if (isset($return['success'])) {
                            $returnData['success'][] = $return['success'];
                        } elseif (isset($return['error'])) {
                            $returnData['error'][] = $return['error'];
                        } else {
                            $returnData['error'] = $return;
                        }
                    }
                } else {
                    $returnData['notice'][] = [
                        'message' => $purchaseOrderId.' Order already created .',
                        'requested_order_id' => $purchaseOrderId,
                    ];
                }
            }
            if (isset($returnData['success']) && count($returnData['success']) > 0) {
                $model = $this->modelIndox;
                $date = date("Y-m-d H:i:s");
                $model->setData('severity', 4);
                $model->setData('date_added', $date);
                $model->setData('title', "Incoming  Order");
                $model->setData(
                    'description',
                    "Congratulation !! You have received ".count($returnData['success'])." new orders"
                );
                $model->setData('url', "#");
                $model->setData('is_read', 0);
                $model->setData('is_remove', 0);
                $model->save();
                $message['success'] = $returnData['success'];
            }

            if (isset($returnData['error']) && count($returnData['error']) > 0) {
                $message['error'] = $returnData['error'];
            }

            if (isset($returnData['notice']) && count($returnData['notice']) > 0) {
                $message['notice'] = $returnData['notice'];
            }
        } else {
            $message['error'][] = [
                'message' => 'Order not having valid data.',
                'requested_order_id' => null,
            ];
        }

        if (isset($message['error'])) {
            $this->logger->logger(
                'Order Create',
                'Order create Error',
                json_encode($message),
                'Order creation error'
            );
        }
        return $message;
    }

    /**
     * Validate string for null , empty and isset
     * @param string $string
     * @return boolean
     */
    public function validateString($string)
    {
        $stringValidation = (isset($string) && ! empty($string)) ? true : false;
        return $stringValidation;
    }

    /**
     * Create AliConnector customer on Magento
     * @param array $order
     * @param array $customer
     * @param null $store
     * @param string $email
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function _assignCustomer($order, $customer, $store, $email)
    {
        if (!($customer->getId())) {
            try {
                $cname = $order['buyer_username'];
                $customerName = explode(' ', $cname);
                $firstname = $customerName [0];
                unset($customerName[0]);
                $customerName = array_values($customerName) ;
                $lastname = implode(' ', $customerName);
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($firstname);
                $customer->setDob('2000-01-01');
                $customer->setGender(1);
                $customer->setLastname($lastname);
                $customer->setPassword("password");
                $customer->save();
                return $customer;
            } catch (\Exception $e) {
                $this->logger->logger(
                    'Exception',
                    'Customer Exception',
                    $e->getMessage(),
                    'Customer '
                );
                return ['error' => $e->getMessage()];
            }
        } else {
            return $customer;
        }
    }

    /**
     * Generate order in Magento     *
     * @param integer $store
     * @param Object $ncustomer
     * @param array $order
     * @param Object $orderObject
     * @return Boolean
     */
    public function generateQuote($store, $ncustomer, $order, $orderObject)
    {
        $returnData = [];
        try {
            $purchaseOrderId = $order ['ordersn'];
            $autoReject = false;
            $itemsArray = $order['items'];
            $currency = $orderObject['currency'];
            $baseprice = '';
            $shippingcost = '';
            $tax = '';
            $quote = $this->quote->create();
            $quote->setStore($store);
            $quote->setCurrency();
            $customer = $this->customerRepository->getById($ncustomer->getId());
            $quote->assignCustomer($customer);
            $shippingcost = 0;
            $subTotal = 0;
            $taxArray = [];
            $productArray = [];
            $taxTotal = 0;

            $deliver_by =  $order ['pay_time'];
            $order_place = $order ['create_time'];
            foreach ($itemsArray as $item) {
                $tax = 0;
                $message = '';
                $sku = (isset($item['variation_sku']) && !empty($item['variation_sku'])) ?
                    $item['variation_sku'] : $item['item_sku'];
                $quantity = $item['variation_quantity_purchased'];
                $price = $item ['variation_original_price'];
                $product = $this->product->create()->loadByAttribute('sku', $sku);
                if ($product) {
                    if ($product->getStatus() == '1') {
                        $stockRegistry = $this->stockRegistry;
                        /* Get stock item */
                        $stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                        $cancelItemQuantity = '';
                        if (!empty($item['variation_quantity_purchased'])) {
                            $cancelItemQuantity = 0;
                        }
                        $stockstatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ?
                            ($stock->getQty() >= $item ['variation_quantity_purchased'] ?
                                true : ' Qunatity ordered i.e. '.$item ['variation_quantity_purchased'].
                                ' is not available in your store') :
                            ' Is set to Out of Stock') : $product->getSku().'  has 0 Quantity';

                        if ((is_bool($stockstatus) && $stockstatus)) {
                            $productArray [] = [
                                'id' => $product->getEntityId(),
                                'qty' => $item ['variation_quantity_purchased']
                            ];

                            $price = $item ['variation_original_price'];
                            $price = (isset($item['variation_discounted_price']) &&
                                $item['variation_discounted_price'] > 0) ?
                                $item ['variation_discounted_price'] : $price;
                            /*if (isset($item['variation_discounted_price']) &&
                                $item['variation_discounted_price'] > 0) {
                                $price = $item ['variation_discounted_price'];
                            }*/

                            //end
//                            $price = $product->getPrice();
                            $qty = $item ['variation_quantity_purchased'];

                            /*if (isset($item ['estimated_shipping_fee'])){
                                $shippingcost += ($item ['variation_original_price'] * $qty) ;
                            } *//*else{
                                $tax = $tax + ($item ['charges'] ['charge'][0]['tax']['taxAmount']['amount'] * $qty);
                            }*/
                            /*$taxTotal += $tax;*/
                            $rowTotal = $price * $qty;
                            $subTotal +=$rowTotal;
                            $product->setPrice($price)
                                ->setBasePrice($price)
                                ->setOriginalCustomPrice($price)
                                ->setRowTskuotal($rowTotal)
                                ->setBaseRowTotal($rowTotal);
                            $quote->addProduct($product, (int)$qty);
                        } else {
                            $autoReject = true;
                            $returnData['error']['message'] = $stockstatus;
                            $returnData['error']['requested_order_id'] = $purchaseOrderId;
                        }
                    } else {
                        $autoReject = true;
                        $returnData['error']['message'] = ' SKU is Disabled in your System.';
                        $returnData['error']['requested_order_id'] = $purchaseOrderId;
                    }
                } else {
                    $autoReject = true;
                    $returnData['error']['message'] = 'Sku {'.$sku.'} Is not Available In your System.';
                    $returnData['error']['requested_order_id'] = $purchaseOrderId;
                }
            }

            $cname = $order ['buyer_username'];
            $customerName = explode(' ', $cname);
            $firstname = $customerName [0];
            unset($customerName[0]);
            $customerName = array_values($customerName) ;
            $lastname = implode(' ', $customerName);

            // after save order
            if (count($productArray) > 0 && count($itemsArray) == count($productArray) && ! $autoReject) {

                $region = $this->regionFactory->create()
                    ->loadByCode($order ['recipient_address'] ['state'], $order ['recipient_address'] ['country']);
                $regionId = $region->getRegionId();

                if (!$regionId) {
                    $_regionFactory = $this->regionFactory
                        ->create()->getCollection()->getData();
                    foreach ($_regionFactory as $_regionFactor) {
                        if (isset($_regionFactor['default_name']) &&
                            $_regionFactor['default_name'] == $order ['recipient_address'] ['state']) {
                            $state = isset($_regionFactor['code']) ? $_regionFactor['code'] :
                                $order ['recipient_address'] ['state'];
                            $region = $this->regionFactory->create()
                                ->loadByCode($state, $order ['recipient_address'] ['country']);
                            $regionId = $region->getRegionId();
                        }

                    }
                }

                $orderData = [
                    'currency_id' => $order['currency'],
                    'email' => 'test@cedcommerce.com', // buyer email id
                    'shipping_address' => [
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'street' => !empty($order ['recipient_address'] ['full_address']) ?
                            $order['recipient_address']['full_address'] : $order['recipient_address'] ['state'] ,
                        'city' => empty($order['recipient_address']['city']) ? 'no' :
                            $order ['recipient_address']['city'],
                        'country_id' => $order['recipient_address'] ['country'],
                        'region' => $order['recipient_address'] ['state'] ,
                        'region_id' =>empty($regionId) ? 'no':$regionId,
                        'postcode' => $order['recipient_address']['zipcode'],
                        'telephone' => $order['recipient_address']['phone'],
                        'fax' => '',
                        'save_in_address_book' => 1
                    ]
                ];

                $quote->getBillingAddress()->addData($orderData['shipping_address']);
                $shippingAddress = $quote->getShippingAddress()->addData($orderData['shipping_address']);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                    ->setShippingMethod('shipbyconnector_shipbyconnector');
                $quote->setPaymentMethod('payconnector');
                $quote->setInventoryProcessed(false);
                $quote->save();
                //Now Save quote and quote is ready

                // Set Sales Order Payment
                $quote->getPayment()->importData(['method' => 'payconnector']);
                // Collect Totals & Save Quote
                $quote->collectTotals()->save();

                foreach ($quote->getAllItems() as $item) {
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);

                    $sku = $item->getProduct()->getSku();
                    if (isset($taxArray[$sku])) {
                        $item->setTaxAmount($taxArray[$sku]);
                        $item->setBaseTaxAmount($taxArray[$sku]);
                    }
                    $item->setOriginalCustomPrice($item->getPrice())
                        ->setOriginalPrice($item->getPrice())
                        ->save();
                }

                $quote->collectTotals()->save();
                $reserveIncrementId = $quote->getReservedOrderId();
                // $orderAfterQuote = $this->quoteManagement->submit($quote->getId());

                $quote = $this->cartRepositoryInterface->get($quote->getId());
                $orderAfterQuote = $this->cartManagementInterface->submit($quote);
                //var_dump($orderAfterQuote);die;
                $orderId =  $purchaseOrderId;//$orderAfterQuote->getIncrementId();
                $orderAfterQuote->setShippingAmount($shippingcost);
                $orderAfterQuote->setTaxAmount($taxTotal);
                $orderAfterQuote->setBaseTaxAmount($taxTotal);
                $orderAfterQuote->setSubTotal($subTotal);
                $orderAfterQuote->setGrandTotal($subTotal + $shippingcost+$taxTotal)  ;
                $orderAfterQuote->setIncrementId($orderId);
                if (isset($order['source_marketplace'])) {
                    $orderAfterQuote->setSourceMarketplace($order['source_marketplace']);
                }
                $orderAfterQuote->save();
                foreach ($orderAfterQuote->getAllItems() as $item) {
                    $item->setOriginalPrice($item->getPrice())
                        ->setBaseOriginalPrice($item->getPrice())
                        ->save();
                }

                $this->generateInvoice($orderAfterQuote);
                $order = $this->orderFactory->create()
                    ->loadByAttribute('increment_id', $purchaseOrderId);
                $entityId = $order->getId();
                $returnData['success']['message'] = $purchaseOrderId. ' Order created SuccessFully .';
                $returnData['success']['entity_id'] = $entityId;
                $returnData['success']['requested_order_id'] = $purchaseOrderId;
            }
        } catch (\Exception $e) {
            $this->logger->logger(
                'Exception',
                'Order create',
                $e->getMessage(),
                'Order creation error'
            );
            $returnData['error']['message'] = $e->getMessage();
            $returnData['error']['requested_order_id'] = $purchaseOrderId;
        }
        return $returnData;
    }

    /*
     * @Invoice generation Process
     */
    public function generateInvoice($order)
    {
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->dbTransaction->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transactionSave->save();
        $order->setIsCustomerNotified(false)->save();
        $order->setStatus('processing')->save();
    }

    /*
     * @Shipment generation Process
     */
    public function generateShipment($order, $cancelleditems)
    {
        $shipment = $this->_prepareShipment($order, $cancelleditems);
        if ($shipment) {
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = $this->dbTransaction->addObject($shipment)
                    ->addObject($shipment->getOrder());
                $transactionSave->save();
                $order->setStatus('complete')->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    'Error in saving shipping:' . $e->getMessage()
                );
            }
        }
    }

    public function _prepareShipment($order, $cancelleditems)
    {
        foreach ($order->getAllItems() as $orderItems) {
            $qty_ordered = $orderItems->getQtyOrdered();
            $cancelleditems[$orderItems->getId()] = (int)($qty_ordered - $cancelleditems[$orderItems->getId()]);
        }
        $shipment = $this->shipmentFactory->create($order, isset($cancelleditems) ? $cancelleditems : [], []);
        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment;
    }

    /**
     * @param $detail
     * @return bool
     */
    public function checkifTrue($detail)
    {
        if ($detail['refund_quantity'] > 0
            && $detail['return_quantity'] >= $detail['refund_quantity']
            && $detail['refund_quantity'] <= $detail['available_to_refund_qty']) {
            return true;
        } else {
            return false;
        }
    }

    public function getOrderFlag($order_to_complete, $order_cancel, $mixed)
    {
        $order_to_complete = isset($order_to_complete) ? $order_to_complete : [];
        $order_cancel = isset($order_cancel) ? $order_cancel : [];
        $mixed = isset($mixed) ? $mixed : [];
        $Order_flag_array = array_merge($order_to_complete, $order_cancel, $mixed);
        $itemcount = count($Order_flag_array);
        $complete = 0;
        $cancel = 0;
        $mix = 0;
        foreach ($Order_flag_array as $key => $value) {
            if ($value == 'complete') {
                $complete++;
            } elseif ($value == 'cancel') {
                $cancel++;
            } else {
                $mix++;
            }
        }

        return [
            'item_count' => $itemcount,
            'complete'=>$complete,
            'cancel'=> $cancel,
            'mix' => $mix
        ];
    }

    public function parserArray($array)
    {
        $arr = [];
        foreach ($array as $key => $value) {
            if (in_array($key, $arr)) {
                continue;
            }
            $count = count($array);
            $sku = (isset($value['variation_sku']) && !empty($value['variation_sku'])) ?
                $value['variation_sku'] : $value['item_sku'];
            $quantity = 1;
            $lineNumber = $value['lineNumber'];
            for ($i = $key+1; $i < $count; $i++) {
                if (isset($array[$i]) && ($array[$i]['item']['sku'] == $sku)) {
                    $quantity++;
                    $lineNumber = $lineNumber.','.$array[$i]['lineNumber'];
                    unset($array[$i]);
                    array_push($arr, $i);
                    array_values($array);
                }
            }
            $array[$key]['lineNumber'] = $lineNumber;
            $array[$key]['orderLineQuantity']['amount'] = $quantity;
        }
        return $array;
    }
}
