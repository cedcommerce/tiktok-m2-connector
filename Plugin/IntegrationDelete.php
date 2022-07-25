<?php
namespace Ced\MagentoConnector\Plugin;

class IntegrationDelete
{
    public $logger;

    public $config;

    public $dataHelper;

    public $request;

    public $apiEndPoint;

    public $integrationFactory;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Data $dataHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\Integration\Model\IntegrationFactory $integrationFactory
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->apiEndPoint = $apiEndPoint;
        $this->integrationFactory = $integrationFactory;
    }

    public function aroundExecute(
        \Magento\Integration\Controller\Adminhtml\Integration\Delete $subject,
        \Closure $proceed
    ) {
        // logging to test override
        $id = $this->request->getParam('id');
        $tokenType = $this->config->getTokenType();
        if ($this->config->isConnected() &&
            $tokenType == \Ced\MagentoConnector\Model\Source\TokenType\Options::INTEGRATION_TOKEN) {
            $integration =  $this->integrationFactory->create()->load($id);
            $integrationExistsId =  $this->integrationFactory->create()
                ->load(\Ced\MagentoConnector\Helper\IntegrationToken::NAME, 'name')
                ->getId();
            $data = [];
            if (!empty($integration) && $integration->getId() == $integrationExistsId) {
                $data['token_type'] = $this->config->getTokenType();
                $data['storeurl'] = $this->config->getStoreurl();
                $data['integration_delete'] = true;
                $returnRes = $this->apiEndPoint->sendTokenByCurl($data);
                $saveData = [
                    'is_connected' => false,
                    'integration_delete' => true,
                ];
                $this->dataHelper->setConfig($saveData);
                $this->logger->logger(
                    'Integration',
                    'Delete',
                    'Integration Delete : '.json_encode($returnRes),
                    'Delete.'.$integration->getName()
                );
            }
        }
        $returnValue = $proceed();
        return $returnValue;
    }
}
