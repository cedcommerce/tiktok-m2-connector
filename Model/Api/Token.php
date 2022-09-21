<?php
namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\TokenInterface;
use \Ced\MagentoConnector\Model\Source\TokenType\Options;

class Token implements TokenInterface
{
    public $logger;

    public $config;

    public $dataHelper;

    public $integrationToken;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Data $dataHelper,
        \Ced\MagentoConnector\Helper\IntegrationToken $integrationToken
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->integrationToken = $integrationToken;
    }

    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getRefreshToken($userid)
    {
        $returnResponse = [];
        if ($this->config->isConnected()) {
            $userID = $this->config->getUserId();
            $response = [];
            if ($userID == $userid) {
                $data = [];
                $allDetails = $this->config->getAllDetails();
                if (isset($allDetails['token_type']) &&
                    $allDetails['token_type'] == Options::INTEGRATION_TOKEN) {
                    $email = $allDetails['email'];
                    $response = $this->integrationToken->genrateIntegrationToken($email);
                } elseif (isset($allDetails['username']) && isset($allDetails['password'])) {
                    $userName = $allDetails['username'];
                    $password = $allDetails['password'];
                    $subUrl = '/V1/integration/admin/token';
                    $params = [
                        'username' => $userName,
                        'password' => $password
                    ];
                    $response = $this->dataHelper->getToken($subUrl, $params);
                    $data['tokenTime'] = $this->config->currentTime();
                } else {
                    $returnResponse = ['error' => ['message' => "User Password."]];
                }

                if (isset($response['token'])) {
                    $data['AccessToken'] = $response['token'];
                    $this->dataHelper->setConfig($data);
                    $returnResponse['success']['token'] = $response['token'];
                    if (isset($allDetails['token_type']) &&
                        $allDetails['token_type'] != Options::INTEGRATION_TOKEN) {
                        $returnResponse['success']['expireTime'] = $this->config->adminTokenTime();
                    }
                } elseif (isset($response['message'])) {
                    $returnResponse = ['error' => ['message' => $response['message']]];
                } else {
                    $returnResponse = ['error' => ['message' => $response]];
                }
            } else {
                $returnResponse = ['error' => ['message' => "Invalid User Id."]];
            }
        } else {
            $returnResponse = ['error' => ['message' => "you are not connected"]];
        }
        return [$returnResponse];
    }
}
