<?php

namespace Ced\MagentoConnector\Plugin\Sales\Order\Email\Container;

class OrderIdentityPlugin
{

    public $registry;

    public $config;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Ced\MagentoConnector\Helper\Config $config
    ) {
        $this->registry = $registry;
        $this->config = $config;
    }

    /**
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsEnabled(
        \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject,
        callable $proceed
    ) {
        $returnValue = $proceed();
        $isCedOrder = $this->registry->registry('is_ced_connecter_order');
        $marketplaceName = $this->registry->registry('marketplace_name');
        $confiSetting = '';
        if ($isCedOrder) {
            $returnValue = false;
            if ($this->registry->registry('is_ced_connecter_order')) {
                $this->registry->unregister('is_ced_connecter_order');
            }

            if ($this->registry->registry('marketplace_name')) {
                $this->registry->unregister('marketplace_name');
            }
        }
        return $returnValue;
    }
}
