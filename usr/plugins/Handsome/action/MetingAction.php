<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

require_once dirname(__DIR__) . '/cache/cache.php';

/**
 * 初始文件代码来自：https://github.com/MoePlayer/APlayer-Typecho/blob/master/Action.php 原项目基于MIT协议
 * 为了避免与插件启用时候的冲突，修改了类名称 Meting_Action -> Handsome_Meting_Action
 */
class Handsome_Meting_Action extends Typecho_Widget implements Widget_Interface_Do
{
    static $netease_cookie = 'JSESSIONID-WYYY=%2FWV8dFk8DfP%5CsGlQcrvnj3E4rPT3QHOtEXbqtpIfa9e2rOvSE60Eb6ef4s4Tn%5CdzM3rXQY98sAQNs6SpzrqzaFZu%2Fx6KT6aWSU8nf4IgOjmXkYkZpRz2cOIRFgQIt0FkYzCAufRxCtT7HArR7QEIqixO14Be%5CF7AAabE8B%2F%2FkefWJHjQ%3A1647098647606; _iuqxldmzr_=32; _ntes_nnid=f2b3978f38f6a5ddc396d7db4f0782e5,1647096847633; _ntes_nuid=f2b3978f38f6a5ddc396d7db4f0782e5; NMTID=00OIdHlNGdvfHBKMkXpoN4iXdT-tvYAAAF_fp9eTg; WEVNSM=1.0.0; WNMCID=cgcwls.1647096847730.01.0; WM_NI=xv7fQfC%2BW1q01bQh7OcZUS0ICS9ueL4tikGFtbaKGey%2BWeXkvU0myEkCPbrTddzWdGMaAAFmaWPcys%2FLb%2FfwPEWJbKYnknZcwZ%2BnK1qCet50tw3aK%2B9jUJHj3MJiv3LodTY%3D; WM_NIKE=9ca17ae2e6ffcda170e2e6eeb6cd599bb5c0adfb7f8cbc8aa7d44f869b9e84f17ff2eaf888b579abf5a294c92af0fea7c3b92abcaaf987d46e81f599d2f43cf3be9f89b645a1869bb4b343818df796e848f8aca78cc64eb8beafacc66fbbf59986db61b68b008efc4fa99da083f160a8bcb6b8e74d8fbf8fa2c567a389a084fc79ed94aed0e57d949b99abe1419bb2a58fd969b586e58fca3bb39dab95b83cb8a9fd82ca21fbb69babcb62f193868af53990b1acb5dc37e2a3; WM_TID=dJbvo6YAlqREFRUFFAJ7%2F26olq8y7yy%2B; __csrf=c61e62e3715bc2a3b7826d0fb44c51d7; MUSIC_U=9d1749e7dc18cec133052c452e124c29b9e9c1146ae0da54ee54d4e51591487f993166e004087dd3d78b6050a17a35e705925a4e6992f61dfe3f0151024f9e31; __remember_me=true; ntes_kaola_ad=1';
    static $tencent_cookie = 'pgv_pvid=7011075408; fqm_pvqid=c9dcdac5-9801-4260-8542-02e8afb652e5; fqm_sessionid=033da63d-c067-4cd1-aa22-415e9a2ad3fe; pgv_info=ssid=s4342232304; ts_last=y.qq.com/; ts_refer=music.qq.com/; ts_uid=3641312091; RK=un/luMc0ev; ptcz=820a203adc3aa8411becbbb4c79a74f7ef49657cd35a05ea9fceaa96cbe6c874; login_type=1; qm_keyst=Q_H_L_53n8FLhZv-LcsnVvHyAlZC7ImWHIcrDMZW73pexh5vpfIaKVsdkKRXg; euin=oKcl7Kc5ow-lNn**; psrf_qqrefresh_token=39E16E19A2CE961BEEEDB15407FAF384; psrf_qqaccess_token=89230D292EFF60B4E342F3204A1A8936; tmeLoginType=2; psrf_musickey_createtime=1647096981; psrf_access_token_expiresAt=1654872981; psrf_qqopenid=63A26BCA9A6F9F36C3DE491FAF8DACF6; psrf_qqunionid=49CA293F90070C2AE233B49BA2F615BB; wxopenid=; qm_keyst=Q_H_L_53n8FLhZv-LcsnVvHyAlZC7ImWHIcrDMZW73pexh5vpfIaKVsdkKRXg; uin=1875812278; qqmusic_key=Q_H_L_53n8FLhZv-LcsnVvHyAlZC7ImWHIcrDMZW73pexh5vpfIaKVsdkKRXg; wxunionid=; wxrefresh_token=';

    public function execute()
    {
    }

    public function action()
    {
//        $this->on($this->request->is('do=update'))->update();
        $this->on($this->request->is('do=parse'))->shortcode();
        $this->on($this->request->isGet())->api();
    }

    private function check($server, $type, $id)
    {
        if (!in_array($server, array('netease', 'tencent', 'baidu', 'xiami', 'kugou'))) {
            return false;
        }
        if (!in_array($type, array('song', 'album', 'search', 'artist', 'playlist', 'lrc', 'url', 'pic'))) {
            return false;
        }
        if (empty($id)) {
            return false;
        }
        return true;
    }

