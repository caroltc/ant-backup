<?php
namespace AntBackup\DbHandler;

/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-7
 * Time: 下午5:31
 */
class MysqlHandler implements DbHandler
{
    private $username = "";
    private $password = "";
    private $host = "";
    private $db = "";
    private $port = "3306";
    private $charset = "UTF-8";
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
        $this->port = $datas['port'];
        $this->charset = $datas['charset'];
        $this->getConnection();
    }

    private function getConnection()
    {
        if (empty($this->connection)) {
            $this->connection = new \PDO("mysql:dbname={$this->db};host={$this->host};port={$this->port};", $this->username, $this->password);
            $this->connection->exec("set names {$this->charset}");
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
        $all_tables = $stmt->fetchAll(\PDO::FETCH_ASSOC); // get all tables
        if (empty($all_tables)) {
            throw new \Exception('no tables');
        }
        foreach ($all_tables as $table_data) {
            $table_name = $table_data['Name'];
            $stmt_tb = $this->connection->prepare("SHOW CREATE TABLE {$table_name}");
            $stmt_tb->execute();
            $create_datas = $stmt_tb->fetchAll(\PDO::FETCH_ASSOC);
            if (isset($create_datas[0]['Create View']) && !empty($create_datas[0]["Create View"])) {
                $content .= "\r\n /* 创建视图结构 {$table_name}  */";
                $content .= "\r\n DROP VIEW IF EXISTS {$table_name};/* MySQLReback Separation */{$create_datas[0]['Create View']};/* MySQLReback Separation */";
            }
            if (isset($create_datas[0]["Create Table"]) && !empty($create_datas[0]["Create Table"])) {
                $content .= "\r\n /* 创建表结构 {$table_name}  */";
                $content .= "\r\n DROP TABLE IF EXISTS {$table_name};/* MySQLReback Separation */{$create_datas[0]['Create Table']};/* MySQLReback Separation */";
            }
            $stmt_data = $this->connection->prepare("SELECT * FROM {$table_name}");
            $stmt_data->execute();
            $insert_datas = $stmt_data->fetchAll(\PDO::FETCH_ASSOC);

            $valuesArr = array();
            if (!empty($insert_datas)) {
                foreach ($insert_datas as &$y) {
                    foreach ($y as &$v) {
                        if ($v=='')                                  //纠正empty 为0的时候  返回tree
                            $v = 'null';                                    //为空设为null
                        else
                            $v = $this->connection->quote($v);       //非空 加转意符
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
     * @return boolean
     */
    public function DbRecover($data)
    {
        if (empty($data)) {
            return false;
        }
        $content = explode(';/* MySQLReback Separation */', $data);
        $this->connection->beginTransaction();
        try {
            foreach ($content as $i => $sql) {
                $sql = trim($sql);
                if (!empty($sql)) {
                    $mes = $this->connection->exec($sql);
                    if (false === $mes) {                                       //如果 null 写入失败，换成 ''
                        $table_change = array('null' => '\'\'');
                        $sql = strtr($sql, $table_change);
                        $mes = $this->connection->exec($sql);
                    }
                    if (false === $mes) {
                        throw new \Exception('Recover Failed In Sql: '. $sql);
                    }
                }
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return false;
        }
        return true;
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