<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\Service;

use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\DataModel\Image,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\URL,
    Valitron\Validator;

/**
 * Description of SniffService
 *
 * @author robocon
 */
class SniffService extends BaseService {
    
    public function getCollection(){
        $db = DB::getDB();
        return $db->tag;
    }
    
    public function getSnifferCollection(){
        $db = DB::getDB();
        return $db->sniffer;
    }
    
    public function getUsersCollection(){
         $db = DB::getDB();
        return $db->users;
    }
    
    public function getEventCollection(){
         $db = DB::getDB();
        return $db->event;
    }
    
    public function getLocationCollection(){
         $db = DB::getDB();
        return $db->location;
    }
    
    public function getLogCollection(){
         $db = DB::getDB();
        return $db->logs;
    }
    
    public function getEventTagCollection(){
         $db = DB::getDB();
        return $db->event_tag;
    }
    
    public function gets($lang, $options = array(), Context $ctx) {
        
        // default lang is en
        $lang = empty($lang) ? 'en' : $lang;
        
        $items = $this->getCollection()->find([], [$lang]);
        $length = $items->count(true);
        
        $data = [];
        foreach($items as $item){
            $data[] = [
                'id' => $item['_id']->{'$id'}, 
                'name' => $item[$lang]
            ];
        }
        
        $res = [
            'length' => $length,
            'data' => $data,
        ];
        return $res;
    }
    
    
    public function follow($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'user_id']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Filter to check user and event exist?
        $search = $this->filter_follow($params);
        
        if ($search === null) {
            $insert = ArrayHelper::filterKey(['event_id', 'user_id'], $params);
            $this->getSnifferCollection()->insert($insert);
            
            return $insert;
        }else{
            return ResponseHelper::error("User already exist in this event");
        }
        
    }
    
    public function unfollow($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Filter to check user and event exist?
        $search = $this->filter_follow($params);
        
        if ($search !== null) {
            $delete = ArrayHelper::filterKey(['event_id', 'user_id'], $params);
            $this->getSnifferCollection()->remove($delete);
        
            return $delete;
        }else{
            return ResponseHelper::error("Can not find this user and event :(");
        }
        
    }
    
    public function filter_follow($params){
        // Filter to check user and event exist?
        $search = $this->getSnifferCollection()->findOne([
            'event_id' => $params['event_id'],
            'user_id' => $params['user_id'],
        ]);
        
        return $search;
    }
    
    public function follower($event_id, Context $ctx) {
        
        $item_lists = [];
        $items = $this->getSnifferCollection()->find(['event_id' => $event_id]);
        foreach ($items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $users = $this->getUsersCollection()->find(['_id' => new \MongoId($item['user_id'])],['display_name','picture']);
            foreach($users as $user){
                
                $user['id'] = $user['_id']->{'$id'};
                unset($user['_id']);
                unset($item['user_id']);

                $user['picture'] = Image::load($user['picture'])->toArrayResponse();

                $item['user'] = $user;
                $item_lists[] = $item;
            }
        }
        
        $res['data'] = $item_lists;
        $res['length'] = count($item_lists);
        return $res;
    }
    
    public function location($params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['category', 'location']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);
        
        $category = null;
        if (is_array($params['category'])) {
            $category = $params['category'];
        }
        
//        $lat = (float)$params['location']['0'];
//        $lng = (float)$params['location']['1'];
//        $radius = (int)$params['radius'];
//        $search = [
//            'position' => [
//                '$geoWithin' => [
//                    '$center' => [
//                        [$lat,$lng],
//                        $radius
//                    ]
//                ]
//            ],
//        ];
        
//        $search = [
//            'position' => [
//                '$near' => [$lat,$lng]
//            ]
//        ];
        
        
        // Box 
        $bottom_left = $params['location']['0'];
        $top_right = $params['location']['1'];
        $search = [
            'position' => [
                '$geoWithin' => [
                    '$box' => [
                        [(float)$bottom_left['0'],(float)$bottom_left['1']],
                        [(float)$top_right['0'],(float)$top_right['1']]
                    ]
                ]
            ],
        ];
        
        $locations = $this->getLocationCollection()->find($search,['event_id']);
        $event_lists = [];
        foreach($locations as $item){
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $event = $this->getEventCollection()->findOne([
                '_id' => new \MongoId($item['event_id']),
                'approve' => 1,
                'build' => 1,
                '$or' => [
                    ['date_start' => ['$gte' => $current_time]],
                    [
                        '$and' => [
                            ['date_start' => ['$lte' => $current_time]],
                            ['date_end' => ['$gte' => $current_time]]
                        ]
                    ]
                ]
            ],['name','date_start']);
            
            if($event !== null){
                
                $event['id'] = $event['_id']->{'$id'};
                unset($event['_id']);
                
                if ($category !== null) {
                    $tags = $this->getEventTagCollection()->find(['event_id' => $event['id']]);
                    
                    $search_tag = false;
                    foreach($tags as $tag){
                        $search_tag = in_array($tag['tag_id'], $category);
                    }
                    
                    // If not match any category continue to next event
                    if($search_tag === false){
                        continue;
                    }
                }
                
                $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);

                $event['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $item['event_id']])->count();
                $event['view'] = $this->getLogCollection()->find([
                    'type' => 'event',
                    'status' => 'view',
                    'reference_id' => $event['id'],
                ])->count();
                
                $event_lists[] = $event;
                
            }
            
        }
        
        // Sort by view count
        usort($event_lists, function($a, $b){
            return $a['view'] - $b['view'];
        });
        
        // Reverse them and limit top 10
        $final_lists = array_slice(array_reverse($event_lists), 0, 10);
        
        return $final_lists;
    }
}
