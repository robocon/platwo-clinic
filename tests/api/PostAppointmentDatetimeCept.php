<?php
$I = new ApiTester($scenario);
$I->wantTo('Save Appointment datetime');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$I->sendPOST('appoint/datetime', [
    'date' => ['Mon', 'Wed', 'Fri'],
    'time_start' => '09:00',
    'time_end' => '17:00'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);