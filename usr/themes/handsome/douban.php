<?php
/**
 * 豆瓣清单
 *
 * @package custom
 */
?>
<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>
<?php $this->need('component/header.php'); ?>

<!-- aside -->
<?php $this->need('component/aside.php'); ?>
<!-- / aside -->

<?php
require 'libs/component/ParserDom.php';


//获取豆瓣清单数据
function getDoubanData($userID, $type)
{
    require_once __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Handsome/cache/cache.php';
    $cache = new CacheUtil();

    $contents = $cache->cacheRead($type);
    if (!$contents) {//没有缓存数据
        $data = updateData($userID, $type);
        $dataList = $data->data;
    } else {
        //存在缓存数据
        $data = Utils::json_decode($contents);
        if ($data->user != null && $data->user !== $userID) {//用户名有修改
            $data = updateData($userID, $type);
            $dataList = $data->data;
        } else {
            if ($data->data == null || $data->data == "") {//缓存文件中的电影数据为空
                $data = updateData($userID, $type);
                $dataList = $data->data;
            } else {//读取缓存文件中的数据
                $dataList = $data->data;
            }
        }
    }

    //更新最后更新时间
    $lastUpdateTime = date('Y-m-d', $data->time); //H 24小时制 2017-08-08 23:00:01
    echo '<script>$(function(){$(".douban_tips").text("以下数据最后更新于' . $lastUpdateTime . '")})</script>';

    return $dataList;
}

function curl_get_contents($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}


function updateData($doubanID, $type)
{
    $url = "https://" . $type . ".douban.com/people/$doubanID/collect"; //最多取100条数据，后面考虑支持分页获取，因为不知道分页按钮怎么写比较好看……
    $p1 = getHTML($url);
    $movieList = [];
    $p1 = getMoviesAndNextPage($p1, $type);
    $movieList = array_merge($p1['data']);
    $num = 0;
    while ($p1['next'] != null && $num <= 3) {
        $p1 = getHTML($p1['next']);
        $p1 = getMoviesAndNextPage($p1, $type);
        $movieList = array_merge($movieList, $p1['data']);
        $num++;

    }
    if ($movieList == null || $movieList == "") {
        $function = "";
        if (!function_exists('json_decode')) {
            $function .= "服务器不支持json_decode()方法";
        }
        if (!function_exists('curl_init')) {
            $function .= " 服务器没有curl扩展";
        }
        $info = "获取豆瓣数据失败，可能原因是：1. ip被豆瓣封锁（修改140行代码的cookie） 2. 豆瓣id配置错误（检查该地址是否能够打开" . $url . "）3. " . $function;
        echo '<script>$(function(){$(".douban_tips").text("' . $info . '")})</script>';
    }

    require_once __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Handsome/cache/cache.php';
    $cache = new CacheUtil();
    $json = json_encode(['time' => time(), 'user' => $doubanID, 'data' => $movieList]);
    $cache->cacheWrite($type, $json, 86400*3, $type, false, true);
    return json_decode($json);
}

function getMoviesAndNextPage($html, $type)
{
    $selector = [];
    if ($type == "movie") {
        $selector["item"] = "div.item";
        $selector["title"] = "li.title";
        $selector["img"] = "div.pic a img";
        $selector["href"] = "a";
        $selector["next"] = "span.next a";

    } else {
        $selector["item"] = ".subject-item";
        $selector["title"] = ".info h2";
        $selector["img"] = "div.pic a img";
        $selector["href"] = "a";
        $selector["next"] = "span.next a";
    }
    if ($html != "" && $html != null) {
        $doc = new \HtmlParser\ParserDom($html);
        $itemArray = $doc->find($selector["item"]);
        $movieList = [];
        foreach ($itemArray as $v) {
            $t = $v->find($selector['title'], 0);
            $movie_name = trimall($t->getPlainText());
            $movie_img = $v->find($selector["img"], 0)->getAttr("src");
            $movie_url = $t->find($selector["href"], 0)->getAttr("href");
            //已经读过的电影
            $movieList[] = array("name" => $movie_name, "img" => $movie_img, "url" => $movie_url);
        }

        $t = $doc->find($selector["next"], 0);
        if ($t) {
            $t = "https://" . $type . ".douban.com" . $t->getAttr("href");
        } else {
            $t = null;
        }
        return ['data' => $movieList, 'next' => $t];
    } else {
        return ['data' => [], 'next' => null];
    }


}

function getHTML($url = '')
{
    $ch = curl_init();
    $cookie = 'bid=37jvRVbkt74; douban-fav-remind=1; ll="108288"; ap_v=0,6.0; _pk_ref.100001.3ac3=%5B%22%22%2C%22%22%2C1615123876%2C%22https%3A%2F%2Fwww.douban.com%2Fmisc%2Fsorry%3Foriginal-url%3Dhttps%253A%252F%252Fbook.douban.com%252Fpeople%252F220804943%252Fcollect%22%5D; _pk_id.100001.3ac3=30dea2f8cdcf0fe9.1615123876.1.1615123876.1615123876.; _pk_ses.100001.3ac3=*; regpop=1';

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_1_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Edg/88.0.705.81');

    $output = curl_exec($ch);

    if (FALSE === $output)
        throw new Exception(curl_error($ch), curl_errno($ch));

    curl_close($ch);
    return $output;
}

