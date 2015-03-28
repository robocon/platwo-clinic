<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add Appointment');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '5f45012ef2611ec87e0eb19c048bad89c34b29e9c0896cb6cf9fd2930fbedc8d');
$I->sendPOST('appoint', [
    'date_add' => '2015-04-02',
    'time_add' => '10:00',
    'name' => 'test',
    'phone' => '0811111111',
    'detail' => "test to confirm ".time(),
    'status' => 'new'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
