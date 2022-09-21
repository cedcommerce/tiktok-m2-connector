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
namespace Ced\MagentoConnector\Helper;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean;

class Carrierslist extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $shippingConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->shippingConfig = $shippingConfig;
        parent::__construct($context);
    }

    public function getShippingMethods($storeId)
    {
        $carriers = [];
        $carrierInstances = $this->shippingConfig->getAllCarriers($storeId);
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }
}
