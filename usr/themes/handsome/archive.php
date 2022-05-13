<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
$rData = Utils::isLock($this->getArchiveTitle(),$this->getDescription(),$this->getArchiveSlug(),"category");
if ($rData["flag"]):?>

<?php
    $_GET['data']=$rData;
require_once('libs/Lock.php'); ?>

<?php else: ?>


<?php $this->need('component/header.php'); ?>

    <!-- aside -->
    <?php $this->need('component/aside.php'); ?>
    <!-- / aside -->

  <a class="off-screen-toggle hide"></a>
  <main class="app-content-body <?php echo Content::returnPageAnimateClass($this); ?>">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
      <div class="col center-part gpu-speed" id="post-panel">
        <header class="bg-light lter  wrapper-md">
          <h1 class="m-n font-thin h3 text-black l-h"><?php $this->archiveTitle(array(
            'category'  =>  _mt('分类 %s 下的文章'),
            'search'    =>  _mt('包含关键字 %s 的文章'),
            'tag'       =>  _mt('标签 %s 下的文章'),
            'author'    =>  _mt('%s 发布的文章')
        ), '', ''); ?></h1>
          </header>
        <div class="wrapper-md">
            <?php Content::BreadcrumbNavigation($this, $this->options->rootUrl); ?>
       <?php if ($this->have()): ?>
            <!-- 输出文章 TODO:整合该部分代码-->
           <?php Content::echoPostList($this) ?>
       <?php else: ?>
            <p class="m-b-md no_search_result panel"> <?php _me("没有找到搜索结果，请尝试更换关键词。") ?> </p>
          <?php endif; ?>

          <!--分页 按钮-->
          <nav class="text-center m-t-lg m-b-lg" role="navigation">
              <?php $this->pageNav('<i class="fontello fontello-chevron-left"></i>', '<i class="fontello fontello-chevron-right"></i>'); ?>
          </nav>
            <style>
                .page-navigator>li>a, .page-navigator>li>span{
                    line-height: 1.42857143;
                    padding: 6px 12px;
                }
            </style>
        </div>
      </div>
      <!-- 右侧栏-->
      <?php $this->need('component/sidebar.php'); ?>
    </div>
  </main>

    <!-- footer -->
    <?php $this->need('component/footer.php'); ?>
    <!-- / footer -->
<?php endif; ?>
