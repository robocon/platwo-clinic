<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/16/14
 * Time: 3:55 PM
 */

namespace Main;


class DB {
    /** @var  \MongoClient $mongo */
    private static $mongo, $db;
    public static function getMongo(){
        if(is_null(self::$mongo)){
            self::$mongo = new \MongoClient(MONGO_HOST.':'.MONGO_PORT);
        }
        return self::$mongo;
    }

    public static function getDB(){
        if(is_null(self::$db)){
            $db_name = MONGO_DB;
            self::$db = self::getMongo()->$db_name;
        }
        return self::$db;
    }
}