    private function api()
    {
        // 参数检查
//        $this->filterReferer();

        //$this->request->isSecure() 判断是http还是https，
        $protocol = $this->request->isSecure() ? "https" : "http";

        $server = $this->request->get('server');
        $type = $this->request->get('type');
        $id = $this->request->get('id');

        if (!$this->check($server, $type, $id)) {
            http_response_code(403);
            die();
        } else {
            http_response_code(200);
        }

        // 加载 Meting 模块
        if (!extension_loaded('Handsome')) {
            include_once dirname(__DIR__) . '/libs/Meting.php';
        }
        $api = new \Metowolf\Meting($server);
        $api->format(true);

        $cookie = "";
        if ($server == "netease") {
            $cookie = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->cookie;
            if (empty($cookie)) {
                $cookie = self::$netease_cookie;
            }
        } else if ($server == "tencent") {
            $cookie = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->qq_cookie;
            if (empty($cookie)) {
                $cookie = self::$tencent_cookie;
            }
        }
        if (!empty($cookie)) {
            $api->cookie($cookie);
        }

        // 加载 Meting Cache 模块
        $this->cache = new CacheUtil("music");


        //auth 验证
        $EID = $server . $type . $id;
        $salt = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->salt;

        if (!empty($salt)) {
            $auth1 = md5($salt . $EID . $salt);
            $auth2 = $this->request->get('auth');
            if (strcmp($auth1, $auth2)) {
                http_response_code(403);
                die();
            }
        }

        // 歌词解析
        if ($type == 'lrc') {
            $data = $this->cache->cacheRead($EID);
            if (empty($data)) {
                $data = $api->lyric($id);
                $this->cache->cacheWrite($EID, $data, 86400);
            }
            $data = json_decode($data, true);
//            header("Content-Type: application/javascript");
            if (!empty($data['tlyric'])) {
                echo $this->lrctran($data['lyric'], $data['tlyric']);
            } else {
                echo $data['lyric'];
            }
        }

        // 专辑图片解析
        if ($type == 'pic') {
            $data = $this->cache->cacheRead($EID);
            if (empty($data)) {
                $data = $api->pic($id, 90);
                $this->cache->cacheWrite($EID, $data, 86400);
            }
            $data = json_decode($data, true);
            $this->response->redirect($data['url']);
        }

        // 歌曲链接解析
        if ($type == 'url') {
            $data = $this->cache->cacheRead($EID);
            if (empty($data)) {
                //todo bitrate
//                $rate = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->bitrate;
                $rate = 128;
                $data = $api->url($id, $rate);
                $this->cache->cacheWrite($EID, $data, 1200);
            }
            $data = json_decode($data, true);
            $url = $data['url'];

            if ($server == 'netease') {
                $url = str_replace('://m7c.', '://m7.', $url);
                $url = str_replace('://m8c.', '://m8.', $url);
                $url = str_replace('http://m10.', $protocol . '://m10.', $url);
            }


            if ($server == 'xiami') {
                $url = str_replace('http://', $protocol . '://', $url);
            }

            if ($server == 'baidu') {
                $url = str_replace('http://zhangmenshiting.qianqian.com', $protocol . '://gss3.baidu.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu', $url);
            }

            if ($server == "tencent") {
                $url = str_ireplace("ws.stream.qqmusic.qq.com", "dl.stream.qqmusic.qq.com", $url);
            }

            if (empty($url)) {
                $url = '';
            }

            $url = str_replace('http://', $protocol . '://', $url);

            $this->response->redirect($url);
        }

        // 其它类别解析
        if (in_array($type, array('song', 'album', 'search', 'artist', 'playlist'))) {
            $data = $this->cache->cacheRead($EID);
            if (empty($data)) {
                $data = $api->$type($id);
                $this->cache->cacheWrite($EID, $data, 7200);
            }
            $data = json_decode($data, 1);
            $url = Typecho_Common::url('action/handsome-meting-api', Helper::options()->index);

            $music = array();
            foreach ($data as $vo) {
                $music[] = array(
                    'name' => $vo['name'],
                    'artist' => implode(' / ', $vo['artist']),
                    'url' => $url . '?server=' . $vo['source'] . '&type=url&id=' . $vo['url_id'] . '&auth=' . md5($salt . $vo['source'] . 'url' . $vo['url_id'] . $salt),
                    'cover' => $url . '?server=' . $vo['source'] . '&type=pic&id=' . $vo['pic_id'] . '&auth=' . md5($salt . $vo['source'] . 'pic' . $vo['pic_id'] . $salt),
                    'lrc' => $url . '?server=' . $vo['source'] . '&type=lrc&id=' . $vo['lyric_id'] . '&auth=' . md5($salt . $vo['source'] . 'lrc' . $vo['lyric_id'] . $salt),
                );
            }
            header("Content-Type: application/javascript");
            echo json_encode($music);
        }
    }

