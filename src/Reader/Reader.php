<?php
namespace AntBackup\Reader;
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-7
 * Time: 下午5:19
 */
interface Reader
{
    public function setConfig($datas);
    public function getBackUpList();
    public function getBackUpInfo($data);
}