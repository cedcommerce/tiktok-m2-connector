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
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Truncate extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Helper
     * @var PageFactory
     */
    public $filter;

    public $logsCollectionFactory;

    public $logsFactory;

    public $resultFactory;

    /**
     * @param Context $context
     * @param \Ced\MagentoConnector\Model\ResourceModel\Logs\CollectionFactory $logsCollectionFactory
     * @param \Ced\MagentoConnector\Model\LogsFactory $logsFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Ced\MagentoConnector\Model\ResourceModel\Logs\CollectionFactory $logsCollectionFactory,
        \Ced\MagentoConnector\Model\LogsFactory $logsFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        PageFactory $resultPageFactory
    ) {
        $this->filter = $filter;
        $this->logsCollectionFactory = $logsCollectionFactory;
        $this->logsFactory = $logsFactory;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $dataPost = $this->getRequest()->getParam('filters');
        if (isset($dataPost)) {
            $logModelIds = $this->filter->getCollection($this->logsCollectionFactory->create())->getAllIds();
            foreach ($logModelIds as $logModelId) {
                $this->logsFactory->create()
                    ->load($logModelId)
                    ->delete();
            }
            $count = count($logModelIds);
        } else {
            if (!$this->truncateLogsTable()) {
                $this->messageManager->addErrorMessage(__('Log Records Not Send, Please Check!!!'));
                return $redirect->setPath('*/*/loggrid');
            }
            $count = ' All ';
        }
        $this->messageManager->addSuccess(
            __($count .' Logs Record Delete Succesfully')
        );
        return $redirect->setPath('*/*/loggrid');
    }

    /**
     * @return bool
     */
    public function truncateLogsTable()
    {
        try {
            $model = $this->logsFactory->create();
            if ($model->getCollection()->getData()) {
                $connection = $model->getCollection()->getConnection();
                $tableName = $model->getCollection()->getMainTable();
                $connection->truncateTable($tableName);
                return true;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Logs Not Deleted, Please Check Error'));
            $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('*/*/loggrid');
        }
        return false;
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_MagentoConnector::logs');
    }
}
