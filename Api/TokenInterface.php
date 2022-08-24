<?php
namespace Ced\MagentoConnector\Api;

interface TokenInterface
{
    /**
     * @param string $userid
     * @return mixed|string
     */
    public function getRefreshToken($userid);
}
