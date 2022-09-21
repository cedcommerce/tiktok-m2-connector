<?php

namespace Ced\MagentoConnector\Model\Api;

use Ced\MagentoConnector\Api\LogsInterface;

class Logs implements LogsInterface
{

    public $collectionFactory;

    public function __construct(
        \Ced\MagentoConnector\Model\ResourceModel\Logs\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }
    /**
     * @return mixed|string
     */
    public function getLogs()
    {
        $data = $this->collectionFactory->create()
            ->setOrder('id', 'DESC')
            ->setPageSize(20)
            ->getData();
        return [$data];

    }

    /**
     * @return mixed|string
     */
    public function deleteLogs()
    {
        $collection = $this->collectionFactory->create();
        foreach ($collection as $coll) {
            $collection->walk('delete');
        }
        return [['success' => true, 'message' => 'Log Deleted']];
    }

}
