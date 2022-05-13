<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * en.php
 * Author     : hran,hewro
 * Date       : 2017/04/30
 * Version    :
 * Description:
 */
class Lang_Settings_en extends Lang {

    /**
     * @return string 返回语言名称
     */
    public function name() {
        return "English";
    }

    /**
     * @return array 返回包含翻译文本的数组
     */
    public function translated() {
        return array(
            '固定头部' => 'Fixed header',
            '大头图地址' => 'Big header pic address',
            '文章设置' => 'Article settings',
            '管理友情链接' => 'Manage links',
            '友情链接' => 'Links'
        );
    }

    /**
     * @return array 返回日期的格式化字符串
     */
    public function dateFormat() {
        return array(
            "simple" => "F j, Y",
            "detail" => "F j, Y g:i a",
        );
    }
}