    private function shortcode()
    {
        http_response_code(200);

        $url = $this->request->get('data');

        $url = trim($url);
        if (empty($url)) {
            return;
        }
        $server = 'netease';
        $id = '';
        $type = '';
        if (strpos($url, '163.com') !== false) {
            $server = 'netease';
            if (preg_match('/playlist\/?(\?id=)?(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[2], 'playlist');
            } elseif (preg_match('/toplist\/?(\?id=)?(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[2], 'playlist');
            } elseif (preg_match('/album\/?(\?id=)?(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[2], 'album');
            } elseif (preg_match('/song\/?(\?id=)?(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[2], 'song');
            } elseif (preg_match('/artist\/?(\?id=)?(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[2], 'artist');
            }
        } elseif (strpos($url, 'qq.com') !== false) {
            $server = 'tencent';
            if (preg_match('/playsquare\/([^\.]*)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'playlist');
            } elseif (preg_match('/playlist\/([^\.]*)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'playlist');
            } elseif (preg_match('/album\/([^\.]*)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'album');
            } elseif (preg_match('/song\/([^\.]*)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'song');
            } elseif (preg_match('/songDetail\/([^\.]*)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'song');
            } elseif (preg_match('/singer\/([^\.]*)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'artist');
            }
        } elseif (strpos($url, 'xiami.com') !== false) {
            $server = 'xiami';
            if (preg_match('/collect\/(\w+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'playlist');
            } elseif (preg_match('/album\/(\w+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'album');
            } elseif (preg_match('/[\/.]\w+\/[songdem]+\/(\w+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'song');
            } elseif (preg_match('/artist\/(\w+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'artist');
            }
            if (!preg_match('/^\d*$/i', $id, $t)) {
                $data = self::curl($url);
                preg_match('/' . $type . '\/(\d+)/i', $data, $id);
                $id = $id[1];
            }
        } elseif (strpos($url, 'kugou.com') !== false) {
            $server = 'kugou';
            if (preg_match('/special\/single\/(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'playlist');
            } elseif (preg_match('/#hash\=(\w+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'song');
            } elseif (preg_match('/album\/[single\/]*(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'album');
            } elseif (preg_match('/singer\/[home\/]*(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'artist');
            }
        } elseif (strpos($url, 'baidu.com') !== false) {
            $server = 'baidu';
            if (preg_match('/songlist\/(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'playlist');
            } elseif (preg_match('/album\/(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'album');
            } elseif (preg_match('/song\/(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'song');
            } elseif (preg_match('/artist\/(\d+)/i', $url, $id)) {
                list($id, $type) = array($id[1], 'artist');
            }
        } else {
            die("[hplayer]\n[Music title=\"歌曲名\" author=\"歌手\" url=\"{$url}\" pic=\"图片文件URL\" lrc=\"歌词文件URL\"/]\n[/hplayer]\n");
            return;
        }
        if (is_array($id)) {
            $id = '';
        }
        die("[hplayer]\n[Music server=\"{$server}\" id=\"{$id}\" type=\"{$type}\"/]\n[/hplayer]\n");

    }

    private function lrctrim($lyrics)
    {
        $result = "";
        $lyrics = explode("\n", $lyrics);
        $data = array();
        foreach ($lyrics as $key => $lyric) {
            preg_match('/\[(\d{2}):(\d{2}[\.:]?\d*)]/', $lyric, $lrcTimes);
            $lrcText = preg_replace('/\[(\d{2}):(\d{2}[\.:]?\d*)]/', '', $lyric);
            if (empty($lrcTimes)) {
                continue;
            }
            $lrcTimes = intval($lrcTimes[1]) * 60000 + intval(floatval($lrcTimes[2]) * 1000);
            $lrcText = preg_replace('/\s\s+/', ' ', $lrcText);
            $lrcText = trim($lrcText);
            $data[] = array($lrcTimes, $key, $lrcText);
        }
        sort($data);
        return $data;
    }

    private function lrctran($lyric, $tlyric)
    {
        $lyric = $this->lrctrim($lyric);
        $tlyric = $this->lrctrim($tlyric);
        $len1 = count($lyric);
        $len2 = count($tlyric);
        $result = "";
        for ($i = 0, $j = 0; $i < $len1 && $j < $len2; $i++) {
            while ($lyric[$i][0] > $tlyric[$j][0] && $j + 1 < $len2) {
                $j++;
            }
            if ($lyric[$i][0] == $tlyric[$j][0]) {
                $tlyric[$j][2] = str_replace('/', '', $tlyric[$j][2]);
                if (!empty($tlyric[$j][2])) {
                    $lyric[$i][2] .= " ({$tlyric[$j][2]})";
                }
                $j++;
            }
        }
        for ($i = 0; $i < $len1; $i++) {
            $t = $lyric[$i][0];
            $result .= sprintf("[%02d:%02d.%03d]%s\n", $t / 60000, $t % 60000 / 1000, $t % 1000, $lyric[$i][2]);
        }
        return $result;
    }


    private function curl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_REFERER, $url);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }


    private function filterReferer()
    {
        $salt = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->salt;
        if (empty($salt)) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie");
            return;
        }
        //todo 对于非 80 端口的网址匹配有问题，待fix
//        if (isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
//            http_response_code(403);
//            die('[]');
//        }
    }
}
