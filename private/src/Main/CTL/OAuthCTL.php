<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/22/14
 * Time: 2:35 PM
 */

namespace Main\CTL;
use Main\Exception\Service\ServiceException;
use Main\Service\OAuthService;

/**
 * @Restful
 * @uri /oauth
 */
class OAuthCTL extends BaseCTL {
    /**
     * @api {post} /oauth/facebook POST /oauth/facebook
     * @apiDescription Register with facebook
     * @apiName PostOauthFacebook
     * @apiGroup Resister
     * @apiParam {String} facebook_token
     * @apiParam {String} ios_device_token Token from your mobile
     * @apiParam {String} android_token Token from your mobile
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "user_id": "54506d34da354df2078b4569",
     *  "access_token": "9f0f853517eaaed3c0b74838e6e95693",
     *  "type": "normal",
     * }
     * 
     * @POST
     * @uri /facebook
     */
    public function facebook(){
        try{
            $item = OAuthService::getInstance()->facebook($this->reqInfo->inputs(), $this->getCtx());
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @api {post} /oauth/password POST /oauth/password
     * @apiDescription Register with username or email
     * @apiName PostUserLogin
     * @apiGroup Resister
     * @apiParam {String} username Your username or your email
     * @apiParam {String} password Your password
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "user_id": "34ada06eaf0e266a230911d3b15bab3f2645f7f7783f8bcc4b05f522209772bd",
     *  "access_token": "df63b220f30f28bf15fb4e911a0540bed06a6dff89148e5a257c1a24ed56f767",
     *  "type": "normal",
     * }
     * 
     * @POST
     * @uri /password
     */
    public function password(){
        try{
            $item = OAuthService::getInstance()->password($this->reqInfo->inputs(), $this->getCtx());
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     */
    public function token(){
        return $this->getCtx()->getUser();
    }

    /**
     * @POST
     * @GET
     * @uri /logout
     */
    public function logout(){
        try{
            return OAuthService::getInstance()->logout($this->reqInfo->inputs(), $this->getCtx());
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }
}