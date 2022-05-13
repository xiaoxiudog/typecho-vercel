<?php

class Tool{
    public static function json_decode($str,$flag=false){
        $ret = json_decode($str,$flag);
        if ($ret == null){
            return [];
        }else{
            return $ret;
        }
    }
}
