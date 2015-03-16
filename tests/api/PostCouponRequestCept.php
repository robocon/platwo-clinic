<?php 

// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));

$I = new ApiTester($scenario);
$I->wantTo('Test coupon request');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$I->setHeader('X-AUTH-TOKEN', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$I->sendPOST('coupon/request/54fe9fdf10f0ed350a8b456e', [
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user = $I->grabDataFromJsonResponse('user');
$I->seeResponseContainsJson([
    'user' => $user
]);



