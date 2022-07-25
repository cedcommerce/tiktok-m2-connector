<?php

namespace Ced\MagentoConnector\Block\Adminhtml\Button;

use \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Container;

/**
 * Class Gotodashboard
 */
class ReConnect extends Container implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('ReConnect'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'reconnect secondary',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index/id/1/re-connect/1');
    }
}
