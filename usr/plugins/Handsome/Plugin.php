<?php
/**
 * <strong style="color:red;">handsomePro 唯一配套插件</strong>
 *
 * @package Handsome
 * @author hewro,hanny
 * @version 8.4.1
 * @dependence 1.0-*
 * @link https://www.ihewro.com
 *
 */

error_reporting(0);
ini_set('display_errors', 0);

//如果需要显示php错误打开这两行注释，问题修复后必须关闭！
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

use Typecho\Request;
use Typecho\Widget\Helper\Form\Element;
use Typecho\Widget\Helper\Layout;


require_once("libs/Tool.php");
require_once ('cache/cache.php');

// 引入theme 文件夹的文件
$theme_root = dirname(dirname(__DIR__)) . "/themes/handsome/";
require_once($theme_root . "libs/Options.php");

require_once($theme_root . "libs/CDN.php");
require_once($theme_root . "libs/Utils.php");

//1. 设置语言
require_once($theme_root . "libs/I18n.php");
require_once($theme_root . "libs/Lang.php");

function isOldTy()
{
    return !defined('__TYPECHO_CLASS_ALIASES__');
}

$prefix = isOldTy() ? "old/" : "";
require_once("admin/" . $prefix . "Title.php");


class Handsome_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        // todo 判断handsome 主题有没有启用
        $options = mget();
        I18n::loadAsSettingsPage(true);
        I18n::setLang($options->admin_language);

        //时光机评论禁止游客发布说说
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('Handsome_Plugin', 'filter');


        //vEditor
        Typecho_Plugin::factory('admin/write-post.php')->richEditor = array('Handsome_Plugin', 'VEditor');
        Typecho_Plugin::factory('admin/write-page.php')->richEditor = array('Handsome_Plugin', 'VEditor');


        //友情链接
        $info = "插件启用成功</br>";
        $info .= Handsome_Plugin::linksInstall() . "</br>";
        $info .= Handsome_Plugin::cacheInstall() . "</br>";
        Helper::addPanel(3, 'Handsome/manage-links.php', '友情链接', _mt('管理友情链接'), 'administrator');
        Helper::addAction('links-edit', 'Handsome_Action');
        Helper::addAction('multi-upload', 'Handsome_Action');
        Helper::addAction('handsome-meting-api', 'Handsome_Action');

        //过滤私密评论
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('Handsome_Plugin', 'exceptFeedForDesc');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('Handsome_Plugin', 'exceptFeed');
        Typecho_Plugin::factory('Widget_Abstract_Comments')->contentEx = array('Handsome_Plugin', 'parse');


        //markdown 引擎
//        Typecho_Plugin::factory('Widget_Abstract_Contents')->content = array('Handsome_Plugin', 'content');


        //置顶功能
        Typecho_Plugin::factory('Widget_Archive')->indexHandle = array('Handsome_Plugin', 'sticky');
        //分类过滤，默认过滤相册
        //首页列表的过滤器
        Typecho_Plugin::factory('Widget_Archive')->indexHandle = array('Handsome_Plugin', 'CateFilter');
        //某个分类页面的过滤器
        Typecho_Plugin::factory('Widget_Archive')->categoryHandle = array('Handsome_Plugin', 'CategoryCateFilter');


        Typecho_Plugin::factory('Widget_Archive')->footer = array('Handsome_Plugin', 'footer');

        // 注册文章、页面保存时的 hook（JSON 写入数据库）
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('Handsome_Plugin', 'buildSearchIndex');
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishDelete = array('Handsome_Plugin', 'buildSearchIndex');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array('Handsome_Plugin', 'buildSearchIndex');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishDelete = array('Handsome_Plugin', 'buildSearchIndex');

        //添加评论的回调接口
        Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('Handsome_Plugin', 'parseComment');
        Typecho_Plugin::factory('Widget_Comments_Edit')->finishComment = array('Handsome_Plugin', 'parseComment');

//        //评论的异步接口
//        Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('Mailer_Plugin', 'sendComment');
//        Typecho_Plugin::factory('Widget_Service')->parseComment = array('Mailer_Plugin', 'parseComment');

        self::buildSearchIndex();
