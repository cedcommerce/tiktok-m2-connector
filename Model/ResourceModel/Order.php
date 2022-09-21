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

namespace Ced\MagentoConnector\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Ced\MagentoConnector\Model\Order::TABLE_NAME,
            \Ced\MagentoConnector\Model\Order::ID
        );
    }
}
