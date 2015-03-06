<?php 
$I = new ApiTester($scenario);
$I->wantTo('Email login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('register', [
    'email' => 'demouser'.time().'@hotmail.com',
    'username' => 'demo'.time(),
    'password' => '123456',
    'gender' => 'male',
    'birth_date' => '1985-08-30',
//    'ios_device_token' => [
//        'type' => 'product',
//        'key' => '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4'
//    ]
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $user_id
]);