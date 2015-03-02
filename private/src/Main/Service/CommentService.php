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
 * Description of CommentService
 *
 * @author robocon
 */
class CommentService extends BaseService {
    
    public function getCollection(){
        $db = DB::getDB();
        return $db->comment;
    }
    
    public function getUsersCollection(){
         $db = DB::getDB();
        return $db->users;
    }
    
    public function save($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['detail', 'event_id', 'user_id']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $params['time_stamp'] = new \MongoTimestamp();
        
        $insert = ArrayHelper::filterKey(['detail', 'event_id', 'user_id', 'time_stamp'], $params);
        $this->getCollection()->insert($insert);
        
        // Get user detail
        $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($insert['user_id'])));
        $insert['user'] = [
            'display_name' => $user['display_name'],
            'picture' => Image::load($user['picture'])->toArrayResponse()
        ];
        
        return $insert;
    }
    
    public function gets($event_id, $options = [], Context $ctx) {
        
        $default = ["page"=> 1, "limit"=> 10];
        $options = array_merge($default, $options);
        $skip = ($options['page']-1) * $options['limit'];
        
        $items = $this->getCollection()
                ->find([
                    'event_id' => $event_id
                    ])
                ->sort(['time_stamp' => -1])
                ->limit($options['limit'])
                ->skip($skip);
        $length = $items->count(true);
        $total = $this->getCollection()->count(['event_id' => $event_id]);
        
        $prev_total = $total - ($options['page'] * $options['limit']);
        $prev_count = $prev_total < 0 ? 0 : $prev_total ;
        
        $comment_lists = [];
        foreach ($items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $item['time_stamp'] = MongoHelper::dateToYmd($item['time_stamp']);
            
            $user = $this->getUsersCollection()->findOne(['_id' => new \MongoId($item['user_id'])],['display_name','picture']);
            $user['id'] = $item['user_id'];
            $user['picture'] = Image::load($user['picture'])->toArrayResponse();
            
            unset($user['_id']);
            unset($item['user_id']);
            unset($item['event_id']);
            
            $item['user'] = $user;
            $comment_lists[] = $item;
        }
        
        $res['data'] = array_reverse($comment_lists);
        $res['length'] = $length;
        $res['total'] = $total;
        $res['prev_count'] = $prev_count;
        
        if ($length > 0 && $length <= $total ) {
            
            if($prev_count > 0){
                $res['paging']['next'] = URL::absolute('/comment/'.$event_id.'?'.  http_build_query(['page' => (int)$options['page']+1, 'limit' => (int)$options['limit']]));
            }
                
            if ($options['page'] > 1) {
                $res['paging']['prev'] = URL::absolute('/comment/'.$event_id.'?'.  http_build_query(['page' => (int)$options['page']-1, 'limit' => (int)$options['limit']]));
            }
        }
        
        return $res;
    }
}
