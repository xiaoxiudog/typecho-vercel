<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * I18n.php
 * Author     : hewro
 * Date       : 2017/04/23
 * Version    :
 * Description:
 */
class I18n
{

    private static $instance;//一个实例
    private $locale;//语言格式
    private $loadedLangs;//加载语言
    private $dateFormat;//时间格式
    private $isSettingsPage = false;
    private $loaded = false; //
    private $loadSucceed = false;

    private function loadLangIfNotLoad()
    {//loaded == false时候执行该语句
        if (!$this->loaded) {
            if (empty($this->locale)) {//如果没有设置语言，则默认跟随浏览器配置
                $this->locale = $this->acceptLocale();
            }
            $this->loadedLangs = array();
            $this->loadLang($this->locale);
            $this->loaded = true;
        }

    }

    private function setLocale($locale)
    {
        $this->locale = $locale;
    }

    private function setIsSettingPages($is)
    {
        $this->isSettingsPage = $is;
    }

    private function acceptLocale()
    {//获取本地配置
        $accepts = mb_split(',', @$_SERVER['HTTP_ACCEPT_LANGUAGE']);//判断浏览器语言
        $acceptLocales = array();//本地配置数组
        foreach ($accepts as $lang) {
            $q = "1.0";
            if (preg_match('/^([a-zA-Z0-9\-\_]+);q=([0-9\.]+)$/i', $lang, $matched)) {
                $q = $matched[2];
                $acceptLocales[$q] = $matched[1];
            } elseif (preg_match('/^([a-zA-Z0-9\-\_]+)$/i', $lang, $matched)) {
                $acceptLocales[$q] = $lang;
            } else {
                continue;
            }

            $locale = str_replace('-', '_', $acceptLocales[$q]);//转换字符串，变成标准的Locale Code,如 zh_CN
            $parts = mb_split('_', $locale, 2);
            if (count($parts) == 2) {
                $locale = strtolower($parts[0]) . '_' . strtoupper($parts[1]);
            }

            $acceptLocales[$q] = $locale;
        }//foreach结束

        if (count($acceptLocales) > 1) {
            $keys = array_keys($acceptLocales);//返回本地配置的数组的键名称
            rsort($keys, SORT_NUMERIC); #降序排序

            $langs = I18n_Options::listLocaleFiles();//检查本地配置文件

            foreach ($keys as $key) {
                $locale = $acceptLocales[$key];
                if (in_array($locale, $langs)) {//检查数组中是否存在某个值
                    $resultLocale = $locale;
                    break;
                } else {
                    $parts = mb_split('_', $locale, 2);
                    if (in_array($parts[0], $langs)) {
                        $resultLocale = $parts[0];
                        break;
                    }
                }
            }
        } else {
            $resultLocale = array_shift($acceptLocales);
        }

        if (empty($resultLocale)) {
            $resultLocale = "en";
        }

        return $resultLocale;
    }

    private function loadLang($locale)
    {
        if ($this->isSettingsPage) {
            $this->doLoadLang($locale, "lang/settings");
        } else {
            $this->doLoadLang($locale, "lang");
            $this->doLoadLang($locale, "usr/lang");
        }
    }

    private function doLoadLang($locale, $path)
    {
        $file = dirname(__DIR__) . "/{$path}/{$locale}.php";
        if (file_exists($file)) {
            $className = str_replace('/', '_', $path) . '_' . $locale;
            if (!class_exists($className)) {
                require_once($file);
            }
            if (class_exists($className)) {
                $lang = new $className();
            } else {
                $lang = null;
            }
            if (is_subclass_of($lang, "Lang")) {
                if (method_exists($lang, "translated")) {
                    $translated = $lang->translated();
                    if (is_array($translated)) {
                        $this->loadedLangs = array_merge($this->loadedLangs, $translated);
                        $this->loadSucceed = true;
                    }
                }
                if (method_exists($lang, "dateFormat")) {
                    $format = $lang->dateFormat();
                    if (empty($format) && empty($this->dateFormat)) {
                        $format = array(
                            "simple" => mget()->postDateFormat,
                            "detail" => mget()->postDateFormat
                        );
                    }
                    $this->dateFormat = $format;
                }
            }
        }
    }

    private function doTranslate($string)
    {
        $this->loadLangIfNotLoad();
        if (@array_key_exists($string, $this->loadedLangs)) {
            $translated = $this->loadedLangs[$string];
        } else {
            $translated = _t($string);
        }
        return $translated;
    }

