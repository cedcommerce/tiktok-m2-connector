<?php

namespace Ced\MagentoConnector\Block\Adminhtml\Button;

use \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Container;

/**
 * Class Gotodashboard
 */
class Gotodashboard extends Container implements ButtonProviderInterface
{

    public $iframeBlock;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Ced\MagentoConnector\Block\Adminhtml\Shop\Iframe $iframeBlock,
        array $data = []
    ) {
        $this->iframeBlock = $iframeBlock;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Go to Dashboard'),
            'on_click' => 'window.open(\'' . $this->getBackUrl() . '\',\'_blank\');',
            'class' => 'gotodashboard primary',
            'sort_order' => 0
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        $url = $this->iframeBlock->getAppUrl();
        return $url;
    }
}