function trimall($str)
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    $hou = array("", "", "", "", "");
    return str_replace($qian, $hou, $str);
}

?>
<style>
    .panel-body a {
        border: none;
    }

    .panel-body .external-link svg {
        display: none;
    }
</style>
<!-- <div id="content" class="app-content"> -->
<div id="loadingbar" class="butterbar hide">
    <span class="bar"></span>
</div>
<a class="off-screen-toggle hide"></a>
<main class="app-content-body" <?php Content::returnPageAnimateClass($this); ?>>
    <div class="hbox hbox-auto-xs hbox-auto-sm">
        <!--文章-->
        <div class="col center-part gpu-speed" id="post-panel">
            <!--标题下的一排功能信息图标：作者/时间/浏览次数/评论数/分类-->
            <?php echo Content::exportPostPageHeader($this, $this->user->hasLogin(), true); ?>
            <div class="wrapper-md">
                <?php Content::BreadcrumbNavigation($this, $this->options->rootUrl); ?>
                <!--博客文章样式 begin with .blog-post-->
                <div id="postpage" class="blog-post">
                    <article class="single-post panel">
                        <!--文章页面的头图-->
                        <?php echo Content::exportHeaderImg($this); ?>
                        <!--文章内容-->
                        <div id="post-content" class="wrapper-lg">
                            <div class="booklist">

                                <h2>我的书单</h2>
                                <div class="text-muted m-xs">
                                    <i class="fontello fontello-clock-o m-xs" aria-hidden="true"></i>
                                    <small class="letterspacing douban_tips"></small>
                                </div>
                                <div class="section">
                                    <div class="row">
                                        <?php
                                        $readList = getDoubanData($this->fields->doubanID, "book");
                                        foreach ($readList as $v):?>
                                            <div class="col-xs-4 col-sm-3 col-md-3 douban_item">
                                                <div class="panel panel-default box-shadow">
                                                    <div class="douban-image-body panel-body no-padder">
                                                        <a target="_blank" href="<?php echo $v->url; ?>">
                                                            <img class="img-full no-padder m-n douban-list"
                                                                 referrerpolicy="no-referrer"
                                                                 src="<?php $img = "https://images.weserv.nl/?url=" . str_replace('img1', 'img3', $v->img);
                                                                 echo str_replace('img1', 'img3', $v->img); ?>">
                                                        </a>
                                                    </div>
                                                    <div class="panel-footer">
                                                        <span class="text-ellipsis"><?php echo $v->name; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>


                                    <h2>我的观影</h2>
                                    <div class="text-muted m-xs">
                                        <i class="fontello fontello-clock-o m-xs" aria-hidden="true"></i>
                                        <small class="letterspacing douban_tips"></small>
                                    </div>
                                    <div class="padding">
                                        <div class="row box-shadow-2">
                                            <div class="col-md-12">
                                                <div class="row row-xs">

                                                    <?php
                                                    $movieList = getDoubanData($this->fields->doubanID, "movie");
                                                    foreach ($movieList as $v): ?>

                                                        <div class="col-xs-4 col-sm-3 col-md-3 douban_item">
                                                            <div class="panel panel-default box-shadow">
                                                                <div class="douban-image-body panel-body no-padder">
                                                                    <a target="_blank"
                                                                       href="<?php echo $v->url; ?>">
                                                                        <img referrerpolicy="no-referrer"
                                                                             class="img-full no-padder m-n douban-list"
                                                                             src="<?php $img = "https://images.weserv.nl/?url=" . str_replace('img1', 'img3', $v->img);
                                                                             echo str_replace('img1', 'img3', $v->img); ?>">
                                                                    </a>
                                                                </div>
                                                                <div class="panel-footer">
                                                                    <span class="text-ellipsis"><?php echo $v->name; ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <?php Content::postContentHtml($this, $this->user->hasLogin()); ?>
                        </div>
                    </article>
                </div>
                <!--评论-->
                <?php $this->need('component/comments.php') ?>
            </div>
            <?php echo WidgetContent::returnRightTriggerHtml() ?>
        </div>
        <!--文章右侧边栏开始-->
        <?php $this->need('component/sidebar.php'); ?>
        <!--文章右侧边栏结束-->
    </div>
    <script>
        // $(".douban-list").height($(".douban-list")[0].clientWidth * 1.2);
    </script>
</main>

<!-- footer -->
<?php $this->need('component/footer.php'); ?>
<!-- / footer -->
