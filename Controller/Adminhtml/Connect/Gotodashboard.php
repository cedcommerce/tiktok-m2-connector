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
    public $config;
    public $apiEndPoint;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->apiEndPoint = $apiEndPoint;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        if ($this->config->isConnected()) {
            /*$data = $this->config->getAllDetails();
            if (isset($data['user_id'])) {
                $data['userid'] = $data['user_id'];
                unset($data['user_id']);
            }
            $url = $this->apiEndPoint->sendTokenRedirect();
            $subUrl  = http_build_query($data);
            $url = $url.'&'.$subUrl;*/

            return $redirect->setPath("*/*/app");
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_MagentoConnector::MagentoConnector');
        $resultPage->getConfig()->getTitle()->prepend(__('Connection Info'));
        return $resultPage;
    }
}
