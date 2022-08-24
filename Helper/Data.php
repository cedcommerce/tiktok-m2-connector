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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $storeManager;

    public $logger;

    public $configFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->configFactory = $configFactory;
        $this->logger = $logger;
    }

    public function getToken($subUrl, $param)
    {
        $returnData  = [];
        $baseUrl =  $this->storeManager->getStore()->getBaseUrl();
        $url = $baseUrl.'rest'.$subUrl;
        $requestParameters = json_encode($param);
        //@codingStandardsIgnoreStart
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestParameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($requestParameters)
            ]
        );
        $token = curl_exec($ch);
        $token = json_decode($token, 1);
        curl_close($ch);
        //@codingStandardsIgnoreEnd
        if (isset($token['message'])) {
            $returnData['message'] = $token['message'];
            $this->logger->logger(
                'Token',
                'Token Genrate Error',
                json_encode($returnData),
                'Token issue'
            );
        } else {
            $returnData['token'] = $token;
        }
        return $returnData;
    }

    public function setConfig($data)
    {
        try {
            $predata = [];
            foreach ($data as $key => $ke) {
                $predata[$key] = ['value' => $ke];
            }

            $configData = [
                'section' => 'mconnector_configuration',
                'website' => 0,
                'store' => 0,
                'groups' => ['setting' => ['fields' => $predata]]
            ];
            $configModel = $this->configFactory->create(['data' => $configData]);
            $configModel->save();
        } catch (\Exception $e) {
            $this->logger->logger(
                'Configuration save',
                'Save',
                $e->getMessage(),
                'Config'
            );
        }
    }
}
