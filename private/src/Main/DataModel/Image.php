<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/19/14
 * Time: 2:49 PM
 */

namespace Main\DataModel;


use Main\DB;
use Main\Helper\URL;

class Image {
//    const BASE_URL = "http://".MEDIA_HOST;
    protected $id, $width, $height, $url;
//    private $picture = [];
    protected function __construct($id, $width, $height, $url = null){
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
        $this->url = $url;
    }

    public static function absoluteUrl($url){
        return 'http://'.MEDIA_HOST.'/'.$url;
    }

    public function toArray(){
        return [
            'id'=> $this->id,
            'width'=> $this->width,
            'height'=> $this->height
        ];
    }

    public function toArrayResponse(){
        return [
            'id'=> $this->id,
            'width'=> $this->width,
            'height'=> $this->height,
            'url'=> $this->url
        ];
    }

    public static function upload($b64){
        $url = self::absoluteUrl('post');
        $response = @\Unirest::post($url, ["Accept" => "application/json"], ["img"=> $b64]);
//        $response->code; // HTTP Status code
//        $response->headers; // Headers
//        $response->raw_body; // Unparsed body

        $data = $response->body; // Parsed body
        return new self($data->objectid, $data->original_size->original_width, $data->original_size->original_height);
    }

    public static function load($params){
        
        if (empty($params['url'])) {
            $params['url'] = 'http://'.MEDIA_HOST.'/get/'.$params['id'].'/';
        }
        
        return new self($params['id'], $params['width'], $params['height'], $params['url']);
    }

    public static function loads($loads){
        $data = new ImageCollection();
        foreach($loads as $load){
            $data[] = self::load($load);
        }

        return $data;
    }
}