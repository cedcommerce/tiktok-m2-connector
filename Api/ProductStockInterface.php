<?php
namespace Ced\MagentoConnector\Api;

interface ProductStockInterface
{
    /**
     * @return mixed|string
     */
    public function getProductsAndStock();

}
