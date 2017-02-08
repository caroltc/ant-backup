<?php
namespace AntBackup;
use AntBackup\DbHandler\DbHandler;
use AntBackup\DbHandler\MysqlHandler;
use AntBackup\Reader\FileReader;
use AntBackup\Reader\Reader;
use AntBackup\Writer\FileWriter;
use AntBackup\Writer\Writer;

/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-7
 * Time: 下午4:08
 */
class AntBackup
{
    public $writer_type = '';
    public $db_type = 'mysql';
    public $db_username = '';
    public $db_password = '';
    public $db_host = '';
    public $db_name = '';
    public $db_port = '3306';
    public $db_charset = 'UTF-8';
    public $file_path = '';
    public $file_name = '';
    public $gz_write = false;
    /**
     * @var Writer
     */
    public $Write = null;
    /**
     * @var Reader
     */
    public $Reader = null;
    /**
     * @var DbHandler
     */
    public $DbHandler = null;

    public function __construct($data)
    {
        $this->writer_type = isset($data['writer_type']) ? $data['writer_type'] : '';
        $this->db_type = isset($data['db_type']) ? $data['db_type'] : $this->db_type;
        $this->db_username = isset($data['db_username']) ? $data['db_username'] : '';
        $this->db_password = isset($data['db_password']) ? $data['db_password'] : '';
        $this->db_host = isset($data['db_host']) ? $data['db_host'] : '';
        $this->db_name = isset($data['db_name']) ? $data['db_name'] : '';
        $this->db_port = isset($data['db_port']) ? $data['db_port'] : $this->db_port;
        $this->db_charset = isset($data['db_charset']) ? $data['db_charset'] : $this->db_charset;
        $this->file_path = isset($data['file_path']) ? $data['file_path'] : '';
        $this->file_name = isset($data['file_name']) ? $data['file_name'] : '';
        $this->gz_write = isset($data['gz_write']) ? $data['gz_write'] : $this->gz_write;

        $this->getDbHander();
        $this->getWriter();
    }

    public function dbBackup()
    {
        $data = array('db_name' => $this->db_name);
        $content = $this->DbHandler->DbBackup($data);
        $file_info = $this->Write->run($content);
        return $file_info;
    }

    public function getBackupList()
    {
        return $this->Reader->getBackUpList();
    }

    public function deleteBackup()
    {

    }

    public function dbRecover($file)
    {
        $sql_content = $this->Reader->getBackUpInfo($file);
        return $this->DbHandler->DbRecover($sql_content);
    }

    private function getWriter()
    {
        if (empty($this->Write) && $this->writer_type == 'file') {
            $this->Write = new FileWriter();
            $config = array(
                'save_path' => $this->file_path,
                'save_file_name' => $this->file_name,
                'gz_write' => $this->gz_write
            );
            $this->Write->setConfig($config);
            $this->Reader = new FileReader();
            $this->Reader->setConfig($config);
        }
    }

    private function getDbHander()
    {
        if (empty($this->DbHander) && $this->db_type == 'mysql') {
            $this->DbHandler = new MysqlHandler();
            $config = array(
                'username' => $this->db_username,
                'password' => $this->db_password,
                'host' => $this->db_host,
                'db' => $this->db_name,
                'port' => $this->db_port,
                'charset' => $this->db_charset
            );
            $this->DbHandler->setConfig($config);
        }
    }
}