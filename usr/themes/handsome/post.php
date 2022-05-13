<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$rData = Utils::isLock($this->title,$this->categories[0]['description'],$this->categories[0]['slug'],"category",$this->date->timeStamp);
if ($rData["flag"])
:?>
    <?php
    $data = array();
    $data['title'] = $this->categories[0]['name'];
    $data['md5'] = Utils::encodeData($rData['password']);
    $data['type'] = "category";
    $data['unique_id'] = $this->categories[0]['slug'];
    $data['img'] = @$rData['img'];
    $_GET['data']=$data;
    require_once('libs/Lock.php'); ?>
<?php else: ?>
<?php $this->need('component/header.php'); ?>
	<!-- aside -->
	<?php $this->need('component/aside.php'); ?>
	<!-- / aside -->
<?php if (trim($this->options->postFontSize)!==""): ?>
<style>
    #post-content{
        font-size: <?php echo $this->options->postFontSize."px"; ?>;
    }
</style>
<?php endif; ?>
<!-- <div id="content" class="app-content"> -->
   <a class="off-screen-toggle hide"></a>
   <main class="app-content-body <?php Content::returnPageAnimateClass($this); ?>">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
    <!--文章-->
     <div class="col center-part gpu-speed" id="post-panel">
         <!--生成分享图片必须的HTML结构-->
         <?php if (@in_array("sreenshot",$this->options->featuresetup)): ?>
         <?php echo Content::returnSharePostDiv($this) ?>
         <?php endif;?>
    <!--标题下的一排功能信息图标：作者/时间/浏览次数/评论数/分类-->
      <?php echo Content::exportPostPageHeader($this,$this->user->hasLogin()); ?>
       <!--文章标题下面的小部件-->
         <?php if (Content::isImageCategory($this->categories)): ?>
         <?php $display = ' style="display:none"'; echo '<small class="text-muted letterspacing indexWords">'
                 .$this->fields->album
                 .'</small>'; ?>
         <?php endif; ?>
         <ul <?php if (isset($display)) echo $display;?> class="entry-meta text-muted list-inline m-b-none small
             post-head-icon">
             <!--作者-->
             <li class="meta-author"><span class="post-icons"><i data-feather="user"></i></span><span
                         class="sr-only"><?php _me("作者") ?>：</span> <a class="meta-value" href="<?php $this->author->permalink(); ?>" rel="author"> <?php $this->author(); ?></a></li>
             <!--发布时间-->
             <li class="meta-date" data-toggle="tooltip" data-html="true" data-original-title="<?php $this->date(I18n::dateFormat("detail")); ?>"><span class="post-icons"><i data-feather="clock"></i></span><span class="sr-only"><?php _me("发布时间：") ?></span><time class="meta-value"><?php $this->date(I18n::dateFormat()); ?></time></li>
             <!--浏览数-->
             <li class="meta-views"><span class="post-icons"><i data-feather="eye"></i></span><span class="meta-value"><?php echo get_post_view($this) ?>&nbsp;<?php _me('次浏览'); ?></span></li>
             <?php if($this->options->commentChoice =='0'): ?>
                 <!--评论数-->
                 <li class="meta-comments"><span class="post-icons"><i data-feather="message-circle"></i></span><a
                             class="meta-value" href="#comments"><?php if ($this->allowComment == 1)$this->commentsNum(_mt('暂无评论'), _mt('1 条评论'), _mt('%d 条评论'));else _me("关闭评论") ;?></a></li>
             <?php endif; ?>

             <!--文字数目-->
             <li class="meta-word"><span class="post-icons"><i data-feather="pen-tool"></i></span><span class="meta-value"><?php echo Utils::getWordsOfContentPost($this->text); ?><?php _me('字数'); ?></span></li>
             <!--分类-->
             <li class="meta-categories"><span class="post-icons"><i data-feather="hash"></i></span><span class="sr-only"><?php _me("分类") ?>：</span> <span class="meta-value"><?php $this->category(' '); ?></span></li>
         </ul>
      </header>
      <div class="wrapper-md">
	   <?php Content::BreadcrumbNavigation($this, $this->options->rootUrl); ?>
       <!--博客文章样式 begin with .blog-post-->
       <div id="postpage" class="blog-post">
        <article class="single-post panel">
        <!--文章页面的头图-->
        <?php if (!Content::isImageCategory($this->categories)) echo Content::exportHeaderImg($this); ?>
         <!--文章内容-->
         <div id="post-content" class="wrapper-lg">

             <?php
             Content::postContentHtml($this,
                 $this->user->uid); ?>


             <?php if ($this->options->adContentPost != ""): ?>
                 <!--文章页脚的广告位-->
                 <?php $this->options->adContentPost(); ?>
             <?php endif; ?>
             <!--文章的页脚部件：打赏和其他信息的输出-->
             <?php echo PostContent::exportPostFooter($this->modified,$this->options->timezone - idate("Z"),$this); ?>
                 <!--打赏模块-->
             <?php
             $ifHideStar = $this->fields->off_star == "true" || Utils::getExpertValue("off_star_post");
             $ifHideReward = !(!empty($this->options->featuresetup) && in_array('payforauthorinpost', Utils::checkArray( $this->options->featuresetup)));
             echo PostContent::exportPayForAuthors($this->cid,$ifHideStar,$ifHideReward); ?>
             <!--/文章的页脚部件：打赏和其他信息的输出-->
         </div>
        </article>
       </div>
       <!--上一篇&下一篇-->
       <nav class="m-t-lg m-b-lg">
        <ul class="pager">
        <?php thePrev($this); ?>   <?php theNext($this); ?>
        </ul>
       </nav>
       <!--评论-->
        <?php $this->need('component/comments.php') ?>
      </div>
         <?php echo WidgetContent::returnRightTriggerHtml() ?>
     </div>
     <!--文章右侧边栏开始-->
    <?php
    //if (!Content::isImageCategory($this->categories)) {
        $this->need('component/sidebar.php');
    //}
    ?>
     <!--文章右侧边栏结束-->
    </div>
   </main>


    <?php echo Content::returnReadModeContent($this,$this->user->uid); ?>

<!-- footer -->
	<?php $this->need('component/footer.php'); ?>
  	<!-- / footer -->
<?php endif; ?>


