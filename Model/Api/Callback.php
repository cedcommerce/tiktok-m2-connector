<?php
namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\CallbackInterface;

class Callback implements CallbackInterface
{

    public $logger;

    public $factory;

    public $dataHelper;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Data $dataHelper,
        \Magento\Config\Model\Config\Factory $factory
    ) {
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->factory = $factory;
    }

    /**
     * @param mixed $data
     * @return mixed|string
     */
    public function setData($data)
    {
        try {
            $return = [];
            if (isset($data)) {
                $preData = [];
                if (isset($data['success']) && $data['success'] == true && isset($data['user_id'])) {
                    $preData['is_connected'] = true;
                    $preData['setup_upgrade'] = false;
                    $preData['user_id'] = $data['user_id'];
                    $preData['dashboard_url'] = isset($data['dashboard_url']) ?
                        $data['dashboard_url'] : '';
                    $return['success'] = 'Done.';
                } else {
                    $preData['is_connected'] = false;
                    $return['error'] = 'Some details is missing.';
                }
                $this->dataHelper->setConfig($preData);
            } else {
                $return['error'] = ' InCorrect Data.';
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $return['error'] = $error;
        }

        $this->logger->logger(
            'CallBack api',
            'CallBack',
            json_encode($return),
            'api response'
        );

        return [$return];
    }
}