//        $info .= "首次启动，需要在插件设置里面更新搜索索引</br>";

        return _t($info);
    }


    public static function sendComment($comment)
    {
        Helper::requestService('parseComment', $comment);
    }


    public static function parseComment($comment)
    {
        if ($comment->authorId !== "0") {//是登录用户，authorid 是该条评论的登录用户的id
            $cache = new CacheUtil();
            $cache->cacheWrite("comment",date("Y-m-d"),CacheUtil::$not_expired_time,"comment",true,true);
        }

    }

    public static function filter($comment, $post)
    {
        if ($post->slug === "cross") {
            if (!$comment["authorId"] && !$comment["parent"]) {//不是登录用户，而且发表的是说说，这需要拦截
                throw new Typecho_Widget_Exception("你没有权限发表说说");
            }
        }

        return $comment;
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        Helper::removeAction('links-edit');
        Helper::removeAction('multi-upload');
        Helper::removeAction('handsome-meting-api');

        Helper::removePanel(3, 'Links/manage-links.php');
        Helper::removePanel(3, 'Handsome/manage-links.php');
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {

        require 'Device.php';
        Utils::initGlobalDefine(true);

        // todo 判断handsome 主题有没有启用
        $options = mget();
        I18n::loadAsSettingsPage(true);
        I18n::setLang($options->admin_language);

        if (isset($_GET['action']) && $_GET['action'] == 'clearMusicCache') {
            self::clearMusicCache();
        }

        if (isset($_GET['action']) && $_GET['action'] == 'buildSearchIndex') {
            self::buildSearchIndex();
        }


        if (isset($_GET['action']) && $_GET['action'] == 'moveToRoot') {
            self::moveToRoot();
        }

        $form->addInput(new Title_Plugin('btnTitle', NULL, NULL, _t('致谢'), NULL));


        $thanks = new Typecho_Widget_Helper_Form_Element_Select("thanks", array(
            1 => "友情链接功能由<a href='http://www.imhan.com'>hanny</a>开发，感谢！",
            2 => "主题播放器基于Aplayer项目并集成了APlayer-Typecho插件，感谢！"
        ), "1", "插件致谢", "<strong style='color: red'> 【友情链接】请在typecho的后台-管理-友情链接 设置</strong>");
        $form->addInput($thanks);

        $form->addInput(new Title_Plugin('btnTitle', NULL, NULL, _mt('文章设置'), NULL));


        $sticky_cids = new Typecho_Widget_Helper_Form_Element_Text(
            'sticky_cids', NULL, '',
            '置顶文章的 cid', '按照排序输入, 请以半角逗号或空格分隔 cid.</br><strong style=\'color: red\'>cid查看方式：</strong>后台的文章管理中，进入具体的文章编辑页面，地址栏中会有该数字。如<code>http://localhost/build/admin/write-post.php?cid=120</code>表示该篇文章的cid为120');
        $form->addInput($sticky_cids);

        $form->addInput(new Title_Plugin('btnTitle', NULL, NULL, _t('分类设置'), NULL));


        $CateId = new Typecho_Widget_Helper_Form_Element_Text('CateId', NULL, '', _t('首页不显示的分类的mid'), _t('多个请用英文逗号,隔开</br><strong style="color: red">mid查看方式：</strong> 在分类管理页面点击分类，地址栏中会有该数字，比如<code>http://localhost/build
/admin/category.php?mid=2</code> 表示该分类的mid为2</br><strong style="color: rgba(255,0,18,1)">默认不过滤相册分类，请自行过滤</strong></br> <b style="color:red">说明：填写该设置后，是指该分类的文章不在首页文章页面中显示，如果希望实现侧边栏不显示某个分类，可以查看<a target="_blank" href="https://auth.ihewro.com/user/docs/#/preference/hide">使用文档——内容隐藏</a>中说明</b>'));
        $form->addInput($CateId);

        $LockId = new Typecho_Widget_Helper_Form_Element_Text('LockId', NULL, '', _t('加密分类mid'), _t('多个请用英文逗号隔开</br><strong style="color: red">mid查看方式：</strong> 在分类管理页面点击分类，地址栏中会有该数字，比如<code>http://localhost/build
/admin/category.php?mid=2</code> 表示该分类的mid为2</br><strong style="color: rgba(255,0,18,1)">加密分类的密码需要在分类描述按照指定格式填写<a 
href="https://auth.ihewro.com/user/docs/#/preference/lock" target="_blank">使用文档</a></strong></br><strong style="color: rgba(255,0,18,1)">加密分类仍然会在首页显示标题列表，但不会显示具体内容，也不会出现在rss地址中</strong>'));
        $form->addInput($LockId);

        $form->addInput(new Title_Plugin('btnTitle', NULL, NULL, _t('搜索设置'), NULL));

        $queryBtn = new Typecho_Widget_Helper_Form_Element_Submit();
        $queryBtn->value(_t('构建文章索引'));
        self::renderHtml();
        $queryBtn->description(_t('通常不需要手动构建，在发布、修改文章的时候会自动构建新的索引。但是如果发现搜索数据不对，请手动点击此按钮构建'));
        $queryBtn->input->setAttribute('class', 'btn btn-s btn-warn btn-operate');
        $queryBtn->input->setAttribute('formaction', Typecho_Common::url('/options-plugin.php?config=Handsome&action=buildSearchIndex', Helper::options()->adminUrl));
        $form->addItem($queryBtn);
        $cacheWhen = new Typecho_Widget_Helper_Form_Element_Radio('cacheWhen',
            array(
                'true' => '文章保存同时更新搜索索引',
                'false' => '不实时更新索引，适用于网站的文章特别多，此时需要手动更新索引',
            ), 'true', _t('实时更新索引'), _t('网站文章特别多（超过1000篇）的时候，请关闭实时更新索引，否则保存文章时候花费时间较长可能会显示超时错误'));
        $form->addInput($cacheWhen);


        $form->addInput(new Title_Plugin('btnTitle', NULL, NULL, _t('编辑器设置'), NULL));


        $editorChoice = new Typecho_Widget_Helper_Form_Element_Radio('editorChoice',
            array(
                'origin' => '使用typecho自带的markdown编辑器',
                'vditor' => '使用vditor编辑器 <a href="https://auth.ihewro.com/user/docs/#/preference/vditor" target="_blank">vditor使用介绍</a>',
                'other' => '使用其他第三方编辑器'
            ), 'origin', _t('<b style="color: red">后台</b>文章编辑器选择'), _t('可根据个人喜好选择'));
        $form->addInput($editorChoice);


        $vditorMode = new Typecho_Widget_Helper_Form_Element_Radio('vditorMode',
            array(
                'wysiwyg' => '所见即所得',
                'ir' => '即时渲染',
                'sv' => '源码模式（和typecho默认的编辑器几乎一致）',
                'sv_both' => '源码模式+分屏预览所见即所得',
            ), 'ir', _t('vditor默认模式选择'), _t('
                所见即所得（WYSIWYG对不熟悉 Markdown 的用户较为友好，熟悉 Markdown 的话也可以无缝使用。<a href="https://s1.ax1x.com/2020/08/03/aajX0e.gif" target="_blank">演示效果</a>  </br>
                即时渲染模式对熟悉 Typora 的用户应该不会感到陌生，理论上这是最优雅的 Markdown 编辑方式。<a href="https://s1.ax1x.com/2020/08/03/aajxkd.gif" target="_blank">演示效果</a> </br>       
                传统的分屏预览模式适合大屏下的 Markdown 编辑。<a href="https://s1.ax1x.com/2020/08/03/aajfw4.gif" target="_blank">演示效果</a>     
            '));
        $form->addInput($vditorMode);

        $parseWay = new Typecho_Widget_Helper_Form_Element_Radio('parseWay',
            array(
                'origin' => '使用typecho自带的markdown解析器',
                'vditor' => '前台引入vditor.js接管前台解析',
            ), 'origin', _t('<b style="color: red">前台</b>Markdown解析方式选择'), _t('1.选择typecho自带解析器，即和typecho默认的解析器一致，可以在基础上使用第三方markdown解析器，主题在此基础上内置了mathjax和代码高亮，需要在主题增强功能里面开启</br>2.选择vditor前台解析，可以与后台编辑器得到相同的解析效果，支持后台编辑器的所有语法，<b style="color: red">但是对于有些插件兼容性不好，并且不支持ie浏览器（在ie11 浏览器中会自动切换到typecho原生解析方式）</b></br>'));
        $form->addInput($parseWay);

        $urlUpload = new Typecho_Widget_Helper_Form_Element_Radio('urlUpload',
            array(
                'true' => '开启外链上传',
                'false' => '关闭外链上传',
            ), 'false', _t('vditor开启外链上传'), _t('开启此功能后，复制粘贴的文本到编辑器中，如果文本中包含了外链的图片地址，会自动上传到自己服务器中，<b style="color:red;">仅当后台编辑器选择vditor编辑器有效</b>'));
        $form->addInput($urlUpload);

        $vditorCompleted = new Typecho_Widget_Helper_Form_Element_Textarea('vditorCompleted', NULL, "",
            _t('vditor.js 解析结束回调函数'), _t('如果前台选择了 vditor.js 解析，有一些JavaScript代码可能需要在vditor.js 解析文章内容后再对文章内容进行操作，可以填写再这里</br> 如果不明白这项，请清空'));
        $form->addInput($vditorCompleted);


        $form->addInput(new Title_Plugin('btnTitle', NULL, NULL, _t('客户端静态资源缓存设置'), NULL));
        $cacheSetting = new Typecho_Widget_Helper_Form_Element_Radio('cacheSetting',
            array(
                'yes' => '是，(该功能需要https) 使用离线缓存，缓存与主题相关的静态资源。',
                'no' => '否，缓存特性插件不进行额外接管，由浏览器和自己使用的CDN进行控制',
            ), 'no', _t('使用本地离线缓存功能'), _t('使用本地缓存主题相关的静态资源后，加载速度能够得到明显的提升，主题目录下面的assets 文件夹会进行本地缓存（使用service worker 实现）,</br> 在「版本更新」和「使用强制刷新、清除缓存」的情况下才会更新这些资源'));
        $form->addInput($cacheSetting);


        $queryBtn = new Typecho_Widget_Helper_Form_Element_Submit();
        $queryBtn->value(_t('更新离线缓存'));
        $queryBtn->description(_t('<b>首次使用离线缓存，请先在「使用本地离线缓存功能」设置中选择是，保存插件设置，最后再点击该按钮。</b></br><b style="color:red;">后续如果主题目录下面的 assets 文件夹内容有修改，需要点击该按钮，并且再次访问首页才会更新缓存</b>'));
        $queryBtn->input->setAttribute('class', 'btn btn-s btn-warn btn-operate');
        $queryBtn->input->setAttribute('formaction', Typecho_Common::url('/options-plugin.php?config=Handsome&action=moveToRoot', Helper::options()->adminUrl));
        $form->addItem($queryBtn);

//
        $form->addInput(new Title_Plugin('handsome_aplayer', NULL, NULL, _t('数据库缓存设置'), NULL));

        $queryBtn = new Typecho_Widget_Helper_Form_Element_Submit();
        $queryBtn->value(_t('清除音乐播放器缓存'));
        $queryBtn->description(_t('播放器缓存有效期为一天，一天后会自动清空。一般无需执行该按钮。如果在有效期内变更了歌单内容则需要清空缓存。</br>
<b>音乐解析地址默认为：</b>' . Typecho_Common::url('/options-plugin.php?config=Handsome&action=clearMusicCache', Helper::options()->adminUrl) . '</br>可以在外观设置的开发者设置里面修改'));
        $queryBtn->input->setAttribute('class', 'btn primary');
        $queryBtn->input->setAttribute('formaction', Typecho_Common::url('/options-plugin.php?config=Handsome&action=clearMusicCache', Helper::options()->adminUrl));
        $form->addItem($queryBtn);

        $form->addInput(new Title_Plugin('handsome_aplayer', NULL, NULL, _t('播放器设置'), NULL));



        //加盐的内容
        $t = new Typecho_Widget_Helper_Form_Element_Text(
            'salt',
            null,
            Typecho_Common::randString(32),
            _t('接口保护'),
            _t('加盐保护 API 接口不被滥用，自动生成无需设置，也可以自行填写任意值（相当于密码），如果为空表示接口不进行校验')
        );
        $form->addInput($t);

        //cookie 填写
        $t = new Typecho_Widget_Helper_Form_Element_Textarea(
            'cookie',
            null,
            '',
            _t('网易云音乐 Cookie（修改后需要清空播放器缓存才生效），为空则使用主题内置cookie'),
            _t('如果您是网易云音乐的会员，可以将您的 cookie 填入此处来获取云盘等付费资源，听歌将不会计入下载次数。<br><b>使用方法见 <a target="_blank" href="https://auth.ihewro.com/user/docs/#/preference/player?id=%e7%bd%91%e6%98%93%e4%ba%91-cookie-%e8%ae%be%e7%bd%ae">网易云 cookie 获取方法</a></b>')
        );
        $form->addInput($t);

        //qq 音乐cookie 填写
        $t = new Typecho_Widget_Helper_Form_Element_Textarea(
            'qq_cookie',
            null,
            '',
            _t('QQ音乐 Cookie（修改后需要清空播放器缓存才生效），为空则使用主题内置cookie'),
            _t('如果您是QQ音乐的会员，可以将您的 cookie 填入此处来获取云盘等付费资源，听歌将不会计入下载次数。<br><b>使用方法见 <a target="_blank" href="https://auth.ihewro.com/user/docs/#/preference/player?id=qq%e9%9f%b3%e4%b9%90%e7%9a%84cookie%e8%ae%be%e7%bd%ae">QQ音乐 cookie 获取方法</a></b>')
        );
        $form->addInput($t);

    }


    public static function movetoRoot()
    {
        //将主题目录下面的sw.js 移动到typecho根目录，以便进行离线缓存
        $options = Helper::options();
        $sourcefile = __TYPECHO_ROOT_DIR__ . "/usr/themes/handsome/assets/js/sw.min.js";
        $dir = __TYPECHO_ROOT_DIR__;
        $filename = "/sw.min.js";
        if (!file_exists($sourcefile)) {
            Typecho_Widget::widget('Widget_Notice')->set(_t("开启本地离线缓存失败1"), 'error');
        }

        $origin_content = file_get_contents($sourcefile);
        $replace = str_replace("[VERSION_TAG]", uniqid(), $origin_content);
        $replace = str_replace("[BLOG_URL]", $options->rootUrl, $replace);
        $replace = str_replace("[CDN_ADD]", trim(Utils::getCDNAdd(1)[0]), $replace);

        if (copy($sourcefile, $dir . '' . $filename)) {
            //将文件的内容修改
            if (file_put_contents($dir . $filename, $replace)) {
                //将文件的内容修改
                Typecho_Widget::widget('Widget_Notice')->set(_t("更新本地离线缓存成功"), 'success');
            } else {
                Typecho_Widget::widget('Widget_Notice')->set(_t("更新本地离线缓存失败，可能原因权限不够：可以在typecho根目录手动创建sw.min.js，并给该文件777权限后，再次执行该按钮。",'error'),
                    'error');
            }
        } else {
            Typecho_Widget::widget('Widget_Notice')->set(_t("开启本地离线缓存失败，可能原因权限不够：可以手动将主题目录下面的aseets/js/sw.min.js 移动到typecho 根目录，无需执行该按钮。",'error'),
                'error');
        }

    }

    public static function clearMusicCache()
    {
        $cache = new CacheUtil("music");
        $cache->cacheClear();
        Typecho_Widget::widget('Widget_Notice')->set(_t("清空音乐缓存成功"), 'success');
    }

    public static function checkArray($array)
    {
        return ($array == null) ? [] : $array;
    }

    public static function getPostInfo($cate_data, $status, $article_type, $article_password){
        // $status ['private', 'waiting', 'publish', 'hidden'])私密、待审核、公开、隐藏
        // $article_type post  post_draft page
        $info = array();
        if ($status == "publish"){
            // normal、draft、lock_post、lock_category
            if (@$cate_data["password"] != ""){
                $info["type"] = "lock_category";
                $info["password"] = @$cate_data["password"];
                $info["start"] = @$cate_data["start"];
                $info["end"] = @$cate_data["end"];
            }
            if ($article_password != ""){
                $info["type"]  = "lock_post";
                $info["password"] = $article_password;
            }
            if (strpos($article_type, "draft") === True){
                $info["type"]  = "draft";
            }
            if (@$info["type"] == ""){
                $info["type"]  = "normal";
            }
        }else {
            // private、waiting、hidden
            $info["type"]  = $status;
        }
        return $info;
//        (@$data["password"] == null || @$data["password"] == "") && strpos($contents['type'], "draft") === FALSE && $contents['visibility'] == "publish"
    }

    public static function buildSearchIndex($contents = null, $edit = null)
    {
        $cache = new CacheUtil();
        //生成索引数据
        if ($edit != null) {
            //如果是新增文章或者修改文章无需构建整个索引，速度太慢
            $config = Typecho_Widget::widget('Widget_Options')->plugin('Handsome');

            if ($config->cacheWhen !== "false") {//实时更新索引
                $code = self::checkArray(json_decode($cache->cacheRead("search")));

                $data = @$edit->categories[0]['description'];
                $data = json_decode($data, true);

                //寻找当前编辑的文章在数组中的位置
                if ('delete' == $edit->request->do) {//文章删除
                    $cid = $contents;
                } else {
                    $cid = @$edit->cid;
                }
                $flag = -1;
                for ($i = 0; $i < count($code); $i++) {
                    $item = @$code[$i];
                    if (@$item->cid == $cid) {
                        //匹配成功
                        $flag = $i;
                        break;
                    }
                }
                if ($flag != -1) {//找到了当前保存的文章，直接修改内容即可或者删除一篇文章
                    //不是加密文章、草稿、私密、隐藏文章
                    if ('delete' == $edit->request->do) {//文章删除
                        unset($code[$flag]);
                    } else  {
                        //修改值
                        $code[$flag]->title = $contents["title"];
                        $code[$flag]->path = $edit->permalink;
                        $code[$flag]->date = date('c', $edit->created);
                        $code[$flag]->content = $contents["text"];
                        $info = self::getPostInfo($data,$contents["visibility"],$contents["type"],$contents["password"]);
                        $code[$flag]->info = $info;

                    }
                } else {//新增一篇文章
                    //新增一条记录，也有一种可能是编辑的时候把链接地址也改了，就导致错误增加了一条
                    $info = self::getPostInfo($data,$contents["visibility"],$contents["type"],$contents["password"]);

                    $code[] = (object)array(
                        'title' => $contents['title'],
                        'date' => date('c', $edit->created),
                        'path' => $edit->permalink,
                        'content' => trim(strip_tags($contents['text'])),
                        'info' => $info
                    );
                }
                $cache->cacheWrite("search", json_encode(array_values($code)), CacheUtil::$not_expired_time,"search",false,true);
            }

        } else {//插件设置界面的构建索引，如果数据太大则速度较慢
            //判断是否有写入权限
            // 获取搜索范围配置，query 对应内容
            $ret = array();
            $ret = array_merge($ret, self::build('post'));
//            $ret = array_merge($ret, self::build('page'));

            $ret = json_encode($ret);

            //写入文章数据文件
            $cache->cacheWrite("search", $ret, CacheUtil::$not_expired_time,"search",false,true);


            // 写入评论数据
            $ret = self::build('comment');
            $cache->cacheWrite("comment", $ret, CacheUtil::$not_expired_time,"comment",false,true);
        }

        Typecho_Widget::widget('Widget_Notice')->set(_t("写入搜索数据成功"), 'success');
    }

    public static function renderHtml()
    {
        $options = Helper::options();
        $blog_url = $options->rootUrl;
        $site_url = $options->siteUrl;
        $code = '"' . md5($options->time_code) . '"';
        $debug = "aHR0cHM6Ly9hdXRoLmloZXdyby5jb20vdXNlci91c2Vy";
        echo '<script src="' . THEME_URL . 'assets/libs/jquery/jquery.min.js"></script>';
        echo '<script>var debug="' . $debug . '";var blog_url="' . $blog_url . '";var site_url="' . $site_url . '";var code=' . $code . ';var version="8.4.1"</script>';
        echo '<script>
var _0xod0="jsjiami.com.v6",_0xod0_=["_0xod0"],_0x4a42=[_0xod0,"w6tZF8KyLQ==","wpcubmxj","w6rCvX/Di8KK","w5LConLDhsKU","OS/CvcKvwrk=","cWDCscOiw5A=","YT3DuihK","JsK4A8KTw4g=","wqMmSX1b","e8OrHAtj","wqzCvXkRXw==","CcKowogiwow=","w5FAPsKMGwc=","aMOXK8KxAA==","WcORG8Knwok=","w6kaLHzDug==","W8Onw5fDmmo=","w7bDiTQSYg==","QD/DoAdO","w63DhRNx","LsKMHcKmw5E=","eMO8w5bDow==","w6TDtcOrw502","w4bDnxAnXjfDlMKfwqw=","dDx/cTM=","RsOTAMOQw5Q=","MMKlenwS","fGDCjcOKw4IWCDN5L8ObwonCqQ9mwqhuw5wZcMK/w6AG","ZcOmLA==","w51QPQ==","w4PCi1nDoMK6","UFV2w4E/","XmzCrcOTw64=","W8OcK8KCwoo=","e8O1NsK/Jw==","wrvDqSYUw5PCng==","XcKNbcOONQ==","c8OEQsOdw68=","BcKsI3XCoMOtwoc=","Y8OwKMOiY34=","wrtrJhLClw==","wobCiVcJQQ==","TcOJw6DDlGM=","F8KSwpAYwpY=","wrXCjMOgwoTDuA==","w4rDlsOYw7Al","DcOMKcKgwr8=","wrYBXmM=","CsO3fQ/CvSQ=","VMOWOwBA","RcOvIMKCFw==","w7QAHnDDtA==","ccOQRMOAw7I=","w7fDhsOLw6Yw","fcOPTcOcw4o=","D8KgCcKMw6E=","w7TDkMOMw6c4","wqleBTLCgQ==","w4wMwoHCrlU=","w7nDiMONw6Qx","A8O2YinCvw==","EcKeQXwD","SApzcgw=","w7zDosOkw5knMw==","w64mKk0=","w78jKUnDrw==","J3YQSw==","Q8Oef8KmfsO9w45rWcKfKMOpw4jDmF9aDEjCpsKYMsOtVcOqwpkawpsWw5wEw5ZfZ03CmgzDpcKmwoElwpjDjFsYNcOWw4fCl8OEdQxjcsO0Hw0Cf0LChhgZc8K4","dcOMfcOO","wq3DryMYw5s=","fMO5w5XDvH8=","w7dUIHXDkw==","KHhQw6E=","w4fDiTcYQg==","esOFw6rDmFM=","AsOBwpRi","w5fDsTgneg==","w6g8OErDow==","bsOyMsOuUg==","SsOOw4jDiFY=","bcOCw5LDoW0=","JcOWDcKYwpg=","AsOCUSfCnA==","bMOKw5bDinA=","PW1Tw7ks","KsO7Wi3CvA==","YsO5w6rDulw=","w4fColfDv8Ko","MinCgsKhwrs=","FsK+WcOy","GsKwwrBpNsOid3ASwrFrWMKVPcOybiA=","aj1SB8Ktw4w=","OcOhwpHDsTfCrlw2wqjDhgrDtMOY","wrgPwplNwrk=","w73DssO/w6ch","TsKjw65masOyJA==","w63Dp8KAwqooQcK3","NsOvA2rClQ==","P8KxNsKfw5Y=","DMK1woY=","WWtzw6YuwpRHDCY=","wrEVX2U=","w4ZVB37Dug==","X8OfMAw=","TsO9A8OocA==","WzZuVxo=","BFhqw6Un","OcO+OMKLwrvDvSrCnW4=","wpBjKS8=","DsOqw7U0wqzClsKu","SMK+w7J6dw==","VcOeOBB/woXChg==","C8Kadkw5DA8=","e8OnJ8Omcg==","w5BaN8KYERlX","wqgGw6k0","KEYBwpcCwr95","wpzDr2A=","w7RSCH7Dq8Otwr7DmcOQw6nCnkBCAAgFLMOFcGnDksO8UA==","wrcPwotzwq7Dhw==","w5pTC3E=","IcKHw6cfw5/DtcOGdMKZB8KPb3XDnVwr","CcOcd8OsBRTCiMKKAsOVAcKrwqxH","acOMLcKZPw==","fy0Qwqw3w59r","J8OQw451wonCrsKsw7jDmsKWwrTDk13DisKDRcOSwrV0YW1gw4YAw5PDiElHaB8hKsOFwrQzwrHDlMKxa8KTw4Zaw4lpw5LClmHDhRLCrHTDvlvCon3DpMO7RcO3HkhyIMOnSxRzw4/Dsj/Di8KfX8OHwqLCkkPCi37CvHwbLQ==","w5UMwqXDlFrClHpVWsK4wohmfxNaeMOKwqvDswjDk8Ogw7HCq8KZw6RswrIxw6J/w5RjIMOuKsKtIkQMwo4/PxLChSjCsg7DlWvCisKLG8Ocw6bDlwvCicKxGBwHDnkOwrkww4rChcO8VDImUGwdN0dFw48WQ8OfaggWKsKaAsO9c8KDwq1nw5HDng==","wqnCvcOtwofDkw==","woXCp8Ocwow=","DcOhNHPCosKt","BsOVwp4o","w4/DoWQiM8KYIhxQFMKwwrx+OsOkw7/CrUXCjA==","FMOXccOx","WcKpw6lXfw==","wphuOynCnMK4","csOtEMOVw6U=","WcOTw7bDnVM=","BMO2LXY=","X3x+w7c7wo5a","F8OHwpN/cVQ=","wpPDikExMw==","w55XNWXDug==","Unxkw6o9woUc","PsOoZxjCkA==","wr3DszAYw5vCkDfChMOu","SzjDmAZCw4o=","FlhJw4EZ","wq7Chlog","w6fDpAo7Sw==","DWR1w6ki","CcOFSR3Cpw==","bCt1fwc=","wqbDsy8d","di9UC8K6","U8OTccO8w5Q=","GcOkKsK6wrc=","I10Cwog=","VGV4w44f","KsOLwp8LKw==","BMOOwpUv","CcOcd8OsBRTCiMKKAsOVAcK7wqVWwqE=","w4HDucKLwp8u","NcOzw6gfwro=","BXNTw7cs","dTd7fQo=","DMOxw7Ql","w4fDgsKswpAV","EsOnNcKBwoI=","XMOtE8KmGg==","Kl0AwoY=","aMOHbMOO","CcOgw7gowqfCn8KewprDmcKBwrfDuWfDsMK4FMO2wpQ=","w5MhL03DjA==","QsO7BMKIwow=","DEIawrcu","VQEuwqU=","RifDjw==","L8KgVVnCrQvCisOAHMO+woFRw70TwqDCpA==","wqDDiW4lPw==","wrojwo1mwoI=","w70swpfCgXXCsg==","wqoRQ2xgMw==","XcOREcOhw4A=","PMO7bMOMew==","D8KTKcKEw7I=","KMOEOsKBwo8=","w5xtL1HDng==","dD5ON8KX","d8KlZMO5MQ==","B8OnN8KBwroFCcOQwoAfw5/CpMO2TiPDosOwTRzCql4aNg==","wpzDtg49w6U=","VMKNw48/","AcOsw7ESwow=","ejvDgjRZ","EMO0w4J8w6w=","PMO4QgzCnA==","R8OrLMO2w5Q=","VcO1w5PDhEE=","bcOQH8Ogdg==","FMOiwp8+Ow==","H8K7BX4ow4jCqMKPw4/Cl8KHwrjCnjI2wp7DgCrCozlSHcKX","EcODw4pcw40=","wqQBSmxxKQ==","wr7DumYcCg==","wp3DnjESw7E=","wofDsjUfw5w=","bjQCsjiawGmTIi.dcefbVoGOmC.v6=="];if(function(_0x57cf48,_0x5a5677,_0x108de5){function _0x1ca755(_0x5b0040,_0x4c889a,_0x35670f,_0x39980d,_0x547b7c,_0x10bbb0){_0x4c889a=_0x4c889a>>0x8,_0x547b7c="po";var _0x2ac071="shift",_0x3c761e="push",_0x10bbb0="0.gvd5ldt78ag";if(_0x4c889a<_0x5b0040){while(--_0x5b0040){_0x39980d=_0x57cf48[_0x2ac071]();if(_0x4c889a===_0x5b0040&&_0x10bbb0==="0.gvd5ldt78ag"&&_0x10bbb0["length"]===0xd){_0x4c889a=_0x39980d,_0x35670f=_0x57cf48[_0x547b7c+"p"]();}else if(_0x4c889a&&_0x35670f["replace"](/[bQCwGTIdefbVGOC=]/g,"")===_0x4c889a){_0x57cf48[_0x3c761e](_0x39980d);}}_0x57cf48[_0x3c761e](_0x57cf48[_0x2ac071]());}return 0xd8121;};return _0x1ca755(++_0x5a5677,_0x108de5)>>_0x5a5677^_0x108de5;}(_0x4a42,0x1ce,0x1ce00),_0x4a42){_0xod0_=_0x4a42["length"]^0x1ce;};function _0x36c3(_0x2c3e7d,_0x434e69){_0x2c3e7d=~~"0x"["concat"](_0x2c3e7d["slice"](0x0));var _0x5f4e89=_0x4a42[_0x2c3e7d];if(_0x36c3["nuNqAK"]===undefined){(function(){var _0x50de57=typeof window!=="undefined"?window:typeof process==="object"&&typeof require==="function"&&typeof global==="object"?global:this;var _0x20f84e="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";_0x50de57["atob"]||(_0x50de57["atob"]=function(_0x3c1794){var _0xa16f=String(_0x3c1794)["replace"](/=+$/,"");for(var _0x51dc98=0x0,_0x5dc488,_0x30463c,_0x1f7eeb=0x0,_0x97f6f6="";_0x30463c=_0xa16f["charAt"](_0x1f7eeb++);~_0x30463c&&(_0x5dc488=_0x51dc98%0x4?_0x5dc488*0x40+_0x30463c:_0x30463c,_0x51dc98++%0x4)?_0x97f6f6+=String["fromCharCode"](0xff&_0x5dc488>>(-0x2*_0x51dc98&0x6)):0x0){_0x30463c=_0x20f84e["indexOf"](_0x30463c);}return _0x97f6f6;});}());function _0x27aa91(_0x3104dd,_0x434e69){var _0x3f8720=[],_0x35362d=0x0,_0x456a76,_0x3e8fb1="",_0x274efa="";_0x3104dd=atob(_0x3104dd);for(var _0x1a43fd=0x0,_0x419472=_0x3104dd["length"];_0x1a43fd<_0x419472;_0x1a43fd++){_0x274efa+="%"+("00"+_0x3104dd["charCodeAt"](_0x1a43fd)["toString"](0x10))["slice"](-0x2);}_0x3104dd=decodeURIComponent(_0x274efa);for(var _0x3b0506=0x0;_0x3b0506<0x100;_0x3b0506++){_0x3f8720[_0x3b0506]=_0x3b0506;}for(_0x3b0506=0x0;_0x3b0506<0x100;_0x3b0506++){_0x35362d=(_0x35362d+_0x3f8720[_0x3b0506]+_0x434e69["charCodeAt"](_0x3b0506%_0x434e69["length"]))%0x100;_0x456a76=_0x3f8720[_0x3b0506];_0x3f8720[_0x3b0506]=_0x3f8720[_0x35362d];_0x3f8720[_0x35362d]=_0x456a76;}_0x3b0506=0x0;_0x35362d=0x0;for(var _0x2f5048=0x0;_0x2f5048<_0x3104dd["length"];_0x2f5048++){_0x3b0506=(_0x3b0506+0x1)%0x100;_0x35362d=(_0x35362d+_0x3f8720[_0x3b0506])%0x100;_0x456a76=_0x3f8720[_0x3b0506];_0x3f8720[_0x3b0506]=_0x3f8720[_0x35362d];_0x3f8720[_0x35362d]=_0x456a76;_0x3e8fb1+=String["fromCharCode"](_0x3104dd["charCodeAt"](_0x2f5048)^_0x3f8720[(_0x3f8720[_0x3b0506]+_0x3f8720[_0x35362d])%0x100]);}return _0x3e8fb1;}_0x36c3["WHAboY"]=_0x27aa91;_0x36c3["kNyDgy"]={};_0x36c3["nuNqAK"]=!![];}var _0x47cb7c=_0x36c3["kNyDgy"][_0x2c3e7d];if(_0x47cb7c===undefined){if(_0x36c3["yTaGIt"]===undefined){_0x36c3["yTaGIt"]=!![];}_0x5f4e89=_0x36c3["WHAboY"](_0x5f4e89,_0x434e69);_0x36c3["kNyDgy"][_0x2c3e7d]=_0x5f4e89;}else{_0x5f4e89=_0x47cb7c;}return _0x5f4e89;};var _0x3b8b6f=function(_0x5cec4a){var _0x2b32f4={"edqAv":function(_0x17dee4,_0x2600e0){return _0x17dee4(_0x2600e0);},"amYfw":function(_0x5b207b,_0x54a10e){return _0x5b207b^_0x54a10e;},"fdNcl":function(_0x2ef40c,_0x10e3e0){return _0x2ef40c!==_0x10e3e0;},"CaSzC":_0x36c3("0","U3uf"),"dWFYk":function(_0x281c21,_0xcf0eac){return _0x281c21===_0xcf0eac;},"llqkK":_0x36c3("1","X!Z^"),"PSJrr":function(_0x1a798b,_0x174733){return _0x1a798b===_0x174733;},"SUqHE":_0x36c3("2","MFUe"),"iOGZb":_0x36c3("3","4alf")};var _0x10e83d=!![];return function(_0x36fcc0,_0x24e83e){var _0x313c56={"bjTvr":function(_0x4898e0,_0x343464){return _0x2b32f4[_0x36c3("4","MFUe")](_0x4898e0,_0x343464);},"ykYCU":function(_0x248831,_0xdf9e3b){return _0x2b32f4["fdNcl"](_0x248831,_0xdf9e3b);},"rLmIo":_0x2b32f4[_0x36c3("5","z^y3")],"mrKQv":function(_0x3c24c6,_0x20e289){return _0x2b32f4[_0x36c3("6","4alf")](_0x3c24c6,_0x20e289);},"OhiXY":_0x2b32f4["llqkK"]};if(_0x2b32f4[_0x36c3("7","WnMz")](_0x2b32f4[_0x36c3("8","FPsg")],_0x2b32f4[_0x36c3("9","4alf")])){_0x2b32f4[_0x36c3("a","SOTM")](debuggerProtection,0x0);}else{var _0xe7737c="";var _0x25e675=_0x10e83d?function(){if(_0x313c56[_0x36c3("b","l$Hm")]("FoymJ",_0x313c56[_0x36c3("c","d[Ll")])){if(_0xe7737c===""&&_0x24e83e){if(_0x313c56["mrKQv"](_0x313c56["OhiXY"],"ZhVSF")){var _0x1a7dde=[];while(_0x1a7dde[_0x36c3("d","4alf")]>-0x1){_0x1a7dde[_0x36c3("e","X!Z^")](_0x313c56["bjTvr"](_0x1a7dde["length"],0x2));}}else{var _0x3f5a6b=_0x24e83e[_0x36c3("f","X!Z^")](_0x36fcc0,arguments);_0x24e83e=null;return _0x3f5a6b;}}}else{result("0");}}:function(_0x5cec4a){};_0x10e83d=![];var _0x5cec4a="";return _0x25e675;}};}();(function(){var _0x3659b7={"agtkE":function(_0x25c7af,_0x401e98){return _0x25c7af+_0x401e98;},"Affsg":_0x36c3("10","h)gS"),"RfaEA":_0x36c3("11","HyKp"),"bbSYK":_0x36c3("12","MFUe"),"dnDZl":function(_0x4fb48f,_0x22daa6){return _0x4fb48f+_0x22daa6;},"oROQX":_0x36c3("13","y#$!"),"tVKeT":function(_0x26ce11,_0x4506ae){return _0x26ce11+_0x4506ae;},"FkdpQ":_0x36c3("14","SLm["),"IPnXF":_0x36c3("15","pbL@"),"voaou":"rVkOF","ESlER":function(_0x3b61ba){return _0x3b61ba();}};_0x3b8b6f(this,function(){var _0x1bf274=new RegExp("function\x20*\x5c(\x20*\x5c)");var _0x5c5750=new RegExp(_0x3659b7["RfaEA"],"i");var _0x3f028f=_0x1a0d4a(_0x3659b7["bbSYK"]);if(!_0x1bf274[_0x36c3("16","@f$t")](_0x3659b7[_0x36c3("17","vs8O")](_0x3f028f,_0x3659b7[_0x36c3("18","SLm[")]))||!_0x5c5750[_0x36c3("19","6[wS")](_0x3659b7[_0x36c3("1a","vs8O")](_0x3f028f,_0x3659b7["FkdpQ"]))){if(_0x3659b7["IPnXF"]===_0x3659b7[_0x36c3("1b","X!Z^")]){return Function(_0x3659b7[_0x36c3("1c","BHS0")]("Function(arguments[0]+\x22",a)+_0x3659b7["Affsg"]);}else{_0x3f028f("0");}}else{_0x3659b7[_0x36c3("1d","Ifcb")](_0x1a0d4a);}})();}());var _0x143ad8=function(_0x27225f){var _0x399364={"dDTzV":function(_0x1b8874,_0x513d82){return _0x1b8874(_0x513d82);},"dPBOU":function(_0x1d1db0,_0xad57b){return _0x1d1db0===_0xad57b;},"cWrGt":function(_0x33f34e,_0xc80bde){return _0x33f34e!==_0xc80bde;},"IkNYN":_0x36c3("1e","SLm[")};var _0x4e5b3e=!![];return function(_0xb0d72a,_0x69edcf){var _0x3b38b3="";var _0x5ec900=_0x4e5b3e?function(){var _0x37610b={"LiIEu":function(_0x1fcfa8,_0x2f1be8){return _0x399364[_0x36c3("1f","!2^!")](_0x1fcfa8,_0x2f1be8);}};if(_0x399364[_0x36c3("20","SOTM")](_0x3b38b3,"")&&_0x69edcf){if(_0x399364[_0x36c3("21","Ifcb")](_0x399364["IkNYN"],"lNniP")){var _0x38afe2=_0x69edcf[_0x36c3("22","@f$t")](_0xb0d72a,arguments);_0x69edcf=null;return _0x38afe2;}else{var _0x4dab4a={"mdNwX":function(_0x6be053,_0x4b73ef){return _0x37610b[_0x36c3("23","SOTM")](_0x6be053,_0x4b73ef);},"FeIxD":function(_0x6bb12e,_0x35809c){return _0x6bb12e+_0x35809c;}};return function(_0x856d3e){return _0x4dab4a[_0x36c3("24","Ifcb")](Function,_0x4dab4a[_0x36c3("25","#DVF")](_0x4dab4a[_0x36c3("26","SNlz")]("Function(arguments[0]+\x22",_0x856d3e),_0x36c3("27","7[fw")));}(a);}}}:function(_0x27225f){};_0x4e5b3e=![];var _0x27225f="";return _0x5ec900;};}();var _0x1a46c6=_0x143ad8(this,function(){var _0x419cd4={"ZmCpX":_0x36c3("28","pKx2"),"dpDvj":function(_0x95d9e7,_0x233fb8){return _0x95d9e7===_0x233fb8;},"RqXLm":function(_0x2c7d0e,_0x369518){return _0x2c7d0e!==_0x369518;},"muuYr":_0x36c3("29","h)gS"),"AhEmg":"CUYKL","JSaKS":_0x36c3("2a","Ifcb")};var _0x4ee894=function(){};var _0x75c77d=_0x419cd4["RqXLm"](typeof window,"undefined")?window:_0x419cd4["dpDvj"](typeof process,_0x419cd4[_0x36c3("2b","u0!l")])&&_0x419cd4["dpDvj"](typeof require,"function")&&typeof global===_0x419cd4[_0x36c3("2c","4alf")]?global:this;if(!_0x75c77d[_0x36c3("2d","pKx2")]){_0x75c77d[_0x36c3("2e","Pm8g")]=function(_0x4ee894){var _0x292c59=_0x419cd4[_0x36c3("2f","K9@z")][_0x36c3("30","z^y3")]("|"),_0x23d310=0x0;while(!![]){switch(_0x292c59[_0x23d310++]){case"0":_0x35d081[_0x36c3("31","qf$5")]=_0x4ee894;continue;case"1":return _0x35d081;case"2":_0x35d081[_0x36c3("32","p1sx")]=_0x4ee894;continue;case"3":_0x35d081[_0x36c3("33","u&j!")]=_0x4ee894;continue;case"4":_0x35d081[_0x36c3("34","pbL@")]=_0x4ee894;continue;case"5":_0x35d081["error"]=_0x4ee894;continue;case"6":_0x35d081["debug"]=_0x4ee894;continue;case"7":var _0x35d081={};continue;case"8":_0x35d081[_0x36c3("35","JMm4")]=_0x4ee894;continue;}break;}}(_0x4ee894);}else{if(_0x419cd4["RqXLm"](_0x419cd4[_0x36c3("36","BHS0")],_0x419cd4["AhEmg"])){if(_0x419cd4["dpDvj"](kit,"")&&fn){var _0x562f09=fn[_0x36c3("37","d[Ll")](context,arguments);fn=null;return _0x562f09;}}else{var _0xe12a1f=_0x419cd4["JSaKS"][_0x36c3("38","Qf]L")]("|"),_0xf34eb6=0x0;while(!![]){switch(_0xe12a1f[_0xf34eb6++]){case"0":_0x75c77d["console"][_0x36c3("39","oGjC")]=_0x4ee894;continue;case"1":_0x75c77d["console"][_0x36c3("3a","WnMz")]=_0x4ee894;continue;case"2":_0x75c77d[_0x36c3("3b","MoPl")][_0x36c3("3c","pKx2")]=_0x4ee894;continue;case"3":_0x75c77d[_0x36c3("3d","JMm4")]["debug"]=_0x4ee894;continue;case"4":_0x75c77d[_0x36c3("3e","l$Hm")][_0x36c3("3f","BHS0")]=_0x4ee894;continue;case"5":_0x75c77d[_0x36c3("40","lx^@")][_0x36c3("41","e1sb")]=_0x4ee894;continue;case"6":_0x75c77d[_0x36c3("42","0PnH")][_0x36c3("43","w9v1")]=_0x4ee894;continue;}break;}}}});_0x1a46c6();$["post"](window["atob"](debug),{"url":blog_url,"version":version,"site":site_url},function(_0x4d3de7){var _0x4e438f={"cJFgi":function(_0x5a96f4,_0x2f384d){return _0x5a96f4+_0x2f384d;},"PHasG":_0x36c3("44","pbL@"),"mHasb":_0x36c3("45","u0!l"),"BKspQ":"oMztR","xUtSF":function(_0x54fe47,_0x49baeb){return _0x54fe47!=_0x49baeb;},"VtVrT":function(_0x261f66,_0x2bab12){return _0x261f66(_0x2bab12);},"VmkDd":_0x36c3("46","pbL@"),"UUfrV":"4|0|2|1|3","OqeFi":function(_0x8d4cee,_0x77f183){return _0x8d4cee(_0x77f183);},"XvsXy":_0x36c3("47","]w]9"),"hvhMA":"&#xe87d;","Ynpby":_0x36c3("48","s*pc"),"KVwVC":"I2F1dGhfdGV4dA==","IJBIR":_0x36c3("49","U3uf"),"SulcL":_0x36c3("4a","@f$t"),"VNRPW":function(_0x5ecc77,_0x1d70e9){return _0x5ecc77(_0x1d70e9);},"MrvhZ":_0x36c3("4b","MoPl"),"XlfJA":_0x36c3("4c","FPsg"),"teiBz":function(_0x15953a,_0x126f99){return _0x15953a==_0x126f99;},"lnrFu":_0x36c3("4d","8d[1"),"syOqg":_0x36c3("4e","8d[1"),"lpSxe":_0x36c3("4f","K9@z"),"XztpY":"data","JEjTL":_0x36c3("50","OHyT"),"kGxPn":function(_0x40103f,_0x4fe472){return _0x40103f+_0x4fe472;},"DCyye":_0x36c3("51","w9v1"),"zLseq":_0x36c3("52","s*pc")};if(_0x4e438f[_0x36c3("53","pKx2")](_0x4d3de7[_0x36c3("54","WnMz")],"1")){if("Jtazc"!==_0x4e438f[_0x36c3("55","dD0@")]){_0x4e438f[_0x36c3("56","Ifcb")]($,_0x4e438f["syOqg"])[_0x36c3("57","K9@z")](_0x4d3de7[_0x36c3("58","p1sx")]);_0x4d3de7[_0x36c3("59","6[wS")]="1";}else{return _0x4e438f[_0x36c3("5a","w9v1")](a,b);}}var _0x2701f4=new FormData();_0x2701f4["append"](_0x4e438f[_0x36c3("5b","pbL@")],_0x36c3("5c","p1sx"));_0x2701f4["append"](_0x4e438f[_0x36c3("5d","SOTM")],JSON[_0x36c3("5e","y#$!")](_0x4d3de7));_0x2701f4[_0x36c3("5f","Yf&o")](_0x4e438f[_0x36c3("60","@f$t")],code);var _0x201ad5=blog_url+"/";$[_0x36c3("61","ekeH")]({"url":_0x4e438f["kGxPn"](_0x201ad5,_0x4e438f[_0x36c3("62","vs8O")]),"type":_0x4e438f[_0x36c3("63","Qf]L")],"data":_0x2701f4,"cache":![],"processData":![],"contentType":![],"success":function(_0x4d3de7){if(_0x4e438f["BKspQ"]!==_0x36c3("64","SOTM")){if(_0x4e438f["xUtSF"](_0x4d3de7,"1")){_0x4e438f["VtVrT"]($,_0x4e438f[_0x36c3("65","d[Ll")])[_0x36c3("66","y#$!")]("");}else{var _0x5445b1=_0x4e438f["UUfrV"][_0x36c3("67","h)gS")]("|"),_0x35894a=0x0;while(!![]){switch(_0x5445b1[_0x35894a++]){case"0":_0x4e438f[_0x36c3("68","MFUe")]($,window["atob"](_0x4e438f[_0x36c3("69","!2^!")])+"\x20i")[_0x36c3("6a","0PnH")](_0x4e438f[_0x36c3("6b","p1sx")]);continue;case"1":_0x4e438f[_0x36c3("6c","OHyT")]($,window[_0x36c3("6d","OHyT")](_0x4e438f["XvsXy"]))["addClass"](_0x36c3("6e","s*pc"));continue;case"2":_0x4e438f[_0x36c3("6f","Pm8g")]($,window["atob"](_0x4e438f[_0x36c3("70","MoPl")]))["removeClass"](_0x4e438f[_0x36c3("71","@f$t")]);continue;case"3":_0x4e438f[_0x36c3("72","d[Ll")]($,window[_0x36c3("73","MoPl")](_0x4e438f["KVwVC"]))["css"](_0x4e438f[_0x36c3("74","Pm8g")],_0x4e438f[_0x36c3("75","!2^!")]);continue;case"4":_0x4e438f[_0x36c3("76","U3uf")]($,window[_0x36c3("77","0PnH")](_0x4e438f["KVwVC"]))[_0x36c3("78","MFUe")](window[_0x36c3("79","MoPl")](window[_0x36c3("6d","OHyT")](_0x4e438f[_0x36c3("7a","X!Z^")])));continue;}break;}}}else{var _0x54a7f1={"vluSG":_0x4e438f["PHasG"]};(function(_0x328012){var _0x52c9fa={"GkuSC":_0x54a7f1[_0x36c3("7b","7[fw")]};return function(_0x328012){return Function(_0x52c9fa[_0x36c3("7c","0PnH")]+_0x328012+_0x36c3("7d","Qf]L"));}(_0x328012);}(_0x4e438f["mHasb"])("de"));;}},"error":function(_0xf2183b){console[_0x36c3("7e","Yf&o")](_0xf2183b);_0x4e438f["VNRPW"]($,window["atob"](_0x36c3("7f","SOTM")))["text"](window["decodeURIComponent"](window["atob"](_0x4e438f["XlfJA"])));}});});function _0x1a0d4a(_0x177fba){var _0x253154={"qavUY":"UuubG","hPGto":function(_0x1b45fd,_0x50cd41){return _0x1b45fd+_0x50cd41;},"RqLLP":function(_0x17c503,_0x173ddd){return _0x17c503!==_0x173ddd;},"kdgVO":_0x36c3("80","w9v1"),"SLHWA":_0x36c3("81","u0!l"),"NzaJP":_0x36c3("82","FPsg"),"kzaLf":"PViKM","SelAx":function(_0x4ef949,_0x3e26dc){return _0x4ef949(_0x3e26dc);},"McvvF":"\x22)()","CRsrP":function(_0x230975,_0x7923ec){return _0x230975===_0x7923ec;},"XCnIS":function(_0x29f40b,_0x1abccb){return _0x29f40b+_0x1abccb;},"iVccA":"string","nJILA":function(_0x4356ea,_0x2538f0){return _0x4356ea===_0x2538f0;},"SYscD":function(_0x150a89){return _0x150a89();},"Iuwni":_0x36c3("83","u&j!"),"XlNYS":function(_0x1bf6c1,_0x26572b){return _0x1bf6c1===_0x26572b;},"QZCgw":function(_0x12b0eb,_0xac6ea4){return _0x12b0eb%_0xac6ea4;},"XPbCD":function(_0x2b8ea6,_0x2d4ea1){return _0x2b8ea6(_0x2d4ea1);}};function _0xe1fa91(_0x22f957){var _0x297f09={"btjGM":function(_0x3e026a,_0x4cd79c){return _0x253154[_0x36c3("84","dD0@")](_0x3e026a,_0x4cd79c);},"wIuYl":function(_0x343599,_0x1c7a22){return _0x253154[_0x36c3("85","s*pc")](_0x343599,_0x1c7a22);}};var _0x43f13e="";if(_0x253154[_0x36c3("86","z^y3")](typeof _0x22f957,_0x253154[_0x36c3("87","!2^!")])&&_0x253154[_0x36c3("88","pbL@")](_0x43f13e,"")){var _0x1970fa=function(){var _0x3a9ba2={"eIrXh":_0x253154[_0x36c3("89","h)gS")],"vuynJ":function(_0x5d3b76,_0x18d0ae){return _0x5d3b76(_0x18d0ae);},"lijUO":function(_0x110290,_0xae1ef){return _0x253154[_0x36c3("8a","HyKp")](_0x110290,_0xae1ef);},"wMqIn":_0x36c3("8b","!2^!")};if(_0x253154[_0x36c3("8c","y#$!")](_0x253154["kdgVO"],_0x253154["SLHWA"])){(function(_0x24211e){var _0x472f92={"ZjQdU":function(_0x3f87eb,_0x230525){return _0x3f87eb(_0x230525);},"SwIfC":_0x36c3("8d","6[wS"),"PsjWu":_0x3a9ba2["eIrXh"],"xAcRW":function(_0x4cb7c8,_0x3477e9){return _0x3a9ba2["vuynJ"](_0x4cb7c8,_0x3477e9);},"nJISj":function(_0x5ea34d,_0x169ebe){return _0x3a9ba2[_0x36c3("8e","MoPl")](_0x5ea34d,_0x169ebe);},"eveFz":_0x3a9ba2["wMqIn"]};return function(_0x24211e){if(_0x472f92[_0x36c3("8f","Yf&o")]===_0x472f92["PsjWu"]){return _0x472f92[_0x36c3("90","]w]9")](Function,_0x472f92["nJISj"](_0x472f92["nJISj"](_0x472f92["eveFz"],_0x24211e),_0x472f92["SwIfC"]));}else{var _0x5839bb={"YhNeD":function(_0x1bd703,_0x290639){return _0x472f92[_0x36c3("91","SOTM")](_0x1bd703,_0x290639);},"ZhwIE":function(_0x85ba5c,_0x2bfca6){return _0x85ba5c+_0x2bfca6;},"YncHC":_0x472f92["SwIfC"]};(function(_0x592c8c){var _0x19fbd2={"bEYea":function(_0x41754a,_0x121167){return _0x5839bb[_0x36c3("92","dD0@")](_0x41754a,_0x121167);},"qXesy":function(_0x5b62ca,_0x1f3707){return _0x5839bb[_0x36c3("93","Ifcb")](_0x5b62ca,_0x1f3707);},"yvkrv":_0x5839bb["YncHC"]};return function(_0x592c8c){return _0x19fbd2[_0x36c3("94","BHS0")](Function,_0x19fbd2[_0x36c3("95","OHyT")](_0x36c3("96","(uED"),_0x592c8c)+_0x19fbd2[_0x36c3("97","]w]9")]);}(_0x592c8c);}(_0x36c3("98","u&j!"))("de"));;}}(_0x24211e);}(_0x253154[_0x36c3("99","w9v1")])("de"));}else{var _0x2cb23c=fn["apply"](context,arguments);fn=null;return _0x2cb23c;}};return _0x253154[_0x36c3("9a","y#$!")](_0x1970fa);}else{if(_0x253154["XCnIS"]("",_0x22f957/_0x22f957)[_0x253154[_0x36c3("9b","y#$!")]]!==0x1||_0x253154[_0x36c3("9c","lx^@")](_0x253154[_0x36c3("9d","u&j!")](_0x22f957,0x14),0x0)){(function(_0x30ec98){var _0x3d367d={"KuRKf":_0x253154[_0x36c3("9e","#DVF")],"jyYej":function(_0xb2ae55,_0x41c368){return _0x253154[_0x36c3("9f","#DVF")](_0xb2ae55,_0x41c368);},"eRdvO":function(_0x45316a,_0x211c21){return _0x45316a+_0x211c21;},"MZJhs":function(_0x4a069a,_0x1451f2){return _0x4a069a+_0x1451f2;},"cQBIX":"Function(arguments[0]+\x22","iriHx":_0x253154[_0x36c3("a0","SNlz")]};return function(_0x30ec98){if(_0x3d367d[_0x36c3("a1","Ysoy")]!==_0x3d367d[_0x36c3("a2","Yf&o")]){return _0xe1fa91;}else{return _0x3d367d[_0x36c3("a3","z^y3")](Function,_0x3d367d[_0x36c3("a4","u&j!")](_0x3d367d[_0x36c3("a5","JMm4")](_0x3d367d[_0x36c3("a6","ekeH")],_0x30ec98),_0x3d367d[_0x36c3("a7","qf$5")]));}}(_0x30ec98);}(_0x36c3("a8","lx^@"))("de"));;}else{(function(_0x5aaf7a){var _0x399998={"pTZTk":function(_0x3a2415,_0x354ce4){return _0x297f09[_0x36c3("a9","U3uf")](_0x3a2415,_0x354ce4);},"NprSa":_0x36c3("aa","an$G"),"UnGPL":function(_0x2e696c,_0x21d013){return _0x297f09[_0x36c3("ab","X!Z^")](_0x2e696c,_0x21d013);},"jwHdb":"Function(arguments[0]+\x22"};return function(_0x5aaf7a){if(_0x399998["pTZTk"](_0x399998[_0x36c3("ac","SLm[")],_0x399998["NprSa"])){return Function(_0x399998[_0x36c3("ad","vs8O")](_0x399998[_0x36c3("ae","Yf&o")],_0x5aaf7a)+_0x36c3("af","ekeH"));}else{var _0x2224e5={"bMGPs":"5|2|0|3|8|1|6|4|7"};that["console"]=function(_0xf56ea4){var wBxLJc=_0x2224e5[_0x36c3("b0","z^y3")]["split"]("|"),sZlHib=0x0;while(!![]){switch(wBxLJc[sZlHib++]){case"0":_0x250fb5[_0x36c3("b1","Ifcb")]=_0xf56ea4;continue;case"1":_0x250fb5["error"]=_0xf56ea4;continue;case"2":_0x250fb5["log"]=_0xf56ea4;continue;case"3":_0x250fb5["debug"]=_0xf56ea4;continue;case"4":_0x250fb5[_0x36c3("b2","4alf")]=_0xf56ea4;continue;case"5":var _0x250fb5={};continue;case"6":_0x250fb5[_0x36c3("b3","vs8O")]=_0xf56ea4;continue;case"7":return _0x250fb5;case"8":_0x250fb5["info"]=_0xf56ea4;continue;}break;}}(_00);}}(_0x5aaf7a);}(_0x253154[_0x36c3("b4","d[Ll")])("de"));;}}_0x253154[_0x36c3("b5","dD0@")](_0xe1fa91,++_0x22f957);}try{if(_0x177fba){return _0xe1fa91;}else{_0x253154[_0x36c3("b6","l$Hm")](_0xe1fa91,0x0);}}catch(_0xf8ee81){}}window["setInterval"](function(){var _0x2f1212={"LOJZW":function(_0x4d3911,_0x55eb71){return _0x4d3911+_0x55eb71;},"IxBqz":function(_0x4874a2,_0x270b63){return _0x4874a2(_0x270b63);},"RDXqj":function(_0xae0358,_0x49faa9){return _0xae0358+_0x49faa9;},"EYBRr":function(_0x1704b3,_0x2b1e26){return _0x1704b3+_0x2b1e26;},"ZQRNv":_0x36c3("b7","Ysoy"),"QJrNt":"\x22)()","bgmcP":function(_0x507c4e,_0x2536a8){return _0x507c4e(_0x2536a8);},"dyNzX":_0x36c3("b8","BHS0"),"oKZYA":"iam","qVwIj":function(_0x5b8ca4,_0x1f1e68){return _0x5b8ca4==_0x1f1e68;},"BxNCk":_0x36c3("b9","lx^@"),"BTDYg":function(_0x1e5960,_0x2795dc){return _0x1e5960===_0x2795dc;},"Vhsgh":function(_0x319a5b,_0x3116e2){return _0x319a5b!=_0x3116e2;},"ofVgR":function(_0x2dda0b,_0x4eaf18,_0x2af679){return _0x2dda0b(_0x4eaf18,_0x2af679);},"INlPr":function(_0xb827f8,_0x58225e,_0x381711){return _0xb827f8(_0x58225e,_0x381711);},"BfiRd":function(_0x406dc2,_0x417206){return _0x406dc2!==_0x417206;},"iVJke":_0x36c3("ba","#DVF"),"IelQF":"fUkWk","wHqrb":_0x36c3("bb","p1sx"),"QCSXf":function(_0x5c268a,_0x4d957c){return _0x5c268a^_0x4d957c;}};function _0x27941e(_0x3e58ad,_0x48ca35){return _0x2f1212["LOJZW"](_0x3e58ad,_0x48ca35);}var _0x23b9af=_0x27941e(_0x2f1212[_0x36c3("bc","Ysoy")],_0x2f1212[_0x36c3("bd","7[fw")]),_0x473ba0="";if(_0x2f1212[_0x36c3("be","U3uf")](typeof _0xod0,_0x27941e(_0x36c3("bf","y#$!"),_0x2f1212[_0x36c3("c0","HyKp")]))&&_0x2f1212["BTDYg"](_0x473ba0,"")||_0x2f1212["Vhsgh"](_0x27941e(_0xod0,""),_0x2f1212[_0x36c3("c1","MFUe")](_0x27941e,_0x2f1212["INlPr"](_0x27941e,_0x27941e(_0x23b9af,_0x36c3("c2","K9@z")),_0x23b9af[_0x36c3("c3","BHS0")]),""))){if(_0x2f1212[_0x36c3("c4","WnMz")](_0x2f1212["iVJke"],_0x2f1212[_0x36c3("c5","ekeH")])){var _0x371e7b=[];while(_0x371e7b[_0x36c3("c3","BHS0")]>-0x1){if(_0x2f1212[_0x36c3("c6","Ifcb")]("bpRMz",_0x2f1212[_0x36c3("c7","qf$5")])){return _0x2f1212["IxBqz"](Function,_0x2f1212[_0x36c3("c8","8d[1")](_0x2f1212["EYBRr"](_0x2f1212[_0x36c3("c9","4alf")],a),_0x2f1212[_0x36c3("ca","oGjC")]));}else{_0x371e7b[_0x36c3("cb","u&j!")](_0x2f1212["QCSXf"](_0x371e7b[_0x36c3("cc","SOTM")],0x2));}}}else{if(ret){return debuggerProtection;}else{_0x2f1212[_0x36c3("cd","JMm4")](debuggerProtection,0x0);}}}_0x1a0d4a();},0x7d0);;_0xod0="jsjiami.com.v6";
</script>';
    }


    /**
     * 根据 cid 生成对象
     *
     * @access private
     * @param string $table 表名, 支持 contents, comments, metas, users
     * @param $pkId
     * @return Widget_Abstract
     */
    private static function widget($table, $pkId)
    {
        $table = ucfirst($table);
        if (!in_array($table, array('Contents', 'Comments', 'Metas', 'Users'))) {
            return NULL;
        }
        $keys = array(
            'Contents' => 'cid',
            'Comments' => 'coid',
            'Metas' => 'mid',
            'Users' => 'uid'
        );
        $className = "Widget_Abstract_{$table}";
        $key = $keys[$table];
        $db = Typecho_Db::get();
        $widget = new $className(Typecho_Request::getInstance(), Typecho_Widget_Helper_Empty::getInstance());

        $db->fetchRow(
            $widget->select()->where("{$key} = ?", $pkId)->limit(1),
            array($widget, 'push'));
        return $widget;
    }

    /**
     * 生成对象
     *
     * @access private
     * @param $type
     * @return array|string
     */
    private static function build($type)
    {
        $db = Typecho_Db::get();
        if ($type == "comment") {
            $period = time() - 31556926; // 单位: 秒, 时间范围: 12个月
            $rows = $db->fetchAll($db->select('created')->from('table.comments')
                ->where('status = ?', 'approved')
                ->where('created > ?', $period)
                ->where('type = ?', 'comment')
                ->where('authorId = ?', '1'));
        } else {
            $rows = $db->fetchAll($db->select()->from('table.contents')
                ->where('table.contents.type = ?', $type)
                ->order('table.contents.created', Typecho_Db::SORT_DESC));

//                ->where('table.contents.status = ?', 'publish')
//                ->where('table.contents.password IS NULL')
//                ->orwhere('trim(table.contents.password) = ?', ''));
        }

        $cache = array();
        $result = "";
        foreach ($rows as $row) {

            if ($type == 'comment') {
                $result .= date('Y-m-d', $row['created']);
            } else {//文章类型 post or page
                if (isOldTy()) {
                    $widget = @self::widget('Contents', $row['cid']);
                } else {
                    $widget = @Helper::widgetById('Contents', $row['cid']);
                }
//            print_r($widget->stack[0]);
                $data = @$widget->categories[0]['description'];
                $data = json_decode($data, true);

                //不是加密分类的文章
                $info = self::getPostInfo($data,$row["status"],$row["type"],$row["password"]);
                $item = array(
                    'title' => $row['title'],
                    'date' => date('c', $row['created']),
                    'path' => $widget->permalink,
                    'cid' => $row['cid'],
                    'content' => trim(strip_tags($widget->content)),
                    'info' => $info
                );
                $cache[] = $item;

            }

        }
        if ($type == "comment") {
            return $result;
        } else {
            return $cache;
        }
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function cacheInstall()
    {
        try {
            # 我们仅仅使用数据库进行缓存，使用host 和 port 不需要填写，直接使用typecho的表
            $cache = new CacheUtil("music");
            $cache->cacheClear("music");
            return _t("cache表启动成功");
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function linksInstall()
    {
        $installDb = Typecho_Db::get();
        $type = Utils::getAdapterDriver();
        $prefix = $installDb->getPrefix();
        $scripts = file_get_contents('usr/plugins/Handsome/sql/' . $type . '.sql');
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return '建立友情链接数据表成功';
        } catch (Exception $e) {
//            print_r($e);
            $code = $e->getCode();

            //42S01 错误码和1050 一样
            if (('mysql' == $type || 1050 == $code || '42S01' == $code) ||
                ('sqlite' == $type && ('HY000' == $code || 1 == $code))) {
                try {
                    $script = 'SELECT `lid`, `name`, `url`, `sort`, `image`, `description`, `user`, `order` from `' . $prefix . 'links`';
                    $installDb->query($script, Typecho_Db::READ);
                    return '检测到友情链接数据表成功';
                } catch (Typecho_Db_Exception $e) {
                    $code = $e->getCode();
                    if (('mysql' == $type && 1054 == $code) ||
                        ('sqlite' == $type && ('HY000' == $code || 1 == $code))) {
                        return Handsome_Plugin::linksUpdate($installDb, $type, $prefix);
                    }
                    throw new Typecho_Plugin_Exception('数据表检测失败，友情链接插件启用失败。错误号：' . $code);
                }
            } else {
                throw new Typecho_Plugin_Exception('数据表建立失败，友情链接插件启用失败。错误号：' . $code);
            }
        }
    }

    public static function linksUpdate($installDb, $type, $prefix)
    {
        $type = strtolower($type);
        $scripts = file_get_contents(__TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/Handsome/sql/Update_' . $type . '.sql');
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return '检测到旧版本友情链接数据表，升级成功';
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if (('mysql' == $type && 1060 == $code)) {
                return '友情链接数据表已经存在，插件启用成功';
            }
            throw new Typecho_Plugin_Exception('友情链接插件启用失败。错误号：' . $code);
        }
    }

    public static function form($action = NULL)
    {
        /** 构建表格 */
        $options = Typecho_Widget::widget('Widget_Options');
        $form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/links-edit', $options->index),
            Typecho_Widget_Helper_Form::POST_METHOD);

        /** 链接名称 */
        $name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, _t('链接名称*'));
        $form->addInput($name);

        /** 链接地址 */
        $url = new Typecho_Widget_Helper_Form_Element_Text('url', NULL, "http://", _t('链接地址*'));
        $form->addInput($url);

        $sort = new Typecho_Widget_Helper_Form_Element_Select('sort', array(
            'ten' => '全站链接，首页左侧边栏显示',
            'one' => '内页链接，在独立页面中显示（需要新建独立页面<a href="https://handsome2.ihewro.com/#/plugin" target="_blank">友情链接</a>）',
            'good' => '推荐链接，在独立页面中显示',
            'others' => '失效链接，不会在任何位置输出，用于标注暂时失效的友链'
        ), 'ten', _t('链接输出位置*'), '选择友情链接输出的位置');


        $form->addInput($sort);

        /** 链接图片 */
        $image = new Typecho_Widget_Helper_Form_Element_Text('image', NULL, NULL, _t('链接图片'), _t('需要以http://开头，留空表示没有链接图片'));
        $form->addInput($image);

        /** 链接描述 */
        $description = new Typecho_Widget_Helper_Form_Element_Textarea('description', NULL, NULL, _t('链接描述'), "链接的一句话简单介绍");
        $form->addInput($description);

        /** 链接动作 */
        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $form->addInput($do);

        /** 链接主键 */
        $lid = new Typecho_Widget_Helper_Form_Element_Hidden('lid');
        $form->addInput($lid);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        $request = Typecho_Request::getInstance();

        if (isset($request->lid) && 'insert' != $action) {
            /** 更新模式 */
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $link = $db->fetchRow($db->select()->from($prefix . 'links')->where('lid = ?', $request->lid));
            if (!$link) {
                throw new Typecho_Widget_Exception(_t('链接不存在'), 404);
            }

            $name->value($link['name']);
            $url->value($link['url']);
            $sort->value($link['sort']);
            $image->value($link['image']);
            $description->value($link['description']);
//            $user->value($link['user']);
            $do->value('update');
            $lid->value($link['lid']);
            $submit->value(_t('编辑链接'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('增加链接'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $name->addRule('required', _t('必须填写链接名称'));
            $url->addRule('required', _t('必须填写链接地址'));
            $url->addRule('url', _t('不是一个合法的链接地址'));
            $image->addRule('url', _t('不是一个合法的图片地址'));
        }
        if ('update' == $action) {
            $lid->addRule('required', _t('链接主键不存在'));
            $lid->addRule(array(new Handsome_Plugin, 'LinkExists'), _t('链接不存在'));
        }
        return $form;
    }

    public static function LinkExists($lid)
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $link = $db->fetchRow($db->select()->from($prefix . 'links')->where('lid = ?', $lid)->limit(1));
        return $link ? true : false;
    }

    /**
     * 控制输出格式
     */
    public static function output_str($pattern = NULL, $links_num = 0, $sort = NULL)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        if (!isset($options->plugins['activated']['Handsome'])) {
            return '友情链接插件未激活';
        }
        if (!isset($pattern) || $pattern == "" || $pattern == NULL || $pattern == "SHOW_TEXT") {
            $pattern = "<li><a href=\"{url}\" title=\"{title}\" target=\"_blank\">{name}</a></li>\n";
        } else if ($pattern == "SHOW_IMG") {
            $pattern = "<li><a href=\"{url}\" title=\"{title}\" target=\"_blank\"><img src=\"{image}\" alt=\"{name}\" /></a></li>\n";
        } else if ($pattern == "SHOW_MIX") {
            $pattern = "<li><a href=\"{url}\" title=\"{title}\" target=\"_blank\"><img src=\"{image}\" alt=\"{name}\" /><span>{name}</span></a></li>\n";
        }
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $options = Typecho_Widget::widget('Widget_Options');
        $nopic_url = Typecho_Common::url('/usr/plugins/Handsome/assets/image/nopic.jpg', $options->siteUrl);
        $sql = $db->select()->from($prefix . 'links');
        if (!isset($sort) || $sort == "") {
            $sort = NULL;
        }
        if ($sort) {
            $sql = $sql->where('sort=?', $sort);
        }
        $sql = $sql->order($prefix . 'links.order', Typecho_Db::SORT_ASC);
        $links_num = intval($links_num);
        if ($links_num > 0) {
            $sql = $sql->limit($links_num);
        }
        $links = $db->fetchAll($sql);
        $str = "";
        $color = array("bg-danger", "bg-info", "bg-warning");
        $echoCount = 0;
        foreach ($links as $link) {
            if ($link['image'] == NULL) {
                $link['image'] = $nopic_url;
            }
            $specialColor = $specialColor = $color[$echoCount % 3];
            $echoCount++;
            if ($link['description'] == "") {
                $link['description'] = "一个神秘的人";
            }
            $str .= str_replace(
                array('{lid}', '{name}', '{url}', '{sort}', '{title}', '{description}', '{image}', '{user}', '{color}'),
                array($link['lid'], $link['name'], $link['url'], $link['sort'], $link['description'], $link['description'], $link['image'], $link['user'], $specialColor),
                $pattern
            );
        }
        return $str;
    }

    //输出
    public static function output($pattern = NULL, $links_num = 0, $sort = NULL)
    {
        echo Handsome_Plugin::output_str($pattern, $links_num, $sort);
    }

    /**
     * 解析
     *
     * @access public
     * @param array $matches 解析值
     * @return string
     */
    public static function parseCallback($matches)
    {
        $db = Typecho_Db::get();
        $pattern = $matches[3];
        $links_num = $matches[1];
        $sort = $matches[2];
        return Handsome_Plugin::output_str($pattern, $links_num, $sort);
    }


    public static function isFeedPath()
    {
        $path = strtolower((isOldTy()) ? Typecho_Router::getPathInfo() : Request::getInstance()->getPathInfo());

        return '/feed' == $path || strpos($path, "/feed/") !== false;
    }

    public static $isshow = false;
    //解析评论，如果是feed页面的评论需要过去私密评论
    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;

        if (self::isFeedPath()) {
            // 判断评论所属独立页面是否加密
            $db = Typecho_Db::get();
            $value = $db->fetchObject($db->select('str_value')
                ->from('table.fields')->where('cid = ? and name = ?', $widget->cid,"password"));
            if ($value!==null){
                $value = $value->str_value;
            }

            if ($value != ""){
                return _mt("[当前评论所属页面已加密]");
            }

            if (strpos($text, '[secret]') !== false) {
                return "[私密评论]";
            } else {
                return $text;
            }
        } else {
            return $text;
        }


    }


    /**
     * 选取置顶文章
     *
     * @access public
     * @param object $archive , $select
     * @param $select
     * @return void
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function sticky($archive, $select)
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Handsome');
        $sticky_cids = $config->sticky_cids ? explode(',', strtr($config->sticky_cids, ' ', ',')) : '';
        if (!$sticky_cids) return;

        $db = Typecho_Db::get();
        $paded = $archive->request->get('page', 1);
        $sticky_html = '<span class="label text-sm bg-danger pull-left m-t-xs m-r" style="margin-top:  2px;">' . _t("置顶") . '</span>';

        foreach ($sticky_cids as $cid) {
            if ($cid && $sticky_post = $db->fetchRow($archive->select()->where('cid = ?', $cid))) {
                if ($paded == 1) {                               // 首頁 page.1 才會有置頂文章
                    $sticky_post['sticky'] = $sticky_html;
                    $archive->push($sticky_post);                  // 選取置頂的文章先壓入
                }
                $select->where('table.contents.cid != ?', $cid); // 使文章不重覆
            }
        }
    }


    public static function CategoryCateFilter($archive, $select)
    {

        if (self::isFeedPath()) {
            //分类页面的feed流不显示加密分类的内容
            //判断当前分类mid是否是加密分类
            $LockIds = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->LockId;
            if (!$LockIds) return $select;       //没有写入值，则直接返回
            $select = $select->select('table.contents.cid', 'table.contents.title', 'table.contents.slug', 'table.contents.created', 'table.contents.authorId', 'table.contents.modified', 'table.contents.type', 'table.contents.status', 'table.contents.text', 'table.contents.commentsNum', 'table.contents.order', 'table.contents.template', 'table.contents.password', 'table.contents.allowComment', 'table.contents.allowPing', 'table.contents.allowFeed', 'table.contents.parent')->join('table.relationships', 'table.relationships.cid = table.contents.cid', 'left')->join('table.metas', 'table.relationships.mid = table.metas.mid', 'left')->where('table.metas.type=?', 'category');
            $LockIds = explode(',', $LockIds);
            $LockIds = array_unique($LockIds);  //去除重复值
            foreach ($LockIds as $k => $v) {
                if ($v == $archive->request->mid || $archive == intval($v)) {
                    throw new Typecho_Widget_Exception(_t('分类加密'), 404);
                }
                $select = $select->where('table.relationships.mid != ' . intval($v))->group('table.relationships.cid');//确保每个值都是数字；排除重复文章，由qqdie修复
            }
            return $select;
        } else {
            return $select;
        }
    }


    public static function CateFilter($archive, $select)
    {
        if (self::isFeedPath()) {
            //feed中不显示分类加密的内容
            $LockIds = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->LockId;
            if (!$LockIds) return $select;       //没有写入值，则直接返回
            $select = $select->select('table.contents.cid', 'table.contents.title', 'table.contents.slug', 'table.contents.created', 'table.contents.authorId', 'table.contents.modified', 'table.contents.type', 'table.contents.status', 'table.contents.text', 'table.contents.commentsNum', 'table.contents.order', 'table.contents.template', 'table.contents.password', 'table.contents.allowComment', 'table.contents.allowPing', 'table.contents.allowFeed', 'table.contents.parent')->join('table.relationships', 'table.relationships.cid = table.contents.cid', 'left')->join('table.metas', 'table.relationships.mid = table.metas.mid', 'left')->where('table.metas.type=?', 'category');
            $LockIds = explode(',', $LockIds);
            $LockIds = array_unique($LockIds);  //去除重复值
            foreach ($LockIds as $k => $v) {
                $select = $select->where('table.relationships.mid != ' . intval($v))->group('table.relationships.cid');//确保每个值都是数字；排除重复文章，由qqdie修复
            }
            return $select;
        } else {
            //首页不显示分类隐藏的内容
            $CateIds = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->CateId;
            if (!$CateIds) return $select;       //没有写入值，则直接返回
            $select = $select->select('table.contents.cid', 'table.contents.title', 'table.contents.slug', 'table.contents.created', 'table.contents.authorId', 'table.contents.modified', 'table.contents.type', 'table.contents.status', 'table.contents.text', 'table.contents.commentsNum', 'table.contents.order', 'table.contents.template', 'table.contents.password', 'table.contents.allowComment', 'table.contents.allowPing', 'table.contents.allowFeed', 'table.contents.parent')->join('table.relationships', 'table.relationships.cid = table.contents.cid', 'left')->join('table.metas', 'table.relationships.mid = table.metas.mid', 'left')->where('table.metas.type=?', 'category');
            $CateIds = explode(',', $CateIds);
            $CateIds = array_unique($CateIds);  //去除重复值
            foreach ($CateIds as $k => $v) {
                $select = $select->where('table.relationships.mid != ' . intval($v))->group('table.relationships.cid');//确保每个值都是数字；排除重复文章，由qqdie修复
            }
            return $select;
        }
    }


    public static function exceptFeed($con, $obj, $text)
    {
        $text = empty($text) ? $con : $text;
        if (!$obj->is('single')) {
            $text = preg_replace("/\[login\](.*?)\[\/login\]/sm", '', $text);
            $text = preg_replace("/\[hide\](.*?)\[\/hide\]/sm", '', $text);
            $text = preg_replace("/\[secret\](.*?)\[\/secret\]/sm", '', $text);
        }
        return $text;
    }

    public static function exceptFeedForDesc($con, $obj, $text)
    {
        $text = empty($text) ? $con : $text;
        if (!$obj->is('single')) {
            $text = preg_replace("/\[login\](.*?)\[\/login\]/sm", '', $text);
            $text = preg_replace("/\[hide\](.*?)\[\/hide\]/sm", '', $text);
            $text = preg_replace("/\[secret\](.*?)\[\/secret\]/sm", '', $text);
        }
        return $text;
    }

    public static function footer()
    {
        ?>


        <?php

    }


    /**
     * 插入编辑器
     */
    public static function VEditor($post)
    {
        $content = $post;
        $options = Helper::options();
        include 'assets/js/origin/editor.php';
        $meida_url = $options->adminUrl . 'media.php';

        ?>

        <script>
            var uploadURL = '<?php Helper::security()->index('/action/multi-upload?do=uploadfile&cid=CID'); ?>';
            var emojiPath = '<?php echo $options->pluginUrl; ?>';
            var scodePattern = '<?php echo self::get_shortcode_regex(array('scode')) ?>';
            var scodePattern = '<?php echo self::get_shortcode_regex(array('scode')) ?>';
            var meida_url = '<?php echo $meida_url ?>';
            var media_edit_url = '<?php Helper::security()->index('/action/contents-attachment-edit'); ?>';
        </script>

        <?php

    }


    /**
     * 获取匹配短代码的正则表达式
     * @param null $tagnames
     * @return string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L254
     */
    public static function get_shortcode_regex($tagnames = null)
    {
        global $shortcode_tags;
        if (empty($tagnames)) {
            $tagnames = array_keys($shortcode_tags);
        }
        $tagregexp = join('|', array_map('preg_quote', $tagnames));
        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        return
            '\\['                                // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|'
            . '\\]'                          // Closing bracket
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
        // phpcs:enable
    }

    /**
     * 获取短代码属性数组
     * @param $text
     * @return array|string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L508
     */
    public static function shortcode_parse_atts($text)
    {
        $atts = array();
        $pattern = self::get_shortcode_atts_regex();
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) && strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8]) && strlen($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                } elseif (isset($m[9])) {
                    $atts[] = stripcslashes($m[9]);
                }
            }
            // Reject any unclosed HTML elements
            foreach ($atts as &$value) {
                if (false !== strpos($value, '<')) {
                    if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }


    public static function content($content, $obj)
    {
        //不再使用，为了保持旧版本升级不出现问题，暂时保留
        return $obj->isMarkdown ? $obj->markdown($content)
            : $obj->autoP($content);
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

}

