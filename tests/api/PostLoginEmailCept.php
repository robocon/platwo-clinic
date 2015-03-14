<?php 
$I = new ApiTester($scenario);
$I->wantTo('Login email');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('oauth/password', [
    'username' => 'roboconk@gmail.com',
    'password' => '111111',
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$I->seeResponseContainsJson([
    'user_id' => $user_id
]);