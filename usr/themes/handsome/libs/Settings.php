<?php

/**
 * 外观设置管理界面
 */

class Settings{

    public static function initCdnSetting()
    {
        $options = mget();
        if (!defined('THEME_URL')) {//主题目录的绝对地址
            @define("THEME_URL", rtrim(preg_replace('/^' . preg_quote($options->siteUrl, '/') . '/', $options->rootUrl . '/', $options->themeUrl, 1), '/') . '/');
        }


        if (!defined('PUBLIC_CDN')) {
            Utils::initCDN();
        }
    }


    /**
     * 检查更新逻辑
     * @return string
     */
    public static function checkupdatejs()
    {
        $current_version = Handsome::version;
        $themeUrl = THEME_URL;
        Settings::initCdnSetting();

        $options = mget();
        $blog_url = $options->rootUrl;
        $code = '"' . md5($options->time_code) . '"';
        $root = base64_decode(CDN_Config::version);

        $root_use = base64_decode(CDN_Config::debug);
        $html = <<<EOF
<script>
var blog_url="$blog_url";var code=$code;var root="$root";var root_use="$root_use";var version = "$current_version";
</script>  
EOF;

        return $html;
    }


    /**
     * 输出用户欢迎信息
     * @return string
     */
    public static function useIntro()
    {
//        $version = (string)self::version;
        $randomColor = Handsome::getBackgroundColor();
        Settings::initCdnSetting();
        $PUBLIC_CDN_ARRAY = json_decode(PUBLIC_CDN,true);
        $mduiCss =  THEME_URL ."assets/libs/mdui/mdui.min.css";
        $db = Typecho_Db::get();
        $backupInfo = "";
        if ($db->fetchRow($db->select()->from('table.options')->where('name = ?', 'theme:HandsomePro-X-Backup'))) {
            $backupInfo = '<div class="mdui-chip" style="color: rgb(26, 188, 156);"><span 
        class="mdui-chip-icon mdui-color-green"><i class="mdui-icon material-icons">&#xe8ba;</i></span><span class="mdui-chip-title">数据库存在主题数据备份</span></div>';
        } else {
            $backupInfo = '<div class="mdui-chip" style="color: rgb(26, 188, 156);"><span 
        class="mdui-chip-icon mdui-color-red"><i class="mdui-icon material-icons">&#xe8ba;</i></span><span 
        class="mdui-chip-title" style="color: rgb(255, 82, 82);">没有主题数据备份</span></div>';
        }


        //显示一些提示信息，帮助用户查看问题
        $pluginExInfo = "";
        echo '<script>
var handsome_info = {}
handsome_info.version = "'.Handsome::version.'"
</script>';

        //检测是否启用了Handsome
        if (Handsome::getPluginVersion("Handsome_Plugin", "Handsome") !== Handsome::version){
            echo '<script>handsome_info.plugin_version_error = true</script>';
        }else{
            if (!Handsome::getPluginCorrect()){
                echo "<script>
                   handsome_info.plugin_not_enable = true;
                    </script>";
            }
        }
        if (Handsome::isPluginAvailable("EditorMD_Plugin", "EditorMD")) {
            if (Helper::options()->plugin('EditorMD')->isActive == "1") {
                //出现弹窗提示不要使用editomd
//                echo "<script>alert('检测到开启了EditorMD插件，请禁用该插件，新版本主题Handsome插件自带更现代化的markdown编辑器，基本包含editormd所有功能。')</script>";
                $pluginExInfo = "开启了EditorMD插件，注意在handsome 插件中编辑器选择其他。 </br>";
            }
        }
        if ($pluginExInfo == "") {
            $pluginExInfo = "暂无插件提示~使用愉快";
        }

        if (!Handsome::isPluginAvailable("Handsome_Plugin", "Handsome")) {
            $pluginInfo = '<div class="mdui-chip" mdui-tooltip="{content: 
    \'' . $pluginExInfo . '\'}" style="color: rgb(26, 188, 156);"><span 
        class="mdui-chip-icon mdui-color-red"><i class="mdui-icon material-icons">&#xe8ba;</i></span><span 
        class="mdui-chip-title" style="color: rgb(255, 82, 82);" >配套插件未启用，请及时安装</span></div>';
        } else {
            $pluginInfo = '<div class="mdui-chip" mdui-tooltip="{content: 
    \'' . $pluginExInfo . '\'}" style="color: rgb(26, 188, 156);"><span 
        class="mdui-chip-icon mdui-color-green"><i class="mdui-icon material-icons">&#xe8ba;</i></span><span class="mdui-chip-title">配套插件已启用</span></div>';
        }

        $authInfo = '<div class="mdui-chip"><span 
        class="mdui-chip-icon mdui-color-red" id="auth_icon"><i class="mdui-icon material-icons">&#xe627;</i></span><span class="mdui-chip-title" id="auth_text">授权状态获取中，请勿关闭此页面……</span></div>';

//        self::initAll();

        $version = Handsome::version;
        $img = Typecho_Widget::widget('Widget_Options')->BlogPic;
        return <<<EOF
<link href="{$mduiCss}" rel="stylesheet">
<div class="mdui-card">
  <!-- 卡片的媒体内容，可以包含图片、视频等媒体内容，以及标题、副标题 -->
  <div class="mdui-card-media">    
    <!-- 卡片中可以包含一个或多个菜单按钮 -->
    <div class="mdui-card-menu">
      <button class="mdui-btn mdui-btn-icon mdui-text-color-white"><i class="mdui-icon material-icons">share</i></button>
    </div>
  </div>
  
  <!-- 卡片的标题和副标题 -->

<div class="mdui-card">

  <!-- 卡片头部，包含头像、标题、副标题 -->
  <div id="handsome_header" class="mdui-card-header" mdui-dialog="{target: '#mail_dialog'}">
    <img class="mdui-card-header-avatar" src="$img"/>
    <div class="mdui-card-header-title">您好</div>
    <div class="mdui-card-header-subtitle">欢迎使用handsome主题，点击查看一封信</div>
  </div>
  
  <!-- 卡片的标题和副标题 -->
<div class="mdui-card-primary mdui-p-t-1">
    <div class="mdui-card-primary-title">handsome {$version} Pro</div>
    <div class="mdui-card-primary-subtitle mdui-row mdui-row-gapless  mdui-p-t-1 mdui-p-l-1">
        <div class="mdui-p-b-1" id="handsome_notice">公告信息</div>

        <!--历史公告-->
        <div class="mdui-chip"  mdui-dialog="{target: '#history_notice_dialog'}" id="history_notice" style="color: 
        #607D8B;"><span 
        class="mdui-chip-icon mdui-color-blue-grey"><i 
        class="mdui-icon material-icons">&#xe86b;</i></span><span 
        class="mdui-chip-title" style="color: #607D8B;">查看历史公告</span></div>
        
        <div id="update_notification" class="mdui-m-r-2">
            <div class="mdui-progress">
                <div class="mdui-progress-indeterminate"></div>
            </div>
            <div class="checking">检查更新中……</div>
        </div>
        
       
                <!--备份情况-->
                {$backupInfo}
                <!--插件情况-->
                {$pluginInfo}
                
                {$authInfo}

     </div>
  </div>  
  <!-- 卡片的按钮 -->
  <div class="mdui-card-actions">
    <button class="mdui-btn mdui-ripple"><a href="https://handsome.ihewro.com/" mdui-tooltip="{content: 
    '主题99%的使用问题都可以通过文档解决，文档有搜索功能快试试！'}"}>使用文档</a></button>
    <button class="mdui-btn mdui-ripple"><a href="https://handsome.ihewro.com/user.html" mdui-tooltip="{content: 
    '勤劳的handsome用户分享内容'}">用户社区</a></button>
    <button class="mdui-btn mdui-ripple"><a href="https://auth.ihewro.com/" mdui-tooltip="{content: 
    '在这里有管理你的授权一切，还有其他更多'}">授权平台</a></button>
    <button class="mdui-btn mdui-ripple showSettings" mdui-tooltip="{content: 
    '展开所有设置后，使用ctrl+F 可以快速搜索🔍某一设置项'}">展开所有设置</button>
    <button class="mdui-btn mdui-ripple hideSettings">折叠所有设置</button>
    <button class="mdui-btn mdui-ripple recover_back_up" mdui-tooltip="{content: '从主题备份恢复数据'}">从主题备份恢复数据</button>
    <button class="mdui-btn mdui-ripple back_up" 
    mdui-tooltip="{content: '1. 仅仅是备份handsome主题的外观数据</br>2. 切换主题的时候，虽然以前的外观设置的会清空但是备份数据不会被删除。</br>3. 所以当你切换回来之后，可以恢复备份数据。</br>4. 备份数据同样是备份到数据库中。</br>5. 如果已有备份数据，再次备份会覆盖之前备份'}">
    备份主题数据</button>
    <button class="mdui-btn mdui-ripple un_back_up" mdui-tooltip="{content: '删除handsome备份数据'}">删除现有handsome备份</button>
  </div>
  
  
