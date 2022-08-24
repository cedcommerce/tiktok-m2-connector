<?php
namespace Ced\MagentoConnector\Api;

interface CurrencyListInterface
{
    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getCurrency($userid);
}
