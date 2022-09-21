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

class Disconnect extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_MagentoConnector::MagentoConnector';
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;
    public $resultFactory;
    public $config;
    public $apiEndPoint;
    public $dataHelper;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint
     * @param \Ced\MagentoConnector\Helper\Data $dataHelper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Ced\MagentoConnector\Helper\Data $dataHelper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->apiEndPoint = $apiEndPoint;
        $this->dataHelper = $dataHelper;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        if ($this->config->isConnected()) {
            $postedData = [];
            $data = $this->config->getAllDetails();
            if (isset($data['token_type']) && $data['token_type'] == 'integration_token') {
                $postedData['token_type'] = 'integration_token';
            } else {
                $postedData['token_type'] = 'admin_token';
            }
            $postedData['email'] = $data['email'];
            $postedData['storeurl'] = $data['storeurl'];
            $postedData['status'] = 'disconnect';
            $response = $this->apiEndPoint->sendTokenByCurl($postedData);
            if(isset($response['success']) && isset($response['status']) && $response['status'] == "disable") {
                $this->dataHelper->setConfig(['is_connected' => false, 'disconnect' => true]);
                $this->messageManager->addSuccessMessage('App DisConnected Successfully.');
                return $redirect->setPath("*/*/index");
            } else {
                $this->dataHelper->setConfig(['is_connected' => true, 'disconnect' => false]);
                $this->messageManager->addErrorMessage("We can't disconnect your app, Please try to connect with cedcommerce.");
                return $redirect->setPath("*/*/success");
            }
        } else {
            $this->messageManager->addErrorMessage("You are not connected, Please try to connect.");
            return $redirect->setPath("*/*/index/");
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_MagentoConnector::MagentoConnector');
        $resultPage->getConfig()->getTitle()->prepend(__('Connection Info'));
        return $resultPage;
    }
}
