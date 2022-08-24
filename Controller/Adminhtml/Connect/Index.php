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

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_MagentoConnector::MagentoConnector';
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;
    public $resultFactory;
    public $config;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Ced\MagentoConnector\Helper\Config $config,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $param = $this->getRequest()->getParam('re-connect');
        if ($this->config->isConnected() && !$param) {
            $this->messageManager->addSuccessMessage('Connected Successfully.');
            return $redirect->setPath('*/*/success');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_MagentoConnector::MagentoConnector');
        $resultPage->getConfig()->getTitle()->prepend(__(' Connecter Information'));
        return $resultPage;
    }
}
