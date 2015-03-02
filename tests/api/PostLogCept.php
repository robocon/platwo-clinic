<?php 
$event_id = '54e5b57c10f0edf6058b4595';

$I = new ApiTester($scenario);
$I->wantTo('Add log');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '4d1cee2298152bede6876e16e4086c5c7f1cc23973166a226401cdfccc664cf7');
$I->sendPOST('log', [
    'reference_id' => $event_id,
//    'message' => 'test to share to facebook '.time(),
    'type' => 'event',
    'status' => 'view',
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);
