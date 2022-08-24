<?php
namespace Ced\MagentoConnector\Api;

interface AttributeOptionsInterface
{
    /**
     * @param string $attrCode
     * @return mixed|string
     */
    public function getAttributOptions($attrCode);
}
