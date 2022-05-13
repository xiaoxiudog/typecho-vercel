<?php

/**
 * Content.php
 * Author     : hewro
 * Date       : 2021/12/08
 * Version    : 1.0.0
 * Description: 文章相关的PHP输出
 */
class PostContent{
    function __construct() {

    }


    /**
     * 输出打赏信息
     * @param int $cid
     * @param bool $ifHideStar 是否需要隐藏点赞按钮
     * @param bool $ifHideReward 是否需要隐藏打赏按钮
     * @return string
     */
    public static function exportPayForAuthors($cid,$ifHideStar =false,$ifHideReward=false)
    {

        $returnHtml = "";
        if ($ifHideStar && $ifHideReward){
            return $returnHtml;
        }

        $returnHtml .= '<div class="support-author">';

        $options = mget();

        // 1.打赏部分
        if (!$ifHideReward){
            //        $color = Content::getThemeColor()[3];
            $color = "yellow";
            $returnHtml .= '
                 <button id="support_author"  data-toggle="modal" data-target="#myModal" class="box-shadow-wrap-lg btn_post_footer btn btn-pay btn-' . $color . ' btn-rounded"><svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="icon" aria-hidden="true"><path d="M10.084 7.606c3.375-1.65 7.65-1.154 10.493 1.487 3.497 3.25 3.497 8.519 0 11.77-3.498 3.25-9.167 3.25-12.665 0-.897-.834-2.488-2.96-2.488-5.085" stroke="currentColor" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17.392 14.78s1.532-1.318-.053-2.79c-1.585-1.473-3.17-.404-3.719.403-.549.807.495 2.082.93 2.69.434.61 1.364 2.182-.054 3.202-1.417 1.012-3.002.658-4.153-.708-1.15-1.367-.602-3.365 0-3.924M17.338 11.982l1.159-1.076M9.87 18.922l.937-.871" stroke="currentColor" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M.8 1.205s7.15 4.673 8.773 6.182c1.623 1.508 3.231 4.008 1.616 5.509-2.195 2.04-4.054.595-6.737-.75-.884-.447-3.15-1.777-3.15-1.777M10.136.9l1.047 3.188" stroke="currentColor" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg><span>' . _mt("打赏") . '</span></button>';

            //打赏的model
            $returnHtml .=' <div id="myModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
                 <div class="modal-dialog modal-sm" role="document">
                     <div class="modal-content box-shadow-wrap-lg">
                         <div class="modal-header box-shadow-bottom-normal">
                             <button type="button" class="close" data-dismiss="modal"><i style="vertical-align: bottom;" data-feather="x-circle"></i></button>
                             <h4 class="modal-title">' . _mt("赞赏作者") . '</h4>
                         </div>
                         <div class="modal-body">
                             <div class="solid-tab tab-container post_tab">
                                <ul class="nav no-padder b-b scroll-hide" role="tablist">';

            if ($options->AlipayPic != null) {
                $returnHtml .= ' <li class="nav-item active" role="presentation"><a class="nav-link active" style="" data-toggle="tab"  role="tab" data-target="#alipay_author"><i class="iconfont icon-alipay" aria-hidden="true"></i>'._mt("支付宝").'</a></li>';
            }
            if ($options->WechatPic != null) {
                $active = "";
                if ($options->AlipayPic == null){
                    $active = "active";
                }
                $returnHtml .= '<li class="nav-item '.$active.'" role="presentation"><a class="nav-link " style="" data-toggle="tab"  role="tab" data-target="#wechatpay_author"><i class="iconfont icon-wechatpay" aria-hidden="true"></i>'._mt("微信").'</a></li>';
            }
            $returnHtml .='        </ul>
                                <div class="tab-content no-border">';

            if ($options->AlipayPic != null) {
                $returnHtml .= '<div role="tabpanel" id="alipay_author" class="tab-pane fade active in">
                            <img noGallery class="pay-img tab-pane" id="alipay_author" role="tabpanel" src="' . Utils::choosePlaceholder(mget()) . '" data-original="' . $options->AlipayPic . '" />
                            </div>';
            }
            if ($options->WechatPic != null) {
                $active = "";
                if ($options->AlipayPic == null){
                    $active = "active in";
                }
                $returnHtml .= '<div role="tabpanel" id="wechatpay_author" class="tab-pane fade '.$active.' ">
                            <img noGallery  class="pay-img tab-pane" id="wechatpay_author" role="tabpanel" src="' . Utils::choosePlaceholder(mget()) . '" data-original="' . $options->WechatPic . '" />
                            </div>';
            }

            $returnHtml .= '    </div><!--tab-content-->
                             </div> <!--tab-container-->';



            $returnHtml .= '</div> <!--modal-body-->
                         </div><!--modal-content-->
                     </div><!--modal-dialog-->
                 </div><!--modal-->
        ';
        }


        // 2.点赞部分
        if (!$ifHideStar){
            $star_num = Database::getDataByField("stars",Database::$type_int_10,"contents",'cid = ?', $cid);
            $stars = Typecho_Cookie::get('extend_post_stars');
            if (empty($stars)) {
                $stars = array();
            } else {
                $stars = explode(',', $stars);
            }
            $star_class = (in_array($cid, $stars)) ? "like_button_active " : "";// 是否点赞了
            $returnHtml .='<button id="star_post" data-cid="'.$cid.'" class="'.$star_class.'box-shadow-wrap-lg btn_post_footer like_button btn btn-pay btn-rounded">
                 <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shake-little unlike_svg feather feather-thumbs-up"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                 <div class="circle-rounded"></div>
                 <svg class="liked_svg" style="transform: scale(2.2);" xmlns="http://www.w3.org/2000/svg" viewBox="30 30 60 60" width="60" height="60" preserveAspectRatio="xMidYMid meet">
                    <g clip-path="url(#__lottie_element_1061)">
                        <g style="display: block;" transform="matrix(1,-0.0000012433954452717444,0.0000012433954452717444,1,47.87498474121094,47.057003021240234)" opacity="1">
                            <g class="like_rotate">
                                <g opacity="1" transform="matrix(1,0,0,1,14.376999855041504,11.416000366210938)">
                                    <path stroke-linecap="butt" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="10" stroke="rgb(255,255,255)" stroke-opacity="1" stroke-width="2" d=" M-7.936999797821045,9.531000137329102 C-7.936999797821045,9.531000137329102 3.378000020980835,9.531000137329102 3.378000020980835,9.531000137329102 C3.815999984741211,9.531000137329102 4.209000110626221,9.258999824523926 4.360000133514404,8.847000122070312 C4.360000133514404,8.847000122070312 7.501999855041504,0.36000001430511475 7.501999855041504,0.36000001430511475 C8.020000457763672,-1.0360000133514404 6.986000061035156,-2.5199999809265137 5.497000217437744,-2.5199999809265137 C5.497000217437744,-2.5199999809265137 -0.5669999718666077,-2.5199999809265137 -0.5669999718666077,-2.5199999809265137 C-0.6399999856948853,-2.5199999809265137 -0.6859999895095825,-2.5969998836517334 -0.6499999761581421,-2.6600000858306885 C-0.36800000071525574,-3.1679999828338623 0.6269999742507935,-4.922999858856201 0.8870000243186951,-5.764999866485596 C1.309000015258789,-7.13100004196167 0.847000002861023,-8.715999603271484 -1.4539999961853027,-9.519000053405762 C-1.4759999513626099,-9.526000022888184 -1.4989999532699585,-9.527000427246094 -1.5180000066757202,-9.519000053405762 C-1.5299999713897705,-9.513999938964844 -1.5410000085830688,-9.505999565124512 -1.5490000247955322,-9.494000434875488 C-1.7309999465942383,-9.234999656677246 -2.6489999294281006,-7.934000015258789 -3.6419999599456787,-6.52400016784668 C-4.795000076293945,-4.888000011444092 -6.050000190734863,-3.1059999465942383 -6.380000114440918,-2.638000011444092 C-6.434000015258789,-2.562000036239624 -6.519000053405762,-2.5199999809265137 -6.611000061035156,-2.5199999809265137 C-6.611000061035156,-2.5199999809265137 -7.938000202178955,-2.5199999809265137 -7.938000202178955,-2.5199999809265137 C-7.982999801635742,-2.5199999809265137 -8.020000457763672,-2.4839999675750732 -8.020000457763672,-2.437999963760376 C-8.020000457763672,-2.437999963760376 -8.020000457763672,9.447999954223633 -8.020000457763672,9.447999954223633 C-8.020000457763672,9.494000434875488 -7.982999801635742,9.531000137329102 -7.936999797821045,9.531000137329102z"></path>
                                    <path fill="rgb(255,255,255)" fill-opacity="1" d=" M-7.936999797821045,9.531000137329102 C-7.936999797821045,9.531000137329102 3.378000020980835,9.531000137329102 3.378000020980835,9.531000137329102 C3.815999984741211,9.531000137329102 4.209000110626221,9.258999824523926 4.360000133514404,8.847000122070312 C4.360000133514404,8.847000122070312 7.501999855041504,0.36000001430511475 7.501999855041504,0.36000001430511475 C8.020000457763672,-1.0360000133514404 6.986000061035156,-2.5199999809265137 5.497000217437744,-2.5199999809265137 C5.497000217437744,-2.5199999809265137 -0.5669999718666077,-2.5199999809265137 -0.5669999718666077,-2.5199999809265137 C-0.6399999856948853,-2.5199999809265137 -0.6859999895095825,-2.5969998836517334 -0.6499999761581421,-2.6600000858306885 C-0.36800000071525574,-3.1679999828338623 0.6269999742507935,-4.922999858856201 0.8870000243186951,-5.764999866485596 C1.309000015258789,-7.13100004196167 0.847000002861023,-8.715999603271484 -1.4539999961853027,-9.519000053405762 C-1.4759999513626099,-9.526000022888184 -1.4989999532699585,-9.527000427246094 -1.5180000066757202,-9.519000053405762 C-1.5299999713897705,-9.513999938964844 -1.5410000085830688,-9.505999565124512 -1.5490000247955322,-9.494000434875488 C-1.7309999465942383,-9.234999656677246 -2.6489999294281006,-7.934000015258789 -3.6419999599456787,-6.52400016784668 C-4.795000076293945,-4.888000011444092 -6.050000190734863,-3.1059999465942383 -6.380000114440918,-2.638000011444092 C-6.434000015258789,-2.562000036239624 -6.519000053405762,-2.5199999809265137 -6.611000061035156,-2.5199999809265137 C-6.611000061035156,-2.5199999809265137 -7.938000202178955,-2.5199999809265137 -7.938000202178955,-2.5199999809265137 C-7.982999801635742,-2.5199999809265137 -8.020000457763672,-2.4839999675750732 -8.020000457763672,-2.437999963760376 C-8.020000457763672,-2.437999963760376 -8.020000457763672,9.447999954223633 -8.020000457763672,9.447999954223633 C-8.020000457763672,9.494000434875488 -7.982999801635742,9.531000137329102 -7.936999797821045,9.531000137329102z"></path>
                                </g>
                                <g opacity="1" transform="matrix(1,0,0,1,2.694000005722046,14.967000007629395)">
                                    <path fill="rgb(255,255,255)" fill-opacity="1" d=" M0.5019999742507935,7.0269999504089355 C0.5019999742507935,7.0269999504089355 -0.5019999742507935,7.0269999504089355 -0.5019999742507935,7.0269999504089355 C-0.7789999842643738,7.0269999504089355 -1.003999948501587,6.802000045776367 -1.003999948501587,6.525000095367432 C-1.003999948501587,6.525000095367432 -1.003999948501587,-6.525000095367432 -1.003999948501587,-6.525000095367432 C-1.003999948501587,-6.802000045776367 -0.7789999842643738,-7.0269999504089355 -0.5019999742507935,-7.0269999504089355 C-0.5019999742507935,-7.0269999504089355 0.5019999742507935,-7.0269999504089355 0.5019999742507935,-7.0269999504089355 C0.7789999842643738,-7.0269999504089355 1.003999948501587,-6.802000045776367 1.003999948501587,-6.525000095367432 C1.003999948501587,-6.525000095367432 1.003999948501587,6.525000095367432 1.003999948501587,6.525000095367432 C1.003999948501587,6.802000045776367 0.7789999842643738,7.0269999504089355 0.5019999742507935,7.0269999504089355z"></path>
                                </g>
                            </g>
                        </g>
                    </g>
                 </svg>
                 <span>' . _mt("赞") . '&nbsp;<span id="like_label" class="like_label">'.$star_num.'</span></span>
                 </button>';
        }


        //整体结束位置
        if ($options->payTips == "") {
            $payTips = _mt("如果觉得我的文章对你有用，请随意赞赏");
        } else {
            $payTips = $options->payTips;
        }
        $returnHtml .='<div class="mt20 text-center article__reward-info">
                        <span class="mr10">' . $payTips . '</span>
                       </div>
                     </div><!--support-author-->';

        return $returnHtml;
    }



