<?php
namespace Ced\MagentoConnector\Api;

interface CarrierListInterface
{
    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getCarriers($userid);
}
