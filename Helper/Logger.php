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
 * @category  Ced
 * @package   Ced_MagentoConnector
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Helper;

class Logger extends \Monolog\Logger
{
    /*
     * Debug Flag
     */
    public $debugMode;

    public $scopeConfigManager;

    public $logs;

    public $config;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\MagentoConnector\Model\Logs $logs,
        \Ced\MagentoConnector\Helper\Config $config
    ) {
        $this->scopeConfigManager = $scopeConfig;
        $this->logs = $logs;
        $this->config = $config;
        $this->debugMode = $this->config->getDebugSetting();
    }
    public function logger(
        $type = "Test",
        $subType = "Test",
        $response = [],
        $comment = ""
    ) {

        if ($this->debugMode) {
            $this->logs->setLogType($type)
                ->setLogSubType($subType)
                ->setLogDate(date("d-m-y H:i:s"))
                ->setLogValue($response)
                ->setLogComment($comment)
                ->save();
            return true;
        }
        return false;
    }
}
