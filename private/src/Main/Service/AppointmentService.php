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
    Main\Helper\ResponseHelper,
    Main\Helper\MongoHelper,
    Main\Helper\UserHelper,
    Main\Helper\NotifyHelper,
    Valitron\Validator;

/**
 * Description of AppointmentService
 *
 * @author robocon
 */
class AppointmentService extends BaseService {
    
    public function check_password($password, Context $ctx) {
        
        $v = new Validator(['password' => $password]);
        $v->rule('required', ['password']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
       
        $db = DB::getDB();
        $config = $db->config->findOne([],['clinic_password' => true]);
        if($config['clinic_password'] !== $password){
            return ResponseHelper::error('Invalid password');
        }
        
        return true;
    }
    
    public function add($user_id, $params, Context $ctx) {
        
        if(isset($params['access_token'])){
            unset($params['access_token']);
        }
        
        $params['user_id'] = $user_id;
        
        $v = new Validator($params);
        $v->rule('required', ['user_id','date_add','time_add','detail','status']);
        $v->rule('in', 'status', ['new','pending','confirmed','cancelled','missed']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $pre_timestamp = strtotime($params['date_add'].' '.$params['time_add'].':00');
        $params['date_time'] = new \MongoDate($pre_timestamp);
        unset($params['date_add']);
        unset($params['time_add']);
        
        $db = DB::getDB();
        try {
            $db->appointment->insert($params);
            
            $res = [
                'success' => true,
                'id' => $params['_id']->{'$id'},
            ];
            
            return $res;
        } catch (\MongoException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage(), $e->getCode()));
        }
    }
    
    public function add_history($user_id, $params, Context $ctx) {
        
        if(isset($params['access_token'])){
            unset($params['access_token']);
        }
        
        $params['user_id'] = $user_id;
        
        $v = new Validator($params);
        $v->rule('required', ['user_id','date_add','time_add','detail']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $pre_timestamp = strtotime($params['date_add'].' '.$params['time_add'].':00');
        $params['date_time'] = new \MongoDate($pre_timestamp);
        unset($params['date_add']);
        unset($params['time_add']);
        
        $db = DB::getDB();
        try {
            $db->appointment->insert($params);
            return true;
        } catch (\MongoException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage(), $e->getCode()));
        }
    }
    
    public function gets($user_id, Context $ctx) {
        
        $db = DB::getDB();
        $items = $db->appointment->find([
            'user_id' => $user_id, 
            'status' => ['$ne' => 'cancelled'] 
        ],['detail','date_time','status'])->sort(['date_time' => -1]);
        
        $item_lists = [];
        foreach ($items as $item) {
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);

            $item['date_time'] = MongoHelper::dateToYmd($item['date_time']);
            
            if(!isset($item['status'])){
                $item['status'] = '';
            }
        
            $item_lists[] = $item;
        }
        
        return $item_lists;
    }
    
    public function get($appoint_id, Context $ctx){
        
        $db = DB::getDB();
        $item = $db->appointment->findOne(['_id' => new \MongoId($appoint_id)],[
            'detail','name','phone','status','date_time'
        ]);
        
        $item['id'] = $item['_id']->{'$id'};
        unset($item['_id']);
        
        if(!isset($item['status'])){
            $item['status'] = '';
        }
        $item['date_time'] = MongoHelper::dateToYmd($item['date_time']);
        
        return $item;
    }
    
    public function change_status($appoint_id, $params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['status']);
        $v->rule('in', 'status', ['new','pending','confirmed','cancelled','missed']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $item = $this->get($appoint_id, $ctx);
        $db = DB::getDB();
        
        // If update status from pending to confirmed will send a notification to user
//        if($item['status'] == 'pending' && $params['status'] == 'confirmed'){
//            
//            $pre_user = UserHelper::getUserDetail();
//            $user = $db->users->findOne(['_id' => new \MongoId($pre_user['id'])]);
//            
//            $objectId = new \MongoId($item['id']);
//            $type = 'card';
//            $header = 'ได้คอนเฟิร์ม';
//            $message = $item['detail'];
//            $userId = $user['_id'];
//            
//            $entity = NotifyHelper::create($objectId, $type, $header, $message, $userId);
//            $entity['object']['id'] = MongoHelper::standardId($objectId);
//            $entity['id'] = MongoHelper::standardId($entity['_id']);
//            
//            $args = [
//                'id'=> $entity['id'],
//                'object_id'=> $entity['object']['id'],
//                'type'=> $type
//            ];
//            
//            $send = NotifyHelper::send($user, $message, $args);
//            
//        }
        
        $update = $db->appointment->update(['_id' => new \MongoId($appoint_id)],['$set' => $params]);
        
        if ($update['n'] > 0) {
            return true;
        }
        return false;
    }
    
    public function update_appointment($appoint_id, $params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['date_add','time_add','detail','status']);
        $v->rule('in', 'status', ['new','pending','confirmed','cancelled','missed']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $pre_timestamp = strtotime($params['date_add'].' '.$params['time_add'].':00');
        $params['date_time'] = new \MongoDate($pre_timestamp);
        unset($params['date_add']);
        unset($params['time_add']);
        
        $db = DB::getDB();
        $update = $db->appointment->update(['_id' => new \MongoId($appoint_id)],['$set' => $params]);
        if ($update['n'] > 0) {
            return true;
        }
        return false;
    }
    
    public function save_datetime($params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['date','time_start','time_end']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $db = DB::getDB();
        $find = $db->config->findOne();
        $update = $db->config->update(['_id' => $find['_id']],['$set' => $params]);
        if ($update['n'] > 0) {
            return true;
        }
        return false;
    }
    
    public function get_datetime(Context $ctx) {
        
        $db = DB::getDB();
        $find = $db->config->findOne([],['date','time_start','time_end','repeat']);
        unset($find['_id']);
        return $find;
    }
}
