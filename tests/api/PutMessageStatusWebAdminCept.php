<?php 
$I = new ApiTester($scenario);
$I->wantTo('Update message status');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('message/status', [
    'access_token' => '2cba37f7c3a7815f8a380a4f51fbc5c8766d2fbf7b96d94e3b85b970a0ff0cc2',
    'id' => '5513aed8da354da0088b4568',
    'target_id' => '54f95eb010f0ed41048b456b'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
