<?php

namespace Ced\MagentoConnector\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminUserSave implements ObserverInterface
{

    public $request;
    public $helperData;
    public $logger;
    public $config;
    public $userFactory;
    public $apiEndPoint;
    public $storeManager;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Data $helperData,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\UserFactory $userFactory,
        \Ced\MagentoConnector\Helper\Config $config
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->apiEndPoint = $apiEndPoint;
        $this->userFactory = $userFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->config->getUserId()) {
                $data = $this->request->getParams();
                $user_id = isset($data['user_id']) ? $data['user_id'] : '';
                $userName = isset($data['username']) ? $data['username'] : '';
                $saveUser = $this->config->getUserName();
                $user = $this->userFactory->create()->load($saveUser, 'username');

                $tokenType = $this->config->getTokenType();
                if (!empty($user) && $user->getId() == $user_id &&
                    $tokenType == \Ced\MagentoConnector\Model\Source\TokenType\Options::ADMIN_TOKEN) {
                    if (isset($data['password'])
                        && isset($data['password_confirmation']) &&
                        !empty($data['password']) &&
                        $this->config->getUserPassword() != $data['password']) {
                        $newData = [
                            'username' => $userName,
                            'password' => $data['password']
                        ];
                        $url = '/V1/integration/admin/token';
                        $response = $this->helperData->getToken($url, $newData);

                        $data = [
                            "username" => $userName,
                            "password" => $data['password'],
                            "oldpassword" => $this->config->getUserPassword(),
                            "email" => $user->getEmail(),
                            "AccessToken" => $this->config->getAccessToken(),
                        ];
                        $this->helperData->setConfig($data);
                        $baseUrl =  $this->storeManager->getStore()->getBaseUrl();

                        $data = [
                            "username" => $userName,
                            "expireTime" => $this->config->adminTokenTime(),
                            "tokenTime" => $this->config->currentTime(),
                            "email" => $user->getEmail(),
                            'storeurl' => $baseUrl,
                            'passwordChange' => true,
                            'token_type' => \Ced\MagentoConnector\Model\Source\TokenType\Options::ADMIN_TOKEN
                        ];
                        if (isset($response['token'])) {
                            $data['AccessToken'] = $response['token'];
                            $this->helperData->setConfig(["AccessToken" => $response['token']]);
                            $sendData = $data;
                            unset($sendData['password']);
                            unset($sendData['oldpassword']);
                            $returnRes = $this->apiEndPoint->sendTokenByCurl($data);
                            $this->logger->logger(
                                'Token send',
                                'User name '.$userName,
                                json_encode($returnRes),
                                'Admin user save token send Response'
                            );

                        } else {

                            $this->logger->logger(
                                'Token not genrate for user ',
                                'User name '.$userName,
                                json_encode($response),
                                'Admin user save token not genrate'
                            );

                            $returnRes = $this->apiEndPoint->sendTokenByCurl($data);
                            $this->logger->logger(
                                'Token not genrate for user ',
                                'User name '.$userName,
                                json_encode($data),
                                'Reponse .'.json_encode($returnRes)
                            );
                        }
                    }
                }
            }
        } catch (\Execption $e) {
            $this->logger->logger(
                'Admin change user password',
                'Save',
                $e->getMessage(),
                'Admin user save'
            );
            return $observer;
        }
        return $observer;
    }
}
