<?php
namespace Ced\MagentoConnector\Api;

interface CallbackInterface
{
    /**
     * POST for test api
     * @param mixed $data
     * @return mixed|string
     */

    public function setData($data);
}
