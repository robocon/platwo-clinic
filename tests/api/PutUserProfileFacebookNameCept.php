<?php 
$I = new ApiTester($scenario);
$I->wantTo('Update User Profile Facebook Name');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$I->sendPUT('user/profile', [
//    'fb_name' => 'IAMCARTMAN',
    'hn_number' => 'TH1900190011',
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);