    /**
     * 输出文章底部信息
     * @param $time
     * @param $obj
     * @param $archive
     * @return string
     */
    public static function exportPostFooter($time, $obj, $archive)
    {

        $content = "";
        $interpretation = "";
        if ($archive->fields->reprint == "" || $archive->fields->reprint == "standard") {
            $content = _mt("允许规范转载");
            $interpretation = _mt("转载请保留本文转载地址，著作权归作者所有");
        } else if ($archive->fields->reprint == "pay") {
            $content = _mt("允许付费转载");
            $interpretation = _mt("您可以联系作者通过付费方式获得授权");
        } else if ($archive->fields->reprint == "forbidden") {
            $content = _mt("禁止转载");
            $interpretation = _mt("除非获得原作者的单独授权，任何第三方不得转载");
        } else if ($archive->fields->reprint == "trans") {
            $content = _mt("转载自他站");
            $interpretation = _mt("本文转载自他站，转载请保留原站地址");
        } else if ($archive->fields->reprint == "internet") {
            $content = _mt("来自互联网");
            $interpretation = _mt("本文来自互联网，未知来源，侵权请联系删除");
        }
        $html = "";
        $html .= '<div class="show-foot">';
        if (@Utils::getExpertValue("show_post_last_date", true) !== false) {
            $html .= '<div class="notebook" data-toggle="tooltip" data-original-title="'.date(I18n::dateFormat("detail"), $time + $obj).'">
                     <i class="fontello fontello-clock-o"></i>
                     <span>' . _mt("最后修改") . '：' . date(I18n::dateFormat(), $time + $obj) . '</span>
                 </div>';
        }

        $html .= '<div class="copyright" data-toggle="tooltip" data-html="true" data-original-title="' . $interpretation . '"><span>© ' . $content . '</span>
                 </div>
             </div>
        ';

        return $html;

    }


