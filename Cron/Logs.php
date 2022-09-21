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

class Logs
{
    /**
     * @var \Ced\MagentoConnector\Model\LogsFactory
     */
    protected $logsFactory;

    /**
     * Logs constructor.
     * @param \Ced\MagentoConnector\Model\LogsFactory $logsFactory
     */
    public function __construct(
        \Ced\MagentoConnector\Model\LogsFactory $logsFactory
    ) {
        $this->logsFactory = $logsFactory;
    }

    public function execute()
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
            return false;
        }
        return false;
    }
}
