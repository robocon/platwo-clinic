<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\Service;

use Main\Context\Context;
use Main\DB;
use Main\Exception\Service\ServiceException;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

/**
 * Description of TagService
 *
 * @author robocon
 */
class TagService extends BaseService {
    
    public function getCollection(){
        $db = DB::getDB();
        return $db->event_tag;
    }
    
    public function getTagCollection(){
        $db = DB::getDB();
        return $db->tag;
    }
    
    public function check($event_id, Context $ctx) {
        $event = $this->getCollection()->find(['event_id' => $event_id]);
        return $event->count(true);
    }
    
    public function add($event_id, $params, Context $ctx) {
        
        $params['event_id'] = $event_id;
        
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'tags']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // default lang is en
        $lang = empty($params['lang']) ? 'en' : $params['lang'];
        
        $res = [];
        foreach($params['tags'] as $tag){
            $insert = ['tag_id' => $tag, 'event_id' => $event_id];
            $this->getCollection()->insert($insert);
            
            $tag_id = MongoHelper::mongoId($tag);
            $tag_detail = $this->getTagCollection()->findOne(['_id' => $tag_id], [$lang]);
            
            $res[] = ['id' => $tag, 'name' => $tag_detail[$lang]];
        }
        
        return $res;
    }
    
    public function edit($event_id, $params, Context $ctx){
        
        $params['event_id'] = $event_id;
        
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'tags']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Clear old tags with remove all tags from event_id
        $this->removeFromEventId($event_id);
        
        // Add a new tag
        $insert = $this->add($event_id, $params, $ctx);
        return $insert;
    }
    
    /**
     * Remove all tags from event_id
     * 
     * @param type $event_id
     */
    private function removeFromEventId($event_id){
        $this->getCollection()->remove(["event_id" => $event_id]);
    }
}