    //判断图片地址需要替换
    public static function handleCdnReplace($matches,$isImagePost){

        if (Utils::getCdnUrl(0) == ""){
            return $matches;
        }

        $options = mget();
        $localUrl = $options->rootUrl;//本地加速域名
        $imageSrc = $matches[2];
        //判断当前的图片地址是不是自己的博客地址前缀
        if (!Utils::startWith($imageSrc,$localUrl)){
            return $matches;
        }


        //1.获取cdn地址
        $cdnArray = explode("|", Utils::getCdnUrl(0));
        $cdnUrl = trim($cdnArray[0], " \t\n\r\0\x0B\2F");//cdn自定义域名
        $cdnArray_new = explode("|", Utils::getCdnUrl(1));
        $cdnUrl_new = trim($cdnArray_new[0], " \t\n\r\0\x0B\2F");//cdn自定义域名
        $cdn_temp_url = $cdnUrl;
        // 特化场景，如果是2021年上传的则使用第二个cdn地址
        if (Utils::useSecondCDNUrl($imageSrc)){
            $cdn_temp_url = $cdnUrl_new;
        }

        //2.替换地址前缀
        $matches[2]= str_replace($localUrl,$cdn_temp_url,$imageSrc);

        //3.判断是否需要加云处理后缀
        $alt = $matches[4];
        if (strpos($alt, " :ignore") == false) {
            return $matches;
        }

        //去除该alt 控制信息
        $matches[4] = str_replace(" :ignore", "", $alt);

        //4.添加后缀
        $width = 0;
        if ($isImagePost) {
            $width = 300;//图片的缩略图大小
        }
        $suffix = Utils::getImageAddOn($options, true, trim($cdnArray[1]), $width, 0, "post");//图片云处理后缀
        $matches[2].=$suffix;

        return $matches;


    }

