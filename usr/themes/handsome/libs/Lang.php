<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Language.php
 * Author     : hran
 * Date       : 2017/04/23
 * Version    :
 * Description:
 */
abstract class Lang {

    public function locale() {//获取类的名称，只保留 local code
        $c = get_called_class();
        $c = str_replace('Usr_', '', $c);
        $c = str_replace('Lang_', '', $c);
        $c = str_replace('Settings_', '', $c);
        return $c;
    }

    /**
     * @return string 返回语言名称
     */
    public abstract function name();

    /**
     * @return string 返回日期的格式化字符串
     */
    public abstract function dateFormat();

    /**
     * @return array 返回包含翻译文本的数组
     */
    public abstract function translated();
}
