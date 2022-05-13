<?php

class Database{

    //int的值为10 int（10）显示结果为0000000010  int（3）显示结果为010
    public static $type_int_10 = "INTEGER DEFAULT 0";//int 字段，10位显示宽度

    /**
     * 主题启动必须创建的字段，contents 表的views 字段、stars 字段，comment表的 stars 字段
     * @return void
     */
    public static function initField(){
        Database::createFiledInTable("stars",Database::$type_int_10,"comments");
    }

    /**
     * @param $filed
     * @param $filed_type mixed|null null则表示不进行查询字段是否存在，直接获取值
     * @param $table string
     * @param $args
     * @return mixed|null
     */
    public static function getDataByField($filed,$filed_type,$table,...$args){
        $db = Typecho_Db::get();
        try{
            if ($filed_type && !self::createFiledInTable($filed,$filed_type,$table)) {//没有数据，此时会创建该字段
                return null;
            } else {
                $row = $db->fetchRow($db->select($filed)->from('table.'.$table)->where(...$args));
                return $row[$filed];
            }
        }catch (Exception $exception){
//            var_dump($exception);
            return null;
        }

    }

    //创建一个字段
    public static function createFiledInTable($field,$filed_type,$table){
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        try {
            if (!array_key_exists($field, Utils::checkArray($db->fetchRow($db->select()->from('table.'.$table))))) {
                $db->query('ALTER TABLE `' . $prefix . $table.'` ADD `'.$field.'` '.$filed_type.';');
                return false;
            }
        }catch (Exception $exception){
            return false;//已经有该字段了，但是没有行数据，所以此时重复创建该字段会有问题
        }
        return true;
    }

    // 修改某条记录的某个字段
    public static function updateField($filed,$field_value,$table,...$args){
        $db = Typecho_Db::get();
        $db->query($db->update('table.'.$table)->rows(array($filed => $field_value))->where(...$args));
    }

    //插入一行数据
    public static function insertRow(){
        $db = Typecho_Db::get();
        $insert = $db->insert('table.relationships')->rows(array("cid"=>$insertId,"mid"=>$mid));
        return $db->query($insert);

    }


    // 创建一个表


    // 删除一个表


    // 查询表是否存在



}
