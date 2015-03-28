<?php
$I = new ApiTester($scenario);
$I->wantTo('Update Appointment Status');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '5f45012ef2611ec87e0eb19c048bad89c34b29e9c0896cb6cf9fd2930fbedc8d');
$I->sendPUT('appoint/status/5515209910f0ed79188b4567', [
//    'status' => 'pending',
    'status' => 'confirmed'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
