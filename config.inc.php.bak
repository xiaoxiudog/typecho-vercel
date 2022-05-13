<?php

/** 开启https */
define('__TYPECHO_SECURE__',true);

// site root path
define('__TYPECHO_ROOT_DIR__', dirname(__FILE__));

// plugin directory (relative path)
define('__TYPECHO_PLUGIN_DIR__', '/usr/plugins');

// theme directory (relative path)
define('__TYPECHO_THEME_DIR__', '/usr/themes');

// admin directory (relative path)
define('__TYPECHO_ADMIN_DIR__', '/admin/');

/** 设置包含路径 */
@set_include_path(get_include_path() . PATH_SEPARATOR .
__TYPECHO_ROOT_DIR__ . '/var' . PATH_SEPARATOR .
__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__);

// register autoload
require_once __TYPECHO_ROOT_DIR__ . '/var/Typecho/Common.php';

// init
Typecho_Common::init();

// config db
$db = new \Typecho\Db('Pdo_Mysql', 'typecho_');
$db->addServer(array (
  'host' => '173.82.226.222',
  'port' => 3306,
  'user' => 'Pblood',
  'password' => 'fkdzsnxfe2xTfJzS',
  'charset' => 'utf8mb4',
  'database' => 'pblood',
  'engine' => 'InnoDB',
), \Typecho\Db::READ | \Typecho\Db::WRITE);
\Typecho\Db::set($db);
