<?php
$I = new ApiTester($scenario);
$I->wantTo('Update Appointment Status');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '2cba37f7c3a7815f8a380a4f51fbc5c8766d2fbf7b96d94e3b85b970a0ff0cc2');
$I->sendPUT('appoint/status/5514c44510f0edee048b4567', [
    'status' => 'confirmed'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
