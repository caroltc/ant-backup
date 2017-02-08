<?php
namespace AntBackup\Writer;
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-7
 * Time: 下午5:18
 */
class FileWrite implements Writer
{
    public $save_path = '';
    public $save_file_name = '';
    public function run($data)
    {
        $filename = $this->save_path . '/' . $this->save_file_name; //文件名为当天的日期
        $fp = fopen($filename,'w');
        fputs($fp,$data);
        fclose($fp);
        return pathinfo($filename);
    }

    public function setConfig($datas)
    {
        $this->save_path = $datas['save_path'];
        $this->save_file_name = isset($datas['save_file_name']) ? $datas['save_file_name'] : date('YmdHis')."_backup.sql";
    }
}