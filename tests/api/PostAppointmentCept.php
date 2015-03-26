<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add Appointment');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '2cba37f7c3a7815f8a380a4f51fbc5c8766d2fbf7b96d94e3b85b970a0ff0cc2');
$I->sendPOST('appoint', [
    'date_add' => '2015-03-11',
    'time_add' => '13:30',
    'name' => 'Cartman',
    'phone' => '0811111223',
    'detail' => "test to add detail ".time(),
    'status' => 'new'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
