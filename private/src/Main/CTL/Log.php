<?php

namespace Main\CTL;
use Main\Service\LogService,
    Main\Http\RequestInfo,
    Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper;

/**
 * @Restful
 * @uri /log
 */
class Log extends BaseCTL {
    
    /**
     * @api {post} /log POST /log
     * @apiDescription Save log
     * @apiName PostLog
     * @apiGroup Logs
     * 
     * @apiParam {String} type Type of log e.g. event, comment, picture
     * @apiParam {String} status Log state e.g. add, view, edit, share
     * @apiParam {String} reference_id Id of an event, comment or picture
     * @apiParam {String} message (Optional) Message when share an event to facebook
     * @apiHeader {String} Access-Token User Access Token
     * 
     * @apiHeaderExample {string} Header-Example:
     * Access-Token: 4309946fd4133f4bfc7f79d5881890400f4d59997b0d8b26886641394814cbd2
     * 
     * @apiParamExample {string} Request-Example:
     * type=event&status=share&message=share+to+facebook&reference_id=54cb466710f0ed24048b4567
     * 
     * @apiSuccessExample {json} Success-Response:
     * {"success":true}
     * 
     * @POST
     * @uri
     */
    public function save() {
        try {
            
            if(UserHelper::check_token() === false){
                throw new ServiceException(ResponseHelper::error('Invalid user'));
            }
            
            $item = LogService::getInstance()->save($this->reqInfo->params(), $this->getCtx());
            return ['success' => $item];
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
