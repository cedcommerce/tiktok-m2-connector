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
 * @author     CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Setup\Exception;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const IS_CONNECTED = 'mconnector_configuration/setting/is_connected';
    const SETTING_PATH = 'mconnector_configuration/setting/';
    const ADMIN_TOKEN_TIME = 'oauth/access_token_lifetime/admin';

    public $scopeConfigManager;

    public $dateTime;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        Context $context
    ) {
        $this->scopeConfigManager = $scopeConfig;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    public function isConnected()
    {
        $value = $this->scopeConfig->getValue(self::IS_CONNECTED);
        return $value;
    }

    public function getStoreId()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'storeID');
        return $value;
    }

    public function adminTokenTime()
    {
        $value = $this->scopeConfig->getValue(self::ADMIN_TOKEN_TIME);
        $date = $this->dateTime->gmtDate();
        if ($value) {
            $now = new \DateTime();
            $hours = (int)$value;
            $modified = (clone $now)->add(new \DateInterval("PT{$hours}H"));
            $date = $modified->getTimestamp();
        }
        return $date;
    }

    public function currentTime()
    {
        $date = $this->dateTime->gmtDate();
        return $date;
    }

    public function getUserId()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'user_id');
        return $value;
    }

    public function getAccessToken()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'AccessToken');
        return $value;
    }

    public function getStoreurl()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'storeurl');
        return $value;
    }

    public function getUserName()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'username');
        return $value;
    }

    public function getTokenType()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'token_type');
        return $value;
    }

    public function getUserPassword()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'password');
        return $value;
    }

    public function getUserOldPassword()
    {
        $value = $this->scopeConfig->getValue(self::SETTING_PATH.'oldpassword');
        return $value;
    }

    public function getAllDetails()
    {
        $returnData = [];
        $keys = [
            "AccessToken",
            "username",
            "storeID",
            'storeCode',
            "storeurl",
            "expireTime",
            "email",
            'password',
            'token_type',
            'user_id',
            'oldpassword'
        ];
        foreach ($keys as $key) {
            $value = $this->scopeConfig->getValue(self::SETTING_PATH.''.$key);
            $returnData[$key] = $value;
        }
        return $returnData;
    }
}
