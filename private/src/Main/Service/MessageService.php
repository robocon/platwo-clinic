<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 25/11/2557
 * Time: 17:10 น.
 */

namespace Main\Service;


use Main\Context\Context;
use Main\DB;
use Main\Exception\Service\ServiceException;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\NotifyHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\UpdatedTimeHelper;
use Main\Helper\URL;
use Valitron\Validator;

class MessageService extends BaseService {
    public function getUserCollection(){
        return DB::getDB()->users;
    }
    public function getMessageCollection(){
        return DB::getDB()->messages;
    }

    public function add($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['to', 'message']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $insert = ArrayHelper::filterKey(['message'], $params);
        MongoHelper::setCreatedAt($insert);

        $this->getMessageCollection()->insert($insert);

        $condition = [];
        if($params['to'] == "all"){
            $condition = [];
        }
        else if($params['to'] == "facebook"){
            $condition = ["fb_id"=> ['$ne'=> ""]];
        }
        else if($params['to'] == "register"){
            $condition = ["fb_id"=> ""];
        }
        else if($params['to'] == "users"){
            $v = new Validator($params);
            $v->rule('required', ['users_id']);

            if(!$v->validate()){
                throw new ServiceException(ResponseHelper::validateError($v->errors()));
            }

            $users_id = [];
            foreach($params['users_id'] as $key=> $value){
                $users_id[] = MongoHelper::mongoId($value);
            }
            $condition = ["_id"=> ['$in'=> $users_id]];
        }

        $users = $this->getUserCollection()->find($condition);
        foreach($users as $user){
            $entity = NotifyHelper::create($insert['_id'], "message", "ข้อความจากระบบ", NotifyHelper::cutMessage($insert['message']), $user['_id']);
            NotifyHelper::incBadge($user['_id']);
            $user['display_notification_number']++;

            $args = [
                'id'=> MongoHelper::standardId($entity['_id']),
                'object_id'=> MongoHelper::standardId($insert['_id']),
                'type'=> "message"
            ];

            if(!isset($user['setting']))
                continue;

            if(!$user['setting']['notify_message'])
                continue;

            NotifyHelper::send($user, $entity['preview_content'], $args);
        }

        return $insert;
    }

    public function get($id, Context $ctx){
        $item = $this->getMessageCollection()->findOne(['_id'=> MongoHelper::mongoId($id)]);
        if(is_null($item)){
            throw new ServiceException(ResponseHelper::notFound());
        }
        return $item;
    }

    public function gets($params, Context $ctx){
        $default = array(
            "page"=> 1,
            "limit"=> 15,
        );
        $options = array_merge($default, $params);

        $skip = ($options['page']-1)*$options['limit'];

        $condition = [];
        $cursor = $this->getMessageCollection()
            ->find($condition)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['created_at'=> -1]);

        $data = [];

        foreach($cursor as $item){
            $data[] = $item;
        }

        $total = $this->getMessageCollection()->count($condition);
        $length = $cursor->count(true);

        $res = [
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength)==$pagingLength? floor($pagingLength): floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
            $res['paging']['next'] = URL::absolute('/message'.'?'.$nextQueryString);
        }

        // add last_update to response
        $lastUpdate = UpdatedTimeHelper::get('message');
        $res['last_updated'] = MongoHelper::timeToInt($lastUpdate['time']);

        return $res;
    }
    
    public function send_specify($params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['type', 'id']);
        $v->rule('in','type', ['appointment','history','news','coupon','service']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $db = DB::getDB();
        $user = $ctx->getUser();
        $id = new \MongoId($params['id']);
        if($user !== null){
            
            if ($params['type'] == 'appointment') {
                $item = $db->appointment->findOne(['_id' => $id]);
                
                $objectId = $item['_id'];
                $type = 'card';
                $header = 'ได้นัดหมาย';
                $message = $item['detail'];
                
                // Override above user
                $user = $ctx->loadUser(new \MongoId($params['traget_id']));
                $userId = $user['_id'];
                
                $send_type = 'single';
            
            }  elseif ($params['type'] == 'history') {
                $item = $db->appointment->findOne(['_id' => $id]);
                
                $objectId = $item['_id'];
                $type = 'card';
                $header = 'ได้เพิ่มประวัติ';
                $message = $item['detail'];
                
                // Override above user
                $user = $ctx->loadUser(new \MongoId($params['traget_id']));
                $userId = $user['_id'];
                
                $send_type = 'single';
                
            } elseif ($params['type'] == 'news') {
                $item = $db->feed->findOne(['_id' => $id]);
                
                $objectId = $item['_id'];
                $type = 'news';
                $header = 'ได้เพิ่มข่าว';
                $message = $item['detail'];
                
                $send_type = 'all';
                
            } elseif ($params['type'] == 'coupon') {
                $item = $db->coupon->findOne(['_id' => $id]);
                
                $objectId = $item['_id'];
                $type = 'promotion';
                $header = 'ได้เพิ่มโปรโมชั่น';
                $message = $item['detail'];
                
                $send_type = 'all';
            } elseif ($params['type'] == 'service') {
                $item = $db->services->findOne(['_id' => $id]);
                
                $objectId = $item['_id'];
                $type = 'service';
                $header = 'ได้เพิ่มบริการ';
                $message = $item['detail'];
                
                $send_type = 'all';
            }
            
            if($send_type == 'single'){
                
                $entity = NotifyHelper::create($objectId, $type, $header, $message, $userId);
                $entity['object']['id'] = MongoHelper::standardId($objectId);
                $entity['id'] = MongoHelper::standardId($entity['_id']);

                $args = [
                    'id'=> $entity['id'],
                    'object_id'=> $entity['object']['id'],
                    'type'=> $type
                ];
                
                $send = NotifyHelper::send($user, $message, $args);
            }else{
                $send = NotifyHelper::sendAll($objectId, $type, $header, $message);
            }
            
            if($send !== null OR !empty($send)){
                return ['success' => true];
            }else{
                return ResponseHelper::error('Can not send notification');
            }
            
        }else{
            return ResponseHelper::error('Invalid token');
        }
        
    }
}