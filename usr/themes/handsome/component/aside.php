  <?php
  /**
   * 左侧边栏
   */
  if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

  <?php echo Content::selectLayout($this->options->indexsetup) ?>
  <!-- headnav -->
  <?php $this->need('component/headnav.php'); ?>
  <!-- / headnav -->

  <!--选择侧边栏的颜色-->
  <?php echo Content::selectAsideStyle(); ?>
  <!--<aside>-->
  <?php
  $avatarClass = array("","","");
  if (@!in_array("BigAvatar",$this->options->indexsetup)){
      $avatarClass = array("vertical-wrapper","vertical-avatar","vertical-flex");
  }
  ?>
      <div class="aside-wrap" layout="column">
        <div class="navi-wrap scroll-y scroll-hide" flex>
          <!-- user -->
          <div class="clearfix hidden-xs text-center hide  <?php if (!empty($this->options->indexsetup) && !in_array('show-avatar', Utils::checkArray( $this->options->indexsetup))) echo "show"; ?>" id="aside-user">
            <div class="dropdown wrapper <?php echo $avatarClass[0]?>">
                <div ui-nav>
            <?php if($this->options->rewrite == 1): ?>
              <a href="<?php echo Utils::returnDefaultIfEmpty($this->options->clickAvatarLink,$this->options->rootUrl.'/cross.html') ; ?>">
            <?php else: ?>
              <a href="<?php echo Utils::returnDefaultIfEmpty($this->options->clickAvatarLink,$this->options->rootUrl.'/index.php/cross.html') ; ?>">
            <?php endif; ?>
                <span class="thumb-lg w-auto-folded avatar m-t-sm  <?php echo $avatarClass[1]?>">
                  <img src="<?php $this->options->BlogPic() ?>" class="img-full img-circle">
                </span>
              </a>
                </div>
              <a href="#" data-toggle="dropdown" class="dropdown-toggle hidden-folded  <?php echo $avatarClass[2]?>">
                <span class="clear">
                  <span class="block m-t-sm">
                    <strong class="font-bold text-lt"><?php $this->options->BlogName(); ?></strong>
                    <b class="caret"></b>
                  </span>
                  <span class="text-muted text-xs block"><?php $this->options->BlogJob() ?></span>
                </span>
              </a>
              <!-- dropdown -->
              <ul class="dropdown-menu animated fadeInRight w hidden-folded no-padder">
                <li class="wrapper b-b m-b-sm bg-info m-n">
                  <span class="arrow top hidden-folded arrow-info"></span>
                  <div>
                <?php
                    $time= date("H",time()+($this->options->timezone - idate("Z")));
                    $percent= $time/24;
                    $percent= sprintf("%01.2f", $percent*100).'%';
                ?>
                <?php if($time>=6 && $time<=11): ?>
                  <p><?php _me("早上好，永远年轻，永远热泪盈眶") ?></p>
                <?php elseif($time>=12 && $time<=17): ?>
                  <p><?php _me("下午好，是时候打个盹了") ?></p>
                <?php else : ?>
                <p><?php _me("晚上好，注意早点休息") ?></p>
                <?php endif; ?>
                  </div>
                  <div class="progress progress-xs m-b-none dker">
                    <div class="progress-bar bg-white" data-toggle="tooltip" data-original-title="<?php _me("时间已经度过"); echo $percent; ?>" style="width: <?php echo $percent; ?>"></div>
                  </div>
                </li>
              </ul>
              <!-- / dropdown -->
            </div>
          </div>
          <!-- / user -->

          <!-- nav -->
          <nav ui-nav class="navi clearfix">
            <ul class="nav">
             <!--index-->
                <div class="line dk hidden-folded"></div>
                <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span><?php _me("导航") ?></span>
              </li>
              <?php
              $hideHomeItem = false;
              if(!empty(Typecho_Widget::widget('Widget_Options')->asideItems)){

                  $asideItems = Content::parseJson2Array(Typecho_Widget::widget('Widget_Options')->asideItems);
                  $asideItemsOutput = "";
                  foreach ($asideItems as $asideItem){

                      @$itemName = $asideItem->name;
                      @$itemStatus = $asideItem->status;

                      @$itemSub = $asideItem->sub;

                      if ($itemName === 'home' && strtoupper($itemStatus) === 'HIDE'){
                          $hideHomeItem = true;
                          continue;//本次循环结束，不再执行下面内容
                      }

                      $haveSub = false;
                      $subListHtml = "";
//                      print_r($itemSub);
                      if (is_array($itemSub)){
                          $haveSub = true;
                          $subListHtml .= '<ul class="nav nav-sub dk">';
                          foreach ($itemSub as $subItem){
                              $subListHtml .= Content::returnLeftItem($subItem,false,"");
                          }
                          $subListHtml .= '</ul>';
                      }

                      $asideItemsOutput .= Content::returnLeftItem($asideItem,$haveSub,$subListHtml);
                  }
              }
              ?>
              <?php if (!$hideHomeItem): ?>
              <!--主页-->
              <li>
                <a href="<?php $this->options->rootUrl(); ?>/" class="auto">
                    <span class="nav-icon"><i data-feather="home"></i></span>
                    <span><?php _me("首页") ?></span>
                </a>
              </li>
              <!-- /主页 -->
              <?php endif; ?>
              <?php echo @$asideItemsOutput ?>
                <?php if (@!in_array('component', Utils::checkArray($this->options->asideSetting))): ?>
              <li class="line dk"></li>
			<!--Components-->
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span><?php _me("组成") ?></span>
              </li>
              <!--分类category-->
                <?php
                $class = "";
                    if (in_array("openCategory",$this->options->featuresetup)){
                        $class = "class=\"active\"";
                    }
                    ?>
              <li <?php echo $class; ?>>
                <a class="auto">
                  <span class="pull-right text-muted">
                    <i class="fontello icon-fw fontello-angle-right text"></i>
                    <i class="fontello icon-fw fontello-angle-down text-active"></i>
                  </span>
