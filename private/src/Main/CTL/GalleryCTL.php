<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 12:37 PM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\UserHelper,
    Main\Service\GalleryService,
    Main\Service\LogService;

/**
 * Class EventCTL
 * @package Main\CTL
 * @Restful
 * @uri /gallery
 */
class GalleryCTL extends BaseCTL {

    /**
     * @api {post} /gallery/:event_id POST /gallery/:event_id
     * @apiDescription Please looking on /event/gallery/:event_id
     * @apiName PostPicture
     * @apiGroup Gallery
     * 
     * @POST
     * @uri /[h:event_id]
     */
    public function add(){
        try{     
            
            $data = [
                'picture' => $this->reqInfo->param('picture'),
                'user_id' => $this->reqInfo->param('user_id'),
                'event_id' => $this->reqInfo->urlParam('event_id'),
                'detail' => $this->reqInfo->param('detail'),
            ];
            
            $res = [];
            $res['picture'] = GalleryService::getInstance()->add($data, $this->getCtx());
            $res['user_id'] = $data['user_id'];
            $res['event_id'] = $data['event_id'];
            $res['id'] = $data['event_id'];
            $res['status'] = 200;
            return $res;

        } catch(ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /gallery/:event_id GET /gallery/:event_id
     * @apiDescription Get all picture from event_id
     * @apiName GetPictures
     * @apiGroup Gallery
     * @apiParam {String} event_id Event id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
    {
        "picture": {
            "id": "54c8c13490cc13a8048b4619png",
            "width": 25,
            "height": 25,
            "url": "http://110.164.70.60/get/54c8c13490cc13a8048b4619png/"
        },
        "id": "54c85e2610f0ed1e048b4569"
    },
    {...}
    ],
    "length": 2
}
     * 
     * @GET
     * @uri /[h:event_id]
     */
    public function gets() {
        try {
            
            $items['data'] = GalleryService::getInstance()->gets($this->reqInfo->urlParam('event_id'), $this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /gallery/picture/:picture_id GET /gallery/picture/:picture_id
     * @apiDescription Get picture
     * @apiName GetPicture
     * @apiGroup Gallery
     * @apiParam {String} picture_id Picture id
     * @apiHeader {String} Access-Token (Optional) User Access Token
     * @apiSuccessExample {json} Success-Response:
{
    "data": {
        "picture": {
            "id": "54cba97490cc1382588b4567png",
            "width": 25,
            "height": 25,
            "url": "http://110.164.70.60/get/54cba97490cc1382588b4567png/"
        },
        "id": "54cb466810f0ed23048b4567",
        "detail": ""
    }
}
     * 
     * @GET
     * @uri /picture/[h:picture_id]
     */
    public function get() {
        try {
            
            $item['data'] = GalleryService::getInstance()->get($this->reqInfo->urlParam('picture_id'), $this->getCtx());
            
            // For none register user
            UserHelper::$user_id = 0;
                    
            $token = \Main\Http\RequestInfo::getToken();
            if($token !== false){
                if(UserHelper::check_token() === false){
                    throw new ServiceException(ResponseHelper::error('Invalid user'));
                }
            }
            
            $data_log = [
                'reference_id' => $item['data']['id'],
                'type' => 'picture',
                'status' => 'view',
            ];
            LogService::getInstance()->save($data_log, $this->getCtx());
            
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {delete} /gallery/picture/:picture_id DELETE /gallery/picture/:picture_id
     * @apiDescription Delete picture
     * @apiName DeletePicture
     * @apiGroup Gallery
     * @apiParam {String} picture_id Picture id
     * @apiSuccessExample {json} Success-Response:
     * {"success":true}
     * @DELETE
     * @uri /picture/[h:picture_id]
     */
    public function delete() {
        try {
            
            $item = GalleryService::getInstance()->delete($this->reqInfo->urlParam('picture_id'), $this->getCtx());
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}