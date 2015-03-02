<?php
/**
 * Created by PhpStorm.
 * User: MRG
 * Date: 10/18/14 AD
 * Time: 11:41 AM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\UserService;


/**
 * @Restful
 * @uri /register
 */
class RegisterCTL extends BaseCTL {

    /**
     * @api {post} /register POST /register
     * @apiDescription Register with email
     * @apiName PostRegister
     * @apiGroup Resister
     * @apiParam {String} username Your username using for login to system
     * @apiParam {String} email Email address
     * @apiParam {String} password Your password
     * @apiParam {String} gender male or female
     * @apiParam {String} birth_date Your birth date
     * @apiParam {String} ios_device_token (Optional) Token from your mobile
     * @apiParam {String} android_token (Optional) Token from your mobile
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "user_id": "54506d34da354df2078b4569",
     *  "access_token": "9f0f853517eaaed3c0b74838e6e95693",
     *  "type": "normal",
     * }
     * @POST
     */
    public function add(){
        try{
            $item = UserService::getInstance()->add($this->reqInfo->inputs(), $this->getCtx());
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }
    
    /**
     * @api {post} /register/noneuser POST /register/noneuser
     * @apiDescription Booking user with device token
     * @apiName PostRegisterNoneuser
     * @apiGroup Resister
     * @apiParam {String} ios_device_token (Optional) Token from your mobile
     * @apiParam {String} android_token (Optional) Token from your mobile
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "user_id": "54506d34da354df2078b4569",
     *  "access_token": "dbc0296946cc0591bb0520eb59e633a21be8c0f8aa746be4e2edeac300fad566",
     *  "type": "none",
     * }
     * @POST
     * @uri /noneuser
     */
    public function noneuser() {
        try {
            $item = UserService::getInstance()->noneuser($this->reqInfo->inputs(), $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}