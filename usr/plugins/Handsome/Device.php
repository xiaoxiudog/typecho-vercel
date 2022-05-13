<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Device.php
 * Author     : Hran
 * Date       : 2016/12/10
 * Version    :
 * Description:
 */
class Device {
    private static $_userAgent;

    private static function getUserAgent() {
        if (empty(self::$_userAgent)) {
            self::$_userAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);
        }
        return self::$_userAgent;
    }

    public static function isMobile(){
        return self::is('Mobile');
    }
    public static function isPhone(){
        if(self::isTablet()){
            return false;
        }
        return self::is(array("Android", 'iPhone', 'iPod', 'Phone'));
    }
    public static function isTablet(){
        return self::is(array("iPad", 'Tablet'));
    }
    public static function isWindowsAboveVista() {
        return self::isWindows() && self::getWindowsNTVersion() >= 6;
    }
    public static function isWindowsBlowXp(){
        return self::isWindows() && self::getWindowsNTVersion() < 6;
    }
    public static function isWindowsBlowWin8() {
        return self::isWindows() && self::getWindowsNTVersion() <= 6.1;
    }
    public static function isWindows() {
        return self::is("Windows");
    }
    public static function isMacOSX() {
        return self::is("Macintosh");
    }
    public static function isSpider() {
        return self::is(array("Spider", "YodaoBot", "Googlebot", "Bingbot", "Slurp", "MSNBot", "DuckDuckBot", "YandexBot", "yahoo-blogs", "psbot", "ia_archiver"));
    }
    public static function isELCapitanOrAbove() {
        $version = self::getMacOSXVersion();
        if ($version && $version >= 11) {
            return true;
        }
        return false;
    }
    public static function isSierraOrAbove(){
        $version = self::getMacOSXVersion();
        if ($version && $version >= 12) {
            return true;
        }
        return false;
    }
    public static function getMacOSXVersion(){
        //Mac OS X 10_11_4
        if (preg_match('/^.+Mac\s+OS\s+X\s+\d+[^a-zA-Z0-9]+(\d+).*$/i', self::getUserAgent(), $matches)) {
            return intval($matches[1]);
        }
        return false;
    }
    public static function getWindowsNTVersion() {
        //Windows NT 6.1
        if (preg_match('/^.*Windows\s+NT\s+([0-9\.]+).*$/i', self::getUserAgent(), $matches)) {
            return doubleval($matches[1]);
        }
        return false;
    }
    public static function isIE() {
        return self::check(array("Trident", "Windows"), true);
    }
    public static function isSafari() {
        return self::check(array("Safari", "Version/"), true) && !self::check(array("Chrome", "Opera", "OPR", "QQ"), false);
    }
    public static function is($is, $not = array(), $needAllMatch = false){
        if(empty($not)){
            return self::check($is, $needAllMatch);
        }
        return self::check($is, $needAllMatch) && !self::check($not, $needAllMatch);
    }
    public static function shouldEnableBlurFilter(){
        if(self::check(array("Firefox")) || (self::is(array("Chrome"), array("Edge")))){
            return false;
        }
        if (self::isMobile()){
            if(self::check(array("Android"))){
                return false;
            }
        }
        return true;
    }
    public static function canEnableWebP() {
        return @strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }
    private static function check($items, $needAllMatch = false) {
        if(is_array($items)){
            if($needAllMatch){
                $res = true;
                foreach ($items as $item) {
                    if(!strpos(self::getUserAgent(), strtolower($item))){
                        $res = false;
                    }
                }
                return $res;
            }else{
                foreach ($items as $item) {
                    if(strpos(self::getUserAgent(), strtolower($item))){
                        return true;
                    }
                }
                return false;
            }
        }else{
            return strpos(self::getUserAgent(), strtolower($items));
        }
    }
}