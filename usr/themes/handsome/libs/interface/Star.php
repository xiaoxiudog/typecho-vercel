<?php
/**
 * Created by PhpStorm.
 * User: hewro
 * Date: 2018/6/27
 * Time: 17:15
 * Description: 点赞请求
 */


class Star{
    public static function getInterface(){
        self::starTalk();
        self::startPost();
    }

    public static function postInterface(){

    }


    private static function starCommon($id_name, $table,$cookie_key){
        if (!empty($_GET[$id_name])){
            $id = $_GET[$id_name];
            $stars = Typecho_Cookie::get($cookie_key);
            if(empty($stars)){
                $stars = array();
            }else{
                $stars = explode(',', $stars);
            }
            if(!in_array($id,$stars)){//如果cookie不存在才会加1
                $star_num = Database::getDataByField("stars",null,$table,$id_name.' = ?', $id);
                if ($star_num !== null){
                    Database::updateField("stars",(int) $star_num + 1,$table,$id_name.' = ?', $id);
                    array_push($stars, $id);
                    $stars = implode(',', $stars);
                    Typecho_Cookie::set($cookie_key, $stars); //记录查看cookie
                    echo 1;//点赞成功
                }else{
                    echo -1;//数据库失败
                }
            }else{
                echo 2;//已经点赞过了
            }
        }else{
            echo -1;//信息缺失
        }
    }

    public static function startPost(){
        if (@$_GET['action'] == 'star_post') {
            self::starCommon("cid","contents","extend_post_stars");
            die();

        }
    }


    public static function starTalk(){
        if (@$_GET['action'] == 'star_talk'){
            self::starCommon("coid","comments","extend_say_stars");
            die();
        }
    }
}


?>
