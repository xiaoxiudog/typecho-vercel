<?php

/*
 * 短代码编写
 */

class ScodeContent
{
    public static function parseColumnCallback($matches)
    {

        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $content = $matches[5];

        $pattern = Content::get_shortcode_regex(array('block'));
        preg_match_all("/$pattern/", $content, $block_matches);

        $html = '<div class="flex-column">';

        //$block_matches[3] 是一个数组，每个成员是block的属性字符串
        //$block_matches[5] 是一个数组，每个成员是block中间的内容

        for ($i = 0; $i < count($block_matches[3]); $i++) {
            $item = $block_matches[3][$i];
            $attr = htmlspecialchars_decode($item);//还原转义前的参数列表
            $attrs = Content::shortcode_parse_atts($attr);//获取短代码的参数

            //根据属性值来获取当前栏目的flex比例
            $flex = (@$attrs["size"]) ? $attrs["size"] : "auto";

            $content = $block_matches[5][$i];
            if (substr($content, 0, 4) == "<br>") {
                $content = substr($content, 4);
            }
            $html .= '<div class="flex-block" style="flex:' . $flex . '">' . htmlspecialchars_decode($content) . '</div>';
        }
        $html .= '</div>';

        return $html;


    }

    public static function parseExternalSingleVideo($info, $api, $needActive, $id)
    {

        $url = $api . $info["url"];
        $title = $info["title"];
        $active = $needActive ? "active" : "";
        $ret = <<<EOF
  <label class="btn btn-info $active" data-src="$url" data-iframe="$id" data-origin="{$info["url"]}">
    <input type="radio" name="options" id="option1"> $title
  </label>
EOF;
        return $ret;


    }

    /**
     * 视频解析的回调函数
     * @param $matches
     * @return bool|string
     */
    public static function videoParseCallback($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        // 判断是单个视频还是视频合集
        if ($matches[5] != "") {
            //视频合集，现在默认是外部解析接口
            // todo 暂不支持本地视频地址的合集
            //获取内部内容
            $pattern = Content::get_shortcode_regex(array('Video'));
            preg_match_all("/$pattern/", $matches[5], $all);
            $playerCode = "";
            if (sizeof($all[3])) {
                //当内部有内容时候，可能是一首歌曲或者多首歌曲
                $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
                $attrs = Content::shortcode_parse_atts($attr);//获取短代码的参数
                $isCollapse = @$attrs["status"] != "true";// 默认折叠
                $collapse = $isCollapse ? "collapse" : "collapse in";


                $index = 0;
                $id = uniqid();

                foreach ($all[3] as $vo) {
                    $info = Content::shortcode_parse_atts(htmlspecialchars_decode($vo));
                    $api = Utils::getExpertValue("video_external_api", "https://okjx.cc/?url=");

                    $title = _mt("正在播放：");
                    $list = _mt("剧集列表");

                    if ($index == 0) {
                        $first_url = $isCollapse ? $api . $info["url"] : "";
                        $iframe_url = $isCollapse ? "" : $api . $info["url"];
                        $playerCode .= <<<EOF
<div class="video-player panel panel-default box-shadow-wrap-lg collapse-panel">
    <div class="panel-heading panel-collapse" data-toggle="collapse" data-iframe="$id" data-src="$first_url" data-target="#video-$id" aria-expanded="false"><i data-feather="airplay"></i><span>{$title}</span><a id="origin_$id" target="_blank" href="{$info["url"]}"><b id="title_$id" class="video-name">{$info["title"]}</b></a></div>
     <div class="panel-body $collapse" id="video-$id">
                <div class="iframe_player embed-responsive embed-responsive-16by9">
                    <iframe id="{$id}" allowfullscreen="true" src="{$iframe_url}"></iframe>
                </div>
            
[collapse status="true" title="<i data-feather='list'></i> $list"]

<div class="btn-group" data-toggle="buttons">
EOF;
                    }
                    $playerCode .= self::parseExternalSingleVideo($info, $api, $index == 0, $id);
                    $index++;
                }

                $playerCode .= <<<EOF
</div>  <!--end of btn-group-->
[/collapse]
</div> <!--end of panel-body-->
</div> <!--end of video-player-->
EOF;


            }
            return $playerCode;
        } else {
            //单个视频
            //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
            $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/", '$1', $matches[3]);
            $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
            $attrs = Content::shortcode_parse_atts($attr);//获取短代码的参数
            if ($attrs['url'] !== null || $attrs['url'] !== "") {
                $url = $attrs['url'];
            } else {
                return "";
            }

            if (array_key_exists('pic', $attrs) && ($attrs['pic'] !== null || $attrs['pic'] !== "")) {
                $pic = $attrs['pic'];
            } else {
                $pic = STATIC_PATH . 'img/video.jpg';
            }
            $playCode = '<video src="' . $url . '" style="background-image:url(' .
                $pic . ');background-size: cover;"></video>';

            //把背景图片作为第一帧
//        $playCode = '<video src="' . $url . '" poster="'.$pic.'"></video>';
            return $playCode;
        }


    }


}
