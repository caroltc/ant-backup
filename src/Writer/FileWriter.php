<?php
namespace AntBackup\Writer;
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-7
 * Time: 下午5:18
 */
class FileWriter implements Writer
{
    public $save_path = '';
    public $save_file_name = '';
    public $gz_write = false;
    public function run($data)
    {
        $filename = $this->save_path . '/' . $this->save_file_name;
        if ($this->gz_write) {
            if (function_exists('gzwrite')) {
                if ($gz = gzopen($filename, 'w')) {
                    gzwrite($gz, $data);
                    gzclose($gz);
                } else {
                    throw new \Exception('write failed! check disk space or permission');
                }
            } else {
                throw new \Exception('gzip extend not install');
            }
        } else {
            $fp = fopen($filename,'w');
            fputs($fp,$data);
            fclose($fp);
        }
        return $filename;
    }

    public function setConfig($datas)
    {
        $this->save_path = $datas['save_path'];
        $this->gz_write = $datas['gz_write'];
        $this->save_file_name = isset($datas['save_file_name']) && !empty($datas['save_file_name']) ? $datas['save_file_name'] : date('YmdHis')."_backup";
        $this->save_file_name .= $this->gz_write === false ? '.sql' : '.gz';
    }
}