    private static function Instance()
    {//新建一个I18n的实例
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /*begain*/
    public static function setLang($locale)
    {
        if ($locale === "") {
            $locale = "zh_CN"; //为空的时候使用中文
        }
        if (strtoupper($locale) != "AUTO") {
            $instance = self::Instance();
            $instance->setLocale($locale);
        }
    }

    public static function getLang()
    {
        $instance = self::Instance();
        return $instance->locale;
    }

    public static function loadAsSettingsPage($is)
    {
        $instance = self::Instance();
        $instance->setIsSettingPages($is);
    }

    public static function translate($string)
    {
        $instance = self::Instance();
        return $instance->doTranslate($string);
    }

    public static function dateFormat($type = "simple")
    {
        $instance = self::Instance();
        return $instance->dateFormat[$type];
    }
}

//国际化语言后台配置类
class I18n_Options
{
    private static function doListLocalFiles($path)
    {
        $dir = dirname(__DIR__) . "/{$path}";
        if (!file_exists($dir)) {
            return array();
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $it->setMaxDepth(1);//递归获取目录下面的文件名称

        $langs = array();
        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filename = $fileInfo->getFilename();
                $filename = str_replace('.php', '', $filename);
                $langs[] = $filename;
            }
        }

        return $langs;
    }

    private static function doListLangs($path)
    {//获取具体路径下的语言文件，并返回语言种类
        $dir = dirname(__DIR__) . "/{$path}";
        if (!file_exists($dir)) {
            return array();
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $it->setMaxDepth(1);//递归获取目录下面的文件名称

        $langs = array();


        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile()) {
                $file = $fileInfo->getPathname();
                $filename = $fileInfo->getFilename();
                //判断是否是.php 后缀
                if (!Utils::endsWith($filename, ".php")) {
                    continue;
                }
                $filename = str_replace('.php', '', $filename);
                if (is_readable($file)) {
                    //echo "  is readable";
                    $className = str_replace('/', '_', $path) . '_' . $filename;
                    //echo "   " . $className . '<br />';
                    if (!class_exists($className)) {//如果找不到该类名称，则将该类对应的class文件引进
                        //echo "yes ";
                        require_once($file);
                    }

                    if (class_exists($className)) {
                        $lang = new $className();
                        //echo "require OK !";
                    } else {
                        $lang = null;
                        //echo "reqire failed!";
                    }

                    if (is_subclass_of($lang, "Lang")) {
                        if (method_exists($lang, "locale")) {
                            $locale = $lang->locale();
                            if (!empty($locale) && method_exists($lang, "name")) {
                                $name = $lang->name();
                                if (!empty($name)) {
                                    $langs[$locale] = $name;
                                }
                            }
                        }
                    }
                }

            }
        }
        return $langs;
    }

    public static function listLangs($isSettingsPage = false)
    {//返回后台配置中显示的可使用的语言种类列表
        if ($isSettingsPage) {
            return self::doListLangs("lang/settings");
        } else {
            $lang = array('auto' => 'Auto');
            $langs = array_merge($lang, self::doListLangs("lang"), self::doListLangs("usr/lang"));
            return $langs;
        }

    }

    public static function listLocaleFiles($isSettingsPage = false)
    {
        $lang = array();
        if ($isSettingsPage) {//合并需要导入的文件的数组
            $langs = array_merge($lang, self::doListLocalFiles("lang/settings"));
        } else {
            $langs = array_merge($lang, self::doListLocalFiles("lang"), self::doListLocalFiles("usr/lang"));
        }
        return array_unique($langs);//移除数组中重复的值

    }
}

function _mt($string)
{
    if (func_num_args() <= 1) {//只有一个参数
        return I18n::translate($string);
        //echo "次浏览";
    } else {
        $args = func_get_args();//返回一个包含函数参数列表的数组
        array_shift($args);//将数组开头的单元移出数组
        return vsprintf(I18n::translate($string), $args);
    }
    //func_get_arg() 和 func_num_args() 一起使用，使得用户自定义函数可以接受自定义个数的参数列表。
}

function _me()
{
    $args = func_get_args();
    echo call_user_func_array('_mt', $args);
    //将$args数组的值作为_mt函数的参数值回调_mt函数
    //echo "次浏览";
}

function _mn($single, $plural, $number)
{
    return $number > 1 ? I18n::translate($plural) : I18n::translate($single);
}
