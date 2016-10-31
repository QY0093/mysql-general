<?php
//先引入文件
include "MySQL.php";
//载入配置文件
$config = include 'dbconfig.php';


//测试数据，运用单例模式则只连接MySQL数据库一次，全局调用一个实例化对象。而用new时，则有几个实例化对象
//单例模式在一个类中无论写多少次实例化，最终智游一个有效
$MySQLObj = MySQL::getInstance();
//$MySQLObj = new MySQL();
$MySQLObj->open($config);
$MySQLObj = MySQL::getInstance();
$MySQLObj->open($config);
$MySQLObj = MySQL::getInstance();
$MySQLObj->open($config);

//$MySQLObj->open($config);


//var_dump($MySQLObj);
//$sql = "SELECT 1+1";
//$row = $MySQLObj->select("*", "worker", "id = 12");
$row1 = $MySQLObj->select("*", "worker", "id = 12")->fetchAll();
$row2 = $MySQLObj->select("*", "worker")->fetchOne();
var_dump($row1, $row2);


//插入
//$row = $MySQLObj->insert([
//    'name' => '张三'
//], "worker");
//var_dump($row);
 
 


/*
//更新
$row = $MySQLObj->update([
    'name' => '李四'
],'worker', "name = '张三'");
var_dump($row);
 * 
 */
 
/*
//删除
$row = $MySQLObj->delete("worker", "id > 30");
var_dump($row);
 * 
 */