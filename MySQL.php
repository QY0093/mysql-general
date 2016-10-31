<?php

class MySQL {

    //配置文件
    protected $config;
    //database handle
    protected $dbh;
    //报错码
    protected $errno;
    //报错信息
    protected $error;
    //最近一次mysql_query的资源信息
    protected $lastQid;
    
    //instance
    private static $_instance;

    public function __construct() {
        echo "__construct<br>";
    }
    
    /**
     *单例模式 
     */
    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        } 
        return self::$_instance;   
    }
    
    /**
     * 打开数据库连接，载入配置
     * 
     * @param type $config
     */
    public function open($config) {
        $this->config = $config;
        $this->connect();
    }

    /**
     * 真正的数据库连接
     */
    protected function connect() {
        //连接数据库
        $this->dbh = @mysql_connect($this->config['host'], $this->config['user'], $this->config['pass']);
//        $this->dbh = new mysqli($this->config['host'], $this->config['user'], $this->config['pass']);
        if ($this->dbh == false) {
            $this->halt();
            return false;
        }
        //选择数据库
        if (mysql_select_db($this->config['database'], $this->dbh) == false) {
            $this->halt();
        }
        //指定UTF8编码
        mysql_query("SET NAMES UTF8;", $this->dbh);
    }

    /**
     * 中断连接
     */
    protected function halt($message = null) {
        $this->errno = mysql_errno();
        $this->error = mysql_error();
        $errorTpl = "<b>MySQL错误</b>(%s)：%s，报错信息：%s";
        printf($errorTpl, mysql_errno(), mysql_error(), $message);
        exit();
    }

    /**
     * 执行查询
     */
    public function query($sql) {
        return $this->execute($sql);
    }

    /**
     * 执行SQL语句
     */
    public function execute($sql) {
        $this->lastQid = mysql_query($sql, $this->dbh);
        if ($this->lastQid == false) {
            $this->halt($sql);
        }
        return $this->lastQid;
    }

    /**
     * SELECT 操作
     * 
     * @param type $field
     * @param type $table
     * @param type $where
     * @param type $limit
     * @param type $order
     * @param type $group
     * @param type $key
     */
    public function select($field, $table, $where = '', $limit = '', $order = '', $group = '') {
        $_where = $where == '' ? '' : ' WHERE ' . $where;
        $_order = $order == '' ? '' : ' ORDER BY ' . $order;
        $_group = $group == '' ? '' : ' GROUP BY ' . $group;
        $_limit = $limit == '' ? '' : ' LIMIT ' . $limit;
        $sql = 'SELECT ' . $field . ' FROM ' . $table . $_where . $_group . $_order . $_limit;
        $this->execute($sql);
        return $this;
        /*
          //获取结果集
          $result = $this->fetchAll();
          //释放结果集
          $this->freeResult();
          return $result;
         * 
         */
    }

    /**
     * 获取所有结果集
     */
    public function fetchAll() {
        if ($this->lastQid == false) {
            return false;
        }
        $result = [];
        while ($row = mysql_fetch_assoc($this->lastQid)) {
            $result[] = $row;
//            var_dump($this->lastQid);
        }
        return $result;
    }

    /**
     * 获取一条结果集
     */
    public function fetchOne() {
        if ($this->lastQid == false) {
            return false;
        }
        $row = mysql_fetch_assoc($this->lastQid);
        return $row;
    }

    /**
     * 释放结果集
     */
    protected function freeResult() {
        if (is_resource($this->lastQid)) {
            mysql_free_result($this->lastQid);
            $this->lastQid = null;
        }
    }

    /**
     * 插入数据
     * 
     * @param array $data
     * @param string $table
     */
    public function insert($data, $table, $lastid = false) {
        if (empty($data) || empty($table)) {
            return false;
        }
        $_fields = array_keys($data);
        $_values = array_values($data);
//        $_values = array_map(array($this, "quoto"), $_values);
        array_walk($_values, array($this, "quoto"));
        var_dump($_values);
        $fields = implode(",", $_fields);
        $values = implode(",", $_values);
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
        $this->execute($sql);
        //如果要返回last_insert_id
        if ($lastid) {
            return $this->lastInsertId();
        } else {
            return $this->affectedRows();
        }
    }

    /**
     * 更新数据
     * 
     * @param type $data     要更新的数据内容
     * @param type $table
     * @param type $where    更新数据时的条件
     */
    public function update($data, $table, $where = 1) {
        if (empty($data) || empty($table)) {
            return false;
        }
        foreach ($data as $k => $v) {
            $_sdata[] = ("{$k} = '{$v}'");
        }
        $set = implode(",", $_sdata);
        $where = " WHERE " . $where;
        $sql = "UPDATE {$table} SET {$set} {$where}";
        $this->execute($sql);
        return $this->affectedRows();
    }

    /**
     * 删除数据
     * 
     * @param type $table
     * @param string $where
     */
    public function delete($table, $where = 1) {
        if (empty($table)) {
            return false;
        }
        $where = ' WHERE ' . $where;
        $sql = "DELETE FROM {$table} {$where}";
        $this->execute($sql);
        return $this->affectedRows();
    }

    /**
     * 加引号
     * 
     * @param type $value
     */
    protected function quoto(&$value) {
//        return "'" . $value . "'";
        $value = "'" . $value . "'";
    }

    /**
     * 最后插入的ID，跟主键有关系
     * 
     * @return type
     */
    protected function lastInsertId() {
        $last_insert_id = mysql_insert_id($this->dbh);
        return $last_insert_id;
    }

    protected function affectedRows() {
        $affect = mysql_affected_rows($this->dbh);
        return $affect;
    }

}
