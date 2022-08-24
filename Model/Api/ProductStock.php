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
     * @return mixed|string
     */
    public function getProductsAndStock()
    {
        $returnData = [];
        if ($this->config->isConnected()) {
            $userID = $this->config->getUserId();
            $storeID = $this->config->getStoreId();
            $params = $this->request->getParams();
            $sub = '/V1/products';
            //pageSize
            if(isset($params['pageSize'])) {
                $sub .= '?searchCriteria[pageSize]='.$params['pageSize'];
            } else {
                $sub .= '?searchCriteria[pageSize]=20';
            }
            if(isset($params['currentPage'])) {
                $sub .= '&searchCriteria[currentPage]='.$params['currentPage'];
            }
            if(isset($params['visibility']) && ($params['visibility'] <= 4)) {
                $sub .= '&searchCriteria[filter_groups][0][filters][0][field]=visibility&searchCriteria[filter_groups][0][filters][0][value]='.$params['visibility'].'&searchCriteria[filter_groups][0][filters][0][condition_type]=eq';
            }
            $sub .= '&searchCriteria[filter_groups][0][filters][0][field]=type_id&searchCriteria[filter_groups][0][filters][0][value]=configurable&searchCriteria[filter_groups][0][filters][0][condition_type]=eq';

            $baseUrl = $this->config->getStoreurl();
            $url = $baseUrl.'rest'.$sub;
            $response = $this->getRequestCurl($url);
            if (isset($response['items'])) {
                foreach ($response['items'] as $key => $respon) {
                    if (isset($respon['type_id']) && $respon['type_id'] == 'configurable') {
                        $childslist = $this->getChildsFromParent($respon['sku']);
                        foreach ($childslist as $key1 => $child) {
                                if(isset($child['sku'])) {
                                    $stock = $this->getStockInfo('/V1/stockItems/'.$child['sku']);
                                    $childslist[$key1]['stock'] = $stock;
                                }
                        }
                       $response['items'][$key]['childs_list'] = $childslist;
                    } else {
                        $stock = $this->getStockInfo('/V1/stockItems/'.$respon['sku']);
                        $response['items'][$key]['stock'] = $stock;
                    }
                }
            }
            $returnData['success'] = $response;
        } else {
            $returnData = ['error' => ['message' => "You are not connected"]];
        }
        return [$returnData];
    }

    public function getRequestCurl($url)
    {
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
    }

    public function getStockInfo($suburl)
    {
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