    //进行图片尺寸的控制
    public static function handleImageSize($matches,$isImagePost){
        $alt = $matches[4];
        //对相册内部的图片大小不进行控制
        if ($isImagePost || strpos($alt, " :size") == false) {
            return $matches;
        }


        if (preg_match('/:size=(\d+)x(\d+)/', $alt, $ret)) {
            $w = $ret[1];
            $h = $ret[2];
            if ($w!=-1){
                $matches[6] .= 'width:'.$w.'px;';
            }
            if ($h!=-1){
                $matches[6] .= 'height:'.$h.'px;';
            }

            //去除该alt 控制信息
            $matches[4] = str_replace($ret[0], "", $alt);


        } else if (preg_match('/:size=(\d+)%/', $alt, $ret)) {
            $percent = $ret[1];
            $matches[6] .= 'width:'.$percent.'%;';

            $matches[4] = str_replace($ret[0], "", $alt);

        } else {
            //匹配不上，说明格式有问题
        }
        return $matches;
    }



    //处理延迟加载的图片
    public static function handleLazyload($matches,$isImagePost){
        $options = mget();
        $placeholder = Utils::choosePlaceholder($options);//图片占位符
        if ($isImagePost) {
            $matches[6] .= 'width:100%;height=100%;';
        }


        $matches[7] .= 'data-original="'.$matches[2] .'"';
        $matches[2] = $placeholder;


        return $matches;
    }


