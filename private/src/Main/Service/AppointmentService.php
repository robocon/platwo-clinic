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
            return true;
        } catch (\MongoException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage(), $e->getCode()));
        }
    }
    
    public function get($user_id, Context $ctx) {
        
        $db = DB::getDB();
        $items = $db->appointment->find(['user_id' => $user_id],['detail','date_time','status']);
        
        $item_lists = [];
        foreach ($items as $item) {
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);

            $item['date_time'] = MongoHelper::dateToYmd($item['date_time']);
            $item_lists[] = $item;
        }
        
        return $item_lists;
    }
    
    public function change_status($appoint_id, $params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['status']);
        $v->rule('in', 'status', ['new','pending','confirmed','cancelled','missed']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $db = DB::getDB();
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
}