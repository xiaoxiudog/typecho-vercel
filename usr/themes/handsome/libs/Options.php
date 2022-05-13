<?php

/**
 * Class Handsome_Settings
 * 设计为单例，外部通过 mget 和 mset函数读写所有的options 配置信息
 */

class Handsome_Settings{
    private static $instance = NULL;

    private $themeOptions = NULL;
    private $pluginOptions = NULL;


    private $localStorage = array();
    /**
     * @var Widget_User|mixed
     */
    public $currentUser = NULL;

    private static function init() {
        self::$instance = new Handsome_Settings();
        self::$instance->themeOptions = Helper::options();
        self::$instance->currentUser = Typecho_Widget::widget('Widget_User');
    }

    /**
     * @return Handsome_Settings|mixed
     */
    public static function instance(){
        if (self::$instance == NULL) {
            self::init();
        }
        return self::$instance;
    }

    public function __get($key){
        return $this->get($key);
    }


    public function get($key, $default = NULL){
        if (array_key_exists($key, $this->localStorage)) {
            $value = $this->localStorage[$key];
        }else {
            $value = $this->themeOptions->{$key};
        }
        if (NULL == $value && $value !== FALSE) {
            if (!strpos($key, '__') > 0) {//查找字符串首次出现的位置
                return $default;
            }
            $key = preg_split('/__/i', $key, 2);
            $option = $key[0];
            $value = @$key[1];
            $option = $this->get($option);
            if (is_array($option) && !empty($value)) {
                if (in_array($value, $option)) {
                    return true;
                }
                return false;
            }
            return NULL;
        }
        return $value;
    }


    public function __set($key, $value){
        $this->set($key, $value);
    }

    public function set(){
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $key = $args[0];
            array_shift($args);
            if (empty($args)) {
                unset($this->localStorage[$key]);//销毁变量
            }elseif (count($args) == 1) {
                $value = $args[0];
                if ($value == NULL) {
                    unset($this->localStorage[$key]);
                }else {
                    $this->localStorage[$key] = $value;
                }
            }else {
                $this->localStorage[$key] = $args;
            }
        }
    }
    public function __call($name, $args){
        echo $this->get($name, @$args[0]);
    }

}

if (!function_exists('mget')) {
    function mget(){
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $key = $args[0];
            $default = @$args[1];
            return Handsome_Settings::instance()->get($key, $default);
        }else {
            return Handsome_Settings::instance();
        }
    }

}

if (!function_exists('mset')) {
    /**
     * @return Handsome_Settings | mixed
     */
    function mset(){
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $key = $args[0];
            array_shift($args);
            if (count($args) == 1) {
                $value = $args[0];
                Handsome_Settings::instance()->set($key, $value);
            }else{
                Handsome_Settings::instance()->set($key, $args);
            }
        }

        return Handsome_Settings::instance();
    }
}

if (!function_exists('redText')){
    function redText(){
        if (func_num_args() >= 1) {
            $args = func_get_args();
            $text = $args[0];
            return "<span style='color: red;display: inline' >$text</span>";
        }
        return "";
    }
}
