<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Service\CountryService,
    Main\Service\CityService;

/**
 * Description of LocationCTL
 *
 * @author robocon
 * 
 * @Restful
 * @uri /location
 */
class LocationCTL extends BaseCTL {
    
    /**
     * @api {get} /location/countries GET /location/countries
     * @apiDescription Get all country
     * @apiName LocationCountries
     * @apiGroup Location
     * @apiSuccessExample {json} Success-Response:
     * {
     *      "data": [
     *          {
     *              "id": "54b8dfa810f0edcf048b4567",
     *              "name" "thailand",
     *          },
     *          ...
     *      ]
     * }
     * 
     * @GET
     * @uri /countries
     */
    public function countries() {
        try {
            $countries = CountryService::getInstance()->get(array(), $this->getCtx());
            return $countries;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /location/cities/:country_id GET /location/cities/:country_id
     * @apiDescription Get all city from country
     * @apiName LocationCities
     * @apiGroup Location
     * @apiSuccessExample {json} Success-Response:
     * {
     *      "data": [
     *          {
     *              "id": "54b8e0e010f0edcf048b4568",
     *              "name" "Krabi",
     *          },
     *          {
     *              "id": "54b8e0e010f0edcf048b4569",
     *              "name" "Bangkok",
     *          },
     *          ...
     *      ]
     * }
     * 
     * @GET
     * @uri /cities/[h:country_id]
     */
    public function cities() {
        try {
            $cities = CityService::getInstance()->get($this->reqInfo->urlParam('country_id'), $this->getCtx());
            return $cities;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
