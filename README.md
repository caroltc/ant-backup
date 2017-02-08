## ant-backup
quickly backup your database

## 安装
```
composer require caroltc/ant-backup
```

### 使用示例
```
$data = array(
            'writer_type' => 'file', // 目前只支持file
            'db_type' => 'mysql',
            'db_username' => 'root',
            'db_password' => 'root',
            'db_host' => '127.0.0.1',
            'db_name' => 'ctbms_db',
            'db_port' => '3306',
            'db_charset' => 'UTF-8',
            'file_path' => '/phpstudy/www/BMS_3.0/data/backup', // 需读写权限
            'file_name' => '', // 可为空
            'gz_write' => true  // 是否开启gzip压缩,需gzip拓展
        );
        $BC = new AntBackup($data);
        
        // 备份
        $rs = $BC->dbBackup();
        print_r($rs); 
        
        // 获取备份列表
        $list = $BC->getBackupList();
        print_r($list);
        
        // 恢复指定文件
        $rs = $BC->dbRecover('20170208162648_backup.gz');
        var_dump($rs);
```

### 开发计划

- 基本备份[已完成]
- 基本恢复[已完成]
- 备份压缩[已完成]
- 从压缩文件恢复[已完成]
- 下载备份
- 上传备份恢复
- 邮件备份
- 设置备份最大数量
- 指定表备份