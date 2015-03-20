<?php 
$I = new ApiTester($scenario);
$I->wantTo('Test send message');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '40685aaa34591b0932df6080e58dfc3cf61c621859aee1c62108bc8b105a2788');
$I->sendPOST('demo/message', [
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);