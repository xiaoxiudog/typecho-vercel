<?php
/**
 * bilibili 追番
 *
 * @package custom
 */

/**
 * 本独立页面代码来自：https://mo66.cn/archives-42.html
 * 仅仅做了与handsome主题的兼容，感谢原作者
 */
?>
<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('component/header.php'); ?>

<!-- aside -->
<?php $this->need('component/aside.php'); ?>
<!-- / aside -->

<style>
    .Mo66CnBilifanItem{
        line-height: 20px;
        width: 100%;
        overflow: hidden;
        display: block;
        padding: 10px;
        height:120px;
        background: #fff;
        color: #14191e;
    }
    .Mo66CnBilifanItem:hover{
        color: #14191e;
        opacity: 0.8;
        filter: saturate(150%);
        -webkit-filter: saturate(150%);
        -moz-filter: saturate(150%);
        -o-filter: saturate(150%);
        -ms-filter: saturate(150%);
    }
    .Mo66CnBilifanItem img{
        width: auto!important;
        height:100%;
        display:inline-block;
        float:left;
        margin: 0 5% 0 0!important;
    }
    .Mo66CnBilifanItem .textBox{
        text-overflow:ellipsis;overflow:hidden;
        position: relative;
        z-index: 1;
        height: 100%;
    }
    .Mo66CnBilifanItem .jinduBG{
        height:16px;
        width: 100%;
        background-color:gray;
        display:inline-block;
        border-radius:4px;
        position: absolute;
        bottom: 3px;
    }
    .Mo66CnBilifanItem .jinduFG
    {
        height:16px;
        background-color:#ff8c83;
        border-radius:4px;
        position: absolute;
        bottom: 0px;
        z-index: 1;
    }
    .Mo66CnBilifanItem .jinduText
    {
        width:100%;height:auto;
        text-align:center;
        color:#fff;
        line-height:15px;
        font-size:15px;
        position: absolute;
        bottom: 0px;
        z-index: 2;
    }
    @media screen and (max-width:1000px) {
        .Mo66CnBilifanItem{
            width:95%;
        }
    }
</style>
<!-- <div id="content" class="app-content"> -->
<a class="off-screen-toggle hide"></a>
<main class="app-content-body <?php echo Content::returnPageAnimateClass($this); ?>">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
        <!--文章-->
        <div class="col center-part gpu-speed" id="post-panel">
            <!--标题下的一排功能信息图标：作者/时间/浏览次数/评论数/分类-->
            <?php echo Content::exportPostPageHeader($this,$this->user->hasLogin(),true); ?>
            <div class="wrapper-md">
                <?php Content::BreadcrumbNavigation($this, $this->options->rootUrl); ?>
                <!--博客文章样式 begin with .blog-post-->
                <div id="postpage" class="blog-post">
                    <article class="single-post panel">
                        <!--文章页面的头图-->
                        <?php echo Content::exportHeaderImg($this); ?>
                        <!--文章内容-->
                        <div class="wrapper-lg" id="post-content">
                            <div class="post-content" id="post-content" style="display: flow-root">
                                <?php Typecho_Plugin::factory('page_bilifan.php')->navBar(); ?>
                            </div>
                            <?php Content::postContentHtml($this,
                                $this->user->hasLogin()); ?>
                            <?php Content::pageFooter($this->options,$this) ?>
                        </div>
                    </article>
                </div>
                <!--评论-->
                <?php $this->need('component/comments.php') ?>
            </div>
        </div>
        <!--文章右侧边栏开始-->
        <?php $this->need('component/sidebar.php'); ?>
        <!--文章右侧边栏结束-->
    </div>
</main>

<!-- footer -->
<?php $this->need('component/footer.php'); ?>
<!-- / footer -->
