<?php
namespace AntBackup\DbHander;

/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-7
 * Time: 下午5:31
 */
class MysqlHander implements DbHander
{
    private $username = "";
    private $password = "";
    private $host = "";
    private $db = "";
    /**
     * @var \PDO
     */
    private $connection = null;

    public function setConfig($datas)
    {
        $this->username = $datas['username'];
        $this->password = $datas['password'];
        $this->host = $datas['host'];
        $this->db = $datas['db'];
        $this->getConnection();
    }

    private function getConnection()
    {
        if (empty($this->connection)) {
            $this->connection = new \PDO("mysql:dbname=$this->db;host=$this->host", $this->username, $this->password);
        }
    }

    /**
     * @param array $data
     * @return \PDOStatement
     * @throws \Exception
     */
    public function DbBackup($data)
    {
        $content = "/* 数据库{$data['db_name']}备份" . date('Y-m-d H:i:s') . "   */";
        $stmt = $this->connection->prepare("SHOW TABLE STATUS FROM {$data['db_name']}");
        $stmt->execute();
        $all_tables = $stmt->fetchAll(); // get all tables
        if (empty($all_tables)) {
            throw new \Exception('no tables');
        }
        foreach ($all_tables as $table_data) {
            $table_name = $table_data['Name'];
            $stmt_tb = $this->connection->prepare("SHOW CREATE TABLE {$table_name}");
            $stmt_tb->execute();
            $create_datas = $stmt_tb->fetchAll();
            if (isset($create_datas[0]['Create View']) && !empty($create_datas[0]["Create View"])) {
                $content .= "\r\n /* 创建视图结构 {$table_name}  */";
                $content .= "\r\n DROP VIEW IF EXISTS {$table_name};/* MySQLReback Separation */ {$create_datas[0]['Create View']}";
            }
            if (isset($create_datas[0]["Create Table"]) && !empty($create_datas[0]["Create Table"])) {
                $content .= "\r\n /* 创建表结构 {$table_name}  */";
                $content .= "\r\n DROP TABLE IF EXISTS {$table_name};/* MySQLReback Separation */ {$create_datas[0]['Create Table']}";
            }
            $stmt_data = $this->connection->prepare("SELECT * FROM {$table_name}");
            $stmt_data->execute();
            $insert_datas = $stmt_data->fetchAll();

            $valuesArr = array();
            if (!empty($insert_datas)) {
                foreach ($insert_datas as &$y) {
                    foreach ($y as &$v) {
                        if ($v=='')                                  //纠正empty 为0的时候  返回tree
                            $v = 'null';                                    //为空设为null
                        else
                            $v = "'" . $this->connection->quote($v) . "'";       //非空 加转意符
                    }
                    $valuesArr[] = '(' . implode(',', $y) . ')';
                }
            }
            $temp = $this->chunkArrayByByte($valuesArr);
            if (is_array($temp)) {
                foreach ($temp as $v) {
                    $values = implode(',', $v) . ';/* MySQLReback Separation */';
                    if ($values != ';/* MySQLReback Separation */') {
                        $content .= "\r\n /* 插入数据 {$table_name} */";
                        $content .= "\r\n INSERT INTO {$table_name} VALUES {$values}";
                    }
                }
            }
        }
        return $content;
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function DbRecover($data)
    {
        // TODO: Implement simpleRecover() method.
    }

    /* -
       * +------------------------------------------------------------------------
       * * @ 把传过来的数据 按指定长度分割成数组
       * +------------------------------------------------------------------------
       * * @ $array 要分割的数据
       * * @ $byte  要分割的长度
       * +------------------------------------------------------------------------
       * * @ 把数组按指定长度分割,并返回分割后的数组
       * +------------------------------------------------------------------------
       */
    private function chunkArrayByByte($array, $byte = 5120) {
        $i = 0;
        $sum = 0;
        $return = array();
        foreach ($array as $v) {
            $sum += strlen($v);
            if ($sum < $byte) {
                $return[$i][] = $v;
            } elseif ($sum == $byte) {
                $return[++$i][] = $v;
                $sum = 0;
            } else {
                $return[++$i][] = $v;
                $i++;
                $sum = 0;
            }
        }
        return $return;
    }
}