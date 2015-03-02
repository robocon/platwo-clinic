<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 10:27 AM
 */

namespace Main\Service;

use Main\Context\Context,
    Main\DB,
    Main\DataModel\Image,
    Main\Exception\Service\ServiceException,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper,
    Main\Helper\URL,
    Valitron\Validator;

class EventService extends BaseService {

    /**
     * Collection event
     */
    public function getCollection(){
        $db = DB::getDB();
        return $db->event;
    }

    /**
     * Collection gallery
     */
    public function getGalleryCollection(){
         $db = DB::getDB();
        return $db->gallery;
    }

    /**
     * Collection sniffer
     */
    public function getSnifferCollection(){
         $db = DB::getDB();
        return $db->sniffer;
    }

    /**
     * Collection comment
     */
    public function getCommentCollection(){
         $db = DB::getDB();
        return $db->comment;
    }

    /**
     * Collection users
     */
    public function getUsersCollection(){
         $db = DB::getDB();
        return $db->users;
    }

    /**
     * Collection tag
     */
    public function getTagCollection(){
         $db = DB::getDB();
        return $db->tag;
    }

    /**
     * Collection event_tag
     */
    public function getEventTagCollection(){
         $db = DB::getDB();
        return $db->event_tag;
    }
    
    /**
     * Collection location
     */
    public function getLocationCollection(){
         $db = DB::getDB();
        return $db->location;
    }
    
    public function all(Context $ctx) {
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        
        $events = $this->getCollection()->find([
            'approve' => 1,
            'build' => 1,
            '$or' => [
                ['date_start' => ['$gte' => $current_day]],
                [
                    '$and' => [
                        ['date_start' => ['$lte' => $current_day]],
                        ['date_end' => ['$gte' => $current_day]]
                    ]
                ]
            ]
        ],['name', 'date_start', 'date_end'])->sort(['date_start' => 1]);
        
        $group_one = [];
        $group_two =[];
        
        foreach ($events as $item) {
            $item_start = $item['date_start']->{'sec'};
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $item['group_date'] = date('Y-m-d', $item['date_start']->{'sec'});
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
            
            $picture = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
            $item['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                
            $item['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $item['id']])->count();
            
            if ($item_start > $start_time && $item_start < $end_time) {
                $group_one[] = $item;
            }else{
                $group_two[] = $item;
            }
        }
        
        return array_merge($group_one, $group_two);
    }
    
    public function gets($options = [], Context $ctx) {

        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);
        $skip = ($options['page']-1) * $options['limit'];

        $items = $this->getCollection()
                ->find(['build' => 1, 'approve' => 1])
                ->limit($options['limit'])
                ->skip($skip);
        $length = $items->count(true);
        $total = $this->getCollection()->count(['build' => 1, 'approve' => 1]);

        $data = [];
        foreach ($items as $item) {

            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);

            // Get last Picture
            $picture = $this->getGalleryCollection()
                    ->find(['event_id' => $item['id']])
                    ->sort(['_id' => -1]) // Look like DESC in MySQL
                    ->limit(1);

            if ($picture->count(true)) {
                foreach($picture as $pic){
                    $item['thumb'] = Image::load($pic['picture'])->toArrayResponse();
                }
            }

            $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            $item['time_edit'] = MongoHelper::dateToYmd($item['time_edit']);
            $item['time_stamp'] = MongoHelper::dateToYmd($item['time_stamp']);
            
            unset($item['build']);
            unset($item['approve']);
            
            $data[] = $item;
        }

        $res = [
            'length' => $length,
            'total' => $total,
            'data' => $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];

        if ($length > 0 && $length <= $total ) {
            $res['paging']['next'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']+1, 'limit' => (int)$options['limit']]));

            if ($options['page'] > 1) {
                $res['paging']['prev'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']-1, 'limit' => (int)$options['limit']]));
            }
        }

        return $res;
    }

    public function get($id, Context $ctx) {

        $id = MongoHelper::mongoId($id);
        $item = $this->getCollection()->findOne(['_id' => $id]);

        $item['id'] = $item['_id']->{'$id'};
        unset($item['_id']);

        $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
        $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
        $item['time_edit'] = MongoHelper::dateToYmd($item['time_edit']);
        $item['time_stamp'] = MongoHelper::dateToYmd($item['time_stamp']);

        // Get location
        $location = $this->getLocationCollection()->findOne([
            'event_id' => $item['id']
        ],['name','position']);
        $location['id'] = $location['_id']->{'$id'};
        unset($location['_id']);
        
        if (!is_array($location['position'])) {
            $position = explode(',', $location['position']);
            $location['position'] = array_map('trim',$position);
        }
        $item['location'] = $location;
        
        // Get latest 5 pictures
        $gallery = $this->getGalleryCollection()
            ->find(['event_id' => $item['id']])
            ->sort(['_id' => -1])
            ->limit(5);
        $item['pictures'] = [];

        if ($gallery->count(true)) {
            $pictures = [];
            foreach ($gallery as $picture) {
                $pictures[] = Image::load($picture['picture'])->toArrayResponse();
            }
            $item['pictures'] = $pictures;
        }

        // Get latest 20 sniffer
        $sniffers = $this->getSnifferCollection()
            ->find(['event_id' => $item['id']])
            ->sort(['_id' => -1])
            ->limit(20);
        $item['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $item['id']])->count();
        $item['sniffer'] = [];
        if ($sniffers->count(true)) {
            $user_lists = [];
            foreach($sniffers as $sniffer){
                $sniffer['id'] = $sniffer['_id']->{'$id'};
                unset($sniffer['_id']);

                // Get user detail
                $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($sniffer['user_id'])));
                $user_lists[] = [
                    'id' => $user['_id']->{'$id'},
                    'picture' => Image::load($user['picture'])->toArrayResponse()
                ];
            }
            $item['sniffer'] = $user_lists;
        }

        // get latest 3 comment
        $comment_lists = $this->getCommentCollection()
            ->find(['event_id' => $item['id']])
            ->sort(['_id' => -1])
            ->limit(3);
        $item['total_comment'] = $this->getCommentCollection()->find(['event_id' => $item['id']])->count();
        $item['comments'] = [];
        if ($comment_lists->count(true)) {
            $comments = [];

            foreach($comment_lists as $comment){
                $comment['id'] = $comment['_id']->{'$id'};
                unset($comment['_id']);

                // Get user detail
                $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($comment['user_id'])));
                $comment['user'] = [
                    'display_name' => $user['display_name'],
                    'picture' => Image::load($user['picture'])->toArrayResponse()
                ];
                $comment['time_stamp'] = MongoHelper::timeToStr($comment['time_stamp']);

                $comments[] = $comment;
            }
            $item['comments'] = $comments;
        }
        
        