<!--                  <i class="glyphicon glyphicon-th"></i>-->
                    <span class="nav-icon"><i data-feather="grid"></i></span>

                    <span><?php _me("分类") ?></span>
                </a>
                <ul class="nav nav-sub dk">
                  <li class="nav-sub-header">
                    <a>
                      <span><?php _me("分类") ?></span>
                    </a>
                  </li>
                  <!--循环输出分类-->

                    <?php $this->widget('Handsome_Widget_Metas_Category_List')->listCategories('wrapClass=nav nav-sub dk&childClass=child-nav'); ?>
                    <?php
                    $this->widget('Widget_Metas_Category_List')->to($categorys);
//                    echo Content::returnCategories($categorys)
                    ?>

                    <!--/循环输出分类-->

                </ul>
              </li>
                <?php if (@!in_array('pages', Utils::checkArray($this->options->asideSetting))): ?>
                    <!--独立页面pages-->
              <li>
                <a class="auto">
                  <span class="pull-right text-muted">
                    <i class="fontello icon-fw fontello-angle-right text"></i>
                    <i class="fontello icon-fw fontello-angle-down text-active"></i>
                  </span>
                    <span class="nav-icon"><i data-feather="file"></i></span>
                  <span><?php _me("页面") ?></span>
                </a>
                <ul class="nav nav-sub dk">
                  <li class="nav-sub-header">
                    <a data-no-instant>
                      <span><?php _me("页面") ?></span>
                    </a>
                  </li><!--这个字段不会被显示出来-->
                  <!--循环输出独立页面-->
                  <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
                   <?php while($pages->next()): ?>
                       <?php if ($pages->fields->navbar == "hide") continue; ?>
                       <li><a href="<?php $pages->permalink(); ?>"><span><?php $pages->title(); ?></span></a></li>
                   <?php endwhile; ?>
                </ul>
              </li>
                <?php endif; ?>
                <?php if (@!in_array('links', Utils::checkArray($this->options->asideSetting))): ?>
                    <!--友情链接-->
              <li>
                <a class="auto">
                  <span class="pull-right text-muted">
                    <i class="fontello icon-fw fontello-angle-right text"></i>
                    <i class="fontello icon-fw fontello-angle-down text-active"></i>
                  </span>
                    <span class="nav-icon"><i data-feather="user"></i></span>
                  <span><?php _me("友链") ?></span>
                </a>
                <ul class="nav nav-sub dk">
                  <li class="nav-sub-header">
                    <a data-no-instant>
                      <span><?php _me("友链") ?></span>
                    </a>
                  </li>
                  <!--使用links插件，输出全站友链-->
                 <?php $mypattern1 = "<li data-original-title=\"{title}\" data-toggle=\"tooltip\" 
