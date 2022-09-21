<?php
namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\OrderInterface;

class Order implements OrderInterface
{

    public $logger;

    public $config;

    public $orderHelper;

    public $orderFactory;

    public $orderRepository;

    public $statusCollectionFactory;

    public function __construct(
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\Order $orderHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->orderHelper = $orderHelper;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * @param mixed $data
     * @return mixed|string
     */
    public function setData($data)
    {
        $return = [];
        try {
            if ($this->config->isConnected() || true) {
                if (isset($data['orders'])) {
                    $return = $this->orderHelper->fetchLatestOrders($data);
                } else {
                    $return['error'] = ' InCorrect Data.';
                }
            } else {
                $return = ['error' => ['message' => "You are not connected"]];
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
            $return['error'] = $error;
        }
        $this->logger->logger(
            'OrderCreate api',
            'OrderCreate',
            json_encode($return),
            'api response'
        );
        return [$return];
    }

    /**
     * @param int $id
     * @return \Magento\Setup\Module\Dependency\Parser\Composer\Json|mixed|string
     */
    public function cancelOrder($id)
    {
        $return = [];
        try {
            if ($this->config->isConnected()) {
                if (!$id) {
                    $return['error'] = 'Order id is required :'. $id;
                    return [$return];
                }
                $order = $this->orderRepository
                    ->get($id);
                if ($order && $order->getId()) {
                    if ($order->getData('state') !== 'canceled') {
                        $order = $this->orderFactory->create()->load($id);
                        $order->setState("canceled")->setStatus("canceled")->save();
                        $order = $this->orderRepository->get($id);
                        $status = $order->getData('state');
                        if ($status == 'canceled') {
                            $return['success']['message'] = 'Order canceled';
                            $return['success']['order_id'] = $id;
                        }
                    } else {
                        $return['success']['message'] = 'Order already canceled';
                        $return['success']['order_id'] = $id;
                    }
                } else {
                    $return['error'] = 'Order Not found with id :'. $id;
                }
            } else {
                $return = ['error' => ['message' => "You are not connected"]];
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
            $return['error'] = $error;
        }

        $this->logger->logger(
            'cancelOrder api',
            'cancelOrder',
            json_encode($return),
            'api response'
        );
        return [$return];
    }

    /**
     * @return mixed|string
     */
    public function getStatusList()
    {
        $returnData = [];
        if ($this->config->isConnected()) {
            $returnData = $this->statusCollectionFactory->create()->toOptionArray();
        } else {
            $returnData = ['error' => ['message' => "You are not connected"]];
        }
        $this->logger->logger(
            'Order Status List',
            'OrderStatus api',
            json_encode($returnData),
            'OrderStatus api response'
        );

        return [$returnData];

    }
}
