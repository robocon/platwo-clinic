<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\CommentService;

/**
 * Description of CommentCTL
 *
 * @author robocon
 * 
 * @package Main\CTL
 * @Restful
 * @uri /comment
 */
class CommentCTL extends BaseCTL {
    
    /**
     * 
     * @param type $param
     * 
     * @api {post} /comment/:event_id POST /comment/:event_id
     * @apiDescription Comment into event
     * @apiName CommentSave
     * @apiGroup Comment
     * @apiParam {String} event_id Event id
     * @apiParam {String} detail Comment details
     * @apiParam {String} user_id User id
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "detail": "hello world",
     *  "user_id": "54ba29c210f0edb8048b457a",
     *  "event_id": "54ba191510f0edb7048b456a",
     *  "time_stamp": "2015-01-21 11:09:11",
     *  "user": {
     *      "name": "Kritsanasak Kuntaros",
     *      "picture": {
     *          "id": "54ba8cd690cc1350158b4619jpg",
     *          "width": 180,
     *          "height": 180,
     *          "url": "http:\/\/110.164.70.60\/get\/54ba8cd690cc1350158b4619jpg\/"
     *      }
     *  },
     *  "id": "54bf266710f0ed12048b456a"
     * }
     * 
     * @POST
     * @uri /[h:event_id]
     */
    public function save() {
        try {
            
            $params = $this->reqInfo->params();
            $params['event_id'] = $this->reqInfo->urlParam('event_id');

            $item = CommentService::getInstance()->save($params, $this->getCtx());
            MongoHelper::standardIdEntity($item);
            
            $item['time_stamp'] = MongoHelper::timeToStr($item['time_stamp']);
            
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /comment/:event_id GET /comment/:event_id
     * @apiDescription Get comments from event_id
     * @apiName GetComment
     * @apiGroup Comment
     * @apiParam {String} event_id User id
     * @apiParam {String} page (Optional) Pagination length
     * @apiParam {String} limit (Optional) Limit an event when show from each page
     * 
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
        {
            "detail": "test comment 1422678850",
            "time_stamp": "2015-01-31 11:34:10",
            "id": "54cc5b4210f0ed21048b456c",
            "user": {
            "display_name": "Kritsanasak Kuntaros",
            "picture": {
                "id": "54ba8cd690cc1350158b4619jpg",
                "width": 180,
                "height": 180,
                "url": "http://110.164.70.60/get/54ba8cd690cc1350158b4619jpg/"
            },
            "id": "54ba29c210f0edb8048b457a"
        }
        },{...}
    ],
    "length": 10,
    "total": 23,
    "prev_count": 3,
    "paging": {
        "next": "http://eventsniff.dev/comment/54ba191510f0edb7048b456a?page=2&limit=10",
        "prev": "http://eventsniff.dev/comment/54ba191510f0edb7048b456a?page=1&limit=10"
    }
}
     * 
     * @GET
     * @uri /[h:event_id]
     */
    public function gets() {
        try {
            
            $items = CommentService::getInstance()->gets($this->reqInfo->urlParam('event_id'), $this->reqInfo->params(), $this->getCtx());
            return $items;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
