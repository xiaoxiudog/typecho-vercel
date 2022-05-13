<?php

/**
 * get 请求接口
 */

require_once("Time.php");
require_once("Star.php");

function md5_($data){
    return md5("handsome!@#$%^&*()-=+@#$%$".$data."handsome!@#$%^&*()-=+@#$%$");
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && @$_GET['action'] != "open_world"){
    $options = mget();
    //如果路径包含后台管理路径，则不显示Lock.php
    $password = Typecho_Cookie::get('open_new_world');
    $cookie = false;//true为可以直接进入
    if (!empty($password) && $password == md5_($options->open_new_world)){
        $cookie = true;
    }
    if (strpos($_SERVER["SCRIPT_NAME"], __TYPECHO_ADMIN_DIR__)===false){
        if (!$cookie && trim($options->open_new_world) != ""){//没有cookie认证且访问的不是管理员界面
            $data = array();
            $data['title'] = $options->title;
            $data['md5'] = md5_($options->open_new_world);
            $data['type'] = "index";
            $data['unique_id'] = "-1"; //针对全站加密，该字段无需填写
            $_GET['data']=$data;
            require_once(dirname(__DIR__).'/Lock.php');
            die();
        }else{
            //检查是否有mbstring扩展
            if (!function_exists("mb_split") || !function_exists("file_get_contents")){
                throw new Typecho_Exception(CDN_Config::not_support);
            }
        }
    }
}


class Ajax{
    public static function request(){
        $options = mget();
        if (strtoupper($options->language) != "AUTO") {
            I18n::setLang($options->language);
        }
        TimeMachine::getInterface();
        Star::getInterface();
        themeBackUpGet();
        staticInfoGet();
        searchGet();
        lockOpenGet();
        avatarGet();
//        searchCacheGet();
    }

    public static function post(){
        TimeMachine::postInterface();
        Star::postInterface();
    }
}



function themeBackUpGet(){
    if (@$_GET['action'] == 'back_up' || @$_GET['action'] == 'un_back_up' || @$_GET['action'] == 'recover_back_up'){//备份管理

        $action = @$_GET['action'];
        $code = @$_GET["code"];
        $options = mget();

        if ($code == md5($options->time_code) && trim($options->time_code)!==""){
            $db = Typecho_Db::get();

            $themeName = $db->fetchRow($db->select()->from ('table.options')->where ('name = ?', 'theme'));
            $handsomeThemeName = "theme:".$themeName['value'];
            $handsomeThemeBackupName = "theme:HandsomePro-X-Backup";


            if ($action == "back_up"){//备份数据
                $handsomeInfo=$db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeName));
                $handsomeValue = $handsomeInfo['value'];//最新的主题数据

                if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))) {//如果有了，直接更新
                    $update = $db->update('table.options')->rows(array('value' => $handsomeValue))->where('name = ?', $handsomeThemeBackupName);
                    $updateRows = $db->query($update);
                    echo 1;
                }else{//没有的话，直接插入数据
                    $insert = $db->insert('table.options')
                        ->rows(array('name' => $handsomeThemeBackupName,'user' => '0','value' => $handsomeValue));
                    $db->query($insert);
                    echo 2;
                }
            }else if ($action == "un_back_up"){//删除备份
                $db = Typecho_Db::get();
                if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))){
                    $delete = $db->delete('table.options')->where ('name = ?', $handsomeThemeBackupName);
                    $deletedRows = $db->query($delete);
                    echo 1;
                }else{
                    echo -1;//备份不存在
                }
            }else if ($action == "recover_back_up"){//恢复备份
                $db = Typecho_Db::get();
                if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))){
                    $themeInfo = $db->fetchRow($db->select()->from ('table.options')->where ('name = ?',
                        $handsomeThemeBackupName));
                    $themeValue = $themeInfo['value'];
                    $update = $db->update('table.options')->rows(array('value'=>$themeValue))->where('name = ?', $handsomeThemeName);
                    $updateRows= $db->query($update);
                    echo 1;
                }else{
                    echo -1;//没有备份数据
                }
            }
        }else{
            echo -2;//鉴权失败
        }
        die();//只显示ajax请求内容，禁止显示博客内容
    }
}


