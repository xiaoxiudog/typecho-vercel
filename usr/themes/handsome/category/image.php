<?php
/**
 * 自定义图片分类的样式
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>

<?php
$content = $this->getDescription();
$content = json_decode($content, true);

$password = Typecho_Cookie::get('category_' . $this->getArchiveSlug());

$cookie = false;//true为可以直接进入
//这个地方md5不改也行，因为cookie存的也是md5
if (!empty($password) && $password == Utils::encodeData(@$content['password'])) {
    $cookie = true;
}

if (is_array($content) && @$content['lock'] == true && !$cookie):?>

    <?php

    $data = array();
    $data['title'] = $this->getArchiveTitle();
    $data['md5'] = Utils::encodeData($content['password']);
    $data['type'] = "category";
    $data['unique_id'] = $this->getArchiveSlug();
    $data['img'] = @$content['img'];


    $_GET['data'] = $data;
    require_once(dirname(__DIR__).'/libs/Lock.php');
    ?>

<?php else: ?>

    <?php $this->need('component/header.php'); ?>


    <!-- aside -->
    <?php $this->need('component/aside.php'); ?>
    <!-- / aside -->

    <!-- <div id="content" class="app-content"> -->
    <a class="off-screen-toggle hide"></a>
    <main class="app-content-body <?php Content::returnPageAnimateClass($this); ?>">
        <div class="hbox hbox-auto-xs hbox-auto-sm">
            <div class="col center-part gpu-speed" id="post-panel">
                <?php if ($this->options->blogNotice): ?>

                    <!--/详细介绍-->
                <?php endif; ?>
                <header class="bg-light lter wrapper-md">
                    <h1 class="m-n font-thin h3 text-black l-h"><?php $this->archiveTitle(array(
                            'category' => _mt('%s'),
                        ), '', ''); ?></h1>
                    <small class="text-muted letterspacing indexWords"><?php
                        echo Utils::getCateDesc($this->getDescription()); ?></small>
                </header>
                <div class="wrapper-md">
                    <!--首页输出相册-->
                    <div class="layout">
                        <div class="albums">
                            <?php
                            $index = 0;
                            while ($this->next()) {
                                $base64 = false;
                                if ($this->hidden == 1) {
                                    $base64 = true;
                                    $cover = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgMjQwIj48ZyBvcGFjaXR5PSIuOCIgZmlsbD0iI0ZGRiI+PHBhdGggZD0iTTEyMSAwQzUzLjktLjYtLjYgNTMuOSAwIDEyMWMuNiA2NS4yIDUzLjggMTE4LjQgMTE5IDExOSA2Ny4xLjYgMTIxLjYtNTMuOSAxMjEtMTIxQzIzOS40IDUzLjggMTg2LjIuNiAxMjEgMHpNOTAuNSA1OWMuMy0uOSAxLTEuNSAyLjItMS4yIDIuMi41IDE5LjkgNC4zIDE5LjkgNC4zczM2LjgtNS42IDM4LjEtNS45YzEuMS0uMiAxLjkuNCAyLjEgMS40LjEuNCA2LjMgMjEuMyAxMS43IDM5LjVINzguM0M4My45IDc5LjYgOTAuMSA2MCA5MC41IDU5em04NS45IDEwMy4zYy0uOCAxMi4yLTEwLjcgMjIuMS0yMi45IDIyLjktMTQuMy45LTI2LjEtMTAuNC0yNi4xLTI0LjUgMC0uNyAwLTEuNC4xLTIuMS0yLS43LTQuMi0xLTYuNC0xLTIuMyAwLTQuNS40LTYuNyAxLjEuMS43LjEgMS4zLjEgMiAwIDE0LjEtMTEuOCAyNS40LTI2LjEgMjQuNS0xMi4yLS44LTIyLjEtMTAuNy0yMi45LTIyLjktLjgtMTQuMiAxMC41LTI2LjEgMjQuNS0yNi4xIDEwLjIgMCAxOSA2LjMgMjIuNyAxNS4yIDIuNy0uOCA1LjUtMS4zIDguNC0xLjMgMi44IDAgNS41LjQgOC4xIDEuMiAzLjctOC45IDEyLjQtMTUuMSAyMi43LTE1LjEgMTQuMSAwIDI1LjQgMTEuOSAyNC41IDI2LjF6bTIzLjQtMzQuM0g0Mi40Yy0uMiAwLS4zLS4zLS4xLS40IDUuMi0yLjcgMzUuNC0xNy42IDc5LTE3LjYgNDMuNyAwIDczLjUgMTQuOCA3OC42IDE3LjYuMi4xLjEuNC0uMS40eiIvPjxjaXJjbGUgY3g9IjE1MS45IiBjeT0iMTYwLjgiIHI9IjE3LjQiLz48Y2lyY2xlIGN4PSI5MC4xIiBjeT0iMTYwLjgiIHI9IjE3LjQiLz48L2c+PC9zdmc+";
                                } else {
                                    $base64 = false;
                                    $cover = Content::returnHeaderImgSrc($this, "index", $index, true);
                                }
                                $title = $this->title;
                                $url = $this->permalink;
                                $isLock = "";
                                $imgHtml = Utils::returnImageLazyLoadHtml($base64, $cover, 300, 300);
                                if ($this->hidden == 1) {
                                    $isLock = " image-lock";
                                }
                                echo <<<EOF
<figure class="album-thumb" itemprop="associatedMedia" itemscope="" itemtype="http://schema.org/ImageObject">

                        <a href="{$url}" class="img-wrap{$isLock}">

                            <img class="lazy" alt="{$title}" {$imgHtml} itemprop="thumbnail">
                        </a>
                        <figcaption itemprop="caption description">{$title}</figcaption>
                    </figure>
EOF;
                                $index++;
                            }
                            ?>
                        </div>
                    </div>
                    <!--分页首页按钮-->
                    <nav class="text-center m-t-lg m-b-lg image_nav" role="navigation" style="display: none">
                        <?php $this->pageNav('<i class="fontello fontello-chevron-left"></i>', '<i class="fontello fontello-chevron-right"></i>'); ?>
                    </nav>
                    <nav class="text-center m-t-lg m-b-lg">
                        <button class="btn m-b-xs btn-sm btn-dark btn-addon view-more-button"><?php _me("加载更多") ?></button>
                    </nav>
                    <nav class="text-center m-t-lg m-b-lg page-load-status">
                        <!--<button class="btn m-b-xs btn-sm btn-dark btn-addon infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i><?php /*_me("正在加载……") */ ?></button>-->
                        <p class="infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i>Loading...
                        </p>

                        <p class="infinite-scroll-last"><?php _me("已经到底了") ?></p>
                        <p class="infinite-scroll-error"><?php _me("加载错误，请稍后重试") ?></p>
                    </nav>
                    <style>
                        .page-navigator > li > a, .page-navigator > li > span {
                            line-height: 1.42857143;
                            padding: 6px 12px;
                        }
                    </style>
                </div>
                <?php echo WidgetContent::returnRightTriggerHtml() ?>
            </div>
            <!--首页右侧栏-->
            <?php $this->need('component/sidebar.php') ?>
        </div>
    </main>


    <script>
        if ($('.next a').length > 0) {
            $.getScript("<?php echo STATIC_PATH ?>js/features/infinite-scroll.pkgd.min.js", function () {
                $container = $('.albums').infiniteScroll({
                    // options
                    path: '.next a',
                    append: '.album-thumb',
                    history: false,
                    button: '.view-more-button',
                    status: '.page-load-status',
                    hideNav: '.image_nav',

                });
                var $viewMoreButton = $('.view-more-button');
                //$viewMoreButton.click();
                $viewMoreButton.on('click', function () {
                    console.log("it is click");
                    $container.infiniteScroll('loadNextPage');
                    $container.infiniteScroll('option', {
                        loadOnScroll: false,
                    });
                    // $viewMoreButton.hide();
                });

                $container.on('append.infiniteScroll', function (event, response, path, items) {
                    const temp = $(".next a", response);
                    if (temp.length > 0){
                        $(".next a").attr("href",temp.attr("href"))
                    }else{
                        $(".next a").remove();
                    }

                    // $container.infiniteScroll('option', {
                    //     loadOnScroll: false,
                    // });
                    // $(".next a").attr("href") =
                    <?php if (@in_array('lazyload', Utils::checkArray( $this->options->featuresetup))): ?>
                    <?php if (in_array('isPageAnimate', Utils::checkArray( $this->options->featuresetup))): ?>
                    $('.app-content-body').animateCss('fadeInUpBig', function () {
                        $(".lazy").lazyload({
                            effect: "fadeIn",
                            threshold: "500"
                        });
                    });
                    <?php else: ?>
                    $(".lazy").lazyload({
                        effect: "fadeIn",
                        threshold: "500"
                    });
                    <?php endif; ?>
                    <?php endif; ?>
                });
            });
        } else {
            $('.view-more-button').hide();
        }
    </script>

    <!-- footer -->
    <?php $this->need('component/footer.php'); ?>
    <!-- / footer -->
<?php endif; ?>
