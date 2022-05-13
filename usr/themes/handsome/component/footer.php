<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php  $dark_setting= ""; if (@$this->options->dark_setting == "") $dark_setting = "light" ;elseif
(@$this->options->dark_setting == "auto" || @$this->options->dark_setting == "time" ||
    $this->options->dark_setting == "compatible")  $dark_setting = "auto"; else  $dark_setting =
$this->options->dark_setting;  ?>
</div><!-- /content -->

<!--right panel-->
<?php if (@in_array('showSettingsButton', Utils::checkArray($this->options->featuresetup)) || $dark_setting == "auto")
    : ?>
    <script type="text/template" id="tmpl-customizer">
        <div class="settings panel panel-default setting_body_panel right_panel" aria-hidden="true">
            <button class="rightSettingBtn fix-padding btn btn-default pos-abt border-radius-half-left"
                    data-toggle="tooltip" data-placement="left" data-original-title="<?php (!in_array('showSettingsButton',
                    $this->options->featuresetup) && $this->options->dark_setting == "auto") ?_me("夜/日间模式") :
                _me("外观设置") ?>"
                    data-toggle-class=".settings=active, .settings-icon=animate-spin-span,.tocify-mobile-panel=false">
                  <span class="settings-icon"><i width="13px" height="13px"
                                                 data-feather="settings"></i></span>
            </button>
            <div class="panel-heading">
                <button class="mode-set pull-right btn btn-xs btn-rounded btn-danger " name="reset" data-toggle="tooltip"
                        data-placement="left" data-original-title="<?php _me("恢复默认值") ?>" ><?php _me("重置") ?></button>
                <?php

                (!in_array('showSettingsButton',
                        $this->options->featuresetup) && $dark_setting == "auto") ?_me("夜/日间模式") :
                    _me("设置") ?>

            </div>
            <div class="setting_body">
                <div class="panel-body">
                    <# for ( var keys = _.keys( data.sections.settings ), i = 0, name; keys.length > i; ++i ) { #>
                    <div<# if ( i !== ( keys.length - 1 ) ) print( ' class="m-b-sm"' ); else print(' id="mode_set" class="mode_set"')
                    #>>
                    <label class="i-switch bg-info pull-right">
                        <input type="checkbox" name="{{ keys[i] }}" <#
                        print( ' value="'+handsome_UI.mode+data
                        .defaults[keys[i]]+'"' )
                        if ((data.defaults[keys[i]]=="auto" && handsome_UI.mode =="dark") || data
                        .defaults[keys[i]] == true) print( ' checked="checked"' );
                        #> />
                        <i></i>
                    </label>

                    <span> <# if(data.defaults[keys[i]]=="auto") print(LocalConst.DARK_MODE_AUTO); else if(data
                      .sections.settings[keys[i]] == LocalConst.DARK_MODE) {print(LocalConst.DARK_MODE_FIXED)
                      }else print(data.sections.settings[keys[i]]);#></span>
                    <# if ( i == ( keys.length - 1 ) ) {print( ' <small id="auto_info"'); if(data.defaults[keys[i]]!=="auto") print(' style="display:none"') ;print('><i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="bottom" data-original-title="<?php if ($this->options->dark_setting == "auto") _me("网站深色模式自动依据您的设备关于深色模式的设置进行切换");elseif ($this->options->dark_setting == "time") _me("网站深色模式依据时间自动切换，%s 时至 %s 时为夜间",$this->options->darkTime,$this->options->dayTime);elseif ($this->options->dark_setting =="compatible") _me("如果您的设备为深色，网站模式为深色，如果您的设备为亮色，则自动根据时间自动切换，%s 时至 %s 时为夜间",$this->options->dayTime,$this->options->darkTime)?>"></i></small>')}; #>
                </div>
                <# } #>
            </div>
            <div class="wrapper b-t b-light bg-light lter r-b">
                <div class="row row-sm">
                    <div class="col-xs-4">
                        <#
                        _.each( data.sections.colors, function( color, i ) {
                        var newColumnBefore = ( i % 5 ) === 4;
                        #>
                        <label class="i-checks block<# if ( !newColumnBefore ) print( ' m-b-sm' ); #>">
                            <input type="radio" name="color" value="{{ i }}"<# if ( data.defaults['color'] === i ) print( ' checked="checked"' ); #> />
                            <span class="block bg-light clearfix pos-rlt">
								<span class="active pos-abt w-full h-full bg-black-opacity text-center">
									<i class="fontello fontello-check text-md text-white m-t-xs"></i>
								</span>
								<b class="{{ color.navbarHeader }} header"></b>
								<b class="{{ color.navbarCollapse }} header"></b>
								<b class="{{ color.aside.replace( ' b-r', '' ) }}"></b>
							</span>
                        </label>
                        <#
                        if ( newColumnBefore && ( i + 1 ) < data.sections.colors.length )
                        print( '</div><div class="col-xs-4">' );
                        } );
                        #>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </script>