function staticInfoGet(){
    if (@$_GET['action'] == "get_statistic"){
        header('Content-type:text/json');     //这句是重点，它告诉接收数据的对象此页面输出的是json数据；

        Typecho_Widget::widget('Widget_Metas_Category_List')->to($categorys);
        Typecho_Widget::widget('Widget_Metas_Tag_Cloud','ignoreZeroCount=1&limit=30')->to($tags);

        $object = [];

        $windowSize = @$_GET['size'];
        $monthNum = 10;
        if ($windowSize !== ""){
            if ($windowSize> 1600){
                $monthNum = 12;
            } else if ($windowSize > 1200){
                $monthNum = 10;
            }else if ($windowSize>992){
                $monthNum = 8;
            }else if ($windowSize > 600){
                $monthNum = 10;
            }
            else{
                $monthNum = 5;
            }
        }

        $post_calendar = Content::getStatisticContent("post-calendar",null,$monthNum);
        $posts_chart = Content::getStatisticContent("posts-chart",null);
        $category_radar = Content::getStatisticContent("category-radar",$categorys);
        $categories_chart = Content::getStatisticContent("categories-chart",$categorys);
        $tags_chart = Content::getStatisticContent("tags-chart",$tags);

        $object["post_calendar"] = $post_calendar;
        $object["post_chart"] = $posts_chart;
        $object["category_radar"] = $category_radar;
        $object["categories_chart"] = $categories_chart;
        $object["tags_chart"] = $tags_chart;

        echo json_encode($object);

        die();
    }
}

function searchGet(){
    if (@$_GET['action'] == "ajax_search"){
        header('Content-type:text/json');     //这句是重点，它告诉接收数据的对象此页面输出的是json数据；
        $thisText = @$_GET['content'];
//        $OnlyTitle = @$_GET['onlytitle'];//只查询标题字段
        $object = [];
        $html = "";

        if (trim($thisText) !== ""){
            $searchResultArray = Utils::searchGetResult($thisText,Typecho_Widget::widget('Widget_User')->hasLogin());//搜索结果

            if (count($searchResultArray) ===0){
                $html = "<li><a href=\"#\">"._mt("无相关搜索结果")."🔍</a></li>";
            }else{
                foreach ($searchResultArray as $item){
                    $html .= "<li><a href=\"".$item["path"]."\">".$item["title"]."<p class=\"text-muted\">"
                        .$item["content"]."</p></a></li>";
                }
            }
        }


        $object['results'] = $html;
        echo json_encode($object);

        die();
    }
}


function lockOpenGet(){
    if(@$_GET['action'] == 'open_world'){
        if (!empty($_GET['password'])){
            $password = $_GET['password'];
            $md5 = $_GET['md5'];
            $type = $_GET['type'];//type:index 表示首页 category 表示分类加锁，single 表示单个页面
            $returnData = array();
            if (Utils::encodeData($password) == $md5){
                $returnData['status'] = "1";
//                echo 1;//密码正确
                if ($type == "index"){
                    Typecho_Cookie::set('open_new_world', Utils::encodeData($password)); //保存密码的cookie，以便后面可以直接访问
                }elseif($type == "category") {
                    $category = $_GET['unique_id'];//需要加密的分类缩略名
                    Typecho_Cookie::set('category_'.$category, Utils::encodeData($password)); //保存密码的cookie，以便后面可以直接访问
                }elseif ($type == "single"){
                    $id = $_GET['unique_id'];//需要加密的分类缩略名
                    Typecho_Cookie::set('single_'.$id, Utils::encodeData($password)); //保存密码的cookie，以便后面可以直接访问
                }
            }else{
                $returnData['status'] = "-1";
//                echo -1;//密码错误
            }
        }else{
            $returnData['status'] = "-2";
//            echo -2;//信息不完成
        }
        echo json_encode($returnData);

        die();
    }
}

function avatarGet(){
    if(@$_GET['action'] == 'ajax_avatar_get') {
        $email = strtolower( $_GET['email']);
        echo Utils::getAvator($email,65);
        die();
    }
}

function searchCacheGet(){
    //todo 暂时下掉这个接口
    if (@$_GET['action'] == 'get_search_cache'){
        require_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/Handsome/cache/cache.php';
        $cache = new CacheUtil();
        $file = $cache->cacheRead("search");
        if ($file !== false){
            echo $file;
        }else{
            echo "{}";
        }
        die();
    }
}


