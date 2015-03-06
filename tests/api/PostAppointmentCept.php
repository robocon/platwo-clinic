<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add Appointment');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
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
