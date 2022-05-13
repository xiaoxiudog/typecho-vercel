<?php
class HandsomeCache extends HandsomeCacheBase
{
    public function __construct($option)
    {
        parent::__construct($option);
    }
    public function install()
    {
        $sql = '
DROP TABLE IF EXISTS `%dbname%`;
CREATE TABLE `%dbname%` (
  `key` char(32) NOT NULL,
  `data` longtext,
  `time` bigint(20) DEFAULT NULL,
  `type` char(10),
  PRIMARY KEY (`key`)
)  DEFAULT CHARSET=%charset%';
        $dbname = $this->db->getPrefix().$this->cache_db_name;
        $search = array('%dbname%', '%charset%');
        $replace = array($dbname, str_replace('UTF-8', 'utf8', Helper::options()->charset));

        $sql = str_replace($search, $replace, $sql);
        $sqls = explode(';', $sql);
        foreach ($sqls as $sql) {
            $this->db->query($sql);
        }
    }

    public function is_exist_table()
    {
        $this->db = Typecho_Db::get();
        $dbname = $this->db->getPrefix() . $this->cache_db_name;
        $sql = "SHOW TABLES LIKE '%" . $dbname . "%'";
        if (count($this->db->fetchAll($sql)) == 0) {
            return false;
        }else{
            return true;
        }
    }
}
