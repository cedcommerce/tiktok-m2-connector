<?php

namespace Ced\MagentoConnector\Block\Adminhtml\Shop;

class Iframe extends \Magento\Backend\Block\Template
{
    public $_template = "shopconnection/iframe.phtml";

    public $config;

    public $apiEndPoint;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        array $data = []
    ) {
        $this->apiEndPoint = $apiEndPoint;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Magento Contructor
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_template);
    }

    public function checkDetails()
    {
        $value = $this->config->isConnected();
        return $value;
    }

    public function checkAllDetails()
    {
        $value = $this->config->getAllDetails();
        return $value;
    }

    public function getAppUrl()
    {
        $array = $this->config->getAllDetails();
        $array['tokenTime'] = $this->config->currentTime();
        $array['storeID'] = $array['storeCode'];
        unset($array['user_id']);
        unset($array['storeCode']);
        unset($array['oldpassword']);
        unset($array['password']);
        $array['time'] = time();
        $url = $this->apiEndPoint->sendTokenRedirect();
        $subUrl  = http_build_query($array);
        $url = $url.'&'.$subUrl;
        return $url;
    }
}
