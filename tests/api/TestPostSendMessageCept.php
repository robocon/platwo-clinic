<?php 
$I = new ApiTester($scenario);
$I->wantTo('Test send message');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '9dbf855f5f8d099fa44fc71e6f566c34ad46dfd6c791f51a59ef676934d288a3');
$I->sendPOST('demo/message', [
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);