<?php

namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\ProductStockInterface;

class ProductStock implements ProductStockInterface
{
    public $logger;

    public $config;

    public $currency;

    public $priceCurrencyInterface;

    public $productFactory;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Directory\Model\Currency $currency
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->currency = $currency;
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
    }
    /**
     * @param mixed $data
     * @return mixed|string
     */
    public function getProductsAndStock($data)
    {
        try {
            $returnData = [];
            if ($this->config->isConnected()) {

                $userID = $this->config->getUserId();
                $storeId = $this->config->getStoreId();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                $store = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore($storeId);

                $storeCode = $this->config->getStoreCode();
                $sub = '/V1/products';
                if ($storeCode) {
                    $sub = '/'.$storeCode.'/V1/products';
                }
                if(isset($data['pageSize'])) {
                    $sub .= '?searchCriteria[pageSize]='.$data['pageSize'];
                } else {
                    $sub .= '?searchCriteria[pageSize]=20';
                }
                if(isset($data['currentPage'])) {
                    $sub .= '&searchCriteria[currentPage]='.$data['currentPage'];
                } else {
                    $sub .= '&searchCriteria[currentPage]=0';
                }

                if(isset($data['visibility']) && ($data['visibility'] <= 4)) {
                    $sub .= '&searchCriteria[filter_groups][0][filters][0][field]=visibility&searchCriteria[filter_groups][0][filters][0][value]='.$data['visibility'].'&searchCriteria[filter_groups][0][filters][0][condition_type]=eq';
                } else if(!isset($data['visibility'])) {
                    $sub .= '&searchCriteria[filter_groups][0][filters][0][field]=visibility&searchCriteria[filter_groups][0][filters][0][value]=1&searchCriteria[filter_groups][0][filters][0][condition_type]=neq';
                }

                $baseUrl = $this->config->getStoreurl();
                $url = $baseUrl.'rest'.$sub;
                $response = $this->getRequestCurl($url);
                if (isset($response['items'])) {
                    foreach ($response['items'] as $key => $respon) {
                        if (isset($respon['type_id']) && $respon['type_id'] == 'configurable') {
                            $childslist = $this->getChildsFromParent($respon['sku']);
                            foreach ($childslist as $key1 => $child) {
                                if(isset($child['sku'])) {
                                    $stock = [];//$this->sourceItem->getSourceItemDetailBySKU($child['sku']);
                                    $stockItem = $stockRegistry->getStockItem($child['id'], $store->getWebsiteId());
                                    $stock = [
                                        'qty' => $stockItem->getQty(),
                                        'is_in_stock' => $stockItem->getIsInStock(),
                                    ];

                                    /*if(!isset($stock[0]['source_item_id'])) {
                                        $substock = '/V1/stockItems/';
                                        if ($storeCode) {
                                            if($storeCode =='default') {
                                                $substock = '/all/V1/stockItems/';
                                            } else {
                                                $substock = '/'.$storeCode.'/V1/stockItems/';
                                            }
                                        }
                                        $stock = $this->getStockInfo($substock.urlencode($respon['sku']));
                                    }*/
                                    $childslist[$key1]['stock'] = $stock;
                                }
                            }
                            $response['items'][$key]['childs_list'] = $childslist;
                        } else {
                            $stockItem = $stockRegistry->getStockItem($respon['id'], $store->getWebsiteId());
                            $stock = [
                                'qty' => $stockItem->getQty(),
                                'is_in_stock' => $stockItem->getIsInStock(),
                            ];
                            /*if(!isset($stock[0]['source_item_id'])) {
                                $substock = '/V1/stockItems/';
                                if($storeCode =='default') {
                                    $substock = '/all/V1/stockItems/';
                                } else {
                                    $substock = '/'.$storeCode.'/V1/stockItems/';
                                }
                                $stock = $this->getStockInfo($substock.urlencode($respon['sku']));
                            }*/
                            $response['items'][$key]['stock'] = $stock;
                        }
                    }
                }
                $returnData['success'] = $response;
            } else {
                $returnData = ['error' => ['message' => "You are not connected"]];
            }
            $this->logger->logger(
                'Product Data',
                'Product data and stock',
                json_encode($returnData),
                'api response'
            );
            return [$returnData];
        } catch (\Exception $e) {
            $this->logger->logger(
                'Product Data',
                'Product data and stock Exception',
                $e->getMessage(),
                'api response'
            );
        }
        return 'No data';
    }

    public function getRequestCurl($url)
    {
        try {
            $token = $this->config->getAccessToken();
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                'Authorization: Bearer '.$token
            ));
            $response = curl_exec($ch);
            $response = json_decode($response, 1);
            return $response;
        } catch (\Exception $e) {
            $this->logger->logger(
                'Product Data',
                'Exception',
                $e->getMessage(),
                'api response'
            );
        }

    }

    public function getStockInfo($suburl)
    {
        try {

            $token = $this->config->getAccessToken();
            $baseUrl = $this->config->getStoreurl();
            $url = $baseUrl."rest".$suburl;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    "Content-Type: application/json",
                    "Authorization: Bearer " .$token
                ]
            );
            $result = curl_exec($ch);
            $result = json_decode($result, 1);
            return $result;
        } catch (\Exception $e) {
            $this->logger->logger(
                'Product Data',
                'Exception',
                $e->getMessage(),
                'api response'
            );
        }

    }

    public function getChildsFromParent($sku)
    {
        $token = $this->config->getAccessToken();
        $baseUrl = $this->config->getStoreurl();
        $url = $baseUrl."rest/all/V1/configurable-products/".urlencode($sku)."/children";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                "Content-Type: application/json",
                "Authorization: Bearer " .$token
            ]
        );
        $result = curl_exec($ch);
        $result = json_decode($result, 1);
        return $result;
    }
}
