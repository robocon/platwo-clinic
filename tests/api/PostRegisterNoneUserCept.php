<?php 
$I = new ApiTester($scenario);
$I->wantTo('Register none user');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('register/noneuser', [
    'ios_device_token' => [
        'type' => 'product',
        'key' => '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4'
    ]
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $id
]);