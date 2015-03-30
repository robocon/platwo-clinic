<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/23/14
 * Time: 10:40 AM
 */

namespace Main\Service;


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

class FeedService extends BaseService {

    public function getCollection(){
        $db = DB::getDB();
        return $db->feed;
    }

    public function add($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail','thumb']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        if($ctx->getUser() === null){
            return ResponseHelper::error('Invalid token');
        }
        
        $insert = ArrayHelper::filterKey(['name', 'detail','thumb'], $params);

        $insert['thumb'] = Image::upload($params['thumb'])->toArray();

        // seq insert
        $agg = $this->getCollection()->aggregate([
            ['$group'=> ['_id'=> null, 'max'=> ['$max'=> '$seq']]]
        ]);
        $insert['seq'] = (int)@$agg['result'][0]['max'] + 1;
        
        $now = new \MongoDate(time());
        $insert['created_at'] = $now;
        $insert['updated_at'] = $now;

        $this->getCollection()->insert($insert);
        UpdatedTimeHelper::update('feed', time());

        // notify
//        Event::add('after_response', function() use($insert){
            NotifyHelper::sendAll($insert['_id'], 'news', 'ได้เพิ่มข่าว', $insert['detail']);
//        });

        return $insert;
    }

    public function edit($id, $params, Context $ctx){

        $id = MongoHelper::mongoId($id);

        $set = ArrayHelper::filterKey(['name', 'detail','thumb'], $params);

        if(isset($set['thumb'])){
            $set['thumb'] = Image::upload($set['thumb'])->toArray();
        }
        MongoHelper::setUpdatedAt($set);

        // update
        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);

        // feed update timestamp (last_update)
        UpdatedTimeHelper::update('feed', time());

        return $this->get($id, $ctx);
    }

    public function get($id, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $item = $this->getCollection()->findOne(array("_id"=> $id));
        if(is_null($item)){
            return ResponseHelper::notFound("Feed not found");
        }

        $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();

        MongoHelper::standardIdEntity($item);

//        $item['node'] = $this->makeNode($item);

        return $item;
    }

    public function gets($options = array(), Context $ctx){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];
        //$select = array("name", "detail", "feature", "price", "pictures");
        $condition = array();

        $cursor = $this->getCollection()
            ->find($condition)
            ->limit($options['limit'])
            ->skip($skip)
            ->sort(array('seq'=> -1));

        $total = $this->getCollection()->count($condition);
        $length = $cursor->count(true);

        $data = [];
        foreach($cursor as $item){
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            $item['created_key'] = $item['created_at']->{'sec'};
            $item['created_at'] = MongoHelper::dateToYmd($item['created_at']);
            $item['updated_at'] = MongoHelper::dateToYmd($item['updated_at']);
            
//            $item['node'] = NodeHelper::news($item['id']);
//            $items['data'][$key] = $item;
            
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
            $item['type'] = 'feed';
            unset($item['seq']);
            $item['node'] = $this->makeNode($item);

            $data[] = $item;
        }
        
        $db = DB::getDB();
        $promotions = $db->coupons->find([],['name','detail','thumb','created_at','updated_at'])->sort(['_id' => -1]);
        foreach ($promotions as $item) {
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            $item['created_key'] = $item['created_at']->{'sec'};
            $item['created_at'] = MongoHelper::dateToYmd($item['created_at']);
            $item['updated_at'] = MongoHelper::dateToYmd($item['updated_at']);
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
            $item['type'] = 'coupon';
            $item['node'] = $this->makeCouponNode($item);
            $data[] = $item;
        }
        
        usort($data, function($a, $b) {
            return $a['created_key'] - $b['created_key'];
        });
        
        $data = array_reverse($data);
        
        $res = [
//            'length'=> $length,
//            'total'=> $total,
            'length' => count($data),
            'data'=> $data,
//            'paging'=> [
//                'page'=> (int)$options['page'],
//                'limit'=> (int)$options['limit']
//            ]
        ];

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength)==$pagingLength? floor($pagingLength): floor($pagingLength) + 1;
//        $res['paging']['length'] = $pagingLength;
//        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
//            $res['paging']['next'] = URL::absolute('/feed'.'?'.$nextQueryString);
        }

        $lastUpdate = UpdatedTimeHelper::get('feed');
//        $res['last_updated'] = MongoHelper::timeToInt($lastUpdate['time']);
        return $res;
    }

    public function sort($param, Context $ctx = null){
        foreach($param['id'] as $key=> $id){
            $mongoId = MongoHelper::mongoId($id);
            $seq = $key+$param['offset'];
            $this->getCollection()->update(array('_id'=> $mongoId), array('$set'=> array('seq'=> $seq)));
        }

        // feed update timestamp (last_update)
        UpdatedTimeHelper::update('feed', time());

        return array('success'=> true);
    }

    public function delete($id, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $this->getCollection()->remove(array("_id"=> $id));

        // feed update timestamp (last_update)
        UpdatedTimeHelper::update('feed', time());

        return array("success"=> true);
    }

    public function makeNode($item){
        return array(
            "share"=> URL::share('/news.php?id='.$item['id'])
        );
    }
    
    public function makeCouponNode($item){
        return array(
            "share"=> URL::share('/coupon.php?id='.$item['id'])
        );
    }

    public function incView($id){
        $this->getCollection()->update(['_id'=> MongoHelper::mongoId($id)], ['$inc'=> ['view_count'=> 1]]);
    }
    
    public function get_overview(Context $ctx) {
        $db = DB::getDB();
        $item = $db->feed_overview->findOne();
        
        $picture_lists = [];
        foreach($item['pictures'] as $pic){
            $load = Image::load($pic);
            $picture_lists[] = $load->toArrayResponse();
        }
        $item['pictures'] = $picture_lists;
        
        $item['length'] = count($item['pictures']);
        unset($item['_id']);
        return $item;
    }
    
    public function overview($params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['details']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $db = DB::getDB();
        $item = $db->feed_overview->findOne();
        $db->feed_overview->update(['_id' => $item['_id']],['$set' => $params]);
        
        return $params;
    }
    
    public function overview_picture($params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['picture']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $picture = Image::upload($params['picture'])->toArray();
        $db = DB::getDB();
        $item = $db->feed_overview->findOne();
        $db->feed_overview->update(['_id' => $item['_id']],['$addToSet' => ['pictures' => $picture]]);
        
        $res = Image::load($picture)->toArrayResponse();
        return $res;
    }
}