<?php
namespace Ced\MagentoConnector\Plugin;

class AdminUserDelete
{

    public $logger;

    public $config;

    public $dataHelper;

    public $apiEndPoint;

    public $userFactory;

    public $request;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Data $dataHelper,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->apiEndPoint = $apiEndPoint;
        $this->userFactory = $userFactory;
        $this->request = $request;
    }

    public function aroundExecute(\Magento\User\Controller\Adminhtml\User\Delete $subject, \Closure $proceed)
    {
        // logging to test override
        $data =  $this->request ->getParams();
        $tokenType = $this->config->getTokenType();
        if ($this->config->isConnected() &&
            $tokenType == \Ced\MagentoConnector\Model\Source\TokenType\Options::ADMIN_TOKEN) {
            $user_id = isset($data['user_id']) ? $data['user_id'] : '';
            $saveUserName = $this->config->getUserName();
            $user = $this->userFactory->create()->load($saveUserName, 'username');
            $data = [];
            if (!empty($user) && $user->getId() == $user_id) {
                $data['token_type'] = $this->config->getTokenType();
                $data['storeurl'] = $this->config->getStoreurl();
                $data['user_delete'] = true;
                $returnRes = $this->apiEndPoint->sendTokenByCurl($data);
                $saveData = [
                    'is_connected' => false,
                    'username' => '',
                    'password' => '',
                    'user_delete' => true,
                ];
                $this->dataHelper->setConfig($saveData);
                $this->logger->logger(
                    'Admin User',
                    'Delete',
                    'Admin user Delete : '.json_encode($returnRes),
                    'Delete'.$user_id
                );
            }
        }
        $returnValue = $proceed();
        return $returnValue;
    }
}
