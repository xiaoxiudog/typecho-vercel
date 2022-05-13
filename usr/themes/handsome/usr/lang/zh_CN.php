<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * zh_cn.php
 * Author     : hewro,hran
 * Date       : 2017/04/30
 * Version    :
 * Description:
 */
class Usr_Lang_zh_CN extends Lang_zh_CN{

    /**
     * @return array 返回包含翻译文本的数组
     */
    public function translated() {
        return array(
            '作者' => '博主'
            //添加翻译的词汇,每两组之间，用英文逗号隔开
        );
    }

    public function dateFormat() {
        return array(
            "simple" => "Y 年 m 月 d 日",
            "detail" => "Y 年 m 月 d 日 h : i  A",
        );
    }
}
