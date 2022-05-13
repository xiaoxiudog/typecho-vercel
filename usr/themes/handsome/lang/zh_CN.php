<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * zh_CN.php
 * Author:  hran,hewro
 * Date: 2017/4/29
 * Version : 1.0
 * Description: 中国简体语言
 */
class Lang_zh_CN extends Lang
{
    /**
     * @return string 返回语言名称
     */
    public function name()
    {
        return "简体中文";
    }

    /**
     * @return array 返回包含翻译文本的数组
     */
    public function translated()
    {
        return array();
    }

    /**
     * @return array 返回日期的格式化字符串
     */
    public function dateFormat()
    {
        return array(
            "simple" => "Y 年 m 月 d 日",
            "detail" => "Y 年 m 月 d 日 h : i  A",
        );
    }

}
