<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Utils.php
 * Author     : Hran,hewro
 * Date       : 2017/05/29
 * Version    :
 * Description: handsome主题的一些工具方法
 */

require_once("component/ImgCompress.php");


class Utils
{

    public static function getCateDesc($desc)
    {
        $content = json_decode($desc, true);

        if (is_array($content)) {
            return (@$content["desc"] == "") ? "" : $content["desc"];
        } else {
            return $desc;
        }
    }

    public static function initExpertSetting()
    {
        $options = mget();
        if (!is_array($options->expert)) {
            $expert = json_decode($options->expert, true);
            if ($expert == null) {
                $expert = array();
            }
            $options->expert = $expert;
            if ($options->expert == "{}") {
                $options->expert = [];
            }
        }
        $options->cdnAdds = explode("\n", $options->cdn_add);
    }


    public static function initPluginOptions()
    {
        $options = mget();
        if (Utils::isPluginAvailable("Handsome_Plugin", "Handsome")) {
            $options->pluginOptions = Helper::options()->plugin('Handsome');
            $options->pluginReady = true;
        }else{
            $options->pluginReady = false;
        }
    }

    public static function getPluginOptionValue($key, $default = false)
    {
        $options = mget();
        if ($options->pluginOptions == null) {
            return $default;
        } else {
            return $options->pluginOptions->{$key};
        }
    }

    public static function getExpertValue($key, $default = false)
    {
        $options = mget();

        if ($options->expert == null || !@array_key_exists($key, $options->expert)) {
            return $default;
        } else {
            return $options->expert[$key];
        }
    }

    public static function getCDNAdd($index = 0)
    {
        return explode("|", Utils::getCdnUrl($index));

    }

    public static function getCDNUrl($index = 0)
    {
        $options = mget();
        if (CDN_Config::SPECIAL_MODE != 1) {
            $index = 0;
        }
        if (!is_array($options->cdnAdds)) {
            return "";
        } else {
            if ($index >= count($options->cdnAdds)) {
                $index = 0;
            }
            return $options->cdnAdds[$index];
        }
    }

    public static function json_decode($str, $flag=false)
    {
        $ret = json_decode($str, $flag);
        if ($ret == null) {
            return [];
        } else {
            return $ret;
        }
    }


    public static function initGlobalDefine($fromPlugin = false)
    {
        Utils::initExpertSetting();
        if (!$fromPlugin) {
            Utils::initPluginOptions();
        }

        $options = mget();
        if (!defined('THEME_URL')) {//主题目录的绝对地址
            define("THEME_URL", rtrim(preg_replace('/^' . preg_quote($options->siteUrl, '/') . '/', $options->rootUrl . '/', $options->themeUrl, 1), '/') . '/');
        }

//    约定，定义所有的url末尾都要有斜杆
        if (!defined("BLOG_URL")) {
            define("BLOG_URL", $options->rootUrl . "/");
        }

        if (!defined("THEME_FILE")) {
            define("THEME_FILE", CDN_Config::returnThemePath());
        }
        if ($options->rewrite == 1) {//定义博客php的地址，判断是否开启了伪静态
            if (!defined('BLOG_URL_PHP'))
                define("BLOG_URL_PHP", BLOG_URL);
        } else {
            if (!defined('BLOG_URL_PHP'))
                define("BLOG_URL_PHP", BLOG_URL . 'index.php/');
        }

        if (strlen(trim($options->LocalResourceSrc)) > 0) {//主题静态资源的绝对地址
            @define('STATIC_PATH', $options->LocalResourceSrc);
        } else {
            @define('STATIC_PATH', THEME_URL . 'assets/');
        }

        @define("PJAX_ENABLED", in_array('isPjax', Utils::checkArray($options->featuresetup)));
        @define("PJAX_COMMENT_ENABLED", in_array('ajaxComment', Utils::checkArray($options->featuresetup)));
        @define('IS_TOC', in_array('tocThree', Utils::checkArray($options->featuresetup)));

        if ($options->commentChoice == '0') {
            @define("COMMENT_SYSTEM", CDN_Config::COMMENT_SYSTEM_ROOT);
        } else if ($options->commentChoice == '1') {
            @define("COMMENT_SYSTEM", CDN_Config::COMMENT_SYSTEM_CHANGYAN);
        } else if ($options->commentChoice == '2') {
            @define("COMMENT_SYSTEM", CDN_Config::COMMENT_SYSTEM_OTHERS);
        } else if ($options->commentChoice == '3') {
            @define("COMMENT_SYSTEM", CDN_Config::COMMENT_SYSTEM_NONE);
        } else {//空值的时候默认使用原生评论
            @define("COMMENT_SYSTEM", CDN_Config::COMMENT_SYSTEM_ROOT);
        }


        $sticky = Utils::getPluginOptionValue("sticky_cids", "");
        if (trim($sticky) != "" && $sticky !== null) {
            $stickyNum = count(explode(',', strtr($sticky, ' ', ',')));
        } else {
            $stickyNum = 0;
        }
        @define('INDEX_IMAGE_ARRAY', serialize(Utils::getImageNumRandomArray($options->pageSize + $stickyNum, Utils::getSj1ImageNum())));
        //右侧边栏图片
        @define('SIDEBAR_IMAGE_ARRAY', serialize(Utils::getImageNumRandomArray(5, Utils::getSj2ImageNum())));
        Utils::initCDN();

    }

    public static $PUBLIC_CDN_ARRAY = null;

