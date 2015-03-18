<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/23/14
 * Time: 3:42 PM
 */

namespace Main\CTL;
use Main\DataModel\Image,
    Main\Exception\Service\ServiceException,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\UserHelper,
    Main\Helper\NodeHelper,
    Main\Http\RequestInfo,
    Main\Service\CouponService,
    Main\Service\PromotionService;

/**
 * @Restful
 * @uri /coupon
 */
class CouponCTL extends BaseCTL {
    /**
     * @POST
     */
    public function addCoupon(){
        try {
            $item = CouponService::getInstance()->addCoupon($this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($item);
            $item['created_at'] = MongoHelper::dateToYmd($item['created_at']);
            $item['updated_at'] = MongoHelper::dateToYmd($item['updated_at']);
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
//            ArrayHelper::pictureToThumb($item);
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     */
    public function gets(){
        try {
            
            $token = RequestInfo::getToken();
            $user = false;
            if($token){
                $check_user = UserHelper::check_token();
                if($check_user !== false){
                    $user = UserHelper::getUserDetail();
                }
            }
            
            $items = CouponService::getInstance()->gets($this->reqInfo->params(), $this->getCtx());
            foreach($items['data'] as $key => $item){
                MongoHelper::standardIdEntity($item);
                $item['created_at'] = MongoHelper::dateToYmd($item['created_at']);
                $item['updated_at'] = MongoHelper::dateToYmd($item['updated_at']);
                
                    $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
                    $item['used_status'] = "none";
                    
                    if($user !== false){
                        foreach($item['used_users'] as $value){
                            if($value['user']['id'] == $user['id']){
                                if($value['expire']->{'sec'} <= time()){
                                    $item['used_status'] = "timeout";
                                    break;
                                }
                                $item['used_status'] = "countdown";
                                break;
                            }
                        }
                    }
                unset($item['used_users']);
                $items['data'][$key] = $item;
            }
            return $items;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        try {
            
            $token = RequestInfo::getToken();
            $user = false;
            if($token){
                $check_user = UserHelper::check_token();
                if($check_user !== false){
                    $user = UserHelper::getUserDetail();
                }
            }
            
            $item = CouponService::getInstance()->get($this->reqInfo->urlParam('id'), $this->getCtx());
            CouponService::getInstance()->incView($this->reqInfo->urlParam('id'), $this->getCtx());

            MongoHelper::standardIdEntity($item);
            $item['created_at'] = MongoHelper::dateToYmd($item['created_at']);
            $item['updated_at'] = MongoHelper::dateToYmd($item['updated_at']);
//            if($item['type']=="coupon" || true){
//            if(true){
                $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
//                $user = $this->getCtx()->getUser();
                $item['used_status'] = "none";
                
                if($user !== false){
                    foreach($item['used_users'] as $value){
                        if($value['user']['id'] == $user['id']){
                            if($value['expire']->{'sec'} <= time()){
                                $item['used_status'] = "timeout";
                                break;
                            }
                            $item['used_status'] = "countdown";
                            break;
                        }
                    }
                }
                unset($item['used_users']);
//            }
//            else {
//                $item['node'] = NodeHelper::promotion($item['id']);
//                ArrayHelper::pictureToThumb($item);
//            }

            // translate
//            if($this->getCtx()->getTranslate()){
//                ArrayHelper::translateEntity($item, $this->getCtx()->getLang());
//            }

            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @PUT
     * @uri /[h:id]
     */
    public function editCoupon(){
        try {
            $item = CouponService::getInstance()->editCoupon($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($item);
            $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
            $item['updated_at'] = MongoHelper::timeToInt($item['updated_at']);
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
//            ArrayHelper::pictureToThumb($item);

            // translate
//            if($this->getCtx()->getTranslate()){
//                ArrayHelper::translateEntity($item, $this->getCtx()->getLang());
//            }

//            $item['node'] = NodeHelper::roomtype($item['id']);

            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @DELETE
     * @uri /[h:id]
     */
    public function delete(){
        try {
            CouponService::getInstance()->delete($this->reqInfo->urlParam('id'), $this->getCtx());
            return ['success'=> true];
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @POST
     * @uri /request/[h:id]
     */
    public function couponRequest(){
        try {
            
            $token = UserHelper::check_token();
            if($token === false){
                throw new ServiceException(ResponseHelper::error('Invalid user token'));
            }
            
            $res = CouponService::getInstance()->requestCoupon($this->reqInfo->urlParam('id'), $this->getCtx());
            return $res;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     * @uri /[h:id]/used_users
     */
    public function usedUsers(){
        try {
            $items = CouponService::getInstance()->usedUsers($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            foreach($items['data'] as $key=> $item){
                MongoHelper::standardIdEntity($item['user']);
                $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
                $item['expire'] = MongoHelper::timeToInt($item['expire']);

                $items['data'][$key] = $item;
            }
            return $items;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @POST
     * @uri /sort
     */
    public function sort(){
        try {
            $res = CouponService::getInstance()->sort($this->reqInfo->params(), $this->getCtx());
            return $res;
        }
        catch (ServiceException $e){
            return $e->getResponse();
        }
    }
}