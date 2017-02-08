<?php
namespace AntBackup;
use AntBackup\DbHander\DbHander;
use AntBackup\DbHander\MysqlHander;
use AntBackup\Writer\FileWrite;
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
    public $file_path = '';
    public $file_name = '';
    /**
     * @var Writer
     */
    public $Write = null;
    /**
     * @var DbHander
     */
    public $DbHander = null;

    public function __construct($data)
    {
        $this->writer_type = isset($data['writer_type']) ? $data['writer_type'] : '';
        $this->db_type = isset($data['writer_type']) ? $data['writer_type'] : $this->db_type;
        $this->db_username = isset($data['db_username']) ? $data['db_username'] : '';
        $this->db_password = isset($data['db_password']) ? $data['db_password'] : '';
        $this->db_host = isset($data['db_host']) ? $data['db_host'] : '';
        $this->db_name = isset($data['db_name']) ? $data['db_name'] : '';
        $this->file_path = isset($data['file_path']) ? $data['file_path'] : '';
        $this->file_name = isset($data['file_name']) ? $data['file_name'] : '';

        $this->getDbHander();
        $this->getWriter();
    }

    public function dbBackup()
    {
        $data = array('db_name' => $this->db_name);
        $content = $this->DbHander->DbBackup($data);
        $file_info = $this->Write->run($content);
        return $file_info;
    }

    public function dbRecover()
    {

    }

    private function getWriter()
    {
        if (empty($this->Write) && $this->writer_type == 'file') {
            $this->Write = new FileWrite();
            $config = array(
                'save_path' => $this->file_path,
                'save_file_name' => $this->file_name
            );
            $this->Write->setConfig($config);
        }
    }

    private function getDbHander()
    {
        if (empty($this->DbHander) && $this->db_type == 'mysql') {
            $this->DbHander = new MysqlHander();
            $config = array(
                'user_name' => $this->db_username,
                'password' => $this->db_password,
                'host' => $this->db_host,
                'db' => $this->db_name
            );
            $this->DbHander->setConfig($config);
        }
    }
}