<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-7
 * Time: 下午5:35
 */

namespace AntBackup\DbHander;


interface DbHander
{
    public function setConfig($datas);

    /**
     * @param array $data
     * @return String
     */
    public function DbBackup($data);

    /**
     * @param string $data
     * @return mixed
     */
    public function DbRecover($data);
}