<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add Appointment');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', 'f70862b6e1347aa07ec841be1f555bb6632061c3f56f27bb9232cb68b7d40209');
$I->sendPOST('appoint/history', [
    'date_add' => '2015-03-24',
    'time_add' => '13:30',
    'name' => 'Test add history',
    'phone' => '0812634178',
    'detail' => "test to add history detail ".time(),
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
