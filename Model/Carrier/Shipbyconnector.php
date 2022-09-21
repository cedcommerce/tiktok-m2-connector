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

namespace Ced\MagentoConnector\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use \Magento\Shipping\Model\Carrier\CarrierInterface;
use \Magento\Shipping\Model\Carrier\AbstractCarrier;

class Shipbyconnector extends AbstractCarrier implements CarrierInterface
{
    public $_code = 'shipbyconnector';

    public $_logger;
    /**
     * @var bool
     */
    public $_isFixed = true;
    /**
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    public $_rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    public $_rateMethodFactory;

    public $_state;

    public $registry;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\State $state
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\State $state,
        $data = []
    ) {
        $this->appState = $appState;
        $this->registry = $registry;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        $this->_state = $state;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect rates for this shipping method based on information in $request
     * @param \Magento\Shipping\Model\Rate\Result $request
     * @return \Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $iscedconnecterorder = $this->registry->registry('is_ced_connecter_order');
        $marketplacename = $this->registry->registry('marketplace_name');
        if($iscedconnecterorder) {
            if ($marketplacename === NULL) {
                return false;
            }
        } else {
            return false;
        }

        if ($this->appState->getAreaCode() =='crontab'
            || $this->appState->getAreaCode() =='adminhtml'
            || $this->appState->getAreaCode() == 'webapi_rest'
        ) {

            $price = $this->getConfigData('price');
            if (!$price) {
                $price = 0;
            }

            $handling = $this->getConfigFlag('handling');

            /** @var \Magento\Shipping\Model\Rate\Result $result */
            $result = $this->_rateResultFactory->create();
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setMethod($this->_code);

            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($price);
            $method->setCost(0);

            $result->append($method);
            return $result;

        } else {
            return false;
        }
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('title')];
    }

    public function getCode()
    {
        return $this->_code;
    }
}
