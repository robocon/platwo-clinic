<?php 
$I = new ApiTester($scenario);
$I->wantTo('Update Appointment detail');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$I->sendPUT('appoint/54f96d0310f0ed41048b456d', [
    'date_add' => '2015-03-12',
    'time_add' => '15:15',
    'name' => 'Cartman',
    'phone' => '0822222222',
    'detail' => "test to edit detail ".time(),
    'status' => 'new'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
