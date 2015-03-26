<?php

namespace Main\CTL;
use Main\Exception\Service\ServiceException,
    Main\Service\AppointmentService,
    Main\Helper\UserHelper,
    Main\Helper\ResponseHelper;

/**
 * @Restful
 * @uri /appoint
 */
class AppointmentCTL extends BaseCTL {
    
    /**
     * @POST
     * @uri /password
     */
    public function check_password() {
        try {
            
            if(UserHelper::hasPermission('appoint', 'read') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            
            $item = AppointmentService::getInstance()->check_password($this->reqInfo->param('password'), $this->getCtx());
            
            if($item !== true){
                return $item;
            }else{
                return ['success' => $item];
            }
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @POST
     */
    public function add(){
        try {
            
            if(UserHelper::hasPermission('appoint', 'add') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            $user_id = UserHelper::$user_id;
            
            $item = AppointmentService::getInstance()->add($user_id, $this->reqInfo->params(), $this->getCtx());
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @POST
     * @uri /history
     */
    public function add_history(){
        try {
            
            if(UserHelper::hasPermission('appoint', 'add') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            $user_id = UserHelper::$user_id;
            
            $item = AppointmentService::getInstance()->add_history($user_id, $this->reqInfo->params(), $this->getCtx());
            return ['success' => $item];
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @GET
     */
    public function gets() {
        try {
            
            if(UserHelper::hasPermission('appoint', 'read') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            $user_id = UserHelper::$user_id;
            $items['data'] = AppointmentService::getInstance()->gets($user_id, $this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @GET
     * @uri /[h:appoint_id]
     */
    public function get(){
        try {
            if(UserHelper::hasPermission('appoint', 'read') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            
            $item = AppointmentService::getInstance()->get($this->reqInfo->urlParam('appoint_id'), $this->getCtx());
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @PUT
     * @uri /status/[h:appoint_id]
     */
    public function change_status() {
        try {
            
            if(UserHelper::hasPermission('appoint_status', 'update') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            
            $item = AppointmentService::getInstance()->change_status($this->reqInfo->urlParam('appoint_id'), $this->reqInfo->params(), $this->getCtx());
            return ['success' => $item];
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @PUT
     * @uri /[h:appoint_id]
     */
    public function update_appointment() {
        try {
            if(UserHelper::hasPermission('appoint_status', 'update') === false){
                throw new ServiceException(ResponseHelper::notAuthorize('Access deny'));
            }
            
            $item = AppointmentService::getInstance()->update_appointment($this->reqInfo->urlParam('appoint_id'), $this->reqInfo->params(), $this->getCtx());
            return ['success' => $item];
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @POST
     * @uri /datetime
     */
    public function save_datetime() {
        try {
            
            $item = AppointmentService::getInstance()->save_datetime($this->reqInfo->params(), $this->getCtx());
            return ['success' => $item];
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @GET
     * @uri /datetime
     */
    public function get_datetime() {
        try {
            
            $item = AppointmentService::getInstance()->get_datetime($this->getCtx());
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
