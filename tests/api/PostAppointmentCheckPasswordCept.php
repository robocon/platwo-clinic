<?php
$I = new ApiTester($scenario);
$I->wantTo('Appointment Check Password');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '86c212c49d1dc3af59087212933376156d3a28d8738752a4acb6e1f1fe2eac55');
$I->sendPOST('appoint/password', [
    'password' => '1234'
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);