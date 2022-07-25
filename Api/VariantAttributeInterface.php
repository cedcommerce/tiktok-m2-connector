<?php
namespace Ced\MagentoConnector\Api;

interface VariantAttributeInterface
{
    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getAttributes($userid);
}
