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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Controller\Adminhtml\Connect;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Gotodashboard extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_MagentoConnector::MagentoConnector';
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;
    public $resultFactory;
    public $integrationToken;
    public $helper;
    public $apiEndPoint;
    public $logger;
    public $config;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Ced\MagentoConnector\Helper\IntegrationToken $integrationToken
     * @param \Ced\MagentoConnector\Helper\Data $helper
     * @param \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint
     * @param \Ced\MagentoConnector\Helper\Logger $logger
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Ced\MagentoConnector\Helper\IntegrationToken $integrationToken,
        \Ced\MagentoConnector\Helper\Data $helper,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->integrationToken = $integrationToken;
        $this->helper = $helper;
        $this->apiEndPoint = $apiEndPoint;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //check after upgrade token
        $setupFlag = $this->config->isSetupUpgradeFlag();
        if($setupFlag) {
            $token = $this->integrationToken->genrateIntegrationToken($this->config->getEmail());
            if(isset($token['token'])) {
                $this->helper->setConfig(['AccessToken' => $token['token'], 'setup_upgrade' => false]);
                $data = [];
                $data['token_type'] = $this->config->getTokenType();
                $data['storeurl'] = $this->config->getStoreurl();
                $data['email'] = $this->config->getEmail();
                $data['token'] = $token['token'];
                $returnRes = $this->apiEndPoint->sendTokenByCurl($data);
                $this->logger->logger(
                    'Integration Token',
                    'Regenrate ',
                    json_encode($returnRes),
                    'Token Regenrate'
                );
            }
        }
        //end
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        if ($this->config->isConnected()) {
            return $redirect->setPath("*/*/app");
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_MagentoConnector::MagentoConnector');
        $resultPage->getConfig()->getTitle()->prepend(__('Connection Info'));
        return $resultPage;
    }
}
