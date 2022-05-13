<?php

/**
 * 与时光机相关的接口信息
 */


/**
 * @param $content
 * @param $rootUrl
 * @return string
 */
function typeLocationContent($content,$rootUrl){
    $locations = mb_split('#',$content);
    $label = $locations[2];
    $imageUrl = $locations[3];
    //这里的content是url地址
    $url = Utils::uploadPic($rootUrl,uniqid(),$imageUrl,"web",".jpg");
    $content = '📌'.$label.'<img src="'.$url.'"/>';
    return $content;
}

function typeImageContent($content,$rootUrl){
    $url = Utils::uploadPic($rootUrl,uniqid(),$content,"web",".jpg");
    $content = '<img src="'.$url.'"/>';
    return $content;
}
function typeLinkContent($content){
    $links = mb_split('#',$content);
    $title = $links[0];
    $description = $links[1];
    $url = $links[2];
    //对url进行转义
    $url = str_replace('','\/',$url);
    $content = '[post title="'.$title.'" intro="'.$description.'" url="'.$url.'" /]';
    return $content;
}

function typeTextContent($content,$flag = true){
    //检查content是否包含url，如果包含url，匹配是否可以匹配成音乐
    if ($flag){
        $content = $content."\n\n";
    }
    $content = preg_replace_callback("/(https?:\/\/[-A-Za-z0-9+&@#\/%?=~_|!:,.;]+[-A-Za-z0-9+&@#\/\%=~_|])/", function
    ($matches) {
        if ($matches[1] !== ""){
            $ret = Utils::parseMusicUrlText($matches[1]);
            if (!empty($ret)){
                return "\n".'[hplayer  media="'.$ret["server"].'" id="'.$ret["id"].'" type="'.$ret["type"].'" size="'
                    .$ret["size"].'" '.$ret["audoplayHtml"].' /]'."\n";
            }else{
                return $matches[1];
            }
        }else{
            return $matches[1];
        }
    }, $content);
    if (Utils::startWith($content,"#")){
        $content = "[secret]".mb_substr($content,1)."\n[/secret]";
    }
    return $content;
}

function parseMixContent($thisText,$options){
    $contentArray = json_decode($thisText,true);
    $contentArray = $contentArray["results"];
    $thisText = "";
    $isHaveImage = false;
    $imageContent = "[album]";
    foreach ($contentArray as $contentItem){
        if ($contentItem['type'] == "image"){
            $isHaveImage = true;
            $imageContent .= typeImageContent($contentItem['content'],$options->rootUrl);
        }elseif ($contentItem['type'] == "text"){
            $thisText .= typeTextContent($contentItem['content'],true);
        }elseif ($contentItem['type'] == "location"){
            $thisText .= typeLocationContent($contentItem['content'],$options->rootUrl);
        }else if ($contentItem['type'] == "link"){
            $thisText = typeLinkContent($contentItem['content']);
        }
    }
    if ($isHaveImage){
        $imageContent .= "[/album]";
        $thisText .= typeTextContent($imageContent,false);
    }
    return $thisText;

}

function parseMixPostContent($thisText,$options){
    $contentArray = json_decode($thisText,true);
    $contentArray = $contentArray["results"];
    $thisText = "";
    $isHaveImage = false;
    $imageContent = "[album]";
    foreach ($contentArray as $contentItem){
        if ($contentItem['type'] == "image"){
            $isHaveImage = true;
//            $imageContent .= typeImageContent($contentItem['content'],$options->rootUrl);
            $thisText .= typeImageContent($contentItem['content'],$options->rootUrl);
        }elseif ($contentItem['type'] == "text"){
            $thisText .= typeTextContent($contentItem['content'],true);
        }elseif ($contentItem['type'] == "location"){
            $thisText .= typeLocationContent($contentItem['content'],$options->rootUrl);
        }else if ($contentItem['type'] == "link"){
            $thisText = typeLinkContent($contentItem['content']);
        }
    }
    /*if ($isHaveImage){
        $imageContent .= "[/album]";
        $thisText .= typeTextContent($imageContent,false);
    }*/
    return $thisText;

}

class TimeMachine{

