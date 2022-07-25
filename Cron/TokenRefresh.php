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

namespace Ced\MagentoConnector\Cron;

class TokenRefresh
{

    protected $config;

    protected $data;

    protected $logger;

    protected $configFactory;

    public function __construct(
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Data $data,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Ced\MagentoConnector\Helper\Logger $logger
    ) {
        $this->config = $config;
        $this->data = $data;
        $this->configFactory = $configFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            if ($this->config->isConnected()) {
                $allDetails = $this->config->getAllDetails();
                if (isset($allDetails['username']) && $allDetails['password']) {
                    $params = [
                        'username' => $allDetails['username'],
                        'password' => $allDetails['password']
                    ];
                    $url = '/V1/integration/admin/token';
                    $response = $this->data->getToken($url, $params);
                    if (isset($response['token'])) {
                        $allDetails['AccessToken'] = $response['token'];
                        $returnRes = $this->apiEndPoint->sendToken($allDetails);
                        $predata = ["AccessToken" => $response['token']];
                        $predata["tokenTime"] = $this->config->currentTime();
                        $this->data->setConfig($predata);
                    } else {
                        $this->logger->logger(
                            'Token Genrate',
                            'By cron Token refresh',
                            json_encode($response),
                            'Refresh token'
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }
}
