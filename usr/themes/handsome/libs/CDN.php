<?php

/**
 * CDN.php
 * Author     : hewro
 * Date       : 2020/07/22
 * Version    :
 * Description:
 */


class CDN_Config
{
    const COMMENT_SYSTEM_ROOT = 0;
    const COMMENT_SYSTEM_CHANGYAN = 1;
    const COMMENT_SYSTEM_OTHERS = 2;
    const COMMENT_SYSTEM_NONE = 3;
    const DEVELOPER_DEBUG = 0;//开发者本地开发模式，请勿修改此变量,0为普通用户模式，1为开发者模式
    const SPECIAL_MODE = 0; #请勿修改该变量，否则可能会导致一些问题出现
    const SPECIAL_MODE_NUM = ["2021","2022","image"];


    const version = "aHR0cHM6Ly9hdXRoLmloZXdyby5jb20v";
    const debug = "aHR0cHM6Ly9hdXRoLmloZXdyby5jb20vYXV0aC9ub3RpY2U=";


    const handsome = "42cec9c1f098e880cf25bd79a37478eb";
    const handsome_check = "A1E1AE6EBBDDC95A9F0B07B861CD2C5B";
    const notice = "";
    const AUTH = "thx";
    const bad_check = "<b>按照下面流程即可完成授权操作</b></br></br><small>正版付费用户添加域名后，打开<a href=\"admin/options-theme.php\">handsome外观设置界面</a>刷新一下即可刷新授权</small>";
    const not_full = "主题文件不完整或被恶意修改破坏，请联系主题作者获取解决方案\n";


    const PHP_ERROR_DISPLAY = 'off';//on 开启php错误输出，off强制关闭php错误输出
    const HANDSOME_DEBUG_DISPLAY = 'off'; //on 开启handsome调试信息，off 关闭handsome调试信息显示

    const NORMAL_PLACEHOLDER = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAABS2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxMzggNzkuMTU5ODI0LCAyMDE2LzA5LzE0LTAxOjA5OjAxICAgICAgICAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIi8+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgo8P3hwYWNrZXQgZW5kPSJyIj8+IEmuOgAAAA1JREFUCJljePfx038ACXMD0ZVlJAYAAAAASUVORK5CYII=';
    const OPACITY_PLACEHOLDER = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAABS2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxMzggNzkuMTU5ODI0LCAyMDE2LzA5LzE0LTAxOjA5OjAxICAgICAgICAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIi8+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgo8P3hwYWNrZXQgZW5kPSJyIj8+IEmuOgAAAA1JREFUCJljOHz4cAMAB2ACypfyMOEAAAAASUVORK5CYII=';


    const not_support = "php缺少mbstring模块支持，请联系作者获取解决方案";
    const action = "action/themes-edit";


