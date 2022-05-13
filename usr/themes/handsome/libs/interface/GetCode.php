<?php
    /**
     * @author hewro
     * @description 生成网站二维码的接口
     */

require '../component/phpqrcode.php';


if ($_SERVER["REQUEST_METHOD"] == "GET"){

    $query=  '?'.$_SERVER['QUERY_STRING'];
    $index = strpos($query,"&content=");
    $url = substr($query,$index+9);
//    print_r($url);
    if (!empty($_GET['type']) && !empty($_GET['content'])){
        $type = $_GET['type'];
        $content = $url;
        getImageCode($content,$type);
    }

}


function getImageCode($content,$type){
    $value = $content;                  //二维码内容
    $errorCorrectionLevel = 'L';    //容错级别
    $matrixPointSize = 5;           //生成图片大小
    //生成二维码图片
    QRcode::png($value,false,$errorCorrectionLevel, $matrixPointSize, 2);

}


?>
