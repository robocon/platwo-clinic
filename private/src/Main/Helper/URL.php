<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/19/14
 * Time: 4:47 PM
 */

namespace Main\Helper;


class URL {
    public static function absolute($url){
        $base_url = (strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : '');
        return $base_url.$url;
    }

    public static function share($url){
        return "http://pla2app.com/thaweeyont/share".$url;
    }
}