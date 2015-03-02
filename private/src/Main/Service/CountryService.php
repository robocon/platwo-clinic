<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\Service;

use Main\DB,
    Main\Context\Context;

/**
 * Description of CountryService
 *
 * @author robocon
 */
class CountryService extends BaseService {
    public function getCollection(){
        $db = DB::getDB();
        return $db->countries;
    }
    
    public function get($options = array(), Context $ctx) {
        $items = $this->getCollection()->find();
        
        $data = [];
        foreach($items as $item){
            $data[] = [
                'id' => $item['_id']->{'$id'}, 
                'name' => $item['name']
            ];
        }
        
        return ['data' => $data];
    }
}
