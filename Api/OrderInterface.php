<?php
namespace Ced\MagentoConnector\Api;

interface OrderInterface
{
    /**
     * POST for test api
     * @param mixed $data
     * @return mixed|string
     */

    public function setData($data);

    /**
     * @param int $id
     * @return mixed|string
     */
    public function cancelOrder($id);
}
