<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\ReportService;

/**
 * @Restful
 * @uri /report
 */
class ReportCTL extends BaseCTL {
    
    /**
     * @api {post} /report POST /report
     * @apiDescription Send a report to admin
     * @apiName PostReport
     * @apiGroup Report
     * @apiParam {String} detail Message detail
     * @apiParam {String} type Something like event, picture
     * @apiParam {String} user_id User id
     * @apiParam {String} reference_id Id from event or picture
     * @apiSuccessExample {json} Success-Response:
{
    "detail":"Testing message detail 1423194517",
    "type":"event",
    "user_id":"54ba29c210f0edb8048b457a",
    "reference_id":"54cb466710f0ed24048b4567",
    "id":"54d4399610f0eda9048b4568"
}
     * @POST
     */
    public function save() {
        try {
            $items = ReportService::getInstance()->save($this->reqInfo->params(), $this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
