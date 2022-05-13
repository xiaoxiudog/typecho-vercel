<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('component/header.php');
?>


<!-- aside -->
<?php $this->need('component/aside.php'); ?>
<!-- / aside -->

<?php
//从自己的接口中获取搜索内容
$t1 = microtime(true);
$array = Utils::searchGetResult($this->request->keywords,$this->user->hasLogin(),100);
$t2 = microtime(true);
$spend = round($t2 - $t1, 3);
?>

    <!-- content -->
        <main class="app-content-body <?php Content::returnPageAnimateClass($this); ?>">
            <div class="hbox hbox-auto-xs hbox-auto-sm">
                <div class="col center-part" id="post-panel">

                <div class="bg-light lter wrapper-md">
                <h1 class="entry-title m-n font-thin text-black l-h"><span class="title-icons"><i data-feather="feather"></i></span><?php _me("搜索结果") ?></h1>
                <div class="entry-meta text-muted  m-b-none small post-head-icon"><?php $this->archiveTitle(array(
                        'search'    =>  _mt('找到关于 <b>%s</b> '),
                    ), '', ''); _me("的%s条结果",count(Utils::checkArray($array)));
                    _me("（用时 %s 秒）",$spend);?></div>
            </div>
                <div class="wrapper-md">
                <div class="tab-container post_tab">
                    <ul class="nav no-padder b-b">
                        <li class="nav-item active"><a  class="nav-link" href data-toggle="tab"
                                                       data-target="#tab_1"><?php _me
                                ("文章")
                                ?></a></li>
<!--                        <li class="nav-item"><a  class="nav-link" href data-toggle="tab"-->
<!--                                                        data-target="#tab_comments">--><?php //_me
//                                ("评论")
//                                ?><!--</a></li>-->
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            <ul class="list-group no-borders pull-in m-b-none">
                                <?php
//                                print_r($array);
                                if (!empty($array)): ?>
                                <?php foreach ($array as $item): ?>
                                <li class="list-group-item">
                                    <a href="<?php echo $item["path"]; ?>" class="font-bold text-ellipsis h5 block"><?php
                                        echo $item["title"]; ?></a>
                                    <p class="summary l-h-2x text-muted"><?php echo  $item["content"]; ?></p>
                                </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="m-t-md no_search_result"> <?php _me("没有找到搜索结果，请尝试更换关键词。") ?> </p>
                                <?php endif; ?>
                            </ul>

                            <!--分页 按钮-->
<!--                            <nav class="text-center " role="navigation">-->
<!--                                --><?php //$this->pageNav('<i class="fontello fontello-chevron-left"></i>', '<i class="fontello fontello-chevron-right"></i>'); ?>
<!--                            </nav>-->
<!--                            <style>-->
<!--                                .page-navigator>li>a, .page-navigator>li>span{-->
<!--                                    line-height: 1.42857143;-->
<!--                                    padding: 6px 12px;-->
<!--                                }-->
<!--                            </style>-->
                        </div>

                    </div>
                </div>


                <?php echo WidgetContent::returnRightTriggerHtml() ?>

            </div>
                </div>
                <!--首页右侧栏-->
                <?php $this->need('component/sidebar.php') ?>
        </div>
        <!-- /content -->
        </main>

<!-- footer -->
<?php $this->need('component/footer.php'); ?>
<!-- / footer -->
