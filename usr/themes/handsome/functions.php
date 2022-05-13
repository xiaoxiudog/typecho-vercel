<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

error_reporting(0);
ini_set('display_errors', 0);

//如果需要显示php错误打开这两行注释，问题修复后必须关闭！
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/**
 * 主题后台必须引入的组件
 */


require_once("libs/utils/Database.php");

require_once("libs/Options.php");
require_once("libs/CDN.php");
require_once("libs/Lang.php");
require_once("libs/I18n.php");
require_once("libs/Handsome.php");

require_once("libs/Request.php");
require_once("libs/HAdmin.php");

require_once("libs/component/UA.php");
require_once("libs/component/Device.php");


require_once("functions_mine.php");


/*表单组件*/
$prefix = Utils::getMixPrefix();
require_once("libs/admin/".$prefix."FormElements.php");
require_once('libs/admin/'.$prefix.'Checkbox.php');
require_once('libs/admin/'.$prefix.'Text.php');
require_once('libs/admin/'.$prefix.'Radio.php');
require_once('libs/admin/'.$prefix.'Select.php');
require_once('libs/admin/'.$prefix.'Textarea.php');