data-placement=\"top\"><a rel='noopener' href=\"{url}\" target=\"_blank\"><span>{name}</span></a></li>";
                 $options = mget();
                 if ($options->pluginReady){
                     Handsome_Plugin::output($mypattern1, 0, "ten");
                 } ?>
                </ul>
              </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
          </nav>
          <!-- nav -->
        </div>
          <!--end of .navi-wrap-->
          <!--left_footer-->
          <?php if (@!in_array('notShowleftBottomMenu', Utils::checkArray($this->options->indexsetup)) && @in_array("aside-fix", Utils::checkArray($this->options->indexsetup))): ?>
          <div id="left_footer" class="footer wrapper-xs text-center nav-xs lt">
                  <?php if (@in_array('hideLogin', Utils::checkArray($this->options->featuresetup))){
                      $class = "col-xs-6";
                  }else{
                      $class = "col-xs-4";
                  }; ?>

                  <?php if (!@in_array('hideLogin', Utils::checkArray($this->options->featuresetup))): ?>
                      <div class="<?php echo $class; ?> no-padder">
                          <a target="_blank" class="tinav" href="<?php $this->options->adminUrl(); ?>" title="" data-toggle="tooltip" data-placement="top" data-original-title="<?php _me("后台管理") ?>">
                              <span class="left-bottom-icons block"><i data-feather="settings"></i></span>
                              <small class="text-muted"><?php _me("管理") ?></small>
                          </a>
                      </div>
                  <?php endif; ?>
                  <div class="<?php echo $class; ?> no-padder">
                      <a target="_blank" class="tinav" href="<?php $this->options->feedUrl(); ?>" title=""
                         data-toggle="tooltip" data-placement="top" data-original-title="<?php _me("文章RSS订阅") ?>">
                          <span class="left-bottom-icons block"><i data-feather="rss"></i></span>
                          <small class="text-muted"><?php _me("文章") ?></small>
                      </a>
                  </div>
                  <div class="<?php echo $class; ?> no-padder">
                      <a target="_blank" href="<?php $this->options->commentsFeedUrl(); ?>" title=""
                         data-toggle="tooltip" data-placement="top" data-original-title="<?php _me("评论RSS订阅") ?>">
                          <span class="left-bottom-icons block"><i data-feather="message-square"></i></span>
                          <small class="text-muted"><?php _me("评论") ?></small>
                      </a>
                  </div>
          </div>
          <?php endif; ?>

      </div><!--.aside-wrap-->
  </aside>
<!-- content -->

<div id="content" class="app-content">
    <div class="resize-pane">
        <div id="trigger_left_content" class="trigger_content"><div class="trigger_drag_content"></div></div>
        <div id="trigger_left_button" data-placement="right" data-toggle="tooltip" data-original-title="<?php _me("点击展开左侧边栏");?>" class="normal-widget resize-pane-trigger box-shadow-wrap-lg"><i data-feather="sidebar"></i></div>
    </div>
    <!--loading animate-->
    <?php echo Content::returnPjaxAnimateHtml(); ?>






