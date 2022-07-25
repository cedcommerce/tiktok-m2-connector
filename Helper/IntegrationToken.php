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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Setup\Exception;

class IntegrationToken extends \Magento\Framework\App\Helper\AbstractHelper
{

    const NAME = "Magento Connector";

    public $storeManager;

    public $logger;

    public $configFactory;

    public $integrationFactory;

    public $oauthService;

    public $authorizationService;

    public $token;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Integration\Model\IntegrationFactory $integrationFactory,
        \Magento\Integration\Model\OauthService $oauthService,
        \Magento\Integration\Model\AuthorizationService $authorizationService,
        \Magento\Integration\Model\Oauth\Token $token,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->configFactory = $configFactory;
        $this->integrationFactory = $integrationFactory;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->token = $token;
    }

    public function genrateIntegrationToken($email)
    {
        $returnData = [];
        $integrationExists = $this->integrationFactory->create()
            ->load(self::NAME, 'name')
            ->getData();
        if (!empty($integrationExists) && $integrationExists['name'] == "Magento Connector") {
            try {
                $integrationFactory = $this->integrationFactory->create();
                $integration = $integrationFactory->load($integrationExists['integration_id']);
                $integrationId = $integration->getId();
                $consumerName = 'Integration' . $integrationId;

                // Code to create consumer
                $consumer = $this->oauthService->createConsumer(['name' => $consumerName]);
                $consumerId = $consumer->getId();
                $integration->setConsumerId($consumer->getId());
                $integration->save();
                // Code to grant permission
                $this->authorizationService->grantAllPermissions($integrationId);
                // Code to Activate and Authorize
                $uri = $this->token->createVerifierToken($consumerId);
                $this->token->setType('access');
                $this->token->save();
                $token = $this->token->getData('token');
                if ($token) {
                    $returnData['token'] = $token;
                } else {
                    $returnData['message'] = "Please retry !!";
                }
            } catch (Exception $e) {
                $returnData['message'] = $e->getMessage();
            }
        }
        return $returnData;
    }
}