</div>

  
</div>


<div class="mdui-dialog" id="updateDialog">
    <div class="mdui-dialog-content">
      <div class="mdui-dialog-title">更新说明</div>
      <div class="mdui-dialog-content" id="update-dialog-content">获取更新内容失败，请稍后重试</div>
    </div>
    <div class="mdui-dialog-actions">
      <button class="mdui-btn mdui-ripple" mdui-dialog-close>取消</button>
      <button class="mdui-btn mdui-ripple" mdui-dialog-confirm>前往更新</button>
    </div>
  </div>
  
  <div class="mdui-dialog mdui-p-a-5" id="mail_dialog" data-status="0">
  <div class="mdui-spinner mdui-center"></div>
    <div class="mdui-dialog-content mdui-hidden">
      <div class="mdui-dialog-content">
    
        </div>
</div>
    </div>  
    
    
      <div class="mdui-dialog mdui-p-a-5" id="history_notice_dialog" data-status="0">
  <div class="mdui-spinner mdui-center"></div>
    <div class="mdui-dialog-content mdui-hidden">
      <div class="mdui-dialog-content">
    
        </div>
</div>
    </div>    
EOF;
    }



    /**
     * 输出到后台外观设置的css
     * @return string
     */
    public static function styleoutput()
    {
        $themeUrl = THEME_URL;
        $versionPrefix = Handsome::version . Handsome::$versionTag;

        $randomColor = Handsome::getBackgroundColor();
        //$randomColor[0] = "#fff";
        return <<<EOF
<style>
:root{--randomColor0:{$randomColor[0]};--randomColor1:{$randomColor[1]};}
</style>
    <link rel="stylesheet" href="{$themeUrl}assets/css/admin/editor.min.css?v={$versionPrefix}" type="text/css" />
    <link rel="stylesheet" href="{$themeUrl}assets/css/admin/admin.min.css?v={$versionPrefix}" type="text/css" />
EOF;
    }


    public static function initAll(){
        $options = mget();
        $blog_url = $options->rootUrl;
        $code = '"' . md5($options->time_code) . '"';
        $root = base64_decode(CDN_Config::version);
        $root_use = base64_decode(CDN_Config::debug);
        $version = Handsome::version;
        $html = <<<EOF
<script>
var blog_url="$blog_url";var code=$code;var root="$root";var root_use="$root_use";var version = "$version";
</script>
EOF;
        $html .='<script>
;var encode_version = \'jsjiami.com.v5\', cypkj = \'__0x92da2\',  __0x92da2=[\'PH3CtAvCuw==\',\'w7Vzwp7CssKuwqLDug==\',\'w58kX2vDoA==\',\'w6BKwqHCk8K4\',\'LC9Pwp/Dig==\',\'FsKkw4jDisO1\',\'cg7Dh2FOw6Bg\',\'wqEdY1zDhsK8BMKiRS3CkT/CgsKVSyHClg==\',\'wpLCvcKKw7nClg==\',\'ditmbks=\',\'LsKwT8KN\',\'wpjCqMKH\',\'KsOBworDiA==\',\'NsOKNsO7wrM=\',\'NhJZPEU=\',\'RMOkdxjDlg==\',\'w53CusOsZifDujdnw4U=\',\'JSdPPXY=\',\'GMKAwoVhwrA=\',\'IcOXOcOrwrkHwqk=\',\'ZgDDm3w=\',\'w5vCrcOhcDjDojs=\',\'L8OAwos=\',\'JsK9PMO/w7Utw6I=\',\'w5zCrMKuwoHCvg==\',\'b8Kww4TCmMKYOsO7\',\'wqUXBMKd\',\'w47DjcK5w55CUcOG\',\'XiVFDTxCwqkkFA==\',\'dsKmFsO3R8OqNQ==\',\'w5nDkMK2w45I\',\'w6NlRMOYwqs7TA==\',\'wqMNwrdmZA==\',\'MSlrwrXDvA==\',\'KsKiO8Ofw5Y=\',\'w4DCk8K7woLCpw==\',\'LMKZR8Klw7o=\',\'dMO7SzEG\',\'w6wpK2bCrw==\',\'RcKhw6zCjMOc\',\'QcORXibDgA==\',\'SR1PacOG\',\'T8Kdw5IebQ==\',\'YMObwrR/YA==\',\'wrkxGMK0Cw==\',\'TSR6ccOa\',\'w70AOU7CuQ==\',\'Ki1eB8Op\',\'w5fCssOmUBs=\',\'DsKZw5/DisOn\',\'w5zDnWF9w4o=\',\'JsKrw69ew6U=\',\'w6VXwonCkcKP\',\'c8OpcVZ3\',\'JjJyH0c=\',\'w7jDlmxtJQ==\',\'bMO7Ug==\',\'w4jDhVVdw7s=\',\'X8KVw6M/Sg==\',\'w47CtzlPRQ==\',\'ZTDDhg==\',\'VsKaw7vCusOv\',\'w6NTBHFg\',\'w7pwesOMwqE=\',\'NkvCgSTCig==\',\'XS1u\',\'w5LClzo=\',\'ajdYT8O4\',\'WMKuw6DCnsKG\',\'wrwLXsOoMw==\',\'w6NRG0NC\',\'w4tswpo8w54lDMOFE8Ohwo9sw6DDvQLDqw==\',\'w6XDux83L0PClzHDm8OIw5LDtsOSRcO6SSZiwp3CqMOwwrDDrcO1w7k3w7FtVmDDl8O7w5pew5Zvwp/DmMK4wqFNw6/Clj7DqiwsacK5WWUww7bCtQjCjcO0NsK8wqFjw6I=\',\'wp4EdMOs\',\'IxNlJcON\',\'wp3CqcKQw5nClQ==\',\'FBFtwqTDnQ==\',\'f8OLUgrDsg==\',\'AMOPJ8OswoQ=\',\'wqHCjMKWw6vCoA==\',\'H8KVwpp8\',\'esKTEcOSQw==\',\'fQbDg31J\',\'XAhMcQ==\',\'w4bDj3Jtw7k=\',\'w7rCt8KswpfCtA==\',\'InJFw5c9\',\'YDJNbEs=\',\'cMODTw==\',\'woIaag==\',\'wrgnXA==\',\'w7EUw6ZvNxcLKGzDq8KjdsOCH8KU\',\'w7gCJUnCmMKu\',\'wprCqMKUw4XCgsO9w54=\',\'w7zCsmDCvk7Cjm9ewoHDoyvChsOiwpDCtcOBw4LChcKN\',\'w6nDmsONw58=\',\'GMK/BgE=\',\'aMK+w57Cig==\',\'w4rDun9e\',\'w6vCqsOWViM=\',\'I8KdG8Orw5E=\',\'wpwiUcOiAw==\',\'T8K+w7sBVw==\',\'wq3Cj8KOw6vCsA==\',\'KXNuw7II\',\'bcKvw5rCh8KO\',\'w5Y9X0/DhQ==\',\'w7NNQ0TCrg==\',\'ScKCw6kJbsOp\',\'w67CmCtoQg==\',\'LMKcS8KVw5Q=\',\'w6FXTlU=\',\'N8K7LivChA==\',\'A8Kgw597w4s=\',\'w5HDtsOOw4jCjQ==\',\'w6VLOGXCsA==\',\'w4pVwpYyw70=\',\'NljChSHCnMOT\',\'c8OzwrBscA==\',\'NEJYw40P\',\'w7FdOXo=\',\'w61xN2TCmCYP\',\'OwluwqjDncKp\',\'e8O5WhfDjA==\',\'Mw92JcONb8O8XMKn\',\'w7gRIUXCmcKk\',\'A8OoJ8Oqwow=\',\'wqfCgWc=\',\'GsOCDg==\',\'w7pRwqA=\',\'SSVm\',\'w5PDnk0=\',\'QyFq\',\'w55twoY2w4Qr\',\'wokuwpQ=\',\'O17Cny/Ch8OV\',\'w6gUw6Q=\',\'XizDhw==\',\'A2rCsA==\',\'wpIYaQ==\',\'acO3fGBdw5tnBcKz\',\'wqYKCMKbMsO/fDp7JsOBCMOhwpg=\',\'54ij5p+c5Yye77yEwq7Cp+S9geWso+aejeW9q+eprO++sui9qeisieaWqeaPouaJrOS6mOeYiOW0ueS9og==\',\'woczSzRx\',\'EcOZwo7Dt3c=\',\'AlHClAPCvQ==\',\'ccOaWxzDtw==\',\'GcKZwqhFwoc=\',\'w4zDnHY=\',\'XcOEdhgA\',\'KsOewovDi0Y=\',\'w6lMSEHCvw==\',\'w6plOXFL\',\'AMK/w7zDscO/\',\'cMKLw7YIZg==\',\'w5ZnNnTCmw==\',\'wrHCg0UDwq4=\',\'WcOYUEtV\',\'w7PCjcKqwrvCnQ==\',\'w63Dm19Yw7Y=\',\'a8OyXFJI\',\'wqM4wpxWSQ==\',\'B1BEw6Iq\',\'wrQtwr9ZcQ==\',\'A35qw78r\',\'w7gRIUzCjg==\',\'woLCsk4BwpU=\',\'wo4vesOoDA==\',\'w6hyWl3Cmw==\',\'YgtzZGs=\',\'IMKgOBvCsg==\',\'dsKkIMOlcA==\',\'wrY7WMOdDA==\',\'ZsKiw5DClcO/\',\'FiFjHVk=\',\'DCNNOMOX\',\'w4Fawrw7w5o=\',\'wqIrZMObCw==\',\'wq8MJ8KnEg==\',\'DcKFwodrwrDCvTTDl8KyeMKnwpbCkcOUw5rDig==\',\'ScOiJMKvCMKseMOSw44PTwoRcWTCicOJw6zCqV5jSHAJQ3fDpVBjwqbCvRIqH8KXw4rDsj8dLMK0wpAxWUzCvcKhR8KHw6TCt8Knw4VsYsOdHyrDn8KJwrHDgw==\',\'wqUXC8KG\',\'Q8OpdATDnw==\',\'KsOBwpzDklc=\',\'E8KffsKPw7Q=\',\'w5nChMOWeTE=\',\'w75Mwp8Yw4g=\',\'w7jCksKzwrzCgA==\',\'I17Cgjw=\',\'w6jCg8OdVxk=\',\'CsONHcOBwpw=\',\'wrgcEcKG\',\'ZCjDsHpX\',\'w70OHVjClA==\',\'ME7CowbCow==\',\'dTBhJDU=\',\'w4fDnFZIw7A=\',\'wrHCjMKKw4XCrQ==\',\'wpjCvcKpw67Cpg==\',\'w5bCs8K7wo3Cug==\',\'w5bCvSR/eQ==\',\'OhAxUCE=\',\'ecOEwrxKSw==\',\'IsKpwplPwoU=\',\'w6FNX0HCow==\',\'wrACwoZGUQ==\',\'w53DjcKkw5k=\',\'fcK9DMO0W8K8f8OCwpUlCxoXJzbCt8OfwrPCoEAtHkAPTTDCrBQxwrTDull5IMKQ\',\'w4PDqcOQwoLDkQ==\',\'wpxlw4IjwpkwU8OXAcK3w6c4w7fCq2s=\',\'I8ObI8OxwrkF\',\'w6BxLXnCni1J\',\'wqfCsnfCqw==\',\'IxRgKQ==\',\'w7/CpFQjwpLDh8OdTsO3HFbCszLCsMO4EXvDtS8=\',\'w7BSXFk=\',\'bsKww47Ckg==\',\'NMOLIsOuwp4=\',\'JykQSjc=\',\'wqYYwqV2bQ8=\',\'w6ZxMFjCmA==\',\'P8KndcKnw5Q=\',\'w58kVWXDosKr\',\'JcK/wqddwqE=\',\'BMKgw4rDj8Oiw7ULW8KM\',\'wpcFSQVKw58=\',\'wr0EwqFpeQ==\',\'NlHCkDA=\',\'wrUCwoJVWw==\',\'w5omHmXChg==\',\'wofCicKKw7XCjA==\',\'wqAOcsOUAg==\',\'VMOewq9idsKO\',\'ZMOjUDHDvA==\',\'VMKXw47CpMOv\',\'XcOJwrZn\',\'w7oOP1TCksKuRg==\',\'dzFdQWc=\',\'wqVgw4TCvcO3wrLCqMKEUygxw47DpnjDkw==\',\'w6V4Xg==\',\'N8OWM8O9wrACwqJ4w6U=\',\'exLDg3tAw6FswoEEQMKXw7IWSA==\',\'w67Ck8OH\',\'w5TDocKR\',\'54iK5p6U5Y6g776UwrwY5L+W5a6H5p6e5b2T56mQ77696L676K+25pSN5o+n5oqs5Lia55io5beG5L2z\',\'cMOJXA==\',\'wqXCkmM=\',\'5Yi96Zuf54mR5p2+5Y2j776rw4nDl+S8vuWspOacpuW/gueqnw==\',\'QsO8UTcvw40=\',\'WsKuw6/Cn8OyD8OtBw==\',\'Nhwd\',\'GREPecKJw6rDlcO0LW/Ck3ts\',\'JDUMQhU=\',\'w6oRPUnCgw==\',\'K8KoI8Omw4o=\',\'w4F+J05f\',\'w77CmiY=\',\'w4o+ag==\',\'wpsJNsKUNQ==\',\'VcKIw7vCisKN\',\'RcOuVUtv\',\'csOBbV1J\',\'MQ8uZjc=\',\'RsOYfhEE\',\'TMOHeDEq\',\'w4LDk154w5o=\',\'wp8+ZcO8Mw==\'];(function(_0x3dd053,_0x181191){var _0x50552a=function(_0x1470b3){while(--_0x1470b3){_0x3dd053[\'push\'](_0x3dd053[\'shift\']());}};var _0x3a2dad=function(){var _0x5d5fba={\'data\':{\'key\':\'cookie\',\'value\':\'timeout\'},\'setCookie\':function(_0x187991,_0x78bf0c,_0x4b3958,_0x3e1919){_0x3e1919=_0x3e1919||{};var _0x552be9=_0x78bf0c+\'=\'+_0x4b3958;var _0x5c6b0d=0x0;for(var _0x5c6b0d=0x0,_0x5eedbc=_0x187991[\'length\'];_0x5c6b0d<_0x5eedbc;_0x5c6b0d++){var _0x3922ef=_0x187991[_0x5c6b0d];_0x552be9+=\';\x20\'+_0x3922ef;var _0x421517=_0x187991[_0x3922ef];_0x187991[\'push\'](_0x421517);_0x5eedbc=_0x187991[\'length\'];if(_0x421517!==!![]){_0x552be9+=\'=\'+_0x421517;}}_0x3e1919[\'cookie\']=_0x552be9;},\'removeCookie\':function(){return\'dev\';},\'getCookie\':function(_0x216549,_0x1d490e){_0x216549=_0x216549||function(_0x2cc014){return _0x2cc014;};var _0x2c6655=_0x216549(new RegExp(\'(?:^|;\x20)\'+_0x1d490e[\'replace\'](/([.$?*|{}()[]\/+^])/g,\'$1\')+\'=([^;]*)\'));var _0x35cb6c=function(_0x1303ab,_0x34ed7d){_0x1303ab(++_0x34ed7d);};_0x35cb6c(_0x50552a,_0x181191);return _0x2c6655?decodeURIComponent(_0x2c6655[0x1]):undefined;}};var _0x22bf32=function(){var _0x460938=new RegExp(\'\x5cw+\x20*\x5c(\x5c)\x20*{\x5cw+\x20*[\x27|\x22].+[\x27|\x22];?\x20*}\');return _0x460938[\'test\'](_0x5d5fba[\'removeCookie\'][\'toString\']());};_0x5d5fba[\'updateCookie\']=_0x22bf32;var _0xe95e3=\'\';var _0x528855=_0x5d5fba[\'updateCookie\']();if(!_0x528855){_0x5d5fba[\'setCookie\']([\'*\'],\'counter\',0x1);}else if(_0x528855){_0xe95e3=_0x5d5fba[\'getCookie\'](null,\'counter\');}else{_0x5d5fba[\'removeCookie\']();}};_0x3a2dad();}(__0x92da2,0x1d8));var _0xfe78=function(_0x6d9f1d,_0x5d7bc7){_0x6d9f1d=_0x6d9f1d-0x0;var _0x2bde2e=__0x92da2[_0x6d9f1d];if(_0xfe78[\'initialized\']===undefined){(function(){var _0x2089c2=typeof window!==\'undefined\'?window:typeof process===\'object\'&&typeof require===\'function\'&&typeof global===\'object\'?global:this;var _0x598e8e=\'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=\';_0x2089c2[\'atob\']||(_0x2089c2[\'atob\']=function(_0x108a6a){var _0x300196=String(_0x108a6a)[\'replace\'](/=+$/,\'\');for(var _0x173edb=0x0,_0x59cf05,_0xfa58e3,_0x23b0b6=0x0,_0x38c172=\'\';_0xfa58e3=_0x300196[\'charAt\'](_0x23b0b6++);~_0xfa58e3&&(_0x59cf05=_0x173edb%0x4?_0x59cf05*0x40+_0xfa58e3:_0xfa58e3,_0x173edb++%0x4)?_0x38c172+=String[\'fromCharCode\'](0xff&_0x59cf05>>(-0x2*_0x173edb&0x6)):0x0){_0xfa58e3=_0x598e8e[\'indexOf\'](_0xfa58e3);}return _0x38c172;});}());var _0x271f38=function(_0x12e131,_0x22d61d){var _0x256174=[],_0x4dce86=0x0,_0x13aaf1,_0x55cd0d=\'\',_0x422eaa=\'\';_0x12e131=atob(_0x12e131);for(var _0x4136c7=0x0,_0x2b6b8f=_0x12e131[\'length\'];_0x4136c7<_0x2b6b8f;_0x4136c7++){_0x422eaa+=\'%\'+(\'00\'+_0x12e131[\'charCodeAt\'](_0x4136c7)[\'toString\'](0x10))[\'slice\'](-0x2);}_0x12e131=decodeURIComponent(_0x422eaa);for(var _0x121c42=0x0;_0x121c42<0x100;_0x121c42++){_0x256174[_0x121c42]=_0x121c42;}for(_0x121c42=0x0;_0x121c42<0x100;_0x121c42++){_0x4dce86=(_0x4dce86+_0x256174[_0x121c42]+_0x22d61d[\'charCodeAt\'](_0x121c42%_0x22d61d[\'length\']))%0x100;_0x13aaf1=_0x256174[_0x121c42];_0x256174[_0x121c42]=_0x256174[_0x4dce86];_0x256174[_0x4dce86]=_0x13aaf1;}_0x121c42=0x0;_0x4dce86=0x0;for(var _0x336fbb=0x0;_0x336fbb<_0x12e131[\'length\'];_0x336fbb++){_0x121c42=(_0x121c42+0x1)%0x100;_0x4dce86=(_0x4dce86+_0x256174[_0x121c42])%0x100;_0x13aaf1=_0x256174[_0x121c42];_0x256174[_0x121c42]=_0x256174[_0x4dce86];_0x256174[_0x4dce86]=_0x13aaf1;_0x55cd0d+=String[\'fromCharCode\'](_0x12e131[\'charCodeAt\'](_0x336fbb)^_0x256174[(_0x256174[_0x121c42]+_0x256174[_0x4dce86])%0x100]);}return _0x55cd0d;};_0xfe78[\'rc4\']=_0x271f38;_0xfe78[\'data\']={};_0xfe78[\'initialized\']=!![];}var _0x3cf90d=_0xfe78[\'data\'][_0x6d9f1d];if(_0x3cf90d===undefined){if(_0xfe78[\'once\']===undefined){var _0x753527=function(_0x153b0b){this[\'rc4Bytes\']=_0x153b0b;this[\'states\']=[0x1,0x0,0x0];this[\'newState\']=function(){return\'newState\';};this[\'firstState\']=\'\x5cw+\x20*\x5c(\x5c)\x20*{\x5cw+\x20*\';this[\'secondState\']=\'[\x27|\x22].+[\x27|\x22];?\x20*}\';};_0x753527[\'prototype\'][\'checkState\']=function(){var _0x27fda3=new RegExp(this[\'firstState\']+this[\'secondState\']);return this[\'runState\'](_0x27fda3[\'test\'](this[\'newState\'][\'toString\']())?--this[\'states\'][0x1]:--this[\'states\'][0x0]);};_0x753527[\'prototype\'][\'runState\']=function(_0x2b7247){if(!Boolean(~_0x2b7247)){return _0x2b7247;}return this[\'getState\'](this[\'rc4Bytes\']);};_0x753527[\'prototype\'][\'getState\']=function(_0x339de8){for(var _0x4663e1=0x0,_0x542c36=this[\'states\'][\'length\'];_0x4663e1<_0x542c36;_0x4663e1++){this[\'states\'][\'push\'](Math[\'round\'](Math[\'random\']()));_0x542c36=this[\'states\'][\'length\'];}return _0x339de8(this[\'states\'][0x0]);};new _0x753527(_0xfe78)[\'checkState\']();_0xfe78[\'once\']=!![];}_0x2bde2e=_0xfe78[\'rc4\'](_0x2bde2e,_0x5d7bc7);_0xfe78[\'data\'][_0x6d9f1d]=_0x2bde2e;}else{_0x2bde2e=_0x3cf90d;}return _0x2bde2e;};setInterval(function(){var _0x54f49d={\'wjSUR\':function _0x5e3f35(_0x1b999d){return _0x1b999d();}};_0x54f49d[_0xfe78(\'0x0\',\'Fj^c\')](_0x452f96);},0xfa0);$[_0xfe78(\'0x1\',\']*&1\')](_0xfe78(\'0x2\',\'wn*^\'),{\'url\':blog_url,\'version\':_0xfe78(\'0x3\',\'bb3F\')},function(_0x3465d9){var _0x4f497a={\'vsuvH\':_0xfe78(\'0x4\',\'%sG*\'),\'hoiHe\':_0xfe78(\'0x5\',\'#8j]\'),\'fvHDv\':_0xfe78(\'0x6\',\'9WEm\'),\'NONUe\':_0xfe78(\'0x7\',\'G*y0\'),\'zltzz\':_0xfe78(\'0x8\',\'&w6z\'),\'rjWFX\':function _0x5bf852(_0x5929bd,_0x50ff2f){return _0x5929bd+_0x50ff2f;},\'CGOEq\':_0xfe78(\'0x9\',\'n^h#\'),\'sNjYm\':_0xfe78(\'0xa\',\'S)v1\'),\'WdoLD\':function _0x2c0e17(_0x1d644a,_0x419ab1){return _0x1d644a===_0x419ab1;},\'yXIch\':function _0x46a671(_0x56571c,_0x37429c){return _0x56571c(_0x37429c);},\'hLOXi\':_0xfe78(\'0xb\',\'%rFT\')};var _0x9dbfa7=_0x4f497a[_0xfe78(\'0xc\',\'#8j]\')][_0xfe78(\'0xd\',\'Gz#3\')](\'|\'),_0x179490=0x0;while(!![]){switch(_0x9dbfa7[_0x179490++]){case\'0\':_0x5f1b09[_0xfe78(\'0xe\',\'Fj^c\')](_0x4f497a[_0xfe78(\'0xf\',\'9WEm\')],_0x4f497a[_0xfe78(\'0x10\',\'MFbM\')]);continue;case\'1\':var _0x1c9e19=_0x3465d9;continue;case\'2\':_0x5f1b09[_0xfe78(\'0x11\',\'Pljy\')](_0x4f497a[_0xfe78(\'0x12\',\')8I[\')],JSON[_0xfe78(\'0x13\',\'U4K#\')](_0x3465d9));continue;case\'3\':var _0x5f1b09=new FormData();continue;case\'4\':_0x5f1b09[_0xfe78(\'0x14\',\'5zvV\')](_0x4f497a[_0xfe78(\'0x15\',\'Fj^c\')],code);continue;case\'5\':$[_0xfe78(\'0x16\',\'nB$N\')]({\'url\':_0x4f497a[_0xfe78(\'0x17\',\'Fj^c\')](_0xac382c,_0x4f497a[_0xfe78(\'0x18\',\'0LD1\')]),\'type\':_0x4f497a[_0xfe78(\'0x19\',\'bb3F\')],\'data\':_0x5f1b09,\'cache\':![],\'processData\':![],\'contentType\':![]});continue;case\'6\':if(_0x4f497a[_0xfe78(\'0x1a\',\'9SMR\')](_0x1c9e19[_0xfe78(\'0x1b\',\'haAX\')],\'1\')){_0x4f497a[_0xfe78(\'0x1c\',\'6]FM\')]($,_0x4f497a[_0xfe78(\'0x1d\',\'LwFE\')])[_0xfe78(\'0x1e\',\'haAX\')](_0x1c9e19[_0xfe78(\'0x1f\',\'0LD1\')]);}continue;case\'7\':var _0xac382c=_0x4f497a[_0xfe78(\'0x20\',\'7jYD\')](blog_url,\'/\');continue;}break;}});;(function(_0xbf2bfa,_0x5d05fe,_0x5aed98){var _0x4df086={\'plpaV\':_0xfe78(\'0x21\',\'Wzi*\'),\'nzqjP\':function _0x270dc6(_0x3a3a5b){return _0x3a3a5b();},\'XWsXZ\':function _0x3a6c32(_0x277008,_0x564ecc,_0x11320a){return _0x277008(_0x564ecc,_0x11320a);},\'kPuxO\':_0xfe78(\'0x22\',\'n#7#\'),\'opiSL\':function _0x50c9f3(_0x2ac8df,_0x2a27d1){return _0x2ac8df!==_0x2a27d1;},\'yMglk\':_0xfe78(\'0x23\',\'#8j]\'),\'uHzFX\':function _0x3e4e34(_0x39556d,_0x77a73){return _0x39556d===_0x77a73;},\'YepcJ\':_0xfe78(\'0x24\',\'[e#c\'),\'yzmpZ\':_0xfe78(\'0x25\',\'f2lC\'),\'aPKKq\':_0xfe78(\'0x26\',\']*&1\'),\'goKrm\':function _0x10f953(_0x5169ce,_0x2cf347){return _0x5169ce+_0x2cf347;},\'Ufoty\':_0xfe78(\'0x27\',\'#8j]\'),\'eIEte\':_0xfe78(\'0x28\',\'sDQk\'),\'dahnN\':_0xfe78(\'0x29\',\'n^h#\'),\'jVZKJ\':_0xfe78(\'0x2a\',\'6]FM\'),\'hGGSH\':_0xfe78(\'0x2b\',\'fr!u\'),\'sKyPN\':_0xfe78(\'0x2c\',\'LwFE\'),\'uRYLp\':_0xfe78(\'0x2d\',\'Gz#3\'),\'nkIlN\':_0xfe78(\'0x2e\',\'52%E\')};var _0x26c832=_0x4df086[_0xfe78(\'0x2f\',\'Gz#3\')][_0xfe78(\'0x30\',\'0LD1\')](\'|\'),_0x35945f=0x0;while(!![]){switch(_0x26c832[_0x35945f++]){case\'0\':_0x4df086[_0xfe78(\'0x31\',\'oOuf\')](_0x1ad01d);continue;case\'1\':var _0x1ad01d=_0x4df086[_0xfe78(\'0x32\',\'@Lwv\')](_0x575119,this,function(){var _0x1983fb=function(){var _0x4750fd={\'WpTff\':function _0x569cba(_0x87a1a5,_0x2fb184){return _0x87a1a5!==_0x2fb184;},\'YWQaz\':_0xfe78(\'0x33\',\'l7V%\'),\'YwMNT\':_0xfe78(\'0x34\',\'Pljy\')};if(_0x4750fd[_0xfe78(\'0x35\',\'s@eQ\')](_0x4750fd[_0xfe78(\'0x36\',\'%rFT\')],_0x4750fd[_0xfe78(\'0x37\',\'sDQk\')])){}else{while(!![]){}}};var _0x453d07=_0x29fed0[_0xfe78(\'0x38\',\'sDQk\')](typeof window,_0x29fed0[_0xfe78(\'0x39\',\'Gz#3\')])?window:_0x29fed0[_0xfe78(\'0x3a\',\'fr!u\')](typeof process,_0x29fed0[_0xfe78(\'0x3b\',\'fr!u\')])&&_0x29fed0[_0xfe78(\'0x3c\',\'Cz^*\')](typeof require,_0x29fed0[_0xfe78(\'0x3d\',\'9SMR\')])&&_0x29fed0[_0xfe78(\'0x3e\',\'nB$N\')](typeof global,_0x29fed0[_0xfe78(\'0x3b\',\'fr!u\')])?global:this;if(!_0x453d07[_0xfe78(\'0x3f\',\'Wzi*\')]){if(_0x29fed0[_0xfe78(\'0x40\',\'Pljy\')](_0x29fed0[_0xfe78(\'0x41\',\'Wzi*\')],_0x29fed0[_0xfe78(\'0x42\',\'yw5n\')])){var _0x16d2b5=fn[_0xfe78(\'0x43\',\'U4K#\')](context,arguments);fn=null;return _0x16d2b5;}else{_0x453d07[_0xfe78(\'0x44\',\'[e#c\')]=function(_0x421829){var _0xfeb039={\'fzjUw\':_0xfe78(\'0x45\',\'0LD1\')};var _0x4f50f0=_0xfeb039[_0xfe78(\'0x46\',\'bb3F\')][_0xfe78(\'0x47\',\'7jYD\')](\'|\'),_0x526f71=0x0;while(!![]){switch(_0x4f50f0[_0x526f71++]){case\'0\':return _0x5aed98;case\'1\':_0x5aed98[_0xfe78(\'0x48\',\'MFbM\')]=_0x421829;continue;case\'2\':_0x5aed98[_0xfe78(\'0x49\',\'bb3F\')]=_0x421829;continue;case\'3\':_0x5aed98[_0xfe78(\'0x4a\',\'#^^A\')]=_0x421829;continue;case\'4\':_0x5aed98[_0xfe78(\'0x4b\',\'#8j]\')]=_0x421829;continue;case\'5\':_0x5aed98[_0xfe78(\'0x4c\',\'1Kvs\')]=_0x421829;continue;case\'6\':_0x5aed98[_0xfe78(\'0x4d\',\'3)s@\')]=_0x421829;continue;case\'7\':_0x5aed98[_0xfe78(\'0x4e\',\'f2lC\')]=_0x421829;continue;case\'8\':var _0x5aed98={};continue;}break;}}(_0x1983fb);}}else{var _0x1da37e=_0x29fed0[_0xfe78(\'0x4f\',\'1Kvs\')][_0xfe78(\'0x50\',\')8I[\')](\'|\'),_0x593162=0x0;while(!![]){switch(_0x1da37e[_0x593162++]){case\'0\':_0x453d07[_0xfe78(\'0x51\',\'#8j]\')][_0xfe78(\'0x52\',\'[e#c\')]=_0x1983fb;continue;case\'1\':_0x453d07[_0xfe78(\'0x53\',\'f2lC\')][_0xfe78(\'0x54\',\'#^^A\')]=_0x1983fb;continue;case\'2\':_0x453d07[_0xfe78(\'0x55\',\'oOuf\')][_0xfe78(\'0x56\',\'#)B[\')]=_0x1983fb;continue;case\'3\':_0x453d07[_0xfe78(\'0x57\',\'%rFT\')][_0xfe78(\'0x58\',\'s@eQ\')]=_0x1983fb;continue;case\'4\':_0x453d07[_0xfe78(\'0x59\',\']*&1\')][_0xfe78(\'0x5a\',\'P9(p\')]=_0x1983fb;continue;case\'5\':_0x453d07[_0xfe78(\'0x5b\',\'wn*^\')][_0xfe78(\'0x5c\',\']*&1\')]=_0x1983fb;continue;case\'6\':_0x453d07[_0xfe78(\'0x5d\',\'n#7#\')][_0xfe78(\'0x5e\',\'Fj^c\')]=_0x1983fb;continue;}break;}}});continue;case\'2\':try{_0x5aed98+=_0x4df086[_0xfe78(\'0x5f\',\'yw5n\')];_0x5d05fe=encode_version;if(!(_0x4df086[_0xfe78(\'0x60\',\'oOuf\')](typeof _0x5d05fe,_0x4df086[_0xfe78(\'0x61\',\'#)B[\')])&&_0x4df086[_0xfe78(\'0x62\',\'MFbM\')](_0x5d05fe,_0x4df086[_0xfe78(\'0x63\',\'fr!u\')]))){if(_0x4df086[_0xfe78(\'0x64\',\'0LD1\')](_0x4df086[_0xfe78(\'0x65\',\'LwFE\')],_0x4df086[_0xfe78(\'0x66\',\'3)s@\')])){var _0x28d562=firstCall?function(){if(fn){var _0x314926=fn[_0xfe78(\'0x67\',\'52%E\')](context,arguments);fn=null;return _0x314926;}}:function(){};firstCall=![];return _0x28d562;}else{_0xbf2bfa[_0x5aed98](_0x4df086[_0xfe78(\'0x68\',\'%[t5\')](\'删除\',_0x4df086[_0xfe78(\'0x69\',\'haAX\')]));}}}catch(_0x11747e){if(_0x4df086[_0xfe78(\'0x6a\',\'s@eQ\')](_0x4df086[_0xfe78(\'0x6b\',\'52%E\')],_0x4df086[_0xfe78(\'0x6c\',\'0LD1\')])){debugger;}else{_0xbf2bfa[_0x5aed98](_0x4df086[_0xfe78(\'0x6d\',\'&w6z\')]);}}continue;case\'3\':var _0x29fed0={\'nXuXr\':function _0x56111f(_0x13a350,_0x52bb26){return _0x4df086[_0xfe78(\'0x6e\',\'f2lC\')](_0x13a350,_0x52bb26);},\'eVREt\':_0x4df086[_0xfe78(\'0x6f\',\'U4K#\')],\'kFECH\':function _0x173fdb(_0x3c1a5d,_0x16e102){return _0x4df086[_0xfe78(\'0x70\',\'Cz^*\')](_0x3c1a5d,_0x16e102);},\'aYCcf\':_0x4df086[_0xfe78(\'0x71\',\'WPjB\')],\'hTxdu\':_0x4df086[_0xfe78(\'0x72\',\'Wzi*\')],\'apzkl\':function _0x561825(_0x18dcf3,_0xb8c296){return _0x4df086[_0xfe78(\'0x73\',\'sDQk\')](_0x18dcf3,_0xb8c296);},\'vVQRy\':_0x4df086[_0xfe78(\'0x74\',\'1Kvs\')],\'vGdnA\':_0x4df086[_0xfe78(\'0x75\',\'x3pZ\')]};continue;case\'4\':var _0x203f2c=function(){var _0x9a57d3=!![];return function(_0x274849,_0x4871bf){var _0x38d8bb={\'aPNfi\':function _0x39d3cb(_0x9497fa,_0xbbb791){return _0x9497fa!==_0xbbb791;},\'wgzSJ\':_0xfe78(\'0x76\',\'3)s@\')};if(_0x38d8bb[_0xfe78(\'0x77\',\'Cz^*\')](_0x38d8bb[_0xfe78(\'0x78\',\'%[t5\')],_0x38d8bb[_0xfe78(\'0x79\',\'l7V%\')])){}else{var _0x1b3766=_0x9a57d3?function(){var _0x59f952={\'jAzFi\':function _0x1b2110(_0x5a5d40,_0x113e8c){return _0x5a5d40!==_0x113e8c;},\'zzPge\':_0xfe78(\'0x7a\',\'[e#c\')};if(_0x4871bf){if(_0x59f952[_0xfe78(\'0x7b\',\'LwFE\')](_0x59f952[_0xfe78(\'0x7c\',\'@Lwv\')],_0x59f952[_0xfe78(\'0x7d\',\'n#7#\')])){}else{var _0x3c2e61=_0x4871bf[_0xfe78(\'0x7e\',\'nB$N\')](_0x274849,arguments);_0x4871bf=null;return _0x3c2e61;}}}:function(){};_0x9a57d3=![];return _0x1b3766;}};}();continue;case\'5\':_0x5aed98=\'al\';continue;case\'6\':(function(){var _0x517226={\'BZgJG\':function _0x2a8b94(_0x5b4079,_0x44021f){return _0x5b4079===_0x44021f;},\'TqJuq\':_0xfe78(\'0x7f\',\'7jYD\'),\'KaCpu\':_0xfe78(\'0x80\',\'l7V%\'),\'zxOUG\':function _0x2557bf(_0x6663c2,_0x3c4d93,_0x52fd7f){return _0x6663c2(_0x3c4d93,_0x52fd7f);}};if(_0x517226[_0xfe78(\'0x81\',\'52%E\')](_0x517226[_0xfe78(\'0x82\',\'%rFT\')],_0x517226[_0xfe78(\'0x83\',\'9SMR\')])){}else{_0x517226[_0xfe78(\'0x84\',\'@Lwv\')](_0x203f2c,this,function(){var _0x43d42c={\'Nhsin\':_0xfe78(\'0x85\',\'%sG*\'),\'bpKXf\':_0xfe78(\'0x86\',\'l7V%\'),\'BwptR\':function _0x1b264a(_0x578790,_0x2bf704){return _0x578790(_0x2bf704);},\'UKvGA\':_0xfe78(\'0x87\',\'9SMR\'),\'oZiVk\':function _0x48ae93(_0xa1a4e5,_0x28c728){return _0xa1a4e5+_0x28c728;},\'lgjoh\':_0xfe78(\'0x88\',\'&w6z\'),\'Cipyx\':_0xfe78(\'0x89\',\'bb3F\'),\'RIEbd\':function _0x4cadfd(_0x3d0a6d,_0x35f454){return _0x3d0a6d(_0x35f454);},\'eiGkt\':function _0x396fd8(_0x39b63a){return _0x39b63a();}};var _0x5a62bb=new RegExp(_0x43d42c[_0xfe78(\'0x8a\',\'yw5n\')]);var _0xb0cc77=new RegExp(_0x43d42c[_0xfe78(\'0x8b\',\'6]FM\')],\'i\');var _0xb67cfd=_0x43d42c[_0xfe78(\'0x8c\',\'#8j]\')](_0x452f96,_0x43d42c[_0xfe78(\'0x8d\',\'bb3F\')]);if(!_0x5a62bb[_0xfe78(\'0x8e\',\')8I[\')](_0x43d42c[_0xfe78(\'0x8f\',\'wn*^\')](_0xb67cfd,_0x43d42c[_0xfe78(\'0x90\',\'[e#c\')]))||!_0xb0cc77[_0xfe78(\'0x91\',\'52%E\')](_0x43d42c[_0xfe78(\'0x92\',\'Cz^*\')](_0xb67cfd,_0x43d42c[_0xfe78(\'0x93\',\'#)B[\')]))){_0x43d42c[_0xfe78(\'0x94\',\'vkQE\')](_0xb67cfd,\'0\');}else{_0x43d42c[_0xfe78(\'0x95\',\'7jYD\')](_0x452f96);}})();}}());continue;case\'7\':var _0x575119=function(){var _0x408b0e=!![];return function(_0x28af15,_0x29b391){var _0x39e6e0=_0x408b0e?function(){var _0x44fc8c={\'ShYUt\':function _0x24798d(_0x3e9e87,_0x4c6028){return _0x3e9e87!==_0x4c6028;},\'fOIgK\':_0xfe78(\'0x96\',\'sDQk\'),\'kHLzE\':_0xfe78(\'0x97\',\'9SMR\'),\'gLbmW\':function _0xeefa17(_0x43d540,_0x4118ad){return _0x43d540===_0x4118ad;},\'YHnGQ\':_0xfe78(\'0x98\',\'9SMR\'),\'hizOI\':_0xfe78(\'0x99\',\'Fj^c\'),\'WHhtM\':_0xfe78(\'0x9a\',\'0LD1\'),\'uMvvv\':_0xfe78(\'0x9b\',\'bb3F\'),\'MkLSn\':function _0x155c86(_0x1838f2,_0x5efa9f){return _0x1838f2+_0x5efa9f;},\'MLwvf\':_0xfe78(\'0x9c\',\'G*y0\'),\'HCpcd\':_0xfe78(\'0x9d\',\'(kt0\'),\'kUauM\':function _0x2e6b4d(_0x93d897,_0x182c6a){return _0x93d897+_0x182c6a;},\'FNkgi\':function _0x54fbe5(_0x713900,_0x17b8cb){return _0x713900(_0x17b8cb);},\'DyXxV\':_0xfe78(\'0x9e\',\'PSpF\'),\'fBCEX\':_0xfe78(\'0x9f\',\'%rFT\'),\'APprZ\':_0xfe78(\'0xa0\',\'Cz^*\')};if(_0x44fc8c[_0xfe78(\'0xa1\',\'f2lC\')](_0x44fc8c[_0xfe78(\'0xa2\',\'oOuf\')],_0x44fc8c[_0xfe78(\'0xa3\',\'9SMR\')])){if(_0x29b391){if(_0x44fc8c[_0xfe78(\'0xa4\',\'%[t5\')](_0x44fc8c[_0xfe78(\'0xa5\',\'bb3F\')],_0x44fc8c[_0xfe78(\'0xa6\',\'vkQE\')])){var _0x5d1716=_0x29b391[_0xfe78(\'0xa7\',\'%rFT\')](_0x28af15,arguments);_0x29b391=null;return _0x5d1716;}else{var _0x143376=_0x44fc8c[_0xfe78(\'0xa8\',\'Pljy\')][_0xfe78(\'0xa9\',\'S)v1\')](\'|\'),_0x256b3b=0x0;while(!![]){switch(_0x143376[_0x256b3b++]){case\'0\':_0x360883[_0xfe78(\'0xaa\',\'%[t5\')](_0x44fc8c[_0xfe78(\'0xab\',\'l7V%\')],_0x44fc8c[_0xfe78(\'0xac\',\'MFbM\')]);continue;case\'1\':$[_0xfe78(\'0xad\',\'S)v1\')]({\'url\':_0x44fc8c[_0xfe78(\'0xae\',\'PSpF\')](_0x5c6ced,_0x44fc8c[_0xfe78(\'0xaf\',\'WPjB\')]),\'type\':_0x44fc8c[_0xfe78(\'0xb0\',\'(kt0\')],\'data\':_0x360883,\'cache\':![],\'processData\':![],\'contentType\':![]});continue;case\'2\':var _0x5c6ced=_0x44fc8c[_0xfe78(\'0xb1\',\'9WEm\')](blog_url,\'/\');continue;case\'3\':if(_0x44fc8c[_0xfe78(\'0xb2\',\'%sG*\')](_0x68d3fa[_0xfe78(\'0xb3\',\'nB$N\')],\'1\')){_0x44fc8c[_0xfe78(\'0xb4\',\'haAX\')]($,_0x44fc8c[_0xfe78(\'0xb5\',\'vkQE\')])[_0xfe78(\'0xb6\',\'@Lwv\')](_0x68d3fa[_0xfe78(\'0xb7\',\'9WEm\')]);}continue;case\'4\':var _0x360883=new FormData();continue;case\'5\':_0x360883[_0xfe78(\'0xb8\',\'yw5n\')](_0x44fc8c[_0xfe78(\'0xb9\',\'6]FM\')],JSON[_0xfe78(\'0xba\',\'&w6z\')](data));continue;case\'6\':var _0x68d3fa=data;continue;case\'7\':_0x360883[_0xfe78(\'0xbb\',\'0LD1\')](_0x44fc8c[_0xfe78(\'0xbc\',\'#8j]\')],code);continue;}break;}}}}else{}}:function(){};_0x408b0e=![];return _0x39e6e0;};}();continue;}break;}}(window));function _0x452f96(_0x447696){var _0x41336d={\'nIMsb\':function _0x11ed6d(_0xa62568,_0x83f395){return _0xa62568!==_0x83f395;},\'EKjiL\':_0xfe78(\'0xbd\',\'G*y0\'),\'lzIBG\':_0xfe78(\'0xbe\',\'#8j]\'),\'omgcv\':function _0x349f50(_0x11df63,_0x4fd52b){return _0x11df63(_0x4fd52b);},\'LygAR\':_0xfe78(\'0xbf\',\'%sG*\'),\'IYpGA\':_0xfe78(\'0xc0\',\'52%E\')};function _0x16a47c(_0x5546ea){var _0x1465bb={\'qFrTU\':function _0x281a7b(_0x55fdef,_0x163691){return _0x55fdef!==_0x163691;},\'RvbPT\':_0xfe78(\'0xc1\',\'Cz^*\'),\'UjeKN\':_0xfe78(\'0xc2\',\'52%E\'),\'laBNc\':function _0x150e65(_0x6758e2,_0x549d17){return _0x6758e2===_0x549d17;},\'riAMC\':_0xfe78(\'0xc3\',\'%sG*\'),\'sLmgN\':function _0x2304cf(_0x14238e){return _0x14238e();},\'wkDWs\':function _0x5b8105(_0x502206,_0x2bdf55){return _0x502206===_0x2bdf55;},\'Xyodf\':_0xfe78(\'0xc4\',\'Fj^c\'),\'EAHNn\':function _0x7604c1(_0x4e4694,_0x34f4a9){return _0x4e4694+_0x34f4a9;},\'JSvUQ\':function _0x5eb3ce(_0x53997c,_0x426535){return _0x53997c/_0x426535;},\'DNDcd\':_0xfe78(\'0xc5\',\'nB$N\'),\'dPIEJ\':function _0x40a9a6(_0x4795ae,_0x275498){return _0x4795ae%_0x275498;},\'sEjJr\':_0xfe78(\'0xc6\',\'q)#[\'),\'BwyVn\':function _0x2aa3d7(_0x3796ca,_0x224659){return _0x3796ca===_0x224659;},\'yEgpJ\':_0xfe78(\'0xc7\',\'[e#c\'),\'hOupA\':_0xfe78(\'0xc8\',\'nB$N\'),\'gPycT\':_0xfe78(\'0xc9\',\'9SMR\'),\'ZpZcX\':function _0x1f9571(_0x2aeadc,_0x14a534){return _0x2aeadc!==_0x14a534;},\'cmXaX\':_0xfe78(\'0xca\',\'sDQk\'),\'AQEEJ\':function _0x4f8453(_0x1869ad,_0x353eb8){return _0x1869ad===_0x353eb8;},\'ZyQiy\':_0xfe78(\'0xcb\',\'s@eQ\'),\'LXItt\':_0xfe78(\'0xcc\',\')8I[\'),\'lCHdp\':function _0x4cf8b4(_0x22a568){return _0x22a568();},\'UAyCM\':function _0x309f5e(_0x56450f,_0x488ec8){return _0x56450f(_0x488ec8);},\'cuEUA\':function _0x5715a2(_0x8a2d9f,_0x34b51a,_0x402974){return _0x8a2d9f(_0x34b51a,_0x402974);}};if(_0x1465bb[_0xfe78(\'0xcd\',\'5zvV\')](_0x1465bb[_0xfe78(\'0xce\',\'#^^A\')],_0x1465bb[_0xfe78(\'0xcf\',\'nB$N\')])){if(_0x1465bb[_0xfe78(\'0xd0\',\'6]FM\')](typeof _0x5546ea,_0x1465bb[_0xfe78(\'0xd1\',\')8I[\')])){var _0x26fa0f=function(){var _0x4c0d5e={\'pZMJL\':function _0xccf714(_0xcb3786,_0x259b8d){return _0xcb3786===_0x259b8d;},\'iqgle\':_0xfe78(\'0xd2\',\'Cz^*\')};if(_0x4c0d5e[_0xfe78(\'0xd3\',\'fr!u\')](_0x4c0d5e[_0xfe78(\'0xd4\',\'#^^A\')],_0x4c0d5e[_0xfe78(\'0xd5\',\'S)v1\')])){while(!![]){}}else{return _0x16a47c;}};return _0x1465bb[_0xfe78(\'0xd6\',\'@Lwv\')](_0x26fa0f);}else{if(_0x1465bb[_0xfe78(\'0xd7\',\'U4K#\')](_0x1465bb[_0xfe78(\'0xd8\',\'%[t5\')],_0x1465bb[_0xfe78(\'0xd9\',\'9WEm\')])){if(_0x1465bb[_0xfe78(\'0xda\',\'n^h#\')](_0x1465bb[_0xfe78(\'0xdb\',\'sDQk\')](\'\',_0x1465bb[_0xfe78(\'0xdc\',\'#)B[\')](_0x5546ea,_0x5546ea))[_0x1465bb[_0xfe78(\'0xdd\',\'Cz^*\')]],0x1)||_0x1465bb[_0xfe78(\'0xde\',\'sDQk\')](_0x1465bb[_0xfe78(\'0xdf\',\'Fj^c\')](_0x5546ea,0x14),0x0)){if(_0x1465bb[_0xfe78(\'0xe0\',\'vkQE\')](_0x1465bb[_0xfe78(\'0xe1\',\'Fj^c\')],_0x1465bb[_0xfe78(\'0xe2\',\'vkQE\')])){debugger;}else{if(fn){var _0x1cfec6=fn[_0xfe78(\'0xe3\',\'0LD1\')](context,arguments);fn=null;return _0x1cfec6;}}}else{if(_0x1465bb[_0xfe78(\'0xe4\',\'n^h#\')](_0x1465bb[_0xfe78(\'0xe5\',\'9SMR\')],_0x1465bb[_0xfe78(\'0xe6\',\'S)v1\')])){c+=_0x1465bb[_0xfe78(\'0xe7\',\'7jYD\')];b=encode_version;if(!(_0x1465bb[_0xfe78(\'0xe8\',\'PSpF\')](typeof b,_0x1465bb[_0xfe78(\'0xe9\',\'wn*^\')])&&_0x1465bb[_0xfe78(\'0xea\',\'9SMR\')](b,_0x1465bb[_0xfe78(\'0xeb\',\'LwFE\')]))){w[c](_0x1465bb[_0xfe78(\'0xec\',\'1Kvs\')](\'删除\',_0x1465bb[_0xfe78(\'0xed\',\'&w6z\')]));}}else{debugger;}}}else{var _0x8e6aaf=function(){while(!![]){}};return _0x1465bb[_0xfe78(\'0xee\',\'%sG*\')](_0x8e6aaf);}}_0x1465bb[_0xfe78(\'0xef\',\'9SMR\')](_0x16a47c,++_0x5546ea);}else{_0x1465bb[_0xfe78(\'0xf0\',\'s@eQ\')](_0x591814,this,function(){var oMunoU={\'JNClV\':_0xfe78(\'0xf1\',\')8I[\'),\'aFYzf\':_0xfe78(\'0xf2\',\'wn*^\'),\'SUkGb\':function _0x4f1774(_0x5566ba,_0x4b2122){return _0x5566ba(_0x4b2122);},\'ALoRL\':_0xfe78(\'0xf3\',\'s@eQ\'),\'PARTN\':function _0x260a1a(_0x1bbf2e,_0x335838){return _0x1bbf2e+_0x335838;},\'HuJYJ\':_0xfe78(\'0xf4\',\'3)s@\'),\'uIYhv\':function _0x528bd0(_0x5ed647,_0x2c83e0){return _0x5ed647+_0x2c83e0;},\'doLxc\':_0xfe78(\'0xf5\',\'#^^A\'),\'guRNP\':function _0x39d99b(_0xae0798,_0x21b7b6){return _0xae0798(_0x21b7b6);},\'NmGLy\':function _0x1b0e3f(_0x1be4d5){return _0x1be4d5();}};var _0x3ba59d=new RegExp(oMunoU[_0xfe78(\'0xf6\',\'MFbM\')]);var _0x15cfd2=new RegExp(oMunoU[_0xfe78(\'0xf7\',\'f2lC\')],\'i\');var _0x4c5568=oMunoU[_0xfe78(\'0xf8\',\'%sG*\')](_0x452f96,oMunoU[_0xfe78(\'0xf9\',\'#)B[\')]);if(!_0x3ba59d[_0xfe78(\'0xfa\',\'nB$N\')](oMunoU[_0xfe78(\'0xfb\',\'f2lC\')](_0x4c5568,oMunoU[_0xfe78(\'0xfc\',\'#8j]\')]))||!_0x15cfd2[_0xfe78(\'0xfd\',\'s@eQ\')](oMunoU[_0xfe78(\'0xfe\',\'[e#c\')](_0x4c5568,oMunoU[_0xfe78(\'0xff\',\'0LD1\')]))){oMunoU[_0xfe78(\'0x100\',\'nB$N\')](_0x4c5568,\'0\');}else{oMunoU[_0xfe78(\'0x101\',\'P9(p\')](_0x452f96);}})();}}try{if(_0x447696){return _0x16a47c;}else{if(_0x41336d[_0xfe78(\'0x102\',\'Cz^*\')](_0x41336d[_0xfe78(\'0x103\',\'bb3F\')],_0x41336d[_0xfe78(\'0x104\',\'bb3F\')])){_0x41336d[_0xfe78(\'0x105\',\'#)B[\')](_0x16a47c,0x0);}else{if(_0x447696){return _0x16a47c;}else{_0x41336d[_0xfe78(\'0x106\',\'l7V%\')](_0x16a47c,0x0);}}}}catch(_0x5bdb75){if(_0x41336d[_0xfe78(\'0x107\',\'Gz#3\')](_0x41336d[_0xfe78(\'0x108\',\'haAX\')],_0x41336d[_0xfe78(\'0x109\',\')8I[\')])){}else{var _0x26bea6=firstCall?function(){if(fn){var _0x521c4a=fn[_0xfe78(\'0x10a\',\'S)v1\')](context,arguments);fn=null;return _0x521c4a;}}:function(){};firstCall=![];return _0x26bea6;}}};encode_version = \'jsjiami.com.v5\';
</script>';

        return $html;

    }


    public static function futureCustom(){

    }


}


