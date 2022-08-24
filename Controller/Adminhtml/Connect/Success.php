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

class Success extends \Magento\Backend\App\Action
{
   // const ADMIN_RESOURCE = 'Ced_MagentoConnector::MagentoConnector';
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    public $config;

    public $resultFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\MagentoConnector\Helper\Config $config
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\MagentoConnector\Helper\Config $config,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        if (!$this->config->isConnected()) {
            $this->messageManager->addErrorMessage('Your magento not connected. Please try again.');
            return $redirect->setPath('*/*/index/id/1');
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_MagentoConnector::MagentoConnector');
        $resultPage->getConfig()->getTitle()->prepend(__('Connecter Information'));

        return $resultPage;
    }
}
