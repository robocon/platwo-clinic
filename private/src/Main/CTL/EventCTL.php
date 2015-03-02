<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 10:05 AM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Valitron\Validator,
    Main\Service\EventService,
    Main\Service\GalleryService,
    Main\Service\TagService,
    Main\Service\LocationService,
    Main\Service\SniffService,
    Main\Service\LogService,
    Main\Helper\UserHelper;

/**
 * Class EventCTL
 *
 * @package Main\CTL
 * @Restful
 * @uri /event
 */
class EventCTL extends BaseCTL {
    
    /**
     * @api {get} /event/all GET /event/all
     * @apiDescription Get all event for mobile
     * @apiName GetAllEvent
     * @apiGroup Event
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
        {
            "date_end": "2015-02-04 10:57:27",
            "date_start": "2015-01-28 10:57:27",
            "name": "test add name 1422417447",
            "id": "54c85e2610f0ed1e048b4568",
            "group_date": "2015-01-28",
            "thumb": {
                "id": "54c8c13490cc13a8048b4619png",
                "width": 25,
                "height": 25,
                "url": "http://110.164.70.60/get/54c8c13490cc13a8048b4619png/"
            },
            "total_sniffer": 0
        },
        {...},
    ],
    "length": 5
}
     * 
     * @GET
     * @uri /all
     */
    public function all() {
        try {
            $items['data'] = EventService::getInstance()->all($this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /event GET /event
     * @apiDescription Get all event
     * @apiName GetEvents
     * @apiGroup Event
     * @apiSuccessExample {json} Success-Response:
    {
        "length": 1,
        "total": 1,
        "data": [
            {
                "alarm": 0,
                "approve": 1,
                "build": 1,
                "credit": "https:\/\/www.google.com",
                "date_end": "1970-01-01 07:00:00",
                "date_start": "1970-01-01 07:00:00",
                "detail": "Example detail",
                "name": "Example title",
                "time_edit": "1970-01-01 07:00:00",
                "time_stamp": "1970-01-01 07:00:00",
                "user_id": "1",
                "id": "54ba191510f0edb7048b456a",
                "thumb": {
                    "id": "54ba7c3590cc13ab048b4628png",
                    "width": 100,
                    "height": 100,
                    "url": "http:\/\/110.164.70.60\/get\/54ba7c3590cc13ab048b4628png\/"
                }
            },
            { ... }
        ],
        "paging": {
            "page": 1,
            "limit": 15,
            "next": "http:\/\/eventsniff.dev\/event?page=2"
        }
    }
     *
     * @GET
     */
    public function gets() {
        try {
            $items = EventService::getInstance()->gets($this->reqInfo->params(), $this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     * @api {get} /event/:event_id GET /event/:event_id
     * @apiDescription Get event from id [not complete yet]
     * @apiName GetEvent
     * @apiGroup Event
     * @apiParam {String} event_id Event id
     * @apiHeader {String} Access-Token (Optional) User Access Token
     * @apiSuccessExample {json} Success-Response:
    {
        "alarm": 0,
        "credit": "https:\/\/www.google.com",
        "date_end": "1970-01-01 07:00:00",
        "date_start": "1970-01-01 07:00:00",
        "detail": "Example detail",
        "name": "Example title",
        "time_edit": "1970-01-01 07:00:00",
        "time_stamp": "1970-01-01 07:00:00",
        "user_id": "1",
        "id": "54ba191510f0edb7048b456a",
        "location": {
            "name": "",
            "position": [
                "19.906496",
                "99.834254"
            ],
            "id": "54ba194110f0edb7048b456f"
        },
        "pictures": [
            {
                "id": "54ba7c3590cc13ab048b4628png",
                "width": 100,
                "height": 100,
                "url": "http:\/\/110.164.70.60\/get\/54ba7c3590cc13ab048b4628png\/"
            },
            {...}
        ],
        "total_sniffer": 2,
        "sniffer": [
        {
            "id": "54ba29c210f0edb8048b457a",
            "picture": {
                "id": "54ba8cd690cc1350158b4619jpg",
                "width": 180,
                "height": 180,
                "url": "http:\/\/110.164.70.60\/get\/54ba8cd690cc1350158b4619jpg\/"
            }
        }
        ],
        "total_comment": 2,
        "comments": [
            {
                "detail": "hello world",
                "user_id": "54ba29c210f0edb8048b457a",
                "event_id": "54ba191510f0edb7048b456a",
                "time_stamp": "2015-01-21 11:09:11",
                "id": "54bf266710f0ed12048b456a",
                "user": {
                    "display_name": "Kritsanasak Kuntaros",
                    "picture": {
                        "id": "54ba8cd690cc1350158b4619jpg",
                        "width": 180,
                        "height": 180,
                        "url": "http:\/\/110.164.70.60\/get\/54ba8cd690cc1350158b4619jpg\/"
                    }
                }
            },
            {...}
        ]
    }
     *
     * @GET
     * @uri /[h:event_id]
     */
    public function get() {
        try {
            $item = EventService::getInstance()->get($this->reqInfo->urlParam('event_id'), $this->getCtx());
            unset($item['approve']);
            unset($item['build']);
            
            // For none register user
            UserHelper::$user_id = 0;
                    
            $token = \Main\Http\RequestInfo::getToken();
            if($token !== false){
                if(UserHelper::check_token() === false){
                    throw new ServiceException(ResponseHelper::error('Invalid user'));
                }
            }
            
            $data_log = [
                'reference_id' => $item['id'],
                'type' => 'event',
                'status' => 'view',
            ];
            LogService::getInstance()->save($data_log, $this->getCtx());
            
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     * @POST
     */
    public function add(){
        try{

            $item = EventService::getInstance()->add($this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($item);

            // Store image into gallery
            $data = [
                'pictures' => [
                    $item['thumb']
                ],
                'user_id' => $item['user_id'],
                'event_id' => $item['id'],
            ];
            $pictures = GalleryService::getInstance()->add($data, $this->getCtx());

            EventService::getInstance()->updateThumb($item['id'], $pictures['0'], $this->getCtx());

            return $item;

        } catch(ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Save data from mobile that has send a picture and user_id
     *
     * @api {post} /event/gallery POST /event/gallery
     * @apiDescription Booking event with first picture
     * @apiName PostEventGallery
     * @apiGroup Event
     * @apiParam {String} picture Picture in base64_encode
     * @apiParam {String} user_id User id
     * @apiSuccessExample {json} Success-Response:
    {
        "event_id": "54b5e76510f0edc9068b4572",
        "user_id": "1",
        "id": "54bf399610f0ed11048b456b",
        "picture": {
            "id": "54bf9ca890cc13aa048b4617png",
            "width": 100,
            "height": 100,
            "url": "http:\/\/110.164.70.60\/get\/54bf9ca890cc13aa048b4617png\/"
        },
        "status": 200
    }
     *
     * @POST
     * @uri /gallery
     */
    public function booking_gallery(){

        try {

            // Add into event with only user_id for the first time
            $event = EventService::getInstance()->mobile_add($this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($event);

            // Store image into gallery
            $data = [
                'picture' => $this->reqInfo->param('picture'),
                'user_id' => $event['user_id'],
                'event_id' => $event['id'],
            ];

            $res = [
                'event_id' => $event['id'],
                'user_id' => $event['user_id'],
                'id' => $event['id'],
            ];

            $res['picture'] = GalleryService::getInstance()->add($data, $this->getCtx());
            $res['status'] = 200;
            return $res;

        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     * @api {post} /event/gallery/:event_id POST /event/gallery/:event_id
     * @apiDescription Save picture after first picture
     * @apiName PostAddEventGallery
     * @apiGroup Event
     * @apiParam {String} event_id Event id
     * @apiParam {String} picture Picture in base64_encode
     * @apiParam {String} user_id User id
     * @apiSuccessExample {json} Success-Response:
    {
        "picture": {
            "id": "54bf9d0b90cc13625e8b4577png",
            "width": 100,
            "height": 100,
            "url": "http:\/\/110.164.70.60\/get\/54bf9d0b90cc13625e8b4577png\/"
        },
        "user_id": "1",
        "event_id": "54b5e76510f0edc9068b4572",
        "id": "54b5e76510f0edc9068b4572",
        "status": 200
    }
     *
     * @POST
     * @uri /gallery/[h:id]
     */
    public function add_event_gallery() {
        try {

            $data = [
                'picture' => $this->reqInfo->param('picture'),
                'user_id' => $this->reqInfo->param('user_id'),
                'event_id' => $this->reqInfo->urlParam('id')
            ];

            $res = [];
            $res['picture'] = GalleryService::getInstance()->add($data, $this->getCtx());
            $res['user_id'] = $data['user_id'];
            $res['event_id'] = $data['event_id'];
            $res['id'] = $data['event_id'];
            $res['status'] = 200;
            return $res;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Update event data from eventdetail
     *
     * @api {put} /event/:event_id PUT /event/:event_id
     * @apiDescription Update event details
     * @apiName PutEvent
     * @apiGroup Event
     * @apiParam {String} event_id Event id
     * @apiParam {String} name Event name
     * @apiParam {String} detail Event description
     * @apiParam {String} date_start Event datetime E.g. 2014-01-15 11:00:00
     * @apiParam {String} date_end Event datetime
     * @apiParam {String} credit Something where are you get this event from
     * @apiParam {String} user_id User id
     * @apiParam {String} location Lat Lng from google map
     * @apiParam {String} location_name Location name
     * @apiParam {Array} tags Category id E.g. ['uj65tg', 'o8akuj', 'we8qw5']
     * @apiParam {String} lang Language like en, th. Default is en
     * @apiParam {String} country Country id
     * @apiParam {String} city City id
     * @apiSuccessExample {json} Success-Response:
{
    "name": "Example title",
    "detail": "Example detail",
    "date_start": "1970-01-01 07:00:00",
    "date_end": "1970-01-01 07:00:00",
    "credit": "https:\/\/www.google.com",
    "time_edit": "1970-01-01 07:00:00",
    "local":["54b8dfa810f0edcf048b4567","54b8e0e010f0edcf048b4575"],
    "id": "54ba1bc910f0edb8048b456c",
    "tags": [
        {
            "tag_id": "6f2da37e72bf9e100b40567c",
            "name": "Promotion"
        },
        {...},
    ],
    "location": {
        "name": "CNX",
        "position": [
            "19.906496",
            "99.834254"
        ],
    },
    "status": 200
}
     *
     * @PUT
     * @uri /[h:id]
     */
    public function edit() {

        try {

            // Update an event
            $res = EventService::getInstance()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());

            $res['date_start'] = MongoHelper::dateToYmd($res['date_start']);
            $res['date_end'] = MongoHelper::dateToYmd($res['date_end']);
            $res['time_edit'] = MongoHelper::dateToYmd($res['time_edit']);
            $res['id'] = $this->reqInfo->urlParam('id');

            // Check an event already tag or not
            $check_tags = TagService::getInstance()->check($res['id'], $this->getCtx());

            if ($check_tags > 0) {

                // Edit tags
                $res['tags'] = TagService::getInstance()->edit($res['id'], $this->reqInfo->params(), $this->getCtx());
            }else{

                // Add tags
                $res['tags'] = TagService::getInstance()->add($res['id'], $this->reqInfo->params(), $this->getCtx());
            }

            // Check a location already tag or not
            $check_location = LocationService::getInstance()->check($res['id'], $this->getCtx());
            if ($check_location > 0) {
                
                $res['location'] = LocationService::getInstance()->edit($res['id'], $this->reqInfo->params(), $this->getCtx());
            }else{
                
                $location = LocationService::getInstance()->add($res['id'], $this->reqInfo->params(), $this->getCtx());
                MongoHelper::standardIdEntity($location);
                $res['location'] = $location;
            }

            $res['status'] = 200;
            return $res;

        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     *
     * @return type
     *
     * @api {put} /event/alarm/:event_id/:active PUT /event/alarm/:event_id/:active
     * @apiDescription Update alarm an event
     * @apiName PutEventAlarm
     * @apiGroup Event
     * @apiParam {String} event_id Event id
     * @apiParam {Integer} active 0 is Disable, 1 is Enable
     * @apiSuccessExample {json} Success-Response:
        {
            "event_id": "54ba191510f0edb7048b456a",
            "active": 1
        }
     *
     * @PUT
     * @uri /alarm/[h:event_id]/[i:active]
     */
    public function alarm() {
        try {
            $params = [
                'event_id' => $this->reqInfo->urlParam('event_id'),
                'active' => (int)$this->reqInfo->urlParam('active'),
            ];

            $update = EventService::getInstance()->alarm($params, $this->getCtx());
            return $update;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     * @api {get} /event/category_lists/:lang GET /event/category_lists/:lang
     * @apiDescription List an event that is not empty
     * @apiName GetEventCategory
     * @apiGroup Event
     * @apiParam {String} lang Language like en, th. Default is en
     * @apiSuccessExample {json} Success-Response:
    {
        "data": [
            {
                "id": "54c0ad7410f0ed5e048b4567",
                "name": "Promotion"
                "thumb": {
                    "id": "54ba7edc90cc137f238b45ffpng",
                    "width": 100,
                    "height": 100,
                    "url": "http:\/\/110.164.70.60\/get\/54ba7edc90cc137f238b45ffpng\/"
                }
            },
            {...},
        ]
    }
     *
     * @GET
     * @uri /category_lists/[a:lang]
     */
    public function category_lists() {
        try {

            $params = [
                'lang' => $this->reqInfo->urlParam('lang'),
            ];

            $v = new Validator($params);
            $v->rules([
                    'required' => [ ['lang'] ],
                    'length' => [['lang', 2]]
                ]);
            if(!$v->validate()){
                throw new ServiceException(ResponseHelper::validateError($v->errors()));
            }

            // Get categories
            $category_lists = SniffService::getInstance()->gets($params['lang'], [], $this->getCtx());

            // Filter categories
            $category = EventService::getInstance()->category_lists($category_lists['data'], $this->getCtx());

            $res = [
                'data' => $category,
            ];

            return $res;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /event/today/:lang GET /event/today/:lang
     * @apiDescription Show an event from the future
     * @apiName GetEventToday
     * @apiGroup Event
     * @apiParam {String} lang Language like en, th. Default is en
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
        {
            "name": "Title example",
            "thumb": {
                "id": "54ba7edc90cc137f238b45ffpng",
                "width": 100,
                "height": 100,
                "url": "http:\/\/110.164.70.60\/get\/54ba7edc90cc137f238b45ffpng\/"
            },
            "id": "54ba1bc910f0edb8048b456c",
            "date_start": "2015-01-24 10:15:00",
            "date_end": "2015-01-24 10:15:00",
            "type": "item",
            "total_sniffer": 10
        },
        {...}
    ],
    "length": 2
}
     *
     * @GET
     * @uri /today/[a:lang]
     */
    public function today() {
        try {
            
            $params = [
                'lang' => $this->reqInfo->urlParam('lang'),
            ];

            $v = new Validator($params);
            $v->rules([
                    'required' => [ ['lang'] ],
                    'length' => [['lang', 2]]
                ]);
            if(!$v->validate()){
                throw new ServiceException(ResponseHelper::validateError($v->errors()));
            }
            
            // Get categories
            $category_lists = SniffService::getInstance()->gets($params['lang'], [], $this->getCtx());

            $res['data'] = EventService::getInstance()->today($category_lists, $this->getCtx());
            $res['length'] = count($res['data']);
            return $res;

        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /event/upcoming GET /event/upcoming
     * @apiDescription Show an upcoming event 
     * @apiName GetEventUpcoming
     * @apiGroup Event
     * @apiParam {String} limit [Optional] Limit event to display. Default is 20
     * @apiParamExample {String} Request-Example:
     * /event/upcoming?limit=2
     * @apiSuccessExample {json} Success-Response:
{
  "data": [
    {
        "date_start": "2015-02-04 17:13:01",
        "detail": "test add detail 1422439981",
        "name": "test add name 1422439981",
        "id": "54c8b62d10f0ed1e048b4584",
        "thumb": {
            "id": "54c9193a90cc13ac048b4638png",
            "width": 25,
            "height": 25,
            "url": "http://110.164.70.60/get/54c9193a90cc13ac048b4638png/"
        }
        "total_sniffer": 2
    },
    {...}
  ],
  "length": 2
}
     * 
     * @GET
     * @uri /upcoming
     */
    public function upcoming() {
        try {
            $items = EventService::getInstance()->upcoming($this->reqInfo->params(), $this->getCtx());
            
            // New sort with rand
            usort($items, function($a, $b){
                return $a['rand'] - $b['rand'];
            });
            
            return ['data' => $items, 'length' => count($items)];
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /event/category_set/:category_id GET /event/category_set/:category_id
     * @apiDescription Get event from category on today and in range
     * @apiName GetEventCategorySet
     * @apiGroup Event
     * @apiParam {String} category_id Category Id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
    {
        "date_start": "2015-01-29 10:49:15",
        "name": "test add name 1422503355",
        "id": "54c9adb810f0ed5b048b4568",
        "category": "54c0ad7410f0ed5e048b4572",
        "thumb": {
            "id": "54ca10c790cc13aa048b461apng",
            "width": 25,
            "height": 25,
            "url": "http://110.164.70.60/get/54ca10c790cc13aa048b461apng/"
        }
    },
    {...}
    ],
    "length": 2
}
     * 
     * @GET
     * @uri /category_set/[h:category_id]
     */
    public function category_set() {
        try {
                    
            $res['data'] = EventService::getInstance()->category_set($this->reqInfo->urlParam('category_id'), $this->getCtx());
            $res['length'] = count($res['data']);
            return $res;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /event/category/upcoming/:category_id GET /event/category/upcoming/:category_id
     * @apiDescription Get event list when click from /event/category_lists/:lang
     * @apiName GetEventCategoryUpcoming
     * @apiGroup Event
     * @apiParam {String} category_id Category Id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
    {
        "date_end": "2015-02-11 17:13:01",
        "date_start": "2015-02-04 17:13:01",
        "name": "test add name 1422439981",
        "id": "54c8b62d10f0ed1e048b4584",
        "thumb": {
            "id": "54c9193a90cc13ac048b4638png",
            "width": 25,
            "height": 25,
            "url": "http://110.164.70.60/get/54c9193a90cc13ac048b4638png/"
        },
        "total_sniffer": 0
    },
    {...}
    ],
    "length": 2
}
     * 
     * @GET
     * @uri /category/upcoming/[h:category_id]
     */
    public function category_upcoming() {
        try {
            
            $res['data'] = EventService::getInstance()->category_upcoming($this->reqInfo->urlParam('category_id'), $this->getCtx());
            $res['length'] = count($res['data']);
            return $res;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {post} /event/search POST /event/search
     * @apiDescription Search an event
     * @apiName PostEventSearch
     * @apiGroup Event
     * @apiParam {String} word Any word that you want to search
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
    {
        "name": "test add name 1422439981",
        "id": "54c8b62d10f0ed1e048b4584"
    },
    {...}
    ],
    "length": 2
}
     * @POST
     * @uri /search
     */
    public function search() {
        try {
            $res['data'] = EventService::getInstance()->search($this->reqInfo->input('word'), $this->getCtx());
            $res['length'] = count($res['data']);
            return $res;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /event/advertise GET /event/advertise
     * @apiDescription Get an event that is advertise
     * @apiName GetEventAdvertise
     * @apiGroup Event
     * @apiHeader {String} Access-Token User Access Token
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
        {
            "name": "Test an event",
            "date_start": "2015-02-14 00:00:00",
            "date_end": "2015-02-17 23:30:00",
            "advertise": {
                "enable": 1,
                "cities": [
                    "54b8e0e010f0edcf048b4569",
                    "54b8e0e010f0edcf048b4575",
                    "54b8e0e010f0edcf048b459e"
                ],
                "time_start": "2015-02-26 15:20:29"
            },
            "id": "54ee0b7efc5067a3208b4567",
            "thumb": {
                "id": "54ee6e8590cc13ab778b4593jpg",
                "width": 595,
                "height": 397,
                "url": "http://110.164.70.60/get/54ee6e8590cc13ab778b4593jpg/"
            },
            "total_sniffer": 0
        },
        {...}
    ],
    "length": 2
}
     * @GET
     * @uri /advertise
     */
    public function get_advertise() {
        try {
            if(UserHelper::hasPermission('event', 'read') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access Deny'));
            }
            
            $items['data'] = EventService::getInstance()->get_advertise($this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {put} /event/advertise/:event_id PUT /event/advertise/:event_id
     * @apiDescription Edit an event by add advertise
     * @apiName PutEventAdvertise
     * @apiGroup Event
     * @apiParam {String} event_id Event id
     * @apiParam {String} enable Set to 0 for disable, set to 1 to enable
     * @apiHeader {String} Access-Token User Access Token
     * @apiSuccessExample {json} Success-Response:
{
    "enable":1,
    "cities":[
        "54b8e0e010f0edcf048b4569",
        "54b8e0e010f0edcf048b4575",
        "54b8e0e010f0edcf048b459e"
    ],
    "id":"54ee0b7efc5067a3208b4567"
}
     * @PUT
     * @uri /advertise/[h:event_id]
     */
    public function edit_advertise() {
        try {
            
            if(UserHelper::hasPermission('event', 'edit') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access Deny'));
            }
            
            $items = EventService::getInstance()->edit_adverties($this->reqInfo->urlParam('event_id'), $this->reqInfo->params(), $this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
