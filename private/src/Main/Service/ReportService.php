<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\Service;

use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Valitron\Validator;


class ReportService extends BaseService {
    
    public function getReportCollection(){
        $db = DB::getDB();
        return $db->report;
    }
    
    public function save($params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['detail', 'type', 'user_id', 'reference_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $this->getReportCollection()->insert($params);
        
        $params['id'] = $params['_id']->{'$id'};
        unset($params['_id']);
        
        return $params;
    }
}