    public static function initCDN()
    {

        if (!@defined("PUBLIC_CDN")) {
            $options = mget();
            switch ($options->publicCDNSelcet) {
                case 0:
                    @define('PUBLIC_CDN', CDN_Config::BOOT_CDN);
                    @define('PUBLIC_CDN_PREFIX', "");
                    break;
                case 1:
                    @define('PUBLIC_CDN', CDN_Config::BAIDU_CDN);
                    @define('PUBLIC_CDN_PREFIX', "");
                    break;
                case 2:
                    @define('PUBLIC_CDN', CDN_Config::SINA_CDN);
                    @define('PUBLIC_CDN_PREFIX', "");
                    break;
                case 3:
                    @define('PUBLIC_CDN', CDN_Config::QINIU_CDN);
                    @define('PUBLIC_CDN_PREFIX', "");
                    break;
                case 4:
                    @define('PUBLIC_CDN', CDN_Config::JSDELIVR_CDN);
                    @define('PUBLIC_CDN_PREFIX', "");
                    break;
                case 5:
                    @define('PUBLIC_CDN', CDN_Config::CAT_CDN);
                    @define('PUBLIC_CDN_PREFIX', "");
                    break;
                case 6://本地
                    @define('PUBLIC_CDN', CDN_Config::LOCAL_CDN);
                    //判断是否使用了加速功能
                    if (strlen(trim($options->LocalResourceSrc)) > 0) {//主题静态资源的绝对地址
                        @define('PUBLIC_CDN_PREFIX', $options->LocalResourceSrc . "libs/");
                    } else {
                        @define('PUBLIC_CDN_PREFIX', THEME_URL . "assets/libs/");
                    }
                    break;

                case 7:
                    @define('PUBLIC_CDN', CDN_Config::BY_CDN);
                    @define('PUBLIC_CDN_PREFIX', "");
                    break;
                default:
                    @define('PUBLIC_CDN', CDN_Config::LOCAL_CDN);
                    @define('PUBLIC_CDN_PREFIX', THEME_URL . "assets/libs/");
                    break;
            }

            Utils::$PUBLIC_CDN_ARRAY = json_decode(PUBLIC_CDN, true);
        }


    }

    // 对于这种体积较大且非必须类型公共库，判断本地是否存在资源，如果存在则直接走本地资源
    public static function getLocalCDN($localPath, $remote, $targetPath = "", $type = "js")
    {
        if (is_dir(dirname(dirname(__FILE__)) . "/assets/libs/" . $localPath)) {
            // 本地化也可能走自己的cdn
            if ($targetPath == "") {
                return STATIC_PATH . "libs/" . $localPath;
            } else {
                return STATIC_PATH . "libs/" . $targetPath;
            }
        } else {
            return Utils::$PUBLIC_CDN_ARRAY[$type][$remote];
        }

    }


    public static function getMathjaxOption($postOption = "")
    {
        $options = mget();

        if ($postOption == null || $postOption == "" || $postOption == "auto") {
            $ret = @in_array('mathJax', Utils::checkArray($options->featuresetup));
        } else {
            $ret = ($postOption === "true");
        }

        if ($ret) {
            return "1";
        } else {
            return "";
        }
    }

    public static function str_equal($str1, $str2)
    {
        return $str1 == $str2;
    }

    public static function formatDate($obj, $time, $format)
    {
//        var_dump($time);
//        var_dump($format);
        $options = mget();
        $time = $time + ($options->timezone - idate("Z"));
        if (strtoupper($format) == 'NATURAL') {
            return self::naturalDate($time, "all");
        } else {
            //return self::naturalDate($time);//强制开启友好化格式化时间
            return date($format, $time);
        }
    }

    public static function unicodeDecode($unicode_str)
    {
        return base64_decode($unicode_str);
    }


    public static function startWith($str, $pattern)
    {
        return mb_strpos($str, $pattern) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return strrpos($haystack, $needle) === (strlen($haystack) - strlen($needle));
    }

    public static function naturalDate($from, $level)
    {
        $now = time();
        $between = time() - $from;
        if ($level != "short") {
            if ($between > 31536000) {
                return date(I18n::dateFormat(), $from);
            } else if ($between > 0 && $between < 172800                                // 如果是昨天
                && (date('z', $from) + 1 == date('z', $now)                             // 在同一年的情况
                    || date('z', $from) + 1 == date('L') + 365 + date('z', $now))) {    // 跨年的情况
                return _mt('昨天 %s', date('H:i', $from));
            }
        }
        $f = array(
            '31536000' => '%d 年前',
            '2592000' => '%d 个月前',
            '604800' => '%d 星期前',
            '86400' => '%d 天前',
            '3600' => '%d 小时前',
            '60' => '%d 分钟前',
            '1' => '%d 秒前',
        );
        if ($between == 0) {
            return _mt("刚刚");
        }
        foreach ($f as $k => $v) {
            if (0 != $c = floor($between / (int)$k)) {
                if ($c == 1) {//一倍整除
                    return _mt(sprintf($v, $c));
                }
                return _mt($v, $c);//多倍整除
            }
        }
        return "";
    }

    public static function avatarHtml($obj, $time = false)
    {
        $email = $obj->mail;
        $avatorSrc = Utils::getAvator($email, 65);
        if ($obj->parent && $time) {
            return '<img nogallery src="' . $avatorSrc . '" class="img-40px photo img-square normal-shadow" data-placement="bottom" data-toggle="tooltip" title="' . $obj->author . '">';
        } else {
            return '<img nogallery src="' . $avatorSrc . '" class="img-40px photo img-square normal-shadow">';
        }


    }

    public static function returnIf($if, $else)
    {
        return ($if) ? $if : $else;
    }

    public static function getAvator($email, $size)
    {
        $options = mget();
        $cdnUrl = $options->CDNURL;
        if (@ in_array('emailToQQ', Utils::checkArray($options->featuresetup))) {
            $str = explode('@', $email);
            if (@$str[1] == 'qq.com' && @ctype_digit($str[0]) && @strlen($str[0]) >= 5
                && @strlen($str[0]) <= 11) {
                $avatorSrc = 'https://q2.qlogo.cn/g?b=qq&nk=' . $str[0] . '&s=100';
            } else {
                $avatorSrc = Utils::getGravator($email, $cdnUrl, $size);
            }
        } else {
            $avatorSrc = Utils::getGravator($email, $cdnUrl, $size);
        }
        return $avatorSrc;
    }

