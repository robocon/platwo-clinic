<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/16/14
 * Time: 3:58 PM
 */

namespace Main\Service;


use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper;

class ContactCommentService extends BaseService {
    public function getCollection(){
        return DB::getDB()->contacts_comments;
    }

    public function gets($options = array(), Context $ctx){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($default['page']-1)*$default['limit'];
        //$select = array("name", "detail", "feature", "price", "pictures");
        $condition = array();
        $cursor = $this->getCollection()
            ->find($condition)
            ->sort(array('_id'=> 1))
            ->limit($default['limit'])
            ->skip($skip);

        $total = $this->getCollection()->count($condition);
        $length = $cursor->count();

        $data = array();
        foreach($cursor as $item){
            $data[] = $item;
        }

        return array(
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> array(
                'page'=> $options['page'],
                'limit'=> $options['limit']
            )
        );
    }

    public function add($params, Context $ctx){
        $now = new \MongoDate();
        $user = UserHelper::getUserDetail();
//        $user = $ctx->getUser();
//        if(is_null($user)){
//            throw new ServiceException(ResponseHelper::requireAuthorize());
//        }
        $entity = array(
            "message"=> $params['message'],
            "created_at"=> $now,
            "user"=> [
                "id"=> $user['id'],
                "display_name"=> $user['display_name']
            ]
        );

        $this->getCollection()->insert($entity);

        return $entity;
    }

    public function getCommentById ($id, Context $ctx) {
        if(!($id instanceof \MongoId)){
            $id = new \MongoId($id);
        }
        $item = $this->getCollection()->findOne(array("_id"=> $id));
        return $item;
    }

    public function deleteCommentById ($id, Context $ctx) {
        $this->getCollection()->remove(['_id'=> MongoHelper::mongoId($id)]);
        return ['success'=> true];
    }

}