    public static function handleImages($obj,$content)
    {
        $isImagePost = Content::isImageCategory($obj->categories);

        $options = mget();
        //对vditor解析器不需要处理
        $isLazy = in_array('lazyload', Utils::checkArray( $options->featuresetup)) && Content::getPostParseWay($obj->fields->parseWay) != "vditor";
        $content = preg_replace_callback('/(<img\s[^>]*?src=")([^>]*?)("[^>]*?alt=")([^>]*?)("[^>]*?)\/?>/',
            function ($matches) use ($isImagePost,$isLazy) {
                //$matches[1] html部分
                //$matches[2] src 图片地址
                //$matches[3] html部分
                //$matches[4] alt
                //$matches[5] html部分

                //增加$matches[6] 来存储style的值
                $matches[] = 'style="';// style属性开始
                //增加$matches[7] 来存储额外增加的属性，比如data-original
                $matches[] = "";

                $matches = self::handleCdnReplace($matches,$isImagePost);
                $matches = self::handleImageSize($matches,$isImagePost);
                if ($isLazy){
                    $matches = self::handleLazyload($matches,$isImagePost);
                }

                $matches[6] .= '"';//style 属性闭合


                // 最后拼接各个部分组装成最终的html结构
                return $matches[1].$matches[2].$matches[3].$matches[4].$matches[5].$matches[6].$matches[7].">";

            }, $content);


        return $content;

    }


