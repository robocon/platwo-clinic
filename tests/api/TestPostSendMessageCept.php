<?php 
$I = new ApiTester($scenario);
$I->wantTo('Test send message');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', 'cdee94ec81af380033a9b7fdc80bf67ed3180d943e418ba11b585d9f31513f7e');
$I->sendPOST('demo/message', [
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);