    const BOOT_CDN = '
{
  "css": {
    "bootstrap": "https://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css",
    "mdui": "https://cdn.bootcss.com/mdui/0.4.0/css/mdui.min.css"
  },
  "js": {
    "bootstrap": "https://cdn.bootcss.com/bootstrap/3.3.4/js/bootstrap.min.js",
    "jquery": "https://cdn.bootcss.com/jquery/2.1.4/jquery.min.js",
    "mdui": "https://cdn.bootcss.com/mdui/0.4.0/js/mdui.min.js",
    "mathjax_svg": "https://cdn.bootcdn.net/ajax/libs/mathjax/3.1.2/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://cdn.bootcdn.net/ajax/libs/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}
    ';


    const BAIDU_CDN = '
{
  "css": {
    "bootstrap": "https://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css"
  },
  "js": {
    "bootstrap": "https://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js",
    "jquery": "https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js",
    "mathjax_svg": "https://cdn.staticfile.org/mathjax/3.1.2/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://lf6-cdn-tos.bytecdntp.com/cdn/expire-5-y/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}
';

    const SINA_CDN = '
{
  "css": {
    "bootstrap": "https://lib.sinaapp.com/js/bootstrap/latest/css/bootstrap.min.css",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css"
  },
  "js": {
    "bootstrap": "https://lib.sinaapp.com/js/bootstrap/latest/js/bootstrap.min.js",
    "jquery": "https://lib.sinaapp.com/js/jquery/2.2.4/jquery-2.2.4.min.js",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js",
    "mathjax_svg": "https://cdn.staticfile.org/mathjax/3.1.2/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://lf6-cdn-tos.bytecdntp.com/cdn/expire-5-y/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}
';

    const QINIU_CDN = '
{
  "css": {
    "bootstrap": "https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css"
  },
  "js": {
    "bootstrap": "https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js",
    "jquery": "https://cdn.staticfile.org/jquery/2.2.4/jquery.min.js",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js",
    "mathjax_svg": "https://cdn.staticfile.org/mathjax/3.1.2/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://lf6-cdn-tos.bytecdntp.com/cdn/expire-5-y/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}
';


    const JSDELIVR_CDN = '
{
  "css": {
    "bootstrap": "https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css"
  },
  "js": {
    "bootstrap": "https://cdn.jsdelivr.net/npm/bootstrap@3.3.4/dist/js/bootstrap.min.js",
    "jquery": "https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js",
    "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js",
    "mathjax_svg": "https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://lf6-cdn-tos.bytecdntp.com/cdn/expire-5-y/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}
';

    const CAT_CDN = '
{
  "css": {
    "bootstrap": "https://cdnjs.loli.net/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css",
    "mdui": "https://cdnjs.loli.net/ajax/libs/mdui/0.4.0/css/mdui.min.css"
  },
  "js": {
    "bootstrap": "https://cdnjs.loli.net/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js",
    "jquery": "https://cdnjs.loli.net/ajax/libs/jquery/2.2.4/jquery.min.js",
     "mdui": "https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js",
    "mathjax_svg": "https://cdnjs.loli.net/ajax/libs/mathjax/3.1.2/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://lf6-cdn-tos.bytecdntp.com/cdn/expire-5-y/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}
';

    const BY_CDN = '
{
  "css": {
    "bootstrap": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/twitter-bootstrap/3.3.4/css/bootstrap.min.css",
    "mdui": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/mdui/0.4.0/css/mdui.min.css"
  },
  "js": {
    "bootstrap": "http://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/twitter-bootstrap/3.3.4/js/bootstrap.min.js",
    "jquery": "https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/jquery/2.2.4/jquery.min.js",
    "mdui": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/mdui/0.4.3/js/mdui.min.js",
    "mathjax_svg": "https://cdnjs.loli.net/ajax/libs/mathjax/3.1.2/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://lf6-cdn-tos.bytecdntp.com/cdn/expire-5-y/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}   
 ';

    const LOCAL_CDN = '
{
  "relative": true,
  "css": {
    "bootstrap": "bootstrap/css/bootstrap.min.css",
    "mdui": "mdui/mdui.min.css"
  },
  "js": {
    "bootstrap": "bootstrap/js/bootstrap.min.js",
    "jquery": "jquery/jquery.min.js",
    "mdui": "mdui/mdui.min.js",
    "mathjax_svg": "https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.min.js",
    "vditor": "https://cdn.jsdelivr.net/npm/vditor@3.8.10",
    "echart": "https://lf6-cdn-tos.bytecdntp.com/cdn/expire-5-y/echarts/4.5.0",
    "highlight": "https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/highlight.js/10.6.0"
  }
}
';




    public static function returnThemePath()
    {
        $DIRECTORY_SEPARATOR = "/";
        $childDir = $DIRECTORY_SEPARATOR . 'usr' . $DIRECTORY_SEPARATOR . 'themes' . $DIRECTORY_SEPARATOR . 'handsome'
            . $DIRECTORY_SEPARATOR;
        $dir = __TYPECHO_ROOT_DIR__ . $childDir;
        return $dir;
    }

    public static function returnHandsomePath()
    {
        return __DIR__ . "/HAdmin.php";//todo文件名称加密
    }




}
?>