    public static function postInterface($isLogin = false){
        if(@$_POST['action'] == 'send_talk'){

            //从微信公众号发送说说说
            //获取必要的参数

            if (!empty($_POST['content']) && !empty($_POST['time_code']) && !empty($_POST['cid']) && !empty($_POST['token'])){
                $cid = $_POST['cid'];
                $thisText=$_POST['content']; //发送的内容
                $time_code= $_POST['time_code'];//用来检验是否是博客主人
                $token= $_POST['token'];//用来表示调用这个接口的来源，wexin表示微信公众号，crx表示浏览器扩展
                $msg_type = $_POST['msg_type'];
                $options = mget();
                //身份验证
                if ($time_code === md5($options->time_code) && trim($options->time_code)!==""){//验证成功
                    require_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/Handsome/cache/cache.php';
                    $cache = new CacheUtil();
                    if ($msg_type == "mixed_post"){//发送博文
                        $thisText = "<!--markdown-->".parseMixPostContent($thisText,$options);
                        $mid = $_POST["mid"];
                        //1.向数据库添加文章记录
                        $db = Typecho_Db::get();
                        //先找到作者信息
                        $getAdminSql = $db->select()->from('table.users')
                            ->limit(1);
                        $user = $db->fetchRow($getAdminSql);
                        $time =  date("Y 年 m 月 d 日 H 时 i 分");
                        $timeSlug = date('Y-n-j-H:i:s',time());
                        $insert = $db->insert('table.contents')
                            ->rows(array("title"=>$time,"slug"=>$timeSlug,"created"=>time(),"modified"=>time(),
                                "text"=>$thisText,"authorId"=>$user['uid'],"allowComment"=>'1'));
                        //将构建好的sql执行, 如果你的主键id是自增型的还会返回insert id，获取到插入文章的cid
                        $insertId = $db->query($insert);

                        //2. 绑定分类
                        $insert = $db->insert('table.relationships')->rows(array("cid"=>$insertId,"mid"=>$mid));
                        //将构建好的sql执行, 如果你的主键id是自增型的还会返回insert id，获取到插入文章的cid
                        $insertId = $db->query($insert);

                        //3.分类下的文章数目+1
                        $row = $db->fetchRow($db->select('count')->from('table.metas')->where('mid = ?',$mid));
                        $db->query($db->update('table.metas')->rows(array('count' => (int) $row['count'] + 1))->where('mid = ?',
                            $mid));
                        echo "1";
                    }else{//发送时光机

                        if ($msg_type == "image"){//上传图片
                            $thisText = typeImageContent($thisText,$options->rootUrl);
                        }else if ($msg_type == "location"){//地理位置
                            $thisText = typeLocationContent($thisText,$options->rootUrl);
                        }else if($msg_type == "mixed_talk"){//混合类型，content是json字符串，需要解析成数组
                            $thisText = parseMixContent($thisText,$options);
                        }else if ($msg_type == "text"){
                            $thisText = typeTextContent($thisText,false);
                        }else if ($msg_type == "link"){
                            $thisText = typeLinkContent($thisText);
                        }



                        //向数据库添加说说记录
                        $db = Typecho_Db::get();
                        //先找到作者信息
                        $getAdminSql = $db->select()->from('table.users')
                            ->limit(1);
                        $user = $db->fetchRow($getAdminSql);

                        $insert = $db->insert('table.comments')
                            ->rows(array("cid" => $cid,"created" => time(),"author" => $user['screenName'],"authorId" =>
                                $user['uid'],"ownerId" => $user['uid'],"text"=> $thisText,"url" => $user['url'],"mail" =>
                                $user['mail'],"agent"=>$token));
                        //将构建好的sql执行, 如果你的主键id是自增型的还会返回insert id
                        $insertId = $db->query($insert);
                        //修改评论数目+1
                        $row = $db->fetchRow($db->select('commentsNum')->from('table.contents')->where('cid = ?',$cid));
                        $db->query($db->update('table.contents')->rows(array('commentsNum' => (int) $row['commentsNum'] + 1))->where('cid = ?', $cid));

                        $cache->cacheWrite("comment",date("Y-m-d"),CacheUtil::$not_expired_time,"comment",true,true);
                        echo "1";//发送成功
                    }
                }else{
                    echo "-3";//身份验证失败
                }

            }else{
                echo "-2";//信息缺失
            }
            die();
        }
        elseif (@$_POST['action'] == 'send_post'){
            //检查参数
            if (!empty($_POST['content']) && !empty($_POST['time_code']) && !empty($_POST['cid']) && !empty($_POST['token'])){

            }

        } else if(@$_POST['action'] == 'upload_img'){
            $returnData = array();
            //支持上传base64数据和url格式两种，网络图片一律使用.jpg格式
            $options = mget();
            //鉴权：判断是否登录或者根据时光机id来判断
            $flag = false;
            if ($isLogin){
                $flag = true;
            }elseif ($_POST['time_code'] === md5($options->time_code) && trim($options->time_code)!==""){
                $flag = true;
            }else{
                $flag = false;
            }
            if ($flag){
                $data = $_POST['file'];
                $suffix = @$_POST["type"];//低版本插件没有该该选项
                if ($suffix == ""){
                    $suffix = ".jpg";
                }
                $prefix = substr($data,0,4);
                if ($prefix == "data"){//本地图片
                    $base64_string= explode(',', $data); //截取data:image/png;base64, 这个逗号后的字符
//                根据数据自动识别不需要传递这个参数了
                    $data= base64_decode($base64_string[1]);
                    $returnData['status'] = "1";
                    $returnData['data'] = Utils::uploadPic($options->rootUrl,uniqid(),$data,"local",$suffix);
                }else if ($prefix == "http"){//网络图片
                    $returnData['status'] = "1";
                    $returnData['data'] = Utils::uploadPic($options->rootUrl,uniqid(),$data,"web",".jpg");
                }else{
                    $returnData['status'] = "-1";//请求参数错误
                }
            }else{
                $returnData['status'] = "-3";//身份验证错误
            }
            //用json字符串格式返回请求信息
            echo json_encode($returnData);
            die();
        }

    }
    public static function getInterface(){

    }

}
