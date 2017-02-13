<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-8
 * Time: 下午3:00
 */

namespace AntBackup\Reader;


class FileReader implements Reader
{
    public $path = '';
    public function setConfig($datas)
    {
        $this->path = $datas['save_path'];
    }

    public function getBackUpList()
    {
        if(!is_dir($this->path)) {
            return false;
        }
        $files = array();
        if ($dh = opendir($this->path)) {
            while (($file = readdir($dh)) !== false) {
                $ext = strrchr($file, '.');
                if ($ext == '.sql' || $ext == '.gz') {
                    $files[] = $file;
                }
            }
            closedir($dh);
        }
        return $files;
    }

    public function getBackUpInfo($data)
    {
        $file = $this->path . '/' . $data;
        if (is_file($file)) {
            $ext = strrchr($file, '.');
            if ($ext == '.sql') {
                $content = file_get_contents($file);
            } elseif ($ext == '.gz') {
                $content = implode('', gzfile($file));
            } else {
                throw new \Exception('file not sql or gz!');
            }
        } else {
            throw new \Exception('file not exist!');
        }
        return $content;
    }
}