    /**
     * 输入内容之前做一些有趣的替换+输出文章内容
     *
     * @param $obj
     * @param $status
     * @param string $way
     * @return string
     */
    public static function postContent($obj, $status, $way = "origin")
    {
        if (Device::isIE() || $way == "origin") {
            $content = $obj->content;
        } else {//vditor
            $content = $obj->text;
//            $content = Handsome_Parsedown::instance()
//                ->setBreaksEnabled(false)
//                ->text($obj->content);
        }

        $options = mget();
        $isImagePost = Content::isImageCategory($obj->categories);
        if (!$isImagePost && trim($obj->fields->outdatedNotice) == "yes" && $obj->is('post')) {
            date_default_timezone_set("Asia/Shanghai");
            $created = round((time() - $obj->created) / 3600 / 24);
            $updated = round((time() - $obj->modified) / 3600 / 24);
            if ($updated >= 60 || $created > 80) {
                $content = '<div class="tip share">' . sprintf(_mt("请注意，本文编写于 %d 天前，最后修改于 %d 天前，其中某些信息可能已经过时。"),
                        $created, $updated) . '</div>
'."\n" . $content;
            }
        }

        $content = PostContent::handleImages($obj,$content);


        if ($isImagePost) {//照片文章
            $content = Content::postImagePost($content, $obj);
        } else {//普通文章
            if ($obj->hidden == true && trim($obj->fields->lock) != "") {//加密文章且没有访问权限
                echo '<p class="text-muted protected"><i class="glyphicon glyphicon-eye-open"></i>&nbsp;&nbsp;' . _mt("密码提示") . '：' . $obj->fields->lock . '</p>';
            }

            $db = Typecho_Db::get();
            $sql = $db->select()->from('table.comments')
                ->where('cid = ?', $obj->cid)
                ->where('status = ?', 'approved')
                ->where('mail = ?', $obj->remember('mail', true))
                ->limit(1);
            $result = $db->fetchAll($sql);//查看评论中是否有该游客的信息

            //文章中部分内容隐藏功能（回复后可见）1 号用户或者文章发布者可以不用评论直接访问
            if ($status == $obj->authorId || $status == 1 || $result) {
                $content = preg_replace("/\[hide\](.*?)\[\/hide\]/sm", '<div class="hideContent">'."\n\n".'$1</div>',
                    $content);
            } else {
                $content = preg_replace("/\[hide\](.*?)\[\/hide\]/sm", '<div class="hideContent">' . _mt("此处内容需要评论回复后（审核通过）方可阅读。") . '</div>', $content);
            }

            //仅登录用户可查看的内容
            if (strpos($content, '[login') !== false) {//提高效率，避免每篇文章都要解析
                $pattern = Content::get_shortcode_regex(array('login'));
                $isLogin = $status;
                $content = preg_replace_callback("/$pattern/", function ($matches) use ($isLogin) {
                    // 不解析类似 [[player]] 双重括号的代码
                    if ($matches[1] == '[' && $matches[6] == ']') {
                        return substr($matches[0], 1, -1);
                    }
                    if ($isLogin) {
                        if (substr($matches[5],0,4) == "<br>"){
                            $matches[5] = substr($matches[5],4);
                        }
                        return '<div class="hideContent">'."\n\n" . $matches[5] . '</div>';
                    } else {
                        return '<div class="hideContent">' . _mt("该部分仅登录用户可见") . '</div>';
                    }
                }, $content);
            }
            $content = Content::parseContentPublic($content);
        }
        return trim($content);
    }
}