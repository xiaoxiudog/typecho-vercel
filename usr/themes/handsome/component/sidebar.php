  <?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
  <?php if (@(!in_array('no-index', Utils::checkArray($this->options->sidebarSetting)) && (!$this->is("post") &&
              !$this->is("page")))||
      (!@in_array('no-others',
          Utils::checkArray($this->options->sidebarSetting)) && ($this->is("post") || $this->is("page")))): ?>
     <aside id="rightAside" class="asideBar col w-md bg-white-only bg-auto no-border-xs" role="complementary">
     <div id="sidebar">
         <?php if (@!in_array('column', Utils::checkArray($this->options->sidebarSetting))): ?>
      <section id="tabs-4" class="widget widget_tabs clear">
       <div class="nav-tabs-alt no-js-hide">
        <ul class="nav nav-tabs nav-justified box-shadow-bottom-normal tablist" role="tablist">
         <li data-index="0" class="active" role="presentation"> <a data-target="#widget-tabs-4-hots" role="tab"
                                                                   aria-controls="widget-tabs-4-hots" aria-expanded="true" data-toggle="tab"><div class="sidebar-icon wrapper-sm"><i data-feather="thumbs-up"></i></div><span class="sr-only"><?php _me("热门文章") ?></span> </a></li>
            <?php if (COMMENT_SYSTEM == 0): ?>
                <li role="presentation" data-index="1"> <a data-target="#widget-tabs-4-comments" role="tab" aria-controls="widget-tabs-4-comments" aria-expanded="false" data-toggle="tab"><div class="sidebar-icon wrapper-sm"><i  data-feather="message-square"></i></div> <span class="sr-only"><?php _me("最新评论") ?></span> </a></li>
            <?php endif; ?>
            <li data-index="2" role="presentation"> <a data-target="#widget-tabs-4-random" role="tab" aria-controls="widget-tabs-4-random" aria-expanded="false" data-toggle="tab"> <div class="sidebar-icon wrapper-sm"><i data-feather="gift"></i></div>  <span class="sr-only"><?php _me("随机文章") ?></span>
             </a></li>
            <span class="navs-slider-bar"></span>
        </ul>
       </div>
       <div class="tab-content">
       <!--热门文章-->
        <div id="widget-tabs-4-hots" class="tab-pane  fade in wrapper-md active" role="tabpanel">
         <h5 class="widget-title m-t-none text-md"><?php _me("热门文章") ?></h5>
         <ul class="list-group no-bg no-borders pull-in m-b-none">
          <?php Content::returnHotPosts($this); ?>
         </ul>
        </div>
           <?php if (COMMENT_SYSTEM == 0): ?>
        <!--最新评论-->
        <div id="widget-tabs-4-comments" class="tab-pane fade wrapper-md no-js-show" role="tabpanel">
         <h5 class="widget-title m-t-none text-md"><?php _me("最新评论") ?></h5>
         <ul class="list-group no-borders pull-in auto m-b-none no-bg">
          <?php $this->widget('Widget_Comments_Recent', 'ignoreAuthor=true&pageSize=5&parentId=0')->to($comments2); ?>
          <?php while($comments2->next()): ?>
          <li class="list-group-item">

              <a href="<?php $comments2->permalink(); ?>" class="pull-left thumb-sm avatar m-r">
                  <?php
                      if (count($this->options->indexsetup)>0 && !in_array('notShowRightSideThumb', Utils::checkArray($this->options->indexsetup))){
                          echo Utils::avatarHtml($comments2);
                      }
                  ?>
              </a>
              <a href="<?php $comments2->permalink(); ?>" class="text-muted">
                  <!--<i class="iconfont icon-comments-o text-muted pull-right m-t-sm text-sm" title="<?php /*_me("详情") */?>" aria-hidden="true" data-toggle="tooltip" data-placement="auto left"></i>
                  <span class="sr-only"><?php /*_me("评论详情") */?></span>-->
              </a>
              <div class="clear">
                  <div class="text-ellipsis">
                      <a href="<?php $comments2->permalink(); ?>" title="<?php $comments2->author(false); ?>"> <?php $comments2->author(false); ?> </a>
                  </div>
                  <small class="text-muted">
                      <span>
                          <?php
                              $content = Content::postCommentContent(Markdown::convert($comments2->text),
                              $this->user->hasLogin(),"","","");
                              $commentValue = $content;
                              $commentValue = strip_tags($commentValue);
                              $commentValue = trim($commentValue);
                              if ($commentValue == "") {//只含有空白或者空格自字符
                                  echo _mt("空白占位符");
                              } else {
                                  echo Typecho_Common::subStr($commentValue, 0, 34, "...");
                              }
                          ?>
                      </span>
                  </small>
              </div>
          </li>
          <?php endwhile; ?>
         </ul>
        </div>
           <?php endif; ?>
        <!--随机文章-->
        <div id="widget-tabs-4-random" class="tab-pane fade wrapper-md no-js-show" role="tabpanel">
            <h5 class="widget-title m-t-none text-md"><?php _me("随机文章") ?></h5>
            <ul class="list-group no-bg no-borders pull-in">
            <?php Content::returnRandomPosts($this);?>
            </ul>
        </div>
       </div>
      </section>
         <?php endif; ?>
      <!--博客信息-->
         <?php if (@!in_array('info', Utils::checkArray($this->options->sidebarSetting))): ?>
      <section id="blog_info" class="widget widget_categories wrapper-md clear">
       <h5 class="widget-title m-t-none text-md"><?php _me("博客信息") ?></h5>
       <ul class="list-group box-shadow-wrap-normal">
           <?php Typecho_Widget::widget('Widget_Stat')->to($stat); ?>
           <li class="list-group-item text-second"><span class="blog-info-icons"> <i data-feather="award"></i></span> <span
                       class="badge
           pull-right"><?php $stat->publishedPostsNum() ?></span><?php _me("文章数目") ?></li>
           <?php if (COMMENT_SYSTEM == 0): ?>
           <li class="list-group-item text-second"> <span class="blog-info-icons"> <i data-feather="message-circle"></i></span>
               <span class="badge
           pull-right"><?php $stat->publishedCommentsNum() ?></span><?php _me("评论数目") ?></li>
           <?php endif; ?>
           <li class="list-group-item text-second"><span class="blog-info-icons"> <i data-feather="calendar"></i></span>
               <span class="badge
           pull-right"><?php echo Utils::getOpenDays(); ?></span><?php _me("运行天数") ?></li>
           <li class="list-group-item text-second"><span class="blog-info-icons"> <i data-feather="activity"></i></span> <span
                       class="badge
           pull-right"><?php echo Utils::getLatestTime($this); ?></span><?php _me("最后活动") ?></li>
       </ul>
      </section>
      <?php endif; ?>
         <?php if ($this->options->adContentSidebar != ""): ?>
         <!--广告位置-->
         <section id="a_d_sidebar" class="widget widget_categories wrapper-md clear">
             <h5 class="widget-title m-t-none text-md"><?php _me("广告") ?></h5>
            <?php $this->options->adContentSidebar(); ?>
         </section>
         <?php endif; ?>
         <!--非文章页面-->
      <?php if (!($this->is('post'))) : ?>
      <section id="tag_cloud-2" class="widget widget_tag_cloud wrapper-md clear">
       <h5 class="widget-title m-t-none text-md"><?php _me("标签云") ?></h5>
          <div class="tags l-h-2x">
              <?php Typecho_Widget::widget('Widget_Metas_Tag_Cloud','ignoreZeroCount=1&limit=30')->to($tags); ?>
              <?php if($tags->have()): ?>
                  <?php while ($tags->next()): ?>
                      <a href="<?php $tags->permalink();?>" class="label badge" title="<?php $tags->name(); ?>" data-toggle="tooltip"><?php $tags->name(); ?></a>
                  <?php endwhile; ?>
              <?php endif; ?>
          </div>
      </section>
    <?php else: ?>
          <!--文章页面-->
          <section id="tag_cloud-2" class="widget widget_tag_cloud wrapper-md clear">
              <h5 class="widget-title m-t-none text-md"><?php _me("文章标签") ?></h5>
              <div class="post-tags tags l-h-2x">
                  <?php $this->tags(' ', true, _mt("暂无标签")); ?>
              </div>
          </section>
          <?php if (IS_TOC): ?>
          <div id="tag_toc_body" class="tag_toc_body">
              <section id="tag_toc" class="widget widget_categories wrapper-md clear">
                  <h5 class="widget-title m-t-none text-md"><?php _me("文章目录") ?></h5>
                  <div class="tags l-h-2x box-shadow-wrap-normal">
                      <div id="toc" class="small-scroll-bar overflow-y-auto"></div>
                  </div>
              </section>
          </div>
          <?php else: ?>

              <?php endif; ?>
    <?php endif; ?>
    </div>
     </aside>
  <?php endif; ?>
