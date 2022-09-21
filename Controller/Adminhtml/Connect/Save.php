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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Controller\Adminhtml\Connect;

use Ced\MagentoConnector\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Data
     */
    protected $dataHelper;
    /**
     * @var AccountFactory
     */
    protected $accounts;
    /**
     * @var CollectionFactory
     */
    protected $accountCollection;

    protected $resultFactory;

    protected $apiEndPoint;

    protected $userFactory;

    protected $storeManager;

    protected $configFactory;

    protected $config;

    protected $integrationToken;

    protected $logger;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        Context $context,
        Data $dataHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\User\Model\UserFactory $userFactory,
        \Ced\MagentoConnector\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\MagentoConnector\Helper\IntegrationToken $integrationToken,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Magento\Config\Model\Config\Factory $configFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->apiEndPoint = $apiEndPoint;
        $this->userFactory = $userFactory;
        $this->storeManager = $storeManager;
        $this->configFactory = $configFactory;
        $this->integrationToken = $integrationToken;
        $this->logger = $logger;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Function execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            $userName = $this->getRequest()->getParam('user_name', false);
            $userPassword = $this->getRequest()->getParam('user_password', false);
            $storeId = $this->getRequest()->getParam('store_id', false);
            if ($storeId !== "") {
                $storeId = $this->getRequest()->getParam('storeID');
            }
            $token_type = $this->getRequest()->getParam('token_type');
            $email = $this->getRequest()->getParam('email');
            $response = [];
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
            $baseUrl =  $this->storeManager->getStore()->getBaseUrl();
            $data = [
                "storeID" => $storeId,
                'storeCode' => $storeCode,
                "storeurl" => $baseUrl,
            ];
            if ($token_type == \Ced\MagentoConnector\Model\Source\TokenType\Options::INTEGRATION_TOKEN) {
                $response = $this->integrationToken->genrateIntegrationToken($email);
                $data["token_type"] = \Ced\MagentoConnector\Model\Source\TokenType\Options::INTEGRATION_TOKEN;
            } elseif ($userPassword) {
                $url = '/V1/integration/admin/token';
                $params = [
                    'username' => $userName,
                    'password' => $userPassword
                ];
                $data["token_type"] = \Ced\MagentoConnector\Model\Source\TokenType\Options::ADMIN_TOKEN;
                $response = $this->dataHelper->getToken($url, $params);
                $user = $this->userFactory->create()->load($userName, 'username');
                if ($user) {
                    $email = $user->getEmail();
                    $data["username"] = $userName;
                    $data["expireTime"] = $this->config->adminTokenTime();
                    $data["tokenTime"] = $this->config->currentTime();
                    $data['password'] = $userPassword;
                } else {
                    $this->messageManager->addErrorMessage(__("User not found with user name :".$userName));
                }
            }

            if (isset($response['token'])) {
                $data["AcessToken"] = $response['token'];
                $data["email"] = $email;
                $sendData = $data;
                unset($sendData['password']);
                $returnRes = ['success' => 1 ];
                if (isset($returnRes['success'])) {
                    $data['is_connected'] = true;
                    $data['user_id'] = isset($returnRes['user_id']) ? $returnRes['user_id'] : '';
                    $data['dashboard_url'] = isset($returnRes['dashboard_url']) ?
                        $returnRes['dashboard_url'] : '';
                    $this->messageManager->addSuccessMessage(__("Connected Successfully ."));
                } else {
                    $data['is_connected'] = false;
                    $this->messageManager->addErrorMessage(__("Unable to connect please connect with ced team."));
                }
                $this->dataHelper->setConfig($data);
                return $redirect->setPath('*/*/success');

            } elseif (isset($response['message'])) {
                $data['is_connected'] = false;
                $this->messageManager->addErrorMessage(__($response['message']));
            } else {
                $data['is_connected'] = false;
                $this->messageManager->addErrorMessage(__("Invalid Details please try again."));
            }
            $this->dataHelper->setConfig($data);
            return $redirect->setPath('*/*/index/id/1');
        } catch (\Exception $e) {
            $this->logger->logger(
                'Connection ',
                'Save',
                $e->getMessage(),
                'Details save'
            );
            $data['is_connected'] = false;
            $this->dataHelper->setConfig($data);
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            return $redirect->setPath('*/*/index/id/1');
        }
    }
}
