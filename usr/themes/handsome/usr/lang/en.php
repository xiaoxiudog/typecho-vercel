<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * en.php
 * Author     : hewro
 * Date       : 2017/04/30
 * Version    :
 * Description:
 */
class Usr_Lang_en extends Lang_en {

    /**
     * @return array 返回包含翻译文本的数组
     */
    public function translated() {
        return array(
            //添加翻译的词汇,每两组之间，用英文逗号隔开
        );
    }

    /**
     * @return array 返回日期的格式化字符串
     */
    public function dateFormat(){
        return array(
            "simple" => "F j, Y",
            "detail" => "F j, Y g:i a",
        );
    }
}
