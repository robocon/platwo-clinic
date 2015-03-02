<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/27/14
 * Time: 2:42 PM
 */

namespace Main\Service;

use Facebook\FacebookRequest,
    Facebook\FacebookRequestException,
    Facebook\FacebookSession,
    Facebook\GraphUser,
    Main\Context\Context,
    Main\DataModel\Image,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper,
    Main\Helper\FacebookHelper,
    Main\Http\RequestInfo,
    Valitron\Validator;

class OAuthService extends BaseService {
    public function getUsersCollection(){
        $db = DB::getDB();
        return $db->users;
    }

    public function facebook($params, Context $ctx){
        
        $v = new Validator($params);
        $v->rule('required', ['facebook_token']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        FacebookSession::setDefaultApplication(FacebookHelper::$app_id, FacebookHelper::$app_secret);
        $session = new FacebookSession($params['facebook_token']);
        try {
            $me = (new FacebookRequest(
                $session, 'GET', '/me'
            ))->execute()->getGraphObject(GraphUser::className());
            $fbId = $me->getId();

        } catch(FacebookRequestException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage()));
        }
        
        // Check facebook id again
        if($fbId === null){
            throw new ServiceException(ResponseHelper::error('Invalid facebook token'));
        }
        
        try {
            
            // Search facebook_id from database
            $item = $this->getUsersCollection()->findOne(['fb_id'=> $fbId], ['access_token', 'type', 'private_key']);
            $now = new \MongoDate();
            
            if(is_null($item)){
                
                $fb_birth_date = $me->getBirthday();
                if(!is_null($fb_birth_date)){
                    $birth_date_timestamp = $fb_birth_date->getTimestamp();
                }
                else {
                    $birth_date_timestamp = time();
                }
                
                $birth_date = new \MongoDate($birth_date_timestamp);
                $user_private_key = UserHelper::generate_key();
                $item = [
                    '_id'=> new \MongoId(),
                    'fb_id'=> $fbId,
                    'fb_name'=> $me->getName(),
                    'display_name'=> $me->getName(),
                    'email'=> $me->getProperty('email'),
                    'gender'=> $me->getProperty('gender'),
                    'birth_date'=> $birth_date,
                    'website'=> '',
                    'mobile'=> '',
                    'created_at'=> $now,
                    'updated_at'=> $now,
                    'type'=> 'normal',
                    'setting'=> UserHelper::defaultSetting(),
                    'display_notification_number' => 0,
                    'detail' => '',
                    'username' => '',
                    'password' => '',

                    // set default last login
                    'last_login' => $now,
                    'private_key' => $user_private_key,
                    'level' => 1,
                    'advertiser' => 0,
                    'group_role' => ['group_id' => '54e855072667467f7709320e', 'role_perm_id' => '54eaf79810f0ed0d0a8b4568']
                ];
                
                $item['access_token'] = UserHelper::generate_token(MongoHelper::standardId($item['_id']), $user_private_key);

                // get picture from facebook
                $pictureSource = file_get_contents('http://graph.facebook.com/'.$fbId.'/picture?type=large');
                if($pictureSource === false){
                    throw new ServiceException(ResponseHelper::error("Can't read facebook profile picture."));
                }

                // Upload facebook profile picture to Media Server
                $pic = Image::upload(base64_encode($pictureSource));
                $item['picture'] = $pic->toArray();
                
                // Check mobile device token
                $device_token = isset($params['ios_device_token']) ? $params['ios_device_token']['key'] : $params['android_token'] ; 
                $user = $this->getUsersCollection()->findOne([
                    '$or' => [
                        ['ios_device_token.key' => $device_token],
                        ['android_token' => $device_token]
                    ]
                ]);
                
                if($user === null){
                    $this->getUsersCollection()->insert($item);
                }  else {
                    unset($item['_id']);
                    $this->getUsersCollection()->update(['_id' => new \MongoId($user['_id']->{'$id'})], ['$set' => $item]);
                    $item['_id'] = $user['_id'];
                }
                
            }
            // If login with facebook again it will generate new access_token
            else{
                $update = [
                    'last_login' => $now,
                    'last_login' => $now,
                    'access_token' => UserHelper::generate_token(MongoHelper::standardId($item['_id']), $item['private_key'])
                ];
                $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$set'=> $update]);
                $item['access_token'] = $update['access_token'];
            }
            
            $this->getUsersCollection()->ensureIndex(['access_token'=> 1, 'app_id'=> 1]);
            
            // remember device token
            if(isset($params['ios_device_token'])){
                // 
//                file_put_contents('test.txt', $params['ios_device_token']);

                $hasToken = false;
                if(isset($item['ios_device_token']) && is_array($item['ios_device_token'])){
                    foreach($item['ios_device_token'] as $key => $value){
                        if($value['type'] == $params['ios_device_token']['type']
                            && $value['key'] == $params['ios_device_token']['key']){
                            $hasToken = true;
                        }
                    }
                }
                if(!$hasToken){
                    $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['ios_device_token'=> $params['ios_device_token'] ]]);
                }
            }
            if(isset($params['android_token'])){
                $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['android_token'=> $params['android_token'] ]]);
            }

            return ['user_id'=> MongoHelper::standardId($item['_id']), 'access_token'=> $item['access_token'], 'type'=> $item['type']];

        } catch (\Exception $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage()));
        }
    }

    public function password($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['username', 'password']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Login with username or password
        $item = $this->getUsersCollection()->findOne([
            '$or' => [
                ['username'=> $params['username']],
                ['email'=> $params['username']]
            ]
        ]);
        if(is_null($item)){
            throw new ServiceException(ResponseHelper::notFound('Not found user'));
        }
        
        $check_user_password = UserHelper::generate_password($params['password'], $item['private_key']);
        
        if(!isset($item['password']) || $item['password'] != $check_user_password){
            throw new ServiceException(ResponseHelper::error('Wrong password'));
        }
        if(!isset($item['access_token'])){
            $item['access_token'] = UserHelper::generate_token(MongoHelper::standardId($item['_id']), $item['private_key']);
            $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$set'=> ['access_token'=> $item['access_token']]]);
            $this->getUsersCollection()->ensureIndex(['access_token'=> 1, 'app_id'=> 1]);
        }

        // remember device token
        if(isset($params['ios_device_token'])){
            if(isset($params['ios_device_token']['type']) && isset($params['ios_device_token']['key'])){
                $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['ios_device_token'=> $params['ios_device_token'] ]]);
            }
        }
        if(isset($params['android_token'])){
            $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['android_token'=> $params['android_token'] ]]);
        }

        // set last login
        $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$set'=> ['last_login'=> new \MongoTimestamp()]]);

        return ['user_id'=> MongoHelper::standardId($item['_id']), 'access_token'=> $item['access_token'], 'type'=> $item['type']];
    }

    public function generateToken($id){
        return md5(uniqid($id, true));
    }

    public function logout($params, Context $ctx){
        $user = $ctx->getUser();
        if(is_null($user)){
            return ResponseHelper::notAuthorize();
        }
        if(isset($params['ios_device_token'])){
            $this->getUsersCollection()->update(['_id'=> $user['_id']], ['$pull'=> ['ios_device_token'=> $params['ios_device_token']]]);
        }
        if(isset($params['android_token'])){
            $this->getUsersCollection()->update(['_id'=> $user['_id']], ['$pull'=> ['android_token'=> $params['android_token']]]);
        }

        return array('success'=> true);
    }
}