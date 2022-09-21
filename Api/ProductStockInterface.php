<?php
namespace Ced\MagentoConnector\Api;

interface ProductStockInterface
{
    /**
     * @param mixed $data
     * @return mixed|string
     */
    public function getProductsAndStock($data);

}