<?php else: ?>
    <style>
        .topButton>.btn{
            top: 0;
        }
    </style>
<?php endif; ?>
<div class="topButton panel panel-default">
    <button id="goToTop" class="fix-padding btn btn-default rightSettingBtn  pos-abt hide
          border-radius-half-left"
            data-toggle="tooltip" data-placement="left" data-original-title="<?php _me("返回顶部") ?>">
        <span class="settings-icon2"><i width="13px" height="13px" data-feather="corner-right-up"></i></span>
        <!--              <i class="fontello fontello-chevron-circle-up" aria-hidden="true"></i>-->
    </button>
</div>
<?php if (IS_TOC): ?>
    <div class="tag_toc_body hide">
        <div class="tocify-mobile-panel panel panel-default setting_body_panel right_panel" aria-hidden="true">
            <button class="fix-padding rightSettingBtn border-radius-half-left btn btn-default pos-abt "
                    data-toggle="tooltip"
                    data-placement="left"
                    data-original-title="<?php _me("目录") ?>" data-toggle-class=".tocify-mobile-panel=active,
                    .settings=false">
                <span class="settings-icon2"><i width="13px" height="13px" data-feather="list"></i></span>
            </button>
            <div class="panel-heading"><?php _me("文章目录") ?></div>
            <div class="setting_body toc-mobile-body">
                <div class="panel-body">
                    <div id="tocTree" class="tocTree overflow-y-auto"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<footer id="footer" class="app-footer" role="footer">
<!--    <div class="wrapper bg-light">-->
<!--        <a class="pull-right hidden-xs text-ellipsis">-->
<!--            <span class="label badge" style="margin-right: 10px;">Powered by typecho</span>-->
<!--            <a href="https://www.ihewro.com/archives/489/" class="label badge">Theme by handsome</a>-->
<!--        </a>-->
<!--        <a href="" target="_blank" rel="noopener noreferrer" class="label badge" data-toggle="tooltip" data-original-title="">Copyright ©2021</a>-->
<!--    </div>-->
    <div class="wrapper bg-light">
      <span class="pull-right hidden-xs text-ellipsis">
      <?php $this->options->BottomInfo();
      // 可以去除主题版权信息，最好保留版权信息或者添加主题信息到友链，谢谢你的理解
      ?>
      Powered by <a target="_blank" href="http://www.typecho.org">Typecho</a>&nbsp;|&nbsp;Theme by <a target="_blank"
                                                                                                      href="https://www.ihewro.com/archives/489/">handsome</a>
      </span>
        <span class="text-ellipsis">&copy;&nbsp;<?php echo date("Y"); ?> Copyright&nbsp;<?php
            $this->options->BottomleftInfo(); ?></span>
    </div>

</footer>



  </div><!--end of .app app-header-fixed-->


<?php $this->footer(); ?>


<!--定义全局变量-->

<?php if ( $dark_setting != "auto"): ?>
    <style>
        #mode_set{
            display: none;
        }
    </style>
<?php endif; ?>

<?php if (Utils::getExpertValue("setting_only_day_mode")=== true || (!in_array('showSettingsButton',
            $this->options->featuresetup) && $dark_setting == "auto")): ?>
<style>
    .setting_body .panel-body .m-b-sm{
        display: none;
    }
    .setting_body .panel-body ~ .wrapper{
        display: none;
    }
</style>

<?php endif; ?>
<!--主题核心js-->

<?php if (Content::getPostParseWay() == "vditor"): ?>
<script id="vditor_method" src="<?php echo Utils::getLocalCDN("vditor/dist/js/lute","vditor","vditor") ?>/dist/method.min.js"></script>
<?php endif; ?>



<?php if (CDN_Config::DEVELOPER_DEBUG == 1): ?>

    <?php Utils::getFileList("origin","js") ?>
    <?php Utils::getFileList("core","js") ?>

