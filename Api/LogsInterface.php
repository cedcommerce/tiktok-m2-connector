<?php
namespace Ced\MagentoConnector\Api;

interface LogsInterface
{
    /**
     * @return mixed|string
     */
    public function getLogs();

    /**
     * @return mixed|string
     */
    public function deleteLogs();
}
