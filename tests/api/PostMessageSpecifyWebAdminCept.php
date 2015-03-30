<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add Appointment');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('message/specify', [
    'access_token' => '2cba37f7c3a7815f8a380a4f51fbc5c8766d2fbf7b96d94e3b85b970a0ff0cc2',
//    'type' => 'appointment',
    'type' => 'history',
    'traget_id' => '54f95eb010f0ed41048b456b',
    
    'id' => '5517d307fc50670b3a6080c0',
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
