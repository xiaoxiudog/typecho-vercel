<?php
/**
 * github项目列表
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

?>

<?php $this->need('component/header.php'); ?>

	<!-- aside -->
	<?php $this->need('component/aside.php'); ?>
	<!-- / aside -->

<!-- <div id="content" class="app-content"> -->
   <a class="off-screen-toggle hide"></a>
   <main class="app-content-body <?php Content::returnPageAnimateClass($this); ?>">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
    <!--文章-->
     <div class="col center-part gpu-speed" id="post-panel">
         <div class="bg-light lter wrapper-md">
             <h1 class="entry-title m-n font-thin text-black l-h"><span class="title-icons"><i data-feather="github"></i></span><?php _me("项目展示") ?></h1>
             <?php if (trim($this->fields->intro) !== ""): ?>
                 <div class="entry-meta text-muted  m-b-none small post-head-icon"><?php echo $this->fields->intro; ?></div>
             <?php endif ?>
         </div>
      <div class="wrapper-md">
       <!--博客文章样式 begin with .blog-post-->
       <div id="postpage" class="blog-post">
        <article class="single-post panel">
        <!--文章页面的头图-->
            <?php echo Content::exportHeaderImg($this); ?>
         <!--文章内容-->
         <div id="post-content" class="wrapper-lg">
          <div class="l-h-2x row">
              <?php Content::postContentHtml($this,$this->user->hasLogin()); ?>
              <small class="text-muted letterspacing github_tips"></small>
              <!--github--->
              <div class="github_page">
                  <nav class="loading-nav text-center m-t-lg m-b-lg">
                      <p class="infinite-scroll-request"><i class="animate-spin fontello
                      fontello-refresh"></i><?php _me("Loading……") ?></p>
                  </nav>
                  <nav class="error-nav hide text-center m-t-lg m-b-lg">
                      <p class="infinite-scroll-request"><i class="glyphicon
                            glyphicon-refresh"></i>加载失败！尝试重新加载</p>
                  </nav>
              </div>
          </div>
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

       <?php

       $githubUser = $this->fields->github;

       if ($githubUser == "" || $githubUser == null){
           echo '<script>$(".github_tips").text("请填写正确的github用户名，主题检查github用户为空或者错误，已经切换ihewro用户仓库项目。");</script>';
           $githubUser = 'ihewro';
       }

       ?>
       <script type="text/javascript">
            var githubItemTemple = '<div class="col-xs-12 col-sm-6">'+
                '<div class="panel b-light {BG_COLOR}">\n' +
                '        <div class="panel-body"><div class="github_language">{PROJECT_LANGUAGE}</div>' +
                '          \n' +
                '          <div class="clear">\n' +
                '            <span class="text-ellipsis font-thin h3">{REPO_NAME}</span>\n' +
                '            <small class="block m-sm"><i class="iconfont icon-star m-r-xs"></i>{REPO_STARS} stars / <i class="iconfont icon-fork"></i> {REPO_FORKS} forks</small>\n' +
                    '<small class="text-ellipsis block text-muted">{REPO_DESC}</small>'+
                '<a target="_blank" href="{REPO_URL}" class="m-sm btn btn-rounded btn-sm lter btn-{BUTTON_COLOR}"><i class="glyphicon glyphicon-hand-up"></i>访问</a>' +
                '          </div>\n' +
                '        </div>\n' +
                '      </div>'+
                '</div>';


           var open = function(){

               var handleGithub = function(){
                   var repoContainer = $('.github_page');
                   var loadingContainer = repoContainer.find(".loading-nav");
                   var errorContainer = repoContainer.find(".error-nav");
                   var countContainer = $(".github_tips");
                   var colors = ["light","info","dark","success","black","warning","primary","danger"];
                   $.get("https://api.github.com/users/<?php echo $githubUser; ?>/repos",function(result){
                       if(result){
                           loadingContainer.addClass("hide");
                           var ul = $("<div class='raw'><div class='col-md-12'><div class=\"row row-sm text-center " +
                               "github_contain" +
                               "\"></div></div></div>");
                           repoContainer.append(ul);
                           var contentContainer = $(".github_contain");
                           for(var i in result){
                               var repo = result[i];
                               repo.updated_at = repo.updated_at.substring(0,repo.updated_at.lastIndexOf("T"));
                               if (repo.language == null){
                                   repo.language = "未知";
                               }
                               //匹配替换
                               var item = githubItemTemple.replace("{REPO_NAME}",repo.name)
                                   .replace("{REPO_URL}",repo.html_url)
                                   .replace("{REPO_STARS}",repo.stargazers_count)
                                   .replace("{REPO_FORKS}",repo.forks_count)
                                   .replace("{REPO_DESC}",repo.description)
                                   .replace("{BG_COLOR}","bg-"+colors[i % 8])
                                   .replace("{BUTTON_COLOR}",colors[(i) % 8])
                                   .replace("{PROJECT_LANGUAGE}",repo.language);
                               contentContainer.append(item);
                           }
                       }else{
                           errorContainer.removeClass("hide");
                       }
                   });
               };

               return {
                   init : function(){
                       handleGithub();
                   }
               }
           };

           $(open().init);

       </script>
   </main>


    <!-- footer -->
	<?php $this->need('component/footer.php'); ?>
  	<!-- / footer -->

