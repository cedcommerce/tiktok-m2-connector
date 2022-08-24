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

namespace Ced\MagentoConnector\Model\Payment;

class Payconnector extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * @var string $_code
     */
    public $_code = 'payconnector';
    /**
     * @var bool $_canAuthorize
     */
    public $_canAuthorize = true;
    /**
     * @var bool $_canCancelInvoice
     */
    public $_canCancelInvoice = false;
    /**
     * @var bool $_canCapture
     */
    public $_canCapture = false;
    /**
     * @var bool $_canCapturePartial
     */
    public $_canCapturePartial = false;
    /**
     * @var bool $_canCreateBillingAgreement
     */
    public $_canCreateBillingAgreement = false;//
    /**
     * @var bool $_canFetchTransactionInfo
     */
    public $_canFetchTransactionInfo = false;
    /**
     * @var bool $_canManageRecurringProfiles
     */
    public $_canManageRecurringProfiles = false;//
    /**
     * @var bool $_canOrder
     */
    public $_canOrder = false;
    /**
     * @var bool $_canRefund
     */
    public $_canRefund = false;
    /**
     * @var bool $_canRefundInvoicePartial
     */
    public $_canRefundInvoicePartial = false;
    /**
     * @var bool $_canReviewPayment
     */
    public $_canReviewPayment = false;
  /* Setting for disable from front-end. */
  /* START */
    /**
     * @var bool $_canUseCheckout
     */
    public $_canUseCheckout = false;
    /**
     * @var bool $_canUseForMultishipping
     */
    public $_canUseForMultishipping = false;//
    /**
     * @var bool $_canUseInternal
     */
    public $_canUseInternal = false;
    /**
     * @var bool $_canVoid
     */
    public $_canVoid = false;
    /**
     * @var bool $_isGateway
     */
    public $_isGateway = false;
    /**
     * @var bool $_isInitializeNeeded
     */
    public $_isInitializeNeeded = false;

  /* END */

    /**
     * Function isAvailable
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    /**
     * Function getCode
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }
}
