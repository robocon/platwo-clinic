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
use Main\Helper\ResponseHelper;
use Valitron\Validator;

/**
 * Description of LocationService
 *
 * @author robocon
 */
class LocationService extends BaseService {

    public function getCollection(){
        $db = DB::getDB();
        return $db->location;
    }
    
    public function check($event_id, Context $ctx) {
        $item = $this->getCollection()->find(['event_id' => $event_id]);
        return $item->count(true);
    }
    
    public function add($event_id, $params, Context $ctx) {
        
        $params['event_id'] = $event_id;
        
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'location']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $name = empty($params['location_name']) ? '' : $params['location_name'] ;
        unset($params['location_name']);
        
        $position = $params['location'];
        if(!is_array($params['location'])){
            $position = explode(',', $params['location']);
        }
            
        $lat = (float)$position['0'];
        $lng = (float)$position['1'];
        $location = [$lat, $lng];
        
        $insert = [
            'name' => $name,
            'position' => $location,
            'event_id' => $event_id
        ];
        
        try {
            $this->getCollection()->insert($insert);
        } catch(\MongoCursorException $e) {
            echo "Can't save the same person twice!\n";
            echo $e->getMessage();
            echo $e->getCode();
        }
        unset($insert['event_id']);
        return $insert;
    }
    
    public function edit($event_id, $params, Context $ctx) {
        
        $params['event_id'] = $event_id;
        
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'location']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        if (empty($params['location_name'])) {
            $params['location_name'] = '';
        }
        
        $position = explode(',', $params['position']);
        $params['location'] = array_map('trim',$position);
        
        $set = ['position' => $params['location'], 'location_name' => $params['location_name']];
        
        // update
        $this->getCollection()->update(['event_id'=> $event_id], ['$set'=> $set]);
        
        $res = ['name' => $params['location_name'], 'position' => $params['location']];
        return $res;
    }
}
