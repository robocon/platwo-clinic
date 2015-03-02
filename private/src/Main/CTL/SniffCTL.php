<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\Helper\MongoHelper,
    Main\Helper\UserHelper,
    Main\Service\SniffService;

/**
 * @Restful
 * @uri /sniff
 */
class SniffCTL extends BaseCTL {
    
    /**
     * @api {get} /sniff/follower/:event_id GET /sniff/follower/:event_id
     * @apiDescription Show sniffer from event_id
     * @apiName GetSniffer
     * @apiGroup Sniff
     * @apiParam {String} event_id Event id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
    {
        "event_id": "54ba191510f0edb7048b456a",
        "id": "54be2e6610f0ed53058b456b",
        "user": {
            "display_name": "Demo User",
            "picture": {
                "id": "54ba8cd690cc1350158b4619jpg",
                "width": 180,
                "height": 180,
                "url": "http://110.164.70.60/get/54ba8cd690cc1350158b4619jpg/"
            },
            "id": "54ba29c210f0edb8048b457a"
        }
    }
    ],
    "length": 1
}
     * 
     * @GET
     * @uri /follower/[h:event_id]
     */
    public function follower() {
        try {
            
            $items = SniffService::getInstance()->follower($this->reqInfo->urlParam('event_id'), $this->getCtx());
            return $items;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
        
    /**
     * @api {post} /sniff/follow/:event_id/:user_id POST /sniff/follow/:event_id/:user_id
     * @apiDescription Follow an event
     * @apiName SniffFollow
     * @apiGroup Sniff
     * @apiParam {String} event_id Event id
     * @apiParam {String} user_id User id
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "event_id":"54ba191510f0edb7048b456a",
     *  "user_id":"54ba29c210f0edb8048b457a",
     *  "id":"54be2e6610f0ed53058b456b"
     * }
     * 
     * @POST
     * @uri /follow/[h:event_id]/[h:user_id]
     */
    public function follow() {
        
        $params = [];
        $params['event_id'] = $this->reqInfo->urlParam('event_id');
        $params['user_id'] = $this->reqInfo->urlParam('user_id');
        
        try {
            $item = SniffService::getInstance()->follow($params, $this->getCtx());
            MongoHelper::standardIdEntity($item);
            
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
        
    }
    
    /**
     * @api {delete} /sniff/follow/:event_id/:user_id DELETE /sniff/follow/:event_id/:user_id
     * @apiDescription Unfollow an event
     * @apiName SniffUnfollow
     * @apiGroup Sniff
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "event_id":"54ba191510f0edb7048b456a",
     *  "user_id":"54ba29c210f0edb8048b457a",
     * }
     * 
     * @DELETE
     * @uri /follow/[h:event_id]/[h:user_id]
     */
    public function unfollow() {
        
        $params = [];
        $params['event_id'] = $this->reqInfo->urlParam('event_id');
        $params['user_id'] = $this->reqInfo->urlParam('user_id');
        
        try {
            $item = SniffService::getInstance()->unfollow($params, $this->getCtx());
            MongoHelper::standardIdEntity($item);
            
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /sniff/category/:lang GET /sniff/category/:lang
     * @apiDescription Get all category
     * @apiName SniffCategory
     * @apiGroup Sniff
     * @apiParam {String} lang Language like en, th. Default is en
     * @apiSuccessExample {json} Success-Response:
     * {
     *      "length": 20,
     *      "data": [
     *          {
     *              "id": "6f2da37e72bf9e100b40567c",
     *              "name" "Awards",
     *          },
     *          {
     *              "id": "e9c5c932c205770da433d3de",
     *              "name" "Conferences",
     *          },
     *          ...
     *      ]
     * }
     * 
     * @GET
     * @uri /category/[a:lang]
     */
    public function category() {
        try {
            $categories = SniffService::getInstance()->gets($this->reqInfo->urlParam('lang'), array(), $this->getCtx());
            return $categories;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {post} /sniff/location POST /sniff/location
     * @apiDescription Get an event from lat,lng and filter with category
     * @apiName PostSniffLocation
     * @apiGroup Sniff
     * @apiParam {Array} category Category in an array to filter but if you want to show all categroy send it to String with 'all'
     * @apiParam {Array} location Lat,Lng from map with South-West and North-East
     * @apiHeader {String} Access-Token User Access Token
     * 
     * @apiHeaderExample {string} Header-Example:
     * Access-Token: 4309946fd4133f4bfc7f79d5881890400f4d59997b0d8b26886641394814cbd2
     * 
     * @apiParamExample {string} Request-Example:
     * category[]=54c0ad7410f0ed5e048b4575&category[]=54c0ad7410f0ed5e048b4579&location[0][]=18.776836&location[0][]=98.969479&location[1][]=18.800888&location[1][]=98.999863
     * 
     * @apiSuccessExample {json} Success-Response:
{
    "data":[
        {
            "name":"test add name 1424340350",
            "date_start":"2015-02-26 17:05:50",
            "id":"54e5b57c10f0edf6058b4595",
            "total_sniffer":0,
            "view":4
        },
        {...}
    ],
    "length":2
}
     * @POST
     * @uri /location
     */
    public function location() {
        try {
            
//            $token = UserHelper::check_token();
//            if($token === false){
//                throw new ServiceException(ResponseHelper::error('Invalid user token'));
//            }
            
            if(UserHelper::hasPermission('sniff', 'read') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            
            $items['data'] = SniffService::getInstance()->location($this->reqInfo->params(), $this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
