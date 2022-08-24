<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Fyndiq
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Ui\DataProvider\Shop;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Ced\Fyndiq\Ui\DataProvider\JobScheduler
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var $collection
     */
    public $collection;

    /**
     * @var $addFieldStrategies
     */
    public $addFieldStrategies;

    /**
     * @var $addFilterStrategies
     */
    public $addFilterStrategies;

    public $config;

    public $authSession;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\MagentoConnector\Helper\Config $config,
        \Magento\Backend\Model\Auth\Session $authSession,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        $meta = [],
        $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->config = $config;
        $this->authSession = $authSession;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->config->getAllDetails();
        $userName = $this->authSession->getUser()->getUsername();
        $userEmail = $this->authSession->getUser()->getEmail();
        if (isset($data['email']) && !empty($data['email'])) {
            $userEmail = $data['email'];
        }
        if (isset($data['username']) && !empty($data['username'])) {
            $userName = $data['username'];
        }
        $return[1] = [
            'user_name' =>  $userName,
            'user_password' =>  $data['password'],
            'store_id' =>  $data['storeID'],
            'email' =>  $userEmail,
            'token_type' =>  $data['token_type']
        ];
        return $return;
    }

    /**
     * Function addFilter
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return bool
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return true;
    }
}