        return $item;
    }

    /**
     * Add from website
     *
     * @param type $params
     * @param Context $ctx
     * @return type
     * @throws ServiceException
     */
    public function add($params, Context $ctx){


        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'date_start', 'date_end', 'approve', 'thumb', 'credit', 'user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $data = [
            'name' => $params['name'],
            'detail' => $params['detail'],
            'date_start' => $params['date_start'],
            'date_end' => $params['date_end'],
            'approve' => $params['approve'],
            'thumb' => $params['thumb'],
            'credit' => $params['credit'],
            'user_id' => $params['user_id'],
            'time_stamp' => $params['time_stamp'],
            'time_edit' => $params['time_stamp']
        ];

        $insert = ArrayHelper::filterKey(['name', 'detail', 'date_start', 'date_end', 'approve', 'thumb', 'credit', 'user_id', 'time_stamp', 'time_edit'], $data);
        $this->getCollection()->insert($insert);

        return $insert;
    }

    public function updateThumb($id, $picture, Context $ctx){

        // Set this ID First
        $id = MongoHelper::mongoId($id);
        $set['thumb'] = $picture;

        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
    }

    /**
     * Add event from mobile
     *
     * @param array $params
     * @param Context $ctx
     * @return type
     * @throws ServiceException
     */
    public function mobile_add($params, Context $ctx) {

        $v = new Validator($params);
        $v->rule('required', ['user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        // Set build and approve to 0 if send from mobile
        $params['build'] = 0;
        $params['approve'] = 0;
        $params['time_stamp'] = new \MongoDate();
        $params['alarm'] = 0;
        $params['advertise'] = [
            'status' => 0
        ];
        
        $insert = ArrayHelper::filterKey(['user_id', 'build', 'approve', 'time_stamp', 'alarm', 'advertise'], $params);

        $this->getCollection()->insert($insert);
        return $insert;
    }

    public function edit($id, $params, Context $ctx) {

        // Set this ID First
        $id = MongoHelper::mongoId($id);

        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'date_start', 'date_end', 'credit', 'country', 'city']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $set['name'] = $params['name'];
        $set['detail'] = $params['detail'];
        $set['date_start'] = new \MongoDate(strtotime($params['date_start']));
        $set['date_end'] = new \MongoDate(strtotime($params['date_end']));
        $set['credit'] = $params['credit'];
        $set['time_edit'] = new \MongoDate(time());
        $set['build'] = 1;
        $set['location'] = [$params['country'], $params['city']];

        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
        unset($set['build']);
        $set['local'] = $set['location'];
        unset($set['location']);
        return $set;
    }

    public function alarm($params, Context $ctx) {

        $v = new Validator($params);
        $v->rules([
                'required' => [ ['event_id'], ['active'] ],
                'integer' => [ ['active'] ],
                'length' => [['active', 1]]
            ]);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        // Set this ID First
        $event_id = MongoHelper::mongoId($params['event_id']);

        $find_event = $this->getCollection()->findOne(['_id' => $event_id]);
        if ($find_event === null) {
            return ResponseHelper::error("Can not find this event :(");
        }

        $this->getCollection()->update(
            ['_id' => $event_id],
            ['$set'=> [
                'alarm' => $params['active']
                ]
            ]);

        return $params;
    }

    public function category_lists($category_lists, Context $ctx) {

        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);

        $new_category_lists = [];
        foreach($category_lists as $category){

            $event_tags = $this->getEventTagCollection()->find(['tag_id' => $category['id']]);
            
            // Count an event from event_tag
            $event_tags_count = $event_tags->count(true);
            
            if ($event_tags_count > 0) {
                
                $event_lists = [];
                $i = 0;
                foreach ($event_tags as $item) {

                    $event = $this->getCollection()->findOne([
                        'approve' => 1,
                        'build' => 1,
                        '_id' => new \MongoId($item['event_id']),
                        'date_start' => ['$gt' => $current_time]
                    ],['name', 'date_start', 'date_end']);
                    
                    if ($event !== null) {
                        $event['id'] = $event['_id']->{'$id'};
                        unset($event['_id']);
                        
                        $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                        $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
                        
                        $set_key = (string)strtotime($event['date_start']);
                        
                        $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                        $event['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                        
                        $event_lists[$set_key] = $event;
                        $i++;
                    }
                }
                
                if ($i > 0) {
                    ksort($event_lists, SORT_NUMERIC);
                    $get_keys = array_keys($event_lists);
                    $first_event = $get_keys['0'];
                    $real_event = $event_lists[$first_event];
                    
                    $category['thumb'] = $real_event['thumb'];
                    
                    $new_category_lists[] = $category;
                }
            }
        }
        
        return $new_category_lists;
    }
    
    public function today($category_lists, Context $ctx) {
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        
        $new_category_lists = [];
        $first_category_lists = [];
        
        foreach ($category_lists['data'] as $category) {
            
            $event_tags = $this->getEventTagCollection()->find(['tag_id' => $category['id']]);
            $event_tags_count = $event_tags->count(true);
            if ($event_tags_count > 1) {
                
                $event_lists = [];
                $i = 0;
                
                foreach ($event_tags as $item) {

                    $event = $this->getCollection()->findOne([
                        'approve' => 1,
                        'build' => 1,
                        '_id' => new \MongoId($item['event_id']),
                        '$or' => [
                            ['date_start' => ['$gte' => $current_day]],
                            [
                                '$and' => [
                                    ['date_start' => ['$lte' => $current_day]],
                                    ['date_end' => ['$gte' => $current_day]]
                                ]
                            ]
                        ]
                    ],['name', 'date_start', 'date_end']);
                    
                    if($event !== null){
                        
                        $event['id'] = $event['_id']->{'$id'};
                        unset($event['_id']);
                        
                        $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                        $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
                        
                        $set_key = (string)strtotime($event['date_start']);
                        
                        $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                        $event['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                        
                        $sniffer = $this->getSnifferCollection()->find(['event_id' => $event['id']]);
                        $event['total_sniffer'] = $sniffer->count(true);
                        
                        $event_lists[$set_key] = $event;
                        $i++;
                        
                    }
                }
                
                if ($i > 0) {
                    ksort($event_lists, SORT_NUMERIC);
                    $get_keys = array_keys($event_lists);
                    $first_event = (string)$get_keys['0'];
                    $real_event = $event_lists[$first_event];

                    $category['thumb'] = $real_event['thumb'];
                    $category['total_sniffer'] = $real_event['total_sniffer'];
                    $category['date_end'] = $real_event['date_end'];
                    $category['type'] = 'category';
                    $category['date_start'] = $first_event;
                    
                    if ($get_keys['0'] > $start_time && $get_keys['0'] < $end_time) {
                        $first_category_lists[] = $category;
                    }else{
                        $new_category_lists[] = $category;
                    }
                }
                
            } // End if
            elseif ($event_tags_count == 1) {
                foreach ($event_tags as $item) {
                    $event = $this->getCollection()->findOne([
                        'approve' => 1,
                        'build' => 1,
                        '_id' => new \MongoId($item['event_id']),
                        '$or' => [
                            ['date_start' => ['$gte' => $current_day]],
                            [
                                '$and' => [
                                    ['date_start' => ['$lte' => $current_day]],
                                    ['date_end' => ['$gte' => $current_day]]
                                ]
                            ]
                        ]
                    ],['name', 'date_start', 'date_end']);
                    if($event !== null){
                        
                        $event['id'] = $event['_id']->{'$id'};
                        unset($event['_id']);
                        
                        $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                        $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
                        
                        $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                        $category['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                        
                        $sniffer = $this->getSnifferCollection()->find(['event_id' => $event['id']]);
                        $category['total_sniffer'] = $sniffer->count(true);
                        
                        $category['id'] = $event['id'];
                        $category['type'] = 'item';
                        $category['name'] = $event['name'];
                        $event_time = strtotime($event['date_start']);
                        $category['date_start'] = (string)$event_time;
                        $category['date_end'] = $event['date_end'];
                        
                        if ($event_time > $start_time && $event_time < $end_time) {
                            $first_category_lists[] = $category;
                        }else{
                            $new_category_lists[] = $category;
                        }
                    }
                } // End foreach
            } // End if
        } // End foreach
        
        usort($first_category_lists, function($a, $b){
            return $a['date_start'] - $b['date_start'];
        });
        
        usort($new_category_lists, function($a, $b){
            return $a['date_start'] - $b['date_start'];
        });
        
        $final_category = array_merge($first_category_lists, array_reverse($new_category_lists));
        
        $i = 0;
        foreach ($final_category as $item) {
            $final_category[$i]['date_start'] = date('Y-m-d H:i:s', $item['date_start']);
            $i++;
        }
        return $final_category;
    }
    
    public function upcoming($options = [], Context $ctx) {
        
        $limit = 20;
        if (!empty($options['limit'])) {
            $limit = $options['limit'];
        }
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);
        
        $items = $this->getCollection()->find([
            'approve' => 1,
            'build' => 1,
            '$and' => [
                ['date_start' => ['$gte' => $current_time]],
                ['date_end' => ['$gte' => $current_time]]
            ]
        ],['name','detail','date_start'])->sort(['date_start' => 1])->limit($limit);
        
        $res = [];
        foreach ($items as $item) {
            
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);

            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
            $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();
            
            $sniffer = $this->getSnifferCollection()->find(['event_id' => $item['id']]);
            $item['total_sniffer'] = $sniffer->count(true);
            
            // For random an item
            $item['rand'] = rand(100000, 199999);
            $res[] = $item;
        }
        return $res;
    }
    
    public function category_set($category_id, Context $ctx) {
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $start_of_day = new \MongoDate($start_time);
        
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        $end_of_day = new \MongoDate($end_time);
        
        $item_lists = [];

        // Filter by date_start only
        $first_items = $this->getCollection()->find([
                'approve' => 1,
                'build' => 1,
                'date_start' => ['$gt' => $start_of_day, '$lt' => $end_of_day]
            ],['name','date_start'])
            ->sort(['date_start' => 1])
            ->limit(10);
        
        $check_duplicate_id = [];
        
        foreach ($first_items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $category = $this->getEventTagCollection()->findOne(['event_id' => $item['id']]);
            $item['category'] = $category['tag_id'];
            
            if ($category_id == $item['category']) {
                $check_duplicate_id[] = new \MongoId($item['id']);
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);

                $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
                $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();

                $item_lists[] = $item;
            }
        }
        
        $second_items = $this->getCollection()->find([
            'approve' => 1,
            'build' => 1,
            '$and' => [
                ['date_start' => ['$lte' => $current_day]],
                ['date_end' => ['$gte' => $current_day]]
            ],
            '_id' => ['$nin' => $check_duplicate_id]
        ], ['name','date_start'])
        ->sort(['date_start' => -1])
        ->limit(10);
        foreach ($second_items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $category = $this->getEventTagCollection()->findOne(['event_id' => $item['id']]);
            $item['category'] = $category['tag_id'];
            
            if ($category_id == $item['category']) {
                $check_duplicate_id[] = new \MongoId($item['id']);
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);

                $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
                $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();

                $item_lists[] = $item;
            }
        }
        
        return $item_lists;
    }
    
    public function category_upcoming($category_id, Context $ctx) {
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);
        
        $new_lists = [];
        
        $event_tags = $this->getEventTagCollection()->find(['tag_id' => $category_id]);
        foreach ($event_tags as $item) {
            
            $event = $this->getCollection()->findOne([
                'approve' => 1,
                'build' => 1,
                '_id' => new \MongoId($item['event_id']),
                'date_start' => ['$gt' => $current_time]
            ],['name', 'date_start', 'date_end']);
            
            if ($event !== null) {
                $event['id'] = $event['_id']->{'$id'};
                unset($event['_id']);

                $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);

                $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                $event['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                
                $sniffer = $this->getSnifferCollection()->find(['event_id' => $event['id']]);
                $event['total_sniffer'] = $sniffer->count(true);
            
                $new_lists[] = $event;
            }
        }
        
        return $new_lists;
    }
    
    public function search($word, Context $ctx) {
        
        if (empty($word)) {
            throw new ServiceException(ResponseHelper::notFound());
        }
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);
        
        $search = new \MongoRegex("/".str_replace(['"', "'", "\x22", "\x27"], '', $word)."/i");
        $items = $this->getCollection()->find([
            'approve' => 1,
            'build' => 1,
            'name' => $search,
            '$or' => [
                ['date_start' => ['$gte' => $current_time]],
                [
                    '$and' => [
                        ['date_start' => ['$lte' => $current_time]],
                        ['date_end' => ['$gte' => $current_time]]
                    ]
                ]
            ]
        ],['name'])->sort(['date_start' => -1]);
        
        $item_lists = [];
        if ($items->count() > 0) {
            foreach($items as $item){
                $item['id'] = $item['_id']->{'$id'};
                unset($item['_id']);
                
                $item_lists[] = $item;
            }
        }
        
        return $item_lists;
    }
    
    public function get_advertise(Context $ctx) {
        try {
            
            $location = UserHelper::$default_location;
            $items = $this->getCollection()->find([
                'advertise.enable' => 1,
                'advertise.cities' => $location['1']
                ],['name','date_start','date_end','advertise']);
            $item_lists = [];
            foreach($items as $item){
                $item['id'] = $item['_id']->{'$id'};
                unset($item['_id']);
                
                // Get last Picture
                $picture = $this->getGalleryCollection()
                        ->find(['event_id' => $item['id']])
                        ->sort(['_id' => -1]) // Look like DESC in MySQL
                        ->limit(1);

                if ($picture->count(true)) {
                    foreach($picture as $pic){
                        $item['thumb'] = Image::load($pic['picture'])->toArrayResponse();
                    }
                }
                
                $sniffer = $this->getSnifferCollection()->find(['event_id' => $item['id']]);
                $item['total_sniffer'] = $sniffer->count(true);
            
                $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
                $item['advertise']['time_start'] = MongoHelper::dateToYmd($item['advertise']['time_start']);
                
                $item_lists[] = $item;
                
            }
            
            return $item_lists;
            
        } catch (\MongoException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage(), $e->getCode()));
        }
    }
    
    public function edit_adverties($event_id, $params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['enable', 'cities']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $params['time_start'] = new \MongoDate();
        $params['enable'] = intval($params['enable']);
        $set = [
            'advertise' => $params
        ];
        try {
            $this->getCollection()->update(['_id' => new \MongoId($event_id)],['$set' => $set]);
            $params['id'] = $event_id;
            unset($params['time_start']);
            return $params;
        } catch (\MongoException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage(), $e->getCode()));
        }
    }
}