    public static function getGravator($email, $host, $size)
    {
        $options = mget();
        $default = '';
        if (strlen($options->defaultAvator) > 0) {
            $default = $options->defaultAvator;
        }
        $url = '/';//自定义头像目录,一般保持默认即可
        //$size = '40';//自定义头像大小
        $rating = Helper::options()->commentsAvatarRating;
        $hash = md5(strtolower($email));
        $avatar = $host . $url . $hash . '?s=' . $size . '&r=' . $rating . '&d=' . $default;
        return $avatar;
    }

    public static function sticky($archive)
    {
        $db = Typecho_Db::get();
        $paded = $archive->request->get('page', 1);
        $sticky_post = $db->fetchRow($archive->select()->where('cid = ?', 10));
        $archive->push($sticky_post);
        //$select->where('table.contents.cid != ?', 10);

    }

    public static function loginAction($archive)
    {
        $requestURL = $archive->request->getRequestUrl();

        $requestURL = str_replace('&_pjax=%23content', '', $requestURL);
        $requestURL = str_replace('?_pjax=%23content', '', $requestURL);
        $requestURL = str_replace('_pjax=%23content', '', $requestURL);

        return Typecho_Widget::widget('Widget_Security')->getTokenUrl($archive->rootUrl . '/index.php/action/login');
    }

