<?php 
$user_id = '54f4281010f0ed53058b4567';

$I = new ApiTester($scenario);
$I->wantTo('Update User Profile Facebook Name');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '86c212c49d1dc3af59087212933376156d3a28d8738752a4acb6e1f1fe2eac55');
$I->sendPUT('user/profile/'.$user_id, [
//    'fb_name' => 'Cartman v2',
    'hn_number' => 'TH112233445566',
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);