<?php else: ?>

    <script src="<?php echo STATIC_PATH ?>js/<?php Utils::getFileName("function.min.js") ?>?v=<?php echo
        Handsome::version
        .Handsome::$versionTag
    ?>"></script>

    <script src="<?php echo STATIC_PATH ?>js/<?php Utils::getFileName("core.min.js") ?>?v=<?php echo Handsome::version.Handsome::$versionTag
    ?>"></script>

<?php endif; ?>


<script>
    $(function () {
        if ('serviceWorker' in navigator) {
            if (LocalConst.USE_CACHE) {
                navigator.serviceWorker.addEventListener('controllerchange', function (ev) {
                    try {
                        if (LocalConst.SERVICE_WORKER_INSTALLED){
                            $.message({
                                title:"<?php _me("检测到本地缓存需要更新"); ?>",
                                message:"<a href='#' onclick='window.location.reload();'><?php _me('点击刷新页面'); ?></a><?php _me
                                ('更新本地缓存'); ?>",
                                type:'warning',
                                time: '300000'
                            });
                        }else{
                            console.log("controllerchange:first sw install success");
                        }
                    }catch (e) {
                        console.log("controllerchange error",e);
                    }
                });
            }
        }
    })
</script>

<!--主题组件js加载-->
<?php if (PJAX_ENABLED): ?>
    <script src="<?php echo STATIC_PATH;?>js/features/jquery.pjax.min.js" type="text/javascript"></script>
<?php endif; ?>

<?php if (!empty($this->options->featuresetup) && in_array('smoothscroll', Utils::checkArray(
        $this->options->featuresetup)) && Device::isWindowsAboveVista() && Device::is('Chrome', 'Edge')): ?>
    <!--平滑滚动组件-->
    <script src="<?php echo STATIC_PATH ?>js/features/SmoothScroll.min.js"></script>
<?php endif; ?>


<!--pjax动画组件-->
<?php if($this->options->pjaxAnimate !== "default"): ?>
    <script>
        window.paceOptions = {
            ajax: {
                ignoreURLs: ['/.*handsome-meting-api.*/']
            },//忽视音乐加载的ajax请求
            restartOnPushState: false,
            startOnPageLoad: false,
            restartOnRequestAfter: false
        };
    </script>
    <script src="<?php echo STATIC_PATH ?>js/features/pace.min.js"></script>

<?php if(!($this->options->pjaxAnimate == "default" || $this->options->pjaxAnimate == "" || $this->options->pjaxAnimate == "whiteRound" || $this->options->pjaxAnimate == "customise")): ?>
    <link href="<?php echo STATIC_PATH ?>css/features/pjax/pace-theme-<?php $this->options->pjaxAnimate ()?>.min.css"
          rel="stylesheet">
<?php endif; ?>

    <?php if (trim($this->options->progressColor) !== ""): ?>
        <style>
            <?php if (!($this->options->pjaxAnimate == "default" || $this->options->pjaxAnimate == "" || $this->options->pjaxAnimate == "whiteRound" || $this->options->pjaxAnimate == "customise") && $this->options->isPjaxShowMatte == '0'): ?>
            .pace-running #content,.pace-running #aside{
                opacity: 0.4;
            }

            .pace-done #content,.pace-done #aside{
                opacity: 1;
            }
            <?php endif; ?>
            <?php echo Content::returnPjaxAnimateCss(); ?>
        </style>
    <?php endif; ?>
<?php endif; ?>

<?php if (@in_array('lazyload', Utils::checkArray($this->options->featuresetup))): ?>
    <script src="<?php echo STATIC_PATH ?>js/features/lazyload.min.js"></script>
    <script>

        $(".lazy").lazyload({
            effect: "fadeIn",
            threshold: "500"
        });
    </script>
<?php endif; ?>




<?php if ($this->options->thumbArrangeStyle == "water_fall"): ?>
    <script src="<?php echo STATIC_PATH ?>js/features/macy.min.js"></script>
    <script>
        handsome_enhance.initWaterFall();
    </script>
<?php endif; ?>

<?php if (@in_array("sreenshot",$this->options->featuresetup)): ?>
<!--截图插件-->
<script src="<?php echo STATIC_PATH ?>js/features/html2canvas.min.js"></script>
<?php endif; ?>
<!--主题组件js加载结束-->

<!--用户自定义js-->
<script type="text/javascript">
    <?php $this->options->customJs() ?>
</script>

<?php $this->options->bottomHtml(); ?>


</body>
</html><!--html end-->
