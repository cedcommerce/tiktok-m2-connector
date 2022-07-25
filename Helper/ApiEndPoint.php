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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Setup\Exception;

class ApiEndPoint extends \Magento\Framework\App\Helper\AbstractHelper
{

    const TYPE = 'live'; //sandbox live
    const FRAMEWORK = 'TIKTOK'; // TIKTOK
    //const FRAMEWORK = 'ALI'; // Aliexpress
    const URLS = [
        'ALI' => [
            'live' => [
                'AUTHENTICATION' => 'https://aliexpress-channel.remote.sellernext.com/apiconnect/request/auth?sAppId=1',
                'REFRESH_TOKEN' => 'https://aliexpress-channel.remote.sellernext.com/magentohome/request/getNewRefershToken',
                'WEB_HOOK_URL' => 'https://phfnhcoq12.execute-api.eu-west-2.amazonaws.com/live/magento_webhook_live'
            ],
            'sandbox' => [
                'AUTHENTICATION' => 'https://dev.common-remote.cedcommerce.com/apiconnect/request/auth?sAppId=2',
                'REFRESH_TOKEN' => 'https://dev.common-remote.cedcommerce.com/magentohome/request/getNewRefershToken',
                'WEB_HOOK_URL' => 'https://cr0gdi1hs5.execute-api.ap-southeast-1.amazonaws.com/v1/magento_webhook_py'
            ],
            'local' => [
                'AUTHENTICATION' => 'http://remote.local.cedcommerce.com:8080/apiconnect/request/auth?sAppId=16',
                'REFRESH_TOKEN' => 'http://remote.local.cedcommerce.com:8080/magentohome/request/getNewRefershToken',
                'WEB_HOOK_URL' => 'https://cr0gdi1hs5.execute-api.ap-southeast-1.amazonaws.com/v1/magento_webhook_py'
            ]
        ],
        'TIKTOK' => [
            'sandbox' => [
                'AUTHENTICATION' => 'https://connector-dev.demo.cedcommerce.com/remote/public/apiconnect/request/auth?sAppId=36',
                'REFRESH_TOKEN' => 'https://connector-dev.demo.cedcommerce.com/tiktok/public/magentohome/request/getNewRefershToken',
                'WEB_HOOK_URL' => 'https://cr0gdi1hs5.execute-api.ap-southeast-1.amazonaws.com/v1/magento_webhook_py'
            ],
            'live' => [
                'AUTHENTICATION' => 'https://aliexpress-channel.remote.sellernext.com/apiconnect/request/commenceAuth?sAppId=6',
                'REFRESH_TOKEN' => 'https://connector-dev.demo.cedcommerce.com/tiktok-integration/public/magentohome/request/getNewRefershToken',
                'WEB_HOOK_URL' => 'https://cr0gdi1hs5.execute-api.ap-southeast-1.amazonaws.com/v1/magento_webhook_py'
            ]
        ]
    ];

    public $config;

    public $logger;

    public function __construct(
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Logger $logger,
        Context $context
    ) {
        $this->config = $config;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function sendTokenByCurl($data)
    {
        //$url = self::TOKEN_SUB;
        $url =  self::URLS[self::FRAMEWORK][self::TYPE]['REFRESH_TOKEN'];
        $data['user_id'] = $this->config->getUserId();
        $finaleData = $data;
        $requestParameters = json_encode($finaleData);
        //@codingStandardsIgnoreStart
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestParameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($requestParameters))
        );
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        //return $response;
        curl_close($ch);
        //@codingStandardsIgnoreEnd
        if (isset($response['success'])) {
            return $response;
        } else {
            $this->logger->logger(
                'Token Send',
                'by curl',
                json_encode($response),
                'Token send magento to'
            );
            return false;
        }
    }

    public function sendTokenRedirect()
    {
        $url =  self::URLS[self::FRAMEWORK][self::TYPE]['AUTHENTICATION'];
        return $url;
    }

    public function productChangeSend($token, $data)
    {
        $data['store_url'] = $this->config->getStoreurl();
        $finaleData = [];
        $finaleData["action"] = "webhook_manage";
        $finaleData['data'] = $data;
        $url =  self::URLS[self::FRAMEWORK][self::TYPE]['AUTHENTICATION'];
        $requestParameters = json_encode($finaleData);
        //@codingStandardsIgnoreStart
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestParameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token,
                'Content-Length: ' . strlen($requestParameters))
        );
        $response = curl_exec($ch);
        $response = json_decode($response, true);

        $this->logger->logger(
            'Product change',
            'data product Change Send',
            json_encode($finaleData),
            'Response Data'.json_encode($response)
        );
        curl_close($ch);
        //@codingStandardsIgnoreEnd

        return $response;
    }

    public function orderShip($token, $data)
    {
        $data['store_url'] = $this->config->getStoreurl();
        $finaleData = [];
        $finaleData["action"] = "webhook_manage";
        $finaleData['data'] = $data;
        $url =  self::URLS[self::TYPE]['WEB_HOOK_URL'];
        //$url = self::WEBHOOK_PRODUCT_CHANGE;
        $requestParameters = json_encode($finaleData);
        //@codingStandardsIgnoreStart

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestParameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token,
                'Content-Length: ' . strlen($requestParameters))
        );
        $response = curl_exec($ch);

        $response = json_decode($response, true);
        $this->logger->logger(
            'Order Shipment',
            'Order ship Request data',
            json_encode($finaleData),
            'Response Data'.json_encode($response)
        );
        curl_close($ch);
        //@codingStandardsIgnoreEnd
        return $response;
    }
}