    /**
     * 从分享文字中解析出链接
     * @param $url
     * @return array
     */
    public static function parseMusicUrlText($url)
    {
        {
//        var_dump($url);
            $url = trim($url);
            if (empty($url)) {
                return [];
            }
            $server = 'netease';
            $id = '';
            $type = '';
            if (strpos($url, '163.com') !== false) {
                $server = 'netease';
                if (preg_match('/playlist\?id=(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'playlist');
                } elseif (preg_match('/toplist\?id=(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'playlist');
                } elseif (preg_match('/album\?id=(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'album');
                } elseif (preg_match('/song\?id=(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'song');
                } elseif (preg_match('/artist\?id=(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'artist');
                }
            } elseif (strpos($url, 'qq.com') !== false) {
                $server = 'tencent';
                if (preg_match('/playsquare\/([^\.]*)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'playlist');
                } elseif (preg_match('/playlist\/([^\.]*)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'playlist');
                } elseif (preg_match('/album\/([^\.]*)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'album');
                } elseif (preg_match('/song\/([^\.]*)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'song');
                } elseif (preg_match('/singer\/([^\.]*)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'artist');
                }
            } elseif (strpos($url, 'xiami.com') !== false) {
                $server = 'xiami';
                if (preg_match('/collect\/(\w+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'playlist');
                } elseif (preg_match('/album\/(\w+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'album');
                } elseif (preg_match('/[\/.]\w+\/[songdem]+\/(\w+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'song');
                } elseif (preg_match('/artist\/(\w+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'artist');
                }
                if (!preg_match('/^\d*$/i', $id, $t)) {
                    $data = self::curl($url);
                    preg_match('/' . $type . '\/(\d+)/i', $data, $id);
                    $id = $id[1];
                }
            } elseif (strpos($url, 'kugou.com') !== false) {
                $server = 'kugou';
                if (preg_match('/special\/single\/(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'playlist');
                } elseif (preg_match('/#hash\=(\w+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'song');
                } elseif (preg_match('/album\/[single\/]*(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'album');
                } elseif (preg_match('/singer\/[home\/]*(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'artist');
                }
            } elseif (strpos($url, 'baidu.com') !== false) {
                $server = 'baidu';
                if (preg_match('/songlist\/(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'playlist');
                } elseif (preg_match('/album\/(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'album');
                } elseif (preg_match('/song\/(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'song');
                } elseif (preg_match('/artist\/(\d+)/i', $url, $id)) {
                    list($id, $type) = array($id[1], 'artist');
                }
            } else {
                return [];
            }
            if (is_array($id)) {
                $id = '';
            }

            $result = array(
                'server' => $server,
                'id' => $id,
                'type' => $type
            );
            return $result;
        }
    }


    public static function getImageNumRandomArray($NeedSize, $imageNum)
    {
        $indexNumberArray = array();
        if ($NeedSize > $imageNum) {
            //这种情况下图片重复是不可避免的，原因是缩略图的数目不够
            /*for($i = 0;$i<$options->pageSize - $options->RandomPicAmnt ;$i++){
                $indexNumberArray[] = random_int(1, $options->RandomPicAmnt);
            }*/
            while (count($indexNumberArray) < $NeedSize) {
                $number = rand(1, $imageNum);
                $indexNumberArray[] = $number;
            }
        } else {
            while (count($indexNumberArray) < $NeedSize) {
                $number = rand(1, $imageNum);
                $flag = false;//当前生成的数字是否已经存在了
                foreach ($indexNumberArray as $value) {
                    if ($value == $number) {
                        $flag = true;
                        break;
                    }
                }
                if (!$flag) {
                    $indexNumberArray[] = $number;
                }
            }
        }
        //print_r($indexNumberArray);
        return $indexNumberArray;
    }

    /**
     * 16进制颜色转rgb颜色
     * @param $hexColor
     * @return string
     */
    public static function hex2rgb($hexColor)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {

            $rgb = hexdec(substr($color, 0, 2)) . "," . hexdec(substr($color, 2, 2)) . "," . hexdec(substr($color, 4, 2));
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = hexdec($r) . "," . hexdec($g) . "," . hexdec($b);
        }
        return $rgb;
    }


    /**
     * 替换默认的preg_replace_callback函数
     * @param $pattern
     * @param $callback
     * @param $subject
     * @return string
     */
    public static function handle_preg_replace_callback($pattern, $callback, $subject)
    {
        return self::handleHtml($subject, function ($content) use ($callback, $pattern) {
            return preg_replace_callback($pattern, $callback, $content);
        });
    }


    public static function handle_preg_replace($pattern, $replacement, $subject)
    {
        return self::handleHtml($subject, function ($content) use ($replacement, $pattern) {
            return preg_replace($pattern, $replacement, $content);
        });
    }

    /**
     * 处理 HTML 文本，确保不会解析代码块中的内容
     * @param $content
     * @param callable $callback
     * @return string
     */
    public static function handleHtml($content, $callback)
    {
        $replaceStartIndex = array();
        $replaceEndIndex = array();
        $currentReplaceId = 0;
        $replaceIndex = 0;
        $searchIndex = 0;
        $searchCloseTag = false;
        $contentLength = strlen($content);
        while (true) {
            if ($searchCloseTag) {
                $tagName = substr($content, $searchIndex, 4);
                if ($tagName == "<cod") {
                    $searchIndex = strpos($content, '</code>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 7;
                } elseif ($tagName == "<pre") {
                    $searchIndex = strpos($content, '</pre>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 6;
                } elseif ($tagName == "<kbd") {
                    $searchIndex = strpos($content, '</kbd>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 6;
                } elseif ($tagName == "<scr") {
                    $searchIndex = strpos($content, '</script>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 9;
                } elseif ($tagName == "<sty") {
                    $searchIndex = strpos($content, '</style>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 8;
                } else {
                    break;
                }


                if (!$searchIndex) {
                    break;
                }
                $replaceIndex = $searchIndex;
                $searchCloseTag = false;
                continue;
            } else {
                $searchCodeIndex = strpos($content, '<code', $searchIndex);
                $searchPreIndex = strpos($content, '<pre', $searchIndex);
                $searchKbdIndex = strpos($content, '<kbd', $searchIndex);
                $searchScriptIndex = strpos($content, '<script', $searchIndex);
                $searchStyleIndex = strpos($content, '<style', $searchIndex);
                if (!$searchCodeIndex) {
                    $searchCodeIndex = $contentLength;
                }
                if (!$searchPreIndex) {
                    $searchPreIndex = $contentLength;
                }
                if (!$searchKbdIndex) {
                    $searchKbdIndex = $contentLength;
                }
                if (!$searchScriptIndex) {
                    $searchScriptIndex = $contentLength;
                }
                if (!$searchStyleIndex) {
                    $searchStyleIndex = $contentLength;
                }
                $searchIndex = min($searchCodeIndex, $searchPreIndex, $searchKbdIndex, $searchScriptIndex, $searchStyleIndex);
                $searchCloseTag = true;
            }
            $replaceStartIndex[$currentReplaceId] = $replaceIndex;
            $replaceEndIndex[$currentReplaceId] = $searchIndex;
            $currentReplaceId++;
            $replaceIndex = $searchIndex;
        }

        $output = "";
        $output .= substr($content, 0, $replaceStartIndex[0]);
        for ($i = 0; $i < count($replaceStartIndex); $i++) {
            $part = substr($content, $replaceStartIndex[$i], $replaceEndIndex[$i] - $replaceStartIndex[$i]);
            if (is_array($callback)) {
                $className = $callback[0];
                $method = $callback[1];
                $renderedPart = call_user_func($className . '::' . $method, $part);
            } else {
                $renderedPart = $callback($part);
            }
            $output .= $renderedPart;
            if ($i < count($replaceStartIndex) - 1) {
                $output .= substr($content, $replaceEndIndex[$i], $replaceStartIndex[$i + 1] - $replaceEndIndex[$i]);
            }
        }
        $output .= substr($content, $replaceEndIndex[count($replaceStartIndex) - 1]);
        return $output;
    }


    public static function useSecondCDNUrl($imageSrc)
    {
        foreach (CDN_Config::SPECIAL_MODE_NUM as $item) {
            if (strpos($imageSrc, $item) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function returnImageSrcWithSuffix($imageSrc = null, $cdnType = null, $width = 0, $height = 0)
    {
        $isLocal = true;
        $options = mget();
        if (Utils::getCdnUrl(0) == "") {
            return $imageSrc;
        }
        if ($imageSrc != null && (strpos($imageSrc, $options->rootUrl) === false && strpos($imageSrc, STATIC_PATH) === false)) {//不是本地服务器图片
            $isLocal = false;
//            echo $imageSrc."|".$options->rootUrl;
        } else {//替换图片的域名地址为云存储空间地址
            //如果时间大于了2021,使用第二个参数
            if (Utils::useSecondCDNUrl($imageSrc)) {
                $cdnArray = explode("|", Utils::getCdnUrl(1));
            } else {
                $cdnArray = explode("|", Utils::getCdnUrl(0));
            }
//            echo $options->rootUrl . "|" .trim($cdnArray[0])."|".$imageSrc;
            $imageSrc = str_ireplace($options->rootUrl, trim($cdnArray[0]), $imageSrc);
            $cdnType = trim($cdnArray[1]);
        }
        return $imageSrc . self::getImageAddOn($options, $isLocal, $cdnType, $width, $height);
    }

    /**
     * 云存储选项
     * @param array $options 后台选项设置
     * @param bool $isLocal 是否是本地服务器图片
     * @param String $cdnType 云服务商类型
     * @param int $width 目标图片的宽度
     * @param int $height 目标图片的高度
     * @param string $location 是文章的图片post还是别的，如首页头图index
     * @return string
     */
    public static function getImageAddOn($options, $isLocal = false, $cdnType = null, $width = 0, $height = 0, $location
    = "index")
    {
        $addOn = "";//图片后缀
        if (!$isLocal) {//不是本地服务器图片
            return $addOn;
        }
        if (Utils::getCdnUrl(0) != "") {//开启了镜像存储的功能

            if ($cdnType == null) {//如果参数中没有cdnType，这里会进行获取cdn类型
                $cdnArray = explode("|", Utils::getCdnUrl(0));
                $cdnType = trim($cdnArray[1]);
            }

            if (@in_array('0', Utils::checkArray($options->cloudOptions))) {//启用了图片处理
                if ($cdnType == "ALIOSS") {
                    $addOn .= "?";//分隔符
                } else if ($cdnType == "UPYUN") {//阿里云和又拍云
                    $addOn .= "!";//分隔符
                } else if ($cdnType == "QINIU") {//七牛云
                    $addOn .= "?";//分隔符
                } else if ($cdnType == "QCLOUD") {
                    $addOn .= "?imageMogr2";
                }
                if ($location == "post") {//为文章中的图片增加自定义后缀
                    $addOn .= trim($options->imagePostSuffix);
                }
                if (!($width == 0 && $height == 0)) {
                    if ($height == 0) {//根据宽度尺寸进行缩放
                        if ($cdnType == "UPYUN") {
                            $addOn .= "/fw/$width";
                        } else if ($cdnType == "ALIOSS") {//阿里云
                            $addOn .= "x-oss-process=image/resize,w_$width";
                        } else if ($cdnType == "QINIU") {//七牛云
                            $addOn .= "/imageView2/2/w/$width?imageslim";
                        } else if ($cdnType == "QCLOUD") {//腾讯云
                            $addOn .= "/scrop/" . $width . "x";
                        }
                    } else if ($width === 0) {//根据高度尺寸进行缩放
                        if ($cdnType == "UPYUN") {
                            $addOn .= "/fh/$height";
                        } else if ($cdnType == "ALIOSS") {
                            $addOn .= "x-oss-process=image/resize,h_$height";
                        } else if ($cdnType == "QINIU") {//七牛云
                            $addOn .= "/imageView2/2/h/$height";
                        } else if ($cdnType == "QCLOUD") {//腾讯云
                            $addOn .= "/scrop/x" . $height;
                        }
                    } else {//按照固定的宽高进行缩放
                        if ($cdnType == "UPYUN") {
                            $addOn .= "/fwfh/" . $width . "x" . $height;
                        } else if ($cdnType == "ALIOSS") {
                            $addOn .= "x-oss-process=image/resize,m_lfit,h_" . $height . ",w_" . $width;
                        } else if ($cdnType == "QINIU") {//七牛云
                            $addOn .= "/imageView2/2/w/" . $width . "/h/" . $height;
                        } else if ($cdnType == "QCLOUD") {//腾讯云
                            $addOn .= "/scrop/" . $width . "x" . $height;
                        }
                    }
                }
                //todo:添加图片质量参数

                //添加图片无损压缩参数
                if ($cdnType == "UPYUN") {
                    $addOn .= "/compress/true";
                } else if ($cdnType == "ALIOSS") {

                } else if ($cdnType == "QINIU") {//七牛云
                    $addOn .= "?imageslim";
                }
            }
        }
        return $addOn;
    }


    public static function returnDivLazyLoadHtml($originalSrc, $width, $height)
    {
        $options = mget();
        $placeholder = Utils::choosePlaceholder($options);
        $lazyLoadHtml = "";

        $originalSrc = self::returnImageSrcWithSuffix($originalSrc, null, $width, $height);

        if (in_array('lazyload', Utils::checkArray($options->featuresetup))) {
            $imageSrc = $placeholder;
            $lazyLoadHtml = 'data-original="' . $originalSrc . '"';
        } else {
            $imageSrc = $originalSrc;
        }


        return $lazyLoadHtml . ' style="background-image: url(' . $imageSrc . ')"';
    }

    public static function checkArray($array)
    {
        return ($array == null) ? [] : $array;
    }

    public static function returnImageLazyLoadHtml($base64, $originalSrc, $width, $height)
    {
        $options = mget();
        $placeholder = Utils::choosePlaceholder($options);
        if (!$base64) {
            $originalSrc = self::returnImageSrcWithSuffix($originalSrc, null, $width, $height);
        }

        if (in_array('lazyload', Utils::checkArray($options->featuresetup))) {
            $imageSrc = $placeholder;
            $lazyLoadHtml = 'data-original="' . $originalSrc . '"';
        } else {
            $lazyLoadHtml = "";
            $imageSrc = $originalSrc;
        }

        return $lazyLoadHtml . ' src="' . $imageSrc . '"';
    }

    public static function choosePlaceholder($options)
    {
        return Utils::getExpertValue("loading_img", STATIC_PATH . "img/loading.svg");
//        return "http://localhost/build/usr/themes/handsome/assets/img/loading.svg";
//        if (@in_array("opacityMode",$options->indexsetup)){//透明模式
//            return CDN_Config::OPACITY_PLACEHOLDER;
//        }else{//普通占位符
//            return CDN_Config::NORMAL_PLACEHOLDER;
//
//        }
    }

    public static function getSj1ImageNum()
    {
        try {
            $basedir = dirname(dirname(__FILE__)) . "/assets/img/sj";
            $arr = scandir($basedir);
            $image = count(preg_grep("/^\d+\.jpg$/", $arr));
            return $image;
        } catch (Exception $e) {
            print_r($e);
            return 5;
        }
    }

    /**
     * 获取右侧边栏的图片数目
     * @return int
     */
    public static function getSj2ImageNum()
    {
        try {
            $basedir = dirname(dirname(__FILE__)) . "/assets/img/sj2";
            $arr = scandir($basedir);
            $image = count(preg_grep("/^\d+\.jpg$/", $arr));
            return $image;
        } catch (Exception $e) {
            print_r($e);
            return 5;
        }
    }

    /**
     * 返回运行时间
     */
    public static function getOpenDays()
    {
        $options = mget();
        $oldtime = $options->startTime;
        try {
            $catime = strtotime($oldtime);
            $now = time();
            $difference = $now - $catime;
            $year = floor($difference / 31536000);
            if ($year >= 1) {
                $difference = $difference - $year * 31536000;
                $day = floor($difference / 86400);
                return sprintf(_mt("%d年%d天"), $year, $day);
            } else {//小于一年
                $day = floor($difference / 86400);
                return sprintf(_mt("%d天"), $day);
            }
        } catch (Exception $exception) {
            return "null";
        }

    }

    /**
     * 返回最后更新时间
     */
    public static function getLatestTime($obj)
    {
        $recent = $obj->widget('Widget_Contents_Post_Recent', 'pageSize=1');
        if ($recent->have()) {
            while ($recent->next()) {
                return Utils::naturalDate($recent->modified, "short");
            }
        }

    }

    public static function hEcho($text)
    {
        if (strtoupper(CDN_Config::HANDSOME_DEBUG_DISPLAY) == 'ON') {
            echo $text;
        }
    }


    public static function returnDefaultIfEmpty($target, $default)
    {
        if (trim($target) == "") {
            return $default;
        } else {
            return $target;
        }
    }

    public static function getWordsOfContentPost($content)
    {
        return mb_strlen(trim(strip_tags($content)), "utf8");
    }

    /**
     * @param $blogUrl
     * @param $name
     * @param $pic
     * @param $type string,表示$pic内容是网络地址，local表示$pic内容是本地图片
     * @param string $suffix 图片后缀
     * @return string
     */
    public static function uploadPic($blogUrl, $name, $pic, $type, $suffix)
    {
        //使用插件里面的接口上传图片接口
        $DIRECTORY_SEPARATOR = "/";
        $childDir = $DIRECTORY_SEPARATOR . 'usr' . $DIRECTORY_SEPARATOR . 'uploads' . $DIRECTORY_SEPARATOR . 'time' . $DIRECTORY_SEPARATOR;
        $dir = __TYPECHO_ROOT_DIR__ . $childDir;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fileName = $name . $suffix;
        $file = $dir . $fileName;
        //TODO:支持图片压缩
        if ($type == "web") {
            //开始捕捉
            $img = self::getDataFromWebUrl($pic);
        } else {
            $img = $pic;//本地图片直接就是二进制数据
        }


        $fp2 = fopen($file, "a");
        fwrite($fp2, $img);
        fclose($fp2);

        //压缩图片
        (new Imgcompress($file, 1))->compressImg($file);

        return $blogUrl . $childDir . $fileName;
    }


    public function returnBlogUrl()
    {

    }

    public static function uploadFile($blogUrl, $suffix)
    {

        $DIRECTORY_SEPARATOR = "/";
        $childDir = $DIRECTORY_SEPARATOR . 'usr' . $DIRECTORY_SEPARATOR . 'uploads' . $DIRECTORY_SEPARATOR . 'shell'
            . $DIRECTORY_SEPARATOR;
        $dir = __TYPECHO_ROOT_DIR__ . $childDir;

        $destFileName = $_FILES["file1"]["name"];

//        return $destFileName;
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $destFileName) > 0) {  //判断字符串中是否有中文
            $destFileName = md5($destFileName) . $suffix;
            $destFile = $dir . $destFileName;
        } else {
            $destFile = $dir . $destFileName;
            $destFile = $dir . $destFileName;
        }

        if ($_FILES["file1"]["error"] <= 0) {
//        return $_FILES["file1"]["name"];
            if (move_uploaded_file($_FILES["file1"]["tmp_name"], $destFile)) {
                return $blogUrl . $childDir . $destFileName;
            } else {
                return "-1";
            }
        } else {
            return $_FILES["file1"]["error"];
        }


    }

    public static function getDataFromWebUrl($url)
    {
        $file_contents = "";
        if (function_exists('file_get_contents')) {
            $file_contents = @file_get_contents($url);
        }
        if ($file_contents == "") {
            $ch = curl_init();
            $timeout = 30;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }
        return $file_contents;
    }

    public static function isPluginAvailable($className, $dirName)
    {
        if (class_exists($className)) {
            $plugins = Typecho_Plugin::export();
            $plugins = $plugins['activated'];
            if (is_array($plugins) && array_key_exists($dirName, $plugins)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public static function isNotice2()
    {
        $DIRECTORY_SEPARATOR = "/";
        $childDir = $DIRECTORY_SEPARATOR . 'usr' . $DIRECTORY_SEPARATOR . 'themes' . $DIRECTORY_SEPARATOR . 'handsome'
            . $DIRECTORY_SEPARATOR;
        $dir = __TYPECHO_ROOT_DIR__ . $childDir;
        $path = $dir . "license";
        //检查license文件
        if (file_exists($path)) {
            return false;
        } else {
            $body = file_get_contents($path);
            echo $body;//输入文件内容
            if ($body == md5(CDN_Config::AUTH)) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * 加密算法
     * @param $data 明文数据
     * @return string
     */
    public static function encodeData($data)
    {
        return self::md5($data);
//        return sha1(self::md5($data));
    }


    /**
     * md5 加密，加入特定字符串，避免太过于简单导致的问题
     * @param $data
     * @return string
     */
    public static function md5($data)
    {
        return md5("handsome!@#$%^&*()-=+@#$%$" . $data . "handsome!@#$%^&*()-=+@#$%$");
    }

    /**
     * 二维数组去重
     * @param $arr
     * @param $key
     * @return array
     */
    public static function array_unset_tt($arr, $key)
    {
        //建立一个目标数组
        $res = array();
        foreach ($arr as $value) {
            //查看有没有重复项
            if (isset($res[$value[$key]])) {
                //有：销毁
                unset($value[$key]);
            } else {
                $res[$value[$key]] = $value;
            }
        }
        return $res;
    }

    public static function remove_last_comma($content)
    {
        if (substr($content, -1) == ",") {
            return substr($content, 0, strlen($content) - 1);
        } else {
            return $content;
        }
    }

    public static function getTimelineRandomColor($index)
    {
        $color = array("light", "info", "dark", "success", "black", "warning", "primary", "danger");
//        $color = array( "info", "dark", "success", "black", "warning", "primary", "danger");
        $index = $index % count($color);
        $colorsec = $color[$index];
        return $colorsec;
    }

    /**
     * timeline 没有日期
     * @param $content
     * @param $type
     * @param $index
     * @param bool $isRandom
     * @param string $color
     * @return string
     */
    public static function getTimelineHeader($content, $type, $index, $isRandom = true, $color = "")
    {
        if ($isRandom) {
            if ($color == "") {
                $color = self::getTimelineRandomColor($index);
            }
        }
        if ($type == "small") {
            return <<<EOF
<div class="bg-{$color} wrapper-sm m-l-n m-r-n m-b r r-2x">{$content}</div>
EOF;
        } else {
            if ($color == "light") {
                return <<<EOF
<li class="tl-header">
          <div class="btn btn-sm btn-default btn-rounded m-t-none">{$content}</div>
</li>
EOF;
            } else {
                return <<<EOF
<li class="tl-header">
        <div class="btn btn-sm btn-{$color} btn-rounded m-t-none">{$content}</div>
    </li>

EOF;
            }
        }
    }

    /**
     * timeline 有日期的item
     * @param $date
     * @param $content
     * @param $type
     * @param $index
     * @param bool $isRandom
     * @param string $color
     * @return string
     */
    public static function getTimeLineItem($date, $content, $type, $index, $isRandom = true, $color = "light")
    {

        if ($isRandom) {
            $color = self::getTimelineRandomColor($index);
        }

        if ($type == "small") {
            ($color == "light") ? $color = "" : null;
            return <<<EOF
<div class="sl-item b-{$color} b-l">
                                <div class="m-l">
                                    <div class="text-muted">{$date}</div>
                                    <p>{$content}</p>
                                </div>
                            </div>
EOF;
        } else {

            if ($color == "light") {
                return <<<EOF
<li class="tl-item">
        <div class="tl-wrap">
            <span class="tl-date">{$date}</span>
            <div class="tl-content panel padder b-a">
                <span class="arrow left pull-up"></span>
                <div>{$content}</div>
            </div>
        </div>
    </li>
EOF;

            } else {
                return <<<EOF
    <li class="tl-item">
        <div class="tl-wrap b-{$color}">
            <span class="tl-date">{$date}</span>
            <div class="tl-content panel padder h5 l-h bg-{$color}">
            <span class="arrow arrow-{$color} left pull-up" aria-hidden="true"></span>
                {$content}
            </div>
        </div>
    </li>
EOF;
            }

        }
    }


    /**
     * 判断当前文章是否需要加密显示
     * @param $content string {lock:true,img:"",start:2021-10-1,end:2022-03-01,password:123}
     * @param $unique_id
     * @param $type
     * @param null $timestamp
     * @return array
     */
    public static function isLock($title,$content, $unique_id, $type, $timestamp = null)
    {
        $content = json_decode($content, true);

        # 修改$content数据，以便直接返回需要的数据
        $content["title"] = $title;
        $content['md5'] = Utils::encodeData(@$content['password']);
        $content['type'] = $type;
        $content["unique_id"] = $unique_id;

        //判断是否是加密分类
        //不是加密分类
        if (!is_array($content) || @$content["lock"] == false) {
            $content["flag"] = false;
            return $content;
        }

        //加密分类判断是否有 cookie
        $password = Typecho_Cookie::get($type . '_' . $unique_id);

        $cookie = false;//true为可以直接进入
        if (!empty($password) && $password == @$content['md5']) {
            $cookie = true;
        }

        //没有 cookie并且没有设置区间时间，没有时间要求（分类页面）
        if (!$cookie && $timestamp == null && !@$content["start"] && !@$content["end"]) {//加密分类没有 cookie，禁止访问
            $content["flag"] = true;
            return $content;
        }

        //有 cookie需要进一步校验，没有 cookie说明是加密的
        $showLockFlag = false;//是否是需要加密显示，默认不加密

        if (@$content['lock'] == true) {//有 cookie需要进一步校验，没有 cookie说明是加密的
            $end = @$content['end'];
            $start = @$content['start'];
            if ($start != "" || $end != "") {//判断文章的时间
                $startFlag = false;//开始时间是否满足
                if ($start != "") {
                    $start = strtotime($start);
                    if ($timestamp > $start) {
                        $startFlag = true;
                    }
                } else {
                    $startFlag = true;
                }

                $endFlag = false;//开始时间是否满足
                if ($end != "") {
                    $end = strtotime($end);

                    if ($timestamp < $end) {
                        $endFlag = true;
                    }
                } else {
                    $endFlag = true;
                }

                if ($startFlag && $endFlag) {
                    $showLockFlag = true;
                }
            } else {
                $showLockFlag = true;
            }
        }


        if ($cookie) {//有cookie，就直接可以访问了，不要加密
            $showLockFlag = false;
        }

        $content["flag"] = $showLockFlag;


        return $content;
    }

    public static function getFileName($name)
    {
        if (CDN_Config::DEVELOPER_DEBUG == 1) {
            echo str_replace(".min.", ".", $name);
        } else {
            echo $name;
        }
    }

    public static function getFileList($dirName, $type, $exclude = "")
    {
        $basedir = dirname(dirname(__FILE__)) . "/assets/" . $type . "/" . $dirName;
        $arr = scandir($basedir);
        $arr = preg_grep("/^.+?\.$type$/", $arr);

        foreach ($arr as $v) {
            if (($exclude === "" || @strpos($v, $exclude) === false) && strpos($v, "min.") === false) {
                if ($type == "css") {
                    echo '<link rel="stylesheet" href="' . STATIC_PATH . $type . '/' . $dirName . '/' . $v . '" type="text/css" />' . "\n";
                } else {
                    if (strpos($v, ".min.") === false) {
                        echo '<script src="' . STATIC_PATH . $type . '/' . $dirName . '/' . $v . '"></script>' . "\n";
                    }
                }
            }

        }
    }

    public static function singleToQuote($str)
    {
        $new = str_replace('\'', '"', $str);
        return trim($new);
    }

    public static function isOldTy()
    {
        return !defined('__TYPECHO_CLASS_ALIASES__');
    }

    public static function getMixPrefix()
    {
        return self::isOldTy() ? "old/" : "";
    }


    public static function has_special_char($str)
    {
        $len = mb_strlen($str);
        $array = [];
        for ($i = 0; $i < $len; $i++) {
            $array[] = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($array[$i]) < 4) {
                return false;
            }
        }
        return true;
    }

    public static function getAdapterDriver(){
        $installDb = Typecho_Db::get();
        $type = explode('_', $installDb->getAdapterName());
        $type = array_pop($type);
        $type=  strtolower($type);
        return ($type == "mysqli") ? "mysql" : $type;
    }

    public static function searchGetResult($thisText,$isLogin,$summaryNam =20){
        require_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/Handsome/cache/cache.php';
        $cache = new CacheUtil();
        $file = $cache->cacheRead("search");

        if (!$file){
            return [];
        }
        $cache = json_decode($file,true);
        $html = "";

        $resultLength = 0;
        $searchResultArray = [];//搜索结果
        if (trim($thisText) !== ""){
            $searchArray = mb_split(" ",$thisText);
            if (count(self::checkArray($searchArray)) > 1){// 增加原来每分割的完整词
                $searchArray[] = $thisText;
            }
            foreach (@$cache as $item) {// 对每篇文章进行匹配检测
                //可能的匹配结果
                $ret = array(
                    "path" => $item["path"],
                    "title" => $item["title"],
                    "content" => ""
                );
                $isMatch = false;
                $isContentMatch = false;
                // 根据登陆清空过滤内容
                $item['content'] = Content::returnExceptShortCodeContent(trim(strip_tags($item['content'])),!$isLogin);
                foreach ($searchArray as $thisText){
                    if (trim($thisText) != ""){// 关键词不为空
                        //1. 检查权限控制，不符合权限的直接不进行任何匹配检测
                        if (!$isLogin){//没有登陆
                            if ($item["info"]["type"] == "lock_post" || $item["info"]["type"]=="private"){
                                break; //未登录下过滤加密和私密文章
                            }
                            if ($item["info"]["type"] == "lock_category"){
                                // 判断加密的日期
                                $canShow = false;//该加密文章不可以显示
                                if (@$item["info"]["start"]) {
                                    if (strtotime($item['date']) < strtotime($item["info"]["start"])) {
                                        $canShow = true;
                                    }
                                }

                                if (@$item["info"]["end"]) {
                                    if (strtotime($item['date']) > strtotime($item["info"]["end"])) {
                                        $canShow = true;
                                    }
                                }
                                if (!$canShow){
                                    break;
                                }
                            }
                        }
                        if ($item["info"]["type"] == "draft" || $item["info"]["type"] == "waiting"){
                            break;//过滤草稿和待审核内容
                        }

                        //2. 检查内容和标题是否匹配
                        //如果内容已经匹配过了，下次只匹配摘要的内容就行了
                        $post_content = (!$isContentMatch) ? $item['content'] : strip_tags($ret["content"]);
                        $content_ok = mb_stripos($post_content, $thisText);
                        $title_ok = mb_stripos(strip_tags($ret['title']), $thisText);

                        if ($content_ok===false && $title_ok===false){
                            continue;//检测下一个关键词
                        }

                        if ($title_ok!==false){//标题有匹配
                            //高亮标题
                            $ret["title"] = str_ireplace($thisText,"<mark class='text_match'>".$thisText."</mark>",
                                $ret["title"]);
                            if (!$isContentMatch){
                                $ret["content"] =  mb_substr($post_content,0,min($summaryNam,mb_strlen($item['content'])),'utf-8');
                            }
                        }

                        if ($content_ok!==false){//内容中有匹配的结果
                            //高亮内容
                            if (!$isContentMatch){//以关键词为中心从文章中选择$summaryNam 个字作为摘要。
                                $start = max(0,$content_ok -$summaryNam/2);
                                $contentMatch = mb_substr($post_content,$start,min($summaryNam,mb_strlen
                                    ($post_content) -$start),'utf-8');
                            }else{//如果已经匹配过，摘要的那一段文字就不动了
                                $contentMatch = $ret["content"];
                            }
//                            echo($post_content."%".$contentMatch."%\n");
                            $contentMatch = str_ireplace($thisText,"<mark class='text_match'>".$thisText."</mark>",
                                $contentMatch);
                            $ret["content"] = $contentMatch;
                            $isContentMatch = true;
                        }
                        $isMatch = true;
                    }
                }


                if ($isMatch){
                    $searchResultArray [] = $ret;
                    $resultLength ++;
                }


            }


            $searchResultArray = Utils::array_unset_tt($searchResultArray,"path");

        }

        return $searchResultArray;
    }


}
