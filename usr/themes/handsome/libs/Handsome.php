<?php

class Handsome
{

    const version = "8.4.1";//主版本号
    public static $versionTag = "2022041901";//版本号后缀，区别同一版本不同修改日期

    /**
     * 随机选取背景颜色
     * @return mixed
     */
    public static function getBackgroundColor()
    {
        $colors = array(
            array('#673AB7', '#512DA8'),
            array('#20af42', '#1a9c39'),
            array('#336666', '#2d4e4e'),
            array('#2e3344', '#232735')
        );
        $randomKey = array_rand($colors, 1);
        $randomColor = $colors[$randomKey];
        return $randomColor;
    }

    public static function isPluginAvailable($className, $dirName)
    {
        if (class_exists($className)) {
            $plugins = Typecho_Plugin::export();
            $plugins = $plugins['activated'];
            if (is_array($plugins) && array_key_exists($dirName, $plugins)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public static function getPluginVersion($className, $dirName)
    {
        if (class_exists($className)) {
            $plugins = Typecho_Plugin::parseInfo(__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . "/" . $dirName . "/Plugin.php");
            return $plugins["version"];
        } else {
            return "-1";
        }
    }

    public static function getPluginCorrect()
    {
        //判断插件的钩子是否存在，不存在说明需要重新启用插件
        $editor = @Typecho_Plugin::export()["handles"]["admin/write-post.php:richEditor"];
        if ($editor != null){
            foreach (@$editor as $item){
                if (@$item[0] == "Handsome_Plugin"){
                    return true;
                }
            }
        }
        return false;
    }
}

?>
