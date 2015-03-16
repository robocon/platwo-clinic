<?php

/**
 * Created by PhpStorm.
 * User: p2
 * Date: 10/10/14
 * Time: 11:49 AM
 */

namespace Main\Service;

use Main\Context\Context,
    Main\DataModel\Image,
    Main\DB,
    Main\Event\Event,
    Main\Exception\Service\ServiceException,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\UserHelper,
    Main\Helper\NotifyHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\UpdatedTimeHelper,
    Main\Helper\URL,
    Valitron\Validator;

class CouponService extends BaseService {

    public function getCouponCodeCollection() {
        return DB::getDB()->coupon_codes;
    }

    public function getCollection() {
        return DB::getDB()->coupons;
    }

    public function addCoupon($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'thumb']);

        if (!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $insert = ArrayHelper::filterKey(['name', 'detail', 'code'], $params);
        $insert['thumb'] = Image::upload($params['thumb'])->toArray();

        // seq insert
        $agg = $this->getCollection()->aggregate([
            ['$group' => ['_id' => null, 'max' => ['$max' => '$seq']]]
        ]);
        $insert['seq'] = (int) @$agg['result'][0]['max'] + 1;

        $now = new \MongoDate(time());
        $insert['code'] = strtoupper(substr(uniqid(), -5));
        $insert['created_at'] = $now;
        $insert['updated_at'] = $now;
//        $insert['cus_only'] = (bool)$params['cus_only'];
//        $insert['type'] = 'coupon';
        $insert['used_count'] = 0;
        $insert['used_users'] = [];

        $this->getCollection()->insert($insert);

        // service update timestamp (last_update)
        UpdatedTimeHelper::update('coupon', time());

        // notify
        Event::add('after_response', function() use($insert) {
            NotifyHelper::sendAll($insert['_id'], 'coupon', 'ได้เพิ่มคูปอง', $insert['detail']);
        });

        return $insert;
    }

    public function get($id, Context $ctx) {
        $item = $this->getCollection()->findOne(['_id' => MongoHelper::mongoId($id)]);
        if (is_null($item)) {
            throw new ServiceException(ResponseHelper::notFound());
        }
        return $item;
    }

    public function editCoupon($id, $params, Context $ctx) {
        $set = ArrayHelper::filterKey(['name', 'detail', 'condition'], $params);
        $entity = $this->get($id, $ctx);
        if (isset($params['thumb'])) {
            $set['thumb'] = Image::upload($params['thumb'])->toArray();
        }
        $this->getCollection()->update(['_id' => MongoHelper::mongoId($id)], ['$set' => ArrayHelper::ArrayGetPath($set)]);

        // service update timestamp (last_update)
        UpdatedTimeHelper::update('coupon', time());

        return $this->get($id, $ctx);
    }

    public function gets($params, Context $ctx) {
        $default = array(
            "page" => 1,
            "limit" => 15,
        );
        $options = array_merge($default, $params);

        $skip = ($options['page'] - 1) * $options['limit'];

//        $isCus = false;
//        $user = $ctx->getUser();
//        if(isset($user['check_in'])){
//            $isCus = true;
//        }

        $condition = [];
//        if(!$isCus && @$params['consumer_key'] != 'admin'){
//            $condition = [
//                '$or'=> [
//                    ['cus_only'=> ['$exists'=> false]],
//                    ['cus_only'=> false]
//                ]
//            ];
//        }
        $cursor = $this->getCollection()
                ->find($condition)
                ->limit((int) $options['limit'])
                ->skip((int) $skip)
                ->sort(['seq' => -1]);

        $data = [];

        foreach ($cursor as $item) {
            $data[] = $item;
        }

        $total = $this->getCollection()->count($condition);
        $length = $cursor->count(true);

        $res = [
            'length' => $length,
            'total' => $total,
            'data' => $data,
            'paging' => [
                'page' => (int) $options['page'],
                'limit' => (int) $options['limit']
            ]
        ];

        $pagingLength = $total / (int) $options['limit'];
        $pagingLength = floor($pagingLength) == $pagingLength ? floor($pagingLength) : floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int) $options['page'];
        if (((int) $options['page'] * (int) $options['limit']) < $total) {
            $nextQueryString = http_build_query(['page' => (int) $options['page'] + 1, 'limit' => (int) $options['limit']]);
            $res['paging']['next'] = URL::absolute('/promotion' . '?' . $nextQueryString);
        }

        // add last_update to response
        $lastUpdate = UpdatedTimeHelper::get('coupon');
        $res['last_updated'] = MongoHelper::timeToInt($lastUpdate['time']);

        return $res;
    }

    public function delete($id, Context $ctx) {
        $condition = ['_id' => MongoHelper::mongoId($id)];
        return $this->getCollection()->remove($condition);
    }

    public function requestCoupon($id, Context $ctx) {
//        $user = $ctx->getUser();
        
        $user = UserHelper::getUserDetail();
//        if (is_null($user)) {
//            throw new ServiceException(ResponseHelper::requireAuthorize());
//        }
        
        $db = DB::getDB();
        $now = new \MongoDate();
        $user_used = $db->coupons->findOne([
            '_id' => MongoHelper::mongoId($id),
            'used_users.user.id' => new \MongoId($user['id']),
        ],['used_users', 'code']);
        
        if ($user_used !== null) {
            $res = [];
            foreach($user_used['used_users'] as $item){
                
//                dump($item);
                if($item['user']['_id']->{'$id'} == $user['id']){
                    $res['user'] = [
                        'id' => $user['id'], 
                        'display_name' => $item['user']['display_name']
                    ];
                    $res['expire'] = MongoHelper::dateToYmd($item['expire']);
                    $res['created_at'] = MongoHelper::dateToYmd($item['created_at']);
                }
                
            }
            $res['code'] = $user_used['code'];
            
//exit;
            if(strtotime($res['expire']) > $now->{'sec'}){
                return $res;
            }else{
                return ResponseHelper::error('Used code');
            }
        }
        
        $expire = new \DateTime(date('Y-m-d H:i:s', time() + 3600));
        $used_user = [
            'user' => ArrayHelper::filterKey(['id', 'display_name'], $user),
            'expire' => new \MongoDate($expire->getTimestamp()),
            'created_at' => $now,
        ];
        
        $this->getCollection()->update(
            ['_id' => MongoHelper::mongoId($id)], ['$push' => ['used_users' => $used_user], '$inc' => ['used_count' => 1]]
        );
        
        $coupon = $this->get($id, $ctx);
        $res = [
            'user' => [
                'id' => $used_user['user']['id'],
                'display_name' => $used_user['user']['display_name']
            ],
            'expire' => MongoHelper::dateToYmd($used_user['expire']),
            'created_at' => MongoHelper::dateToYmd($used_user['created_at']),
            'code' => $coupon['code'],
        ];
        return $res;
    }

    public function usedUsers($id, $params, Context $ctx) {
        $default = array(
            "page" => 1,
            "limit" => 15,
        );
        $options = array_merge($default, $params);

        $item = $this->getCollection()->findOne(['_id' => MongoHelper::mongoId($id)]);
        if (is_null($item)) {
            throw new ServiceException(ResponseHelper::notFound());
        }

        $total = count($item['used_users']);
        $length = count($item['used_users']);

        $res = [
            'length' => $length,
            'total' => $total,
            'data' => $item['used_users'],
            'paging' => [
                'page' => 1,
                'limit' => 1
            ]
        ];

        $pagingLength = $total / (int) $options['limit'];
        $pagingLength = floor($pagingLength) == $pagingLength ? floor($pagingLength) : floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int) $options['page'];
        if (((int) $options['page'] * (int) $options['limit']) < $total) {
            $nextQueryString = http_build_query(['page' => (int) $options['page'] + 1, 'limit' => (int) $options['limit']]);
            $res['paging']['next'] = URL::absolute('/coupon/' . MongoHelper::mongoId($id) . '/used_users?' . $nextQueryString);
        }

        return $res;
    }

    public function sort($param, Context $ctx = null) {
        foreach ($param['id'] as $key => $id) {
            $mongoId = MongoHelper::mongoId($id);
            $seq = $key + $param['offset'];
            $this->getCollection()->update(array('_id' => $mongoId), array('$set' => array('seq' => $seq)));
        }

        // feed update timestamp (last_update)
        UpdatedTimeHelper::update('feed', time());

        return array('success' => true);
    }

    public function incView($id) {
        $this->getCollection()->update(['_id' => MongoHelper::mongoId($id)], ['$inc' => ['view_count' => 1]]);
    }

}
