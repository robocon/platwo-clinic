<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/9/15
 * Time: 12:18 PM
 */

namespace Main\CTL;

use Main\Context\Context;
use Main\DataModel\Image;
use Main\DB;
use Main\Event\Event;
use Main\Exception\Service\ServiceException;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\NotifyHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\UpdatedTimeHelper;
use Main\Helper\URL;
use Valitron\Validator;
use Main\Helper\UserHelper;

/**
 * @Restful
 * @uri /demo
 */
class DemoCTL extends BaseCTL {
    
    /**
     * @POST
     * @uri /message
     */
    public function send_message() {
        try {
            
            $db = DB::getDB();
            $item = $db->feed->findOne(['_id' => new \MongoId('54ffc80310f0ed69058b4567')]);
            
            
            UserHelper::check_token();
            $pre_user = UserHelper::getUserDetail();
            
            $user = $db->users->findOne(['_id' => new \MongoId($pre_user['id'])]);
            
            $args = [];
            
//            $send = NotifyHelper::send($user, $item['name'], $args);
//            return $send;
            
            NotifyHelper::sendAll($item['_id'], 'news', 'ได้เพิ่มข่าว', $item['detail']);
            
            // notify
//            Event::add('after_response', function() use($item, $user, $args){
//                NotifyHelper::send($insert['_id'], 'news', 'ได้เพิ่มข่าว', $insert['detail']);
//                dump($item);
//                $noti = NotifyHelper::send($user, $item['name'], $args);
//                        dump($noti);
//            });
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @GET
     */
    public function gets(){
        try {
            /*
            $items = FeedService::getInstance()->gets($this->reqInfo->params(), $this->getCtx());
            foreach ($items['data'] as $key => $item) {
                MongoHelper::standardIdEntity($item);
                $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
                $item['updated_at'] = MongoHelper::timeToInt($item['updated_at']);
                $item['node'] = NodeHelper::news($item['id']);
                $items['data'][$key] = $item;
            }
            return $items;
            */

            $a['a'] = "GET PAGE";
            return $a;
        }
        catch (ServiceException $e){
            return $e->getResponse();
        }
    }

    /**
     * @POST
     */
    public function add(){
        try {
            $item = FeedService::getInstance()->add($this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($item);
            $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
            $item['updated_at'] = MongoHelper::timeToInt($item['updated_at']);
            $item['node'] = NodeHelper::news($item['id']);
            return $item;
        }
        catch (ServiceException $e){
            return $e->getResponse();
        }
    }

    /**
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        try {
            /*$item = FeedService::getInstance()->get($this->reqInfo->urlParam('id'), $this->getCtx());
            FeedService::getInstance()->incView($this->reqInfo->urlParam('id'), $this->getCtx());

            MongoHelper::standardIdEntity($item);
            $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
            $item['updated_at'] = MongoHelper::timeToInt($item['updated_at']);
            $item['node'] = NodeHelper::news($item['id']);
            return $item;*/
            return array('a' => 'GET WITH [:id]');
        }
        catch (ServiceException $e){
            return $e->getResponse();
        }
    }

    /**
     * @GET
     * @uri /subdemo
     */
    public function mydemo(){
        return ['a' => 1234 ];
    }

    /**
     * @PUT
     * @uri /[h:id]
     */
    public function edit(){
        try {
            $item = FeedService::getInstance()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($item);
            $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
            $item['updated_at'] = MongoHelper::timeToInt($item['updated_at']);
            $item['node'] = NodeHelper::news($item['id']);
            return $item;
        }
        catch (ServiceException $e){
            return $e->getResponse();
        }
    }

    /**
     * @DELETE
     * @uri /[h:id]
     */
    public function delete(){
        try {
            $response = FeedService::getInstance()->delete($this->reqInfo->urlParam('id'), $this->getCtx());
            return $response;
        }
        catch (ServiceException $e){
            return $e->getResponse();
        }
    }

    /**
     * @POST
     * @uri /sort
     */
    public function sort(){
        try {
            $res = FeedService::getInstance()->sort($this->reqInfo->params(), $this->getCtx());
            return $res;
        }
        catch (ServiceException $e){
            return $e->getResponse();
        }
    }
}